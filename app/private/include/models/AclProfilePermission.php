<?php

/**
 * Class AclProfilePermission
 *
 * Model class for table acl_profile_permissions
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
 * @method array|object getByAclProfileId(integer $acl_profile_id, array $options = [])
 * @method array getAllByAclProfileId(integer $acl_profile_id, array $options = [])
 * @method array getAnyByAclProfileId(array $listOfAclProfileIds, array $options = [])
 * @method bool deleteByAclProfileId(integer $acl_profile_id, array $getOptions = [], array $deleteOptions = []))
 * @method bool deleteAllByAclProfileId(integer $acl_profile_id, array $getOptions = [], array $deleteOptions = []))
 * @method bool deleteAnyByAclProfileId(array $listOfAclProfileIds, array $getOptions = [], array $deleteOptions = []))
 * @method array updateByAclProfileId(integer $acl_profile_id, array $getOptions = [], array $updateOptions = [])
 * @method array updateAllByAclProfileId(integer $acl_profile_id, array $getOptions = [], array $updateOptions = [])
 * @method array updateAnyByAclProfileId(array $listOfAclProfileIds, array $getOptions = [], array $updateOptions = [])
 * @method array|object getByAclPermissionId(integer $acl_permission_id, array $options = [])
 * @method array getAllByAclPermissionId(integer $acl_permission_id, array $options = [])
 * @method array getAnyByAclPermissionId(array $listOfAclPermissionIds, array $options = [])
 * @method bool deleteByAclPermissionId(integer $acl_permission_id, array $getOptions = [], array $deleteOptions = []))
 * @method bool deleteAllByAclPermissionId(integer $acl_permission_id, array $getOptions = [], array $deleteOptions = []))
 * @method bool deleteAnyByAclPermissionId(array $listOfAclPermissionIds, array $getOptions = [], array $deleteOptions = []))
 * @method array updateByAclPermissionId(integer $acl_permission_id, array $getOptions = [], array $updateOptions = [])
 * @method array updateAllByAclPermissionId(integer $acl_permission_id, array $getOptions = [], array $updateOptions = [])
 * @method array updateAnyByAclPermissionId(array $listOfAclPermissionIds, array $getOptions = [], array $updateOptions = [])
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
class AclProfilePermission extends AppMySQLModel
{
    public $name = 'AclProfilePermission';
    protected $autoconfig = true;
    protected $user_timestamp_fields = true;

    /**
     * @var array
     */
    public $behaviors = ['CacheClearer' => []];

    /**
     * Validates provided values.
     *
     * @param $element array element being updated
     * @param $values array values being updated
     * @param $options array
     *
     * @return bool validates or not
     */
    public function validates($element, &$values, $options)
    {
        if (!$element) {
            if ($this->count([
                'where' => [
                    'acl_profile_id' => $this->currentValue('acl_profile_id', $element, $values),
                    'acl_permission_id' => $this->currentValue('acl_permission_id', $element, $values),
                ],
            ]) > 0) {
                $this->errors[] = $this->errorsByField['acl_permission_id'] = __('Permission is already set for selected profile.', true);
                $this->errorCodesByField['acl_permission_id'] = 'unique';

                return false;
            }
        }

        return parent::validates($element, $values, $options);
    }

    /**
     * After save callback.
     *
     * @param $created bool whether or not the record was just created
     * @param $element array updated record
     * @param $values array updated values
     * @param $originalElement array element before update
     * @param $options array
     * @return void
     * @throws Exception
     *
     */
    protected function afterSave($created, $element, $values, $originalElement, $options)
    {
        parent::afterSave($created, $element, $values, $originalElement, $options);

        /** @var QueuedTask $QueuedTask **/
        $QueuedTask = MySQLModel::getRecycledInstance('QueuedTask', [], $this);
        $QueuedTask->queue(QueuedTask::REGENERATE_ACL_CACHE);
    }

    /**
     * After delete callback.
     *
     * @param $element array
     * @param $options array
     * @return void
     * @throws Exception
     *
     */
    protected function afterDelete($element, $options)
    {
        parent::afterDelete($element, $options);

        /** @var QueuedTask $QueuedTask **/
        $QueuedTask = MySQLModel::getRecycledInstance('QueuedTask', [], $this);
        $QueuedTask->queue(QueuedTask::REGENERATE_ACL_CACHE);
    }
}
