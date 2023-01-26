<?php

/**
 * App specific model class.
 * Current app models inherit this class instead of the original one. All specific modifications to the model class
 * must be done to this one instead.
 */
class AppMySQLModel extends MySQLModel
{
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
        $textFields = [];
        $textNullableFields = [];

        foreach ($this->structure as $field => $fieldStructure) {
            if (in_array($fieldStructure['DATA_TYPE'], ['varchar', 'char', 'text', 'mediumtext', 'longtext'])) {
                $textFields[] = $field;

                if ($fieldStructure['IS_NULLABLE'] === 'YES') {
                    $textNullableFields[] = $field;
                }
            } elseif (in_array($fieldStructure['DATA_TYPE'], ['int', 'tinyint', 'bigint', 'double', 'float', 'decimal'])) {
                if (
                    isset($values[$field])
                    && is_string($values[$field])
                    && strlen($values[$field]) == 0
                ) {
                    if ($fieldStructure['IS_NULLABLE'] === 'YES') {
                        $values[$field] = null;
                    }
                }
            }
        }

        $this->trimFields($textFields, $values);
        $this->emptyToDefaultFields($textNullableFields, $values);

        return parent::beforeValidate($element, $values, $options);
    }

    /**
     * Trims fields contents
     *
     * @param $fields array|string
     * @param $values array
     */
    protected function trimFields($fields, &$values)
    {
        if (!is_array($fields)) {
            $fields = [$fields];
        }

        foreach ($fields as $field) {
            if (array_key_exists($field, $values)) {
                $values[$field] = trim($values[$field]);
            }
        }
    }

    /**
     * Changes fields contents to null if it was empty
     *
     * @param $fields array|string
     * @param $values array
     * @param $default
     */
    protected function emptyToDefaultFields($fields, &$values, $default = null)
    {
        if (!is_array($fields)) {
            $fields = [$fields];
        }

        foreach ($fields as $field) {
            if (array_key_exists($field, $values) && !$values[$field]) {
                $values[$field] = $default;
            }
        }
    }
}
