<?php

    class Reserve extends User{
        private $reservdation_date,  $book_title, $book_id;


        public function __construct($reservation_date,  $book_title, $book_id){
            $this->reservdation_date = $reservation_date;
            $this->book_id = $book_id;
        }
        

        public function reserve_book(){
            $sql = "INSERT INTO reserve()";
        }




    }


?>