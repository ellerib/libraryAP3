<?php
    session_start();
    include_once __DIR__ . "/../model/User.php";

    if(isset($_POST['login'])) {
        $email = trim($_POST['email']);
        $password = trim($_POST['password']);

        $user = new User("", "", $email, $password);
        $user->login(); // will set session and redirect
    }

    // REGISTER
    if(isset($_POST['register'])){
        $lastname = trim($_POST['lastname']);
        $firstname = trim($_POST['firstname']);
        $email = trim($_POST['email']);
        $password = trim($_POST['password']);
        $role = trim($_POST['role']);

        $register = new User($lastname, $firstname, $email, $password, $role);
        $register->register();
    }

?>

