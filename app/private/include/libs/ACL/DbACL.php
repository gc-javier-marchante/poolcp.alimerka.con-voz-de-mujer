<?php

namespace ACL;

/**
 * Database Access Control List interface
 */
class DbACL
{
    /**
     * Instances
     *
     * @var array
     */
    private static $models = [];

    /**
     * Skipped permission validations
     *
     * @var array
     */
    private static $skippedValidations = [];

    /**
     * Model recycled
     *
     * @param string $model_name
     * @return \AppMySQLModel
     */
    private static function model($model_name)
    {
        if (empty(self::$models[$model_name])) {
            self::$models[$model_name] = \MySQLModel::getInstance($model_name);
        }

        return self::$models[$model_name];
    }

    /**
     * Translates content type action into permission
     *
     * @param string $class_name
     * @param string $name_short
     * @return int|null
     */
    private static function getContentTypeActionPermission($class_name, $name_short)
    {
        $acl_permission_id = null;

        /** @var ContentType $ContentType **/
        $ContentType = self::model('ContentType');
        $content_type_id = $ContentType->getIdForObjectClass($class_name);

        if ($content_type_id) {
            /** @var AclSection $AclSection **/
            $AclSection = self::model('AclSection');
            $acl_section_id = $AclSection->initializeForContentType($content_type_id);

            if ($acl_section_id) {
                /** @var AclPermission $AclPermission **/
                $AclPermission = self::model('AclPermission');
                $acl_permission_id = $AclPermission->field('id', ['where' => [
                    'acl_section_id' => $acl_section_id,
                    'name_short' => $name_short
                ]]);
            }
        }

        return $acl_permission_id;
    }

    /**
     * Handles section access permissions for the user based on content type
     *
     * @param string $class_name
     * @param string $name_short
     * @return bool
     */
    public static function canContentTypeAction($class_name, $name_short)
    {
        return self::can(self::getContentTypeActionPermission($class_name, $name_short));
    }

    /**
     * Skips a validation for the user on current request based on content type
     *
     * @param string $class_name
     * @param string $name_short
     * @param bool $can
     * @return void
     */
    public static function skipContentTypeActionValidation($class_name, $name_short, $can = true)
    {
        return self::skipValidation(self::getContentTypeActionPermission($class_name, $name_short), $can);
    }

    /**
     * Skips a validation for the user on current request
     *
     * @param int acl_permission_id
     * @param bool $can
     * @return void
     */
    public static function skipValidation($acl_permission_id, $can = true)
    {
        if ($acl_permission_id) {
            self::$skippedValidations[$acl_permission_id] = $can;
        }
    }

    /**
     * Handles section access permissions for the user
     *
     * @param $acl_permission_id
     * @return bool
     */
    public static function can($acl_permission_id)
    {
        if (is_bool($acl_permission_id)) {
            return $acl_permission_id;
        }

        if (is_string($acl_permission_id) && !is_numeric($acl_permission_id)) {
            if (defined($acl_permission_id) && is_int(constant($acl_permission_id))) {
                $acl_permission_id = constant($acl_permission_id);
            } elseif (strpos($acl_permission_id, '/') !== false) {
                $permissionIds = explode('/', $acl_permission_id);

                foreach ($permissionIds as $acl_permission_id) {
                    if (self::can($acl_permission_id)) {
                        return true;
                    }
                }

                return false;
            } elseif (strpos($acl_permission_id, '#') !== false) {
                $contentTypeAction = explode('#', $acl_permission_id);

                return self::canContentTypeAction($contentTypeAction[0], $contentTypeAction[1]);
            }
        }

        if (!$acl_permission_id) {
            return false;
        }

        $acl_permission_id = intval($acl_permission_id);
        $can = false;

        if (isset(self::$skippedValidations[$acl_permission_id])) {
            return self::$skippedValidations[$acl_permission_id];
        }

        if ($acl_permission_id) {
            $aclPermissionIds = \Authentication::get('user', 'acl[aclPermissionIds]');

            if (!$aclPermissionIds) {
                $aclPermissionIds = [];
            }

            if (in_array(intval($acl_permission_id), $aclPermissionIds)) {
                $can = true;
            }
        }

        if (
            !$can
            && \Authentication::get('user', 'acl[is_full_access]')
        ) {
            return self::model('AclPermission')->count(['where' => ['id' => $acl_permission_id]]) > 0;
        }

        return $can;
    }

    /**
     * Lists all users matching permission
     *
     * @param int $acl_permission_id
     *
     * @return array
     */
    public static function getUserIdsWithAccess($acl_permission_id)
    {
        /** @var \AclCacheUserPermission $AclCacheUserPermission **/
        $AclCacheUserPermission = self::model('AclCacheUserPermission');
        $where = ['acl_permission_id' => $acl_permission_id];

        return array_keys($AclCacheUserPermission->getAll([
            'where' => $where,
            'fields' => ['user_id'],
            'recursive' => -1,
            'hash_by' => 'user_id',
        ]));
    }

    /**
     * @param $user_id
     * @return array
     */
    public static function getUserAcl($user_id)
    {
        /** @var User $User **/
        $User = self::model('User');
        $user = $User->get(['where' => ['id' => $user_id], 'fields' => ['acl_profile_id'], 'recursive' => -1, 'ignore_hidden' => 1]);
        $acl_profile_id = (!empty($user) ? $user['User']['acl_profile_id'] : null);

        // Default permissions
        $acl = [
            'checked_at_time' => time(),
            'aclPermissionIds' => [],
            'acl_profile_id' => $acl_profile_id,
            'is_full_access' => !!self::model('AclProfile')->field('is_full_access', ['where' => ['id' => $acl_profile_id]]),
        ];

        // Skip empty acl profile
        if (!$acl['acl_profile_id']) {
            return $acl;
        }

        /** @var AclProfilePermission $AclProfilePermission **/
        $AclProfilePermission = self::model('AclProfilePermission');
        $aclPermissionIds = array_keys($AclProfilePermission->getAllByAclProfileId($acl['acl_profile_id'], [
            'recursive' => -1,
            'fields' => ['acl_permission_id'],
            'hash_by' => 'acl_permission_id',
        ]));

        /** @var AclPermission $AclPermission **/
        $AclPermission = self::model('AclPermission');
        $aclPermissions = $AclPermission->getAnyById($aclPermissionIds, [
            'list_by' => 'is_full_access',
            'fields' => ['id', 'is_full_access', 'acl_section_id'],
            'recursive' => -1,
        ]);

        // Add all the permissions of the section for each "full access" permission
        if (isset($aclPermissions[1])) {
            if (!isset($aclPermissions[0])) {
                $aclPermissions[0] = [];
            }

            foreach ($aclPermissions[1] as $aclPermission) {
                $aclPermissions[0] = array_merge($aclPermissions[0], $AclPermission->getAllByAclSectionId($aclPermission['AclPermission']['acl_section_id'], [
                    'where' => [
                        'is_full_access' => 0,
                    ],
                    'fields' => ['id'],
                    'recursive' => -1,
                ]));
            }
        }

        // Add all simple permissions to the array
        if (isset($aclPermissions[0])) {
            foreach ($aclPermissions[0] as $aclPermission) {
                $acl['aclPermissionIds'][] = intval($aclPermission['AclPermission']['id']);
            }
        }

        // Cleanup
        $acl['aclPermissionIds'] = array_unique($acl['aclPermissionIds']);

        return $acl;
    }
}
