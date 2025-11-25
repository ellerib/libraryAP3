<?php
include "../config/databasec.php";
require_once "../model/user.php";

session_start();

if($_SERVER["REQUEST_METHOD"]=='POST'){

    // LOGIN
    if(isset($_POST['login'])){
        $email = trim($_POST['email']);
        $password = trim($_POST['password']);

        $login = new User("", "", $email, $password, "");
        $login->login();
    }

    // REGISTER
    if(isset($_POST['register'])){
        $lastname = trim($_POST['lastname']);
        $firstname = trim($_POST['firstname']);
        $email = trim($_POST['email']);
        $password = trim($_POST['password']);
        $role = trim($_POST['role']);

        $register = new User($lastname, $firstname, $email, $password, $role);
        $register->verify_email();
        $register->register();
    }
}

?>
