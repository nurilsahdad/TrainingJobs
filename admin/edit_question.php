<?php
include '../config/database.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $question_id = (int) $_POST['id'];
    $question_text = mysqli_real_escape_string($conn, $_POST['question_text']);
    $choices = $_POST['choices'];
    $correct_answer = strtoupper($_POST['correct_answer']);

    // Update pertanyaan
    $conn->query("UPDATE questions SET question_text = '$question_text' WHERE id = $question_id");

    // Update pilihan jawaban
    foreach ($choices as $index => $choice_text) {
        $is_correct = ($index === array_search($correct_answer, ['A', 'B', 'C', 'D'])) ? 1 : 0;
        $choice_text = mysqli_real_escape_string($conn, $choice_text);

        $conn->query("UPDATE choices SET choice_text = '$choice_text', is_correct = $is_correct WHERE question_id = $question_id AND id = (SELECT id FROM choices WHERE question_id = $question_id LIMIT 1 OFFSET $index)");
    }

    echo json_encode(['status' => 'success']);
}
?>
