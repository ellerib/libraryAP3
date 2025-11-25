<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Student Dashboard - Library System</title>

  <style>
    * { box-sizing: border-box; }
    body {
      margin: 0;
      font-family: "Segoe UI", Arial, sans-serif;
      background-color: #fdfdfd;
    }

    .sidebar {
      position: fixed;
      top: 0; left: 0;
      width: 250px; height: 100%;
      background-color: #24412f;
      color: white;
      padding: 20px;
      display: flex;
      flex-direction: column;
    }

    .sidebar button {
      display: block;
      background-color: #335c42;
      color: white;
      text-decoration: none;
      border-radius: 20px;
      padding: 10px;
      margin: 8px 0;
      text-align: center;
      transition: 0.3s;
    }
    .sidebar button:hover { background-color: #3f7553; }

    .topbar {
      position: fixed;
      top: 0; left: 250px; right: 0;
      height: 60px;
      background-color: #fff;
      display: flex;
      justify-content: flex-end;
      align-items: center;
      padding: 0 20px;
      box-shadow: 0px 2px 5px rgba(0,0,0,0.1);
    }

    .logout-btn {
      background-color: #24412f;
      color: white;
      border: none;
      border-radius: 20px;
      padding: 8px 16px;
      cursor: pointer;
    }

    .main-content {
      margin-left: 250px;
      margin-top: 70px;
      padding: 20px;
    }

    .card-container {
      display: flex;
      gap: 20px;
      flex-wrap: wrap;
    }

    .card {
      flex: 1;
      min-width: 250px;
      background-color: #ccf9d5;
      border-radius: 10px;
      box-shadow: 0 2px 5px rgba(0,0,0,0.1);
    }

    .card-header {
      background-color: #24412f;
      color: white;
      padding: 10px;
      border-radius: 10px 10px 0 0;
      text-align: center;
      font-weight: bold;
    }

    .card-body {
      padding: 30px;
      font-size: 20px;
      text-align: center;
      color: #24412f;
    }

    /* --- NEW: Modal --- */
    .modal-bg {
      display: none;
      position: fixed;
      top:0; left:0;
      width:100%; height:100%;
      background: rgba(0,0,0,0.4);
      justify-content: center;
      align-items: center;
      z-index: 10;
    }

    .modal-box {
      background: white;
      padding: 20px;
      width: 350px;
      border-radius: 10px;
      box-shadow: 0 3px 10px rgba(0,0,0,0.2);
    }

    .modal-box input {
      width: 100%;
      padding: 8px;
      margin: 6px 0 12px;
      border: 1px solid #ccc;
      border-radius: 5px;
    }

    .modal-btn {
      background-color: #24412f;
      color:white;
      padding: 8px 16px;
      border:none;
      border-radius:6px;
      cursor:pointer;
    }

    /* --- NEW: Table Style --- */
    table {
      width:100%;
      border-collapse: collapse;
      margin-top: 20px;
    }

    table th, table td {
      border: 1px solid #ccc;
      padding: 10px;
      text-align:left;
    }

    table th {
      background-color:#24412f;
      color:white;
    }

  </style>
</head>
<body>

  <!-- Sidebar -->
  <div class="sidebar">
    <h2>Welcome to Library System!<br>User</h2>
    <button class="modal-btn" onclick="openModal()"> Borrow </button>
    <button> Reservation </button>
    <button>Penalties</a>
   
  </div>

  <!-- Top Bar -->
  <div class="topbar">
    <form action="logout.php" method="post">
      <button type="submit" class="logout-btn">Logout</button>
    </form>
  </div>

  <div class="main-content">

    <div class="card-container">
      <div class="card"><div class="card-header">Total Books Borrowed</div><div class="card-body">3</div></div>
      <div class="card"><div class="card-header">Total Book Reservation</div><div class="card-body">2</div></div>
      <div class="card"><div class="card-header">Total Penalties</div><div class="card-body">₱50</div></div>
    </div>

    <!-- NEW: Modal -->
    <div class="modal-bg" id="modal">
      <div class="modal-box">
        <h3>Add Borrow Info</h3>

        <form action="../controller/libraryprocess.php" method="post">
             <label>Book Title:</label>
          <input type="text" name="booktitle" id="bookTitle">

          <label>Borrowed Date:</label>
          <input type="date" name="borrowdate" id="borrowDate">

          <label> Return Date:</label>
          <input type="date" name="returndate" id="returndate">

          <button class="modal-btn" onclick="addBorrow()" name="borrow">Submit</button>
          <button class="modal-btn" style="background:#888" onclick="closeModal()">Cancel</button>
        </form>
       
      </div>
    </div>

    <div class="modal-bg" id="modal">
      <div class="modal-box">
        <h3>Add Reserved Info</h3>

        <form action="../controller/libraryprocess.php" method="post">
             <label>Book Title:</label>
          <input type="text" name="reservetitle" id="bookTitle">

          <label>Reservation Date:</label>
          <input type="date" name="reservedate" id="borrowDate">

          <label> Pick-up Date:</label>
          <input type="date" name="pickupdate" id="returndate">

          <button class="modal-btn" onclick="addreseve()" name="borrow">Submit</button>
          <button class="modal-btn" style="background:#888" onclick="closeModal()">Cancel</button>
        </form>
       
      </div>
    </div>

    <!-- NEW: Display Table -->
    <h2 style="margin-top:30px;">Borrowed Books</h2>
    <table id="borrowTable">
      <tr>
        <th>Book Title</th>
        <th>Date Borrowed</th>
        <th>Return Date </th>
      </tr>
    </table>


    
  </div>

  <!-- NEW: JavaScript -->
  <script>
    function openModal() {
      document.getElementById("modal").style.display = "flex";
    }

    function closeModal() {
      document.getElementById("modal").style.display = "none";
    }

    function addreserve(){
        let reservetitle = document.getElementById("reservetitle").value;
        let reservedate = document.getElementById("reservedate").value;
        let pickupdate = document.getElementById("pickupdate").value;

      if(reservetitle === "" || reservedate ==="" || pickupdate===""){
          alert("Fill all fields");
          return;
      }
    }


    function addBorrow() {
      let title = document.getElementById("bookTitle").value;
      let date = document.getElementById("borrowDate").value;
      let returndate = document.getElementById("").value;

      if (title === "" || date === "" || returndate ==="") {
        alert("Fill up all fields!");
        return;
      }

      let table = document.getElementById("borrowTable");
      let row = table.insertRow(-1);

      row.insertCell(0).innerHTML = title;
      row.insertCell(1).innerHTML = date;

      closeModal();
      document.getElementById("bookTitle").value = "";
      document.getElementById("borrowDate").value = "";
      document.getElementById("returndate").value = "";
    }
  </script>

</body>
</html>
