<?php
session_start();
include __DIR__ . "/../config/databasec.php";
include __DIR__ . "/../models/book.php";

$database = new Database();
$conn = $database->getconnection();

if(isset($_POST['addbook'])){
    $book = new Book(
        null, // auto-increment ID
        $_POST['isbn'],
        $_POST['title'],
        $_POST['author']
    );

    $book->add_book($conn);

    // Redirect back to dashboard after adding
    header("Location: ../views/librarianpage.php"); 
    exit();
}
