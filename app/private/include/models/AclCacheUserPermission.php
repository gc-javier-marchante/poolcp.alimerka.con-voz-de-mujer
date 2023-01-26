<?php

/**
 * Class AclCacheUserPermission
 *
 * Model class for table acl_cache_user_permissions
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
 * @method array|object getByUserId(integer $user_id, array $options = [])
 * @method array getAllByUserId(integer $user_id, array $options = [])
 * @method array getAnyByUserId(array $listOfUserIds, array $options = [])
 * @method bool deleteByUserId(integer $user_id, array $getOptions = [], array $deleteOptions = []))
 * @method bool deleteAllByUserId(integer $user_id, array $getOptions = [], array $deleteOptions = []))
 * @method bool deleteAnyByUserId(array $listOfUserIds, array $getOptions = [], array $deleteOptions = []))
 * @method array updateByUserId(integer $user_id, array $getOptions = [], array $updateOptions = [])
 * @method array updateAllByUserId(integer $user_id, array $getOptions = [], array $updateOptions = [])
 * @method array updateAnyByUserId(array $listOfUserIds, array $getOptions = [], array $updateOptions = [])
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
class AclCacheUserPermission extends AppMySQLModel
{
    /**
     * Model name
     *
     * @var string
     */
    public $name = 'AclCacheUserPermission';
    public $cache = true;

    /**
     * Default display fields for the model. When more than one, they are concatenated with an space.
     *
     * @var array
     */
    public $displayFields = ['id',];

    /**
     * Valid order by expressions. List of expression that can be used to order. Expression can be a field or a complex
     * expression. Do not include direction of order.
     *
     * @var array
     */
    public $validOrderByExpressions = [
        'id',
        'user_id',
        'acl_permission_id',
        'created',
        'created_by_user_id',
        'modified',
        'modified_by_user_id',
    ];

    /**
     * All mysql tables require created and modified fields. If this is set to true, the table also requires
     * created_by_user_id and modified_by_user_id and both area populated automatically.
     *
     * @var bool
     */
    protected $user_timestamp_fields = true;

    /**
     * Validation array for fields.
     * Structure: array('field_name' => array('validation_method' => 'Validation error message.')
     * Validation methods available:
     *  - notempty: if present, value cannot be empty
     *  - required: value must be present on creation. An empty value validates.
     *  - email: value must have an email format
     *  - numeric: value must be numeric
     *  - int: value must be the representation of an integer
     *
     * @var array
     */
    public $validation = [
        'user_id' => [
            'id' => '%readable% must be a valid id value.',
            'required' => '%readable% is required.',
            'notempty' => '%readable% cannot be left empty.',
        ],
        'acl_permission_id' => [
            'id' => '%readable% must be a valid id value.',
            'required' => '%readable% is required.',
            'notempty' => '%readable% cannot be left empty.',
        ],
        'created_by_user_id' => [
            'id' => '%readable% must be a valid id value.',
        ],
        'modified_by_user_id' => [
            'id' => '%readable% must be a valid id value.',
        ],
    ];

    /**
     * Regenerates the ACL cache
     */
    public function regenerateCache()
    {
        self::query(
            /** @lang text */
            'DELETE FROM `acl_cache_user_permissions`'
        );
        self::query(
            /** @lang text */
            'INSERT INTO `acl_cache_user_permissions`
            SELECT NULL, `t1`.`user_id`, `acl_permissions`.`id` AS `acl_permission_id`, NOW(), NULL, NOW(), NULL
            FROM `acl_permissions`
                INNER JOIN
                   (SELECT `users`.`id` AS `user_id`, `acl_permissions`.`id` AS `acl_permission_id`, `acl_permissions`.`acl_section_id`, `acl_permissions`.`is_full_access`
                    FROM `users`
                    INNER JOIN `acl_profile_permissions` ON `acl_profile_permissions`.`acl_profile_id` = `users`.`acl_profile_id`
                    INNER JOIN `acl_permissions` ON `acl_permissions`.`id` = `acl_profile_permissions`.`acl_permission_id`
                    ) AS `t1`
                ON `t1`.`acl_permission_id` = `acl_permissions`.`id` OR (`t1`.`is_full_access` AND `t1`.`acl_section_id` = `acl_permissions`.`acl_section_id`)'
        );
    }
}
