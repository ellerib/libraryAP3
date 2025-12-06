<?php
session_start();
include __DIR__ . "/../config/databasec.php";
$database = new Database();
$conn = $database->getconnection();

// Add Book
if(isset($_POST['addbook'])){
    $stmt = $conn->prepare("INSERT INTO books (title, author, isbn, quantity, price) VALUES (?,?,?,?,?)");
    $stmt->bind_param("ssiid", $_POST['title'], $_POST['author'], $_POST['isbn'], $_POST['quantity'], $_POST['price']);
    $stmt->execute();
    header("Location: ../view/librarianpage.php");
    exit();
}

// Update Book
if(isset($_POST['updatebook'])){
    $stmt = $conn->prepare("UPDATE books SET title=?, author=?, isbn=?, quantity=?, price=? WHERE book_id=?");
    $stmt->bind_param("ssiidi", $_POST['title'], $_POST['author'], $_POST['isbn'], $_POST['quantity'], $_POST['price'], $_POST['book_id']);
    $stmt->execute();
    header("Location: ../view/librarianpage.php");
    exit();
}

// Archive Book
if(isset($_POST['archive_book'])){
    $stmt = $conn->prepare("UPDATE books SET status='archived' WHERE book_id=?");
    $stmt->bind_param("i", $_POST['book_id']);
    $stmt->execute();
    header("Location: ../view/librarianpage.php");
    exit();
}

if(isset($_POST['restore_book'])){
    $book_id = $_POST['restore_book_id'];

    // Get book info from archive
    $book = $conn->query("SELECT * FROM book_archive WHERE book_id=$book_id")->fetch_assoc();

    if($book){
        // Insert back into main books table
        $stmt = $conn->prepare("INSERT INTO books (title, author, isbn, quantity, price) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("sssdi", $book['title'], $book['author'], $book['isbn'], $book['quantity'], $book['price']);
        $stmt->execute();

        // Remove from archive
        $conn->query("DELETE FROM book_archive WHERE book_id=$book_id");
    }

    header("Location: ../view/archivedbook.php");
    exit();
}

// ------------------- DELETE BOOK -------------------
if(isset($_POST['delete_book'])){
    $book_id = $_POST['delete_book_id'];

    // Permanently delete from archive
    $stmt = $conn->prepare("DELETE FROM book_archive WHERE book_id=?");
    $stmt->bind_param("i", $book_id);
    $stmt->execute();

    header("Location: ../view/archivedbook.php");
    exit();
}
?>
