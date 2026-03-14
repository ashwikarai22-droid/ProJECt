<?php
session_start();

if (!isset($_SESSION['role']) || $_SESSION['role'] != 'Faculty') {
    header("Location: login.php");
    exit();
}

include "db.php";

/* ============================
   HANDLE APPROVE / REJECT
============================ */
if (isset($_POST['update_status'])) {
    $project_id = intval($_POST['project_id']);
    $status = ($_POST['status'] === 'Approved') ? 'Approved' : 'Rejected';

    $stmt = $conn->prepare(
        "UPDATE projects SET mentorship_request_status=? WHERE project_id=?"
    );
    $stmt->bind_param("si", $status, $project_id);
    $stmt->execute();
    $stmt->close();

    header("Location: faculty.php");
    exit();
}

/* ============================
   FETCH DATA
============================ */
$pending = $conn->query(
    "SELECT * FROM projects WHERE mentorship_request_status='Pending'"
);

$approved = $conn->query(
    "SELECT * FROM projects WHERE mentorship_request_status='Approved'"
);
?>

<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<title>Faculty Dashboard</title>

<style>
*{box-sizing:border-box}
body{
    font-family:'Segoe UI',Arial,sans-serif;
    background:linear-gradient(135deg,#eef2f7,#f8f9fb);
    margin:20px;
}
h1{color:#2c3e50;margin:25px 0 15px}
table{
    width:100%;
    border-collapse:collapse;
    background:#fff;
    border-radius:14px;
    overflow:hidden;
    box-shadow:0 15px 40px rgba(0,0,0,.08);
}
th,td{padding:14px;text-align:left;vertical-align:top}
th{
    background:linear-gradient(135deg,#3498db,#2980b9);
    color:#fff;font-weight:600
}
tr:hover{background:#f7faff}
button{
    background:linear-gradient(135deg,#3498db,#2980b9);
    color:#fff;border:none;
    padding:6px 12px;
    border-radius:8px;
    cursor:pointer;
    font-size:13px;
}
button:disabled{
    background:#bdc3c7;
    cursor:not-allowed;
}
.details{
    display:none;
    margin-top:10px;
    background:#f4f8ff;
    padding:12px;
    border-radius:10px;
}
.team-member{
    margin-top:8px;
    padding-left:10px;
    border-left:3px solid #3498db;
}
select{
    padding:6px;
    border-radius:6px;
}
.badge{
    display:inline-block;
    padding:5px 12px;
    border-radius:20px;
    font-size:12px;
    color:#fff;
    font-weight:600;
}
.approved{background:#2ecc71}
.rejected{background:#e74c3c}
.pending{background:#f39c12}
form{display:flex;gap:6px;align-items:center}
</style>

<script>
function toggleDetails(id){
    const el=document.getElementById(id);
    el.style.display = (el.style.display==='block')?'none':'block';
}
</script>

</head>
<body>

<!-- ======================================================
     PENDING MENTORSHIP REQUESTS
====================================================== -->

<h1>Mentorship Requests (Pending)</h1>

<?php if($pending->num_rows==0): ?>
<p>No pending mentorship requests.</p>
<?php else: ?>

<table>
<tr>
<th>Project</th>
<th>Team</th>
<th>Team Leader</th>
<th>Project File</th>
<th>Status</th>
</tr>

<?php while($row=$pending->fetch_assoc()): ?>
<tr>

<td>
<strong><?= htmlspecialchars($row['title']) ?></strong><br><br>
<button onclick="toggleDetails('p<?= $row['project_id'] ?>')">Project Details</button>
<div id="p<?= $row['project_id'] ?>" class="details">
<?= nl2br(htmlspecialchars($row['description'])) ?><br><br>
Type: <?= $row['project_type'] ?><br>
Requested Faculty: <?= htmlspecialchars($row['requested_faculty_name']) ?>
</div>
</td>

<td><?= htmlspecialchars($row['team_name']) ?></td>

<td>
    <button onclick="toggleLeader('leader<?= $row['project_id'] ?>')">
        <?= htmlspecialchars($row['leader_name']) ?>
    </button>

    <div id="leader<?= $row['project_id'] ?>" class="details">
        <strong>Roll No:</strong>
        <?= htmlspecialchars($row['leader_roll_no']) ?>
        <br>

        <strong>Branch:</strong> <?= $row['leader_branch'] ?><br>
        <strong>Semester:</strong> <?= $row['leader_sem'] ?><br>

        <strong>Email:</strong>
        <?= htmlspecialchars($row['leader_email']) ?>
        <br>

        <strong>Phone:</strong>
        <?= htmlspecialchars($row['leader_phone']) ?>
        <br><br>

        <button onclick="toggleDetails('leader<?= $row['project_id'] ?>')">

            Team Members
        </button>

        <div id="members<?= $row['project_id'] ?>" class="team-member">
            <?php
            for ($i = 1; $i <= 3; $i++) {
                if (!empty($row["member{$i}_name"])) {
                    echo "<strong>Member {$i}:</strong> " .
                        htmlspecialchars($row["member{$i}_name"]) .
                        " | Roll: " . htmlspecialchars($row["member{$i}_roll_no"]) .
                        " | Branch: " . $row["member{$i}_branch"] .
                        " | Sem: " . $row["member{$i}_sem"] .
                        " | Email: " . htmlspecialchars($row["member{$i}_email"]) .
                        " | Phone: " . htmlspecialchars($row["member{$i}_phone"]) .
                        "<br>";
                }
            }
            ?>
        </div>
    </div>
</td>

<td>
<?php if($row['project_file']): ?>
<a href="<?= htmlspecialchars($row['project_file']) ?>" target="_blank">
<button>View</button></a>
<?php else: ?>No File<?php endif; ?>
</td>

<td>
<span class="badge pending">Pending</span><br><br>
<form method="post">
<input type="hidden" name="project_id" value="<?= $row['project_id'] ?>">
<select name="status">
<option value="Approved">Approve</option>
<option value="Rejected">Reject</option>
</select>
<button name="update_status">Update</button>
</form>
</td>

</tr>
<?php endwhile; ?>
</table>
<?php endif; ?>

<!-- ======================================================
     APPROVED PROJECTS
====================================================== -->

<h1>Approved Projects</h1>

<?php if($approved->num_rows==0): ?>
<p>No approved projects yet.</p>
<?php else: ?>

<table>
<tr>
<th>Project Name</th>
<th>Team</th>
<th>Project File</th>
<th>Create Tasks</th>
<th>Assign Tasks</th>
<th>Kanban</th>
<th>Gantt</th>
<th>Status</th>
<th>Discussion</th>
</tr>

<?php while($row=$approved->fetch_assoc()): ?>
<tr>

<!-- PROJECT -->
<td>
<strong><?= htmlspecialchars($row['title']) ?></strong><br><br>
<button onclick="toggleDetails('ap<?= $row['project_id'] ?>')">Project Details</button>
<div id="ap<?= $row['project_id'] ?>" class="details">
<?= nl2br(htmlspecialchars($row['description'])) ?><br><br>
Type: <?= $row['project_type'] ?><br>
Submission: <?= $row['submission_date'] ?>
</div>
</td>

<!-- TEAM -->
<td>
<button onclick="toggleDetails('team<?= $row['project_id'] ?>')">
<?= htmlspecialchars($row['team_name']) ?>
</button>
<div id="team<?= $row['project_id'] ?>" class="details">
<b>Leader</b><br>
<?= $row['leader_name'] ?> (<?= $row['leader_roll_no'] ?>)<br>
<?= $row['leader_branch'] ?> | <?= $row['leader_sem'] ?><br>
<?= $row['leader_email'] ?> | <?= $row['leader_phone'] ?><br><br>

<b>Members</b><br>
<?php
for($i=1;$i<=3;$i++){
if(!empty($row["member{$i}_name"])){
echo "<div class='team-member'>
{$row["member{$i}_name"]} ({$row["member{$i}_roll_no"]})<br>
{$row["member{$i}_branch"]} | {$row["member{$i}_sem"]}<br>
{$row["member{$i}_email"]} | {$row["member{$i}_phone"]}
</div>";
}}
?>
</div>
</td>

<!-- FILE -->
<td>
<?php if($row['project_file']): ?>
<a href="<?= htmlspecialchars($row['project_file']) ?>" target="_blank">
<button>View</button></a>
<?php else: ?>No File<?php endif; ?>
</td>

<!-- CALENDAR -->
<td><a href="test_f_calender.php"><button>Calendar</button></a></td>

<!-- PLACEHOLDERS -->
<td>
<a href="assign_tasks.php?project_id=<?= $row['project_id'] ?>">
<button>Assign</button>
</a>
</td>

<td>
<a href="kanban.php?project_id=<?= $row['project_id'] ?>">
    <button>View</button>
</a>
</td>

<td><a href="gantt_from_kanban.php?project_id=<?= $row['project_id'] ?>">
    <button>View</button>
</a>
</td>

<td>
<span class="badge approved">Active</span><br><br>
<a href="project_status2.php?project_id=<?= $row['project_id'] ?>">
<button>View / Update</button>
</a>

</td>
<td>
<a href="forum.php?project_id=<?= $row['project_id'] ?>">
    <button style="background:#2ecc71">Team Discussion</button>
</a>
</td>

</tr>
<?php endwhile; ?>
</table>
<?php endif; ?>

</body>
</html>
