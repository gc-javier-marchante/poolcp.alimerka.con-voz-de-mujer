<?php

/**
 * Class CronController.
 */
class CronController extends AppController
{
    /**
     * Controller name
     */
    public $name = 'Cron';

    /**
     * @see Controller::$actionAccessLevel
     */
    protected $actionAccessLevel = [
        '*' => -1,
    ];

    protected $view = false;
    protected $layout = false;

    /**
     * Before filter callback. Called before the action is called.
     * Checks access.
     *
     * @return bool if not true stops execution
     */
    protected function beforeFilter()
    {
        if (!GestyMVC::isCli()) {
            $this->forbidden(false);
        }

        return parent::beforeFilter();
    }

    /**
     * Handles canonicalUrl listing.
     */
    public function worker()
    {
        $startup_time = time();

        foreach ([
            'queuedTasks',
            'checkErrorLogs',
        ] as $cron_function) {
            if ($schedule = GestyMVC::env('CRON_SCHEDULE_' . strtoupper(Inflector::snakeCase($cron_function)))) {
                $cron = new Cron\CronExpression($schedule);

                if ($cron->isDue()) {
                    call_user_func([$this, $cron_function], $startup_time);
                }
            }
        }
    }

    /**
     * Sends an email with failures
     */
    public function checkErrorLogs()
    {
        if (!GestyMVC::config('send_error_logs_to_recipient')) {
            return;
        }

        clitrace('Started ' . __CLASS__ . ':' . __FUNCTION__);

        $folders = scandir(LOGS_PATH);
        $resultFiles = [];

        foreach ($folders as $folder) {
            if (in_array($folder, ['.', '..']) || stripos($folder, 'error') === false) {
                continue;
            }

            $path = LOGS_PATH . $folder;

            if (!is_dir($path)) {
                continue;
            }

            $path = $path . str_replace('/', DIRECTORY_SEPARATOR, date('/Y/m/d/'));

            if (!is_dir($path)) {
                continue;
            }

            $hour = date('H', strtotime('-1 hour'));
            $files = [];

            if ($handle = opendir($path)) {
                while (false !== ($file = readdir($handle))) {
                    if (starts_with($file, $hour . '.')) {
                        $files[] = $path . '/' . $file;
                    }
                }

                closedir($handle);
            }

            if (count($files)) {
                $resultFiles[$folder] = $files;
            }
        }

        if ($resultFiles) {
            $html = '<ul>';

            foreach ($resultFiles as $folder => $files) {
                $html .= '<li>' . $folder . '<ul>';

                foreach ($files as $file) {
                    $html .= '<li>' . strrchr($file, '/');
                    $html .= '<br>' . nl2br(utf8html(file_get_contents($file)));
                    $html .= '</li>';
                }

                $html .= '</ul></li>';
            }

            $html .= '</ul>';

            Email::send(GestyMVC::config('website_name') . ' - ' . __('Error logs', true), '<p>' . __('The following errors were found in the last hour.', true) . '</p>' . $html, GestyMVC::config('send_error_logs_to_recipient'), [], true, null, [
                'reply_to' => GestyMVC::config('send_error_logs_to_recipient'),
                'do_not_override_recipients' => true,
            ]);
        }

        clitrace('Ended ' . __CLASS__ . ':' . __FUNCTION__);
    }

    /**
     * Runs queued tasks
     */
    public function queuedTasks()
    {
        clitrace('Started ' . __CLASS__ . ':' . __FUNCTION__);

        /** @var QueuedTask $QueuedTask **/
        $QueuedTask = MySQLModel::getInstance('QueuedTask');
        $queuedTask = $QueuedTask->get();

        if ($queuedTask) {
            Dispatcher::setCustomRequestId($queuedTask['QueuedTask']['gestymvc_request_identifier']);
            $QueuedTask->deleteAllByCode($queuedTask['QueuedTask']['code']);

            switch ($queuedTask['QueuedTask']['code']) {
                case QueuedTask::REGENERATE_ACL_CACHE:
                    /** @var AclCacheUserPermission $AclCacheUserPermission **/
                    $AclCacheUserPermission = MySQLModel::getInstance('AclCacheUserPermission');
                    $AclCacheUserPermission->regenerateCache();
                    CachedAdapter::clearAll();
                    break;
            }
        }

        clitrace('Ended ' . __CLASS__ . ':' . __FUNCTION__);
    }
}
