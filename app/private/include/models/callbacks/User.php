<?php

namespace App\Callback;

use Authentication;
use GestyMVC;
use MySQLModel, Picture;
use Router;
use UserTfaToken;

class User extends \GestyMVC\Model\Callback
{
    protected function registerEvents()
    {
        $this->registerEvent('beforeValidate', function (&$element, &$values, &$options) {
            if ($this->valueHasChanged('avatar_picture_id', $element, $values)) {
                /** @var Picture $Picture **/
                $Picture = MySQLModel::getRecycledInstance('Picture', [], $this->model);
                $values['avatar_url'] = $Picture->field('src', ['where' => ['id' => $this->currentValue('avatar_picture_id', $element, $values)]]);
            }

            if (!$this->currentValue('avatar_url', $element, $values)) {
                if (GestyMVC::config('use_gravatar_as_default_avatar_url')) {
                    $values['avatar_url'] = 'https://www.gravatar.com/avatar/' . md5(strtolower(trim($this->currentValue('email', $element, $values)))) . '.jpg?s=500';
                } else {
                    $values['avatar_url'] = Router::url(GestyMVC::config('default_avatar_url'));
                }
            }

            return true;
        });

        $this->registerEvent('afterSave', function ($created, $element, $values, $originalElement, $options) {
            if (
                !$created &&
                $this->valueHasChanged('otp_seed', $originalElement, $element[$this->name])
            ) {
                /** @var UserTfaToken $UserTfaToken **/
                $UserTfaToken = MySQLModel::getRecycledInstance('UserTfaToken', [], $this->model);
                $UserTfaToken->deleteAllByUserId($element[$this->name]['id']);
            }
        });

        $this->registerEvent('beforeGet', function (&$options) {
            if (
                Authentication::get('user', 'acl')
                && !Authentication::get('user', 'acl[is_full_access]')
            ) {
                if (!isset($options['where'])) {
                    $options['where'] = [];
                }

                if (!is_array($options['where'])) {
                    $options['where'] = [$options['where']];
                }

                /** @var AclProfile $AclProfile **/
                $AclProfile = MySQLModel::getRecycledInstance('AclProfile', [], $this);

                // Users without profile or with profiles visible by me
                $options['where'][] = [
                    'OR' => [
                        ['acl_profile_id' => null],
                        ['acl_profile_id' => array_values($AclProfile->getList('id', 'id'))]
                    ]
                ];
            }
        });
    }
}
