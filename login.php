<?php
// Start the session
session_start();

// List of allowed users
$users = [
    "ishari" => "123456",
    "john" => "pass123",
    "admin" => "admin@2025"
];

// Get input
$username = $_POST['username'] ?? '';
$password = $_POST['password'] ?? '';

// Check if user exists and password matches
if (isset($users[$username]) && $users[$username] === $password) {
    $_SESSION['username'] = $username;
    header("Location: admin.html");
    exit();
} else {
    echo "<script>alert('Invalid credentials'); window.location.href='login.html';</script>";
}
?>