<?php
class User {
    private $conn;
    private $lastname, $firstname, $role, $email, $password;

    public function __construct($lastname, $firstname, $email, $password, $role){
        $database = new Database();
        $this->conn = $database->getconnection();
        $this->lastname = $lastname;
        $this->firstname = $firstname;
        $this->email = $email;
        $this->password = $password;
        $this->role = $role;
    }

    public function login() {
        $sql = "SELECT user_id, email, password, role FROM users WHERE email=?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("s", $this->email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();

            if (password_verify($this->password, $row['password'])) {

                $_SESSION['user_id'] = $row['user_id'];
                $_SESSION['role'] = $row['role'];

                switch ($row['role']) {
                    case 'student':   header("Location: ../view/studentpage.php"); break;
                    case 'teacher':   header("Location: ../view/teacherpage.php"); break;
                    case 'librarian': header("Location: ../view/librarianpage.php"); break;
                    case 'staff':     header("Location: ../view/staffpage.php"); break;
                }
                exit();
            } else {
                echo "<script>alert('Invalid password');</script>";
            }
        } else {
            echo "<script>alert('User not found');</script>";
        }
    }

    public function register(){
        // ❗ Removed include "register.php";

        $hashpassword = password_hash($this->password, PASSWORD_DEFAULT);

        $sql = "INSERT INTO users(lastname, firstname, email, password, role) 
                VALUES(?, ?, ?, ?, ?)";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("sssss", 
            $this->lastname, 
            $this->firstname, 
            $this->email, 
            $hashpassword, 
            $this->role
        );

        if($stmt->execute()){
            echo "<script>alert('User registered successfully'); window.location='../view/login.php';</script>";
        } else {
            echo "<script>alert('User unsuccessfully registered');</script>";
        }
    }

    public function verify_email(){
        if(!filter_var($this->email, FILTER_VALIDATE_EMAIL)){
            echo "<script>alert('Enter a valid email');</script>";
            exit();
        }
    }
}
?>
