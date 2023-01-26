<?php

use GestyMVC\Storage\Storage;

/**
 * Class FilesController.
 *
 * Handles a file related HTTP request.
 */
class FilesController extends AppController
{
    /**
     * Controller name
     */
    public $name = 'Files';

    /**
     * @see Controller::$actionAccessLevel
     */
    protected $actionAccessLevel = [
        '*' => 0,
        'view' => -1,
    ];

    /**
     * Read file
     *
     * @param $id
     * @param $token
     * @return void
     */
    public function view($id = null, $token = null)
    {
        if (!ACL\DbACL::canContentTypeAction('File', 'View')) {
            $this->forbidden(!$this->isAjaxRequest());
        }

        $expected_token = md5(GestyMVC::config('storage[keys][files]') . $id);

        if ($expected_token != $token) {
            $this->notFound(false);
        }

        /** @var File $File **/
        $File = MySQLModel::getInstance('File');
        $file = $File->getById($id, ['fields' => ['storage', 'remote_path', 'original_basename']]);

        header('Pragma: public');
        header('Expires: 0');
        header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
        header('Cache-Control: private', false);
        header('Content-Disposition: inline; filename=' . $file['File']['original_basename'] . ';');

        if (
            !$file
            || !Storage::getInstance($file['File']['storage'], 'files')->output($file['File']['remote_path'], true)
        ) {
            $this->notFound(false);
        }
    }

    /**
     * Handles file listing.
     */
    public function index($file_category_id = 1)
    {
        if (!ACL\DbACL::canContentTypeAction('File', 'List')) {
            $this->forbidden(!$this->isAjaxRequest());
        }

        /** @var FileCategory $FileCategory **/
        $FileCategory = MySQLModel::getInstance('FileCategory');
        $fileCategory = $FileCategory->getById($file_category_id, ['recursive' => -1, 'fields' => ['id', 'file_category_id', 'name']]);
        $this->set('fileCategory', $fileCategory);

        if (!$fileCategory) {
            $this->notFound();
        }

        $breadcrumbs = [
            'title' => __('Files', true),
            'title_short' => __('Files', true),
            'items' => [],
        ];

        $parent_file_category_id = $fileCategory['FileCategory']['file_category_id'];

        if ($parent_file_category_id) {
            $breadcrumbs['title'] = sprintf(__('Files in %s', true), $fileCategory['FileCategory']['name']);
            $breadcrumbs['title_short'] = $fileCategory['FileCategory']['name'];

            while (
                $parent_file_category_id
                && $parent_file_category_id > 1
            ) {
                $parentFileCategory = $FileCategory->getById($parent_file_category_id, ['recursive' => -1, 'fields' => ['id', 'file_category_id', 'name']]);
                $breadcrumbs['items'][] = [
                    'title' => $parentFileCategory['FileCategory']['name'],
                    'href' => [
                        'controller' => 'Files',
                        'action' => 'index',
                        $parentFileCategory['FileCategory']['id']
                    ],
                ];
                $parent_file_category_id = $parentFileCategory['FileCategory']['file_category_id'];
            }

            $breadcrumbs['items'][] = [
                'title' => __('Files', true),
                'href' => [
                    'controller' => 'Files',
                    'action' => 'index'
                ],
            ];
        }

        $breadcrumbs['items'][] = [
            'title' => __('Media', true),
            'href' => null,
        ];

        $this->set('breadcrumbs', $breadcrumbs);
        $this->set('file_category_id', $file_category_id);

        $this->pagination['where'] = [];
        $this->setQFilter(['name']) && ($filtered_elements = true);

        $fileCategories = $FileCategory->getAll([
            'where' => [
                ['file_category_id' => $file_category_id],
                $this->pagination['where']
            ]
        ]);

        $this->set('fileCategories', $fileCategories);

        /** @var File $File */
        $File = MySQLModel::getInstance('File');
        $File->recursive = 0;

        // Pagination config
        $this->pagination['where'] = ['file_category_id' => $file_category_id];
        $this->pagination['model'] = &$File;
        $this->pagination['default_order_by_direction'] = 'DESC';
        $this->pagination['elements_per_page'] = 11;

        $filtered_elements = false;
        $this->setQFilter(['original_basename']) && ($filtered_elements = true);

        // Retrieve elements
        $files = $this->paginate();

        // Set view vars
        $this->set(compact('files'));
        $this->set('filtered_elements', $filtered_elements);
    }

    public function move()
    {
        if (!ACL\DbACL::canContentTypeAction('File', 'Update')) {
            $this->forbidden(!$this->isAjaxRequest());
        }

        $this->layout = 'json';
        $this->view = false;
        $this->resultForLayout['response']['succeeded'] = false;

        $move_id = post_var('move_id');
        $move_category_id = post_var('move_category_id');
        $file_category_id = post_var('file_category_id');

        $Model = null;

        if ($move_id) {
            /** @var File $Model **/
            $Model = MySQLModel::getInstance('File');
            $element = $Model->updateFields($move_id, ['file_category_id' => $file_category_id]);
        } else {
            /** @var FileCategory $Model **/
            $Model = MySQLModel::getInstance('FileCategory');
            $element = $Model->updateFields($move_category_id, ['file_category_id' => $file_category_id]);
        }

        if ($element) {
            // Set result
            $this->resultForLayout['response']['succeeded'] = true;
            $this->resultForLayout['response']['redirect_to'] = Router::url(['controller' => 'Files', 'action' => 'index', $element[$Model->name]['file_category_id']]);
            $this->resultForLayout['response']['file'] = [
                'id' => $element[$Model->name]['id']
            ];
        } else {
            // Set errors
            $error_message = __('Unable to move.', true);

            if ($Model->errors) {
                // Add all retrieved messages translated
                foreach ($Model->errors as $error) {
                    $error_message .= ' ' . $error;
                }
            }

            // Add retry message
            $error_message .= ' ' . __('Please try again.', true);
            $this->resultForLayout['error'] = $error_message;
        }
    }

    /**
     * Handles file add request.
     */
    public function add($file_category_id = 1)
    {
        if (!ACL\DbACL::canContentTypeAction('File', 'Create')) {
            $this->forbidden(!$this->isAjaxRequest());
        }

        $this->layout = 'json';
        $this->set('file_category_id', $file_category_id);

        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            if (post_var('move_id') || post_var('move_category_id')) {
                return $this->move();
            }

            // Prepare response
            $this->view = false;
            $this->resultForLayout['response']['succeeded'] = false;

            // Save file
            $result = $this->saveUploadedFile('file', null, '', ($file_category_id = post_var('file_category_id')));

            if (!empty($result[0]['file']['File']['id'])) {
                // Set result
                $this->resultForLayout['response']['succeeded'] = true;
                $this->resultForLayout['response']['redirect_to'] = Router::url(['controller' => 'Files', 'action' => 'index', $file_category_id]);
                $this->resultForLayout['response']['file'] = [
                    'id' => $result[0]['file']['File']['id'],
                    'original_basename' => $result[0]['file']['File']['original_basename'],
                ];
            } else {
                // Set errors
                $error_message = __('Unable to save file.', true);

                if (!empty($result[0]['errors'])) {
                    if ($result[0]['errors']) {
                        // Add all retrieved messages translated
                        foreach ($result[0]['errors'] as $error) {
                            $error_message .= ' ' . $error;
                        }
                    }
                }

                // Add retry message
                $error_message .= ' ' . __('Please try again.', true);
                $this->resultForLayout['error'] = $error_message;
            }
        }
    }

    /**
     * Handles file edit request.
     */
    public function edit($file_id = null)
    {
        if (!ACL\DbACL::canContentTypeAction('File', 'Update')) {
            $this->forbidden(!$this->isAjaxRequest());
        }

        $this->layout = 'json';

        /** @var File $File **/
        $File = MySQLModel::getInstance('File');
        $file = $File->getById($file_id, ['recursive' => -1]);

        if (!$file) {
            $this->notFound(false);
        }

        $extension = explode('.', $file['File']['original_basename']);
        $extension = $extension[sizeof($extension) - 1];
        $file['File']['original_basename'] = substr($file['File']['original_basename'], 0, -strlen($extension) - 1);
        $this->set('file', $file);

        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            // Prepare response
            $this->view = false;
            $this->resultForLayout['response']['succeeded'] = false;

            $fileData = post_var('file[File]');
            unset($fileData['id']);

            if (!ends_with($fileData['original_basename'], '.' . $extension)) {
                $fileData['original_basename'] .= '.' . $extension;
            }

            if ($file = $File->updateFields($file_id, $fileData)) {
                // Set result
                $this->resultForLayout['response']['succeeded'] = true;
                $this->resultForLayout['response']['redirect_to'] = Router::url(['controller' => 'Files', 'action' => 'index', $file['File']['file_category_id']]);
                $this->resultForLayout['response']['file'] = [
                    'id' => $file['File']['id']
                ];
            } else {
                // Set errors
                $error_message = __('Unable to save.', true);

                if (!empty($result[0]['errors'])) {
                    if ($result[0]['errors']) {
                        // Add all retrieved messages translated
                        foreach ($result[0]['errors'] as $error) {
                            $error_message .= ' ' . $error;
                        }
                    }
                }

                // Add retry message
                $error_message .= ' ' . __('Please try again.', true);
                $this->resultForLayout['error'] = $error_message;
            }
        }
    }

    /**
     * Handles file delete request.
     *
     * @param $id int
     */
    public function delete($id = null)
    {
        if (!ACL\DbACL::canContentTypeAction('File', 'Delete')) {
            $this->forbidden(!$this->isAjaxRequest());
        }

        if (!$this->isPostRequest()) {
            $this->forbidden(false);
        }

        // Prepare response
        $this->layout = 'json';
        $this->view = false;
        $this->resultForLayout['response']['succeeded'] = false;

        /** @var File $File */
        $File = MySQLModel::getInstance('File');

        // Check if and id was provided
        if (!$id) {
            $this->notFound();
        }

        $file = $File->getById($id, ['recursive' => -1, 'fields' => ['id']]);

        // Check if there was any result
        if (empty($file)) {
            $this->notFound();
        }

        // Try to delete
        if ($File->deleteById($id)) {
            $this->resultForLayout['response']['file_id'] = $file['File']['id'];
            $this->resultForLayout['response']['succeeded'] = true;
        } else {
            // Set error message
            $error_message = __('Unable to delete.', true);

            // Check for errors
            if ($File->errors) {
                // Add all retrieved messages translated
                foreach ($File->errors as $error) {
                    $error_message .= ' ' . $error;
                }
            }

            // Add retry message
            $error_message .= ' ' . __('Please try again.', true);
            $this->resultForLayout['error'] = $error_message;
        }
    }
}
