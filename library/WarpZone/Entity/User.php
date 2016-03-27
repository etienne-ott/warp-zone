<?php
namespace WarpZone\Entity;

class User
{
    protected $userId;

    protected $name;

    protected $email;

    public function __construct($id)
    {
        $this->userId = $id;
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
}