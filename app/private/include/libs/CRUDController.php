<?php

/**
 * Class CRUDController.
 */
class CRUDController extends AppController
{
    /**
     * @see Controller::$actionAccessLevel
     */
    protected $actionAccessLevel = [
        '*' => 0,
    ];

    /**
     * Model name
     *
     * @var string
     */
    protected $model_name = null;

    /**
     * CRUD action
     *
     * @var string
     */
    protected $crud_action = null;

    /**
     * Redirects user to an URL.
     *
     * @param $options array|string options to be translated to Router::url
     * @param int $code
     */
    public function redirect($options, $code = 302)
    {
        if ($this->isAjaxRequest()) {
            $this->setHttpResponse(400);
            exit();
        }

        parent::redirect($options, $code);
    }

    /**
     * Constructor
     *
     * @param $name string
     */
    public function __construct($name = null)
    {
        parent::__construct($name);

        if (!$this->model_name) {
            if (array_key_exists($name, GestyMVC::config('crudControllers'))) {
                $this->model_name = GestyMVC::config('crudControllers')[$name];
            }
        }

        if (
            !$this->model_name
            || !$this->model()->tableExists()
        ) {
            $this->notFound();
        }
    }

    /**
     * Load model
     *
     * @param bool $recycled
     * @return AppMySQLModel
     */
    protected function model($recycled = true)
    {
        if ($recycled) {
            return MySQLModel::getRecycledInstance($this->model_name, [], $this);
        } else {
            return MySQLModel::getInstance($this->model_name);
        }
    }

    /**
     * Default post data
     *
     * @return mixed
     */
    protected function postData()
    {
        $data = [
            lcfirst($this->model_name) => [
                $this->model_name => post_var(lcfirst($this->model_name) . '[' . $this->model_name . ']')
            ]
        ];

        if ($data[lcfirst($this->model_name)][$this->model_name]) {
            if (!empty($this->model()->getConfig()['fields'])) {
                foreach ($this->model()->getConfig()['fields'] as $field) {
                    if ($field['type'] == 'datetime') {
                        if (post_var($field['name'] . '[date]') !== null || post_var($field['name'] . '[time]') !== null) {
                            NestedVariable::set($data, $field['name'], trim(post_var($field['name'] . '[date]') . ' ' . post_var($field['name'] . '[time]')));
                        }
                    }
                }
            }

            foreach ($data[lcfirst($this->model_name)][$this->model_name] as $field => $value) {
                if ($value === '') {
                    $data[lcfirst($this->model_name)][$this->model_name][$field] = null;
                }
            }
        }

        return $data[lcfirst($this->model_name)][$this->model_name];
    }

    /**
     * TH/TD for index
     *
     * @return void
     */
    protected function indexItemCells()
    {
        $ths = [];
        $tds = [];

        foreach ($this->model()->getConfig()['listFields'] as $field => $config) {
            if (get_var('excel')) {
                if (!empty($config['hide']['excel'])) {
                    continue;
                }
            } else {
                if (!empty($config['hide']['index'])) {
                    continue;
                }
            }

            $ths[$field] = [
                'text' => $config['label'],
                'order' => (in_array($field, $this->model()->validOrderByExpressions) ? $field : null),
            ];

            $tds[$field] = $config;
            unset($tds[$field]['label']);
        }

        $ths[] = [
            'class' => 'min-w-50px text-end'
        ];

        if (!$this->isAjaxRequest()) {
            $tds[] = [
                'type' => 'actions',
                'td_class' => 'text-end',
                'data_id' => ['deep' => $this->model_name . '.id'],
                'actions' => $this->indexItemActions()
            ];
        } else {
            $tds[] = [
                'type' => 'actions',
                'td_class' => 'text-end',
                'data_id' => ['deep' => $this->model_name . '.id'],
                'actions' => [
                    'item-select' => [
                        'active' => true,
                        'url' => 'javascript:void(0)',
                        'attributes' => 'data-select="' . get_var('habtm') . get_var('hasMany') . '"' . (!get_var('habtm') && !get_var('hasMany') ? ' data-select-close' : ''),
                        'label' => __('Select', true),
                    ]
                ]
            ];
        }

        return array(array_values($ths), array_values($tds));
    }

    /**
     * Overridable actions for item
     *
     * @return array
     */
    protected function indexItemActions()
    {
        return [
            'item-view' => [
                'active' => ACL\DbACL::canContentTypeAction($this->model_name, 'View'),
                'url' => ['controller' => $this->name, 'action' => 'view', '%id%'],
                'attributes' => '',
                'label' => __('Detail', true),
            ],
            'item-edit' => [
                'active' => ACL\DbACL::canContentTypeAction($this->model_name, 'Update'),
                'url' => ['controller' => $this->name, 'action' => 'edit', '%id%'],
                'attributes' => '',
                'label' => __('Edit', true),
            ],
            'item-move-up' => [
                'active' => ACL\DbACL::canContentTypeAction($this->model_name, 'Update') && $this->model()->hasBehaviour('Orderable') && !get_var('order_by'),
                'attributes' => 'data-gc-on-ajax="moveUp" data-gc-ajax="' . Router::url(['controller' => $this->name, 'action' => 'moveUp', '%id%']) . '"',
                'url' => 'javascript:void(0)',
                'label' => __('Move Up', true),
            ],
            'item-delete' => [
                'active' => ACL\DbACL::canContentTypeAction($this->model_name, 'Delete'),
                'attributes' => 'data-gc-on-ajax="remove" data-gc-confirm-action="' . Router::url(['controller' => $this->name, 'action' => 'delete', '%id%']) . '"',
                'url' => 'javascript:void(0)',
                'confirm' => sprintf(__('Are you sure you want to delete #%s?', true), '%id%'),
                'label' => __('Delete', true),
            ]
        ];
    }

    /**
     * Moves a record up
     * 
     * @param int $id
     */
    public function moveUp($id = null)
    {
        // Check access
        if (!ACL\DbACL::canContentTypeAction($this->model_name, 'Update')) {
            $this->forbidden(!$this->isAjaxRequest());
        }

        // Bad request if object does not support ordering
        if (!$this->model()->hasBehaviour('Orderable')) {
            $this->badRequest();
        }

        // Retrieve element
        $element = $this->model()->getById($id, ['recursive' => -1, 'fields' => ['id']]);

        // Return not found error if unexistant
        if (!$element) {
            $this->notFound(!$this->isAjaxRequest());
        }

        // Prepare response
        $this->layout = 'json';
        $this->view = false;
        $this->resultForLayout['response']['succeeded'] = false;

        if ($this->model()->moveUpById($id)) {
            // Set result
            $this->resultForLayout['response']['succeeded'] = true;
            $this->resultForLayout['response']['id'] = $element[$this->model_name]['id'];
        } else {
            // Set errors
            $error_message = __('Unable to move.', true);

            // Add retry message
            $error_message .= ' ' . __('Please try again.', true);
            $this->resultForLayout['error'] = $error_message;
            $modelErrorsByField[$this->model_name] = $this->model()->errorsByField;
            $this->resultForLayout['errorsByField'] = $modelErrorsByField;
        }
    }

    /**
     * Moves a related record up
     * 
     * @param int $id
     * @param string $relationship_name
     * @param int $habtm_id
     */
    public function moveHabtmUp($id = null, $relationship_name = null, $habtm_id = null)
    {
        // Check access
        if (!ACL\DbACL::canContentTypeAction($this->model_name, 'Update')) {
            $this->forbidden(!$this->isAjaxRequest());
        }

        // Bad request if relationship does not exist
        if (empty($this->model()->habtm[$relationship_name])) {
            $this->badRequest();
        }

        // Retrieve parent element
        $parentElement = $this->model()->getById($id, ['recursive' => -1, 'fields' => ['id']]);

        // Return not found error if unexistant
        if (!$parentElement) {
            $this->notFound(!$this->isAjaxRequest());
        }

        // Get relationship and model
        $relationship = $this->model()->habtm[$relationship_name];
        $RelationshipModel = MySQLModel::getRecycledInstance($relationship['association_model'], [], $this);

        // Bad request if  object does not support ordering
        if (!$RelationshipModel->hasBehaviour('Orderable')) {
            $this->badRequest();
        }

        // Retrieve element
        $element = $RelationshipModel->get([
            'recursive' => -1,
            'fields' => ['id'],
            'where' => [
                $relationship['foreign_key'] => $id,
                $relationship['association_foreign_key'] => $habtm_id,
            ]
        ]);

        // Return not found error if unexistant
        if (!$element) {
            $this->notFound(!$this->isAjaxRequest());
        }

        // Prepare response
        $this->layout = 'json';
        $this->view = false;
        $this->resultForLayout['response']['succeeded'] = false;

        if ($RelationshipModel->moveUpById($element[$RelationshipModel->name]['id'])) {
            // Set result
            $this->resultForLayout['response']['succeeded'] = true;
            $this->resultForLayout['response']['id'] = $element[$RelationshipModel->name]['id'];
        } else {
            // Set errors
            $error_message = __('Unable to move.', true);

            // Add retry message
            $error_message .= ' ' . __('Please try again.', true);
            $this->resultForLayout['error'] = $error_message;
            $modelErrorsByField[$RelationshipModel->name] = $RelationshipModel->errorsByField;
            $this->resultForLayout['errorsByField'] = $modelErrorsByField;
        }
    }

    /**
     * Overridable actions for detail
     *
     * @param array|object $element
     * @return array
     */
    protected function detailActions($element)
    {
        $actions = [];
        $actions['self-edit'] = [
            'active' => ACL\DbACL::canContentTypeAction($this->model_name, 'Update'),
            'url' => ['controller' => $this->name, 'action' => 'edit', $element[$this->model_name]['id']],
            'attributes' => '',
            'label' => __('Edit', true),
        ];

        foreach ($this->model()->hasMany as $relationship_name => $hasManyRelationShip) {
            $related_model = $hasManyRelationShip['model'];
            $actions[$relationship_name . '-add'] = [
                'active' => ACL\DbACL::canContentTypeAction($related_model, 'Create') && (!empty($hasManyRelationShip['crud']['view']['create'])),
                'attributes' => 'data-open-modal="' . Router::url([
                    'controller' => $this->getControllerNameByModelName($related_model),
                    'action' => 'add',
                    '?' => [
                        'hasMany' => $this->model_name . '#' . $relationship_name,
                        'has_many_id' =>  $element[$this->model_name]['id'],
                        'callback' => 'reload',
                    ],
                ]) . '"',
                'url' => 'javascript:void(0)',
                'label' => sprintf(__('Add %s', true), __(Inflector::lowercaseName(Inflector::snakeCase($relationship_name, ' ')), true)),
            ];
        }

        $actions['self-delete'] = [
            'active' => ACL\DbACL::canContentTypeAction($this->model_name, 'Delete'),
            'attributes' => 'data-gc-on-ajax-redirect="' . Router::url(['controller' => $this->name, 'action' => 'index']) . '" data-gc-confirm-action="' . Router::url(['controller' => $this->name, 'action' => 'delete', $element[$this->model_name]['id']]) . '"',
            'url' => 'javascript:void(0)',
            'confirm' => sprintf(__('Are you sure you want to delete #%s?', true), $element[$this->model_name]['id']),
            'label' => __('Delete', true),
        ];

        return $actions;
    }

    /**
     * Overridable actions for edit
     *
     * @param array|object $element
     * @return array
     */
    protected function editActions($element)
    {
        return [
            'self-exit-edit' => [
                'active' => ACL\DbACL::canContentTypeAction($this->model_name, 'View') && ($this->model()->habtm || $this->model()->hasMany),
                'url' => ['controller' => $this->name, 'action' => 'view', $element[$this->model_name]['id']],
                'attributes' => '',
                'label' => __('Finish editing', true),
                'type' => 'secondary',
            ],
            'self-back-to-list' => [
                'active' => ACL\DbACL::canContentTypeAction($this->model_name, 'List') && !get_var('hasMany') && !get_var('habtm'),
                'url' => ['controller' => $this->name, 'action' => 'index'],
                'attributes' => '',
                'label' => __('Back to list', true),
                'type' => 'secondary',
            ],
        ];
    }

    /**
     * Custom pagination for modal index
     *
     * @return void
     */
    protected function modalOverridePagination()
    {
        $this->pagination['elements_per_page'] = min($this->pagination['elements_per_page'], 5);
    }

    /**
     * Listing
     */
    public function index()
    {
        $this->crud_action = 'list';

        if (!ACL\DbACL::canContentTypeAction($this->model_name, 'List')) {
            $this->forbidden(!$this->isAjaxRequest());
        }

        $this->pagination['allow_default_order'] = true;
        $this->pagination['allow_default_page'] = true;

        $this->set('can_export', ACL\DbACL::canContentTypeAction($this->model_name, 'Export'));

        // Pagination config
        $this->pagination['model'] = $this->model();
        $this->pagination['where'] = [];
        $this->setIndexFilters();

        $this->view = 'elements/list/index';

        if (get_var('excel')) {
            if (!ACL\DbACL::canContentTypeAction($this->model_name, 'Export')) {
                $this->forbidden(!$this->isAjaxRequest());
            }

            unset($_GET['p']);
            $this->layout = 'excel';
            $this->pagination['elements_per_page'] = 1000000;
            $this->view = 'elements/list/excel';
            $this->set('filename_for_layout', __(Inflector::lowercaseName(Inflector::pluralize(Inflector::snakeCase($this->model_name, ' '))), true) . '.xls');
        }

        if ($this->isAjaxRequest()) {
            $this->modalOverridePagination();
        }

        $elements = $this->paginate();

        list($ths, $tds) = $this->indexItemCells();

        $this->set([
            'url_no_filters' => Router::url(['action' => 'index', '?' => array_filter(['habtm' => get_var('habtm'), 'hasMany' => get_var('hasMany')])]),
            'breadcrumbs' => [
                'title' => __(Inflector::lowercaseName(Inflector::pluralize(Inflector::snakeCase($this->model_name, ' '))), true),
                'title_short' => __(Inflector::lowercaseName(Inflector::pluralize(Inflector::snakeCase($this->model_name, ' '))), true),
                'items' => [[
                    'title' => __('Admin', true),
                    'href' => null,
                ]],
            ],
            'actions' => [
                'add' => [
                    'active' => ACL\DbACL::canContentTypeAction($this->model_name, 'Create') && !get_var('habtm') && !get_var('hasMany'),
                    'url' => ['controller' => $this->name, 'action' => 'add'],
                    'attributes' => '',
                    'label' => __('Add', true),
                ]
            ],
            'search' => $this->model()->getConfig()['filter'],
            'list' => [
                'download_mode' => !!get_var('excel'),
                'download_name' => __(Inflector::lowercaseName(Inflector::pluralize(Inflector::snakeCase($this->model_name, ' '))), true),
                'elements' => $elements,
                'header' => [
                    'class' => '',
                    'ths' => $ths,
                ],
                'rows' => [
                    'data_row_id' => ['deep' => $this->model_name . '.id'],
                    'class' => '',
                    'tds' => $tds,
                ],
            ]
        ]);

        if ($this->isAjaxRequest()) {
            $this->pagination['elements_per_page'] = max($this->pagination['elements_per_page'], 5);
            $this->layout = 'modal';
            $this->set([
                'is_modal' => true,
                'breadcrumbs' => [
                    'title' => __(Inflector::lowercaseName(Inflector::pluralize(Inflector::snakeCase($this->model_name, ' '))), true)
                ]
            ]);
        }
    }

    /**
     * Sets and reads all filter configuration for index
     * @return void
     */
    protected function setIndexFilters()
    {
        $filtered_elements = !empty($this->viewVars['filtered_elements']);
        $this->setQFilter($this->model()->getConfig()['filter']['simple']) && ($filtered_elements = true);

        $belongsToLists = [];
        $searchFieldSelectList = [];

        if (!empty($this->model()->getConfig()['filter']['advanced'])) {
            foreach ($this->model()->getConfig()['filter']['advanced'] as $field => $filterOptions) {
                if ($filterOptions['type'] == 'daterange') {
                    if (!isset($filterOptions['default']) || $filterOptions['default']) {
                        $this->setMinDateFilter($field . '_from', $field) && ($filtered_elements = true);
                        $this->setMaxDateFilter($field . '_to', $field) && ($filtered_elements = true);
                    }
                } elseif ($field === 'url') {
                    if (!isset($filterOptions['default']) || $filterOptions['default']) {
                        if (!empty($filterOptions['partial'])) {
                            $this->setQFilter(['url'], '_url') && ($filtered_elements = true);
                        } else {
                            $this->setExactValueFilter('_url', false, '`url` = %value%') && ($filtered_elements = true);
                        }
                    }
                } else {
                    if (!isset($filterOptions['default']) || $filterOptions['default']) {
                        if (!empty($filterOptions['partial'])) {
                            $this->setQFilter([$field], $field) && ($filtered_elements = true);
                        } else {
                            $this->setExactValueFilter($field) && ($filtered_elements = true);
                        }
                    }

                    if ($filterOptions['type'] == 'select') {
                        if (!empty($filterOptions['list']['model'])) {
                            $list_name = 'listOf' . Inflector::pascalcase(Inflector::pluralize(Inflector::snakeCase($filterOptions['list']['model'])));
                            $searchFieldSelectList[$field] = $list_name;

                            if (
                                isset($this->viewVars[$list_name])
                                || isset($belongsToLists[$list_name])
                            ) {
                                continue;
                            }

                            if (empty($filterOptions['list']['options'])) {
                                $filterOptions['list']['options'] = [];
                            }

                            /** @var AppMySQLModel $AppMySQLModel **/
                            $ListModel = MySQLModel::getRecycledInstance($filterOptions['list']['model'], [], $this);
                            $belongsToLists[$list_name] = $ListModel->getList('id', $ListModel->displayFields, $filterOptions['list']['options']);
                        }
                    }
                }
            }
        }

        $this->set($belongsToLists);
        $this->set([
            'searchFieldSelectList' => $searchFieldSelectList,
            'filtered_elements' => $filtered_elements
        ]);
    }

    /**
     * Add
     */
    public function add()
    {
        $this->crud_action = 'create';

        if (!ACL\DbACL::canContentTypeAction($this->model_name, 'Create')) {
            $this->forbidden(!$this->isAjaxRequest());
        }

        if (
            $this->isAjaxRequest()
            && $this->isPostRequest()
        ) {
            // Prepare response
            $this->layout = 'json';
            $this->view = false;
            $this->resultForLayout['response']['succeeded'] = false;

            // Prepare data
            $save = true;
            $data = $this->postData();
            unset($data['id']);
            $callback_override = false;

            if (get_var('hasMany') && get_var('has_many_id')) {
                $parent_model = explode('#', get_var('hasMany'))[0];
                $parent_model_relationship_name = explode('#', get_var('hasMany'))[1];
                $parentModel = MySQLModel::getRecycledInstance($parent_model, [], $this);

                if (!empty($parentModel->hasMany[$parent_model_relationship_name])) {
                    $data[$parentModel->hasMany[$parent_model_relationship_name]['foreign_key']] = get_var('has_many_id');

                    if (!empty($parentModel->hasMany[$parent_model_relationship_name]['crud']['on']['create'])) {
                        $callback_override = $parentModel->hasMany[$parent_model_relationship_name]['crud']['on']['create'];
                    }
                } else {
                    unset($_GET['hasMany']);
                }
            }

            $save = $save && $this->addMedia($data);
            $element = $save ? $this->model()->addNew($data) : false;

            if ($element) {
                // Set result
                $this->resultForLayout['response']['succeeded'] = true;

                if (
                    ACL\DbACL::canContentTypeAction($this->model_name, 'Update')
                    && ($this->model()->habtm || $this->model()->hasMany)
                ) {
                    $this->resultForLayout['response']['redirect_to'] = Router::url(['controller' => $this->name, 'action' => 'edit', $element[$this->model_name]['id']]);
                } elseif (ACL\DbACL::canContentTypeAction($this->model_name, 'View')) {
                    $this->resultForLayout['response']['redirect_to'] = Router::url(['controller' => $this->name, 'action' => 'view', $element[$this->model_name]['id']]);
                } else {
                    $this->resultForLayout['response']['redirect_to'] = Router::url(['controller' => $this->name, 'action' => 'index']);
                }

                if (
                    get_var('hasMany')
                    || get_var('habtm')
                ) {
                    unset($this->resultForLayout['response']['redirect_to']);
                    $this->resultForLayout['response']['message'] = __('Element saved.', true);

                    if ($callback_override == 'edit' && ACL\DbACL::canContentTypeAction($this->model_name, 'Update')) {
                        $this->resultForLayout['response']['redirect_to'] = Router::url(['controller' => $this->name, 'action' => 'edit', $element[$this->model_name]['id']]);
                    } elseif (in_array($callback_override, ['view', 'edit']) && ACL\DbACL::canContentTypeAction($this->model_name, 'View')) {
                        $this->resultForLayout['response']['redirect_to'] = Router::url(['controller' => $this->name, 'action' => 'view', $element[$this->model_name]['id']]);
                    } else if (get_var('callback') === 'reload') {
                        $this->resultForLayout['response']['callback'] = 'reload';
                    } else {
                        $this->resultForLayout['response']['callback'] = 'addToList';
                        $this->resultForLayout['response']['addToList'] = [
                            'list' => get_var('hasMany') . get_var('habtm'),
                            'id' => $element[$this->model_name]['id'],
                            'close' => true
                        ];
                    }
                }

                $this->resultForLayout['response']['id'] = $element[$this->model_name]['id'];
            } else {
                // Set errors
                $error_message = __('Unable to save.', true);

                // Add retry message
                $error_message .= ' ' . __('Please try again.', true);
                $this->resultForLayout['error'] = $error_message;
                $this->resultForLayout['errorsByField'] = [$this->model_name => $this->model()->errorsByField];
            }
        } else {
            if (get_var('hasMany') && get_var('has_many_id')) {
                $parent_model = explode('#', get_var('hasMany'))[0];
                $parent_model_relationship_name = explode('#', get_var('hasMany'))[1];
                $parentModel = MySQLModel::getRecycledInstance($parent_model, [], $this);

                if (!empty($parentModel->hasMany[$parent_model_relationship_name])) {
                    foreach ($this->model()->getConfig()['fields'] as $field_name => &$field) {
                        if ($field_name == $parentModel->hasMany[$parent_model_relationship_name]['foreign_key']) {
                            $field['hide']['create'] = true;
                        }
                    }
                }
            }

            $this->set([
                'is_create' => true,
                'breadcrumbs' => [
                    'title' => sprintf(__('Add %s', true), __(Inflector::lowercaseName(Inflector::snakeCase($this->model_name, ' ')), true)),
                    'title_short' => __('Add', true),
                    'items' => [[
                        'title' => __(Inflector::lowercaseName(Inflector::pluralize(Inflector::snakeCase($this->model_name, ' '))), true),
                        'href' => ['controller' => $this->name, 'action' => 'index'],
                    ], [
                        'title' => __('Admin', true),
                        'href' => null,
                    ]],
                ]
            ]);

            // View
            $this->view = 'elements/form/full-width-form';
            $this->set('fields', $this->model()->getConfig()['fields']);
            $this->loadLists();

            if ($this->isAjaxRequest()) {
                $this->layout = 'modal';
                $this->set('hide_footer', true);
                $this->set([
                    'is_modal' => true,
                    'breadcrumbs' => [
                        'title' => $this->viewVars['breadcrumbs']['title']
                    ]
                ]);
            }
        }
    }

    /**
     * View
     * 
     * @param int $id
     */
    public function view($id = null)
    {
        $this->crud_action = 'read';
        $this->editOrView($id);
    }

    /**
     * Edit
     */
    public function edit($id = null)
    {
        $this->crud_action = 'update';
        $this->editOrView($id);
    }

    /**
     * Edit or view detail
     * 
     * @param int $id
     */
    protected function editOrView($id = null)
    {
        $this->set('active_menu_href', ['action' => 'index']);

        if ($this->crud_action == 'read') {
            if (!ACL\DbACL::canContentTypeAction($this->model_name, 'View')) {
                $this->forbidden(!$this->isAjaxRequest());
            }
        } elseif (!ACL\DbACL::canContentTypeAction($this->model_name, 'Update')) {
            $this->forbidden(!$this->isAjaxRequest());
        }

        $element = $this->model()->getById($id, ['recursive' => 1]);

        if (!$element) {
            $this->notFound(!$this->isAjaxRequest());
        }

        $this->set('crud_parent_record_id', $element[$this->model_name]['id']);

        if (
            $this->isAjaxRequest()
            && $this->crud_action == 'update'
            && $this->isPostRequest()
        ) {
            // Prepare response
            $this->layout = 'json';
            $this->view = false;
            $this->resultForLayout['response']['succeeded'] = false;

            // Prepare data
            $this->model()->beginTransaction();
            $save = true;
            $data = $this->postData();
            unset($data['id']);
            $save = $save && $this->addMedia($data);

            if (get_var('hasMany') && get_var('has_many_id')) {
                $parent_model = explode('#', get_var('hasMany'))[0];
                $parent_model_relationship_name = explode('#', get_var('hasMany'))[1];
                $parentModel = MySQLModel::getRecycledInstance($parent_model, [], $this);

                if (!empty($parentModel->hasMany[$parent_model_relationship_name])) {
                    unset($data[$parentModel->hasMany[$parent_model_relationship_name]['foreign_key']]);
                }
            }

            if ($save) {
                if (
                    empty($data)
                    && empty($this->originalPost[lcfirst($this->model_name)][$this->model_name])
                ) {
                    // Nothing was sent
                    $element = [$this->model_name => $element[$this->model_name]];
                } elseif (!empty($data)) {
                    $element = $this->model()->updateFields($id, $data);
                } else {
                    $element = false;
                }
            } else {
                $element = false;
            }

            $modelErrorsByField = [];

            if ($element) {
                if ($this->model()->hasMany || $this->model()->habtm) {
                    foreach ($this->model()->habtm as $relationship_name => $habtmRelationShip) {
                        if (!ACL\DbACL::canContentTypeAction($habtmRelationShip['model'], 'List')) {
                            continue;
                        }

                        if (
                            $save
                            && !empty($this->originalPost['habtm'])
                            && is_array($this->originalPost['habtm'])
                            && in_array($relationship_name, $this->originalPost['habtm'])
                        ) {
                            $postedIds = [];

                            if (!empty($this->originalPost[lcfirst($this->model_name)][$relationship_name])) {
                                $postedIds = $this->originalPost[lcfirst($this->model_name)][$relationship_name];
                            }

                            /** @var AppMySQLModel $AppMySQLModel **/
                            $Model = MySQLModel::getRecycledInstance($habtmRelationShip['association_model'], [], $this);

                            if (!$Model->deleteAll(['where' => [
                                $habtmRelationShip['foreign_key'] => $element[$this->model_name]['id'],
                                $habtmRelationShip['association_foreign_key'] . ' <>' => $postedIds,
                            ]])) {
                                $element = false;
                                break;
                            }

                            $existingIds = array_values($Model->getList($habtmRelationShip['association_foreign_key'], $habtmRelationShip['association_foreign_key'], ['where' => [$habtmRelationShip['foreign_key'] => $element[$this->model_name]['id']]]));

                            foreach (array_diff($postedIds, $existingIds) as $habtm_id) {
                                if (!$Model->addNew([
                                    $habtmRelationShip['foreign_key'] => $element[$this->model_name]['id'],
                                    $habtmRelationShip['association_foreign_key'] => $habtm_id,
                                ])) {
                                    $modelErrorsByField[$relationship_name] = $Model->errorsByField;
                                    $element = false;
                                    break;
                                }
                            }
                        }
                    }

                    foreach ($this->model()->hasMany as $relationship_name => $hasManyRelationShip) {
                        if (!ACL\DbACL::canContentTypeAction($hasManyRelationShip['model'], 'Update')) {
                            continue;
                        }

                        if (
                            $save
                            && !empty($this->originalPost['hasMany'])
                            && is_array($this->originalPost['hasMany'])
                            && in_array($relationship_name, $this->originalPost['hasMany'])
                        ) {
                            $postedIds = [];

                            if (!empty($this->originalPost[lcfirst($this->model_name)][$relationship_name])) {
                                $postedIds = $this->originalPost[lcfirst($this->model_name)][$relationship_name];
                            }

                            /** @var AppMySQLModel $AppMySQLModel **/
                            $Model = MySQLModel::getRecycledInstance($hasManyRelationShip['model'], [], $this);

                            if (!empty($Model->validation[$hasManyRelationShip['foreign_key']]['notempty'])) {
                                // If field is required, delete
                                if (!$Model->deleteAll(['where' => [
                                    $hasManyRelationShip['foreign_key'] => $element[$this->model_name]['id'],
                                    'id' . ' <>' => $postedIds,
                                ]])) {
                                    $modelErrorsByField[$relationship_name] = $Model->errorsByField;
                                    $element = false;
                                    break;
                                }
                            } else {
                                // If field is not required, update
                                if (!$Model->updateAll(['where' => [
                                    $hasManyRelationShip['foreign_key'] => $element[$this->model_name]['id'],
                                    'id' . ' <>' => $postedIds,
                                ]], [
                                    $hasManyRelationShip['foreign_key'] => null
                                ])) {
                                    $element = false;
                                    break;
                                }
                            }

                            $existingIds = array_values($Model->getList('id', 'id', ['where' => [$hasManyRelationShip['foreign_key'] => $element[$this->model_name]['id']]]));

                            foreach (array_diff($postedIds, $existingIds) as $has_many_id) {
                                if (!$Model->updateFields($has_many_id, [
                                    $hasManyRelationShip['foreign_key'] => $element[$this->model_name]['id'],
                                ])) {
                                    $element = false;
                                    break;
                                }
                            }
                        }
                    }
                }
            }

            if ($element) {
                // Transaction
                $this->model()->commitTransaction();

                // Set result
                $this->resultForLayout['response']['succeeded'] = true;
                $this->resultForLayout['response']['message'] = __('Changes saved.', true);

                if (
                    !$this->model()->hasMany
                    && !$this->model()->habtm
                ) {
                    if (ACL\DbACL::canContentTypeAction($this->model_name, 'View')) {
                        $this->resultForLayout['response']['redirect_to'] = Router::url(['controller' => $this->name, 'action' => 'view', $element[$this->model_name]['id']]);
                    } else {
                        $this->resultForLayout['response']['redirect_to'] = Router::url(['controller' => $this->name, 'action' => 'index']);
                    }
                }

                if (
                    get_var('hasMany')
                    || get_var('habtm')
                ) {
                    unset($this->resultForLayout['response']['redirect_to']);
                    $this->resultForLayout['response']['message'] = __('Element saved.', true);
                    $this->resultForLayout['response']['callback'] = 'addToList';
                    $this->resultForLayout['response']['addToList'] = [
                        'list' => get_var('hasMany') . get_var('habtm'),
                        'id' => $element[$this->model_name]['id'],
                        'replace' => true,
                        'close' => true,
                    ];
                }

                $this->resultForLayout['response']['id'] = $element[$this->model_name]['id'];
            } else {
                // Transaction
                $this->model()->rollbackTransaction();

                // Set main model errors
                $modelErrorsByField[$this->model_name] = $this->model()->errorsByField;

                // Set errors
                $error_message = __('Unable to save.', true);

                if (empty($modelErrorsByField[$this->model_name])) {
                    $error_message .= ' ' . implode(' ', array_map(function ($errorsByField_) {
                        return implode(' ', $errorsByField_);
                    }, $modelErrorsByField));
                }

                // Add retry message
                $error_message .= ' ' . __('Please try again.', true);
                $this->resultForLayout['error'] = $error_message;
                $this->resultForLayout['errorsByField'] = $modelErrorsByField;
            }
        } else {
            if ($this->crud_action == 'read') {
                $this->set([
                    'is_view' => true,
                    'breadcrumbs' => [
                        'title' => sprintf(__('%s Details', true), __(Inflector::lowercaseName(Inflector::snakeCase($this->model_name, ' ')), true)),
                        'title_short' => __('Detail', true),
                        'items' => [[
                            'title' => __(Inflector::lowercaseName(Inflector::pluralize(Inflector::snakeCase($this->model_name, ' '))), true),
                            'href' => ['controller' => $this->name, 'action' => 'index'],
                        ], [
                            'title' => __('Admin', true),
                            'href' => null,
                        ]],
                    ],
                    'actions' => $this->detailActions($element),
                ]);
            } else {
                $this->set([
                    'is_update' => true,
                    'breadcrumbs' => [
                        'title' => sprintf(__('Edit %s', true), __(Inflector::lowercaseName(Inflector::snakeCase($this->model_name, ' ')), true)),
                        'title_short' => __('Edit', true),
                        'items' => [[
                            'title' => __(Inflector::lowercaseName(Inflector::pluralize(Inflector::snakeCase($this->model_name, ' '))), true),
                            'href' => ['controller' => $this->name, 'action' => 'index'],
                        ], [
                            'title' => __('Admin', true),
                            'href' => null,
                        ]],
                    ],
                    'actions' => $this->editActions($element),
                ]);
            }

            $this->set('can_view', ACL\DbACL::canContentTypeAction($this->model_name, 'View'));
            $this->set('element_id', $element[$this->model_name]['id']);
            $this->set(lcfirst($this->model_name), $element);
            $this->set('_element_var_name', lcfirst($this->model_name));
            $this->loadLists();
            $this->setEditView($element);

            if ($this->isAjaxRequest()) {
                if (get_var('hasMany') && get_var('has_many_id')) {
                    $parent_model = explode('#', get_var('hasMany'))[0];
                    $parent_model_relationship_name = explode('#', get_var('hasMany'))[1];
                    $parentModel = MySQLModel::getRecycledInstance($parent_model, [], $this);

                    if (!empty($parentModel->hasMany[$parent_model_relationship_name])) {
                        foreach ($this->model()->getConfig()['fields'] as $field_name => &$field) {
                            if ($field_name == $parentModel->hasMany[$parent_model_relationship_name]['foreign_key']) {
                                $field['hide']['update'] = true;
                            }
                        }
                    }
                }

                $this->layout = 'modal';
                $this->set([
                    'is_modal' => true,
                    'hide_footer' => true,
                    'breadcrumbs' => [
                        'title' => __(Inflector::lowercaseName(Inflector::pluralize(Inflector::snakeCase($this->model_name, ' '))), true)
                    ]
                ]);
            }

            $this->set('fields', $this->model()->getConfig()['fields']);
        }
    }

    /**
     * Sets the view
     * 
     * @param array $element
     * @return void
     */
    protected function setEditView($element)
    {
        if ($this->crud_action == 'read') {
            $this->view = 'elements/view/full-width-view';
        } else {
            $this->view = 'elements/form/full-width-form';
        }

        if (
            !get_var('hasMany')
            && ($this->model()->hasMany || $this->model()->habtm)
        ) {
            $this->view = 'elements/table-of-contents/table';
            if ($this->crud_action == 'read') {
                $contents = [
                    ['title' => sprintf(__('%s Data', true), __(Inflector::lowercaseName(Inflector::snakeCase($this->model_name, ' ')), true)), 'hash' => 'basic', 'view' => 'elements/view/view'],
                ];
            } else {
                $contents = [
                    ['title' => sprintf(__('%s Data', true), __(Inflector::lowercaseName(Inflector::snakeCase($this->model_name, ' ')), true)), 'hash' => 'basic', 'view' => 'elements/form/form'],
                ];
            }

            foreach ($this->model()->habtm as $relationship_name => $habtmRelationShip) {
                if (!ACL\DbACL::canContentTypeAction($habtmRelationShip['model'], 'List')) {
                    continue;
                }

                if ($content = $this->setEditViewForHabtm($element, $relationship_name, $habtmRelationShip)) {
                    $contents[] = $content;
                }
            }

            foreach ($this->model()->hasMany as $relationship_name => $hasManyRelationShip) {
                if (!ACL\DbACL::canContentTypeAction($hasManyRelationShip['model'], 'List')) {
                    continue;
                }

                if ($content = $this->setEditViewForHasMany($element, $relationship_name, $hasManyRelationShip)) {
                    $contents[] = $content;
                }
            }

            $this->set('contents', $contents);
        }
    }

    /**
     * Renders a single line for a HABTM item
     *
     * @param $string $relationship_name
     * @return void
     */
    public function renderHabtmLineItem($relationship_name)
    {
        $this->crud_action = 'update';

        if (!ACL\DbACL::canContentTypeAction($this->model_name, 'Update')) {
            $this->forbidden(!$this->isAjaxRequest());
        }

        if (
            empty($this->model()->habtm[$relationship_name])
            || !get_var('id')
        ) {
            $this->notFound(false);
        }

        $habtmRelationShip = $this->model()->habtm[$relationship_name];

        if (!ACL\DbACL::canContentTypeAction($habtmRelationShip['model'], 'List')) {
            $this->forbidden(!$this->isAjaxRequest());
        }

        if (empty($habtmRelationShip['options'])) {
            $habtmRelationShip['options'] = [];
        }

        /** @var AppMySQLModel $Model **/
        $Model = MySQLModel::getRecycledInstance($habtmRelationShip['model'], [], $this);
        $habtmElement = $Model->getById(get_var('id'), $habtmRelationShip['options']);

        if (!$habtmElement) {
            $this->notFound(false);
        }

        if (is_a($habtmElement, GestyMVC\Model\Element::class)) {
            $habtmElement->onRelationship();
        } else {
            $habtmElement = array_merge($habtmElement[$habtmRelationShip['model']], $habtmElement);
            unset($habtmElement[$habtmRelationShip['model']]);
        }

        $data = $this->setEditViewForHabtm(null, $relationship_name, $habtmRelationShip, [$habtmElement]);
        $this->set($data['list']);
        $this->view = 'elements/list/table';
        $this->layout = 'json';
    }

    /**
     * Sets the required data for editing a HABTM list
     *
     * @param array $element
     * @param string $relationship_name
     * @param array $habtmRelationShip
     * @param array $habtmElements
     * @return array
     */
    protected function setEditViewForHabtm($element, $relationship_name, $habtmRelationShip, $habtmElements = null)
    {
        if ($habtmElements === null) {
            $habtmElements = $this->viewVars[lcfirst($this->model_name)][$relationship_name];
        }

        if ($this->crud_action == 'read' && !$habtmElements) {
            return false;
        }

        /** @var AppMySQLModel $Model **/
        $Model = MySQLModel::getRecycledInstance($habtmRelationShip['model'], [], $this);

        $ths = [['class' => 'd-none']];
        $tds = [[
            'td_class' => 'd-none',
            'type' => 'hidden',
            'input' => [
                'name' => lcfirst($this->model_name) . '[' . $relationship_name . '][]',
                'value' => ['deep' => $habtmRelationShip['model'] . '.id'],
            ],
        ]];

        foreach ($Model->getConfig()['listFields'] as $field => $config) {
            if (!empty($config['hide']['habtm'])) {
                continue;
            }

            $ths[$field] = [
                'text' => $config['label'],
                'order' => false,
            ];

            $tds[$field] = $config;
            unset($tds[$field]['label']);
        }

        $habtm_controller_name = $this->getControllerNameByModelName($habtmRelationShip['model']);

        $ths[] = [
            'class' => 'min-w-50px text-end'
        ];
        $tds[] = [
            'type' => 'actions',
            'td_class' => 'text-end',
            'data_id' => ['deep' => $habtmRelationShip['model'] . '.id'],
            'actions' => [
                'item-detail' => [
                    'active' => (($this->crud_action == 'read') && ACL\DbACL::canContentTypeAction($habtmRelationShip['model'], 'View') && (!isset($habtmRelationShip['crud']['view']['view']) || $habtmRelationShip['crud']['view']['view'] === true)) ||
                        (($this->crud_action == 'update') && ACL\DbACL::canContentTypeAction($habtmRelationShip['model'], 'View') && !empty($habtmRelationShip['crud']['update']['view'])),
                    'url' => ['controller' => $habtm_controller_name, 'action' => 'view', '%id%'],
                    'label' => __('Detail', true),
                ],
                'item-move-up' => [
                    'active' => ($this->crud_action == 'update') && (!isset($habtmRelationShip['crud']['update']['moveUp']) || $habtmRelationShip['crud']['update']['move-up'] === true) && MySQLModel::getRecycledInstance($habtmRelationShip['association_model'], [], $this)->hasBehaviour('Orderable'),
                    'attributes' => 'data-gc-on-ajax="moveUp" data-gc-ajax="' . Router::url(['controller' => $this->name, 'action' => 'moveHabtmUp', '%parent_id%', $relationship_name, '%id%']) . '"',
                    'url' => 'javascript:void(0)',
                    'label' => __('Move Up', true),
                ],
                'item-remove' => [
                    'active' => ($this->crud_action == 'update') && (!isset($habtmRelationShip['crud']['update']['delete']) || $habtmRelationShip['crud']['update']['delete'] === true),
                    'attributes' => 'data-gc-on-ajax="remove" data-gc-ajax="true"',
                    'url' => 'javascript:void(0)',
                    'label' => __('Remove', true),
                ],
            ]
        ];

        $ths = array_values($ths);
        $tds = array_values($tds);

        return [
            'title' => __(Inflector::lowercaseName(Inflector::pluralize(Inflector::snakeCase($relationship_name, ' '))), true),
            'hash' => clean_for_url($relationship_name),
            'view' => 'elements/form/habtm',
            'actions' => [
                'list-add-existent' => [
                    'active' => ($this->crud_action == 'update') && (!isset($habtmRelationShip['crud']['update']['assign']) || $habtmRelationShip['crud']['update']['assign'] === true),
                    'url' => ['controller' => $this->name, 'action' => 'add'],
                    'attributes' => 'data-open-modal="' . Router::url(['controller' => $habtm_controller_name, 'action' => 'index', '?' => ['habtm' => $this->model_name . '#' . $relationship_name]]) . '"',
                    'label' => __('Add existent', true),
                ],
                'list-add-new' => [
                    'active' => ($this->crud_action == 'update') && ACL\DbACL::canContentTypeAction($habtmRelationShip['model'], 'Create') && (!isset($habtmRelationShip['crud']['update']['create']) || $habtmRelationShip['crud']['update']['create'] === true),
                    'url' => ['controller' => $this->name, 'action' => 'add'],
                    'attributes' => 'data-open-modal="' . Router::url(['controller' => $habtm_controller_name, 'action' => 'add', '?' => ['habtm' => $this->model_name . '#' . $relationship_name]]) . '"',
                    'label' => __('Add new', true),
                ]
            ],
            'list' => [
                'select' => [
                    'key' => $this->model_name . '#' . $relationship_name,
                    'renderer' => Router::url(['controller' => $this->name, 'action' => 'renderHabtmLineItem', $relationship_name]),
                ],
                'elements' => $habtmElements,
                'header' => [
                    'class' => 'd-none',
                    'ths' => $ths
                ],
                'rows' => [
                    'data_row_id' => ['deep' => $habtmRelationShip['model'] . '.id'],
                    'class' => '',
                    'tds' => $tds
                ],
                'datatable' => true,
                'hide_ths' => true,
                'force_element_index' => $habtmRelationShip['model'],
            ],
            'relationship_name' => $relationship_name,
        ];
    }

    /**
     * Renders a single line for a has many item
     *
     * @param $string $relationship_name
     * @return void
     */
    public function renderHasManyLineItem($relationship_name)
    {
        $this->crud_action = 'update';

        if (!ACL\DbACL::canContentTypeAction($this->model_name, 'Update')) {
            $this->forbidden(!$this->isAjaxRequest());
        }

        if (
            empty($this->model()->hasMany[$relationship_name])
            || !get_var('id')
        ) {
            $this->notFound(false);
        }

        $hasManyRelationShip = $this->model()->hasMany[$relationship_name];

        if (!ACL\DbACL::canContentTypeAction($hasManyRelationShip['model'], 'List')) {
            $this->forbidden(!$this->isAjaxRequest());
        }

        if (empty($hasManyRelationShip['options'])) {
            $hasManyRelationShip['options'] = [];
        }

        /** @var AppMySQLModel $Model **/
        $Model = MySQLModel::getRecycledInstance($hasManyRelationShip['model'], [], $this);
        $hasManyElement = $Model->getById(get_var('id'), $hasManyRelationShip['options']);

        if (!$hasManyElement) {
            $this->notFound(false);
        }

        if (is_a($hasManyElement, GestyMVC\Model\Element::class)) {
            $hasManyElement->onRelationship();
        } else {
            $hasManyElement = array_merge($hasManyElement[$hasManyRelationShip['model']], $hasManyElement);
            unset($hasManyElement[$hasManyRelationShip['model']]);
        }

        $data = $this->setEditViewForHasMany(null, $relationship_name, $hasManyRelationShip, [$hasManyElement]);
        $this->set($data['list']);
        $this->view = 'elements/list/table';
        $this->layout = 'json';
    }

    /**
     * Sets the required data for editing a HABTM list
     *
     * @param array $element
     * @param string $relationship_name
     * @param array $hasManyRelationShip
     * @param array $hasManyElements
     * @return array
     */
    protected function setEditViewForHasMany($element, $relationship_name, $hasManyRelationShip, $hasManyElements = null)
    {
        if ($hasManyElements === null) {
            $hasManyElements = $this->viewVars[lcfirst($this->model_name)][$relationship_name];
        }

        if ($this->crud_action == 'read' && (!$hasManyElements && empty($hasManyRelationShip['crud']['view']['create']))) {
            return false;
        }

        /** @var AppMySQLModel $Model **/
        $Model = MySQLModel::getRecycledInstance($hasManyRelationShip['model'], [], $this);

        $ths = [['class' => 'd-none']];
        $tds = [[
            'td_class' => 'd-none',
            'type' => 'hidden',
            'input' => [
                'name' => lcfirst($this->model_name) . '[' . $relationship_name . '][]',
                'value' => ['deep' => $hasManyRelationShip['model'] . '.id'],
            ],
        ]];

        foreach ($Model->getConfig()['listFields'] as $field => $config) {
            if (!empty($config['hide']['hasMany'])) {
                continue;
            }

            $ths[$field] = [
                'text' => $config['label'],
                'order' => false,
            ];

            $tds[$field] = $config;
            unset($tds[$field]['label']);
        }

        $has_many_controller_name = $this->getControllerNameByModelName($hasManyRelationShip['model']);

        $ths[] = [
            'class' => 'min-w-50px text-end'
        ];
        $tds[] = [
            'type' => 'actions',
            'td_class' => 'text-end',
            'data_id' => ['deep' => $hasManyRelationShip['model'] . '.id'],
            'actions' => [
                'item-view' => [
                    'active' => (($this->crud_action == 'read') && ACL\DbACL::canContentTypeAction($hasManyRelationShip['model'], 'View') && (!isset($hasManyRelationShip['crud']['view']['view']) || $hasManyRelationShip['crud']['view']['view'] === true)) ||
                        (($this->crud_action == 'update') && ACL\DbACL::canContentTypeAction($hasManyRelationShip['model'], 'View') && !empty($hasManyRelationShip['crud']['update']['view'])),
                    'url' => ['controller' => $has_many_controller_name, 'action' => 'view', '%id%'],
                    'label' => __('Detail', true),
                ],
                'item-edit' => [
                    'active' => ACL\DbACL::canContentTypeAction($hasManyRelationShip['model'], 'Update') && ($this->crud_action == 'update') && (!isset($hasManyRelationShip['crud']['update']['edit']) || $hasManyRelationShip['crud']['update']['edit'] === true),
                    'attributes' => 'data-open-modal="' . Router::url(['controller' => $has_many_controller_name, 'action' => 'edit', '%id%', '?' => ['hasMany' => $this->model_name . '#' . $relationship_name, 'has_many_id', 'has_many_id' => (!$element ? null : $element[$this->model_name]['id'])]]) . '"',
                    'url' => 'javascript:void(0)',
                    'label' => __('Edit', true),
                ],
                'item-move-up' => [
                    'active' => ($this->crud_action == 'update') && (!isset($hasManyRelationShip['crud']['update']['moveUp']) || $hasManyRelationShip['crud']['update']['move-up'] === true) && ACL\DbACL::canContentTypeAction($hasManyRelationShip['model'], 'Update') && MySQLModel::getRecycledInstance($hasManyRelationShip['model'], [], $this)->hasBehaviour('Orderable'),
                    'attributes' => 'data-gc-on-ajax="moveUp" data-gc-ajax="' . Router::url(['controller' => $has_many_controller_name, 'action' => 'moveUp', '%id%']) . '"',
                    'url' => 'javascript:void(0)',
                    'label' => __('Move Up', true),
                ],
                'item-delete' => [
                    'active' => ACL\DbACL::canContentTypeAction($hasManyRelationShip['model'], 'Delete') && ($this->crud_action == 'update') && !empty($Model->validation[$hasManyRelationShip['foreign_key']]['notempty']) && (!isset($hasManyRelationShip['crud']['update']['delete']) || $hasManyRelationShip['crud']['update']['delete'] === true),
                    'attributes' => 'data-gc-on-ajax="remove" data-gc-confirm-action="true"',
                    'url' => 'javascript:void(0)',
                    'confirm' => __('Are you sure you want to delete this item?', true),
                    'label' => __('Delete', true),
                ],
                'item-remove' => [
                    'active' => ACL\DbACL::canContentTypeAction($hasManyRelationShip['model'], 'Update') && ($this->crud_action == 'update') && empty($Model->validation[$hasManyRelationShip['foreign_key']]['notempty']) && (!isset($hasManyRelationShip['crud']['update']['delete']) || $hasManyRelationShip['crud']['update']['delete'] === true),
                    'attributes' => 'data-gc-on-ajax="remove" data-gc-ajax="true"',
                    'url' => 'javascript:void(0)',
                    'label' => __('Remove', true),
                ]
            ]
        ];

        $ths = array_values($ths);
        $tds = array_values($tds);

        return [
            'title' => __(Inflector::lowercaseName(Inflector::pluralize(Inflector::snakeCase($relationship_name, ' '))), true),
            'hash' => clean_for_url($relationship_name),
            'view' => 'elements/form/has-many',
            'actions' => [
                'list-add-existent' => [
                    'active' => ACL\DbACL::canContentTypeAction($hasManyRelationShip['model'], 'Update') && ($this->crud_action == 'update') && (!isset($hasManyRelationShip['crud']['update']['assign']) || $hasManyRelationShip['crud']['update']['assign'] === true),
                    'url' => 'javascript:void(0)',
                    'attributes' => 'data-open-modal="' . Router::url(['controller' => $has_many_controller_name, 'action' => 'index', '?' => ['hasMany' => $this->model_name . '#' . $relationship_name]]) . '"',
                    'label' => __('Assign existent', true),
                ],
                'list-add-new' => [
                    'active' => ACL\DbACL::canContentTypeAction($hasManyRelationShip['model'], 'Create') && ($this->crud_action == 'update') && (!isset($hasManyRelationShip['crud']['update']['create']) || $hasManyRelationShip['crud']['update']['create'] === true),
                    'url' => 'javascript:void(0)',
                    'attributes' => 'data-open-modal="' . Router::url(['controller' => $has_many_controller_name, 'action' => 'add', '?' => ['hasMany' => $this->model_name . '#' . $relationship_name, 'has_many_id' => (!$element ? null : $element[$this->model_name]['id'])]]) . '"',
                    'label' => __('Add new', true),
                ],
                'readonlylist-add-new' => [
                    'active' => ACL\DbACL::canContentTypeAction($hasManyRelationShip['model'], 'Create') && ($this->crud_action == 'read') && !empty($hasManyRelationShip['crud']['view']['create']),
                    'url' => 'javascript:void(0)',
                    'attributes' => 'data-open-modal="' . Router::url(['controller' => $has_many_controller_name, 'action' => 'add', '?' => ['hasMany' => $this->model_name . '#' . $relationship_name, 'has_many_id' => (!$element ? null : $element[$this->model_name]['id']), 'callback' => 'reload']]) . '"',
                    'label' => __('Add new', true),
                ]
            ],
            'list' => [
                'select' => [
                    'key' => $this->model_name . '#' . $relationship_name,
                    'renderer' => Router::url(['controller' => $this->name, 'action' => 'renderHasManyLineItem', $relationship_name]),
                ],
                'elements' => $hasManyElements,
                'header' => [
                    'class' => 'd-none',
                    'ths' => $ths
                ],
                'rows' => [
                    'data_row_id' => ['deep' => $hasManyRelationShip['model'] . '.id'],
                    'class' => '',
                    'tds' => $tds
                ],
                'datatable' => true,
                'hide_ths' => true,
                'force_element_index' => $hasManyRelationShip['model'],
            ],
            'relationship_name' => $relationship_name,
        ];
    }

    /**
     * Stores media
     */
    protected function addMedia(&$data)
    {
        $save = true;

        foreach ($this->model()->getConfig()['fields'] as $db_field_name => $field) {
            if (!$save) {
                break;
            }

            if ($field['type'] != 'picture') {
                continue;
            }

            if (post_var($field['name'] . '_upload')) {
                if (post_var($field['name'] . '_remove')) {
                    $data[$db_field_name] = null;
                }

                if (sizeof($result = $this->saveUploadedPicture($field['name'], 1, ''))) {
                    if (!empty($result[0]['picture']['Picture']['id'])) {
                        $data[$db_field_name] = $result[0]['picture']['Picture']['id'];
                    } elseif ($result[0]['no_file'] === false) {
                        $this->model()->errors = array_merge($this->model()->errors, $result[0]['errors']);
                        $save = false;
                    }
                }
            }
        }

        foreach ($this->model()->getConfig()['fields'] as $db_field_name => $field) {
            if (!$save) {
                break;
            }

            if ($field['type'] != 'file') {
                continue;
            }

            if (post_var($field['name'] . '_upload')) {
                if (post_var($field['name'] . '_remove')) {
                    $data[$db_field_name] = null;
                }

                if (sizeof($result = $this->saveUploadedFile($field['name'], 1, ''))) {
                    if (!empty($result[0]['file']['File']['id'])) {
                        $data[$db_field_name] = $result[0]['file']['File']['id'];
                    } elseif ($result[0]['no_file'] === false) {
                        $this->model()->errors = array_merge($this->model()->errors, $result[0]['errors']);
                        $save = false;
                    }
                }
            }
        }

        return $save;
    }

    /**
     * Loads belongs to lists
     */
    private function loadLists()
    {
        $belongsToLists = [];
        $formFieldSelectList = [];

        if (!empty($this->model()->getConfig()['fields'])) {
            foreach ($this->model()->getConfig()['fields'] as $field) {
                if ($field['type'] == 'select') {
                    if (!empty($field['list']['model'])) {
                        $list_name = 'listOf' . Inflector::pascalcase(Inflector::pluralize(Inflector::snakeCase($field['list']['model'])));
                        $formFieldSelectList[$field['name']] = $list_name;

                        if (
                            isset($this->viewVars[$list_name])
                            || isset($belongsToLists[$list_name])
                        ) {
                            continue;
                        }

                        /** @var AppMySQLModel $AppMySQLModel **/
                        $ListModel = MySQLModel::getRecycledInstance($field['list']['model'], [], $this);
                        $belongsToLists[$list_name] = $ListModel->getList('id', $ListModel->displayFields, $field['list']['options']);
                    }
                }
            }
        }

        $this->set($belongsToLists);
        $this->set('formFieldSelectList', $formFieldSelectList);
    }

    /**
     * Handles delete request.
     *
     * @param $id int
     */
    public function delete($id = null)
    {
        $this->crud_action = 'delete';

        if (!ACL\DbACL::canContentTypeAction($this->model_name, 'Delete')) {
            $this->forbidden(!$this->isAjaxRequest());
        }

        if (
            !$this->isPostRequest()
            && !$this->isDeleteRequest()
        ) {
            $this->forbidden(false);
        }

        // Prepare response
        $this->layout = 'json';
        $this->view = false;
        $this->resultForLayout['response']['succeeded'] = false;

        // Check if and id was provided
        if (!$id) {
            $this->notFound();
        }

        $element = $this->model()->getById($id, ['recursive' => -1, 'fields' => ['id']]);

        // Check if there was any result
        if (empty($element)) {
            $this->notFound();
        }

        // Try to delete
        if ($this->model()->deleteById($id)) {
            $this->resultForLayout['response']['id'] = $element[$this->model_name]['id'];
            $this->resultForLayout['response']['succeeded'] = true;
            $this->resultForLayout['response']['redirect_to'] = Router::url(['controller' => $this->name, 'action' => 'index']);
        } else {
            // Set error message
            $error_message = __('Unable to delete.', true);

            // Check for errors
            if ($this->model()->errors) {
                // Add all retrieved messages translated
                foreach ($this->model()->errors as $error) {
                    $error_message .= ' ' . $error;
                }
            }

            // Add retry message
            $error_message .= ' ' . __('Please try again.', true);
            $this->resultForLayout['error'] = $error_message;
        }
    }

    /**
     * Edit/view with with custom permissions, name and form fields
     * 
     * @param int $id
     * @param array $allowedFields
     * @param string $required_permission
     * @param string $title
     * @param string $title_short
     */
    protected function customViewOrEdit($id, $allowedFields = null, $required_permission = null, $title = null, $title_short = null)
    {
        $default_permission = null;

        if ($this->crud_action == 'read') {
            $default_permission = 'View';
        } else {
            $default_permission = 'Update';
        }

        if (
            $required_permission
            && $required_permission != $default_permission
        ) {
            if (!ACL\DbACL::canContentTypeAction($this->model_name, $required_permission)) {
                $this->forbidden(!$this->isAjaxRequest());
            }

            ACL\DbACL::skipContentTypeActionValidation($this->model_name, $default_permission);
        }

        if ($allowedFields !== null) {
            // Hide fields
            foreach ($this->model()->getConfig()['fields'] as $field_name => &$field) {
                if (!in_array($field_name, $allowedFields)) {
                    $field['hide'][($this->crud_action == 'update' ? 'update' : 'view')] = true;
                } else {
                    $field['hide'][($this->crud_action == 'update' ? 'update' : 'view')] = false;
                }
            }
        }

        $this->editOrView($id);

        if (
            $title
            && !empty($this->viewVars['breadcrumbs'])
        ) {
            if (!$title_short) {
                $title_short = $title;
            }

            $this->set('breadcrumbs', [
                'title' => $title,
                'title_short' => $title_short,
                'items' => [[
                    'title' => __(Inflector::lowercaseName(Inflector::pluralize(Inflector::snakeCase($this->model_name, ' '))), true),
                    'href' => ['controller' => $this->name, 'action' => 'index'],
                ], [
                    'title' => __('Admin', true),
                    'href' => null,
                ]],
            ]);
        }
    }

    /**
     * Edit with with custom permissions, name and form fields
     * 
     * @param int $id
     * @param array $allowedFields
     * @param string $required_permission
     * @param string $title
     * @param string $title_short
     */
    protected function customEdit($id, $allowedFields = null, $required_permission = null, $title = null, $title_short = null)
    {
        $this->crud_action = 'update';
        $this->customViewOrEdit($id, $allowedFields, $required_permission, $title, $title_short);
    }

    /**
     * View with with custom permissions, name and form fields
     * 
     * @param int $id
     * @param array $allowedFields
     * @param string $required_permission
     * @param string $title
     * @param string $title_short
     */
    protected function customView($id, $allowedFields = null, $required_permission = null, $title = null, $title_short = null)
    {
        $this->crud_action = 'read';
        $this->customViewOrEdit($id, $allowedFields, $required_permission, $title, $title_short);
    }

    /**
     * Controller name for model
     *
     * @param string $model_name
     * @return string
     */
    protected function getControllerNameByModelName($model_name)
    {
        $controller_name = array_search($model_name, GestyMVC::config('crudControllers'));

        if (!$controller_name) {
            $controller_name = Inflector::pascalcase(Inflector::pluralize(Inflector::snakeCase($model_name)));
        }

        return $controller_name;
    }
}
