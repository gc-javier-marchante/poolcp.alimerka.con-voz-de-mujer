<?php

/**
 * Class UserTfaToken
 *
 * Model class for table user_tfa_tokens
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
 * @method array|object getByUserId(int $user_id, array $options = [])
 * @method array getAllByUserId(int $user_id, array $options = [])
 * @method array getAnyByUserId(array $listOfUserIds, array $options = [])
 * @method bool deleteByUserId(int $user_id, array $getOptions = [], array $deleteOptions = []))
 * @method bool deleteAllByUserId(int $user_id, array $getOptions = [], array $deleteOptions = []))
 * @method bool deleteAnyByUserId(array $listOfUserIds, array $getOptions = [], array $deleteOptions = []))
 * @method array updateByUserId(int $user_id, array $getOptions = [], array $updateOptions = [])
 * @method array updateAllByUserId(int $user_id, array $getOptions = [], array $updateOptions = [])
 * @method array updateAnyByUserId(array $listOfUserIds, array $getOptions = [], array $updateOptions = [])
 * @method array|object getByToken(string $token, array $options = [])
 * @method array getAllByToken(string $token, array $options = [])
 * @method array getAnyByToken(array $listOfTokens, array $options = [])
 * @method bool deleteByToken(string $token, array $getOptions = [], array $deleteOptions = []))
 * @method bool deleteAllByToken(string $token, array $getOptions = [], array $deleteOptions = []))
 * @method bool deleteAnyByToken(array $listOfTokens, array $getOptions = [], array $deleteOptions = []))
 * @method array updateByToken(string $token, array $getOptions = [], array $updateOptions = [])
 * @method array updateAllByToken(string $token, array $getOptions = [], array $updateOptions = [])
 * @method array updateAnyByToken(array $listOfTokens, array $getOptions = [], array $updateOptions = [])
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
class UserTfaToken extends AppMySQLModel
{
    /**
     * Model name
     *
     * @var string
     */
    public $name = 'UserTfaToken';

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
        if (!$element) {
            $values['prefix'] = random_token(false, 5, 5);
            $values['token'] = $values['prefix'] . random_token(true, 100, 130);
        }

        return parent::beforeValidate($element, $values, $options);
    }

    /**
     * Checks token validity
     *
     * @param string $token
     * @param string $user_id
     * @return boolean
     */
    public function isValidForUser($token, $user_id)
    {
        if (
            !$user_id
            || !$token
        ) {
            return false;
        }

        $this->deleteAll(['where' => [
            'user_id' => $user_id,
            'created <' => date('Y-m-d H:i:s', time() - GestyMVC::config('max_otp_seconds'))
        ]]);

        $prefix = substr($token, 0, 5);
        $elements = $this->getAll([
            'where' => [
                'user_id' => $user_id,
                'prefix' => $prefix
            ], 'fields' => ['token'],
            'recursive' => -1
        ]);

        foreach ($elements as $element) {
            if ($element[$this->name]['token'] == $token) {
                return true;
            }
        }

        return false;
    }
}
