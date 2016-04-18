<?php
namespace WarpZone\Entity;

use WarpZone\Entity\User;

class Session
{
    protected $sessionId;

    protected $validTo;

    protected $token;

    protected $user;

    protected $userIsLoggedIn;

    public function __construct($id)
    {
        $this->sessionId = $id;
        $this->userIsLoggedIn = false;
    }

    public function getSessionId()
    {
        return $this->sessionId;
    }

    public function getValidTo()
    {
        return $this->validTo;
    }

    public function setValidTo(\DateTime $date)
    {
        $this->validTo = $date;
        return $this;
    }

    public function getToken()
    {
        return $this->token;
    }

    public function setToken($token)
    {
        $this->token = $token;
        return $this;
    }

    public function getUser()
    {
        return $this->user;
    }

    public function setUser($user)
    {
        $this->user = $user;
        return $this;
    }

    public function getUserIsLoggedIn()
    {
        return $this->userIsLoggedIn;
    }

    public function setUserIsLoggedIn($flag)
    {
        $this->userIsLoggedIn = (bool)$flag;
        return $this;
    }

    public function persist()
    {
        $db  = \Slim\Slim::getInstance()->config('db');
        $sql = "UPDATE session
                SET valid_to = :ts,
                    token = :token,
                    user_id = :user_id,
                    user_is_logged_in = :flag
                WHERE session_id = :id";

        $db->executeQuery($sql, array(
            'ts'      => $this->getValidTo()->format('Y-m-d H:i:s'),
            'user_id' => $this->getUser() instanceof User ? $this->getUser()->getUserId() : null,
            'token'   => $this->getToken(),
            'flag'    => $this->getUserIsLoggedIn(),
            'id'      => $this->getSessionId(),
        ));
    }

    /**
     * Checks if there is a session token cookie and tries to find a valid session
     * for that token. If none found, creates a new session and token cookie.
     *
     * @return \WarpZone\Session The found or created session
     */
    public static function getSession()
    {
        if (!isset($_COOKIE['session_token'])) {
            $token = self::generateToken();

            $domain = \Slim\Slim::getInstance()->config('settings')->App->base_url;
            if (stristr('.', $domain) === false) {
                // Localhost base URLs don't have periods, but setcookie silently
                // requires them, so we don't use a domain. Thanks, PHP, for wasting
                // my time, because I have NOTHING better to do...
                $domain = false;
            }

            \Slim\Slim::getInstance()->setCookie(
                'session_token',
                $token,
                time() + 60 * 60 * 24 * 30,
                '/',
                $domain,
                false,
                true
            );
        } else {
            $token = $_COOKIE['session_token'];
        }

        $session = self::findOneByToken($token);
        $now     = new \DateTime();

        if (
            $session instanceof self
            && $session->getValidTo() >= $now
        ) {
            return $session;
        } else {
            return self::create($token, new \DateTime('+30days'));
        }
    }

    public static function generateToken($length = 60)
    {
        static $chars = '0123456789ABCDEF';

        // As soon as random_int becomes available with php7, use that one,
        // as it is much more secure than this stuff here
        mt_srand((int)(microtime(true) + filemtime(APPLICATION_PATH . '/config.ini')));

        $token = '';
        for ($i = 0; $i < $length; $i++) {
            $token .= $chars[mt_rand(0, 15)];
        }

        $token = password_hash($token, PASSWORD_DEFAULT);
        return $token;
    }

    public static function create($token, \DateTime $validTo)
    {
        if (empty($token) || empty($validTo)) {
            throw new \Exception("Empty token or timestamp for session provided in Session::create.");
        }

        $db  = \Slim\Slim::getInstance()->config('db');
        $sql = "INSERT INTO session (token, valid_to) VALUES (:token, :ts)";

        $db->executeQuery($sql, array(
            'token' => $token,
            'ts' => $validTo->format('Y-m-d H:i:s'),
        ));
        $id = $db->lastInsertId();

        $session = new self($id);
        $session->setToken($token)
            ->setValidTo($validTo);

        return $session;
    }

    public static function findOneByToken($token)
    {
        $db  = \Slim\Slim::getInstance()->config('db');
        $sql = "SELECT *
                FROM session s
                LEFT JOIN user u USING(user_id)
                WHERE s.token = :token
                LIMIT 1";

        $result = $db->executeQuery($sql, array('token' => $token));
        $row    = $result->fetch();

        if (!isset($row['session_id'])) {
            return null;
        } else {
            if (isset($row['user_id'])) {
                $user = new User($row['user_id']);
                $user->setName($row['name'])
                    ->setEmail($row['email']);
            } else {
                $user = null;
            }

            $session = new self((int)$row['session_id']);
            $session->setToken($row['token'])
                ->setValidTo(new \DateTime($row['valid_to']))
                ->setUser($user)
                ->setUserIsLoggedIn((bool)$row['user_is_logged_in']);
            return $session;
        }
    }
}