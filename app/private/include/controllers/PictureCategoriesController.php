<?php

/**
 * Class PictureCategoriesController.
 *
 * Handles a picture category related HTTP request.
 */
class PictureCategoriesController extends AppController
{
    /**
     * Controller name
     */
    public $name = 'PictureCategories';

    /**
     * @see Controller::$actionAccessLevel
     */
    protected $actionAccessLevel = [
        '*' => 0,
    ];

    /**
     * Handles picture category add request.
     */
    public function add($picture_category_id = 1)
    {
        if (!ACL\DbACL::canContentTypeAction('PictureCategory', 'Create')) {
            $this->forbidden(!$this->isAjaxRequest());
        }

        $this->layout = 'json';
        $this->set('picture_category_id', $picture_category_id);

        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            // Prepare response
            $this->view = false;
            $this->resultForLayout['response']['succeeded'] = false;

            /** @var PictureCategory $PictureCategory **/
            $PictureCategory = MySQLModel::getInstance('PictureCategory');
            $pictureCategoryData = post_var('pictureCategory[PictureCategory]');

            if ($pictureCategory = $PictureCategory->addNew($pictureCategoryData)) {
                // Set result
                $this->resultForLayout['response']['succeeded'] = true;
                $this->resultForLayout['response']['redirect_to'] = Router::url(['controller' => 'Pictures', 'action' => 'index', $pictureCategory['PictureCategory']['picture_category_id']]);
                $this->resultForLayout['response']['pictureCategory'] = [
                    'id' => $pictureCategory['PictureCategory']['id']
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
     * Handles picture category edit request.
     */
    public function edit($picture_category_id = null)
    {
        if (!ACL\DbACL::canContentTypeAction('PictureCategory', 'Update')) {
            $this->forbidden(!$this->isAjaxRequest());
        }

        $this->layout = 'json';

        /** @var PictureCategory $PictureCategory **/
        $PictureCategory = MySQLModel::getInstance('PictureCategory');
        $pictureCategory = $PictureCategory->getById($picture_category_id, ['recursive' => -1]);

        if (!$pictureCategory) {
            $this->notFound(false);
        }

        $this->set('pictureCategory', $pictureCategory);

        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            // Prepare response
            $this->view = false;
            $this->resultForLayout['response']['succeeded'] = false;

            $pictureCategoryData = post_var('pictureCategory[PictureCategory]');
            unset($pictureCategoryData['id']);

            if ($pictureCategory = $PictureCategory->updateFields($picture_category_id, $pictureCategoryData)) {
                // Set result
                $this->resultForLayout['response']['succeeded'] = true;
                $this->resultForLayout['response']['redirect_to'] = Router::url(['controller' => 'Pictures', 'action' => 'index', $pictureCategory['PictureCategory']['picture_category_id']]);
                $this->resultForLayout['response']['pictureCategory'] = [
                    'id' => $pictureCategory['PictureCategory']['id']
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
     * Handles picture category delete request.
     *
     * @param $id int
     */
    public function delete($id = null)
    {
        if (!ACL\DbACL::canContentTypeAction('PictureCategory', 'Delete')) {
            $this->forbidden(!$this->isAjaxRequest());
        }

        if (!$this->isPostRequest()) {
            $this->forbidden(false);
        }

        // Prepare response
        $this->layout = 'json';
        $this->view = false;
        $this->resultForLayout['response']['succeeded'] = false;

        /** @var PictureCategory $PictureCategory */
        $PictureCategory = MySQLModel::getInstance('PictureCategory');

        // Check if and id was provided
        if (!$id) {
            $this->notFound();
        }

        $pictureCategory = $PictureCategory->getById($id, ['recursive' => -1, 'fields' => ['id']]);

        // Check if there was any result
        if (empty($pictureCategory)) {
            $this->notFound();
        }

        // Try to delete
        if ($PictureCategory->deleteById($id)) {
            $this->resultForLayout['response']['picture_category_id'] = $pictureCategory['PictureCategory']['id'];
            $this->resultForLayout['response']['succeeded'] = true;
        } else {
            // Set error message
            $error_message = __('Unable to delete.', true);

            // Check for errors
            if ($PictureCategory->errors) {
                // Add all retrieved messages translated
                foreach ($PictureCategory->errors as $error) {
                    $error_message .= ' ' . $error;
                }
            }

            // Add retry message
            $error_message .= ' ' . __('Please try again.', true);
            $this->resultForLayout['error'] = $error_message;
        }
    }
}
