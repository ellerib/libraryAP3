<?php
    include_once __DIR__ . "/user.php";
    include_once __DIR__ . "/../config/databasec.php";

class Borrow extends User {

    public $borrow_date, $return_date;
    private $user_id, $book_id, $semester;

    public function __construct($borrow_date, $return_date){
        $this->borrow_date = $borrow_date;
        $this->return_date = $return_date;
    }

    public function setuserandbookinfo($book_id, $user_id, $semester){
        $this->book_id = $book_id;
        $this->user_id = $user_id;
        $this->semester = $semester;
    }

    public function borrow_book($conn){

    // 1. GET USER ROLE
    $sql_role = "SELECT role FROM users WHERE user_id = ?";
    $stmt_role = $conn->prepare($sql_role);
    $stmt_role->bind_param("i", $this->user_id);
    $stmt_role->execute();
    $role = $stmt_role->get_result()->fetch_assoc()['role'];

    // 2. STUDENT RULE → limit to 3 books
    if($role === "student"){
        $check = "SELECT COUNT(*) AS total FROM borrow WHERE user_id = ?";
        $stmt_check = $conn->prepare($check);
        $stmt_check->bind_param("i", $this->user_id);
        $stmt_check->execute();
        $borrowCount = $stmt_check->get_result()->fetch_assoc()['total'];

        if($borrowCount >= 3){
            return "Borrow limit reached! Students can only borrow 3 books.";
        }
    }

    // 3. INSERT RECORD
    $sql = "INSERT INTO borrow(borrow_date, return_date, book_id, user_id, status, semester)
        VALUES(?, ?, ?, ?, 'Borrowed', ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssiii",
        $this->borrow_date,
        $this->return_date,
        $this->book_id,
        $this->user_id,
        $this->semester
    );


    if($stmt->execute()){
        // Update book quantity globally (affects all users)
        $updateQty = $conn->prepare("UPDATE books SET quantity = quantity - 1 WHERE book_id = ?");
        $updateQty->bind_param("i", $this->book_id);
        $updateQty->execute();

        return "Book borrowed successfully!";
    } else {
        return "Error: Could not borrow book.";
    }
}

}
?>
