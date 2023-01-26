<?php

/**
 * Class UsersController.
 *
 * Handles an user related HTTP request.
 */
class UsersController extends AppController
{
    /**
     * Controller name
     */
    public $name = 'Users';

    /**
     * @see Controller::$actionAccessLevel
     */
    protected $actionAccessLevel = [
        '*' => 0,
        'login' => -1,
        'logout' => -1,
        'forgotPassword' => -1,
        'reset' => -1,
    ];

    /**
     * User home page.
     *
     * @return void
     */
    public function index()
    {
        $this->set('redirect_to_first_available_action', true);
    }

    /**
     * User profile/settings.
     *
     * @return void
     */
    public function settings()
    {
        if (!ACL\DbACL::canContentTypeAction('User', 'Settings')) {
            $this->forbidden(!$this->isAjaxRequest());
        }

        /** @var User $User */
        $User = MySQLModel::getInstance('User');
        $User->recursive = 0;

        // If post data was provided
        if ($this->isAjaxRequest() && post_var('user[User]')) {
            // Prepare response
            $this->layout = 'json';
            $this->view = false;
            $this->resultForLayout['response']['succeeded'] = false;

            // Retrieve post data
            $save = true;
            $userData = post_var('user[User]');
            $current_password = post_var('user[User][current_password]');
            unset($userData['id']);
            unset($userData['level']);
            unset($userData['current_password']);

            // If sensible information was changed, require current password
            if (
                isset($userData['email'])
                || isset($userData['password'])
            ) {
                if (!$User->checkCredentials(Authentication::get('user', 'email'), $current_password)) {
                    $save = false;
                }
            }

            // If is avatar upload
            if (post_var('avatar_upload')) {
                if (post_var('avatar_remove')) {
                    $userData['avatar_picture_id'] = null;
                    $userData['avatar_url'] = ROOT_URL . 'static/img/avatars/blank.png';
                }

                if (sizeof($result = $this->saveUploadedPicture('avatar', 1, sprintf(__('Avatar of %s', true), Authentication::get('user', 'first_name') . ' ' . Authentication::get('user', 'last_name'))))) {
                    if (!empty($result[0]['picture']['Picture']['id'])) {
                        $userData['avatar_picture_id'] = $result[0]['picture']['Picture']['id'];
                        $userData['avatar_url'] = $result[0]['picture']['Picture']['src'];
                    } elseif ($result[0]['no_file'] === false) {
                        $User->errors = array_merge($User->errors, $result[0]['errors']);
                        $save = false;
                    }
                }
            }

            // Try to update user
            $user = ($save ? $User->updateFields(Authentication::get('user', 'id'), $userData) : false);

            // Check result
            if ($user) {
                // Update user session
                Authentication::set('user', $User->getById(Authentication::get('user', 'id'), [
                    'recursive' => -1,
                ])['User']);
                $this->updateUserAcl(true);

                // Set response
                $this->resultForLayout['response']['succeeded'] = true;
                $this->resultForLayout['response']['message'] = __('Changes saved.', true);
                $this->resultForLayout['response']['avatar_url'] = Authentication::get('user', 'avatar_url');
            } else {
                $error_message = __('Unable to save changes.', true);

                // Check for errors
                if ($User->errors) {
                    // Add all retrieved messages translated
                    foreach ($User->errors as $error) {
                        $error_message .= ' ' . $error;
                    }
                }

                // Add retry message
                $error_message .= ' ' . __('Please try again.', true);
                $this->resultForLayout['error'] = $error_message;
            }
        }

        if (!$this->isAjaxRequest()) {
            $this->set('breadcrumbs', [
                'title' => __('Account Settings', true),
                'title_short' => __('Settings', true),
                'items' => [
                    [
                        'title' => __('Account', true),
                        'href' => null,
                    ]
                ],
            ]);

            $this->view = 'elements/table-of-contents/table';
            $this->set('contents', [
                ['title' => __('Basic Information', true), 'hash' => 'basic', 'view' => 'users/settings/basic'],
                ['title' => __('Sign In Information', true), 'hash' => 'security', 'view' => 'users/settings/password'],
            ]);

            $user = $User->getById(Authentication::get('user', 'id'));

            // Check if there was any result
            if (empty($user)) {
                $this->redirect(['controller' => 'Users', 'action' => 'logout']);
            }

            // Set view vars
            $this->set(compact('user'));
        }
    }

    /**
     * Two factor authentication configuration
     *
     * @return void
     */
    public function twoFactor()
    {
        if (!ACL\DbACL::canContentTypeAction('User', 'Settings')) {
            $this->forbidden(!$this->isAjaxRequest());
        }

        $this->layout = 'json';
        $ga = new PHPGangsta_GoogleAuthenticator();

        if ($_SERVER['REQUEST_METHOD'] === 'GET') {
            $secret = $ga->createSecret();
            $qr_url = $ga->getQRCodeGoogleUrl(GestyMVC::config('website_name'), $secret);
            $this->set('secret', $secret);
            $this->set('qr_url', $qr_url);

            /** @var User $User **/
            $User = MySQLModel::getInstance('User');
            $this->set('active', !!$User->field('otp_seed', ['where' => ['id' => Authentication::get('user', 'id')]]));
        } else {
            // Prepare response
            $saveUserData = null;
            $this->layout = 'json';
            $this->view = false;
            $this->resultForLayout['response']['succeeded'] = false;

            if (post_var('user[TwoFactor][disable]')) {
                $saveUserData = ['otp_seed' => null];
            } else {
                // Retrieve post data
                $twoFactorData = [
                    'secret' => post_var('user[TwoFactor][secret]'),
                    'code' => post_var('user[TwoFactor][code]')
                ];

                if ($ga->verifyCode($twoFactorData['secret'], $twoFactorData['code'], 2)) {
                    $saveUserData = ['otp_seed' => $twoFactorData['secret']];
                } else {
                    $this->resultForLayout['error'] = __('Invalid code.', true);
                }
            }

            if ($saveUserData) {
                /** @var User $User **/
                $User = MySQLModel::getInstance('User');

                // Try to update user
                $user = $User->updateFields(Authentication::get('user', 'id'), $saveUserData);

                // Check result
                if ($user) {
                    // Set response
                    $this->resultForLayout['response']['succeeded'] = true;
                    $this->resultForLayout['response']['message'] = __('Two factor authentication configuration updated.', true);
                } else {
                    $error_message = __('Unable to save changes.', true);

                    // Check for errors
                    if ($User->errors) {
                        // Add all retrieved messages translated
                        foreach ($User->errors as $error) {
                            $error_message .= ' ' . $error;
                        }
                    }

                    // Add retry message
                    $error_message .= ' ' . __('Please try again.', true);
                    $this->resultForLayout['error'] = $error_message;
                }
            }
        }
    }

    /**
     * Redirect to home if user is logged in on a page for login purposes
     *
     * @return void
     */
    private function skipLoggedOutOnlyPage()
    {
        if (!$this->isAjaxRequest()) {
            // If user is already logged in, redirect
            if (Authentication::get('user', 'id')) {
                if (get_var('return') && strpos(get_var('return'), 'users/login/') === false) {
                    // If return parameter exists and does not point to the login page, redirect to it
                    $this->redirect(ROOT_URL . get_var('return'));
                } else {
                    // If not, redirect to the user's home page
                    $this->redirect(['action' => 'index']);
                }
            }
        }
    }

    /**
     * Checks OTP cookie
     *
     * @param int $user_id
     * @return boolean
     */
    private function hasValidOtpCookie($user_id = null)
    {
        if (
            empty($_COOKIE['TFA_TOKEN'])
            || !$user_id
        ) {
            return false;
        }

        /** @var UserTfaToken $UserTfaToken **/
        $UserTfaToken = MySQLModel::getInstance('UserTfaToken');
        return $UserTfaToken->isValidForUser($_COOKIE['TFA_TOKEN'], $user_id);
    }

    /**
     * Sets OTP cookie
     * 
     * @return boolean
     */
    private function setNewOptCookie()
    {
        if (!Authentication::get('user', 'id')) {
            return false;
        }

        /** @var UserTfaToken $UserTfaToken **/
        $UserTfaToken = MySQLModel::getInstance('UserTfaToken');
        $userTfaToken = $UserTfaToken->addNew(['user_id' => Authentication::get('user', 'id')]);

        if ($userTfaToken) {
            setcookie('TFA_TOKEN', $userTfaToken['UserTfaToken']['token'], time() + GestyMVC::config('max_otp_seconds'), '', explode(':', $_SERVER['HTTP_HOST'])[0], Router::isHTTPS(), true);
        }
    }

    /**
     * Handles user login request.
     */
    public function login()
    {
        $this->skipLoggedOutOnlyPage();
        $this->layout = 'no-header';

        if (Session::get('userPendingOtp')) {
            if ($this->isAjaxRequest()) {
                $this->layout = 'json';
                $this->view = false;
                $this->resultForLayout['response']['succeeded'] = false;
                $this->resultForLayout['error'] = __('Invalid code.', true);

                if (post_var('otp')) {
                    $ga = new PHPGangsta_GoogleAuthenticator();

                    if ($ga->verifyCode(Session::get('userPendingOtp[otp_seed]'), post_var('otp'), 2)) {
                        // Store data in auth session
                        Authentication::set('user', Session::get('userPendingOtp'));
                        $this->updateUserAcl(true);
                        Session::set('userPendingOtp', false);
                        $this->resultForLayout['response']['succeeded'] = true;
                        $this->resultForLayout['response']['message'] = __('You have successfully logged in!', true);

                        if (post_var('trust')) {
                            $this->setNewOptCookie();
                        }

                        if (get_var('return') && strpos(get_var('return'), 'users/login/') === false) {
                            // If return parameter exists and does not point to the login page, redirect to it
                            $this->resultForLayout['response']['redirect_to'] = Router::url(ROOT_URL . get_var('return'));
                        } else {
                            // If not, redirect to the user's home page
                            $this->resultForLayout['response']['redirect_to'] = Router::url(['action' => 'index']);
                        }
                    }
                }
            } else {
                $this->view = 'users/login/two-factor';
            }

            return;
        }

        // If post data was sent
        if ($this->isAjaxRequest() && post_var('user[User]')) {
            $this->layout = 'json';
            $this->view = false;
            $this->resultForLayout['response']['succeeded'] = false;

            /** @var User $User */
            $User = MySQLModel::getInstance('User');

            // Check provided credentials
            if ($User->checkCredentials(post_var('user[User][email]'), post_var('user[User][password]'))) {
                // Retrieve logged user data
                $user = $User->getByEmail(post_var('user[User][email]'), [
                    'recursive' => -1,
                ]);

                if (
                    $user['User']['otp_seed']
                    && !$this->hasValidOtpCookie($user['User']['id'])
                ) {
                    // Store data in auth session
                    Session::set('userPendingOtp', $user['User']);
                    $this->resultForLayout['response']['succeeded'] = true;
                    $this->resultForLayout['response']['reload'] = true;
                } else {
                    // Store data in auth session
                    Authentication::set('user', $user['User']);
                    $this->updateUserAcl(true);
                    Session::set('userPendingOtp', false);
                    $this->resultForLayout['response']['succeeded'] = true;
                    $this->resultForLayout['response']['message'] = __('You have successfully logged in!', true);

                    if (get_var('return') && strpos(get_var('return'), 'users/login/') === false) {
                        // If return parameter exists and does not point to the login page, redirect to it
                        $this->resultForLayout['response']['redirect_to'] = Router::url(ROOT_URL . get_var('return'));
                    } else {
                        // If not, redirect to the user's home page
                        $this->resultForLayout['response']['redirect_to'] = Router::url(['action' => 'index']);
                    }
                }
            } else {
                // Set error message
                $error_message = '';

                // Check for errors
                if ($User->errors) {
                    // Add all retrieved messages translated
                    foreach ($User->errors as $error) {
                        $error_message .= ($error_message ? ' ' : '') . $error;
                    }
                }

                $this->resultForLayout['error'] = $error_message;
            }
        }
    }

    /**
     * Handles user logout request.
     */
    public function logout()
    {
        // Destroys session
        Session::destroy();

        if ($this->isAjaxRequest()) {
            $this->layout = 'json';
            $this->view = false;
            $this->resultForLayout['response']['succeeded'] = true;
            $this->resultForLayout['response']['redirect_to'] = Router::url(['controller' => 'Users', 'action' => 'login']);
        } else {
            // Redirect to the user's home page
            $this->redirect(['action' => 'index']);
        }
    }

    /**
     * Handles user restore password request.
     */
    public function forgotPassword()
    {
        $this->skipLoggedOutOnlyPage();
        $this->layout = 'no-header';

        // If post data was provided
        if ($this->isAjaxRequest() && post_var('user[User]')) {
            $this->layout = 'json';
            $this->view = false;

            /** @var User $User */
            $User = MySQLModel::getInstance('User');
            $User->sendPasswordResetLink(post_var('user[User][email]'));

            // For security reasons we do not disclose if the user is really registered.
            $this->resultForLayout['response']['succeeded'] = true;
            $this->resultForLayout['response']['message'] = __('If the user is registered on the site, he or she will receive and email with a link to reset the account password.', true);
            $this->resultForLayout['response']['redirect_to'] = Router::url(['controller' => 'Users', 'action' => 'login']);
        }
    }

    /**
     * Handles user reset password request.
     *
     * @param $email string
     * @param $reset_password_token string
     */
    public function reset($email = null, $reset_password_token = null)
    {
        $this->skipLoggedOutOnlyPage();

        /** @var User $User */
        $User = MySQLModel::getInstance('User');

        if (!$this->isAjaxRequest()) {
            $this->layout = 'no-header';
        } else {
            $this->layout = 'json';
            $this->view = false;
            $this->resultForLayout['response']['succeeded'] = false;
        }

        // Set error message
        $error_message = '';

        // On the first load, vars will be passed by $_GET
        if ($email && $reset_password_token) {
            // Check provided token
            if (!$User->checkToken($email, $reset_password_token)) {
                if ($User->errors) {
                    foreach ($User->errors as $error) {
                        $error_message .= ($error_message ? ' ' : '') . $error;
                    }
                }
            }
        } else {
            $error_message .= __('Invalid request.', true);
        }

        if ($error_message) {
            if (!$this->isAjaxRequest()) {
                Session::set('flash[message]', $error_message);
                $this->redirect(['action' => 'login']);
            } else {
                $this->resultForLayout['error'] = $error_message;
                return;
            }
        }

        // Set $data['user'] for the view
        $this->set('user', ['User' => ['email' => $email]]);

        // If post data was sent
        if ($this->isAjaxRequest() && post_var('user[User]')) {
            // Set flash title
            Session::set('flash[title]', __('Reset password', true));

            // Check password repetition
            if (post_var('user[User][new_password]') == post_var('user[User][check_new_password]')) {
                // Attempt to restore password
                if ($User->resetPassword($email, $reset_password_token, post_var('user[User][new_password]'))) {
                    $this->resultForLayout['response']['succeeded'] = true;
                    $this->resultForLayout['response']['message'] = __('Password changed.', true);
                    $this->resultForLayout['response']['redirect_to'] = Router::url(['controller' => 'Users', 'action' => 'login']);
                } else {
                    $error_message = __('Could not change password.', true);

                    if ($User->errors) {
                        foreach ($User->errors as $error) {
                            $error_message .= ' ' . $error;
                        }
                    }

                    $error_message .= ' ' . __('Please try again.', true);
                }
            } else {
                $error_message = __('Passwords do not match.', true);
            }

            if ($error_message) {
                $this->resultForLayout['error'] = $error_message;
            }
        }
    }
}
