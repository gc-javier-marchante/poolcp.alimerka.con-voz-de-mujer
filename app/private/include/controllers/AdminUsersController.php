<?php
class AdminUsersController extends CRUDController
{
    /**
     * Add custom actions for detail view
     *
     * @param array $element
     * @return array
     */
    protected function detailActions($element)
    {
        $actions = parent::detailActions($element);

        if ($element[$this->model_name]['otp_seed']) {
            $actions['self-reset-tfa'] = [
                'active' => ACL\DbACL::canContentTypeAction($this->model_name, 'ResetTFA'),
                'attributes' => 'data-gc-on-ajax-reload data-gc-confirm-action="' . Router::url(['controller' => $this->name, 'action' => 'resetTfa', $element[$this->model_name]['id']]) . '"',
                'url' => 'javascript:void(0)',
                'confirm' => sprintf(__('Are you sure you want to reset this users two factor autentication configuration?', true), '%id%'),
                'label' => __('Reset TFA', true),
            ];
        }

        $actions['self-change-password'] = [
            'active' => ACL\DbACL::canContentTypeAction($this->model_name, 'ChangePassword'),
            'url' => Router::url(['controller' => $this->name, 'action' => 'changePassword', $element[$this->model_name]['id']]),
            'label' => __('Change password', true),
        ];

        return $actions;
    }

    /**
     * Overridable actions for edit
     *
     * @param array|object $element
     * @return array
     */
    protected function editActions($element)
    {
        $actions = parent::editActions($element);

        if ($this->action != 'edit') {
            unset($actions['self-exit-edit']);
        }

        return $actions;
    }

    /**
     * Reset TFA
     * 
     * @param int $id
     */
    public function resetTfa($id = null)
    {
        if (
            !$this->isAjaxRequest()
            || !ACL\DbACL::canContentTypeAction($this->model_name, 'ResetTFA')
        ) {
            $this->forbidden(!$this->isAjaxRequest());
        }

        $element = $this->model()->getById($id, ['recursive' => -1, 'fields' => ['id']]);

        if (!$element) {
            $this->notFound(!$this->isAjaxRequest());
        }

        // Prepare response
        $this->layout = 'json';
        $this->view = false;
        $this->resultForLayout['response']['succeeded'] = false;

        if ($element = $this->model()->updateFields($id, ['otp_seed' => null])) {
            // Set result
            $this->resultForLayout['response']['succeeded'] = true;
            $this->resultForLayout['response']['message'] = __('TFA has been reset.', true);

            if (ACL\DbACL::canContentTypeAction($this->model_name, 'View')) {
                $this->resultForLayout['response']['redirect_to'] = Router::url(['controller' => $this->name, 'action' => 'view', $element[$this->model_name]['id']]);
            } else {
                $this->resultForLayout['response']['redirect_to'] = Router::url(['controller' => $this->name, 'action' => 'index']);
            }

            $this->resultForLayout['response']['id'] = $element[$this->model_name]['id'];
        } else {
            // Set errors
            $error_message = __('Unable to reset TFA.', true);

            // Add retry message
            $error_message .= ' ' . __('Please try again.', true);
            $this->resultForLayout['error'] = $error_message;
            $modelErrorsByField[$this->model_name] = $this->model()->errorsByField;
            $this->resultForLayout['errorsByField'] = $modelErrorsByField;
        }
    }

    /**
     * Change password
     * 
     * @param int $id
     */
    public function changePassword($id = null)
    {
        $this->customEdit(
            $id,
            [
                'password',
                'repeat_password'
            ],
            'ChangePassword',
            'Change Password',
            'Password'
        );
    }
}
