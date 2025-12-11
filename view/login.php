<?php
session_start();
include_once __DIR__ . "/../model/User.php";

// Redirect already logged-in users
User::checkLogin();

// Handle form submission
if (isset($_POST['login'])) {
    $email = $_POST['email'];
    $password = $_POST['password'];

    $user = new User("", "", $email, $password); // Role is not needed for login
    $user->login(); // This will handle session and redirect
}
?>

<!DOCTYPE html>
<html lang="en">   
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Library System Login</title>
<style>
    /* === General Page Styles === */
    body {
        margin: 0;
        font-family: Arial, sans-serif;
        background-color: white;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        height: 100vh;
    }

    header {
        width: 100%;
        background-color: #244a32;
        color: white;
        padding: 12px 30px;
        font-weight: bold;
        font-size: 18px;
        position: absolute;
        top: 0;
        left: 0;
    }

    .loginform {
        background-color: #326b45;
        padding: 40px 50px;
        border-radius: 20px;
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 15px;
        box-shadow: 0 4px 10px rgba(0, 0, 0, 0.3);
        width: 320px;
        margin-top: 80px;
    }

    form {
        width: 100%;
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 10px;
    }

    input[type="text"],
    input[type="password"] {
        width: 100%;
        padding: 10px;
        border-radius: 8px;
        border: none;
        outline: none;
        background-color: white;
        font-size: 14px;
        font-weight: bold;
    }

    button {
        width: 100%;
        padding: 10px;
        background-color: #1c3726;
        color: white;
        border: none;
        border-radius: 8px;
        font-weight: bold;
        cursor: pointer;
        font-size: 15px;
    }

    button:hover { background-color: #21402b; }

    .create-btn {
        display: inline-block;
        width: auto;
        padding: 8px 14px;
        margin-top: 5px;
        background-color: #1c3726;
        border-radius: 6px;
        font-size: 14px;
        font-weight: bold;
        text-align: center;
        color: white;
    }

    .create-btn:hover { background-color: #21402b; }

    .footer {
        position: absolute;
        bottom: 0;
        width: 100%;
        background-color: #244a32;
        height: 40px;
    }

    @media (max-width: 400px) {
        .loginform {
            width: 85%;
            padding: 30px;
        }
    }
</style>
</head>
<body>

<header>Library System</header>

<div class="loginform">
    <form action="" method="post">  
        <input type="text" name="email" placeholder="Email" required>
        <input type="password" name="password" placeholder="Password" required>

        <button type="submit" name="login">Log-In</button>

        <a href="../view/register.php" class="create-btn">Create New User</a>
    </form>
</div>

<div class="footer"></div>

</body>
</html>
