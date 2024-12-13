<?php
session_start();
include '../config/database.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../login.php');
    exit();
}

if (!isset($_GET['user_name'])) {
    die("Nama user tidak ditentukan.");
}

$no = 1;

$user_name = mysqli_real_escape_string($conn, $_GET['user_name']);

$results = $conn->query("
    SELECT q.question_text, c.choice_text, c.is_correct 
    FROM user_answers ua
    JOIN questions q ON ua.question_id = q.id
    JOIN choices c ON ua.answer = c.id
    WHERE ua.user_name = '$user_name'
");

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detail Jawaban User</title>
    <link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" href="../css/styleadmin.css">
    <style>
        .table-grid{
            max-height: 480px;
            grid-template-columns: 35px 1fr 1fr 1fr;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="main">
        <div class="topbar">
                <div class="toggle">
                    <ion-icon name="menu-outline"></ion-icon>
                </div>
            </div>
    <!-- div Conateinr-->
     <div class="contain">
            <h2 style="padding-top:10px; padding-left: 10px;">Nama : <?php echo htmlspecialchars($user_name); ?></h2> <br>
            <?php if ($results->num_rows > 0): ?>
                <div style="overflow-x:auto; margin-top:10px; height:auto; font-size: 12px;">
                <div class="table-grid">
                    <div class="table-header">No</div>
                    <div class="table-header">Soal</div>
                    <div class="table-header">Jawaban yang Dipilih</div>
                    <div class="table-header">Status Jawaban</div>
                    <?php while ($row = $results->fetch_assoc()): ?>
                        <div class="table-cell"><?php echo $no++ ?></div>
                        <div class="table-cell"><?php echo htmlspecialchars($row['question_text']); ?></div>
                        <div class="table-cell"><?php echo htmlspecialchars($row['choice_text']); ?></div>
                        <div class="table-cell"><?php echo $row['is_correct'] ? "Benar" : "<b>Salah</b>"; ?></div>
                    <?php endwhile; ?>
                </div>
                </div>
            <?php else: ?>
                <p>Tidak ada jawaban yang ditemukan untuk user ini.</p>
            <?php endif; ?>
            </div>
        </div>    
    </div>
    <div class="navigation">
            <ul>
                <li>
                    <a href="#">
                    <span class="icon"><img src="../img/logo-karawang.png" alt=""></span>
                    <span class="title" style="font-size:20px; margin-top:10px;"><b>DISNAKERTRANS</b></span>
                    </a>
                </li>
                <li>
                    <a href="admin_dashboard.php">
                        <span class="icon"><ion-icon name="bar-chart-outline"></ion-icon></span>
                        <span class="title">Dashboard</span>
                    </a>
                </li>
                <li>
                    <a href="add_question.php">
                        <span class="icon"><ion-icon name="browsers-outline"></ion-icon></span>
                        <span class="title">Kumpulan Soal</span>
                    </a>
                </li>
                <li>
                    <a href="view_results.php">
                        <span class="icon"><ion-icon name="newspaper-outline"></ion-icon></span>
                        <span class="title">Hasil Jawaban</span>
                    </a>
                </li>
                <li>        
                    <a href="admin_setting.php">
                        <span class="icon"><ion-icon name="settings-outline"></ion-icon></span>
                        <span class="title">Setting Ujian</span>
                    </a>
                </li>
                <li>
                    <a href="logout.php">
                        <span class="icon"><ion-icon name="exit-outline"></ion-icon></span>
                        <span class="title">Logout</span>
                    </a>
                </li>
            </ul>
        </div>
        <script src="../js/main.js"></script>
    <script
      type="module"
      src="https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.esm.js"
    ></script>
    <script
      nomodule
      src="https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.js"
    ></script>
</body>
</html>
