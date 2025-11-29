<?php
    session_start();
    include __DIR__ . "/../config/databasec.php"; // must define $conn
    include __DIR__ . "/../model/Borrow.php";
    $database = new Database();
    $conn = $database->getconnection();

    // Make sure user is logged in
    if(!isset($_SESSION['user_id'])){
        die("You must be logged in to borrow books.");
    }

    $user_id = $_SESSION['user_id'];

    if($_SERVER["REQUEST_METHOD"] == 'POST'){
        // BORROW PROCESS
        if(isset($_POST['borrow'])){
        $borrow_title = trim($_POST['booktitle']);
        $borrow_date = trim($_POST['borrowdate']);
        $return_date = trim($_POST['returndate']);
        $book_id = $_POST['book_id'];

        if(empty($borrow_title) || empty($borrow_date) || empty($return_date)){
            die("<script>alert('All fields are required!'); window.history.back();</script>");
        }

        $borrow = new Borrow($borrow_title, $borrow_date, $return_date);
        $borrow->setuserandbookinfo($book_id, $user_id);

        // Borrow book and get alert message
        $message = $borrow->borrow_book($conn);
        
    }

    // RESERVATION PROCESS (placeholder)
    if(isset($_POST['reservation'])){
        $reservationtitle = trim($_POST['reservetitle']);
        $reservationdate = trim($_POST['reservedate']);
        $pickupdate = trim($_POST['pickupdate']);

        // Implement reservation logic here
    }
}
?>
