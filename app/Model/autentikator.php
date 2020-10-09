<?php


namespace app\Model;

use Nette;
use app\Model\connDatabase;
use Nette\Security\Passwords;
use Nette\Database\Context;
use Nette\Security\IAuthenticator;
use Nette\Security\Identity;
use Nette\Security\AuthenticationException;


class autentikator implements Nette\Security\IAuthenticator
{

    private $database;
    private $passwords;
    private $hash;
    private $pass;

    public function __construct(connDb $database, Nette\Security\Passwords $passwords)
    {
        $this->database = $database;
        $this->passwords = $passwords;
    }
//    public function authenticate(array $credentials): Nette\Security\IIdentity
//    {
//        list($email, $password) = $credentials;
//        $row = $this->database->tbUsers()
//            ->where('email', $email)->fetch();
//
//        if (!$row) {
//            throw new Nette\Security\AuthenticationException('Neplatné uživatelské jméno.');
//        }
//        else
//        {
////            if ($row->status == 1)
////            {
//                $this->password = new Passwords(PASSWORD_BCRYPT, ['cost' => 12]);
//                $this->hash = $this->password->hash($password);
//                if (!$this->password->verify($password, $this->hash)) {
//                    throw new Nette\Security\AuthenticationException('Neplatné heslo.');
//                }
//                return new Nette\Security\Identity($row->id, $row->status, ['email' => $row->email]);
////            } elseif ($row->status == 0) {
////                if ($password != $row->password) {
////                    throw new Nette\Security\AuthenticationException('Nepotvrzená registrace.');
////                }
////
////            }
//        }
//
//    }
    public function authenticate(array $credentials): Nette\Security\IIdentity
    {
        [$email, $password] = $credentials;

        $row = $this->database->tbUsers('users')
            ->where('email', $email)
            ->fetch();
        $this->pass = new Passwords(PASSWORD_BCRYPT, ['cost' => 12]);
        $this->hash = $this->pass->hash($password);
        if (!$row) {
            throw new Nette\Security\AuthenticationException('Špatné uživatelské jméno');
        }

        if (!$this->passwords->verify($password, $row->password)) {
            throw new Nette\Security\AuthenticationException('Špatné heslo');
        }


        return new Nette\Security\Identity(
            $row->id,
            'admin', // nebo pole více rolí
            ['name' => $row->nick,'email'=> $row->email]
        );

    }

}