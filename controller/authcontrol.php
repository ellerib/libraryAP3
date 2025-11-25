<?php
    ob_start();

    require_once "user.php";
    require_once "database.php";
    class Authcontrol{
        public function loginform(){
            include "view/login.php";
        }

        public function login(){
            if($_SERVER['REQUEST_METHOD']=='POST'){
                $login_email = $_POST['email'];
                $login_password = $_POST['password'];

                $login_user = new User("","", $login_email, $login_password, "");
                $login_user->login();
                
            }

        }

        public function register(){
            require_once "register.php";

            if($_SERVER['REQUEST_METHOD']=='POST'){
                $lastname = $_POST['lastname'];
                $firstname = $_POST['firstname'];
                $email = $_POST['email'];
                $password = $_POST['password'];
                $role = $_POST['role'];

                // IF EMAIL IS EMPTY
                if(empty($email)){
                    return "<script> alert('Email must not be empty') <script>";
                }

                // CHECKING IF EMAIL IS VALID OR NOT
                $email->verify_email();

                $new_user = new User($lastname, $firstname, $email, $password, $role);
                $new_user->register();

            }
        }


    }   

    ob_end_flush();


?>