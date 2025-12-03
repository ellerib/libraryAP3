<?php
include_once __DIR__ . '/user.php';

class Book extends User {
    public $book_id;
    public $isbn;
    public $book_title;
    public $author;
    public $quantity;

    public function __construct($book_id, $isbn, $book_title, $author, $quantity){
        $this->book_id = $book_id;
        $this->isbn = $isbn;
        $this->book_title = $book_title;
        $this->author = $author;
        $this->quantity = $quantity;
    }

    public function add_book($conn){
        $sql = "INSERT INTO books(book_id, author, title, isbn, quantity) VALUES (NULL, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sssi", $this->author, $this->book_title, $this->isbn, $this->quantity);
        if($stmt->execute()){
            return "<script>alert('Book inserted');</script>";
        } else {
            return "<script>alert('Book unsuccessfully inserted');</script>";
        }
    }

    public function archive_book($conn){
    if(!$this->book_id){
        return false; // no book ID provided
    }

    $conn->begin_transaction();
    try {
        // Copy to archive table
        $copy = $conn->prepare("
            INSERT INTO book_archive(book_id, title, author, isbn, quantity)
            SELECT book_id, title, author, isbn, quantity
            FROM books
            WHERE book_id = ?
        ");
        $copy->bind_param("i", $this->book_id);
        $copy->execute();

        if($copy->affected_rows === 0){
            throw new Exception("Book not found to archive");
        }

        // Delete from main books table
        $delete = $conn->prepare("DELETE FROM books WHERE book_id = ?");
        $delete->bind_param("i", $this->book_id);
        $delete->execute();

        $conn->commit();
        return true;

    } catch(Exception $e){
        $conn->rollback();
        return false;
    }
}



}
?>
