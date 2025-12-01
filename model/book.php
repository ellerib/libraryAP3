<?php
    class Book extends User{
        public $book_id;
        public $isbn;
        public $book_title;
        
        public $author;

        public function __construct($book_id, $isbn, $book_title, $author) {
            $this->book_id = $book_id;
            $this->isbn = $isbn;
            $this->book_title = $book_title;
            $this->author = $author;
        }

        public function add_book($conn){
            $sql = "INSERT INTO books(book_id, author, title, isbn) VALUES(?,?,?,?)";
            $stmt = $conn->prepare($sql);

            $stmt->bind_param("issi",$this->book_id, $this->author, $this->book_title, $this->isbn);

            if($stmt->execute()){
                return "<script>alert('Book inserted');</script>";
            }else{
                return "<script>alert('Book unsuccessfully inserted')</script>";
            }

        }

        public function archive_book($conn){
            if(isset($_GET['book_id'])){
                $this->book_id = $_GET['book_id'];

                $conn->begin_transaction();

                try{
                    $copy_data = $conn->prepare("
                        INSERT INTO book_archive(book_id, isbn, title, author)
                        SELECT book_id, title, author, isbn
                        FROM books
                        WHERE book_id = ?
                    ");

                    $copy_data->bind_param("i", $this->book_id);
                    $copy_data->execute();

                    // CHECK IF COPY SUCCEEDED
                    if($copy_data->affected_rows===0){
                        throw new Exception("No record found to archive");
                    }

                    // DELETE THE BOOK FROM THE TABLE
                    $deletebook = $conn->prepare("DELETE FROM books WHERE book_id = ?");
                    $deletebook->bind_param("i", $this->book_id);

                    // SAVE CHANGES
                    $conn->commit();

                    return "<script> alert('Book archived')</script>";


                }catch(Exception $error){
                    $conn->rollback();

                    return "<script>alert('Book error archiving') </script>".$error->getMessage();

                }

            }
        }

        public function update_book(){

        }

    }


?>