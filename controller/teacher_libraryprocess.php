<?php
session_start();
include __DIR__ . "/../config/databasec.php";
include __DIR__ . "/../model/Borrow.php";
include __DIR__ . "/../model/reserve.php";

$database = new Database();
$conn = $database->getconnection();

// Check login
if(!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'teacher'){
    die("Access denied.");
}

$user_id = $_SESSION['user_id'];

if ($_SERVER["REQUEST_METHOD"] == 'POST') {

    // BORROW PROCESS
    if (isset($_POST['borrow'])) {
        $borrow_date = trim($_POST['borrowdate']);
        $return_date = trim($_POST['returndate']);
        $book_id = $_POST['book_id'];
        $semester = intval($_POST['semester']); // New semester input

        if(empty($book_id) || empty($borrow_date) || empty($return_date) || empty($semester)) {
            die("<script>alert('All fields are required!'); window.history.back();</script>");
        }

        $borrow = new Borrow($borrow_date, $return_date);
        $borrow->setuserandbookinfo($book_id, $user_id, $semester); // Pass semester

        $message = $borrow->borrow_book($conn);
        echo "<script>alert('{$message}'); window.location.href='../view/teacherpage.php';</script>";
        exit();
    }

    // RESERVE PROCESS
    if (isset($_POST['reserve'])) {
        $reservation_date = trim($_POST['reserve_date']);
        $pickup_date = trim($_POST['pickup_date']);
        $reservebook_id = trim($_POST['reserve_book_id']);
        $semester = intval($_POST['semester']); // Pass semester

        if(empty($reservation_date) || empty($pickup_date) || empty($semester)) {
            die("<script>alert('All fields are required!'); window.history.back();</script>");
        }

        $reserve_book = new Reservation($reservation_date, $pickup_date);
        $reserve_book->reservesetuserandbookinfo($reservebook_id, $user_id, $semester); // Pass semester
        $reserve_book->reserve_book($conn);

        echo "<script>alert('Book reserved successfully'); window.location.href='../view/teacherpage.php';</script>";
        exit();
    }
}
?>
