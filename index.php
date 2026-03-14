<?php
include "db.php";

/* ROLE LOGIC (replace with $_SESSION later) */
$userRole = 'Mentor';
$canEdit = ($userRole === 'Mentor' || $userRole === 'Team Leader');

/* SAVE / UPDATE */
if (isset($_POST['save_task']) && $canEdit) {
    $title = $conn->real_escape_string($_POST['title']);
    $priority = $conn->real_escape_string($_POST['priority']);
    $date = $_POST['date'];
    $id = $_POST['event_id'];

    if (!empty($id)) {
        $conn->query("UPDATE calendar_events 
                      SET title='$title', priority='$priority', event_date='$date'
                      WHERE id=$id");
    } else {
        $conn->query("INSERT INTO calendar_events (title, priority, event_date)
                      VALUES ('$title', '$priority', '$date')");
    }
    header("Location: index.php");
    exit;
}

/* DELETE */
if (isset($_GET['delete']) && $canEdit) {
    $id = (int)$_GET['delete'];
    $conn->query("DELETE FROM calendar_events WHERE id=$id");
    header("Location: index.php");
    exit;
}

/* FETCH EVENTS */
$events = [];
$res = $conn->query("SELECT * FROM calendar_events");
while ($row = $res->fetch_assoc()) {
    $events[] = $row;
}
?>
<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<title>Project Calendar</title>

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

<?php if($canEdit): ?>
<form method="POST">
<input type="hidden" name="date" id="fdate">
<input type="hidden" name="event_id" id="fid">
<input name="title" id="ftitle" placeholder="Task title" required>

<select name="priority" id="fpriority">
<option value="p-high">High Priority</option>
<option value="p-med">Medium Priority</option>
<option value="p-low">Low Priority</option>
</select>

<button name="save_task">Save Task</button>
</form>
<?php endif; ?>
</div>

</div>

<script>
const events = <?php echo json_encode($events); ?>;

let d = new Date(),
    m = d.getMonth(),
    y = d.getFullYear(),
    sel = null;

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
        <div class="cell ${ds===new Date().toISOString().split('T')[0]?'today':''}"
             onclick="pick('${ds}')">
            <strong>${i}</strong><br>${dots}
        </div>`;
    }
}
render();

function pick(ds){
    sel = ds;
    document.getElementById('selDate').innerText = ds;
    document.getElementById('fdate').value = ds;
    load(ds);
}

function load(ds){
    const list = document.getElementById('list');
    list.innerHTML='';
    events.filter(e=>e.event_date===ds).forEach(e=>{
        list.innerHTML += `
        <div class="event">
            <b>${e.title}</b>
            <a href="?delete=${e.id}" onclick="return confirm('Delete this task?')">🗑</a>
        </div>`;
    });
}
</script>

</body>
</html>
