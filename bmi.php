<?php

/* ===== Visitor Counter ===== */

$file = "visitor.txt";

if(!file_exists($file)){
    file_put_contents($file,"0");
}

$count = (int)file_get_contents($file);
$count++;
file_put_contents($file,$count);

$ip = $_SERVER['REMOTE_ADDR'];


/* ===== Online Users ===== */

$onlineFile = "online.json";
$timeout = 60; // วินาที

if(!file_exists($onlineFile)){
    file_put_contents($onlineFile,json_encode([]));
}

$online = json_decode(file_get_contents($onlineFile),true);

if(!is_array($online)){
    $online=[];
}

$time = time();

/* เพิ่มหรืออัพเดท IP */
$online[$ip] = $time;

/* ลบคน offline */
foreach($online as $user_ip=>$last){
    if(($time - $last) > $timeout){
        unset($online[$user_ip]);
    }
}

file_put_contents($onlineFile,json_encode($online));

$onlineCount = count($online);

?>


<!DOCTYPE html>
<html lang="th">
<head>

<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">

<title>ระบบประเมิน BMI (รพ.สต.หนองระเวียง)</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

<link href="https://fonts.googleapis.com/css2?family=Sarabun:wght@300;400;600&display=swap" rel="stylesheet">

    <!-- Favicon -->
    <link rel="icon" href="images/favicon.ico" type="images/.ico">
<style>

body{
font-family:'Sarabun',sans-serif;
background:linear-gradient(135deg,#fffaf3,#ffe8cc,#ffd8a8);
min-height:100vh;
}

/* ===== Card Premium ===== */

.card-pro{
border-radius:25px;
border:none;
background:rgba(255,255,255,0.92);
backdrop-filter: blur(10px);
transition:0.3s;
}

.card-pro:hover{
transform:translateY(-5px);
box-shadow:0 15px 40px rgba(0,0,0,0.15);
}

.card-header{
background:linear-gradient(90deg,#ffa94d,#ff922b);
font-size:20px;
letter-spacing:1px;
}

/* ===== Button ===== */

.btn-orange{
background:linear-gradient(90deg,#ffa94d,#ff922b);
border:none;
font-weight:bold;
transition:0.3s;
}

.btn-orange:hover{
transform:scale(1.03);
box-shadow:0 5px 15px rgba(255,146,43,0.4);
}

/* ===== Result ===== */

.result-box{
background:white;
border-radius:20px;
padding:25px;
border-left:6px solid #ff922b;
box-shadow:0 8px 25px rgba(0,0,0,0.08);
transition:0.3s;
}

/* ===== BMI BIG ===== */

.bmi-number{
color:#dc3545;
font-weight:700;
font-size:48px;
}

/* ===== Progress Premium ===== */

.progress{
height:25px;
border-radius:15px;
overflow:hidden;
}

.progress-bar{
background:linear-gradient(90deg,#ff922b,#ff6b6b,#dc3545);
transition:width 0.6s ease;
}

/* ===== Online Monitor ===== */

.online-box{
background:white;
border-radius:15px;
padding:15px;
box-shadow:0 5px 20px rgba(0,0,0,0.08);
max-width:400px;
margin:auto;
}

.online-user{
padding:4px 0;
border-bottom:1px solid #eee;
}

.bmi-status{
display:inline-block;
padding:6px 15px;
border-radius:50px;
font-weight:600;
margin-top:10px;
}

.card-pro{
box-shadow:0 10px 35px rgba(0,0,0,0.1);
}

</style>


</head>

<body>

<div class="container py-5">

<div class="row justify-content-center">

<div class="col-md-6">

<div class="card card-pro shadow-lg">

<div class="card-header text-white text-center fw-bold">
🏥 ระบบประเมิน BMI + รอบเอว (รพ.สต.หนองระเวียง)
</div>

<div class="card-body p-4">

<form id="bmiForm">

<input type="number"
id="weight"
class="form-control form-control-lg mb-3"
placeholder="น้ำหนัก (กก.)"
step="0.1"
min="0"
required>

<input type="number"
id="height"
class="form-control form-control-lg mb-3"
placeholder="ส่วนสูง (ซม.)"
step="0.1"
min="0"
required>

<input type="number" id="waist" class="form-control form-control-lg mb-4" placeholder="รอบเอว (ซม.) (ไม่บังคับ)">

<button class="btn btn-orange btn-lg w-100">
คำนวณผลสุขภาพ
</button>

</form>

<hr>

<div id="alertBox"></div>

<div id="result" class="result-box text-center"></div>

<div class="progress mt-3">
<div id="bmiBar" class="progress-bar"></div>
</div>

</div>
</div>

</div>
</div>

</div>

		<div class="mt-4 online-box">

		<div class="text-center mb-2">

		<span class="badge bg-success fs-6">
		🟢 ออนไลน์ตอนนี้ <?= $onlineCount ?> คน
		</span>

		</div>

		<div class="small text-start">

		<?php foreach($online as $online_ip => $t): ?>

		<div class="online-user">
		👤 <?= $online_ip ?>
		</div>

		<?php endforeach; ?>

		</div>

		</div>



<script>

document.getElementById("bmiForm").addEventListener("submit", function(e){

e.preventDefault();

let weight=parseFloat(document.getElementById("weight").value);
let height=parseFloat(document.getElementById("height").value)/100;
let waistValue=document.getElementById("waist").value;

if(!weight || !height) return;

let bmi=(weight/(height*height)).toFixed(1);

let status="",color="",width=0,alertType="success";
let recommend="";

if(bmi<18.5){
    status="น้ำหนักน้อย";
    color="bg-warning";
    width=20;
    alertType="warning";

    recommend=`
    🍽️ คำแนะนำ:
    <ul class="text-start mt-2">
        <li>รับประทานอาหารครบ 5 หมู่</li>
        <li>เพิ่มโปรตีน เช่น ไข่ นม เนื้อปลา</li>
        <li>กินอาหารให้ครบ 3 มื้อ</li>
        <li>ออกกำลังกายแบบเวทเทรนนิ่งเพิ่มกล้ามเนื้อ</li>
    </ul>`;
}
else if(bmi<23){
    status="สมส่วน";
    color="bg-success";
    width=40;
    alertType="success";

    recommend=`
    🏃 คำแนะนำ:
    <ul class="text-start mt-2">
        <li>รักษาพฤติกรรมสุขภาพที่ดี</li>
        <li>ออกกำลังกายอย่างน้อย 150 นาที/สัปดาห์</li>
        <li>นอนหลับให้เพียงพอ</li>
    </ul>`;
}
else if(bmi<25){
    status="น้ำหนักเกิน";
    color="bg-info";
    width=60;
    alertType="info";

    recommend=`
    🚶 คำแนะนำ:
    <ul class="text-start mt-2">
        <li>ลดอาหารหวาน มัน เค็ม</li>
        <li>เพิ่มการเดินหรือ cardio</li>
        <li>ควบคุมปริมาณอาหาร</li>
    </ul>`;
}
else if(bmi<30){
    status="อ้วนระดับ 1";
    color="bg-danger";
    width=80;
    alertType="danger";

    recommend=`
    🔥 คำแนะนำ:
    <ul class="text-start mt-2">
        <li>ควบคุมพลังงานอาหาร</li>
        <li>ออกกำลังกาย cardio เช่น เดินเร็ว วิ่ง ปั่นจักรยาน</li>
        <li>ลดน้ำหวานและของทอด</li>
    </ul>`;
}
else{
    status="อ้วนระดับ 2";
    color="bg-dark";
    width=100;
    alertType="danger";

    recommend=`
    ❤️ คำแนะนำ:
    <ul class="text-start mt-2">
        <li>ควรปรึกษาเจ้าหน้าที่สาธารณสุข</li>
        <li>ควบคุมอาหารอย่างจริงจัง</li>
        <li>เริ่มออกกำลังกายแบบค่อยเป็นค่อยไป</li>
    </ul>`;
}

/* ประเมินรอบเอว */
let waistText = "";

if(waistValue){
    let waist=parseFloat(waistValue);
    let waistRisk = waist>=90 ? "เสี่ยงโรคอ้วนลงพุง" : "รอบเอวปกติ";
    waistText = `<hr><div>ประเมินรอบเอว: <b>${waistRisk}</b></div>`;
}

/* แจ้งเตือน */
document.getElementById("alertBox").innerHTML=
`<div class="alert alert-${alertType} text-center">
ผลการประเมิน BMI เรียบร้อย
</div>`;

document.getElementById("result").innerHTML=
`
<div class="mb-2 text-muted">ผลการประเมินสุขภาพ</div>

<div>BMI = <span class="bmi-number">${bmi}</span></div>

<div class="bmi-status badge ${color} fs-6">
${status}
</div>

${waistText}

<div class="mt-4">${recommend}</div>
`;

let bar=document.getElementById("bmiBar");
bar.className="progress-bar "+color;
bar.style.width=width+"%";

});

document.querySelectorAll("#weight,#height").forEach(input=>{
    input.addEventListener("blur",function(){
        if(this.value){
            this.value=parseFloat(this.value).toFixed(1);
        }
    });
});


</script>

</body>
</html>
