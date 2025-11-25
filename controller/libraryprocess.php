<?php

    
    
    if($_sERVER["REQUEST_METHOD"]=='POST'){
        // BORROW PROCESS
        if(isset($_POST['borrow'])){

        }



        // RESERVATION PROCESS
        if(isset($_POST['reservation'])){
            $reservationtitle = trim($_POST['reservetitle']);
            $reservationdate = trim($_POST['reservedate']);
            $pickupdate = trim($_POST['pickupdate']);

            $newreservation = new Borrow($reservationtitle,$reservationdate,$pickupdate);


        }

    }
    
   
?>