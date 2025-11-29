<?php
    include_once __DIR__ . "/user.php"; // include User class
    include_once __DIR__ . "/../config/databasec.php"; // include DB class
    class Borrow extends User{

        public $borrow_title, $borrow_date, $return_date;
        private $user_id, $book_id;


        public function __construct($borrow_title, $borrow_date, $return_date){
            $this->borrow_title = $borrow_title;
            $this->borrow_date = $borrow_date;
            $this->return_date = $return_date;

        }

        public function setuserandbookinfo($book_id, $user_id){
            $this->book_id = $book_id;
            $this->user_id = $user_id;
        }

        public function borrow_book($conn){

        // 1. CHECK USER ROLE FIRST
        $sql_role = "SELECT role FROM users WHERE user_id = ?";
        $stmt_role = $conn->prepare($sql_role);
        $stmt_role->bind_param("i", $this->user_id);
        $stmt_role->execute();
        $result_role = $stmt_role->get_result()->fetch_assoc();

        $role = $result_role['role'];

        // If STUDENT → enforce 3-book limit
        if($role === "student"){
            
            $check = "SELECT COUNT(*) AS total FROM borrow 
                    WHERE user_id = ? AND status = 'borrowed'";
            $stmt_check = $conn->prepare($check);
            $stmt_check->bind_param("i", $this->user_id);
            $stmt_check->execute();
            $result = $stmt_check->get_result()->fetch_assoc();

            if($result['total'] >= 3){
                return "<script>alert('Borrow limit reached! Students can only borrow 3 books.');</script>";
            }
        }

            // 3. INSERT BORROW IF LIMIT NOT EXCEEDED
            $sql = "INSERT INTO borrow(borrow_date, return_date, book_id, user_id, status) 
                    VALUES(?, ?, ?, ?, 'borrowed')";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ssii", $this->borrow_date, $this->return_date, $this->book_id, $this->user_id);

            if($stmt->execute()){
                return "<script>alert('Book successfully borrowed');</script>";
            } else {
                return "<script>alert('Borrow unsuccessful');</script>";
            }
        }




    }




?>