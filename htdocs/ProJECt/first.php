<?php
// Database connection
$host = "localhost";
$user = "root";
$pass = "";
$db   = "project_db";

$conn = mysqli_connect($host, $user, $pass, $db);

if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

// Handle form submission
if (isset($_POST['register'])) {

    $roll_no  = mysqli_real_escape_string($conn, $_POST['roll_no']);
    $email    = mysqli_real_escape_string($conn, $_POST['email']);
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);

    $query = "INSERT INTO users (roll_no, email, password)
              VALUES ('$roll_no', '$email', '$password')";

    if (mysqli_query($conn, $query)) {
        echo "<script>alert('Registration Successful');</script>";
    } else {
        echo "<script>alert('Error: Roll No or Email already exists');</script>";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Registration Form</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f4f6f9;
        }
        .container {
            width: 400px;
            margin: 80px auto;
            padding: 20px;
            background: white;
            border-radius: 8px;
            box-shadow: 0 0 10px #ccc;
        }
        input {
            width: 100%;
            padding: 10px;
            margin: 8px 0;
        }
        button {
            width: 100%;
            padding: 10px;
            background: #007bff;
            color: white;
            border: none;
            cursor: pointer;
        }
        button:hover {
            background: #0056b3;
        }
    </style>
</head>
<body>

<div class="container">
    <h2>Student Registration</h2>
    <form method="POST">
        <input type="text" name="roll_no" placeholder="Roll Number" required>
        <input type="email" name="email" placeholder="Email ID" required>
        <input type="password" name="password" placeholder="Password" required>
        <button type="submit" name="register">Register</button>
    </form>
</div>

</body>
</html>
