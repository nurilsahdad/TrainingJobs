<?php
session_start();
include '../config/database.php';

// Pastikan hanya admin yang bisa mengakses
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../login.php');
    exit();
}

// Set zona waktu ke WIB
date_default_timezone_set('Asia/Jakarta');

// Tambahkan filter kejuruan jika ada
$filterKejuruan = isset($_GET['kejuruan']) ? $_GET['kejuruan'] : '';

$sql = "
    SELECT 
        ua.user_name, 
        u.kejuruan, 
        CONVERT_TZ(MAX(ua.tanggal_pengerjaan), '+00:00', '+06:00') AS last_attempt, 
        (SUM(CASE WHEN c.is_correct = 1 THEN 1 ELSE 0 END) / 
        (SELECT COUNT(*) FROM questions WHERE ujian_id = u.id)) * 100 AS score
    FROM user_answers ua
    JOIN questions q ON ua.question_id = q.id
    JOIN ujian u ON q.ujian_id = u.id
    JOIN choices c ON ua.answer = c.id
";

// Filter jika ada kejuruan yang dipilih
if (!empty($filterKejuruan)) {
    $sql .= " WHERE u.kejuruan = '$filterKejuruan'";
}

$sql .= " GROUP BY ua.user_name, u.kejuruan
          ORDER BY ua.user_name ASC";

$result = $conn->query($sql);

// Header untuk file Excel
header("Content-Type: application/vnd.ms-excel");
header("Content-Disposition: attachment; filename=hasil_ujian_" . date("Y-m-d") . ".xls");

// Header tabel untuk file Excel
echo "Nama\tKejuruan\tTanggal / Jam (WIB)\tNilai\n";

// Iterasi hasil query dan tulis ke Excel
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        // Konversi waktu ke WIB
        $lastAttempt = new DateTime($row['last_attempt']);
        $lastAttempt->setTimezone(new DateTimeZone('Asia/Jakarta'));
        $lastAttemptFormatted = $lastAttempt->format('Y-m-d H:i:s');

        // Tulis data ke Excel
        echo htmlspecialchars($row['user_name']) . "\t" . 
             htmlspecialchars($row['kejuruan']) . "\t" . 
             htmlspecialchars($lastAttemptFormatted) . "\t" . 
             number_format($row['score'], 2) . "\n";
    }
}

exit();
?>
