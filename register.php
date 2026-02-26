<?php

// Debugging output
echo "<pre>";
print_r($_POST);
print_r($_FILES);
echo "</pre>";

// Optional: stop after printing to debug
// exit();

error_reporting(E_ALL);
ini_set('display_errors', 1);

// Database connection
$conn = mysqli_connect("localhost", "dinesh", "Sepaqipe123#", "Synergy");

// Check connection
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

// Get form data safely
$fullName = $_POST['fullName'];
$contactNumber = $_POST['contactNumber'];
$collegeName = $_POST['collegeName'];
$email = $_POST['email'];
$eventSelect = $_POST['eventSelect'];
$teamName = $_POST['teamName'];

// Handle uploaded file
$paymentProof = file_get_contents($_FILES['paymentProof']['tmp_name']); // Get the binary content

// Prepare the query
$stmt = $conn->prepare("INSERT INTO ignite25_registrations (fullName, contactNumber, collegeName, email, eventSelect, teamName, paymentProof) VALUES (?, ?, ?, ?, ?, ?, ?)");

// Bind parameters (notice the "b" for blob!)
$stmt->bind_param("ssssssb", $fullName, $contactNumber, $collegeName, $email, $eventSelect, $teamName, $null);

// Now bind the blob
$stmt->send_long_data(6, $paymentProof);  // 6 means the 7th "?" placeholder (starts from 0)

// Execute
if ($stmt->execute()) {
    echo "Registration successful!";
} else {
    echo "Error: " . $stmt->error;
}

$stmt->close();
$conn->close();
?>
