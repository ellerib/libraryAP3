<?php
session_start();
include __DIR__ . "/../config/databasec.php";
$database = new Database();
$conn = $database->getconnection();

// Check if teacher is logged in
$teacher_id = $_SESSION['user_id'] ?? null;
if(!$teacher_id){
    header("Location: login.php");
    exit();
}

// Fetch all reservations
// Fetch ONLY the teacher's reservations
$stmt = $conn->prepare("
    SELECT r.reservation_id, b.title AS book_title, r.reservation_date, 
           r.pickup_date, r.status
    FROM reservation r
    JOIN books b ON r.book_id = b.book_id
    WHERE r.user_id = ? AND r.status = 'Pending'
    ORDER BY r.reservation_date DESC
");
$stmt->bind_param("i", $teacher_id);
$stmt->execute();
$reservations = $stmt->get_result();

?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>View Reservations - Teacher</title>
<style>
* { margin:0; padding:0; box-sizing:border-box; font-family: "Roboto", sans-serif; }
body { background:#f3f6f4; }

/* Sidebar */
.sidebar { position: fixed; top:0; left:0; width:250px; height:100vh; background:#24412f; padding:25px; color:white; display:flex; flex-direction:column; gap:15px; }
.sidebar h2 { font-weight:300; margin-bottom:30px; }
.sidebar button { width:100%; border:none; padding:12px; border-radius:8px; background:#2f5c43; color:white; cursor:pointer; transition:.3s; }
.sidebar button:hover { background:#3b7b55; }

/* Top Bar */
.topbar { position: fixed; left:250px; right:0; top:0; height:60px; background:white; box-shadow:0px 2px 6px rgba(0,0,0,0.1); display:flex; justify-content:flex-end; align-items:center; padding:0 20px; }
.logout-btn { padding:8px 18px; background:#24412f; color:white; border-radius:6px; border:none; cursor:pointer; }

/* Main Content */
.main-content { margin-left:250px; padding:90px 30px 30px; }

/* Table */
table { width:100%; margin-top:30px; border-collapse:collapse; background:white; border-radius:10px; overflow:hidden; box-shadow:0 4px 10px rgba(0,0,0,0.15); }
th, td { padding:14px; border-bottom:1px solid #eee; font-size:1rem; }
th { background:#24412f; color:white; font-weight:400; }
tr:hover { background:#f2f7f3; }
</style>
</head>
<body>

<div class="sidebar">
    <h2>Reservations</h2>
    <button onclick="window.location.href='teacherpage.php'">Dashboard</button>
    <button onclick="window.location.href='viewteacherreservation.php'">View Reservations</button>
    <button onclick="window.location.href='viewteacherpenalties.php'">View Penalties</button>
</div>

<div class="topbar">
    <form action="/../controller/logout.php" method="post">
        <button type="submit" class="logout-btn">Logout</button>
    </form>
</div>

<div class="main-content">
    <table>
        <tr>
            <th>Book Title</th>
            <th>Reservation Date</th>
            <th>Pickup Date</th>
            <th>Status</th>
        </tr>
        <?php if($reservations && $reservations->num_rows > 0): ?>
            <?php while($row = $reservations->fetch_assoc()): ?>
               <tr>
    <td><?php echo htmlspecialchars($row['book_title']); ?></td>
    <td><?php echo $row['reservation_date']; ?></td>
    <td><?php echo $row['pickup_date']; ?></td>
    <td><?php echo ucfirst($row['status']); ?></td>
</tr>

            <?php endwhile; ?>
        <?php else: ?>
            <tr><td colspan="5" style="text-align:center;">No reservations found.</td></tr>
        <?php endif; ?>
    </table>
</div>

</body>
</html>
