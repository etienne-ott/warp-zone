<?php
namespace WarpZone\Entity;

use WarpZone\Entity\User;

class UserCredentials
{
    protected $userCredentialsId;

    protected $user;

    protected $passwordHash;

    protected $optinHash;

    public function __construct($id)
    {
        $this->userCredentialsId = $id;
    }

    public function getUserCredentialsId()
    {
        return $this->userCredentialsId;
    }

    public function getUser()
    {
        return $this->user;
    }

    public function setUser(User $user)
    {
        $this->user = $user;
        return $this;
    }

    public function getPasswordHash()
    {
        return $this->passwordHash;
    }

    public function setPasswordHash($hash)
    {
        $this->passwordHash = $hash;
        return $this;
    }

    public function getOptinHash()
    {
        return $this->optinHash;
    }

    public function setOptinHash($hash)
    {
        $this->optinHash = $hash;
        return $this;
    }

    public static function findOneByUserId($id)
    {
        $db  = \Slim\Slim::getInstance()->config('db');
        $sql = "SELECT *
                FROM user_credentials uc
                JOIN user USING (user_id)
                WHERE uc.user_id = :id";

        $result = $db->executeQuery($sql, array('id' => $id));
        $row    = $result->fetch();

        if (!isset($row['user_credentials_id'])) {
            return null;
        }

        $user = new User((int)$row['user_id']);
        $user->setName($row['name'])
            ->setEmail($row['email']);

        $cred = new self((int)$row['user_credentials_id']);
        $cred->setPasswordHash($row['password_hash'])
            ->setOptinHash($row['optin_hash'])
            ->setUser($user);

        return $cred;
    }

    public static function findOneByOptinHash($hash)
    {
        $db  = \Slim\Slim::getInstance()->config('db');
        $sql = "SELECT *
                FROM user_credentials uc
                JOIN user USING (user_id)
                WHERE uc.optin_hash = :hash
                LIMIT 1";

        $result = $db->executeQuery($sql, array('hash' => $hash));
        $row    = $result->fetch();

        if (!isset($row['user_credentials_id'])) {
            return null;
        }

        $user = new User((int)$row['user_id']);
        $user->setName($row['name'])
            ->setEmail($row['email']);

        $cred = new self((int)$row['user_credentials_id']);
        $cred->setPasswordHash($row['password_hash'])
            ->setOptinHash($row['optin_hash'])
            ->setUser($user);

        return $cred;
    }

    public static function create(User $user, $pwHash, $oiHash = null)
    {
        if (empty($pwHash)) {
            throw new \Exception("Empty password hash provided in UserCredentials::create.");
        }

        $db  = \Slim\Slim::getInstance()->config('db');
        $sql = "INSERT INTO user_credentials (user_id, password_hash, optin_hash)
                VALUES (:user_id, :pw_hash, :oi_hash)";

        $db->executeQuery($sql, array(
            'user_id' => $user->getUserId(),
            'pw_hash' => $pwHash,
            'oi_hash' => $oiHash,
        ));
        $id = $db->lastInsertId();

        $cred = new self($id);
        $cred->setUser($user)
            ->setPasswordHash($pwHash)
            ->setOptinHash($oiHash);

        return $cred;
    }
}