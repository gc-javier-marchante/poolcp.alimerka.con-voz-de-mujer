<?php

/**
 * Class ErrorsController.
 *
 * Displays HTTP errors
 */
class ErrorsController extends AppController
{
    /**
     * Controller name
     */
    public $name = 'Errors';

    /**
     * Set the layout to use
     *
     * @var string
     */
    protected $layout = 'no-header';

    /**
     * Throws a 404 error.
     *
     * @param $redirectToUrl bool|array|string whether or not to $this->redirect to the 404 page or the URL to redirect to
     * @param $reason
     */
    public function notFound($redirectToUrl = true, $reason = null)
    {
        if ($this->action != __FUNCTION__) {
            parent::notFound($redirectToUrl, $reason);
        }
    }

    /**
     * Throws a 403 error.
     *
     * @param $redirectToUrl bool|array|string whether or not to $this->redirect to the 403 page or the URL to redirect to
     */
    public function forbidden($redirectToUrl = true)
    {
        if ($this->action != __FUNCTION__) {
            parent::forbidden($redirectToUrl);
        }
    }
}
