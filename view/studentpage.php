<?php
session_start();
include __DIR__ . "/../config/databasec.php";
$database = new Database();
$conn = $database->getconnection();

// Assuming your session stores user info
$user_id = $_SESSION['user_id'] ?? null;
$user_type = $_SESSION['user_type'] ?? null;

if(!$user_id){
    header("Location: login.php");
    exit();
}

// Fetch all available books
$books_result = $conn->query("SELECT book_id, title, quantity, price FROM books WHERE quantity > 0");
$books = [];
while($row = $books_result->fetch_assoc()){
    $books[] = $row;
}

// Fetch borrowed books by user
$stmt = $conn->prepare("
    SELECT b.title AS borrow_title, bb.borrow_date, bb.return_date, b.price
    FROM borrow bb
    JOIN books b ON bb.book_id = b.book_id
    WHERE bb.user_id = ?
");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

// Count borrowed books
$stmt2 = $conn->prepare("SELECT COUNT(*) as count FROM borrow WHERE user_id = ?");
$stmt2->bind_param("i", $user_id);
$stmt2->execute();
$borrowCount = $stmt2->get_result()->fetch_assoc()['count'];

// Calculate total penalty for overdue books
$penalty = 0;
$borrowedBooks = [];
while($row = $result->fetch_assoc()){
    $status = (strtotime($row['return_date']) < time()) ? 'Overdue' : 'Borrowed';
    if($status === 'Overdue'){
        $penalty += $row['price']; // Assuming penalty = book price
    }
    $row['status'] = $status;
    $borrowedBooks[] = $row;
}
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
    <button onclick="openBorrowModal()" <?php echo ($borrowCount >= 3) ? 'disabled style="background:#999;cursor:not-allowed;"' : ''; ?>>Borrow</button>
    <button onclick="openReserveModal()">Reserve</button>
    <button>Penalties</button>
    <button>View Reservations</button>
</div>

<!-- Topbar -->
<div class="topbar">
    <form action="logout.php" method="post">
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
            <div class="card-body">--</div>
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
            <th>Status</th>
        </tr>
        <?php foreach($borrowedBooks as $row): ?>
        <tr style="<?php echo ($row['status'] === 'Overdue') ? 'background:#fdd;' : ''; ?>">
            <td><?php echo htmlspecialchars($row['borrow_title']); ?></td>
            <td><?php echo $row['borrow_date']; ?></td>
            <td><?php echo $row['return_date']; ?></td>
            <td><?php echo $row['status']; ?></td>
        </tr>
        <?php endforeach; ?>
    </table>
</div>

<!-- Borrow Modal -->
<div class="modal-bg" id="borrowModal">
    <div class="modal-box">
        <h3>Borrow Book</h3>
        <form method="post" action="../controller/libraryprocess.php">
            <select name="book_id" required>
                <option value="">Select Book</option>
                <?php foreach($books as $book): ?>
                    <option value="<?php echo $book['book_id']; ?>">
                        <?php echo htmlspecialchars($book['title']); ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <input type="date" name="borrow_date" required>
            <input type="date" name="return_date" required>
            <button class="modal-btn" name="borrow">Submit</button>
            <button type="button" class="modal-btn" style="background:#777" onclick="closeBorrowModal()">Cancel</button>
        </form>
    </div>
</div>

<!-- Reserve Modal -->
<div class="modal-bg" id="reserveModal">
    <div class="modal-box">
        <h3>Reserve Book</h3>
        <form method="post" action="../controller/libraryprocess.php">
            <input type="text" name="reserve_title" placeholder="Book Title">
            <input type="date" name="reserve_date">
            <input type="date" name="pickup_date">
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
