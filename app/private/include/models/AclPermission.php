<?php

/**
 * Class AclPermission
 *
 * Model class for table acl_permissions
 *
 * <MAGIC_METHODS>
 * @method array|object getById(int $id, array $options = [])
 * @method array getAllById(int $id, array $options = [])
 * @method array getAnyById(array $listOfIds, array $options = [])
 * @method bool deleteAllById(int $id, array $getOptions = [], array $deleteOptions = []))
 * @method bool deleteAnyById(array $listOfIds, array $getOptions = [], array $deleteOptions = []))
 * @method array updateById(int $id, array $getOptions = [], array $updateOptions = [])
 * @method array updateAllById(int $id, array $getOptions = [], array $updateOptions = [])
 * @method array updateAnyById(array $listOfIds, array $getOptions = [], array $updateOptions = [])
 * @method array|object getByAclSectionId(int $acl_section_id, array $options = [])
 * @method array getAllByAclSectionId(int $acl_section_id, array $options = [])
 * @method array getAnyByAclSectionId(array $listOfAclSectionIds, array $options = [])
 * @method bool deleteByAclSectionId(int $acl_section_id, array $getOptions = [], array $deleteOptions = []))
 * @method bool deleteAllByAclSectionId(int $acl_section_id, array $getOptions = [], array $deleteOptions = []))
 * @method bool deleteAnyByAclSectionId(array $listOfAclSectionIds, array $getOptions = [], array $deleteOptions = []))
 * @method array updateByAclSectionId(int $acl_section_id, array $getOptions = [], array $updateOptions = [])
 * @method array updateAllByAclSectionId(int $acl_section_id, array $getOptions = [], array $updateOptions = [])
 * @method array updateAnyByAclSectionId(array $listOfAclSectionIds, array $getOptions = [], array $updateOptions = [])
 * @method array|object getByName(string $name, array $options = [])
 * @method array getAllByName(string $name, array $options = [])
 * @method array getAnyByName(array $listOfNames, array $options = [])
 * @method bool deleteByName(string $name, array $getOptions = [], array $deleteOptions = []))
 * @method bool deleteAllByName(string $name, array $getOptions = [], array $deleteOptions = []))
 * @method bool deleteAnyByName(array $listOfNames, array $getOptions = [], array $deleteOptions = []))
 * @method array updateByName(string $name, array $getOptions = [], array $updateOptions = [])
 * @method array updateAllByName(string $name, array $getOptions = [], array $updateOptions = [])
 * @method array updateAnyByName(array $listOfNames, array $getOptions = [], array $updateOptions = [])
 * @method array|object getByNameShort(string $name_short, array $options = [])
 * @method array getAllByNameShort(string $name_short, array $options = [])
 * @method array getAnyByNameShort(array $listOfNameShorts, array $options = [])
 * @method bool deleteByNameShort(string $name_short, array $getOptions = [], array $deleteOptions = []))
 * @method bool deleteAllByNameShort(string $name_short, array $getOptions = [], array $deleteOptions = []))
 * @method bool deleteAnyByNameShort(array $listOfNameShorts, array $getOptions = [], array $deleteOptions = []))
 * @method array updateByNameShort(string $name_short, array $getOptions = [], array $updateOptions = [])
 * @method array updateAllByNameShort(string $name_short, array $getOptions = [], array $updateOptions = [])
 * @method array updateAnyByNameShort(array $listOfNameShorts, array $getOptions = [], array $updateOptions = [])
 * @method array|object getByAlias(string $alias, array $options = [])
 * @method array getAllByAlias(string $alias, array $options = [])
 * @method array getAnyByAlias(array $listOfAliases, array $options = [])
 * @method bool deleteByAlias(string $alias, array $getOptions = [], array $deleteOptions = []))
 * @method bool deleteAllByAlias(string $alias, array $getOptions = [], array $deleteOptions = []))
 * @method bool deleteAnyByAlias(array $listOfAliases, array $getOptions = [], array $deleteOptions = []))
 * @method array updateByAlias(string $alias, array $getOptions = [], array $updateOptions = [])
 * @method array updateAllByAlias(string $alias, array $getOptions = [], array $updateOptions = [])
 * @method array updateAnyByAlias(array $listOfAliases, array $getOptions = [], array $updateOptions = [])
 * @method array|object getByIsFullAccess(int $is_full_access, array $options = [])
 * @method array getAllByIsFullAccess(int $is_full_access, array $options = [])
 * @method array getAnyByIsFullAccess(array $listOfIsFullAccesses, array $options = [])
 * @method bool deleteByIsFullAccess(int $is_full_access, array $getOptions = [], array $deleteOptions = []))
 * @method bool deleteAllByIsFullAccess(int $is_full_access, array $getOptions = [], array $deleteOptions = []))
 * @method bool deleteAnyByIsFullAccess(array $listOfIsFullAccesses, array $getOptions = [], array $deleteOptions = []))
 * @method array updateByIsFullAccess(int $is_full_access, array $getOptions = [], array $updateOptions = [])
 * @method array updateAllByIsFullAccess(int $is_full_access, array $getOptions = [], array $updateOptions = [])
 * @method array updateAnyByIsFullAccess(array $listOfIsFullAccesses, array $getOptions = [], array $updateOptions = [])
 * @method array|object getByCreatedByUserId(int $created_by_user_id, array $options = [])
 * @method array getAllByCreatedByUserId(int $created_by_user_id, array $options = [])
 * @method array getAnyByCreatedByUserId(array $listOfCreatedByUserIds, array $options = [])
 * @method bool deleteByCreatedByUserId(int $created_by_user_id, array $getOptions = [], array $deleteOptions = []))
 * @method bool deleteAllByCreatedByUserId(int $created_by_user_id, array $getOptions = [], array $deleteOptions = []))
 * @method bool deleteAnyByCreatedByUserId(array $listOfCreatedByUserIds, array $getOptions = [], array $deleteOptions = []))
 * @method array updateByCreatedByUserId(int $created_by_user_id, array $getOptions = [], array $updateOptions = [])
 * @method array updateAllByCreatedByUserId(int $created_by_user_id, array $getOptions = [], array $updateOptions = [])
 * @method array updateAnyByCreatedByUserId(array $listOfCreatedByUserIds, array $getOptions = [], array $updateOptions = [])
 * @method array|object getByModifiedByUserId(int $modified_by_user_id, array $options = [])
 * @method array getAllByModifiedByUserId(int $modified_by_user_id, array $options = [])
 * @method array getAnyByModifiedByUserId(array $listOfModifiedByUserIds, array $options = [])
 * @method bool deleteByModifiedByUserId(int $modified_by_user_id, array $getOptions = [], array $deleteOptions = []))
 * @method bool deleteAllByModifiedByUserId(int $modified_by_user_id, array $getOptions = [], array $deleteOptions = []))
 * @method bool deleteAnyByModifiedByUserId(array $listOfModifiedByUserIds, array $getOptions = [], array $deleteOptions = []))
 * @method array updateByModifiedByUserId(int $modified_by_user_id, array $getOptions = [], array $updateOptions = [])
 * @method array updateAllByModifiedByUserId(int $modified_by_user_id, array $getOptions = [], array $updateOptions = [])
 * @method array updateAnyByModifiedByUserId(array $listOfModifiedByUserIds, array $getOptions = [], array $updateOptions = [])
 * </MAGIC_METHODS>
 */
class AclPermission extends AppMySQLModel
{
    public $name = 'AclPermission';
    public $cache = true;
    protected $autoconfig = true;
    protected $user_timestamp_fields = true;
}
