<?php
session_start();
include __DIR__ . "/../config/databasec.php";

if(!isset($_SESSION['user_id'], $_SESSION['user_type']) || $_SESSION['user_type'] !== 'staff') {
    header("Location: ../view/login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Manual semester override for presentation
if (isset($_GET['force_sem'])) {
    $_SESSION['force_semester'] = (int)$_GET['force_sem'];
}

$database = new Database();
$conn = $database->getconnection();


// BORROWS (GROUP BY SEMESTER)
$borrows_sem1 = $conn->query("
    SELECT b.*, u.firstname, u.lastname, bo.title
    FROM borrow b
    JOIN users u ON b.user_id = u.user_id
    JOIN books bo ON b.book_id = bo.book_id
    WHERE b.semester = 1
    ORDER BY b.borrow_date DESC
");

$borrows_sem2 = $conn->query("
    SELECT b.*, u.firstname, u.lastname, bo.title
    FROM borrow b
    JOIN users u ON b.user_id = u.user_id
    JOIN books bo ON b.book_id = bo.book_id
    WHERE b.semester = 2
    ORDER BY b.borrow_date DESC
");



// RESERVATIONS (GROUP BY SEMESTER)
$res_sem1 = $conn->query("
    SELECT r.*, u.firstname, u.lastname, bo.title
    FROM reservation r
    JOIN users u ON r.user_id = u.user_id
    JOIN books bo ON r.book_id = bo.book_id
    WHERE r.semester = 1
    ORDER BY r.reservation_date DESC
");

$res_sem2 = $conn->query("
    SELECT r.*, u.firstname, u.lastname, bo.title
    FROM reservation r
    JOIN users u ON r.user_id = u.user_id
    JOIN books bo ON r.book_id = bo.book_id
    WHERE r.semester = 2
    ORDER BY r.reservation_date DESC
");

// PENALTIES (GROUP BY SEMESTER)
$pen_sem1 = $conn->query("
    SELECT p.*, u.firstname, u.lastname, b.title AS book_title
    FROM penalties p
    JOIN borrow br ON p.borrow_id = br.borrow_id
    JOIN users u ON br.user_id = u.user_id
    JOIN books b ON br.book_id = b.book_id
    WHERE br.semester = 1
    ORDER BY p.penalty_id DESC
");

$pen_sem2 = $conn->query("
    SELECT p.*, u.firstname, u.lastname, b.title AS book_title
    FROM penalties p
    JOIN borrow br ON p.borrow_id = br.borrow_id
    JOIN users u ON br.user_id = u.user_id
    JOIN books b ON br.book_id = b.book_id
    WHERE br.semester = 2
    ORDER BY p.penalty_id DESC
");




?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Staff Dashboard</title>
<style>
/* Basic styling for dashboard tables */
    body { font-family: Arial, sans-serif; background:#f2f5f7; padding:20px;}
    h2 { color:#24412f; margin-bottom:10px; }
    table { width:100%; border-collapse: collapse; margin-bottom:30px; background:white; box-shadow:0 4px 10px rgba(0,0,0,0.1); }
    th, td { padding:12px; border-bottom:1px solid #ddd; text-align:left; }
    th { background:#24412f; color:white; }
    tr:hover { background:#f4f8f5; }
    .action-btn { padding:6px 12px; border:none; border-radius:6px; cursor:pointer; color:white; font-size:.9rem; }
    .return-btn { background:#4a7c59; }
    .approve-btn { background:#2f5c43; }
    .pay-btn { background:#4a90e2; }
    .topbar {
       
        left: 250px; right: 0; top: 0;
        height: 60px;
        background: white;
        box-shadow: 0px 2px 6px rgba(0,0,0,0.1);
        display: flex;
        justify-content: flex-end;
        align-items: center;
        padding: 0 20px;
    }

    .logout-btn {
        padding:8px 18px;
        background:#24412f;
        color:white;
        border-radius:6px;
        border:none;
        cursor:pointer;
    }
</style>
</head>
<body>

<div class="topbar">
    <form action="/../controller/logout.php" method="post">
        <button type="submit" class="logout-btn">Logout</button>
    </form>
</div>

<h2>Borrowed Books — 1st Semester</h2>
<table>
<tr>
    <th>User</th><th>Book</th><th>Borrow Date</th>
    <th>Return Date</th><th>Status</th><th>Action</th>
</tr>
<?php while($row = $borrows_sem1->fetch_assoc()): ?>
<tr>
    <td><?= $row['firstname'].' '.$row['lastname'] ?></td>
    <td><?= $row['title'] ?></td>
    <td><?= $row['borrow_date'] ?></td>
    <td><?= $row['return_date'] ?></td>
    <td><?= $row['status'] ?></td>
    <td>
        <?php if($row['status'] != 'Returned'): ?>
        <form method="post" action="../controller/staffprocess.php">
            <input type="hidden" name="borrow_id" value="<?= $row['borrow_id'] ?>">
            <button name="mark_returned" class="action-btn return-btn">Mark Returned</button>
        </form>
        <?php else: ?>✔<?php endif; ?>
    </td>
</tr>
<?php endwhile; ?>
</table>

<h2>Borrowed Books — 2st Semester</h2>
<table>
<tr>
    <th>User</th><th>Book</th><th>Borrow Date</th>
    <th>Return Date</th><th>Status</th><th>Action</th>
</tr>
<?php while($row = $borrows_sem2->fetch_assoc()): ?>
<tr>
    <td><?= $row['firstname'].' '.$row['lastname'] ?></td>
    <td><?= $row['title'] ?></td>
    <td><?= $row['borrow_date'] ?></td>
    <td><?= $row['return_date'] ?></td>
    <td><?= $row['status'] ?></td>
    <td>
        <?php if($row['status'] != 'Returned'): ?>
        <form method="post" action="../controller/staffprocess.php">
            <input type="hidden" name="borrow_id" value="<?= $row['borrow_id'] ?>">
            <button name="mark_returned" class="action-btn return-btn">Mark Returned</button>
        </form>
        <?php else: ?>✔<?php endif; ?>
    </td>
</tr>
<?php endwhile; ?>
</table>


<h2>Reservations - 1st Semester </h2>
<table>
    <tr>
        <th>User</th>
        <th>Book</th>
        <th>Reserve Date</th>
        <th>Pickup Date</th>
        <th>Status</th>
        <th>Action</th>
    </tr>
    <?php while($row = $res_sem1->fetch_assoc()): ?>
    <tr>
        <td><?php echo $row['firstname'].' '.$row['lastname']; ?></td>
        <td><?php echo $row['title']; ?></td>
        <td><?php echo $row['reservation_date']; ?></td>
        <td><?php echo $row['pickup_date']; ?></td>
        <td><?php echo $row['status']; ?></td>
        <td>
            <?php if($row['status'] != 'picked up'): ?>
            <form method="post" action="../controller/staffprocess.php">
                <input type="hidden" name="reserve_id" value="<?php echo $row['reservation_id']; ?>">
                <button type="submit" name="approve_reserve" class="action-btn approve-btn">Approve</button>
            </form>
            <?php else: echo "✔"; endif; ?>
        </td>
    </tr>
    <?php endwhile; ?>
</table>

<h2>Reservations - 2nd Semester </h2>
<table>
    <tr>
        <th>User</th>
        <th>Book</th>
        <th>Reserve Date</th>
        <th>Pickup Date</th>
        <th>Status</th>
        <th>Action</th>
    </tr>
    <?php while($row = $res_sem2->fetch_assoc()): ?>
    <tr>
        <td><?php echo $row['firstname'].' '.$row['lastname']; ?></td>
        <td><?php echo $row['title']; ?></td>
        <td><?php echo $row['reservation_date']; ?></td>
        <td><?php echo $row['pickup_date']; ?></td>
        <td><?php echo $row['status']; ?></td>
        <td>
            <?php if($row['status'] != 'picked up'): ?>
            <form method="post" action="../controller/staffprocess.php">
                <input type="hidden" name="reserve_id" value="<?php echo $row['reservation_id']; ?>">
                <button type="submit" name="approve_reserve" class="action-btn approve-btn">Approve</button>
            </form>
            <?php else: echo "✔"; endif; ?>
        </td>
    </tr>
    <?php endwhile; ?>
</table>

<h2>Penalties - 1st Sem </h2>
<table>
    <tr>
        <th>User</th>
        <th>Book</th>
        <th>Amount</th>
        <th>Status</th>
        <th>Action</th>
    </tr>
    <?php while($row = $pen_sem1->fetch_assoc()): ?>
    <tr>
        <td><?php echo $row['firstname'].' '.$row['lastname']; ?></td>
        <td><?php echo $row['book_title']; ?></td>
        <td><?php echo $row['amount']; ?></td>
        <td><?php echo $row['status']; ?></td>
        <td>
            <?php if($row['status'] != 'paid'): ?>
            <form method="post" action="../controller/staffprocess.php">
                <input type="hidden" name="penalty_id" value="<?php echo $row['penalty_id']; ?>">
                <button type="submit" name="mark_paid" class="action-btn pay-btn">Mark Paid</button>
            </form>
            <?php else: echo "✔"; endif; ?>
        </td>
    </tr>
    <?php endwhile; ?>
</table>

<h2>Penalties - 2nd Sem </h2>
<table>
    <tr>
        <th>User</th>
        <th>Book</th>
        <th>Amount</th>
        <th>Status</th>
        <th>Action</th> 
    </tr>
    <?php while($row = $pen_sem2->fetch_assoc()): ?>
    <tr>
        <td><?php echo $row['firstname'].' '.$row['lastname']; ?></td>
        <td><?php echo $row['book_title']; ?></td>
        <td><?php echo $row['amount']; ?></td>
        <td><?php echo $row['status']; ?></td>
        <td>
            <?php if($row['status'] != 'paid'): ?>
            <form method="post" action="../controller/staffprocess.php">
                <input type="hidden" name="penalty_id" value="<?php echo $row['penalty_id']; ?>">
                <button type="submit" name="mark_paid" class="action-btn pay-btn">Mark Paid</button>
            </form>
            <?php else: echo "✔"; endif; ?>
        </td>
    </tr>
    <?php endwhile; ?>
</table>

</body>
</html>
