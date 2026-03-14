<?php
session_start();
include "db.php";

$error = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST['email'];
    $password = $_POST['password'];

    // Prepare statement to prevent SQL injection
    $stmt = $conn->prepare("SELECT * FROM users WHERE email=?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $res = $stmt->get_result();

    if ($res->num_rows == 1) {
        $user = $res->fetch_assoc();
        if (password_verify($password, $user['password'])) {
            // Set session variables
            $_SESSION['user_id'] = $user['user_id'];   // Changed from user_id to id
            $_SESSION['role']    = $user['role'];
            $_SESSION['name']    = $user['name'];

            // Redirect based on role
            if ($user['role'] == "Student") {
                header("Location: student.php");
            } elseif ($user['role'] == "Faculty") {
                header("Location: faculty.php");
            } elseif ($user['role'] == "Admin") {
                header("Location: admin_system.php
				");
            }
            exit();
        } else {
            $error = "Wrong password";
        }
    } else {
        $error = "User not found";
    }

    $stmt->close();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <link rel="stylesheet" href="style.css">
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            background-image: url("main_background.png");
            background-size: cover;
            background-position: center;
            background-repeat: no-repeat;
            opacity: 0.9;
            margin: 0;
			flex-direction: column;
        }
        .card {
            background: #fff;
            padding: 40px 30px;
            border-radius: 15px;
            box-shadow: 0 8px 20px rgba(0,0,0,0.3);
            width: 350px;
            text-align: center;
            transition: transform 0.3s ease;
        }
        .card:hover { transform: translateY(-5px); }
        .card h2 { margin-bottom: 20px; color: #333; }
        .card input {
            width: 100%;
            padding: 12px 15px;
            margin: 10px 0;
            border: 1px solid #ccc;
            border-radius: 8px;
            transition: 0.3s;
            outline: none;
        }
        .card input:focus {
            border-color: #2575fc;
            box-shadow: 0 0 5px #2575fc;
        }
        .card button {
            width: 100%;
            padding: 12px;
            margin-top: 10px;
            border: none;
            border-radius: 8px;
            background: #6a11cb;
            color: white;
            font-size: 16px;
            cursor: pointer;
            transition: 0.3s;
        }
        .card button:hover { background: #2575fc; }
        .error-msg { color: red; font-size: 14px; margin-bottom: 10px; min-height: 18px; }
        .card a { color: #6a11cb; text-decoration: none; }
        .card a:hover { text-decoration: underline; }
		
		/* Header text alignment */
.college-header {
    text-align: center;
    margin: 30px 0 10px 0;
    padding: 20px 40px;
    border-radius: 15px;
    background: linear-gradient(90deg, #b71c1c, #ff5722);
    color: #fff;
    box-shadow: 0 10px 25px rgba(0,0,0,0.4);
}

/* Header flex layout for logo + text */
.college-header .header-content {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 15px; /* space between logo and text */
}

/* Logo size */
.college-header img {
    width: 80px;
    height: auto;
    border-radius: 0; /* rectangular */
}

/* Header text alignment */
.college-header .header-text h1 {
    font-size: 32px;
    font-weight: 800;
    margin-bottom: 5px;
    color: #fff;
}

.college-header .header-text h3 {
    font-size: 16px;
    font-weight: 500;
    color: #ffe6e6;
}

/* Rolling message */
.scroll-container {
    width: 100%;
    overflow: hidden;
    background: rgba(0,0,0,0.05);
    padding: 10px 0;
    margin-bottom: 30px;
    color: #b71c1c;
    font-weight: bold;
}

.scroll-text {
    display: inline-block;
    white-space: nowrap;
    animation: scroll-left 15s linear infinite;
    font-size: 16px;
}

@keyframes scroll-left {
    0% { transform: translateX(100%); }
    100% { transform: translateX(-100%); }
}

    </style>
</head>
<body>

<!-- Header with logo beside college name -->
<div class="college-header">
    <div class="header-content">
        <img src="logo.jpg" alt="JEC Logo">
        <div class="header-text">
            <h1>Jabalpur Engineering College, Jabalpur (M.P.)</h1>
            <h3>Centralized Academic Project Management System</h3>
        </div>
    </div>
</div>

<!-- Rolling Message -->
<div class="scroll-container">
    <div class="scroll-text">
        🔥 Proect Submission is Open| Submit Your Projects | AI & ML | CSE | ECE | ME | CE | EE| IT | IP | Mechatronics 🔥
    </div>
</div>

<div class="card">
    <h2>Login</h2>
    <div class="error-msg"><?= $error ?></div>
    <form method="POST">
        <input type="email" name="email" placeholder="Email" required>
        <input type="password" name="password" placeholder="Password" required>
        <button type="submit">Login</button>
        
    </form>
</div>

<script>
    const inputs = document.querySelectorAll('.card input');
    inputs.forEach(input => {
        input.addEventListener('focus', () => input.style.backgroundColor = "#f0f8ff");
        input.addEventListener('blur', () => input.style.backgroundColor = "#fff");
    });
</script>

</body>
</html>
