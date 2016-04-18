<?php
namespace WarpZone\Controller;

use WarpZone\Entity\User,
    WarpZone\Entity\UserCredentials,
    WarpZone\Entity\Session;
/**
 * Handles login, password reset and related requests.
 */
class Login extends \WarpZone\Controller\AbstractController
{
    /**
     * Is called when the user is asked to log in.
     *
     * @param array $args The route parameters
     */
    public function loginAction($args)
    {
        if (!empty($_POST)) {
            $errors = $this->handleLoginFormData($_POST);
            unset($_POST['password']);

            if (empty($errors)) {
                $session = Session::getSession();
                $session->setUserIsLoggedIn(true)
                    ->setUser(User::findOneByEmail($_POST['email']));
                $session->persist();

                $this->redirect('/');
            }

            $this->_view->setFormData($_POST);
            $this->_view->setErrors($errors);
            $this->_view->status = 1;
        } else {
            $this->_view->status = 0;
        }
    }

    /**
     * Is called when the user wishes to register.
     *
     * @param array $args The route parameters
     */
    public function registerAction($args)
    {
        if (!empty($_POST)) {
            $errors = $this->handleRegisterFormData($_POST);

            $this->_view->setFormData($_POST);
            $this->_view->setErrors($errors);
            $this->_view->status = empty($errors) ? 2 : 1;
        } else {
            $this->_view->status = 0;
        }
    }

    /**
     * Is called when the user wishes to confirm a registration.
     *
     * @param array $args The route parameters
     */
    public function confirmAction($args)
    {
        $hash = $args[0];
        $cred = UserCredentials::findOneByOptinHash($hash);

        if ($cred instanceof UserCredentials) {
            $user = $cred->getUser();

            if (!$user->getIsConfirmed()) {
                $user->setIsConfirmed(true);
                $user->persist();
                $this->_view->status = 2;
            } else {
                $this->_view->status = 1;
            }
        } else {
            $this->_view->status = 0;
        }
    }

    protected function handleRegisterFormData($data)
    {
        $errors = array();

        if (
            isset($data['email'])
            && isset($data['name'])
            && isset($data['password'])
            && isset($data['repeat'])
        ) {
            if ($data['password'] != $data['repeat']) {
                $errors['repeat'] = "Password and repeat do not match.";
            }

            // We don't perform an in-depth of the email, as this an almost
            // impossible task, but we can at least check it looks like a
            // valid email address (e.g. name@host.tld)
            if (preg_match('#.*@.+\..+#', $data['email']) !== 1) {
                $errors['email'] = "Does not look like an email (e.g. name@host.tld).";
            }

            $existing = User::findOneByEmail($data['email']);
            if ($existing instanceof User) {
                $errors['email'] = "A user with that email already exists.";
            }

            $existing = User::findOneByName($data['name']);
            if ($existing instanceof User) {
                $errors['name'] = "A user with that username already exists.";
            }

            if (!empty($errors)) {
                return $errors;
            }

            $user = User::create($data['email'], $data['name']);
            $cred = UserCredentials::create(
                $user,
                password_hash($data['password'], PASSWORD_DEFAULT),
                $this->generateOptinHash()
            );

            $mail = new \WarpZone\Mail\Registration($cred);
            $mail->send();
        }
    }

    protected function handleLoginFormData($data)
    {
        $errors = array();

        if (
            isset($data['email'])
            && isset($data['password'])
        ) {
            $user = User::findOneByEmail($data['email']);
            if (!$user instanceof User) {
                $errors['email'] = "A user with that email does not exist.";
                return $errors;
            }

            if (!$user->getIsConfirmed()) {
                $errors['email'] = "This user has not completed registration yet.";
                return $errors;
            }

            $credentials = UserCredentials::findOneByUserId($user->getUserId());
            if (!$credentials instanceof UserCredentials) {
                $errors['email'] = "This user has no login data. Please contact an administrator.";
                return $errors;
            }

            if (!password_verify($data['password'], $credentials->getPasswordHash())) {
                $errors['password'] = "Incorrect password.";
                return $errors;
            }
        }
    }

    protected function generateOptinHash()
    {
        static $chars = '0123456789ABCDEF';

        // As soon as random_int becomes available with php7, use that one,
        // as it is much more secure than this stuff here
        mt_srand((int)(microtime(true) + filemtime(APPLICATION_PATH . '/config.ini')));

        $token = '';
        for ($i = 0; $i < 60; $i++) {
            $token .= $chars[mt_rand(0, 15)];
        }
        return $token;
    }
}