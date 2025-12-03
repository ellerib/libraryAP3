<?php
session_start();
include __DIR__ . "/../config/databasec.php";
include __DIR__ . "/../model/book.php";

$database = new Database();
$conn = $database->getconnection();

if($_SERVER["REQUEST_METHOD"] == 'POST' && isset($_POST['addbook'])) {
    $book = new Book(
        null,
        $_POST['isbn'],
        $_POST['title'],
        $_POST['author'],
        $_POST['quantity']
    );

    $book->add_book($conn);

    header("Location: ../view/librarianpage.php");
    exit();
}

// ARCHIVE BOOK
if($_SERVER["REQUEST_METHOD"] == 'POST' && isset($_POST['archive_book']) && isset($_POST['book_id'])){
    $book = new Book($_POST['book_id'], null, null, null, null);
    $book->archive_book($conn);
    header("Location: ../view/librarianpage.php");
    exit();
}

?>
