<?php
session_start();
include '../config/database.php';

header('Content-Type: application/json');

// Pastikan pengguna adalah admin
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    echo json_encode(['success' => false, 'message' => 'Akses ditolak.']);
    exit();
}

// Pastikan parameter ID dikirimkan
if (!isset($_GET['id'])) {
    echo json_encode(['success' => false, 'message' => 'ID tidak ditemukan.']);
    exit();
}

$id = intval($_GET['id']);

// Hapus data ujian berdasarkan ID
$query = $conn->prepare("DELETE FROM ujian WHERE id = ?");
$query->bind_param('i', $id);

if ($query->execute()) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'message' => 'Gagal menghapus data.']);
}
?>
