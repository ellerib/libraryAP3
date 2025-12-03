<?php
    session_start();
    include __DIR__ . "/../config/databasec.php"; // Database connection

    $database = new Database();
    $conn = $database->getconnection();

    // Fetch all books
    $books = $conn->query("SELECT * FROM books ORDER BY book_id DESC");

    // Fetch card counts
    $totalBooks = $conn->query("SELECT COUNT(*) as count FROM books")->fetch_assoc()['count'];
    
    $archivedBooks = $conn->query("SELECT COUNT(*) as count FROM book_archive")->fetch_assoc()['count'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Librarian Dashboard</title>
<style>
* {margin:0; padding:0; box-sizing:border-box; font-family: "Roboto", sans-serif;}
body {background:#f2f5f7; display:flex; min-height:100vh;}

/* Sidebar */
.sidebar {
  width: 240px; background:#24412f; height:100vh; padding:20px 0; color:white;
  display:flex; flex-direction:column; align-items:center; box-shadow:4px 7px 15px rgba(0,0,0,0.3);
}
.sidebar h2 {margin-bottom:40px; font-weight:300; text-align:center;}
.nav-list {list-style:none; width:100%; padding:0;}
.nav-list li, .nav-list button {
  width:100%; padding:12px 20px; display:flex; align-items:center; gap:10px;
  cursor:pointer; border:none; background:none; color:white; text-decoration:none; font-size:1.1rem;
  transition:.3s; text-align:left;
}
.nav-list li:hover, .nav-list button:hover {background:#2f5c43;}
.nav-list button {border-radius:6px; background:#2f5c43; margin-bottom:10px; justify-content:flex-start;}

/* Main Content */
.main-content {flex:1; padding:30px;}
.header-title {font-size:2.3rem; font-weight:300; color:#24412f; margin-bottom:20px;}

/* Cards */
.cards {display:grid; grid-template-columns:repeat(3,1fr); gap:20px; margin-bottom:30px;}
.card {background:white; padding:25px; border-radius:12px; box-shadow:0 4px 10px rgba(0,0,0,0.1); transition:.3s; cursor:pointer;}
.card:hover {transform:translateY(-5px);}
.card h3 {font-size:1.4rem; color:#24412f; font-weight:400;}
.card p {font-size:2.1rem; color:#2f5c43; font-weight:bold; margin-top:10px;}

/* Table */
table {width:100%; border-collapse:collapse; background:white; border-radius:10px; overflow:hidden; box-shadow:0 4px 10px rgba(0,0,0,0.1);}
th, td {padding:15px; text-align:left; border-bottom:1px solid #eee;}
th {background:#24412f; color:white; font-weight:400;}
tr:hover {background:#f4f8f5;}
.action-btn {padding:6px 12px; border:none; border-radius:6px; cursor:pointer; color:white; font-size:.9rem;}
.edit-btn {background:#4a7c59;}
.delete-btn {background:#b53f3f;}

/* Modal */
.modal-bg {display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.4); justify-content:center; align-items:center; z-index:10;}
.modal-box {background:white; padding:25px; border-radius:12px; width:350px; box-shadow:0 4px 10px rgba(0,0,0,0.2);}
.modal-box h3 {margin-bottom:15px; color:#24412f;}
.modal-box input {width:100%; padding:10px; margin-bottom:15px; border:1px solid #ccc; border-radius:6px;}
.modal-btn {padding:8px 16px; border:none; border-radius:6px; cursor:pointer; background:#24412f; color:white; margin-right:5px;}
</style>
</head>
<body>

<!-- Sidebar -->
<div class="sidebar">
  <h2>Librarian</h2>
  <ul class="nav-list">
    <button onclick="openAddBookModal()">📚 Add Book</button>
    <li><a href="#">📦 Archived Books</a></li>
    <li><a href="#">🚪 Logout</a></li>
  </ul>
</div>

<!-- Main Content -->
<div class="main-content">
  <h1 class="header-title">Library Dashboard</h1>

  <!-- Cards -->
  <div class="cards">
    <div class="card" onclick="filterTable('all')">
      <h3>Total Books</h3>
      <p><?php echo $totalBooks; ?></p>
    </div>
    
    <div class="card" onclick="filterTable('Archived')">
      <h3>Archived</h3>
      <p><?php echo $archivedBooks; ?></p>
    </div>
  </div>

  <!-- Book Table -->
  <table id="bookTable">
    <tr>
      <th>Book Title</th>
      <th>Author</th>
      <th>ISBN</th>
      <th>Quantity</th>
      <th>Action</th>
    </tr>
    <?php while($row = $books->fetch_assoc()): ?>

      <td> <?php echo $row['title']; ?> </td>
      <td> <?php echo $row['author']; ?> </td>
      <td> <?php echo $row['isbn']; ?> </td>
      <td> <?php echo $row['quantity'];?> </td>
     <td>
    <button class="action-btn edit-btn">Edit</button>
    <button class="action-btn delete-btn" 
            onclick="openArchiveModal(<?php echo $row['book_id']; ?>, '<?php echo addslashes($row['title']); ?>')">
        Archive
    </button>
</td>

    </tr>
    <?php endwhile; ?>
  </table>
</div>

<!-- Add Book Modal -->
<div class="modal-bg" id="addBookModal">
  <div class="modal-box">
    <h3>Add New Book</h3>
    <form method="post" action="../controller/bookprocess.php">
  <input type="text" name="title" placeholder="Title" required>
  <input type="text" name="author" placeholder="Author" required>
  <input type="text" name="isbn" placeholder="ISBN" required>
  <input type="text" name="quantity" placeholder="Quantity" required>
  <button type="submit" name="addbook" class="modal-btn">Add Book</button>
  <button type="button" class="modal-btn" style="background:#888" onclick="closeAddBookModal()">Cancel</button>
</form>

  </div>
</div>

<!-- Archive Book Modal -->
<div class="modal-bg" id="archiveModal">
  <div class="modal-box">
    <h3>Archive Book</h3>
    <p id="archiveBookTitle">Are you sure you want to archive this book?</p>
    <form method="post" action="../controller/bookprocess.php">
        <input type="hidden" name="book_id" id="archiveBookId">
        <button type="submit" name="archive_book" class="modal-btn">Yes, Archive</button>
        <button type="button" class="modal-btn" style="background:#888" onclick="closeArchiveModal()">Cancel</button>
    </form>
  </div>
</div>


<script>
function openAddBookModal(){ document.getElementById('addBookModal').style.display='flex'; }
function closeAddBookModal(){ document.getElementById('addBookModal').style.display='none'; }

function openArchiveModal(bookId, bookTitle){
    document.getElementById('archiveBookId').value = bookId;
    document.getElementById('archiveBookTitle').innerText = "Are you sure you want to archive: " + bookTitle + "?";
    document.getElementById('archiveModal').style.display = 'flex';
}

function closeArchiveModal(){
    document.getElementById('archiveModal').style.display = 'none';
}

// Filter table rows by status
function filterTable(status) {
  let table = document.getElementById('bookTable');
  let rows = table.getElementsByTagName('tr');
  for(let i=1; i<rows.length; i++){
    if(status === 'all' || rows[i].dataset.status === status || (status==='Archived' && rows[i].dataset.status==='Archived')){
      rows[i].style.display = '';
    } else {
      rows[i].style.display = 'none';
    }
  }
}
</script>
</body>
</html>
