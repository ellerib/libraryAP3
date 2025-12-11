<?php
session_start();
include __DIR__ . "/../config/databasec.php";

$database = new Database();
$conn = $database->getconnection();

// Make sure staff is logged in
if(!isset($_SESSION['user_type']) || $_SESSION['user_type'] != 'staff'){
    die("You must be logged in as staff to access this page.");
}

// MARK BORROW AS RETURNED
if(isset($_POST['mark_returned'])){
    $borrow_id = $_POST['borrow_id'];

    // Update borrow status and set actual return date
    $stmt = $conn->prepare("
        UPDATE borrow 
        SET status='Returned', return_actual_date=NOW() 
        WHERE borrow_id=?
    ");
    $stmt->bind_param("i", $borrow_id);
    $stmt->execute();

    // Fetch borrow info including return_actual_date
    $stmt2 = $conn->prepare("
        SELECT user_id, book_id, return_date, return_actual_date
        FROM borrow
        WHERE borrow_id=?
    ");
    $stmt2->bind_param("i", $borrow_id);
    $stmt2->execute();
    $borrow = $stmt2->get_result()->fetch_assoc();

    // Calculate penalty if overdue
    $due_date = new DateTime($borrow['return_date']);

    // Use actual return date ONLY if saved; otherwise use today's date
    if (!empty($borrow['return_actual_date'])) {
        $actual_date = new DateTime($borrow['return_actual_date']);
    } else {
        $actual_date = new DateTime(); // today's date
    }

    // Check for overdue
    if ($actual_date > $due_date) {
        $diff = $due_date->diff($actual_date);
        $days_overdue = $diff->days;
        $penalty_amount = $days_overdue * 5; // 5 units per day overdue

        // 4️⃣ Insert penalty record
       $stmt3 = $conn->prepare("
            INSERT INTO penalties (borrow_id, book_id, user_id, days_late, amount, status)
            VALUES (?, ?, ?, ?, ?, 'unpaid')
        ");
        $stmt3->bind_param("iiiid", $borrow_id, $borrow['book_id'], 
        $borrow['user_id'], $days_overdue, $penalty_amount);
        $stmt3->execute();


    }

    // update book quantity 
    $stmt4 = $conn->prepare("
        UPDATE books
        SET quantity = quantity + 1
        WHERE book_id=?
    ");
    $stmt4->bind_param("i", $borrow['book_id']);
    $stmt4->execute();
    
    header("Location: ../view/staffpage.php?borrow_returned=success");
    exit();
}


// APPROVE RESERVATION
if(isset($_POST['approve_reserve'])){
    $reserve_id = $_POST['reserve_id'];

    $stmt = $conn->prepare("UPDATE reservation SET status='Approved', pickup_date=NOW() WHERE reservation_id=?");
    $stmt->bind_param("i", $reserve_id);
    $stmt->execute();

    header("Location: ../view/staffpage.php?reserve_approved=success");
    exit();
}

// MARK PENALTY AS PAID
if(isset($_POST['mark_paid'])){
    $penalty_id = $_POST['penalty_id'];

    $stmt = $conn->prepare("UPDATE penalties SET status='paid' WHERE penalty_id=?");
    $stmt->bind_param("i", $penalty_id);
    $stmt->execute();

    header("Location: ../view/staffpage.php?penalty_paid=success");
    exit();
}
?>
