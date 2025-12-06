<?php
session_start();
include __DIR__ . "/../config/databasec.php";
$database = new Database();
$conn = $database->getconnection();

// Fetch active books only
$books = $conn->query("SELECT * FROM books WHERE status='active' ORDER BY book_id DESC");

// Card counts
$totalBooks = $conn->query("SELECT COUNT(*) as count FROM books WHERE status='active'")->fetch_assoc()['count'];

?>


<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
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
.nav-list li, .nav-list a, .nav-list button {
  width:100%; padding:12px 20px; display:flex; align-items:center; gap:10px;
  cursor:pointer; border:none; background:none; color:white; text-decoration:none; font-size:1.1rem;
  transition:.3s; text-align:left;
}
.nav-list li:hover, .nav-list a:hover, .nav-list button:hover {background:#2f5c43;}
.nav-list button {border-radius:6px; background:#2f5c43; margin-bottom:10px; justify-content:flex-start;}

/* Main Content */
.main-content {flex:1; padding:30px;}
.header-title {font-size:2.3rem; font-weight:300; color:#24412f; margin-bottom:20px;}

/* Cards */
.cards {display:grid; grid-template-columns:repeat(2,1fr); gap:20px; margin-bottom:30px;}
.card {background:white; padding:25px; border-radius:12px; box-shadow:0 4px 10px rgba(0,0,0,0.1);}
.card h3 {font-size:1.4rem; color:#24412f;}
.card p {font-size:2.1rem; color:#2f5c43; font-weight:bold; margin-top:10px;}

/* Table */
table {width:100%; border-collapse:collapse; background:white; border-radius:10px; overflow:hidden; box-shadow:0 4px 10px rgba(0,0,0,0.1);}
th, td {padding:15px; text-align:left; border-bottom:1px solid #eee;}
th {background:#24412f; color:white;}
tr:hover {background:#f4f8f5;}
.action-btn {padding:6px 12px; border:none; border-radius:6px; cursor:pointer; color:white; font-size:.9rem;}
.edit-btn {background:#4a7c59;}
.archive-btn {background:#b53f3f;}

/* Modal */
.modal-bg {display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.4); justify-content:center; align-items:center; z-index:10;}
.modal-box {background:white; padding:25px; border-radius:12px; width:350px; box-shadow:0 4px 10px rgba(0,0,0,0.2);}
.modal-box h3 {margin-bottom:15px; color:#24412f;}
.modal-box input {width:100%; padding:10px; margin-bottom:15px; border:1px solid #ccc; border-radius:6px;}
.modal-btn {padding:8px 16px; border:none; border-radius:6px; cursor:pointer; background:#24412f; color:white; margin-right:5px;}
.modal-btn.cancel {background:#888;}
</style>

</head>
<body>
  

<div class="sidebar">
  <h2>Librarian</h2>
  <ul class="nav-list">
    <button onclick="openAddBookModal()">📚 Add Book</button>
    <li><a href="archivedbook.php">📦 Archived Books</a></li>
    <li><a href="logout.php">🚪 Logout</a></li>
  </ul>
</div>

<div class="main-content">
  <h1 class="header-title">Library Dashboard</h1>

  <div class="cards">
    <div class="card"><h3>Total Books</h3><p><?php echo $totalBooks; ?></p></div>
    
  </div>

  <table>
    <tr>
      <th>Title</th>
      <th>Author</th>
      <th>ISBN</th>
      <th>Quantity</th>
      <th>Action</th>
    </tr>
    <?php while($row = $books->fetch_assoc()): ?>
    <tr>
      <td><?php echo $row['title']; ?></td>
      <td><?php echo $row['author']; ?></td>
      <td><?php echo $row['isbn']; ?></td>
      <td><?php echo $row['quantity']; ?></td>
      <td>
        <button class="action-btn edit-btn" onclick="openEditModal(<?php echo $row['book_id']; ?>,'<?php echo addslashes($row['title']); ?>','<?php echo addslashes($row['author']); ?>','<?php echo $row['isbn']; ?>',<?php echo $row['quantity']; ?>)">Edit</button>
        <button class="action-btn archive-btn" onclick="openArchiveModal(<?php echo $row['book_id']; ?>,'<?php echo addslashes($row['title']); ?>')">Archive</button>
      </td>
    </tr>
    <?php endwhile; ?>
  </table>
</div>

<!-- Modals -->
<div class="modal-bg" id="addBookModal">
  <div class="modal-box">
    <h3>Add Book</h3>
    <form method="post" action="../controller/bookprocess.php">
      <input type="text" name="title" placeholder="Title" required>
      <input type="text" name="author" placeholder="Author" required>
      <input type="text" name="isbn" placeholder="ISBN" required>
      <input type="number" name="quantity" placeholder="Quantity" required>
      <input type="number" name="price" placeholder="Price" required>
      <button type="submit" name="addbook" class="modal-btn">Add Book</button>
      <button type="button" class="modal-btn cancel" onclick="closeAddBookModal()">Cancel</button>
    </form>
  </div>
</div>

<div class="modal-bg" id="editBookModal">
  <div class="modal-box">
    <h3>Edit Book</h3>
    <form method="post" action="../controller/bookprocess.php">
      <input type="hidden" name="book_id" id="editBookId">
      <input type="text" name="title" id="editTitle" required>
      <input type="text" name="author" id="editAuthor" required>
      <input type="text" name="isbn" id="editISBN" required>
      <input type="number" name="quantity" id="editQuantity" required>
      <input type="number" name="price" id="editPrice" required>
      <button type="submit" name="updatebook" class="modal-btn">Update Book</button>
      <button type="button" class="modal-btn cancel" onclick="closeEditModal()">Cancel</button>
    </form>
  </div>
</div>

<div class="modal-bg" id="archiveModal">
  <div class="modal-box">
    <h3>Archive Book</h3>
    <p id="archiveBookTitle"></p>
    <form method="post" action="../controller/bookprocess.php">
      <input type="hidden" name="book_id" id="archiveBookId">
      <button type="submit" name="archive_book" class="modal-btn">Yes, Archive</button>
      <button type="button" class="modal-btn cancel" onclick="closeArchiveModal()">Cancel</button>
    </form>
  </div>
</div>

<script>
function openAddBookModal(){ document.getElementById('addBookModal').style.display='flex'; }
function closeAddBookModal(){ document.getElementById('addBookModal').style.display='none'; }

function openEditModal(id,title,author,isbn,quantity){
    document.getElementById('editBookId').value = id;
    document.getElementById('editTitle').value = title;
    document.getElementById('editAuthor').value = author;
    document.getElementById('editISBN').value = isbn;
    document.getElementById('editQuantity').value = quantity;
    document.getElementById('editBookModal').style.display='flex';
}
function closeEditModal(){ document.getElementById('editBookModal').style.display='none'; }

function openArchiveModal(id,title){
    document.getElementById('archiveBookId').value = id;
    document.getElementById('archiveBookTitle').innerText = "Archive book: "+title+"?";
    document.getElementById('archiveModal').style.display='flex';
}
function closeArchiveModal(){ document.getElementById('archiveModal').style.display='none'; }
</script>

</body>
</html>
