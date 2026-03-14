<?php
include "db.php";

/* STUDENT ROLE (READ ONLY) */
$userRole = 'Student';

/* FETCH EVENTS ONLY */
$events = [];
$res = $conn->query("SELECT * FROM calendar_events");
while ($row = $res->fetch_assoc()) {
    $events[] = $row;
}

?><!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<title>Student Calendar</title>
<style>
*{box-sizing:border-box}

body{
    font-family:'Segoe UI',sans-serif;
   background-image: url("calender_backgorund.jpg");
    padding:20px;
}

/* MAIN CARD */
.main{
    display:flex;
    max-width:1150px;
    margin:auto;
    background:#fff;
    border-radius:16px;
    box-shadow:0 15px 40px rgba(0,0,0,.1);
    overflow:hidden;
}

/* LEFT CALENDAR */
.cal{
    flex:2;
    padding:30px;
}
.cal h2{
    margin-bottom:20px;
    color:#1f2937;
}

/* RIGHT PANEL */
.side{
    flex:1;
    padding:30px;
    background:#f9fafb;
    border-left:1px solid #e5e7eb;
}
.side h3{
    margin-bottom:15px;
    color:#374151;
}

/* GRID */
.grid{
    display:grid;
    grid-template-columns:repeat(7,1fr);
    gap:10px;
}

/* DAY CELL */
.cell{
    height:85px;
    border:1px solid #e5e7eb;
    border-radius:12px;
    padding:8px;
    cursor:pointer;
    transition:all .2s ease;
    background:#fff;
}
.cell:hover{
    border-color:#2563eb;
    box-shadow:0 6px 18px rgba(37,99,235,.2);
    transform:translateY(-2px);
}

/* TODAY */
.today{
    background:#ecfdf5;
    border-color:#10b981;
    box-shadow:0 0 0 2px rgba(16,185,129,.2);
}

/* DOTS */
.dot{
    width:7px;
    height:7px;
    border-radius:50%;
    display:inline-block;
    margin-right:3px;
}
.p-high{background:#ef4444}
.p-med{background:#f59e0b}
.p-low{background:#10b981}

/* EVENT LIST */
.event{
    background:#fff;
    padding:12px;
    border-radius:10px;
    margin-bottom:10px;
    display:flex;
    justify-content:space-between;
    align-items:center;
    animation:fadeIn .3s ease;
    box-shadow:0 4px 12px rgba(0,0,0,.05);
}

@keyframes fadeIn{
    from{opacity:0;transform:translateY(5px)}
    to{opacity:1;transform:none}
}

/* FORM */
input,select,button{
    width:100%;
    padding:10px;
    margin-bottom:10px;
    border-radius:8px;
    border:1px solid #d1d5db;
    font-size:14px;
}
input:focus,select:focus{
    outline:none;
    border-color:#2563eb;
    box-shadow:0 0 0 3px rgba(37,99,235,.2);
}

/* BUTTON */
button{
    background:linear-gradient(135deg,#2563eb,#1d4ed8);
    color:#fff;
    border:none;
    cursor:pointer;
    transition:all .2s ease;
}
button:hover{
    transform:translateY(-1px);
    box-shadow:0 8px 22px rgba(37,99,235,.35);
}

/* DELETE ICON */
a{
    text-decoration:none;
    font-size:18px;
}
</style>
</head>

<body>

<div class="main">

<!-- CALENDAR -->
<div class="cal">
<h2 id="title"></h2>
<div class="grid" id="grid"></div>
</div>

<!-- SIDE PANEL -->
<div class="side">
<h3 id="selDate">Select date</h3>
<div id="list"></div>
</div>

</div>
<script>
const events = <?php echo json_encode($events); ?>;

let d = new Date(),
    m = d.getMonth(),
    y = d.getFullYear();

const grid = document.getElementById('grid');

function render(){
    grid.innerHTML='';
    document.getElementById('title').innerText =
        d.toLocaleString('default',{month:'long'}) + ' ' + y;

    const first = new Date(y,m,1).getDay();
    const days = new Date(y,m+1,0).getDate();

    for(let i=0;i<first;i++) grid.innerHTML += '<div></div>';

    for(let i=1;i<=days;i++){
        const ds = `${y}-${String(m+1).padStart(2,'0')}-${String(i).padStart(2,'0')}`;
        const ev = events.filter(e => e.event_date === ds);
        const dots = ev.map(e => `<span class="dot ${e.priority}"></span>`).join('');

        grid.innerHTML += `
        <div class="cell" onclick="pick('${ds}')">
            <strong>${i}</strong><br>${dots}
        </div>`;
    }
}
render();

function pick(ds){
    document.getElementById('selDate').innerText = ds;
    load(ds);
}

function load(ds){
    const list = document.getElementById('list');
    list.innerHTML='';
    events.filter(e=>e.event_date===ds).forEach(e=>{
        list.innerHTML += `
        <div class="event">
            <b>${e.title}</b><br>
            ${e.event_type === 'meeting'
                ? `<small>⏰ ${e.meeting_time} |
                   <a href="${e.meeting_link}" target="_blank">Join</a></small>`
                : `<small>Task</small>`
            }
        </div>`;
    });
}
</script>

</body>
</html>