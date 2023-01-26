<?php

use GestyMVC\Storage\Storage;

/**
 * Class PicturesController.
 *
 * Handles a picture related HTTP request.
 */
class PicturesController extends AppController
{
    /**
     * Controller name
     */
    public $name = 'Pictures';

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
        if (!ACL\DbACL::canContentTypeAction('Picture', 'View')) {
            $this->forbidden(!$this->isAjaxRequest());
        }

        $expected_token = md5(GestyMVC::config('storage[keys][pictures]') . $id);

        if ($expected_token != $token) {
            $this->notFound(false);
        }

        /** @var Picture $Picture **/
        $Picture = MySQLModel::getInstance('Picture');
        $picture = $Picture->getById($id, ['fields' => ['storage', 'remote_path', 'original_basename']]);

        if (!$picture) {
            $this->notFound(false);
        }

        $seconds_to_cache = 7 * 24 * 60 * 60;
        $ts = gmdate('D, d M Y H:i:s', time() + $seconds_to_cache) . ' GMT';
        header('Expires: ' . $ts);
        header('Pragma: cache');
        header('Cache-Control: max-age=' . $seconds_to_cache);
        header('Content-Disposition: inline; filename=' . $picture['Picture']['original_basename'] . ';');

        if (
            !$picture
            || !Storage::getInstance($picture['Picture']['storage'], 'pictures')->output($picture['Picture']['remote_path'], true)
        ) {
            $this->notFound(false);
        }
    }

    /**
     * Handles picture listing.
     */
    public function index($picture_category_id = 1)
    {
        if (!ACL\DbACL::canContentTypeAction('Picture', 'List')) {
            $this->forbidden(!$this->isAjaxRequest());
        }

        /** @var PictureCategory $PictureCategory **/
        $PictureCategory = MySQLModel::getInstance('PictureCategory');
        $pictureCategory = $PictureCategory->getById($picture_category_id, ['recursive' => -1, 'fields' => ['id', 'picture_category_id', 'name']]);
        $this->set('pictureCategory', $pictureCategory);

        if (!$pictureCategory) {
            $this->notFound();
        }

        $breadcrumbs = [
            'title' => __('Pictures', true),
            'title_short' => __('Pictures', true),
            'items' => [],
        ];

        $parent_picture_category_id = $pictureCategory['PictureCategory']['picture_category_id'];

        if ($parent_picture_category_id) {
            $breadcrumbs['title'] = sprintf(__('Pictures in %s', true), $pictureCategory['PictureCategory']['name']);
            $breadcrumbs['title_short'] = $pictureCategory['PictureCategory']['name'];

            while (
                $parent_picture_category_id
                && $parent_picture_category_id > 1
            ) {
                $parentPictureCategory = $PictureCategory->getById($parent_picture_category_id, ['recursive' => -1, 'fields' => ['id', 'picture_category_id', 'name']]);
                $breadcrumbs['items'][] = [
                    'title' => $parentPictureCategory['PictureCategory']['name'],
                    'href' => [
                        'controller' => 'Pictures',
                        'action' => 'index',
                        $parentPictureCategory['PictureCategory']['id']
                    ],
                ];
                $parent_picture_category_id = $parentPictureCategory['PictureCategory']['picture_category_id'];
            }

            $breadcrumbs['items'][] = [
                'title' => __('Pictures', true),
                'href' => [
                    'controller' => 'Pictures',
                    'action' => 'index'
                ],
            ];
        }

        $breadcrumbs['items'][] = [
            'title' => __('Media', true),
            'href' => null,
        ];

        $this->set('breadcrumbs', $breadcrumbs);
        $this->set('picture_category_id', $picture_category_id);

        $this->pagination['where'] = [];
        $this->setQFilter(['name']) && ($filtered_elements = true);

        $pictureCategories = $PictureCategory->getAll([
            'where' => [
                ['picture_category_id' => $picture_category_id],
                $this->pagination['where']
            ]
        ]);

        $this->set('pictureCategories', $pictureCategories);

        /** @var Picture $Picture */
        $Picture = MySQLModel::getInstance('Picture');
        $Picture->recursive = 0;

        // Pagination config
        $this->pagination['where'] = ['picture_category_id' => $picture_category_id];
        $this->pagination['model'] = &$Picture;
        $this->pagination['default_order_by_direction'] = 'DESC';
        $this->pagination['elements_per_page'] = 11;

        $filtered_elements = false;
        $this->setQFilter(['original_basename']) && ($filtered_elements = true);

        // Retrieve elements
        $pictures = $this->paginate();

        // Set view vars
        $this->set(compact('pictures'));
        $this->set('filtered_elements', $filtered_elements);
    }

    public function move()
    {
        if (!ACL\DbACL::canContentTypeAction('Picture', 'Update')) {
            $this->forbidden(!$this->isAjaxRequest());
        }

        $this->layout = 'json';
        $this->view = false;
        $this->resultForLayout['response']['succeeded'] = false;

        $move_id = post_var('move_id');
        $move_category_id = post_var('move_category_id');
        $picture_category_id = post_var('picture_category_id');

        $Model = null;

        if ($move_id) {
            /** @var Picture $Model **/
            $Model = MySQLModel::getInstance('Picture');
            $element = $Model->updateFields($move_id, ['picture_category_id' => $picture_category_id]);
        } else {
            /** @var PictureCategory $Model **/
            $Model = MySQLModel::getInstance('PictureCategory');
            $element = $Model->updateFields($move_category_id, ['picture_category_id' => $picture_category_id]);
        }

        if ($element) {
            // Set result
            $this->resultForLayout['response']['succeeded'] = true;
            $this->resultForLayout['response']['redirect_to'] = Router::url(['controller' => 'Pictures', 'action' => 'index', $element[$Model->name]['picture_category_id']]);
            $this->resultForLayout['response']['picture'] = [
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
     * Handles picture add request.
     */
    public function add($picture_category_id = 1)
    {
        if (!ACL\DbACL::canContentTypeAction('Picture', 'Create')) {
            $this->forbidden(!$this->isAjaxRequest());
        }

        $this->layout = 'json';
        $this->set('picture_category_id', $picture_category_id);

        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            if (post_var('move_id') || post_var('move_category_id')) {
                return $this->move();
            }

            // Prepare response
            $this->view = false;
            $this->resultForLayout['response']['succeeded'] = false;

            // Save picture
            $result = $this->saveUploadedPicture('picture', null, '', null, ($picture_category_id = post_var('picture_category_id')));

            if (!empty($result[0]['picture']['Picture']['id'])) {
                // Set result
                $this->resultForLayout['response']['succeeded'] = true;
                $this->resultForLayout['response']['redirect_to'] = Router::url(['controller' => 'Pictures', 'action' => 'index', $picture_category_id]);
                $this->resultForLayout['response']['picture'] = [
                    'id' => $result[0]['picture']['Picture']['id'],
                    'original_basename' => $result[0]['picture']['Picture']['original_basename'],
                ];
            } else {
                // Set errors
                $error_message = __('Unable to save picture.', true);

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
     * Handles picture edit request.
     */
    public function edit($picture_id = null)
    {
        if (!ACL\DbACL::canContentTypeAction('Picture', 'Update')) {
            $this->forbidden(!$this->isAjaxRequest());
        }

        $this->layout = 'json';

        /** @var Picture $Picture **/
        $Picture = MySQLModel::getInstance('Picture');
        $picture = $Picture->getById($picture_id, ['recursive' => -1]);

        if (!$picture) {
            $this->notFound(false);
        }

        $extension = explode('.', $picture['Picture']['original_basename']);
        $extension = $extension[sizeof($extension) - 1];
        $picture['Picture']['original_basename'] = substr($picture['Picture']['original_basename'], 0, -strlen($extension) - 1);
        $this->set('picture', $picture);

        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            // Prepare response
            $this->view = false;
            $this->resultForLayout['response']['succeeded'] = false;

            $pictureData = post_var('picture[Picture]');
            unset($pictureData['id']);

            if (!ends_with($pictureData['original_basename'], '.' . $extension)) {
                $pictureData['original_basename'] .= '.' . $extension;
            }

            if ($picture = $Picture->updateFields($picture_id, $pictureData)) {
                // Set result
                $this->resultForLayout['response']['succeeded'] = true;
                $this->resultForLayout['response']['redirect_to'] = Router::url(['controller' => 'Pictures', 'action' => 'index', $picture['Picture']['picture_category_id']]);
                $this->resultForLayout['response']['picture'] = [
                    'id' => $picture['Picture']['id']
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
     * Handles picture delete request.
     *
     * @param $id int
     */
    public function delete($id = null)
    {
        if (!ACL\DbACL::canContentTypeAction('Picture', 'Delete')) {
            $this->forbidden(!$this->isAjaxRequest());
        }

        if (!$this->isPostRequest()) {
            $this->forbidden(false);
        }

        // Prepare response
        $this->layout = 'json';
        $this->view = false;
        $this->resultForLayout['response']['succeeded'] = false;

        /** @var Picture $Picture */
        $Picture = MySQLModel::getInstance('Picture');

        // Check if and id was provided
        if (!$id) {
            $this->notFound();
        }

        $picture = $Picture->getById($id, ['recursive' => -1, 'fields' => ['id']]);

        // Check if there was any result
        if (empty($picture)) {
            $this->notFound();
        }

        // Try to delete
        if ($Picture->deleteById($id)) {
            $this->resultForLayout['response']['picture_id'] = $picture['Picture']['id'];
            $this->resultForLayout['response']['succeeded'] = true;
        } else {
            // Set error message
            $error_message = __('Unable to delete.', true);

            // Check for errors
            if ($Picture->errors) {
                // Add all retrieved messages translated
                foreach ($Picture->errors as $error) {
                    $error_message .= ' ' . $error;
                }
            }

            // Add retry message
            $error_message .= ' ' . __('Please try again.', true);
            $this->resultForLayout['error'] = $error_message;
        }
    }

    /**
     * Picture thumbs
     *
     * @param $size int
     * @param $id int
     * @param $token string
     */
    public function thumb($size, $id, $token)
    {
        if (!ACL\DbACL::canContentTypeAction('Picture', 'View')) {
            $this->forbidden(!$this->isAjaxRequest());
        }

        if (in_array($size, [
            320,
            640,
        ])) {
            // Extra memory for image processing
            ini_set('memory_limit', '512M');

            /** @var Picture $Picture */
            $Picture = MySQLModel::getInstance('Picture');
            $thumb_url = $Picture->getThumbUrl($id, $size, $token);

            // Redirect to it permanently
            if ($thumb_url) {
                $this->redirect($thumb_url, 301);
            }
        }

        // No output
        $this->notFound(false);
    }
}
