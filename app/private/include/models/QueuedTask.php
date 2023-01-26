<?php

/**
 * Class QueuedTask
 *
 * Model class for table queued_tasks
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
 * @method array|object getByGestymvcRequestIdentifier(string $gestymvc_request_identifier, array $options = [])
 * @method array getAllByGestymvcRequestIdentifier(string $gestymvc_request_identifier, array $options = [])
 * @method array getAnyByGestymvcRequestIdentifier(array $listOfGestymvcRequestIdentifiers, array $options = [])
 * @method bool deleteByGestymvcRequestIdentifier(string $gestymvc_request_identifier, array $getOptions = [], array $deleteOptions = []))
 * @method bool deleteAllByGestymvcRequestIdentifier(string $gestymvc_request_identifier, array $getOptions = [], array $deleteOptions = []))
 * @method bool deleteAnyByGestymvcRequestIdentifier(array $listOfGestymvcRequestIdentifiers, array $getOptions = [], array $deleteOptions = []))
 * @method array updateByGestymvcRequestIdentifier(string $gestymvc_request_identifier, array $getOptions = [], array $updateOptions = [])
 * @method array updateAllByGestymvcRequestIdentifier(string $gestymvc_request_identifier, array $getOptions = [], array $updateOptions = [])
 * @method array updateAnyByGestymvcRequestIdentifier(array $listOfGestymvcRequestIdentifiers, array $getOptions = [], array $updateOptions = [])
 * @method array|object getByCode(string $code, array $options = [])
 * @method array getAllByCode(string $code, array $options = [])
 * @method array getAnyByCode(array $listOfCodes, array $options = [])
 * @method bool deleteByCode(string $code, array $getOptions = [], array $deleteOptions = []))
 * @method bool deleteAllByCode(string $code, array $getOptions = [], array $deleteOptions = []))
 * @method bool deleteAnyByCode(array $listOfCodes, array $getOptions = [], array $deleteOptions = []))
 * @method array updateByCode(string $code, array $getOptions = [], array $updateOptions = [])
 * @method array updateAllByCode(string $code, array $getOptions = [], array $updateOptions = [])
 * @method array updateAnyByCode(array $listOfCodes, array $getOptions = [], array $updateOptions = [])
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
class QueuedTask extends AppMySQLModel
{
    const REGENERATE_ACL_CACHE = 'REGENERATE_ACL_CACHE';

    /**
     * Model name
     *
     * @var string
     */
    public $name = 'QueuedTask';
    protected $autoconfig = true;
    protected $user_timestamp_fields = true;
    private static $alreadyQueuedTasksOnThisThread = [];

    /**
     * @param $task_code
     * @return array|bool
     */
    public function queue($task_code)
    {
        if (in_array($task_code, self::$alreadyQueuedTasksOnThisThread)) {
            return true;
        }

        self::$alreadyQueuedTasksOnThisThread[] = $task_code;

        return $this->addNew([
            'code' => $task_code,
            'created_by_user_id' => Authentication::get('user', 'id'),
        ], [
            'skip_callbacks' => true,
        ]);
    }

    /**
     * Before save callback
     *
     * @param $element array. Element that's being
     * updated/created.
     * @param $values array. Values to be updated.
     * @param $options array
     *
     * @return bool continue or not
     */
    protected function beforeSave($element, &$values, $options)
    {
        if (empty($element)) {
            $values['gestymvc_request_identifier'] = Dispatcher::getRequestId();
        }

        return parent::beforeSave($element, $values, $options);
    }
}
