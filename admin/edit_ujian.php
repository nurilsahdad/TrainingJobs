<?php
session_start();
include '../config/database.php';

if ($_SESSION['role'] !== 'admin') {
    header('Location: ../login.php');
    exit();
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tambah Data Ujian</title>
    <link rel="stylesheet" href="../css/styleadmin.css">
</head>
<body>
    <div class="container">
        <div class="main">
            <div class="topbar">
                <div class="toggle">
                    <ion-icon name="menu-outline"></ion-icon>
                </div>
            </div>
            <div class="contain">
            <h2>Edit Data Ujian</h2>
                <form method="POST">
                    <input type="hidden" name="id" value="<?= $row['id'] ?>"> <!-- Pastikan ID terbawa dengan benar -->
                    <label>Kode Ujian:</label>
                    <input type="text" name="kodeujian" value="<?= htmlspecialchars($row['kodeujian']) ?>" required>
                    <br>
                    <label>Timer (menit):</label>
                    <input type="number" name="timer" value="<?= $row['timer'] / 60 ?>" required>
                    <br>
                    <label>Kejuruan:</label>
                    <input type="text" name="kejuruan" value="<?= htmlspecialchars($row['kejuruan']) ?>" required>
                    <br>
                    <button type="submit">Simpan</button>
                </form>
            </div>
        </div>
    </div>
    <div class="navigation">
            <ul>
                <li>
                    <a href="#">
                    <span class="icon"><img src="../img/logo-karawang.png" alt=""></span>
                    <span class="title" style="font-size:20px; margin-top:10px;">DISNAKERTRANS</span>
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
                    <a href="../logout.php">
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
