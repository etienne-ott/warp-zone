<?php
namespace WarpZone\Entity;

class User
{
    protected $userId;

    protected $name;

    protected $email;

    protected $isConfirmed;

    public function __construct($id)
    {
        $this->userId = $id;
        $this->isConfirmed = false;
    }

    public function getUserId()
    {
        return $this->userId;
    }

    public function getName()
    {
        return $this->name;
    }

    public function setName($name)
    {
        $this->name = $name;
        return $this;
    }

    public function getEmail()
    {
        return $this->email;
    }

    public function setEmail($email)
    {
        $this->email = $email;
        return $this;
    }

    public function getIsConfirmed()
    {
        return $this->isConfirmed;
    }

    public function setIsConfirmed($flag)
    {
        $this->isConfirmed = (bool)$flag;
        return $this;
    }

    public static function findOneByUserId($id)
    {
        $db  = \Slim\Slim::getInstance()->config('db');
        $sql = "SELECT *
                FROM user
                WHERE user_id = :id";

        $result = $db->executeQuery($sql, array('id' => $id));
        $row    = $result->fetch();

        if (!isset($row['user_id'])) {
            return null;
        }

        $user = new self((int)$row['user_id']);
        $user->setName($row['name'])
            ->setEmail($row['email']);

        return $user;
    }

    public static function findOneByEmail($email)
    {
        $db  = \Slim\Slim::getInstance()->config('db');
        $sql = "SELECT *
                FROM user
                WHERE email = :email";

        $result = $db->executeQuery($sql, array('email' => trim($email)));
        $row    = $result->fetch();

        if (!isset($row['user_id'])) {
            return null;
        }

        $user = new self((int)$row['user_id']);
        $user->setName($row['name'])
            ->setEmail($row['email']);

        return $user;
    }

    public static function findOneByName($name)
    {
        $db  = \Slim\Slim::getInstance()->config('db');
        $sql = "SELECT *
                FROM user
                WHERE name = :name";

        $result = $db->executeQuery($sql, array('name' => trim($name)));
        $row    = $result->fetch();

        if (!isset($row['user_id'])) {
            return null;
        }

        $user = new self((int)$row['user_id']);
        $user->setName($row['name'])
            ->setEmail($row['email']);

        return $user;
    }

    public function persist()
    {
        $db  = \Slim\Slim::getInstance()->config('db');
        $sql = "UPDATE user
                SET email = :email,
                    name = :name
                    is_confirmed = :conf
                WHERE user_id = :id";

        $db->execute($sql, array(
            'id'    => $this->getUserId(),
            'name'  => $this->getName(),
            'email' => $this->getEmail(),
            'conf'  => $this->getIsConfirmed(),
        ));
    }

    public static function create($email, $name)
    {
        $email = trim($email);
        $name  = trim($name);

        if (empty($email) || empty($name)) {
            throw new \Exception("Empty email or name provided in User::create.");
        }

        $db  = \Slim\Slim::getInstance()->config('db');
        $sql = "INSERT INTO user (email, name) VALUES (:email, :name)";

        $db->executeQuery($sql, array(
            'email' => $email,
            'name'  => $name,
        ));
        $id = $db->lastInsertId();

        $user = new self($id);
        $user->setEmail($email)
            ->setName($name);

        return $user;
    }
}