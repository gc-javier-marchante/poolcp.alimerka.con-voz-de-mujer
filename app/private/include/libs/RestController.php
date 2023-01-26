<?php

/**
 * Class RestController.
 */
class RestController extends CRUDController
{
    protected $layout = false;
    protected $view = false;

    /**
     * @see Controller::$actionAccessLevel
     */
    protected $actionAccessLevel = [
        '*' => -1,
    ];

    /**
     * Constructor
     *
     * @param $name string
     */
    public function __construct($name = null)
    {
        $crudControllers = GestyMVC::config('crudControllers');
        GestyMVC::setConfig('crudControllers', GestyMVC::config('restControllers'));
        parent::__construct($name);
        GestyMVC::setConfig('crudControllers', $crudControllers);
    }

    /**
     * Allow only rest action
     *
     * @return bool
     */
    protected function beforeFilter()
    {
        if ($this->action != 'rest') {
            $this->notFound(false);
        }

        $this->authenticate();

        return parent::beforeFilter();
    }

    /** 
     * Get header Authorization
     * @return string|null
     */
    private function getAuthorizationHeader()
    {
        $authorization_header = null;

        if (isset($_SERVER['Authorization'])) {
            $authorization_header = trim($_SERVER['Authorization']);
        } else if (isset($_SERVER['HTTP_AUTHORIZATION'])) {
            // Nginx or fast CGI
            $authorization_header = trim($_SERVER['HTTP_AUTHORIZATION']);
        } elseif (function_exists('apache_request_headers')) {
            $requestHeaders = apache_request_headers();

            // Server-side fix for bug in old Android versions (a nice side-effect of this fix means we don't care about capitalization for Authorization)
            $requestHeaders = array_combine(array_map('ucwords', array_keys($requestHeaders)), array_values($requestHeaders));

            //print_r($requestHeaders);
            if (isset($requestHeaders['Authorization'])) {
                $authorization_header = trim($requestHeaders['Authorization']);
            }
        }

        return $authorization_header;
    }
    /**
     * Get Access token from header
     * @return string|null
     */
    private function getBearerToken()
    {
        $authorization_header = $this->getAuthorizationHeader();

        if (!empty($authorization_header)) {
            if (preg_match('/Bearer\s(\S+)/', $authorization_header, $matches)) {
                return $matches[1];
            }
        }

        return null;
    }

    /**
     * Check if current session if of logged user with username and password and not with token
     *
     * @return boolean
     */
    protected function isAjaxUsernameAndPasswordLoginRequest()
    {
        return Authentication::get('user', 'id')
            && !Authentication::get('user', 'is_api_secret_login')
            && $this->isAjaxRequest();
    }

    /**
     * Logs out user if its an api secret login
     *
     * @return void
     */
    protected function logoutApiSecretLoggedUser()
    {
        if (!$this->isAjaxUsernameAndPasswordLoginRequest()) {
            Session::destroy();
        }
    }

    /**
     * Authenticates with token
     * 
     * @param string $bearer
     * @return void
     */
    protected function authenticateWithToken($bearer)
    {
        if (!$bearer) {
            $this->forbidden(false);
        }

        /** @var User $User **/
        $User = MySQLModel::getRecycledInstance('User', [], $this);
        $user = $User->getByApiSecret($bearer, ['recursive' => -1]);

        if (!$user) {
            $this->forbidden(false);
        }

        $user['User']['is_api_secret_login'] = true;
        Authentication::set('user', $user['User']);
        $this->updateUserAcl(true);
    }

    /**
     * Authenticates user with bearer token
     *
     * @return void
     */
    protected function authenticate()
    {
        if ($this->isAjaxUsernameAndPasswordLoginRequest()) {
            // Ignore auth token if user is logged in with username
            // and password
            return;
        }

        $this->authenticateWithToken($this->getBearerToken());
    }

    /**
     * All requests are "ajax"
     *
     * @return boolean
     */
    protected function isAjaxRequest()
    {
        return true;
    }

    /**
     * Disable default override
     *
     * @return void
     */
    protected function modalOverridePagination()
    {
    }

    /**
     * Main filter
     *
     * @return string
     */
    protected function mainFilterField()
    {
        return 'id';
    }

    /**
     * REST main action
     *
     * @return void
     */
    public function rest()
    {
        $this->dispatchRestAction();
        $this->badRequest();
    }

    /**
     * REST action dispatcher. Dispatched actions must end on response (and exit)
     *
     * @return void
     */
    protected function dispatchRestAction()
    {
        if ($this->isGetRequest() && sizeof($this->params) == 0) {
            $this->restIndex();
        } elseif ($this->isGetRequest() && sizeof($this->params) == 1) {
            $_GET['filter'] = [$this->mainFilterField() => $this->params[0]];
            $this->restIndex(true);
        } elseif ($this->isPostRequest() && sizeof($this->params) == 0) {
            $this->restPost();
        } elseif ($this->isRequestMethod('PUT') && sizeof($this->params) == 1) {
            $this->restPut($this->params[0]);
        } elseif ($this->isRequestMethod('PATCH') && sizeof($this->params) == 1) {
            $this->restPatch($this->params[0]);
        }
    }

    /**
     * Throws a 404 error.
     *
     * @param $redirectToUrl bool|array|string whether or not to $this->redirect to the 404 page or the URL to redirect to
     * @param $reason
     */
    public function notFound($redirectToUrl = true, $reason = null)
    {
        $this->logoutApiSecretLoggedUser();
        parent::notFound(false, $reason);
    }

    /**
     * Throws a 403 error.
     *
     * @param $redirect bool whether or not to $this->redirect to the 403 page.
     */
    public function forbidden($redirect = true)
    {
        $this->logoutApiSecretLoggedUser();
        parent::forbidden(false);
    }

    /**
     * Throws a 400 error.
     */
    protected function badRequest()
    {
        $this->logoutApiSecretLoggedUser();
        parent::badRequest();
    }

    /**
     * Sanitizes where conditions
     *
     * @param array $options
     * @param array $where
     * @param boolean $decode
     * @return void
     */
    protected function sanitizeWhere(&$options, $where, $decode = true)
    {
        if (!$where) {
            return;
        }

        if ($options === false) {
            $options = [];
        }

        if (is_string($where) && $decode) {
            $where = @json_decode($where, true);

            if ($where === null) {
                $this->badRequest();
            }
        }

        if (!is_array($where)) {
            $this->badRequest();
        }

        foreach ($where as $field => &$value) {
            if ((is_int($field) || (is_string($field) && $field === strval(intval($field))))
                && is_array($value)
            ) {
                $newValue = false;
                $value = $this->sanitizeWhere($newValue, $value, false);
            } elseif (in_array($field, ['AND', 'OR'], true)) {
                $newValue = false;
                $value = $this->sanitizeWhere($newValue, $value, false);
            } elseif (
                is_string($field)
                && sizeof($fieldParts = explode(' ', $field)) < 3
                && !empty($this->model()->getConfig()['filter']['advanced'][$fieldParts[0]])
                && ($operator = '=')
                && (sizeof($fieldParts) == 1 || (($operator = $fieldParts[1]) && in_array($operator, ['>', '<', '>=', '<=', 'LIKE', 'NOT LIKE', 'IN', '=', '<>', '!=', 'NOT', 'NOT IN'], true)))
            ) {
                if (
                    is_bool($value)
                ) {
                    $value = ($value ? 1 : 0);
                } elseif (
                    is_array($value)
                ) {
                    if (in_array($operator, ['IN', '=', '<>', '!=', 'NOT', 'NOT IN'], true)) {
                        foreach ($value as $value_) {
                            if (
                                is_bool($value_)
                            ) {
                                $value_ = ($value_ ? 1 : 0);
                            } elseif (
                                !is_string($value_)
                                && !is_int($value_)
                                && !is_float($value_)
                                && !is_double($value_)
                            ) {
                                $this->badRequest();
                            }
                        }
                    } else {
                        $this->badRequest();
                    }
                } elseif (
                    !is_string($value)
                    && !is_int($value)
                    && !is_float($value)
                    && !is_double($value)
                    && !is_null($value)
                ) {
                    $this->badRequest();
                }
            } elseif (
                $field === 'q'
                && !empty($this->model()->getConfig()['filter']['simple'])
                && is_string($value)
            ) {
                $_GET['q'] = $value;
                $this->pagination['where'] = [];
                $this->setQFilter($this->model()->getConfig()['filter']['simple']);
                unset($_GET['q']);
                $value = $this->pagination['where'];
            } else {
                $this->badRequest();
            }
        }

        $options['where'] = $where;
        return $where;
    }

    /**
     * Sanitizes list of fields
     *
     * @param array $options
     * @param object $model
     * @param string|array $fields
     * @return void
     */
    protected function sanitizeFields(&$options, &$model, $fields)
    {
        if ($options === false) {
            $options = [];
        }

        $options['fields'] = [];
        $options['recursive'] = -1;

        $listOfValidBaseFields = ['id'];
        $listOfValidRelationshipFields = [];
        $options['rest']['format'] = [];
        $options['rest']['belongsTo'] = [];
        $options['rest']['hasMany'] = [];
        $options['rest']['habtm'] = [];
        $options['relationships']['belongsTo'] = [];
        $options['relationships']['belongsTo']['CreatedByUser'] = ['model' => 'User', 'foreign_key' => 'created_by_user_id'];
        $options['relationships']['belongsTo']['ModifiedByUser'] = ['model' => 'User', 'foreign_key' => 'modified_by_user_id'];
        $options['relationships']['hasMany'] = [];
        $options['relationships']['habtm'] = [];

        foreach ($model->getConfig()['fields'] as $field_name => $field) {
            $options['rest']['format'][$field_name] = $field['type'];
        }

        foreach (['belongsTo', 'hasMany', 'habtm'] as $relationship_type) {
            if (!empty($model->{$relationship_type})) {
                foreach (array_keys($model->{$relationship_type}) as $relationship_name) {
                    $options['relationships'][$relationship_type][$relationship_name] = $model->{$relationship_type}[$relationship_name];
                }
            }
        }

        foreach ($model->getConfig()['fields'] as $field_name => $field) {
            if (!empty($field['hide']['rest'])) {
                continue;
            }

            $listOfValidBaseFields[] = $field_name;
        }

        $listOfValidBaseFields[] = 'created';
        $listOfValidBaseFields[] = 'created_by_user_id';
        $listOfValidBaseFields[] = 'modified';
        $listOfValidBaseFields[] = 'modified_by_user_id';

        foreach ($options['relationships']['belongsTo'] as $relationship_name => $belongsToRelationship) {
            $options[$relationship_name] = false;
            $index = array_search($belongsToRelationship['foreign_key'], $listOfValidBaseFields);

            if ($index !== false) {
                unset($listOfValidBaseFields[$index]);
                $field_relationship_name = lcfirst($relationship_name);
                $options['rest']['belongsTo'][$field_relationship_name] = $relationship_name;
                $options['rest']['remove'][] = $options['relationships']['belongsTo'][$relationship_name]['foreign_key'];
                $listOfValidRelationshipFields[] = $field_relationship_name;
            }
        }

        foreach (array_keys($options['relationships']['hasMany']) as $relationship_name) {
            $field_relationship_name = Inflector::camelcase(Inflector::pluralize(Inflector::snakeCase($relationship_name)));
            $options['rest']['hasMany'][$field_relationship_name] = $relationship_name;
            $listOfValidRelationshipFields[] = $field_relationship_name;
            $index = array_search($field_relationship_name, $listOfValidBaseFields);

            if ($index !== false) {
                unset($listOfValidBaseFields[$index]);
            }
        }

        foreach (array_keys($options['relationships']['habtm']) as $relationship_name) {
            $options[$relationship_name] = false;
            $field_relationship_name = Inflector::camelcase(Inflector::pluralize(Inflector::snakeCase($relationship_name)));
            $options['rest']['habtm'][$field_relationship_name] = $relationship_name;
            $listOfValidRelationshipFields[] = $field_relationship_name;
            $index = array_search($field_relationship_name, $listOfValidBaseFields);

            if ($index !== false) {
                unset($listOfValidBaseFields[$index]);
            }
        }

        if (!$fields) {
            $fields = array_merge($listOfValidBaseFields, array_keys($options['rest']['belongsTo']));
        }

        if (is_string($fields)) {
            $fields = explode(',', $fields);
        }

        if (
            !is_array($fields)
            || !array_is_list($fields)
        ) {
            $this->badRequest();
        }

        $relationshipFields = [];

        foreach ($fields as $field_name) {
            $fieldParts = explode('.', $field_name);

            if (
                !in_array($fieldParts[0], $listOfValidBaseFields, true)
                && !in_array($fieldParts[0], $listOfValidRelationshipFields, true)
            ) {
                $this->badRequest();
            } elseif (sizeof($fieldParts) > 1) {
                if (in_array($fieldParts[0], $listOfValidBaseFields, true)) {
                    $this->badRequest();
                } else {
                    if (!empty($options['rest']['hasMany'][$fieldParts[0]])) {
                        if (!ACL\DbACL::canContentTypeAction($options['relationships']['hasMany'][$options['rest']['hasMany'][$fieldParts[0]]]['model'], 'List')) {
                            $this->forbidden(false);
                        }

                        $relationshipFields[$options['rest']['hasMany'][$fieldParts[0]]][] = substr($field_name, strlen($fieldParts[0]) + 1);
                    } elseif (!empty($options['rest']['habtm'][$fieldParts[0]])) {
                        if (!ACL\DbACL::canContentTypeAction($options['relationships']['habtm'][$options['rest']['habtm'][$fieldParts[0]]]['model'], 'List')) {
                            $this->forbidden(false);
                        }

                        $relationshipFields[$options['rest']['habtm'][$fieldParts[0]]][] = substr($field_name, strlen($fieldParts[0]) + 1);
                    } elseif (!empty($options['rest']['belongsTo'][$fieldParts[0]])) {
                        if (!ACL\DbACL::canContentTypeAction($options['relationships']['belongsTo'][$options['rest']['belongsTo'][$fieldParts[0]]]['model'], 'View')) {
                            $this->forbidden(false);
                        }

                        $relationshipFields[$options['rest']['belongsTo'][$fieldParts[0]]][] = substr($field_name, strlen($fieldParts[0]) + 1);
                    }
                }
            } elseif (!in_array($fieldParts[0], $listOfValidBaseFields, true)) {
                if (!empty($options['rest']['hasMany'][$fieldParts[0]])) {
                    if (!ACL\DbACL::canContentTypeAction($options['relationships']['hasMany'][$options['rest']['hasMany'][$fieldParts[0]]]['model'], 'List')) {
                        $this->forbidden(false);
                    }

                    $relationshipFields[$options['rest']['hasMany'][$fieldParts[0]]] = true;
                } elseif (!empty($options['rest']['habtm'][$fieldParts[0]])) {
                    if (!ACL\DbACL::canContentTypeAction($options['relationships']['habtm'][$options['rest']['habtm'][$fieldParts[0]]]['model'], 'List')) {
                        $this->forbidden(false);
                    }

                    $relationshipFields[$options['rest']['habtm'][$fieldParts[0]]] = true;
                } elseif (!empty($options['rest']['belongsTo'][$fieldParts[0]])) {
                    $relationshipFields[$options['rest']['belongsTo'][$fieldParts[0]]] = true;
                }
            } else {
                $options['fields'][] = $field_name;
            }
        }

        foreach ($relationshipFields as $relationship_name => $fields) {
            if (!empty($options['relationships']['belongsTo'][$relationship_name])) {
                if ($fields !== true) {
                    $options['recursive'] = max($options['recursive'], 0);

                    $belongsToModel = MySQLModel::getRecycledInstance($options['relationships']['belongsTo'][$relationship_name]['model'], [], $model, 'MySQLModel');
                    $this->sanitizeFields($options[$relationship_name], $belongsToModel, $fields);
                    $options['fields'][] = 'id';
                    $options['fields'][] = $options['relationships']['belongsTo'][$relationship_name]['foreign_key'];

                    if (!in_array('id', $options[$relationship_name]['fields'])) {
                        $options[$relationship_name]['fields'][] = 'id';
                    }
                } else {
                    $options['fields'][] = 'id';
                    $options['fields'][] = $options['relationships']['belongsTo'][$relationship_name]['foreign_key'];
                    $options[$relationship_name] = false;
                }
            } elseif (!empty($options['relationships']['hasMany'][$relationship_name])) {
                $options['recursive'] = max($options['recursive'], 1);

                if ($fields !== true) {
                    $hasManyModel = MySQLModel::getRecycledInstance($options['relationships']['hasMany'][$relationship_name]['model'], [], $model, 'MySQLModel');
                    $this->sanitizeFields($options[$relationship_name], $hasManyModel, $fields);
                    $options['fields'][] = 'id';

                    if (!in_array($options['relationships']['hasMany'][$relationship_name]['foreign_key'], $options[$relationship_name]['fields'])) {
                        $options[$relationship_name]['fields'][] = $options['relationships']['hasMany'][$relationship_name]['foreign_key'];
                    }
                } else {
                    $options[$relationship_name]['fields'] = ['id', $options['relationships']['hasMany'][$relationship_name]['foreign_key']];
                    $options[$relationship_name]['rest']['ids'] = true;
                }
            } elseif (!empty($options['relationships']['habtm'][$relationship_name])) {
                $options['recursive'] = max($options['recursive'], 1);

                if ($fields !== true) {
                    $habtmModel = MySQLModel::getRecycledInstance($options['relationships']['habtm'][$relationship_name]['model'], [], $model, 'MySQLModel');
                    $this->sanitizeFields($options[$relationship_name], $habtmModel, $fields);

                    if (!in_array('id', $options[$relationship_name]['fields'])) {
                        $options[$relationship_name]['fields'][] = 'id';
                    }
                } else {
                    $options[$relationship_name]['fields'] = ['id'];
                    $options[$relationship_name]['rest']['ids'] = true;
                }
            }
        }

        $options['fields'] = array_unique($options['fields']);
    }

    /**
     * Generates index options
     *
     * @return array
     */
    protected function indexOptions()
    {
        $options = false;
        $model = $this->model();
        $this->sanitizeWhere($options, get_var('filter', false));
        $this->sanitizeFields($options, $model, get_var('fields', false));

        foreach (['offset' => 0, 'limit' => 10,] as $int_option => $default_value) {
            if (get_var($int_option)) {
                if (is_int(get_var($int_option)) || (strval(intval(get_var($int_option))) === get_var($int_option))) {
                    $options[$int_option] = intval(get_var($int_option));

                    if ($options[$int_option] < 0) {
                        $this->badRequest();
                    } elseif (
                        $options[$int_option] == 0
                        && $int_option === 'limit'
                    ) {
                        $this->badRequest();
                    }
                } else {
                    $this->badRequest();
                }
            } else {
                $options[$int_option] = $default_value;
            }
        }

        return $options;
    }

    /**
     * Formats list of elements for rest
     *
     * @param string $main_name
     * @param array $elements
     * @param array $options
     * @return void
     */
    protected function indexFormatElements($main_name, &$elements, $options)
    {
        if (!empty($options['rest']['ids'])) {
            $ids = [];

            foreach ($elements as $element) {
                $main = (!empty($element[$main_name]) ? $element[$main_name] : $element);
                $ids[] = intval($main['id']);
            }

            $elements = $ids;
        } else {
            foreach ($elements as &$element) {
                $this->indexFormatElement($main_name, $element, $options);
            };
        }
    }

    /**
     * Formats elements for rest
     *
     * @param string $main_name
     * @param array $element
     * @param array $options
     * @return void
     */
    protected function indexFormatElement($main_name, &$element, $options)
    {
        $main = &$element;

        if (
            $main_name &&
            !empty($main[$main_name])
        ) {
            $main = &$main[$main_name];
        }

        $formattedFields = [];

        if (!empty($options['rest']['belongsTo'])) {
            foreach ($options['rest']['belongsTo'] as $field_name => $relationship_name) {
                if (array_key_exists($relationship_name, $element)) {
                    $main[$field_name] = $element[$relationship_name];
                    unset($element[$relationship_name]);

                    if (!is_null($main[$field_name])) {
                        $this->indexFormatElement(null, $main[$field_name], $options[$relationship_name]);
                    }
                } elseif (
                    !empty($options['rest']['belongsTo'][$field_name])
                    && !empty($options['relationships']['belongsTo'][$options['rest']['belongsTo'][$field_name]]['foreign_key'])
                ) {
                    $foreign_key = $options['relationships']['belongsTo'][$options['rest']['belongsTo'][$field_name]]['foreign_key'];

                    if (array_key_exists($foreign_key, $main)) {
                        $main[$field_name] = is_null($main[$foreign_key]) ? null : intval($main[$foreign_key]);
                        $formattedFields[] = $field_name;
                    } else {
                        unset($main[$field_name]);
                    }
                } else {
                    unset($main[$field_name]);
                }
            }
        }

        foreach (['habtm', 'hasMany'] as $relationship_type) {
            if (!empty($options['rest'][$relationship_type])) {
                foreach ($options['rest'][$relationship_type] as $field_name => $relationship_name) {
                    if (array_key_exists($relationship_name, $element)) {
                        $main[$field_name] = $element[$relationship_name];
                        unset($element[$relationship_name]);
                        $this->indexFormatElements(null, $main[$field_name], $options[$relationship_name]);
                    } else {
                        unset($main[$field_name]);
                    }
                }
            }
        }

        if (!empty($options['rest']['remove'])) {
            foreach ($options['rest']['remove'] as $field_name) {
                unset($main[$field_name]);
            }
        }

        foreach ($main as $field_name => &$value) {
            if (
                is_null($value)
                || is_array($value)
                || in_array($field_name, $formattedFields)
            ) continue;

            !empty($options['rest']['format']['id']) || ($options['rest']['format']['id'] = 'int');
            !empty($options['rest']['format']['created']) || ($options['rest']['format']['created'] = 'datetime');
            !empty($options['rest']['format']['created_by_user_id']) || ($options['rest']['format']['created_by_user_id'] = 'int');
            !empty($options['rest']['format']['modified']) || ($options['rest']['format']['modified'] = 'datetime');
            !empty($options['rest']['format']['modified_by_user_id']) || ($options['rest']['format']['modified_by_user_id'] = 'int');

            if (!empty($options['rest']['format'][$field_name])) {
                switch ($options['rest']['format'][$field_name]) {
                    case 'datetime':
                        $value = str_replace(' ', 'T', $value);
                        break;

                    case 'checkbox':
                        $value = !!$value;
                        break;

                    case 'int':
                    case 'select':
                        !is_string($value) || ($value = intval($value));
                        break;

                    case 'decimal':
                        !is_string($value) || ($value = doubleval($value));
                        break;
                }
            }

            $formattedFields[] = $field_name;
        }

        $element = $main;
    }

    /**
     * Lists items
     *
     * @param boolean $single_item
     * @return void
     */
    protected function restIndex($single_item = false)
    {
        $this->crud_action = 'list';

        if (!ACL\DbACL::canContentTypeAction($this->model_name, 'List')) {
            $this->forbidden(false);
        }

        $options = $this->indexOptions();

        if (
            !empty($options['relationships'])
            && is_array($options['relationships'])
        ) {
            foreach (['belongsTo', 'hasMany', 'habtm'] as $relationship_type) {
                if (!empty($options['relationships'][$relationship_type])) {
                    foreach ($options['relationships'][$relationship_type] as $relationship_name => $relationship) {
                        if (empty($this->model()->{$relationship_type}[$relationship_name])) {
                            $this->model()->{$relationship_type}[$relationship_name] = $relationship;
                        }
                    }
                }
            }
        }

        $elements = $this->model()->getAll($options);
        $this->indexFormatElements($this->model_name, $elements, $options);

        if ($single_item) {
            if (sizeof($elements) === 0) {
                $this->notFound();
            }

            $this->response($elements[0]);
        } else {
            $this->response(['data' => $elements]);
        }
    }

    /**
     * Echoes response
     *
     * @param array|object $object
     * @return void
     */
    protected function response($object)
    {
        $this->layout = false;
        $this->view = false;

        header('Content-Type: application/json');
        echo json_encode($object, JSON_UNESCAPED_SLASHES);

        $this->logoutApiSecretLoggedUser();
        exit();
    }

    /**
     * Generate save options
     *
     * @return array
     */
    protected function saveOptions()
    {
        $options = false;
        $model = $this->model();
        $this->sanitizeFields($options, $model, '');

        return $options;
    }

    /**
     * Revert format of list of elements for saving
     *
     */
    protected function saveUnformatElements(&$elements, $options)
    {
        foreach ($elements as &$element) {
            $this->saveUnformatElement($element, $options);
        }
    }

    /**
     * Revert of element format for saving
     *
     * @param array $main
     * @param array $options
     * @param bool $treat_missing_as_null
     * @return void
     */
    protected function saveUnformatElement(&$main, $options, $treat_missing_as_null = false)
    {
        if (!$main) {
            return;
        }

        $element = $main;

        if (!empty($options['rest']['belongsTo'])) {
            foreach ($options['rest']['belongsTo'] as $field_name => $relationship_name) {
                $foreign_key = $options['relationships']['belongsTo'][$options['rest']['belongsTo'][$field_name]]['foreign_key'];
                unset($element[$field_name]);

                if (array_key_exists($field_name, $main)) {
                    if (!is_null($main[$field_name])) {
                        if (is_array($main[$field_name])) {
                            $this->indexFormatElement($relationship_name, $main[$field_name], $options[$relationship_name]);
                            $element[$foreign_key] = $main[$field_name]['id'];
                        } else {
                            $element[$foreign_key] = $main[$field_name];
                        }
                    }
                } elseif ($treat_missing_as_null) {
                    $element[$foreign_key] = null;
                }
            }
        }

        if ($treat_missing_as_null) {
            foreach ($this->model()->getConfig()['fields'] as $field_name => $field) {
                if (!empty($field['hide']['rest'])) {
                    continue;
                }

                if (!array_key_exists($field_name, $element)) {
                    $element[$field_name] = null;
                }
            }
        }

        $main = $element;
    }

    /**
     * POST (insert)
     *
     * @param string $key
     * @return void
     */
    protected function restPost()
    {
        // Get JSON request
        $valueSets = $this->getPostedJson();

        // Data must be sent
        if (
            !isset($valueSets['data'])
            || empty($valueSets['data'])
            || !is_array($valueSets['data'])
            || !array_is_list($valueSets['data'])
        ) {
            $this->badRequest();
        }

        // Transform
        $valueSets = $valueSets['data'] ?? [];
        $this->saveUnformatElements($valueSets, $this->saveOptions());

        // Full result
        $results = [];

        foreach ($valueSets as $index => $values) {
            // If no values, bad request
            if (!$values) {
                $this->badRequest();
            }

            // Try to save
            $element = $this->model()->addNew($values, ['from_rest' => true]);

            // If success, return id
            if ($element) {
                $results['#' . $index] = [$this->mainFilterField() => $element[$this->model_name][$this->mainFilterField()]];
            } else {
                // Errors
                $errorsByField = [];

                // Add other errors
                foreach ($this->model()->errorsByField as $field => $error) {
                    if (empty($errorsByField[$field])) {
                        $errorsByField[$field] = [
                            'code' => $this->model()->errorCodesByField[$field] ?? 'invalid',
                            'message' => $error
                        ];
                    }
                }

                $results['#' . $index] = ['errors' => $errorsByField];
            }
        }

        // Full result
        $this->setHttpResponse(200);
        $this->response([
            'result' => $results,
        ]);
    }

    /**
     * Patch (update ignoring missing fields)
     *
     * @param string $key
     * @return void
     */
    protected function restPatch($key)
    {
        $this->restUpdate($key, false);
    }

    /**
     * PUT (update treating missing fields as null)
     *
     * @param string $key
     * @return void
     */
    protected function restPut($key)
    {
        $this->restUpdate($key, true);
    }

    /**
     * Saves PUT/PATCH (update) request
     *
     * @param string $key
     * @param bool $treat_missing_as_null
     * @return void
     */
    protected function restUpdate($key, $treat_missing_as_null)
    {
        // Retrieve element
        $element_id = $this->model()->field('id', [
            'where' => [
                $this->mainFilterField() => $key
            ]
        ]);

        // Check element
        if (!$element_id) {
            $this->notFound();
        }

        // Retrieve data
        $values = $this->getPostedJson();
        $this->saveUnformatElement($values, $this->saveOptions(), $treat_missing_as_null);

        // If no values, bad request
        if (!$values) {
            $this->badRequest();
        }

        // Try to save
        $element = $this->model()->updateFields($element_id, $values, ['from_rest' => true]);

        // If success, return id
        if ($element) {
            $this->response([$this->mainFilterField() => $element[$this->model_name][$this->mainFilterField()]]);
        }

        // Errors
        $errorsByField = [];

        // Add other errors
        foreach ($this->model()->errorsByField as $field => $error) {
            if (empty($errorsByField[$field])) {
                $errorsByField[$field] = [
                    'code' => $this->model()->errorCodesByField[$field] ?? 'invalid',
                    'message' => $error
                ];
            }
        }

        // If error, bad request
        $this->setHttpResponse(400);
        $this->response([
            'errors' => $errorsByField,
        ]);
    }
}
