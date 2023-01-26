<?php

/**
 * Class LayoutController.
 *
 * Displays HTTP errors
 */
class LayoutController extends AppController
{
    /**
     * Controller name
     */
    public $name = 'Layout';

    /**
     * Set the layout to use
     *
     * @var string
     */
    protected $layout = 'json';

    /**
     * @see Controller::$actionAccessLevel
     */
    protected $actionAccessLevel = [
        '*' => -1,
    ];

    /**
     * Stores admin menu status
     */
    public function storeMenuStatus()
    {
        // No view
        $this->view = false;

        // Default JSON response
        $this->set('resultForLayout', [
            'error' => null,
            'fatal_error' => false,
            'response' => [
                'succeeded' => true,
            ],
        ]);

        // Update session var
        Session::set('layout[nav_deployed]', (get_var('deployed') ? true : false));
    }
}
