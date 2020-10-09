<?php

declare(strict_types=1);

namespace App\Presenters;

use Nette;
use Nette\Application\UI\Form;
use Nette\Database\Context;
use Nette\Security\AuthenticationException;
use Nette\Security\Passwords;
use Nette\Security\User;
use app\Model\connDb;


final class HomepagePresenter extends Nette\Application\UI\Presenter
{
    private $message = '';
    private $status;
    private $database;
    private $articles;
    private $users;
    private $content;
    private $par = '';
    private $username;
    private $titlemodal = '';
    private $idRow = 0;
    private $dataRow;
    private $userNick = '';



    public function __construct(connDb $db)
    {
        $this->database = $db;
        $this->articles = $this->database->tbArticles();
        $this->users = $this->database->tbUsers();


    }


    public function beforeRender()
    {
        $this->checkLogin();
        $this->template->message = $this->message;
        $this->template->status = $this->status;
        $this->template->titlemodal = $this->titlemodal;
        $this->template->par = $this->par;
        $this->template->idRow = $this->idRow;
        $this->template->dataRow = $this->dataRow;
        $this->template->userNick = $this->userNick;

    }


    public function renderDefault()
    {
        $this->template->tbArticles = $this->articles;
        $this->template->tbUsers = $this->users;


    }
    public function handleAdd($par)
    {

        if ($this->isAjax()){
            if ($this->user->isLoggedIn() == true){

                $this->titlemodal = 'Přidat článek';
                $this->par = $par;
                $this->redrawControl('modaltitle');
                $this->redrawControl('modalcontent');
                $this->redrawControl('items');
            }
            elseif ($this->user->isLoggedIn() == false)
            {
                $this->titlemodal = 'Přihlášení';
                $this->par = 'alert';
                $this->message = 'Musíte se přihlásit';
                $this->redrawControl('modaltitle');
                $this->redrawControl('modalcontent');
                $this->redrawControl('items');

            }
        }
    }
    public function handleView($id, $par)
    {
        if ($this->isAjax()){

            $this->par = $par;
            $this->idRow = $id;
            $this->dataRow = $this->articles->get($id);

            $this->titlemodal = 'Detail článku';
            $this->redrawControl('modaltitle');
            $this->redrawControl('modalcontent');
        }


    }
    public function handleLogin($par)
    {
        if ($this->isAjax()) {

                $this->par = $par;
                $this->titlemodal = 'Přihlášení';
                $this->redrawControl('statuslogin');
                $this->redrawControl('modaltitle');
                $this->redrawControl('modalcontent');



        }

    }
    public function handleRegister($par)
    {
        if ($this->isAjax()){
            $this->par = $par;
            $this->titlemodal = 'Registrace';
            $this->redrawControl('modaltitle');
            $this->redrawControl('modalcontent');
        }
    }
    public function handleEdit($id, $id_user, $par)
    {
        if ($this->isAjax()){
            if ($this->user->isLoggedIn() == true)
            {
                $userId = $this->user->getIdentity()->getId();
                $this->userNick = $this->user->getIdentity()->name;
                if ($userId == $id_user)
                {
                    $this->par = $par;
                    $this->idRow = $id;
                    $this->dataRow = $this->articles->get($id);

                    $this->titlemodal = 'Úprava článku';
                    $this->redrawControl('modaltitle');
                    $this->redrawControl('modalcontent');
                }
                elseif ($userId != $id_user)
                {
                    $this->par = 'alert';
                    $this->titlemodal = "Varování";
                    $this->message = 'Nejste autorem článku.';
                    $this->redrawControl('modaltitle');
                    $this->redrawControl('modalcontent');
                }

            }
            elseif ($this->user->isLoggedIn() == false)
            {
                $this->par = 'alert';
                $this->titlemodal = "Varování";
                $this->message = 'Nepřihlášený uživatel';
                $this->redrawControl('modaltitle');
                $this->redrawControl('modalcontent');

            }

        }
    }
    public function handleDel($par, $id, $id_user)
    {
        if ($this->isAjax()){

            if ($this->user->isLoggedIn() == true)
            {
                $userId = $this->user->getIdentity()->getId();
                if ($userId == $id_user)
                {
                    $this->par = $par;
                    $this->idRow = $id;
                    $this->dataRow = $this->articles->get($id);

                    $this->database->deleteArticle($id);
                    $this->articles = $this->database->tbArticles();

                    $this->titlemodal = 'Varování';
                    $this->message = 'Článek byl odstraněn.';
                    $this->redrawControl('modaltitle');
                    $this->redrawControl('modalcontent');
                    $this->redrawControl('items');

                }
                elseif ($userId != $id_user)
                {
                    $this->par = 'alert';
                    $this->titlemodal = "Varování";
                    $this->message = 'Nejste autorem článku. Nemůžete ho odstranit.';
                    $this->redrawControl('modaltitle');
                    $this->redrawControl('modalcontent');
                }

            }
            elseif ($this->user->isLoggedIn() == false)
            {
                $this->par = 'alert';
                $this->titlemodal = "Varování";
                $this->message = 'Nepřihlášený uživatel';
                $this->redrawControl('modaltitle');
                $this->redrawControl('modalcontent');

            }

        }
    }

    public function handleRefresh()
    {
//        $this->redirect('Homepage:default');
    }

    public function handlelogoutUser()
    {
        $this->user->logout(true);

        $this->redirect('Homepage:default');
    }

    private function checkLogin()
    {
        if ($this->user->isLoggedIn() == true)
        {
            $this->username = $this->database->findUserNick($this->user->getIdentity()->email);
            $this->status = "Přihlášený uživatel: $this->username"; //dodat proměnou s nickem uživatele


        }
        elseif ($this->user->isLoggedIn() == false)
        {
            $this->status = 'Nepřihlášený uživatel';


        }
    }


    private $hash;


    protected function createComponentLoginForm(): Form
    {
        $form = new Form;

        $form->addPassword('password')->setRequired('Vyplňte heslo')
            ->addRule(FORM::MIN_LENGTH, 'Heslo musí mít alespoň 6 znaků',6)
            ->addRule(Form::PATTERN, 'Heslo musí obsahovat číslo.', '.*[0-9].*');

//        $form->addPassword('val_password')->setRequired('Potvrďte heslo');

        $form->addEmail('email')->setRequired('Vyplňte e-mail');
        $form->addSubmit('send');
        $form->onSuccess[]=[$this,'loginFormSucceeded'];

        return $form;
    }
    public function loginFormSucceeded(Form $form, $values): void
    {
        if ($this->isAjax()) {
            try {
                $this->user->login($values->email, $values->password);
                $this->message = $this->flashMessage('Uspěšné přihlášení', 'succsess');

                $this->username = $this->database->findUserNick($values->email);
                $this->redrawControl('statuslogin');
                $this->redrawControl('alertMessage');
//                $this->redirect('Homepage:default');
            } catch (AuthenticationException $e) {
                $mess = $e->getMessage();
                $this->message = $this->flashMessage($mess, 'error');
//                $this->message = $mess;
                $this->redrawControl('alertMessage');
//                if ($this->message == 'Neplatné uživatelské jméno.') {
//                    $this->template->message = $this->flashMessage($this->message, 'error');
//                } elseif ($this->message == 'Neplatné heslo.') {
//                    $this->template->message = $this->flashMessage($this->message, 'error');
//                } elseif ($this->message == 'Nepotvrzená registrace.') {
//                    $this->template->message = $this->flashMessage($this->message, 'error');
//                }

            }
        }
    }

    //Register procedure
    protected function createComponentRegisterForm()
    {
        $form = new Form;
        $form->addPassword('password')->setRequired('Vyplňte heslo')
            ->addRule(FORM::MIN_LENGTH, 'Heslo musí mít alespoň 6 znaků',6)
            ->addRule(Form::PATTERN, 'Heslo musí obsahovat číslo.', '.*[0-9].*');

        $form->addPassword('val_password')->setRequired('Potvrďte heslo');

        $form->addEmail('email')->setRequired('Vyplňte e-mail');
        $form->addText('nick')->setRequired('Vyplňte jmého nebo nick');
        $form->addSubmit('send');
        $form->onSuccess[]=[$this,'registerFormSucceeded'];

        return $form;

    }

    private $objPassword;

    public function registerFormSucceeded(Form $form, $values)
    {

        if ($values->password == $values->val_password)
        {
            foreach ($this->users as $row)
            {

                if($row->email == $values->email)
                {

                    $this->message = $this->flashMessage('Tento e-mail je již registrován.','error');
                    $this->redrawControl('modalContent');

                    exit();
                }

            }
        }
        elseif ($values->password <> $values->val_password)
        {

            $this->message = $this->flashMessage('Heslo neodpovídá.','error');

            $this->redrawControl('modalContent');
            exit();
        }

        $objPassword;
        $hash;

        //hash hesla


        $this->objPassword = new Passwords(PASSWORD_BCRYPT, ['cost' => 12]);
        $this->hash = $this->objPassword->hash($values->password);


        $this->database->register($values->email, $this->hash, $values->nick);


//        $this->template->text = $this->flashMessage('Registrační e-mail byl odeslán.','success');

        $this->redirect('Homepage:default');
    }

    private function hash($pass)
    {
        $this->hash = Passwords::hash($pass);
    }


    //view form
    protected function createComponentViewForm()
    {
        $form = new Form;
        $form->addText('artname')->setRequired('Vyplňte název článku');
        $form->addText('artsummary')->setRequired('Vyplňte obsah článku');
        $form->addText('artdate')->setRequired('Vyplňte datum vložení článku');
        $form->addText('artautor')->setRequired('Vyplňte autora článku');
        $form->addTextArea('artcontent');

        $form->addSubmit('send');
        $form->onSuccess[]=[$this,'viewFormSucceeded'];

        return $form;

    }
    public function viewFormSucceeded($form, $values)
    {
            if ($this->isAjax())
            {
//                $this->redirect('Homepage:default');
            }
//        $this->redirect('Homepage:default');
    }



    //edit form
    protected function createComponentEditForm()
    {
        $form = new Form;
        $form->addText('artid')->setRequired('Vyplňte název článku');
        $form->addText('arttitle')->setRequired('Vyplňte název článku');
        $form->addText('artsummary')->setRequired('Vyplňte obsah článku');
        $form->addText('artdate')->setRequired('Vyplňte datum vložení článku');
        $form->addText('artautor')->setRequired('Vyplňte autora článku');
        $form->addTextArea('artcontent')->setValue($this->dataRow["content"]);

        $form->addSubmit('send')->onClick[] = [$this, 'editFormSucceeded'];
        $form->onSuccess[]=[$this,'editFormSucceeded'];

        return $form;
    }
    public function editFormSucceeded($form, $values)
    {
        if ($this->isAjax())
        {
            $varDatum = new \DateTime($values->artdate);
            $formatedDatum = $varDatum->format('Y-m-d');

            $this->database->updateArticle($values->artid,$values->arttitle, $values->artsummary, $values->artcontent, $formatedDatum);
            $this->articles = $this->database->tbArticles();

            $this->redrawControl('items');

        }
        else
        {
            $this->redirect('Homepage:default');
        }

    }

    //add form

    protected function createComponentAddForm()
    {
        $form = new Form;
        $form->addText('arttitle')->setRequired('Vyplňte název článku');
        $form->addText('artsummary')->setRequired('Vyplňte obsah článku');
        $form->addText('artdate')->setRequired('Vyplňte datum vložení článku');
        $form->addText('artautor')->setValue($this->user->getIdentity()->name);
        $form->addTextArea('artcontent');

        $form->addSubmit('send')->onClick[] = [$this, 'addFormSucceeded'];
//        $form->onSuccess[]=[$this,'addFormSucceeded'];

        return $form;
    }
    public function addFormSucceeded($form, $values)
    {
        if ($this->isAjax())
        {
            $varDatum = new \DateTime($values->artdate);
            $formatedDatum = $varDatum->format('Y-m-d');

            $this->database->insertArticles($this->user->getId(), $values->arttitle, $values->artsummary, $values->artcontent, $formatedDatum);

//            $this->redirect('Homepage:default');
        }
        $this->redirect('Homepage:default');
    }
}
