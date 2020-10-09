<?php


namespace App\Model;

use Nette;
use Nette\Database\Context;
use Nette\SmartObject;

class connDb
{
    use SmartObject;
    private $database;


    Public function __construct(Context $database)
    {
        $this->database = $database;
    }
    public function tbArticles()
    {
        return $this->database->table('articles');
    }
    public function tbUsers()
    {
        return $this->database->table('users');
    }
    public function register($email, $password, $nick)
    {
        $this->database->query('INSERT INTO users', [ // tady můžeme otazník vynechat
            'nick' => $nick,
            'email' => $email,
            'password' => $password,
            'status'=> 0
        ]);
    }
    public function findUserNick($usermail){
        return $this->database->fetchfield ('SELECT nick FROM users WHERE email = ?', $usermail);
    }
    public function selectDetailRow($id){
        return $this->database->fetch ('SELECT * FROM articles WHERE id = ?', $id);

    }
    public function insertArticles($id_user, $title, $summary, $content, $datum)
    {
        $this->database->query('INSERT INTO articles', [ // tady můžeme otazník vynechat
            'id_users' => $id_user,
            'title' => $title,
            'summary' => $summary,
            'content'=> $content,
            'datum' => $datum
        ]);
    }
    public function updateArticle($id, $title, $summary, $content, $datum)
    {
        $this->database->query('UPDATE articles SET',['title' => $title, 'summary' => $summary,'content' => $content, 'datum' => $datum],'WHERE id = ?', $id);
    }
    public function deleteArticle($id)
    {
        $this->database->query('DELETE FROM articles', [],'WHERE id = ?', $id);
    }

}