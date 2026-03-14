<?php
session_start();
include "db.php";

if (!isset($_SESSION['role'])) {
    header("Location: login.php");
    exit();
}

if (!isset($_GET['project_id'])) {
    die("Project not selected");
}

$project_id = (int)$_GET['project_id'];

/* FETCH PROJECT */
$project = $conn->query(
    "SELECT title FROM projects WHERE project_id=$project_id"
)->fetch_assoc();

/* FETCH KANBAN TASKS */
$res = $conn->query("
    SELECT 
        task_title,
        status,
        DATE(created_at) AS start_date,
        due_date
    FROM kanban
    WHERE project_id = $project_id
    ORDER BY created_at
");

$tasks = [];
while ($row = $res->fetch_assoc()) {

    // Auto progress mapping
    switch ($row['status']) {
        case 'Done':        $progress = 100; break;
        case 'Review':      $progress = 80;  break;
        case 'InProgress':  $progress = 50;  break;
        default:            $progress = 0;
    }

    $tasks[] = [
        'name'     => $row['task_title'],
        'start'    => $row['start_date'],
        'end'      => $row['due_date'],
        'progress' => $progress
    ];
}
?>

<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<title>Gantt Chart</title>

<style>
body{
    font-family:Segoe UI,system-ui;
    background:#f8fafc;
    padding:30px;
}

h2{
    text-align:center;
    margin-bottom:30px;
    color:#1e3a8a;
}

.chart{
    max-width:1000px;
    margin:auto;
    background:#fff;
    padding:25px;
    border-radius:16px;
    box-shadow:0 15px 40px rgba(0,0,0,.08);
}

.row{
    display:grid;
    grid-template-columns:200px 1fr;
    align-items:center;
    margin-bottom:16px;
}

.task-name{
    font-weight:600;
    font-size:14px;
    color:#111827;
}

.timeline{
    position:relative;
    height:22px;
    background:#e5e7eb;
    border-radius:999px;
    overflow:hidden;
}

.bar{
    height:100%;
    border-radius:999px;
    background:linear-gradient(135deg,#2563eb,#1e40af);
    display:flex;
    align-items:center;
    justify-content:flex-end;
    padding-right:8px;
    color:#fff;
    font-size:12px;
    font-weight:600;
    white-space:nowrap;
}
</style>
</head>

<body>

<h2>Gantt Chart – <?= htmlspecialchars($project['title']) ?></h2>

<div class="chart" id="chart"></div>

<script>
const tasks = <?= json_encode($tasks) ?>;

// Find global date range
const dates = tasks.flatMap(t => [
    new Date(t.start),
    new Date(t.end)
]);

const minDate = new Date(Math.min(...dates));
const maxDate = new Date(Math.max(...dates));
const totalDays = (maxDate - minDate) / (1000*60*60*24) + 1;

const chart = document.getElementById('chart');

tasks.forEach(t => {
    const startOffset =
        (new Date(t.start) - minDate) / (1000*60*60*24);
    const duration =
        (new Date(t.end) - new Date(t.start)) / (1000*60*60*24) + 1;

    const width = (duration / totalDays) * 100;
    const left  = (startOffset / totalDays) * 100;

    const row = document.createElement('div');
    row.className = 'row';

    row.innerHTML = `
        <div class="task-name">${t.name}</div>
        <div class="timeline">
            <div class="bar"
                 style="width:${width}%;
                        margin-left:${left}%;">
                ${t.progress}%
            </div>
        </div>
    `;

    chart.appendChild(row);
});
</script>

</body>
</html>
