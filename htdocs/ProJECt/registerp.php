<?php session_start();
 include "db.php";
 $message = ""; 
 // Get form data 
 $roll_no = $_POST['roll_no'] ?? ''; 
 $name = $_POST['name'] ?? '';
 $email = $_POST['email'] ?? '';
 $role = $_POST['role'] ?? ''; 
 if ($_SERVER["REQUEST_METHOD"] == "POST") { $password = password_hash($_POST['password'], PASSWORD_DEFAULT); 
 // Prepare statement to prevent SQL injection
 $stmt = $conn->prepare("INSERT INTO users (roll_no, name, email, password, role) VALUES (?, ?, ?, ?, ?)"); 
 $stmt->bind_param("sssss", $roll_no, $name, $email, $password, $role); 
 if ($stmt->execute()) { 
 $message = "Registration Successful"; 
 } 
 else { $message = "Roll No or Email already exists"; } 
 $stmt->close(); } 
 ?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Jabalpur Engineering College - Project Registration</title>

<link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;700&display=swap" rel="stylesheet">

<style>
/* Reset */
* {
    box-sizing: border-box;
    font-family: 'Roboto', sans-serif;
    margin: 0;
    padding: 0;
}

/* Body with simple background color */
body {
    min-height: 100vh;
    background-color: #f5f5f5; /* light gray */
    display: flex;
    flex-direction: column;
    align-items: center;
    padding: 20px;
}

/* College Header */
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

/* Card Form */
.card {
    background: #ffffff;
    width: 420px;
    max-width: 95%;
    padding: 35px 30px;
    border-radius: 20px;
    box-shadow: 0 20px 40px rgba(0,0,0,0.1);
    text-align: center;
    transition: transform 0.3s ease, box-shadow 0.3s ease;
}

.card:hover {
    transform: translateY(-5px);
    box-shadow: 0 25px 50px rgba(0,0,0,0.15);
}

/* Card Image */
.card img {
    width: 150px;
    margin-bottom: 20px;
    border-radius: 0; /* rectangular */
    border: 2px solid #b71c1c;
}

/* Headings */
.card p.heading {
    font-size: 22px;
    color: #b71c1c;
    font-weight: 700;
    margin-bottom: 20px;
}

/* Inputs */
input, select {
    width: 100%;
    padding: 14px 12px;
    margin-bottom: 15px;
    border-radius: 8px;
    border: 1px solid #ccc;
    font-size: 14px;
    transition: all 0.3s ease;
}

input:focus, select:focus {
    border-color: #b71c1c;
    box-shadow: 0 0 8px rgba(183,28,28,0.3);
    outline: none;
}

/* Password toggle */
.password-box { position: relative; }
.toggle-password {
    position: absolute;
    right: 12px;
    top: 50%;
    transform: translateY(-50%);
    cursor: pointer;
    font-size: 13px;
    color: #b71c1c;
}

/* Buttons */
button {
    width: 100%;
    padding: 14px;
    background: linear-gradient(90deg, #b71c1c, #ff5722);
    color: #fff;
    border: none;
    border-radius: 8px;
    font-size: 16px;
    font-weight: bold;
    cursor: pointer;
    transition: all 0.3s ease;
}

button:hover {
    background: linear-gradient(90deg, #ff5722, #b71c1c);
}

/* Links */
p a {
    color: #b71c1c;
    font-weight: 500;
    text-decoration: none;
}

p a:hover { text-decoration: underline; }

/* Success Message */
.success { color: green; margin-bottom: 10px; animation: fadeIn 0.5s ease; }
@keyframes fadeIn { from {opacity:0;} to{opacity:1;} }

/* Responsive */
@media (max-width: 480px) {
    .card { width: 95%; padding: 25px; }
    .college-header .header-text h1 { font-size: 28px; }
    .college-header .header-text h3 { font-size: 14px; }
    .scroll-text { font-size: 14px; }
    .college-header img { width: 60px; }
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

<!-- Registration Card -->
<div class="card">

    <?php if ($message) { ?>
        <p class="success"><?= $message ?></p>
    <?php } ?>

    <p class="heading">Academic Project Registration</p>

    <img src="registerlogo.jpg" alt="Project Logo">

    <form method="POST" onsubmit="return validateForm()">
        <input type="text" name="roll_no" id="roll_no" placeholder="Roll Number" required>
        <input type="text" name="name" id="name" placeholder="Full Name" required>
        <input type="email" name="email" id="email" placeholder="Email" required>

        <div class="password-box">
            <input type="password" name="password" id="password" placeholder="Password" required>
            <span class="toggle-password" onclick="togglePassword()">Show</span>
        </div>

        <select name="role" id="role" required>
            <option value="">Select Role</option>
            <option value="Student">Student</option>
            <option value="Faculty">Faculty</option>
            <option value="Admin">Admin</option>
        </select>

        <button type="submit" id="btn">Register</button>

        <p style="margin-top:15px;">
            Already registered? <a href="login.php">Go to login</a>
        </p>
    </form>
</div>

<script>
function togglePassword() {
    const pass = document.getElementById("password");
    const toggle = document.querySelector(".toggle-password");
    if(pass.type === "password") { pass.type = "text"; toggle.innerText="Hide"; }
    else { pass.type="password"; toggle.innerText="Show"; }
}

function validateForm() {
    const roll = document.getElementById("roll_no").value.trim();
    const name = document.getElementById("name").value.trim();
    const role = document.getElementById("role").value;
    const btn = document.getElementById("btn");
    if(roll.length<3){ alert("Roll number too short"); return false; }
    if(name.length<3){ alert("Name too short"); return false; }
    if(role===""){ alert("Select role"); return false; }
    btn.classList.add("loading"); btn.innerText="Registering...";
    return true;
}
</script>

</body>
</html>