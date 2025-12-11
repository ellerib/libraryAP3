<?php
session_start();
include __DIR__ . "/../config/databasec.php";

if(!isset($_SESSION['user_id'], $_SESSION['user_type']) || $_SESSION['user_type'] !== 'teacher'){
    header("Location: ../view/login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$database = new Database();
$conn = $database->getconnection();

/* -------------------------
   FETCH AVAILABLE BOOKS
 ------------------------- */
$books = [];
$res = $conn->query("SELECT book_id, title, quantity, price 
                     FROM books 
                     WHERE quantity > 0 AND status='active'");
while($r = $res->fetch_assoc()) $books[] = $r;

/* -------------------------
   FETCH BORROWED BOOKS 
 ------------------------- */
$stmt = $conn->prepare("
    SELECT bo.title AS borrow_title, b.borrow_date, b.return_date, b.status, bo.price
    FROM borrow b
    JOIN books bo ON b.book_id = bo.book_id
    WHERE b.user_id = ? AND b.status IN ('Borrowed','Overdue')
");
$stmt->bind_param("i",$user_id);
$stmt->execute();
$borrowed = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

/* COUNT ACTIVE BORROWS */
$cntStmt = $conn->prepare("SELECT COUNT(*) AS cnt FROM borrow 
                           WHERE user_id=? AND status IN ('Borrowed','Overdue')");
$cntStmt->bind_param("i",$user_id);
$cntStmt->execute();
$borrowCount = intval($cntStmt->get_result()->fetch_assoc()['cnt']);

/* RESERVATION COUNT */
$rStmt = $conn->prepare("SELECT COUNT(*) AS cnt FROM reservation 
                         WHERE user_id=? AND status!='Completed'");
$rStmt->bind_param("i",$user_id);
$rStmt->execute();
$reservation_count = intval($rStmt->get_result()->fetch_assoc()['cnt']);

/* PENALTY CALC */
$penalty = 0;
foreach($borrowed as &$row){
    if(strtotime($row['return_date']) < time()){
        $row['status'] = 'Overdue';
        $penalty += (float)$row['price'];
    }
}
?>
<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>Teacher Dashboard</title>

<style>
/* SAME DESIGN AS STUDENT DASHBOARD */
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
    padding: 8px 18px;
    background: #24412f;
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

/* Modals */
.modal-bg {
    display:none;
    position:fixed;
    inset:0;
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
.modal-box input,
.modal-box select {
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

<!-- SIDEBAR -->
<div class="sidebar">
    <h2>Welcome Teacher</h2>
    <button onclick="openBorrowModal()">Borrow</button>
    <button onclick="openReserveModal()">Reserve</button>
    <button onclick="window.location.href='viewteacherreservation.php'">View Reservations</button>
    <button onclick="window.location.href='viewteacherpenalties.php'">View Penalties</button>
</div>

<!-- TOP BAR -->
<div class="topbar">
    <form action="/../controller/logout.php" method="post">
        <button type="submit" class="logout-btn">Logout</button>
    </form>
</div>

<!-- MAIN CONTENT -->
<div class="main-content">

    <div class="cards">
        <div class="card">
            <div class="card-header">Total Borrowed</div>
            <div class="card-body"><?php echo $borrowCount; ?></div>
        </div>

        <div class="card">
            <div class="card-header">Reservations</div>
            <div class="card-body"><?php echo $reservation_count; ?></div>
        </div>

        <div class="card">
            <div class="card-header">Total Penalties</div>
            <div class="card-body">₱<?php echo number_format($penalty,2); ?></div>
        </div>
    </div>

    <table>
        <tr>
            <th>Book Title</th>
            <th>Borrow Date</th>
            <th>Return Date</th>
            <th>Status</th>
        </tr>

        <?php if ($borrowed): ?>
            <?php foreach ($borrowed as $b): ?>
                <tr>
                    <td><?= htmlspecialchars($b['borrow_title']) ?></td>
                    <td><?= $b['borrow_date'] ?></td>
                    <td><?= $b['return_date'] ?></td>
                    <td><?= $b['status'] ?></td>
                </tr>
            <?php endforeach; ?>
        <?php else: ?>
            <tr><td colspan="4" style="text-align:center;">No active borrows</td></tr>
        <?php endif; ?>
    </table>
</div>

<!-- BORROW MODAL -->
<div id="borrowModal" class="modal-bg">
  <div class="modal-box">
    <h3>Borrow Book</h3>

    <form method="post" action="../controller/teacher_libraryprocess.php">
      <select name="book_id" required>
        <option value="">Select Book</option>
        <?php foreach ($books as $book): ?>
            <option value="<?= $book['book_id']; ?>"><?= htmlspecialchars($book['title']); ?></option>
        <?php endforeach; ?>
      </select>

      <label>Borrow Date</label>
      <input type="date" name="borrowdate" required>

      <label>Return Date</label>
      <input type="date" name="returndate" required>

      <label>Semester</label>
      <select name="semester" required>
          <option value="">Select Semester</option>
          <option value="1">1st Semester</option>
          <option value="2">2nd Semester</option>
      </select>

      <button name="borrow" class="modal-btn">Submit</button>
      <button type="button" class="modal-btn" onclick="closeBorrowModal()">Cancel</button>
    </form>
  </div>
</div>

<!-- RESERVE MODAL -->
<div id="reserveModal" class="modal-bg">
  <div class="modal-box">
    <h3>Reserve Book</h3>

    <form method="post" action="../controller/teacher_libraryprocess.php">
      <select name="reserve_book_id" required>
        <option value="">Select Book</option>
        <?php foreach ($books as $book): ?>
            <option value="<?= $book['book_id']; ?>"><?= htmlspecialchars($book['title']); ?></option>
        <?php endforeach; ?>
      </select>

      <label>Reservation Date</label>
      <input type="date" name="reserve_date" required>

      <label>Pickup Date</label>
      <input type="date" name="pickup_date" required>

      <label>Semester</label>
      <select name="semester" required>
          <option value="">Select Semester</option>
          <option value="1">1st Semester</option>
          <option value="2">2nd Semester</option>
      </select>

      <button name="reserve" class="modal-btn">Submit</button>
      <button type="button" class="modal-btn" onclick="closeReserveModal()">Cancel</button>
    </form>
  </div>
</div>

<script>
function openBorrowModal(){ document.getElementById('borrowModal').style.display='flex'; }
function closeBorrowModal(){ document.getElementById('borrowModal').style.display='none'; }
function openReserveModal(){ document.getElementById('reserveModal').style.display='flex'; }
function closeReserveModal(){ document.getElementById('reserveModal').style.display='none'; }
</script>

</body>
</html>
