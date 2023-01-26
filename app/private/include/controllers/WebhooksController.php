<?php
class WebhooksController extends CRUDController
{
    /**
     * Load model
     *
     * @param bool $recycled
     * @return AppMySQLModel
     */
    protected function model($recycled = true)
    {
        // Get default model
        $model = parent::model($recycled);

        if (empty($model->getConfig()['fields']['models']['list']['values'])) {
            // List valid models from structure
            $model_classes = array_diff(
                array_filter(array_map(function ($file) {
                    if (ends_with($file, '.php')) {
                        return substr($file, 0, -4);
                    }

                    return null;
                }, array_diff(scandir(MODELS_PATH . 'structure'), ['.', '..']))),
                [
                    // Exclusions
                    'AclCacheUserPermission',
                    'AclPermission',
                    'AclProfile',
                    'AclProfilePermission',
                    'AclSection',
                    'ContentType',
                    'DbMigration',
                    'File',
                    'FileCategory',
                    'Log',
                    'Picture',
                    'PictureCategory',
                    'QueuedTask',
                    'UserLogin',
                    'UserPasswordHistory',
                    'UserTfaToken',
                    'Webhook',
                ]
            );

            // Translate model names
            $model_names = array_map(function ($str) {
                return __(Inflector::lowercaseName(Inflector::snakeCase($str, ' ')), true);
            }, $model_classes);

            // Set options for input
            $modelOptions = array_combine($model_classes, $model_names);
            asort($modelOptions);
            $model->getConfig()['fields']['models']['list']['values'] = $modelOptions;
        }

        return $model;
    }

    /**
     * Before render callback. Called before the action's view is called.
     *
     * @return bool if not true stops execution
     */
    protected function beforeRender()
    {
        if (in_array($this->crud_action, ['update'])) {
            if (!empty($this->viewVars['webhook']['Webhook']['actions'])) {
                $this->viewVars['webhook']['Webhook']['actions'] = json_decode($this->viewVars['webhook']['Webhook']['actions'], true);
            }

            if (!empty($this->viewVars['webhook']['Webhook']['models'])) {
                $this->viewVars['webhook']['Webhook']['models'] = json_decode($this->viewVars['webhook']['Webhook']['models'], true);
            }
        }

        return parent::beforeRender();
    }

    /**
     * Send webhook
     * @param int $id
     */
    public function send($id = null)
    {
        if (!ACL\DbACL::canContentTypeAction($this->model_name, 'Send')) {
            $this->forbidden(!$this->isAjaxRequest());
        }

        $element = $this->model()->getById($id, ['recursive' => -1]);

        if (!$element) {
            $this->notFound(!$this->isAjaxRequest());
        }

        if (
            $this->isAjaxRequest()
            && $this->isPostRequest()
        ) {
            // Prepare response
            $this->layout = 'json';
            $this->view = false;
            $this->resultForLayout['response']['succeeded'] = false;

            // Send webhook
            if ($this->model()->send(
                $id,
                post_var('webhookSend[WebhookSend][action]'),
                post_var('webhookSend[WebhookSend][model_name]'),
                post_var('webhookSend[WebhookSend][model_id]'),
            )) {
                // Set result
                $this->resultForLayout['response']['succeeded'] = true;
                $this->resultForLayout['response']['message'] = __('Webhook sent.', true);

                if (
                    ACL\DbACL::canContentTypeAction($this->model_name, 'View')
                    && get_var('from_view')
                ) {
                    $this->resultForLayout['response']['redirect_to'] = Router::url(['controller' => $this->name, 'action' => 'view', $id]);
                } else {
                    $this->resultForLayout['response']['redirect_to'] = Router::url(['controller' => $this->name, 'action' => 'index']);
                }

                $this->resultForLayout['response']['id'] = $id;
            } else {
                // Set errors
                $error_message = __('Unable to send.', true);

                // Add retry message
                $error_message .= ' ' . __('Please try again.', true);
                $this->resultForLayout['error'] = $error_message;
                $this->resultForLayout['errorsByField'] = ['WebhookSend' => $this->model()->errorsByField];
            }
        } else {
            $this->set([
                'is_create' => true,
                'discard_to_url' => (get_var('from_view') ? ['action' => 'view', $id] : ['action' => 'index']),
                'breadcrumbs' => [
                    'title' => sprintf(__('Send %s', true), __(Inflector::lowercaseName(Inflector::snakeCase($this->model_name, ' ')), true)),
                    'title_short' => __('Send', true),
                    'items' => [[
                        'title' => __(Inflector::lowercaseName(Inflector::pluralize(Inflector::snakeCase($this->model_name, ' '))), true),
                        'href' => ['controller' => $this->name, 'action' => 'index'],
                    ], [
                        'title' => __('Admin', true),
                        'href' => null,
                    ]],
                ]
            ]);

            // Set fields
            $fields = [
                'action' => [
                    'type' => 'select',
                    'name' => 'webhookSend[WebhookSend][action]',
                    'label' => __('Action', true),
                    'required' => true,
                    'list' => [
                        'values' => [],
                    ],
                ],
                'model_name' => [
                    'type' => 'select',
                    'name' => 'webhookSend[WebhookSend][model_name]',
                    'label' => __('Model', true),
                    'required' => true,
                    'list' => [
                        'values' => [],
                    ],
                ],
                'model_id' => [
                    'type' => 'number',
                    'name' => 'webhookSend[WebhookSend][model_id]',
                    'label' => __('Record ID', true),
                    'required' => true,
                ],
            ];

            // Add webhook selected actions from available
            foreach (json_decode($element[$this->model_name]['actions']) as $action) {
                $fields['action']['list']['values'][$action] = $this->model()->getConfig()['fields']['actions']['list']['values'][$action];
            }

            // Add webhook selected models from available
            foreach (json_decode($element[$this->model_name]['models']) as $model_name) {
                $fields['model_name']['list']['values'][$model_name] = $this->model()->getConfig()['fields']['models']['list']['values'][$model_name];
            }

            // View
            $this->view = 'elements/form/full-width-form';
            $this->set('fields', $fields);
        }
    }

    /**
     * Overridable actions for detail
     *
     * @param array|object $element
     * @return array
     */
    protected function detailActions($element)
    {
        // Default actions
        $actions = parent::detailActions($element);

        // Add send action
        $actions['self-send'] = [
            'active' => ACL\DbACL::canContentTypeAction($this->model_name, 'Send'),
            'url' => ['controller' => $this->name, 'action' => 'send', $element[$this->model_name]['id'], 'from_view' => 1],
            'attributes' => '',
            'label' => __('Send manually', true),
        ];

        // Remove to add at the end
        if (!empty($actions['self-delete'])) {
            $delete = $actions['self-delete'];
            unset($actions['self-delete']);
            $actions['self-delete'] = $delete;
        }

        return $actions;
    }

    /**
     * Overridable actions for item
     *
     * @return array
     */
    protected function indexItemActions()
    {
        // Default actions
        $actions = parent::indexItemActions();

        // Add send action
        $actions['item-send'] = [
            'active' => ACL\DbACL::canContentTypeAction($this->model_name, 'Send'),
            'url' => ['controller' => $this->name, 'action' => 'send', '%id%'],
            'attributes' => '',
            'label' => __('Send manually', true),
        ];

        // Remove to add at the end
        if (!empty($actions['item-delete'])) {
            $delete = $actions['item-delete'];
            unset($actions['item-delete']);
            $actions['item-delete'] = $delete;
        }

        return $actions;
    }
}
