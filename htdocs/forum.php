<?php
session_start();
include "db.php";

// Check Login
if (!isset($_SESSION['user_id'])) { header("Location: login.php"); exit(); }

$project_id = intval($_GET['project_id']);
$user_id = $_SESSION['user_id'];
$user_role = $_SESSION['role']; // 'Student' or 'Faculty'
$user_name = $_SESSION['name']; 

// Fetch Project Details
$project_res = $conn->query("SELECT * FROM projects WHERE project_id = $project_id");
$project = $project_res->fetch_assoc();

// Role Identification (For Leader/Member/Faculty)
$display_role = $user_role;
if($user_role == 'Student') {
    $display_role = ($user_id == $project['student_id']) ? "Team Leader" : "Team Member";
}

// HANDLE MESSAGE SUBMISSION
if (isset($_POST['send_msg'])) {
    $msg = mysqli_real_escape_string($conn, $_POST['message']);
    $is_private = intval($_POST['privacy']); // 0 or 1
    
    if(!empty($msg)) {
        $sql = "INSERT INTO forum_messages (project_id, sender_id, sender_name, sender_role, message, is_private) 
                VALUES ($project_id, $user_id, '$user_name', '$display_role', '$msg', $is_private)";
        $conn->query($sql);
        header("Location: forum.php?project_id=$project_id");
        exit();
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>ProJECt | Forum</title>
    <style>
        :root { --sidebar-bg: #1e293b; --accent: #2563eb; --private-bubble: #fff7ed; }
        body { font-family: 'Inter', sans-serif; display: flex; height: 100vh; margin: 0; background: #f1f5f9; }
        
        /* Sidebar Styles */
        .sidebar { width: 300px; background: var(--sidebar-bg); color: white; display: flex; flex-direction: column; }
        .sidebar-header { padding: 20px; background: #0f172a; font-weight: bold; }
        .info-section { padding: 20px; flex-grow: 1; }
        .card { background: #334155; padding: 12px; border-radius: 8px; margin-bottom: 10px; border-left: 4px solid #94a3b8; }
        .card.active { border-left-color: #22c55e; }
        .card small { font-size: 10px; color: #94a3b8; text-transform: uppercase; }
        .card h5 { margin: 2px 0; font-size: 14px; }

        /* Chat Styles */
        .chat-main { flex-grow: 1; display: flex; flex-direction: column; background: white; }
        .msg-container { flex-grow: 1; padding: 20px; overflow-y: auto; display: flex; flex-direction: column; gap: 12px; background: #f8fafc; }
        
        .bubble { padding: 12px; border-radius: 12px; max-width: 70%; font-size: 14px; box-shadow: 0 2px 4px rgba(0,0,0,0.05); }
        .public { background: white; border: 1px solid #e2e8f0; align-self: flex-start; }
        .private { background: var(--private-bubble); border: 1px solid #fdba74; align-self: flex-start; }
        .my-msg { align-self: flex-end; background: #dbeafe; border: 1px solid #bfdbfe; }
        
        .role-tag { font-weight: bold; font-size: 11px; display: block; margin-bottom: 3px; }
        .lock-icon { color: #f97316; font-size: 10px; font-weight: bold; }

        .footer { padding: 20px; border-top: 1px solid #e2e8f0; display: flex; flex-direction: column; gap: 10px; }
        .controls { display: flex; gap: 10px; }
        input, select { padding: 10px; border-radius: 6px; border: 1px solid #ddd; }
        button { background: var(--accent); color: white; border: none; padding: 10px 20px; border-radius: 6px; cursor: pointer; }
    </style>
</head>
<body>

<div class="sidebar">
    <div class="sidebar-header">ProJECt FORUM</div>
    <div class="info-section">
        <?php if($user_role == 'Faculty'): ?>
            <small>Active Team Members</small>
            <div class="card active"><h5><?= $project['leader_name'] ?></h5><small>Team Leader</small></div>
            <?php for($i=1;$i<=3;$i++) if(!empty($project["member{$i}_name"])): ?>
                <div class="card"><h5><?= $project["member{$i}_name"] ?></h5><small>Member <?= $i ?></small></div>
            <?php endif; ?>
        <?php else: ?>
            <small>Assigned Mentor</small>
            <div class="card active"><h5><?= $project['requested_faculty_name'] ?></h5><small>Faculty</small></div>
        <?php endif; ?>
    </div>
</div>

<div class="chat-main">
    <div class="msg-container" id="chatBox">
        <?php
        // SMART PRIVACY QUERY: 
        // 1. Show all Public messages (is_private=0)
        // 2. Show Private messages ONLY IF user is Faculty OR user is the Sender
        $query = "SELECT * FROM forum_messages WHERE project_id = $project_id AND (
                    is_private = 0 OR 
                    (is_private = 1 AND (sender_id = $user_id OR '$user_role' = 'Faculty'))
                  ) ORDER BY sent_at ASC";
        
        $msgs = $conn->query($query);
        while($m = $msgs->fetch_assoc()):
            $class = ($m['sender_id'] == $user_id) ? "my-msg" : (($m['is_private'] == 1) ? "private" : "public");
        ?>
            <div class="bubble <?= $class ?>">
                <?php if($m['is_private'] == 1): ?> <span class="lock-icon">🔒 PRIVATE</span> <?php endif; ?>
                <span class="role-tag"><?= htmlspecialchars($m['sender_name']) ?> (<?= $m['sender_role'] ?>)</span>
                <?= nl2br(htmlspecialchars($m['message'])) ?>
            </div>
        <?php endwhile; ?>
    </div>

    <form class="footer" method="POST">
        <div class="controls">
            <select name="privacy">
                <option value="0">To: Everyone</option>
                <option value="1">To: Mentor (Private)</option>
            </select>
            <input type="text" name="message" placeholder="Type your message..." required style="flex-grow:1;">
            <button type="submit" name="send_msg">Send</button>
        </div>
    </form>
</div>

<script>document.getElementById('chatBox').scrollTop = document.getElementById('chatBox').scrollHeight;</script>

</body>
</html>