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
    $stmt = $conn->prepare("UPDATE books SET isbn=?, price=? WHERE book_id=?");
    $stmt->bind_param("sdi", $_POST['isbn'], $_POST['price'], $_POST['book_id']);
    $stmt->execute();
    header("Location: ../view/librarianpage.php");
    exit();
}

// Archive Book
if (isset($_POST['archive_book'])) {
    $book_id = $_POST['book_id'];

    // Get book info first
    $stmt = $conn->prepare("SELECT * FROM books WHERE book_id = ?");
    $stmt->bind_param("i", $book_id);
    $stmt->execute();
    $book = $stmt->get_result()->fetch_assoc();

    // Insert into archive
    $stmt2 = $conn->prepare("
        INSERT INTO book_archive (book_id, title, author, isbn, quantity, price)
        VALUES (?, ?, ?, ?, ?, ?)
    ");
    $stmt2->bind_param(
        "isssid",
        $book['book_id'],
        $book['title'],
        $book['author'],
        $book['isbn'],
        $book['quantity'],
        $book['price']
    );
    $stmt2->execute();

    // Delete from active table
    $stmt3 = $conn->prepare("DELETE FROM books WHERE book_id = ?");
    $stmt3->bind_param("i", $book_id);
    $stmt3->execute();

    header("Location: ../view/librarianpage.php?archived=success");
    exit();
}


if (isset($_POST['restore_book'])) {
    $book_id = $_POST['restore_book_id'];

    // Get archived data
    $stmt = $conn->prepare("SELECT * FROM book_archive WHERE book_id = ?");
    $stmt->bind_param("i", $book_id);
    $stmt->execute();
    $book = $stmt->get_result()->fetch_assoc();

    // Insert back to books
    $stmt2 = $conn->prepare("
        INSERT INTO books (book_id, title, author, isbn, quantity, price, status)
        VALUES (?, ?, ?, ?, ?, ?, 'active')
    ");
    $stmt2->bind_param(
        "isssid",
        $book['book_id'],
        $book['title'],
        $book['author'],
        $book['isbn'],
        $book['quantity'],
        $book['price']
    );
    $stmt2->execute();

    // Remove from archive
    $stmt3 = $conn->prepare("DELETE FROM book_archive WHERE book_id = ?");
    $stmt3->bind_param("i", $book_id);
    $stmt3->execute();

    header("Location: ../view/archivedbook.php?restore=success");
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
