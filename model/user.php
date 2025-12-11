<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

include_once __DIR__ . "/../config/databasec.php";

class User {
    private $conn;
    private $lastname, $firstname, $role, $email, $password;

    public function __construct($lastname = "", $firstname = "", $email = "", $password = "", $role = "") {
        $database = new Database();
        $this->conn = $database->getconnection();
        $this->lastname = $lastname;
        $this->firstname = $firstname;
        $this->email = $email;
        $this->password = $password;
        $this->role = $role;
    }

    // Static method to redirect if already logged in
    public static function checkLogin() {
        if(isset($_SESSION['user_id'], $_SESSION['user_type'])) {
            $role = $_SESSION['user_type'];
            $redirect = match($role) {
                'student' => '../view/studentpage.php',
                'teacher' => '../view/teacherpage.php',
                'librarian' => '../view/librarianpage.php',
                'staff' => '../view/staffpage.php',
                default => '../view/login.php'
            };
            header("Location: $redirect");
            exit();
        }
    }

    // Login method
    public function login() {
        if (!filter_var($this->email, FILTER_VALIDATE_EMAIL)) {
            echo "<script>alert('Enter a valid email');</script>";
            return;
        }

        $sql = "SELECT user_id, email, password, role FROM users WHERE email=?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("s", $this->email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 0) {
            echo "<script>alert('User not found');</script>";
            return;
        }

        $row = $result->fetch_assoc();

        if (!password_verify($this->password, $row['password'])) {
            echo "<script>alert('Invalid password');</script>";
            return;
        }

        // ✅ Set session variables
        $_SESSION['user_id'] = $row['user_id'];
        $_SESSION['user_type'] = $row['role'];

        // Redirect based on role
        $redirect = match($row['role']) {
            'student' => '../view/studentpage.php',
            'teacher' => '../view/teacherpage.php',
            'librarian' => '../view/librarianpage.php',
            'staff' => '../view/staffpage.php',
            default => '../view/login.php'
        };

        header("Location: $redirect");
        exit();
    }

    // Register method
    public function register() {
        if (!filter_var($this->email, FILTER_VALIDATE_EMAIL)) {
            echo "<script>alert('Enter a valid email');</script>";
            return;
        }

        $hashpassword = password_hash($this->password, PASSWORD_DEFAULT);

        $sql = "INSERT INTO users(lastname, firstname, email, password, role) 
                VALUES(?, ?, ?, ?, ?)";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("sssss", $this->lastname, $this->firstname, $this->email, $hashpassword, $this->role);

        if ($stmt->execute()) {
            echo "<script>alert('User registered successfully'); window.location='../view/login.php';</script>";
        } else {
            echo "<script>alert('Registration failed');</script>";
        }
    }
}
?>
