<?php
include "db.php";

// --- LOGIC: ADD MENTOR ---
if(isset($_POST['add_mentor'])) {
    $name = $_POST['m_name']; $email = $_POST['m_email']; $dept = $_POST['m_dept'];
    $conn->query("INSERT INTO mentors (name, email, department) VALUES ('$name', '$email', '$dept')");
    header("Location: admin_system.php?tab=mentors");
}

// --- LOGIC: REMOVE MENTOR/STUDENT/PROJECT ---
if(isset($_GET['delete_mentor'])) {
    $id = $_GET['delete_mentor']; $conn->query("DELETE FROM mentors WHERE mentor_id=$id");
    header("Location: admin_system.php?tab=mentors");
}
if(isset($_GET['delete_project'])) {
    $id = $_GET['delete_project']; $conn->query("DELETE FROM projects WHERE project_id=$id");
    header("Location: admin_system.php?tab=projects");
}

$tab = isset($_GET['tab']) ? $_GET['tab'] : 'projects';
// --- ASSIGN MENTOR TO PROJECT ---
if (isset($_POST['assign_mentor'])) {
    $project_id = (int)$_POST['project_id'];
    $mentor_id  = (int)$_POST['mentor_id'];

    $conn->query("
        UPDATE projects 
        SET mentor_id = $mentor_id 
        WHERE project_id = $project_id
    ");

    header("Location: admin_system.php?tab=projects");
    exit();
}

?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Master Ecosystem</title>
    <style>
        body { font-family: 'Segoe UI', Arial; background: #f4f7f6; margin: 0; display: flex; }
        /* Sidebar */
        .sidebar { width: 220px; background: #2c3e50; color: white; height: 100vh; position: fixed; }
        .sidebar h2 { padding: 20px; font-size: 18px; background: #1a252f; margin: 0; }
        .sidebar a { display: block; color: #bdc3c7; padding: 15px 20px; text-decoration: none; border-bottom: 1px solid #34495e; }
        .sidebar a:hover, .sidebar a.active { background: #34495e; color: white; }

        /* Main Content */
        .main-content { margin-left: 220px; padding: 30px; width: calc(100% - 220px); }
        .card { background: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        
        /* Table Styles matching your Image */
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th { background: #000; color: white; padding: 12px; text-align: left; font-size: 13px; }
        td { padding: 12px; border: 1px solid #eee; font-size: 13px; vertical-align: top; }
        tr:nth-child(even) { background: #f9f9f9; }

        /* Buttons */
        .btn { padding: 6px 12px; border-radius: 4px; border: none; cursor: pointer; text-decoration: none; font-size: 12px; color: white; display: inline-block; }
        .btn-add { background: #27ae60; margin-bottom: 15px; }
        .btn-del { background: #e74c3c; }
        .btn-view { background: #3498db; }
        .btn-cyan { background: #5bc0de; }
        
        .form-group { margin-bottom: 15px; }
        input { padding: 8px; width: 200px; margin-right: 10px; border: 1px solid #ccc; border-radius: 4px; }
    </style>
</head>
<body>

<div class="sidebar">
    <h2>ADMIN PANEL</h2>
    <a href="?tab=projects" class="<?= $tab=='projects'?'active':'' ?>">📁 Manage Projects</a>
    <a href="?tab=mentors" class="<?= $tab=='mentors'?'active':'' ?>">👨‍🏫 Manage Mentors</a>
</div>

<div class="main-content">
    <div class="card">
	<?php if ($tab == 'assign' && isset($_GET['project_id'])): ?>

<h3>Assign Mentor to Project</h3>

<form method="post">
    <input type="hidden" name="project_id" 
           value="<?= (int)$_GET['project_id'] ?>">

    <div class="form-group">
        <label>Select Mentor</label><br>
        <select name="mentor_id" required>
            <option value="">-- Select Mentor --</option>
            <?php
            $mres = $conn->query("SELECT mentor_id, name, department FROM mentors");
            while ($m = $mres->fetch_assoc()):
            ?>
            <option value="<?= $m['mentor_id'] ?>">
                <?= $m['name'] ?> (<?= $m['department'] ?>)
            </option>
            <?php endwhile; ?>
        </select>
    </div>

    <button type="submit" name="assign_mentor" class="btn btn-add">
        Assign Mentor
    </button>
</form>

<?php endif; ?>

        
        <?php if($tab == 'projects'): ?>
            <h3>Project Ecosystem Oversight</h3>
            <table>
                <thead>
                    <tr>
                        <th>Team Details</th>
                        <th>Leader Name</th>
                        <th>Contact Detail</th>
                        <th>Idea Status</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $res = $conn->query("
    SELECT 
        p.*, 
        m.name AS mentor_name
    FROM projects p
    LEFT JOIN mentors m 
        ON p.mentor_id = m.mentor_id
");

                    while($row = $res->fetch_assoc()): ?>
                    <tr>
                        <td>
                            <b>ID:</b> <?= $row['project_id'] ?><br>
                            <b>Team:</b> <?= strtoupper($row['team_name']) ?><br>
                            <b>Cat:</b> <?= $row['project_type'] ?><br>
       <b>Mentor:</b> <?= $row['mentor_name'] ?? 'Not Assigned' ?>
 
                         <td>
    <?= $row['leader_name'] ?><br><br>

    <a href="view_members.php?project_id=<?= $row['project_id'] ?>"
       class="btn btn-view">
       View Members
    </a>
</td>
   <a href="?tab=assign&project_id=<?= $row['project_id'] ?>" 
       class="btn btn-view">
       Assign Mentor
    </a>
    <a href="?delete_project=<?= $row['project_id'] ?>" 
       class="btn btn-del"
       onclick="return confirm('Remove Project?')">
       Remove
    </a>
</td>

                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>

        <?php elseif($tab == 'mentors'): ?>
            <h3>Mentor Management</h3>
            <form method="POST" style="background: #f8fafc; padding: 15px; border-radius: 5px;">
                <input type="text" name="m_name" placeholder="Mentor Name" required>
                <input type="email" name="m_email" placeholder="Email" required>
                <input type="text" name="m_dept" placeholder="Department">
                <button type="submit" name="add_mentor" class="btn btn-add">Add New Mentor</button>
            </form>

            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Mentor Name</th>
                        <th>Email</th>
                        <th>Department</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $res = $conn->query("SELECT * FROM mentors");
                    while($row = $res->fetch_assoc()): ?>
                    <tr>
                        <td><?= $row['mentor_id'] ?></td>
                        <td><b><?= $row['name'] ?></b></td>
                        <td><?= $row['email'] ?></td>
                        <td><?= $row['department'] ?></td>
                        <td>
                            <a href="?delete_mentor=<?= $row['mentor_id'] ?>" class="btn btn-del" onclick="return confirm('Remove Mentor?')">Remove</a>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        <?php endif; ?>

    </div>
</div>

</body>
</html>