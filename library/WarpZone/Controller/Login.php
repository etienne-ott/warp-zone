<?php
namespace WarpZone\Controller;

use WarpZone\Entity\User,
    WarpZone\Entity\UserCredentials;
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
            $cred = UserCredentials::create($user, password_hash($data['password'], PASSWORD_DEFAULT));
        }
    }
}