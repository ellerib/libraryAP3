<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1.0" />
<title>Student Dashboard - Library System</title>

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

<!-- SIDEBAR -->
<div class="sidebar">
    <h2>Welcome to Library<br>Student Panel</h2>
    <button onclick="openModal()">Borrow</button>
    <button onclick="openReserve()">Reservation</button>
    <button>Penalties</button>
</div>

<!-- TOPBAR -->
<div class="topbar">
    <form action="logout.php" method="post">
        <button type="submit" class="logout-btn">Logout</button>
    </form>
</div>

<!-- MAIN CONTENT -->
<div class="main-content">

    <div class="cards">
        <div class="card"><div class="card-header">Total Borrowed</div><div class="card-body">3</div></div>
        <div class="card"><div class="card-header">Reservations</div><div class="card-body">2</div></div>
        <div class="card"><div class="card-header">Penalties</div><div class="card-body">₱50</div></div>
    </div>

    <table>
        <tr>
            <th>Book Title</th>
            <th>Date Borrowed</th>
            <th>Return Date</th>
        </tr>
        <!-- PHP rows inserted here -->
        <?php if(isset($result)) while ($row = $result->fetch_assoc()): ?>
        <tr>
            <td><?php echo $row['borrow_title']; ?></td>
            <td><?php echo $row['borrow_date']; ?></td>
            <td><?php echo $row['return_date']; ?></td>
        </tr>
        <?php endwhile; ?>
    </table>
</div>

<!-- MODALS -->
<div class="modal-bg" id="borrowModal">
    <div class="modal-box">
        <h3>Add Borrow Info</h3>
        <form action="../controller/libraryprocess.php" method="post">
            <input type="text" name="booktitle" placeholder="Book Title">
            <input type="date" name="borrowdate">
            <input type="date" name="returndate">
            <button class="modal-btn" name="borrow">Submit</button>
            <button type="button" class="modal-btn" style="background:#777" onclick="closeBorrowModal()">Cancel</button>
        </form>
    </div>
</div>

<div class="modal-bg" id="reserveModal">
    <div class="modal-box">
        <h3>Add Reservation</h3>
        <form action="../controller/libraryprocess.php" method="post">
            <input type="text" name="reservetitle" placeholder="Book Title">
            <input type="date" name="reservedate">
            <input type="date" name="pickupdate">
            <button class="modal-btn" name="reservation">Submit</button>
            <button type="button" class="modal-btn" style="background:#777" onclick="closeReserveModal()">Cancel</button>
        </form>
    </div>
</div>

<script>
function openModal(){ document.getElementById("borrowModal").style.display="flex"; }
function closeBorrowModal(){ document.getElementById("borrowModal").style.display="none"; }

function openReserve(){ document.getElementById("reserveModal").style.display="flex"; }
function closeReserveModal(){ document.getElementById("reserveModal").style.display="none"; }
</script>

</body>
</html>