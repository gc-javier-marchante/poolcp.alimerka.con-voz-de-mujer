<?php

/**
 * Class FileCategoriesController.
 *
 * Handles a file category related HTTP request.
 */
class FileCategoriesController extends AppController
{
    /**
     * Controller name
     */
    public $name = 'FileCategories';

    /**
     * @see Controller::$actionAccessLevel
     */
    protected $actionAccessLevel = [
        '*' => 0,
    ];

    /**
     * Handles file category add request.
     */
    public function add($file_category_id = 1)
    {
        if (!ACL\DbACL::canContentTypeAction('FileCategory', 'Create')) {
            $this->forbidden(!$this->isAjaxRequest());
        }

        $this->layout = 'json';
        $this->set('file_category_id', $file_category_id);

        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            // Prepare response
            $this->view = false;
            $this->resultForLayout['response']['succeeded'] = false;

            /** @var FileCategory $FileCategory **/
            $FileCategory = MySQLModel::getInstance('FileCategory');
            $fileCategoryData = post_var('fileCategory[FileCategory]');

            if ($fileCategory = $FileCategory->addNew($fileCategoryData)) {
                // Set result
                $this->resultForLayout['response']['succeeded'] = true;
                $this->resultForLayout['response']['redirect_to'] = Router::url(['controller' => 'Files', 'action' => 'index', $fileCategory['FileCategory']['file_category_id']]);
                $this->resultForLayout['response']['fileCategory'] = [
                    'id' => $fileCategory['FileCategory']['id']
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
     * Handles file category edit request.
     */
    public function edit($file_category_id = null)
    {
        if (!ACL\DbACL::canContentTypeAction('FileCategory', 'Update')) {
            $this->forbidden(!$this->isAjaxRequest());
        }

        $this->layout = 'json';

        /** @var FileCategory $FileCategory **/
        $FileCategory = MySQLModel::getInstance('FileCategory');
        $fileCategory = $FileCategory->getById($file_category_id, ['recursive' => -1]);

        if (!$fileCategory) {
            $this->notFound(false);
        }

        $this->set('fileCategory', $fileCategory);

        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            // Prepare response
            $this->view = false;
            $this->resultForLayout['response']['succeeded'] = false;

            $fileCategoryData = post_var('fileCategory[FileCategory]');
            unset($fileCategoryData['id']);

            if ($fileCategory = $FileCategory->updateFields($file_category_id, $fileCategoryData)) {
                // Set result
                $this->resultForLayout['response']['succeeded'] = true;
                $this->resultForLayout['response']['redirect_to'] = Router::url(['controller' => 'Files', 'action' => 'index', $fileCategory['FileCategory']['file_category_id']]);
                $this->resultForLayout['response']['fileCategory'] = [
                    'id' => $fileCategory['FileCategory']['id']
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
     * Handles file category delete request.
     *
     * @param $id int
     */
    public function delete($id = null)
    {
        if (!ACL\DbACL::canContentTypeAction('FileCategory', 'Delete')) {
            $this->forbidden(!$this->isAjaxRequest());
        }

        if (!$this->isPostRequest()) {
            $this->forbidden(false);
        }

        // Prepare response
        $this->layout = 'json';
        $this->view = false;
        $this->resultForLayout['response']['succeeded'] = false;

        /** @var FileCategory $FileCategory */
        $FileCategory = MySQLModel::getInstance('FileCategory');

        // Check if and id was provided
        if (!$id) {
            $this->notFound();
        }

        $fileCategory = $FileCategory->getById($id, ['recursive' => -1, 'fields' => ['id']]);

        // Check if there was any result
        if (empty($fileCategory)) {
            $this->notFound();
        }

        // Try to delete
        if ($FileCategory->deleteById($id)) {
            $this->resultForLayout['response']['file_category_id'] = $fileCategory['FileCategory']['id'];
            $this->resultForLayout['response']['succeeded'] = true;
        } else {
            // Set error message
            $error_message = __('Unable to delete.', true);

            // Check for errors
            if ($FileCategory->errors) {
                // Add all retrieved messages translated
                foreach ($FileCategory->errors as $error) {
                    $error_message .= ' ' . $error;
                }
            }

            // Add retry message
            $error_message .= ' ' . __('Please try again.', true);
            $this->resultForLayout['error'] = $error_message;
        }
    }
}
