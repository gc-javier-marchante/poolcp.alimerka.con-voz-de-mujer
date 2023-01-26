<?php

/**
 * Class AclProfile
 *
 * Model class for table acl_profiles
 *
 * <MAGIC_METHODS>
 * @method array|object getById(integer $id, array $options = [])
 * @method array getAllById(integer $id, array $options = [])
 * @method array getAnyById(array $listOfIds, array $options = [])
 * @method bool deleteAllById(integer $id, array $getOptions = [], array $deleteOptions = []))
 * @method bool deleteAnyById(array $listOfIds, array $getOptions = [], array $deleteOptions = []))
 * @method array updateById(integer $id, array $getOptions = [], array $updateOptions = [])
 * @method array updateAllById(integer $id, array $getOptions = [], array $updateOptions = [])
 * @method array updateAnyById(array $listOfIds, array $getOptions = [], array $updateOptions = [])
 * @method array|object getByName(string $name, array $options = [])
 * @method array getAllByName(string $name, array $options = [])
 * @method array getAnyByName(array $listOfNames, array $options = [])
 * @method bool deleteByName(string $name, array $getOptions = [], array $deleteOptions = []))
 * @method bool deleteAllByName(string $name, array $getOptions = [], array $deleteOptions = []))
 * @method bool deleteAnyByName(array $listOfNames, array $getOptions = [], array $deleteOptions = []))
 * @method array updateByName(string $name, array $getOptions = [], array $updateOptions = [])
 * @method array updateAllByName(string $name, array $getOptions = [], array $updateOptions = [])
 * @method array updateAnyByName(array $listOfNames, array $getOptions = [], array $updateOptions = [])
 * @method array|object getByCreatedByUserId(integer $created_by_user_id, array $options = [])
 * @method array getAllByCreatedByUserId(integer $created_by_user_id, array $options = [])
 * @method array getAnyByCreatedByUserId(array $listOfCreatedByUserIds, array $options = [])
 * @method bool deleteByCreatedByUserId(integer $created_by_user_id, array $getOptions = [], array $deleteOptions = []))
 * @method bool deleteAllByCreatedByUserId(integer $created_by_user_id, array $getOptions = [], array $deleteOptions = []))
 * @method bool deleteAnyByCreatedByUserId(array $listOfCreatedByUserIds, array $getOptions = [], array $deleteOptions = []))
 * @method array updateByCreatedByUserId(integer $created_by_user_id, array $getOptions = [], array $updateOptions = [])
 * @method array updateAllByCreatedByUserId(integer $created_by_user_id, array $getOptions = [], array $updateOptions = [])
 * @method array updateAnyByCreatedByUserId(array $listOfCreatedByUserIds, array $getOptions = [], array $updateOptions = [])
 * @method array|object getByModifiedByUserId(integer $modified_by_user_id, array $options = [])
 * @method array getAllByModifiedByUserId(integer $modified_by_user_id, array $options = [])
 * @method array getAnyByModifiedByUserId(array $listOfModifiedByUserIds, array $options = [])
 * @method bool deleteByModifiedByUserId(integer $modified_by_user_id, array $getOptions = [], array $deleteOptions = []))
 * @method bool deleteAllByModifiedByUserId(integer $modified_by_user_id, array $getOptions = [], array $deleteOptions = []))
 * @method bool deleteAnyByModifiedByUserId(array $listOfModifiedByUserIds, array $getOptions = [], array $deleteOptions = []))
 * @method array updateByModifiedByUserId(integer $modified_by_user_id, array $getOptions = [], array $updateOptions = [])
 * @method array updateAllByModifiedByUserId(integer $modified_by_user_id, array $getOptions = [], array $updateOptions = [])
 * @method array updateAnyByModifiedByUserId(array $listOfModifiedByUserIds, array $getOptions = [], array $updateOptions = [])
 * </MAGIC_METHODS>
 */
class AclProfile extends AppMySQLModel
{
    public $name = 'AclProfile';
    protected $autoconfig = true;
    protected $user_timestamp_fields = true;

    /**
     * @var array
     */
    public $behaviors = ['CacheClearer' => []];

    /**
     * Before get callback.
     * ATTENTION: this is the only 'before' callback that cannot break the execution of the call.
     *
     * @param $options array
     *
     * @return void
     */
    protected function beforeGet(&$options)
    {
        parent::beforeGet($options);

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

            $options['where'][] = [
                'is_full_access' => 0
            ];
        }
    }

    /**
     * Get model configuration
     *
     * @return void
     */
    public function getConfig()
    {
        $config = parent::getConfig();

        if (
            Authentication::get('user', 'acl')
            && !Authentication::get('user', 'acl[is_full_access]')
        ) {
            unset($config['fields']['is_full_access']);
            unset($config['listFields']['is_full_access']);

            if ($index = array_search('is_full_access', $config['filter']['advanced'])) {
                unset($config['filter']['advanced'][$index]);
            }
        }

        return $config;
    }

    /**
     * Creates an admin profile if none exists.
     *
     * @return array|bool|null
     */
    public function addFullAdmin()
    {
        if ($this->count() > 0) {
            return null;
        }

        $element = $this->addNew([
            'name' => __('Administrator', true),
        ]);

        if ($element) {
            /** @var AclPermission $AclPermission **/
            $AclPermission = MySQLModel::getRecycledInstance('AclPermission', [], $this);
            $aclPermissionIds = array_keys($AclPermission->getList('id', 'id', ['where' => ['is_full_access' => 1]]));

            $this->setProfilePermissions($element[$this->name]['id'], $aclPermissionIds);
        }

        return $element;
    }

    /**
     * Replaces a profile's permissions.
     *
     * @param $id
     * @param $aclPermissionIds
     */
    public function setProfilePermissions($id, $aclPermissionIds)
    {
        /** @var AclProfilePermission $AclProfilePermission **/
        $AclProfilePermission = MySQLModel::getRecycledInstance('AclProfilePermission', [], $this);
        $aclProfilePermissions = $AclProfilePermission->getAllByAclProfileId($id, [
            'fields' => ['id', 'acl_permission_id'],
            'recursive' => -1,
            'hash_by' => 'acl_permission_id',
        ]);

        foreach ($aclPermissionIds as $acl_permission_id) {
            if (empty($aclProfilePermissions[$acl_permission_id])) {
                $AclProfilePermission->addNew([
                    'acl_profile_id' => $id,
                    'acl_permission_id' => $acl_permission_id,
                ]);
            }
        }

        foreach ($aclProfilePermissions as $aclProfilePermission) {
            if (!in_array($aclProfilePermission['AclProfilePermission']['acl_permission_id'], $aclPermissionIds)) {
                $AclProfilePermission->logicalDeleteById($aclProfilePermission['AclProfilePermission']['id']);
            }
        }
    }

    /**
     * After logical delete callback.
     *
     * @param $element array
     * @param $options array
     * @return void
     * @throws Exception
     *
     */
    protected function afterLogicalDelete($element, $options)
    {
        // Remove dangling permissions
        $this->setProfilePermissions($element[$this->name]['id'], []);

        // Remove dangling users
        /** @var User $User **/
        $User = MySQLModel::getRecycledInstance('User', [], $this);
        $User->updateAllByAclProfileId($element[$this->name]['id'], ['acl_profile_id' => null]);

        parent::afterLogicalDelete($element, $options);
    }

    /**
     * Duplicates a profile
     * @param int $id
     * @return false|array duplicated profile
     */
    public function duplicate($id)
    {
        $originalElement = $this->getById($id, [
            'recursive' => 1,
            'fields' => ['id', 'name', 'description',],
            'ApplicationProfileType' => false,
            'User' => false,
            'AclProfilePermission' => [
                'fields' => [
                    'id',
                    'acl_profile_id',
                    'acl_permission_id',
                ],
            ],
        ]);

        if (!$originalElement) {
            return false;
        }

        $duplicated_base_key = ' - copia';
        $duplicated_key = null;
        $duplicated_index = 0;

        do {
            $duplicated_index++;
            $duplicated_key = $duplicated_base_key . ($duplicated_index > 1 ? ' ' . $duplicated_index : '');
        } while ($this->count(['where' => ['name' => $originalElement[$this->name]['name'] . $duplicated_key]]) > 0);

        $elementData = $originalElement[$this->name];
        unset($elementData['id']);
        $elementData['name'] .= $duplicated_key;
        $element = $this->addNew($elementData);

        if (!$element) {
            return false;
        }

        /** @var AclProfilePermission $AclProfilePermission **/
        $AclProfilePermission = MySQLModel::getRecycledInstance('AclProfilePermission', [], $this);

        foreach ($originalElement['AclProfilePermission'] as $aclProfilePermission) {
            $AclProfilePermission->addNew([
                'acl_profile_id' => $element[$this->name]['id'],
                'acl_permission_id' => $aclProfilePermission['acl_permission_id'],
            ]);
        }

        return $element;
    }
}
