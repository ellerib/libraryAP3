<?php
    class Borrow extends User{

        public $borrow_date, $return_date;
        private $user_id, $book_id;


        public function __construct($borrow_date, $return_date){
            $this->borrow_date = $borrow_date;
            $this->return_date = $return_date;

        }

        public function setuserandbookinfo($book_id, $user_id){
            $this->book_id = $book_id;
            $this->user_id = $user_id;
        }

        public function borrow_book($conn){

            $sql = "INSERT INTO borrow(borrow_date, return_date, book_id) VALUES(?, ?, ?)";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ssi", $this->borrow_date, $this->return_date, $this->book_id);
            
            if($stmt->execute()){
                return "<script> alert('Book successfully borrowed')</script>";
            }else{
                return "<script> alert('Book unsuccessfully borrowed')</script>";
            }

        }



    }




?>