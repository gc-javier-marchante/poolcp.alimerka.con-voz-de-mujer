<?php

/**
 * Class FileCategory
 *
 * Model class for table file_categories
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
 * @method array|object getByName(string $name, array $options = [])
 * @method array getAllByName(string $name, array $options = [])
 * @method array getAnyByName(array $listOfNames, array $options = [])
 * @method bool deleteByName(string $name, array $getOptions = [], array $deleteOptions = []))
 * @method bool deleteAllByName(string $name, array $getOptions = [], array $deleteOptions = []))
 * @method bool deleteAnyByName(array $listOfNames, array $getOptions = [], array $deleteOptions = []))
 * @method array updateByName(string $name, array $getOptions = [], array $updateOptions = [])
 * @method array updateAllByName(string $name, array $getOptions = [], array $updateOptions = [])
 * @method array updateAnyByName(array $listOfNames, array $getOptions = [], array $updateOptions = [])
 * @method array|object getByFileCategoryId(int $file_category_id, array $options = [])
 * @method array getAllByFileCategoryId(int $file_category_id, array $options = [])
 * @method array getAnyByFileCategoryId(array $listOfFileCategoryIds, array $options = [])
 * @method bool deleteByFileCategoryId(int $file_category_id, array $getOptions = [], array $deleteOptions = []))
 * @method bool deleteAllByFileCategoryId(int $file_category_id, array $getOptions = [], array $deleteOptions = []))
 * @method bool deleteAnyByFileCategoryId(array $listOfFileCategoryIds, array $getOptions = [], array $deleteOptions = []))
 * @method array updateByFileCategoryId(int $file_category_id, array $getOptions = [], array $updateOptions = [])
 * @method array updateAllByFileCategoryId(int $file_category_id, array $getOptions = [], array $updateOptions = [])
 * @method array updateAnyByFileCategoryId(array $listOfFileCategoryIds, array $getOptions = [], array $updateOptions = [])
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
class FileCategory extends AppMySQLModel
{
    /**
     * Model name
     *
     * @var string
     */
    public $name = 'FileCategory';
    protected $autoconfig = true;
    protected $user_timestamp_fields = true;

    /**
     * Add new override. Looks for a equivalent before adding.
     *
     * @param array $values
     * @param array $options
     *
     * @return bool|array
     */
    public function addNew($values = [], $options = [])
    {
        if (isset($values['name'])) {
            $repeatedConditions = ['name LIKE' => $values['name']];

            // Add category if field exists
            if ($this->structure && !empty($this->structure['file_category_id'])) {
                $repeatedConditions['file_category_id'] = !empty($values['file_category_id']) ? $values['file_category_id'] : null;
            }

            $element = $this->get(['where' => $repeatedConditions, 'recursive' => -1]);

            if ($element) {
                return $element;
            }
        }

        return parent::addNew($values, $options);
    }

    /**
     * Before validate callback.
     * Executed before validation on updateFields and addNew calls.
     *
     * @param $element array element being updated
     * @param $values array values being updated
     * @param $options array options
     *
     * @return bool
     */
    public function beforeValidate($element, &$values, $options)
    {
        if ($element && $element[$this->name]['id'] == 1) {
            return false;
        }

        if (!$this->currentValue('file_category_id', $element, $values)) {
            $values['file_category_id'] = 1;
        }
        if ($element) {
            $repeatedConditions = [
                'id <>' => $element[$this->name]['id'],
                'name LIKE' => $this->currentValue('name', $element, $values),
            ];

            // Add category if field exists
            if ($this->structure && !empty($this->structure['file_category_id'])) {
                $repeatedConditions['file_category_id'] = $this->currentValue('file_category_id', $element, $values);
            }

            $element = $this->get(['where' => $repeatedConditions, 'recursive' => -1, 'fields' => ['id']]);

            if ($element) {
                $this->errors[] = __('There is already an element with that name.', true);
                return false;
            }
        }

        return parent::beforeValidate($element, $values, $options);
    }

    /**
     * Before delete callback.
     *
     * @param $element array element to be deleted.
     * @param $options array
     *
     * @return bool if not true, stops deleting
     */
    protected function beforeDelete($element, $options)
    {
        if ($element[$this->name]['id'] == 1) {
            return false;
        }

        return parent::beforeDelete($element, $options);
    }
}
