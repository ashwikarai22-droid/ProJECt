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
<html>
<head>
    <title>Registration</title>

    <style>
        * {
            box-sizing: border-box;
            font-family: 'Segoe UI', sans-serif;
        }

        body {
            margin: 0;
            height: 100vh;
             background-image: url("register.jpg");
            display: flex;
            justify-content: center;
            align-items: center;
        }

        .card {
            background: #fff;
            width: 380px;
            padding: 30px;
            border-radius: 12px;
            box-shadow: 0 20px 40px rgba(0,0,0,0.2);
            animation: slideUp 0.6s ease;
        }

        @keyframes slideUp {
            from { transform: translateY(30px); opacity: 0; }
            to { transform: translateY(0); opacity: 1; }
        }

        h2 {
            text-align: center;
            margin-bottom: 20px;
            color: #333;
        }

        input, select {
            width: 100%;
            padding: 12px;
            margin-bottom: 15px;
            border-radius: 6px;
            border: 1px solid #ccc;
            transition: 0.3s;
            font-size: 14px;
        }

        input:focus, select:focus {
            border-color: #667eea;
            outline: none;
            box-shadow: 0 0 5px rgba(102,126,234,0.4);
        }

        .password-box {
            position: relative;
        }

        .toggle-password {
            position: absolute;
            right: 12px;
            top: 50%;
            transform: translateY(-50%);
            cursor: pointer;
            font-size: 13px;
            color: #667eea;
        }

        button {
            width: 100%;
            padding: 12px;
            background: #667eea;
            color: #fff;
            border: none;
            border-radius: 6px;
            font-size: 15px;
            cursor: pointer;
            transition: 0.3s;
        }

        button:hover {
            background: #5a67d8;
        }

        button.loading {
            opacity: 0.7;
            pointer-events: none;
        }

        p {
            text-align: center;
            font-size: 14px;
        }

        a {
            color: #667eea;
            text-decoration: none;
            font-weight: 500;
        }

        .success {
            color: green;
            margin-bottom: 10px;
            animation: fadeIn 0.5s ease;
        }

        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }

        @media (max-width: 420px) {
            .card {
                width: 90%;
            }
        }
    </style>
</head>

<body>

<img src="registerlogo.jpg" alt="Project Link" class="center">

<div class="card">
    <h2>Registration</h2>

    <?php if ($message) { ?>
        <p class="success"><?= $message ?></p>
    <?php } ?>

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

        if (pass.type === "password") {
            pass.type = "text";
            toggle.innerText = "Hide";
        } else {
            pass.type = "password";
            toggle.innerText = "Show";
        }
    }

    function validateForm() {
        const roll = document.getElementById("roll_no").value.trim();
        const name = document.getElementById("name").value.trim();
        const email = document.getElementById("email").value.trim();
        const role = document.getElementById("role").value;
        const btn = document.getElementById("btn");

        if (roll.length < 3) {
            alert("Roll number is too short");
            return false;
        }

        if (name.length < 3) {
            alert("Name must be at least 3 characters");
            return false;
        }

        if (role === "") {
            alert("Please select a role");
            return false;
        }

        btn.classList.add("loading");
        btn.innerText = "Registering...";
        return true;
    }
</script>

</body>
</html>
