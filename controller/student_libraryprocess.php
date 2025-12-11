<?php
session_start();
include __DIR__ . "/../config/databasec.php";
include __DIR__ . "/../model/Borrow.php";
include __DIR__ . "/../model/reserve.php";

$database = new Database();
$conn = $database->getconnection();

// Check login
$user_id = $_SESSION['user_id'] ?? null;
$user_type = $_SESSION['user_type'] ?? null;

if(!$user_id || $user_type !== 'student'){
    die("Access denied.");
}

if ($_SERVER["REQUEST_METHOD"] == 'POST') {

    // ---------- BORROW ----------
    if (isset($_POST['borrow'])) {
        $book_id = $_POST['book_id'];
        $borrow_date = trim($_POST['borrowdate']);
        $return_date = trim($_POST['returndate']);
        $semester = intval($_POST['semester']); // New semester input

        if(empty($book_id) || empty($borrow_date) || empty($return_date) || empty($semester)){
            die("<script>alert('All fields are required!'); window.history.back();</script>");
        }

        // Check max books per semester
        $maxBooks = 3;
        $stmtCount = $conn->prepare("SELECT COUNT(*) AS count 
                                     FROM borrow 
                                     WHERE user_id=? AND status IN ('Borrowed','Overdue') AND semester=?");
        $stmtCount->bind_param("ii", $user_id, $semester);
        $stmtCount->execute();
        $currentCount = $stmtCount->get_result()->fetch_assoc()['count'];

        if($currentCount >= $maxBooks){
            header("Location: ../view/studentpage.php?error=max_limit");
            exit();
        }

        // Borrow book
        $borrow = new Borrow($borrow_date, $return_date);
        $borrow->setuserandbookinfo($book_id, $user_id, $semester); // Pass semester
        $borrow->borrow_book($conn);

        header("Location: ../view/studentpage.php?borrowed=success");
        exit();
    }

    // ---------- RESERVE ----------
    if (isset($_POST['reserve'])) {
        $reservebook_id = trim($_POST['reserve_book_id']);
        $reservation_date = trim($_POST['reserve_date']);
        $pickup_date = trim($_POST['pickup_date']);
        $semester = intval($_POST['semester']); // Add semester to reservation if needed

        if(empty($reservebook_id) || empty($reservation_date) || empty($pickup_date) || empty($semester)){
            die("<script>alert('All fields are required!'); window.history.back();</script>");
        }

        // Check if already reserved this book
        $stmtCheck = $conn->prepare("SELECT COUNT(*) AS count 
                                     FROM reservation 
                                     WHERE user_id=? AND book_id=? AND status IN ('Pending','Approved') AND semester=?");
        $stmtCheck->bind_param("iii", $user_id, $reservebook_id, $semester);
        $stmtCheck->execute();
        $count = $stmtCheck->get_result()->fetch_assoc()['count'];

        if($count > 0){
            header("Location: ../view/studentpage.php?error=already_reserved");
            exit();
        }

        // Reserve book
        $reserve_book = new Reservation($reservation_date, $pickup_date);
        $reserve_book->reservesetuserandbookinfo($reservebook_id, $user_id, $semester); // Pass semester
        $reserve_book->reserve_book($conn);

        header("Location: ../view/studentpage.php?reserved=success");
        exit();
    }
}
?>
