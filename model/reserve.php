<?php
include_once __DIR__ . "/user.php";
include_once __DIR__ . "/../config/databasec.php";

class Reservation extends User {

    private $reservation_date, $pickup_date;
    private $book_id, $user_id, $semester;

    public function __construct($reservation_date, $pickup_date) {
        $this->reservation_date = $reservation_date;
        $this->pickup_date = $pickup_date;
    }

    // Includes semester
    public function reservesetuserandbookinfo($book_id, $user_id, $semester){
        $this->book_id = $book_id;
        $this->user_id = $user_id;
        $this->semester = $semester;
    }

    public function reserve_book($conn){  

        $sql = "INSERT INTO reservation(reservation_date, pickup_date, user_id, book_id, semester, status)
                VALUES(?,?,?,?,?, 'Pending')";

        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssiis",
            $this->reservation_date,
            $this->pickup_date,
            $this->user_id,
            $this->book_id,
            $this->semester
        );

        if($stmt->execute()){
            return "Book reserved successfully!";
        } 
        else {
            return "Error: Could not reserve book.";
        }
    }

}
?>
