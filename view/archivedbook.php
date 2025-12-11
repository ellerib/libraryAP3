<?php
session_start();
include __DIR__ . "/../config/databasec.php"; 

$database = new Database();
$conn = $database->getconnection();

// Fetch all archived books
$books = $conn->query("SELECT * FROM book_archive ORDER BY archive_id DESC");


// Card counts
// $totalBooks = $conn->query("SELECT COUNT(*) as count FROM books")->fetch_assoc()['count'];
$archivedBooks = $conn->query("SELECT COUNT(*) as count FROM book_archive")->fetch_assoc()['count'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Archived Books</title>
<style>
/* --- Styles same as before --- */
* {margin:0; padding:0; box-sizing:border-box; font-family: "Roboto", sans-serif;}
body {background:#f2f5f7; display:flex; min-height:100vh;}
.sidebar {width: 240px; background:#24412f; height:100vh; padding:20px 0; color:white; display:flex; flex-direction:column; align-items:center; box-shadow:4px 7px 15px rgba(0,0,0,0.3);}
.sidebar h2 {margin-bottom:40px; font-weight:300; text-align:center;}
.nav-list {list-style:none; width:100%; padding:0;}
.nav-list li, .nav-list a, .nav-list button {width:100%; padding:12px 20px; display:flex; align-items:center; gap:10px; cursor:pointer; border:none; background:none; color:white; text-decoration:none; font-size:1.1rem; transition:.3s; text-align:left;}
.nav-list li:hover, .nav-list a:hover, .nav-list button:hover {background:#2f5c43;}
.nav-list button {border-radius:6px; background:#2f5c43; margin-bottom:10px; justify-content:flex-start;}
.main-content {flex:1; padding:30px;}
.header-title {font-size:2.3rem; font-weight:300; color:#24412f; margin-bottom:20px;}
.cards {display:grid; grid-template-columns:repeat(2,1fr); gap:20px; margin-bottom:30px;}
.card {background:white; padding:25px; border-radius:12px; box-shadow:0 4px 10px rgba(0,0,0,0.1);}
.card h3 {font-size:1.4rem; color:#24412f;}
.card p {font-size:2.1rem; color:#2f5c43; font-weight:bold; margin-top:10px;}
table {width:100%; border-collapse:collapse; background:white; border-radius:10px; overflow:hidden; box-shadow:0 4px 10px rgba(0,0,0,0.1);}
th, td {padding:15px; text-align:left; border-bottom:1px solid #eee;}
th {background:#24412f; color:white;}
tr:hover {background:#f4f8f5;}
.action-btn {padding:6px 12px; border:none; border-radius:6px; cursor:pointer; color:white; font-size:.9rem;}
.restore-btn {background:#4a7c59;}
.delete-btn {background:#b53f3f;}
.modal-bg {display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.4); justify-content:center; align-items:center; z-index:10;}
.modal-box {background:white; padding:25px; border-radius:12px; width:350px; box-shadow:0 4px 10px rgba(0,0,0,0.2);}
.modal-box h3 {margin-bottom:15px; color:#24412f;}
.modal-btn {padding:8px 16px; border:none; border-radius:6px; cursor:pointer; background:#24412f; color:white; margin-right:5px;}
</style>
</head>
<body>

<!-- Sidebar -->
<div class="sidebar">
  <h2>Librarian</h2>
  <ul class="nav-list">
    <li><a href="../view/librarianpage.php">📚 Dashboard</a></li>
    <li><a href="#">📦 Archived Books</a></li>
    <li><a href="../controller/logout.php">🚪 Logout</a></li>
  </ul>
</div>

<div class="main-content">
  <h1 class="header-title">Archived Books</h1>

  <!-- Cards -->
  <div class="cards">
   
    <div class="card">
      <h3>Archived</h3>
      <p><?php echo $archivedBooks; ?></p>
    </div>
  </div>

  <!-- Table -->
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
        <button class="action-btn restore-btn"
                onclick="openRestoreModal(<?php echo $row['book_id']; ?>,'<?php echo addslashes($row['title']); ?>')">Restore</button>
        <button class="action-btn delete-btn"
                onclick="openDeleteModal(<?php echo $row['book_id']; ?>,'<?php echo addslashes($row['title']); ?>')">Delete</button>
      </td>
    </tr>
    <?php endwhile; ?>
  </table>
</div>

<!-- Restore Modal -->
<div class="modal-bg" id="restoreModal">
  <div class="modal-box">
    <h3>Restore Book</h3>
    <p id="restoreBookTitle">Are you sure you want to restore this book?</p>
    <form method="post" action="../controller/bookprocess.php">
      <input type="hidden" name="restore_book_id" id="restoreBookId">
      <button type="submit" name="restore_book" class="modal-btn">Yes, Restore</button>
      <button type="button" class="modal-btn" style="background:#888" onclick="closeRestoreModal()">Cancel</button>
    </form>
  </div>
</div>

<!-- Delete Modal -->
<div class="modal-bg" id="deleteModal">
  <div class="modal-box">
    <h3>Delete Book</h3>
    <p id="deleteBookTitle">Are you sure you want to permanently delete this book?</p>
    <form method="post" action="../controller/bookprocess.php">
      <input type="hidden" name="delete_book_id" id="deleteBookId">
      <button type="submit" name="delete_book" class="modal-btn">Yes, Delete</button>
      <button type="button" class="modal-btn" style="background:#888" onclick="closeDeleteModal()">Cancel</button>
    </form>
  </div>
</div>

<script>
function openRestoreModal(bookId, bookTitle){
    document.getElementById('restoreBookId').value = bookId;
    document.getElementById('restoreBookTitle').innerText = "Restore book: " + bookTitle + "?";
    document.getElementById('restoreModal').style.display = 'flex';
}
function closeRestoreModal(){ document.getElementById('restoreModal').style.display = 'none'; }

function openDeleteModal(bookId, bookTitle){
    document.getElementById('deleteBookId').value = bookId;
    document.getElementById('deleteBookTitle').innerText = "Delete book: " + bookTitle + "?";
    document.getElementById('deleteModal').style.display = 'flex';
}
function closeDeleteModal(){ document.getElementById('deleteModal').style.display = 'none'; }
</script>

</body>
</html>
