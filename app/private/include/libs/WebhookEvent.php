<?php

class WebhookEvent
{
    /**
     * Action (CRUD)
     *
     * @var string
     */
    private string $action;

    /**
     * Class name
     *
     * @var string
     */
    private string $entity_name;

    /**
     * Data
     *
     * @var array|JsonSerializable
     */
    private $data;

    /**
     * Triggered by user id
     *
     * @var int
     */
    private ?int $user_id = null;

    /**
     * Database model
     *
     * @var Webhook
     */
    private static ?Webhook $model = null;

    /**
     * Constructor
     *
     * @param string $action
     * @param string $entity_name
     * @param array|JsonSerializable $data
     * @param integer|null $user_id
     */
    public function __construct(string $action, string $entity_name, $data, ?int $user_id)
    {
        $this->action = $action;
        $this->entity_name = $entity_name;
        $this->data = $data;
        $this->user_id = $user_id;
    }

    /**
     * Undocumented function
     *
     * @return Webhook
     */
    private static function model(): Webhook
    {
        if (self::$model === null) {
            /** @var Webhook $Webhook **/
            $Webhook = MySQLModel::getInstance('Webhook');
            self::$model = $Webhook;
        }

        return self::$model;
    }

    /**
     * Checks if an entity_name action is watched
     *
     * @param string $action
     * @param string $entity_name
     * @return bool
     */
    public static function isEntityActionWatched(string $action, string $entity_name): bool
    {
        return self::model()->count([
            'where' => self::entityActionWatchConditions($action, $entity_name),
            'from_class' => self::class,
            'from_method' => __FUNCTION__,
        ]) > 0;
    }

    /**
     * Generates the MySQL conditions to check if an entity_name action is watched
     *
     * @param string $action
     * @param string $entity_name
     * @return array
     */
    private static function entityActionWatchConditions(string $action, string $entity_name): array
    {
        return [
            'is_active' => 1,
            'JSON_CONTAINS(`actions`, \'' . sql_escape(json_encode($action)) . '\', \'$\')',
            'JSON_CONTAINS(`models`, \'' . sql_escape(json_encode($entity_name)) . '\', \'$\')',
        ];
    }

    /**
     * Fires the event
     *
     * @param array|null $webhookIdWhitelist list of valid webhooks
     * @return int number of webhooks called
     */
    public function fire(?array $webhookIdWhitelist = null): int
    {
        // Get affected webhooks
        $webhooks_called = 0;
        $webhooks = self::model()->getAll([
            'where' => [
                ($webhookIdWhitelist === null ? 1 : ['id' => $webhookIdWhitelist]),
                self::entityActionWatchConditions($this->action, $this->entity_name),
            ],
            'from_class' => self::class,
            'from_method' => __FUNCTION__,
            'recursive' => -1,
        ]);

        // If no webhooks, do nothing
        if (!$webhooks) {
            return $webhooks_called;
        }

        // Prepare event information
        $eventInfo = [
            'user_id' => $this->user_id,
            'entity' => $this->entity_name,
            'action' => $this->action,
        ];

        // No data message
        $dataless_message = json_encode($eventInfo);

        // Other messages (will be generated if needed)
        $data_message = null;
        $dataless_message_urlencoded = null;

        // Notify each webhook
        foreach ($webhooks as $webhook) {
            // Generate other messages the first time they are required
            if (
                $webhook['Webhook']['is_data']
                && $data_message === null
            ) {
                $data_message = json_encode(array_merge($eventInfo, ['data' => $this->data]));
            } elseif (
                $webhook['Webhook']['method'] !== 'POST'
                && $dataless_message_urlencoded === null
            ) {
                $dataless_message_urlencoded = http_build_query(['event' => $eventInfo]);
            }

            // Default headers
            $headers = [
                'X-WEBHOOK-TOKEN' => sha1($webhook['Webhook']['url'] . $webhook['Webhook']['secret']),
            ];

            // Calls the URL without waiting nor processing the server response
            $ch = curl_init();

            // Set URL
            if ($webhook['Webhook']['method'] !== 'POST') {
                curl_setopt($ch, CURLOPT_URL, $webhook['Webhook']['url'] . (strpos($webhook['Webhook']['url'], '?') === false ? '?' : '&') . $dataless_message_urlencoded);
                curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $webhook['Webhook']['method']);
            } else {
                curl_setopt($ch, CURLOPT_URL, $webhook['Webhook']['url']);
                curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
                curl_setopt($ch, CURLOPT_POSTFIELDS, ($webhook['Webhook']['is_data'] ? $data_message : $dataless_message));

                $headers['Content-Type'] = 'Content-Type: application/json';
                $headers['Content-Length'] = 'Content-Length: ' . strlen(($webhook['Webhook']['is_data'] ? $data_message : $dataless_message));
            }

            // Set headers
            curl_setopt($ch, CURLOPT_HTTPHEADER, array_values($headers));
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
            curl_setopt($ch, CURLOPT_FRESH_CONNECT, true);
            curl_setopt($ch, CURLOPT_TIMEOUT, 1);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            @curl_exec($ch);
            curl_close($ch);
            $webhooks_called++;
        }

        // Return count
        return $webhooks_called;
    }
}
