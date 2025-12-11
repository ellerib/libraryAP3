<?php
session_start();
include __DIR__ . "/../config/databasec.php";
$database = new Database();
$conn = $database->getconnection();

// Session user info
$user_id = $_SESSION['user_id'] ?? null;

if(!$user_id){
    header("Location: login.php");
    exit();
}

// Fetch penalties
$stmt = $conn->prepare("
    SELECT b.title AS book_title, br.borrow_date, br.return_date,
           p.amount AS penalty_amount, p.status
    FROM penalties p
    JOIN borrow br ON p.borrow_id = br.borrow_id
    JOIN books b ON br.book_id = b.book_id
    WHERE p.user_id = ?
    ORDER BY br.return_date DESC
");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$penalties = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
?>
<!DOCTYPE html>
<html lang='en'>
<head>
<meta charset='UTF-8'>
<title>My Penalties</title>
<style>
*{margin:0;padding:0;box-sizing:border-box;font-family:Roboto,sans-serif;}
body{background:#f3f6f4;}
.sidebar{
    position:fixed;top:0;left:0;width:250px;height:100vh;
    background:#24412f;color:white;padding:25px;
}
.sidebar h2{margin-bottom:30px;font-weight:300;}
.sidebar button{
    width:100%;padding:12px;border:none;border-radius:8px;
    background:#2f5c43;color:white;margin-bottom:10px;cursor:pointer;
}
.sidebar button:hover{background:#3b7b55;}
.topbar{
    position:fixed;top:0;left:250px;right:0;height:60px;background:white;
    box-shadow:0px 2px 6px rgba(0,0,0,0.1);
    display:flex;align-items:center;justify-content:flex-end;padding:0 20px;
}
.logout-btn{
    padding:8px 18px;background:#24412f;color:white;border:none;border-radius:6px;
}
.main-content{margin-left:250px;padding:90px 30px;}
table{
    width:100%;border-collapse:collapse;margin-top:30px;
    background:white;border-radius:10px;overflow:hidden;
    box-shadow:0 4px 10px rgba(0,0,0,0.15);
}
th,td{padding:14px;border-bottom:1px solid #eee;}
th{background:#24412f;color:white;}
tr:hover{background:#f2f7f3;}
</style>
</head>
<body>

<div class="sidebar">
    <h2>My Penalties</h2>
    <button onclick="window.location.href='studentpage.php'">Dashboard</button>
    <button onclick="window.location.href='viewreservation.php'">View Reservations</button>
    <button onclick="window.location.href='viewpenalties.php'">View Penalties</button>
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
            <th>Borrow Date</th>
            <th>Return Date</th>
            <th>Penalty (₱)</th>
        </tr>

        <?php if(!empty($penalties)): ?>
            <?php foreach($penalties as $row): ?>
                <tr>
                    <td><?= htmlspecialchars($row['book_title']) ?></td>
                    <td><?= $row['borrow_date'] ?></td>
                    <td><?= $row['return_date'] ?></td>
                    <td><?= number_format($row['penalty_amount'], 2) ?></td>
                </tr>
            <?php endforeach; ?>
        <?php else: ?>
            <tr><td colspan="4" style="text-align:center;">No penalties found.</td></tr>
        <?php endif; ?>
    </table>
</div>

</body>
</html>
