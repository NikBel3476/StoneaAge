<?php
class User {
    function __construct($db) {
        $this->db = $db;
    }

    public function getUserByToken($token) {
        if ($token) {
            return $this->db->getUserByToken($token);
        }
    }

    public function login($login, $passwordHash, $token, $num) {
        $user = $this->db->getUserByLogin($login);
        if ($user) {
            $isRightPassword = password_verify($passwordHash, $user->password);
            $isRightToken = md5($passwordHash . (string)$num) === $token;
            if ($isRightPassword && $isRightToken) {
                return $this->db->updateGamerTokenById($user->id, $token);
            }
        }
        return false;
    }

    public function registration($nickname, $login, $passwordHash, $token, $num) {
        if ($nickname && $login && $passwordHash && $token && $num) {
            if (!$this->db->isRepeatedLogin($login)) {
                if (md5($passwordHash . (string)$num) === $token) {
                    $password = password_hash($passwordHash, PASSWORD_DEFAULT);
                    $resToken = $this->db->createUser($nickname, $login, $password, $token);
                    if ($resToken) {
                        $gamer = $this->db->getGamerByLogin($login);
                        $this->db->createGamer($gamer->id);
                        return $resToken;
                    }
                }
            }
        }
        return false;
    }

    public function logout($token) {
        $user = $this->db->getUserByToken($token);
        if ($user) {
            $result = $this->db->updateToken($user['id'], null); // обновить токен в DB
            if ($result) {
                return true;
            }
        }
        return false;
    }
}