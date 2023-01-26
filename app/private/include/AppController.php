<?php

use Symfony\Component\Yaml\Yaml;

/**
 * App specific controller class.
 * Current app controllers inherit this class instead of the original one. All specific modifications to the controller
 * class must be done to this one instead.
 */
class AppController extends Controller
{
    /**
     * Before filter callback. Called before the action is called.
     * Checks access.
     *
     * @return bool if not true stops execution
     */
    protected function beforeFilter()
    {
        if (!parent::beforeFilter()) {
            return false;
        }

        // Update the session user ACL
        $this->updateUserAcl();

        // After ACL validation, if user has no profile but is logged
        // redirect to "access denied" page
        if (
            Authentication::get('user', 'id')
            && !Authentication::get('user', 'acl[acl_profile_id]')
        ) {
            if ($this->name != 'Errors' && !in_array($this->name . '/' . $this->action, [
                'Users/logout',
            ])) {
                Session::destroy();
                $this->forbidden();
            }
        }

        return true;
    }

    /**
     * Before render callback. Called before the action's view is called.
     *
     * @return bool if not true stops execution
     */
    protected function beforeRender()
    {
        // Disable SEO for non-html results
        if (in_array($this->layout, [
            'json',
            'excel',
        ])) {
            $this->seo = false;
        }

        // Load generic info data
        if (in_array($this->layout, [
            'admin',
            'no-header',
            'modal',
        ])) {
            $this->setDefaultTwigVars();
        } elseif (in_array($this->layout, [
            'default',
        ])) {
            $this->setDefaultPublicTwigVars();
        }

        // Load menu
        if (in_array($this->layout, [
            'admin',
        ])) {
            $this->loadUserLevelTranslations();
            $this->setAdminMenu();
        }

        // Check redirection
        $this->redirectToFirstAvailableAdminMenuOption();

        // Delegate aborting on the parent call
        return parent::beforeRender();
    }

    /**
     * After render callback. Called after the action's view and layout are called.
     *
     * @param $full_content string generated view content with layout
     * @param $only_view_content string generated view content without layout
     *
     * @return bool if not true stops execution
     */
    protected function afterRender(&$full_content, $only_view_content)
    {
        if (in_array($this->layout, ['json', 'modal'])) {
            // Add correct content type header
            header('Content-Type: application/json');

            // Add full content from view
            if ($full_content) {
                $this->resultForLayout['response']['html'] = $full_content;
            }

            // Serialize
            $full_content = json_encode($this->resultForLayout);
        } else if ($this->layout == 'excel') {
            // Set default file name
            if (empty($this->viewVars['filename_for_layout'])) {
                $this->viewVars['filename_for_layout'] = __('Downloaded file', true) . '.xls';
            }

            // Content type header
            header('Content-Type: application/force-download');
            header('Content-Length: ' . strlen($full_content));
            header('Content-Disposition: attachment; filename=' . $this->viewVars['filename_for_layout']);
        }

        return parent::afterRender($full_content, $only_view_content);
    }

    /**
     * Admin menu
     *
     * @return void
     */
    private function setAdminMenu()
    {
        $adminMenu = Yaml::parseFile(CONFIG_PATH . 'menu.yml');
        $aclAdminMenu = [];

        if (!empty($this->viewVars['active_menu_href'])) {
            $this->viewVars['active_menu_href'] = Router::url($this->viewVars['active_menu_href']);
        }

        foreach ($adminMenu as &$adminMenuItem) {
            if (!isset($adminMenuItem['acl'])) {
                $adminMenuItem['acl'] = true;
            }

            if (
                !empty($adminMenuItem['title'])
                && starts_with($adminMenuItem['title'], 'i18n#')
            ) {
                $adminMenuItem['title'] = __(substr($adminMenuItem['title'], 5), true);
            }

            if (!empty($adminMenuItem['items'])) {
                $adminMenuItem['acl'] = false;
                $aclAdminMenuSubitems = [];

                foreach ($adminMenuItem['items'] as &$adminMenuSubitem) {
                    if (!isset($adminMenuSubitem['acl'])) {
                        $adminMenuSubitem['acl'] = true;
                    }

                    if ($adminMenuSubitem['acl'] = ACL\DbACL::can($adminMenuSubitem['acl'])) {
                        $aclAdminMenuSubitems[] = &$adminMenuSubitem;
                        $adminMenuItem['acl'] = true;
                    }

                    if (
                        !empty($adminMenuSubitem['title'])
                        && starts_with($adminMenuSubitem['title'], 'i18n#')
                    ) {
                        $adminMenuSubitem['title'] = __(substr($adminMenuSubitem['title'], 5), true);
                    }

                    if (!empty($adminMenuSubitem['href'])) {
                        if (!empty($this->viewVars['active_menu_href'])) {
                            if (Router::url($adminMenuSubitem['href']) == $this->viewVars['active_menu_href']) {
                                $adminMenuSubitem['active'] = true;
                                $adminMenuItem['active'] = true;
                            }
                        } elseif (
                            $adminMenuSubitem['href']['controller'] == Dispatcher::currentControllerName()
                            && $adminMenuSubitem['href']['action'] == Dispatcher::currentActionName()
                        ) {
                            $adminMenuSubitem['active'] = true;
                            $adminMenuItem['active'] = true;
                        }
                    }
                }

                $adminMenuItem['items'] = $aclAdminMenuSubitems;

                if (sizeof($adminMenuItem['items']) === 1) {
                    $adminMenuItem['title'] = $adminMenuItem['items'][0]['title'];
                    $adminMenuItem['href'] = $adminMenuItem['items'][0]['href'];
                    unset($adminMenuItem['items']);
                }
            } else if (!empty($adminMenuItem['href'])) {
                if (!empty($this->viewVars['active_menu_href'])) {
                    if (Router::url($adminMenuItem['href']) == $this->viewVars['active_menu_href']) {
                        $adminMenuItem['active'] = true;
                    }
                } elseif (
                    $adminMenuItem['href']['controller'] == Dispatcher::currentControllerName()
                    && $adminMenuItem['href']['action'] == Dispatcher::currentActionName()
                ) {
                    $adminMenuItem['active'] = true;
                }
            }

            $adminMenuItem['acl'] = ACL\DbACL::can($adminMenuItem['acl']);

            if ($adminMenuItem['acl']) {
                $aclAdminMenu[] = &$adminMenuItem;
            }
        }

        $this->set('adminMenu', $aclAdminMenu);
    }

    /**
     * Admin menu
     *
     * @return void
     */
    private function redirectToFirstAvailableAdminMenuOption()
    {
        if (
            !empty($this->viewVars['redirect_to_first_available_action'])
            && !empty($this->viewVars['adminMenu'])
        ) {
            foreach ($this->viewVars['adminMenu'] as $menu) {
                if (empty($menu['items'])) {
                    if (
                        !empty($menu['acl'])
                        && !empty($menu['href'])
                    ) {
                        $this->redirect($menu['href']);
                    }
                } else {
                    foreach ($menu['items'] as $menuItem) {
                        if (
                            !empty($menuItem['acl'])
                            && !empty($menuItem['href'])
                        ) {
                            $this->redirect($menuItem['href']);
                        }
                    }
                }
            }
        }
    }

    /**
     * Twig vars
     *
     * @return void
     */
    private function setDefaultPublicTwigVars()
    {
        $this->set('lang', LANG);
        $this->set('root', ROOT_URL);
        $this->set('localed_root', LOCALED_ROOT_URL);
        $this->set('website_name', GestyMVC::config('website_name'));
        $this->set('random_key', random_token(false, 20, 30));
        $this->set('show_cookie_alert', false);
        $this->set('fixed_header', false);
    }

    /**
     * Twig vars
     *
     * @return void
     */
    private function setDefaultTwigVars()
    {
        $this->set('lang', LANG);
        $this->set('root', ROOT_URL);
        $this->set('localed_root', LOCALED_ROOT_URL);
        $this->set('website_name', GestyMVC::config('website_name'));
        $this->set('currentUser', Authentication::get('user'));
        $this->set('random_key', random_token(false, 20, 30));
        $this->set('can_user_settings', ACL\DbACL::canContentTypeAction('User', 'Settings'));
        $this->set('reflective_root', GestyMVC::config('reflective_root_url'));

        $this->set('unread_notifications', 1);
        $this->set('notifications', [
            [
                'Notification' => [
                    'title' => 'Developer Library added',
                    'icon' => '<svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" width="24px" height="24px" viewbox="0 0 24 24" version="1.1"><g stroke="none" stroke-width="1" fill="none" fill-rule="evenodd"><rect x="0" y="0" width="24" height="24"/><path d="M5,3 L6,3 C6.55228475,3 7,3.44771525 7,4 L7,20 C7,20.5522847 6.55228475,21 6,21 L5,21 C4.44771525,21 4,20.5522847 4,20 L4,4 C4,3.44771525 4.44771525,3 5,3 Z M10,3 L11,3 C11.5522847,3 12,3.44771525 12,4 L12,20 C12,20.5522847 11.5522847,21 11,21 L10,21 C9.44771525,21 9,20.5522847 9,20 L9,4 C9,3.44771525 9.44771525,3 10,3 Z" fill="#000000"/><rect fill="#000000" opacity="0.3" transform="translate(17.825568, 11.945519) rotate(-19.000000) translate(-17.825568, -11.945519)" x="16.3255682" y="2.94551858" width="3" height="18" rx="1"/></g></svg>',
                    'created' => now()
                ]
            ]
        ]);

        if (
            !empty($this->viewVars['breadcrumbs']['title'])
            && empty($this->viewVars['seo_title'])
        ) {
            $title = [];
            $title[] = $this->viewVars['breadcrumbs']['title'];

            if (!empty($this->viewVars['breadcrumbs']['items'][0]['title'])) {
                $title[] = $this->viewVars['breadcrumbs']['items'][0]['title'];
            }

            $title[] = GestyMVC::config('website_name');

            $this->set('seo_title', implode(' | ', $title));
        }
    }

    /**
     * User levels are not on the database. Set the vars so they can be used.
     *
     * @return void
     */
    private function loadUserLevelTranslations()
    {
        $this->set('userLevels', [
            __('User', true),
            __('Advanced User', true),
            __('Admin', true),
            __('Super Admin', true),
        ]);
    }

    /**
     * Save uploaded pictures
     *
     * @param string $index
     * @param int $max
     * @param string $alt
     * @param string $scale
     * @param int $picture_category_id
     * @return array
     */
    protected function saveUploadedPicture($index, $max = 1, $alt = '', $scale = null, $picture_category_id = 1)
    {
        $result = [];

        /** @var Picture $Picture **/
        $Picture = MySQLModel::getInstance('Picture');
        $count = 0;

        foreach (files_var($index) as $fileData) {
            if ($max && ++$count > $max) break;

            $Picture->errors = [];
            $picture = $Picture->saveFile($alt, $fileData, $scale, $picture_category_id);
            $result[] = [
                'picture' => $picture,
                'no_file' => $fileData['error'] == UPLOAD_ERR_NO_FILE,
                'errors' => $Picture->errors,
            ];
        }

        return $result;
    }

    /**
     * Save uploaded files
     *
     * @param string $index
     * @param int $max
     * @param string $alt
     * @param int $file_category_id
     * @return array
     */
    protected function saveUploadedFile($index, $max = 1, $alt = '', $file_category_id = null)
    {
        $result = [];

        if (!$file_category_id) {
            $file_category_id = 1;
        }

        /** @var File $File **/
        $File = MySQLModel::getInstance('File');
        $count = 0;

        foreach (files_var($index) as $fileData) {
            if ($max && ++$count > $max) break;

            $File->errors = [];
            $file = $File->saveFile($alt, $fileData);
            $result[] = [
                'file' => $file,
                'no_file' => $fileData['error'] == UPLOAD_ERR_NO_FILE,
                'errors' => $File->errors,
            ];

            if ($file) {
                $File->updateFields($file['File']['id'], ['file_category_id' => $file_category_id]);
            }
        }

        return $result;
    }

    /**
     * Sets the Q filter for pagination
     *
     * @param $fields
     * @param $param
     * @return bool
     */
    protected function setQFilter($fields, $param = 'q')
    {
        if (
            empty($fields)
            || !trim(get_var($param))
        ) {
            return false;
        }

        $queryConditions = [];
        $words = explode(' ', trim(get_var($param)));

        foreach ($words as $word) {
            if (!empty($word)) {
                $wordOrConditions = [];

                foreach ($fields as $field) {
                    $wordOrConditions[$field . ' LIKE'] = '%' . $word . '%';
                }

                $queryConditions[] = [
                    'OR' => $wordOrConditions,
                ];
            }
        }

        $this->pagination['where'][] = $queryConditions;

        return true;
    }

    /**
     * Sets the exact value filter for pagination
     *
     * @param $field
     * @param $allow_empty
     * @param $condition
     * @return bool
     */
    protected function setExactValueFilter($field, $allow_empty = false, $condition = null)
    {
        $field_get_name = str_replace('`', '', $field);
        $field_get_name = explode('.', $field_get_name);
        $field_get_name = end($field_get_name);

        if (!array_key_exists($field_get_name, $_GET)) {
            return false;
        }

        $field_get_value = trim(get_var($field_get_name));

        if (strlen($field_get_value) == 0 && !$allow_empty) {
            return false;
        }

        if (!$condition) {
            $this->pagination['where'][] = [$field => $field_get_value];
        } else {
            $this->pagination['where'][] = str_replace('%value%', '\'' . sql_escape($field_get_value) . '\'', $condition);
        }

        return true;
    }

    /**
     * Sets the range value filter for pagination
     *
     * @param string $field parameter field name
     * @param string $field_db_name database field name
     * @return bool
     */
    protected function setMinDateFilter($field, $field_db_name = null)
    {
        if (empty($field_db_name)) {
            $field_get_name = str_replace('`', '', $field);
            $field_get_name = explode('.', $field_get_name);
            $field_get_name = end($field_get_name);
        } else {
            $field_get_name = $field;
            $field = $field_db_name;
        }

        $field_get_value = get_var($field_get_name);

        if (!$field_get_value) {
            return false;
        }

        $this->pagination['where'][] = [$field  . ' >=' => date('Y-m-d 00:00:00', strtotime($field_get_value))];

        return true;
    }

    /**
     * Sets the range value filter for pagination
     *
     * @param string $field parameter field name
     * @param string $field_db_name database field name
     * @return bool
     */
    protected function setMaxDateFilter($field, $field_db_name = null)
    {
        if (empty($field_db_name)) {
            $field_get_name = str_replace('`', '', $field);
            $field_get_name = explode('.', $field_get_name);
            $field_get_name = end($field_get_name);
        } else {
            $field_get_name = $field;
            $field = $field_db_name;
        }

        $field_get_value = get_var($field_get_name);

        if (!$field_get_value) {
            return false;
        }

        $this->pagination['where'][] = [$field_db_name  . ' <=' => date('Y-m-d 23:59:59', strtotime($field_get_value))];

        return true;
    }

    /**
     * Sets the exact value filter for pagination
     *
     * @param $field
     * @param $allow_empty
     * @param $condition
     * @return bool
     */
    protected function setAnyValueFilter($field, $allow_empty = false, $condition = null)
    {
        $field_get_name = str_replace('`', '', $field);
        $field_get_name = explode('.', $field_get_name);
        $field_get_name = end($field_get_name);

        if (!array_key_exists($field_get_name, $_GET)) {
            return false;
        }

        $field_get_value = get_var($field_get_name);

        if (!is_array($field_get_value)) {
            if (strlen($field_get_value)) {
                $field_get_value = [$field_get_value];
            } else {
                $field_get_value = [];
            }
        }

        if (sizeof($field_get_value) == 0 && !$allow_empty) {
            return false;
        }

        if (!$condition) {
            $this->pagination['where'][] = [$field => $field_get_value];
        } else {
            $escapedValues = [];

            foreach ($field_get_value as $value) {
                $escapedValues[] = '\'' . sql_escape($value) . '\'';
            }

            $this->pagination['where'][] = str_replace('%values%', implode(',', $escapedValues), $condition);
        }

        return true;
    }

    /**
     * Updates the session user ACL
     *
     * @param bool $force
     */
    protected function updateUserAcl($force = false)
    {
        // If no user, nothing to do
        if (!Authentication::get('user', 'id')) {
            return;
        }

        // Retrieve current user ACL
        $userAcl = Authentication::get('user', 'acl');

        // Reload ACL every 5 minutes for users with access
        if ($force || ($userAcl && (!$userAcl['checked_at_time'] || $userAcl['checked_at_time'] < time() - 60 * 5))) {
            Authentication::set('user', 'acl', ACL\DbACL::getUserAcl(Authentication::get('user', 'id')));
        }
    }
}
