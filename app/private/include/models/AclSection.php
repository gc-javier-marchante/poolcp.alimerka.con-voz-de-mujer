<?php

/**
 * Class AclSection
 *
 * Model class for table acl_sections
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
class AclSection extends AppMySQLModel
{
    /**
     * Model name
     *
     * @var string
     */
    public $name = 'AclSection';
    public $cache = true;
    protected $autoconfig = true;
    protected $user_timestamp_fields = true;

    /**
     * Initializes section and permissions for content type
     *
     * @param int $content_type_id
     * @return void
     */
    public function initializeForContentType($content_type_id)
    {
        if ($id = $this->field('id', ['where' => ['content_type_id' => $content_type_id]])) {
            return $id;
        }

        /** @var ContentType $ContentType **/
        $ContentType = MySQLModel::getRecycledInstance('ContentType', [], $this);
        $name = $ContentType->field('alias', ['where' => ['id' => $content_type_id]]);

        if (!$name) {
            return false;
        }

        $this->beginTransaction();

        if ($element = $this->addNew(['content_type_id' => $content_type_id, 'name' => __(Inflector::lowercaseName(Inflector::pluralize(Inflector::snakeCase($name, ' '))), true)])) {
            $permissions = [
                'Full access',
                'Menu',
                'List',
                'View',
                'Create',
                'Update',
                'Delete',
                'Export',
            ];

            /** @var AclPermission $AclPermission **/
            $AclPermission = MySQLModel::getRecycledInstance('AclPermission', [], $this);

            foreach ($permissions as $permission) {
                if (!$AclPermission->addNew([
                    'acl_section_id' => $element[$this->name]['id'],
                    'name' => mb_strtoupper(Inflector::snakeCase($name) . '_' . str_replace(' ', '_', strtolower($permission)), 'UTF-8'),
                    'name_short' => $permission,
                    'alias' => __($permission, true) . ' ' . mb_strtolower(__(Inflector::lowercaseName(Inflector::pluralize(Inflector::snakeCase($name, ' '))), true), 'UTF-8'),
                    'is_full_access' => $permission === 'Full access' ? 1 : 0,
                ])) {
                    $this->rollbackTransaction();

                    return false;
                }
            }

            $this->commitTransaction();
            CachedAdapter::clearAll();

            return $element[$this->name]['id'];
        }

        $this->rollbackTransaction();

        return false;
    }
}
