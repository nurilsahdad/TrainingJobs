<?php
include '../config/database.php';

if (isset($_GET['id'])) {
    $question_id = (int) $_GET['id'];

    $conn->query("DELETE FROM user_answers WHERE question_id = $question_id");
    $conn->query("DELETE FROM choices WHERE question_id = $question_id");
    $conn->query("DELETE FROM questions WHERE id = $question_id");

    echo json_encode(['status' => 'success']);
}
?>
