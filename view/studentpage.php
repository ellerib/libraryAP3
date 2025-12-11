<?php
session_start();
include __DIR__ . "/../config/databasec.php";

if (!isset($_SESSION['user_id'], $_SESSION['user_type']) || $_SESSION['user_type'] !== 'student') {
    header("Location: ../view/login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$database = new Database();
$conn = $database->getconnection();

// Fetch available books (active)
$books = [];
$bookQuery = "SELECT book_id, title, quantity, price FROM books WHERE quantity > 0 AND status = 'active'";
$result = $conn->query($bookQuery);
while ($row = $result->fetch_assoc()) {
    $books[] = $row;
}

// Fetch borrowed books (only active borrows: Borrowed or Overdue)
$borrowedBooks = [];
$stmt = $conn->prepare("
    SELECT bo.title AS borrow_title, b.borrow_date, b.return_date, b.status, bo.price
    FROM borrow b
    JOIN books bo ON b.book_id = bo.book_id
    WHERE b.user_id = ? AND b.status IN ('Borrowed','Overdue')
    ORDER BY b.borrow_date DESC
");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$borrowedBooks = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

// Count active borrows (Borrowed or Overdue)
$stmtCount = $conn->prepare("SELECT COUNT(*) AS cnt FROM borrow WHERE user_id = ? AND status IN ('Borrowed','Overdue')");
$stmtCount->bind_param("i", $user_id);
$stmtCount->execute();
$borrowCount = $stmtCount->get_result()->fetch_assoc()['cnt'] ?? 0;

// Count active reservations (Pending or Approved)
$stmtRes = $conn->prepare("SELECT COUNT(*) AS cnt FROM reservation WHERE user_id = ? AND status IN ('Pending','Approved')");
$stmtRes->bind_param("i", $user_id);
$stmtRes->execute();
$reservation_count = $stmtRes->get_result()->fetch_assoc()['cnt'] ?? 0;

// Calculate penalties (simple sum using book price for overdue items)
$penalty = 0.00;
foreach ($borrowedBooks as &$row) {
    if (!empty($row['return_date']) && strtotime($row['return_date']) < time() && $row['status'] === 'Borrowed') {
        // mark as overdue locally (DB update of status to 'Overdue' should be done by cron or staff process)
        $row['status'] = 'Overdue';
    }
    if ($row['status'] === 'Overdue') {
        $penalty += floatval($row['price']);
    }
}
unset($row); // good practice
?>



<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Student Dashboard</title>

    <style>
    * { margin:0; padding:0; box-sizing:border-box; font-family: "Roboto", sans-serif; }
    body { background:#f3f6f4; }

    /* Sidebar */
    .sidebar {
        position: fixed;
        top: 0; left: 0;
        width: 250px;
        height: 100vh;
        background: #24412f;
        padding: 25px;
        color: white;
        display: flex;
        flex-direction: column;
        gap: 15px;
        box-shadow: 4px 7px 15px rgba(0,0,0,0.25);
    }

    .sidebar h2 {
        font-weight: 300;
        line-height: 1.4em;
        margin-bottom: 30px;
    }

    .sidebar button {
        width: 100%;
        border: none;
        padding: 12px;
        border-radius: 8px;
        background: #2f5c43;
        color: white;
        font-size: 1rem;
        cursor: pointer;
        transition: .3s;
    }
    .sidebar button:hover { background:#3b7b55; }

    /* Top Bar */
    .topbar {
        position: fixed;
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

    /* Main Content */
    .main-content {
        margin-left: 250px;
        padding: 90px 30px 30px;
    }

    .cards {
        display:grid;
        grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
        gap:20px;
    }

    .card {
        background: white;
        border-radius:12px;
        box-shadow:0 4px 8px rgba(0,0,0,0.1);
        overflow:hidden;
        transition: .3s;
    }

    .card:hover { transform: translateY(-5px); }

    .card-header {
        background:#24412f;
        padding:12px;
        color:white;
        text-align:center;
        font-size:1.1rem;
    }

    .card-body {
        padding:25px;
        text-align:center;
        font-size:1.8rem;
        font-weight:600;
        color:#24412f;
    }

    /* Table */
    table {
        width:100%;
        margin-top:30px;
        border-collapse: collapse;
        background:white;
        border-radius:10px;
        overflow:hidden;
        box-shadow:0px 4px 10px rgba(0,0,0,0.15);
    }

    th, td {
        padding:14px;
        border-bottom:1px solid #eee;
        font-size:1rem;
    }

    th {
        background:#24412f;
        color:white;
        font-weight:400;
    }

    tr:hover { background:#f2f7f3; }

    /* Modal */
    .modal-bg {
        display:none;
        position:fixed;
        top:0; left:0;
        width:100%; height:100%;
        background:rgba(0,0,0,0.45);
        justify-content:center;
        align-items:center;
        z-index:10;
    }
    .modal-box select {
    width: 100%;
    padding: 10px;
    margin: 8px 0 15px;
    border: 1px solid #ccc;
    border-radius: 6px;
    background: white;
}


    .modal-box {
        background:white;
        width:360px;
        padding:25px;
        border-radius:12px;
        box-shadow:0 3px 12px rgba(0,0,0,0.25);
    }

    .modal-box h3 {
        margin-bottom:10px;
        font-size:1.3rem;
        color:#24412f;
    }

    .modal-box input {
        width:100%;
        padding:10px;
        margin:8px 0 15px;
        border:1px solid #ccc;
        border-radius:6px;
    }

    .modal-btn {
        background:#24412f;
        color:white;
        padding:8px 16px;
        border:none;
        border-radius:6px;
        cursor:pointer;
        margin-right:5px;
    }

</style>


</head>
<body>

<!-- Sidebar -->
<div class="sidebar">
    <h2>Welcome Student</h2>
    <button onclick="openBorrowModal()" <?php echo ($borrowCount >= 3) ? 'disabled style="background:#999;cursor:not-allowed;"' : ''; ?>>
    Borrow
</button>
    <button onclick="openReserveModal()">Reservation</button>
    <button onclick="window.location.href='viewreservation.php'"> View Reservations </button>
    <button onclick="window.location.href='viewpenalties.php'"> View Penalties</button>

    
</select>

</div>

<!-- Topbar -->
<div class="topbar">
    <form action="/../controller/logout.php" method="post">
        <button type="submit" class="logout-btn">Logout</button>
    </form>
</div>

<!-- Main Content -->
<div class="main-content">
    <div class="cards">
        <div class="card">
            <div class="card-header">Total Borrowed</div>
            <div class="card-body"><?php echo $borrowCount; ?></div>
        </div>
        <div class="card">
            <div class="card-header">Reservations</div>
            <div class="card-body"> <?php echo $reservation_count?></div>
        </div>
        <div class="card">
            <div class="card-header">Total Penalties</div>
            <div class="card-body">₱<?php echo $penalty; ?></div>
        </div>
    </div>

   <table>
    <tr>
        <th>Book Title</th>
        <th>Borrow Date</th>
        <th>Return Date</th>
    </tr>
    <?php foreach($borrowedBooks as $row): ?>
    <tr>
        <td><?php echo htmlspecialchars($row['borrow_title']); ?></td>
        <td><?php echo $row['borrow_date']; ?></td>
        <td><?php echo $row['return_date']; ?></td>
    </tr>
    <?php endforeach; ?>
</table>

</div>

<!-- Borrow Modal -->
<div class="modal-bg" id="borrowModal">
    <div class="modal-box">
        <h3>Borrow Book</h3>
        <form method="post" action="../controller/student_libraryprocess.php">
            <select name="book_id" required>
                <option value="">Select Book</option>
                <?php foreach($books as $book): ?>
                    <option value="<?php echo $book['book_id']; ?>">
                        <?php echo htmlspecialchars($book['title']); ?>
                    </option>
                <?php endforeach; ?>
            </select>

            <input type="date" name="borrowdate" required>
            <input type="date" name="returndate" required>

            <!-- Semester dropdown -->
            <select name="semester" required>
                <option value="">Select Semester</option>
                <option value="1">1st Semester</option>
                <option value="2">2nd Semester</option>
            </select>

            <button class="modal-btn" name="borrow">Submit</button>
            <button type="button" class="modal-btn" style="background:#777" onclick="closeBorrowModal()">Cancel</button>
        </form>
    </div>
</div>


<!-- Reserve Modal -->
<div class="modal-bg" id="reserveModal">
    <div class="modal-box">
        <h3>Reserve Book</h3>
        <form method="post" action="../controller/student_libraryprocess.php">
            <select name="reserve_book_id" required>
                <option value="">Select Book</option>
                <?php foreach($books as $book): ?>
                    <option value="<?php echo $book['book_id']; ?>">
                        <?php echo htmlspecialchars($book['title']); ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <input type="date" name="reserve_date" required>
            <input type="date" name="pickup_date" required>


            <!-- Semester dropdown -->
            <select name="semester" required>
                <option value="">Select Semester</option>
                <option value="1">1st Semester</option>
                <option value="2">2nd Semester</option>
            </select>
            
            <button class="modal-btn" name="reserve">Submit</button>
            <button type="button" class="modal-btn" style="background:#777" onclick="closeReserveModal()">Cancel</button>
        </form>
    </div>
</div>


<script>
function openBorrowModal(){ document.getElementById("borrowModal").style.display="flex"; }
function closeBorrowModal(){ document.getElementById("borrowModal").style.display="none"; }

function openReserveModal(){ document.getElementById("reserveModal").style.display="flex"; }
function closeReserveModal(){ document.getElementById("reserveModal").style.display="none"; }
</script>

</body>
</html>
