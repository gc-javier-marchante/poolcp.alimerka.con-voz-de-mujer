<?php

/**
 * Class Store
 *
 * Model class for table stores
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
 * @method array|object getByCode(string $code, array $options = [])
 * @method array getAllByCode(string $code, array $options = [])
 * @method array getAnyByCode(array $listOfCodes, array $options = [])
 * @method bool deleteByCode(string $code, array $getOptions = [], array $deleteOptions = []))
 * @method bool deleteAllByCode(string $code, array $getOptions = [], array $deleteOptions = []))
 * @method bool deleteAnyByCode(array $listOfCodes, array $getOptions = [], array $deleteOptions = []))
 * @method array updateByCode(string $code, array $getOptions = [], array $updateOptions = [])
 * @method array updateAllByCode(string $code, array $getOptions = [], array $updateOptions = [])
 * @method array updateAnyByCode(array $listOfCodes, array $getOptions = [], array $updateOptions = [])
 * @method array|object getByGroup(int $group, array $options = [])
 * @method array getAllByGroup(int $group, array $options = [])
 * @method array getAnyByGroup(array $listOfGroups, array $options = [])
 * @method bool deleteByGroup(int $group, array $getOptions = [], array $deleteOptions = []))
 * @method bool deleteAllByGroup(int $group, array $getOptions = [], array $deleteOptions = []))
 * @method bool deleteAnyByGroup(array $listOfGroups, array $getOptions = [], array $deleteOptions = []))
 * @method array updateByGroup(int $group, array $getOptions = [], array $updateOptions = [])
 * @method array updateAllByGroup(int $group, array $getOptions = [], array $updateOptions = [])
 * @method array updateAnyByGroup(array $listOfGroups, array $getOptions = [], array $updateOptions = [])
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
class Store extends AppMySQLModel
{
    /**
     * Model name
     *
     * @var string
     */
    public $name = 'Store';

    /**
     * Model auto config generation
     *
     * @var array
     */
    protected $autoconfig = true;

    /**
     * All mysql tables require created and modified fields. If this is set to true, the table also requires
     * created_by_user_id and modified_by_user_id and both area populated automatically.
     *
     * @var bool
     */
    protected $user_timestamp_fields = true;
}
