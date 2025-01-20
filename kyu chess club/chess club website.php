<?php
// Database connection
$db_host = 'localhost';
$db_user = 'your_username';
$db_pass = 'your_password';
$db_name = 'chess_club';

$conn = new mysqli($db_host, $db_user, $db_pass, $db_name);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Get form data and sanitize
    $username = mysqli_real_escape_string($conn, $_POST['username']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT); // Hash the password
    $fide_id = mysqli_real_escape_string($conn, $_POST['fide_id']);
    $phone = mysqli_real_escape_string($conn, $_POST['phone']);
    $rating = mysqli_real_escape_string($conn, $_POST['rating']);

    // Check if user already exists
    $check_query = "SELECT * FROM users WHERE email = ? OR username = ?";
    $check_stmt = $conn->prepare($check_query);
    $check_stmt->bind_param("ss", $email, $username);
    $check_stmt->execute();
    $result = $check_stmt->get_result();

    if ($result->num_rows > 0) {
        echo "User already exists with this email or username";
        exit();
    }

    // Insert new user
    $insert_query = "INSERT INTO users (username, email, password, fide_id, phone, rating) VALUES (?, ?, ?, ?, ?, ?)";
    $insert_stmt = $conn->prepare($insert_query);
    $insert_stmt->bind_param("ssssss", $username, $email, $password, $fide_id, $phone, $rating);

    if ($insert_stmt->execute()) {
        echo "Registration successful! Redirecting to login...";
        header("refresh:2;url=login.html");
    } else {
        echo "Error: " . $insert_stmt->error;
    }

    $insert_stmt->close();
}

$conn->close();
?>