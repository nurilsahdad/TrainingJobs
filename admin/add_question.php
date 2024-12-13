<?php
session_start();
include '../config/database.php';
include '../vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\IOFactory;

if ($_SESSION['role'] !== 'admin') {
    header('Location: ../login.php');
    exit();
}

// hapus semua soal
if (isset($_POST['delete_all'])) {
    $kejuruan_id = isset($_POST['kejuruan_id']) ? (int)$_POST['kejuruan_id'] : null;

    if ($kejuruan_id) {
        // Hapus data soal berdasarkan kejuruan yang dipilih
        $question_ids = $conn->query("SELECT id FROM questions WHERE ujian_id = $kejuruan_id");

        if ($question_ids->num_rows > 0) {
            $ids = [];
            while ($row = $question_ids->fetch_assoc()) {
                $ids[] = $row['id'];
            }

            $id_list = implode(',', $ids);

            // Hapus jawaban pengguna, jawaban, soal berdasarkan jurusan agar tidak rusak
            $conn->query("DELETE FROM user_answers WHERE question_id IN ($id_list)");
            $conn->query("DELETE FROM choices WHERE question_id IN ($id_list)");
            $conn->query("DELETE FROM questions WHERE id IN ($id_list)");
        }
    }

    header('Location: add_question.php');
    exit();
}

// Tambah soal spreadsheet
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_FILES['excel_file'], $_POST['ujian_id'])) {
    $file = $_FILES['excel_file']['tmp_name'];
    $ujian_id = (int)$_POST['ujian_id'];

    $spreadsheet = IOFactory::load($file);
    $sheetData = $spreadsheet->getActiveSheet()->toArray();

    foreach ($sheetData as $index => $row) {
        if ($index == 0) continue;

        $question_text = mysqli_real_escape_string($conn, $row[0]);
        $choices = array_map(function ($choice) use ($conn) {
            return mysqli_real_escape_string($conn, $choice);
        }, array_slice($row, 1, 4));
        $correct_answer_letter = strtoupper($row[5]);

        $correct_answer_index = null;
        switch ($correct_answer_letter) {
            case 'A': $correct_answer_index = 0; break;
            case 'B': $correct_answer_index = 1; break;
            case 'C': $correct_answer_index = 2; break;
            case 'D': $correct_answer_index = 3; break;
        }

        if (is_null($correct_answer_index)) continue;

        $conn->query("INSERT INTO questions (question_text, ujian_id) VALUES ('$question_text', $ujian_id)");
        $question_id = $conn->insert_id;

        foreach ($choices as $choice_index => $choice_text) {
            $is_correct = ($choice_index == $correct_answer_index) ? 1 : 0;
            $conn->query("INSERT INTO choices (question_id, choice_text, is_correct) VALUES ($question_id, '$choice_text', $is_correct)");
        }
    }
    header('Location: add_question.php');
    exit();
}

$kejuruan_id = isset($_GET['kejuruan_id']) ? (int)$_GET['kejuruan_id'] : null;

if ($kejuruan_id) {
    $questions = $conn->query("SELECT * FROM questions WHERE ujian_id = $kejuruan_id");
} else {
    $questions = null;
}

$ujian_data = $conn->query("SELECT * FROM ujian");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tambah Soal</title>
    <style>
        table, th, td {
            border-collapse: collapse;
        }
        th, td {
            padding: 10px;
        }
        .table-grid{
            grid-template-columns: 40px 500px 100px 100px 100px 100px 2fr 2fr;
        }
        .table-cell[title] {
            position: relative;
            cursor: pointer;
        }
        .table-cell[title]:hover::after {
            content: attr(title);
            position: absolute;
            top: 100%;
            left: 0;
            background: #333;
            color: #fff;
            padding: 5px 10px;
            font-size: 12px;
            white-space: nowrap;
            z-index: 10;
            border-radius: 5px;
            box-shadow: 0px 2px 5px rgba(0, 0, 0, 0.2);
        }
        textarea {
            width:300px;
        }
    </style>
    <link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" href="../css/styleadmin.css">
    <script src="
    https://cdn.jsdelivr.net/npm/sweetalert2@11.14.5/dist/sweetalert2.all.min.js
    "></script>
    <link href="
    https://cdn.jsdelivr.net/npm/sweetalert2@11.14.5/dist/sweetalert2.min.css
    " rel="stylesheet">
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
            <h2 style="padding-top:10px; padding-left: 10px;">Bank Soal</h2>
            <div class="action-container" >
            <button onclick="tambahSoal()" class="action-btn" >Tambah Soal</button>   
                <form method="POST">
                <input type="hidden" name="kejuruan_id" value="<?= isset($_GET['kejuruan_id']) ? (int)$_GET['kejuruan_id'] : '' ?>">
                <button type="submit" class="action-btn" name="delete_all" onclick="return confirm('Apakah Anda yakin ingin menghapus semua soal pada kejuruan ini?')">Hapus Soal</button>
            </form>
            <script>
                function tambahSoal(){
                    Swal.fire({
                        title: "<strong>Tambah Soal</strong>",
                        html: `
                            <form method="POST" enctype="multipart/form-data">
                            <label for="kejuruan">Kejuruan:</label>
                            <select name="ujian_id" id="kejuruan" required> 
                                <option value="">-</option>
                                <?php while ($ujian = $ujian_data->fetch_assoc()): ?>
                                    <option value="<?= $ujian['id'] ?>"><?= $ujian['kejuruan'] ?></option>
                                <?php endwhile; ?>
                            </select>
                            <br><br>
                            <label for="excel_file">File xls/xlsx:</label>
                            <input type="file" name="excel_file" accept=".xls,.xlsx" required>
                            <input type="submit" class="action-btn" value="Unggah xls/xlsx">
                        </form>
                        <br>
                        <a href="template/template_soal.xlsx" download>Download Template Excel</a>
                        `,
                        showConfirmButton:false,
                        });
                }
            </script>
                <form method="GET">
                    <select name="kejuruan_id" id="filter_kejuruan" onchange="this.form.submit()" class="filter_kejuruan">
                        <option value="">-</option>
                        <?php
                        $ujian_data = $conn->query("SELECT * FROM ujian");
                        while ($ujian = $ujian_data->fetch_assoc()):
                        ?>
                            <option value="<?= $ujian['id'] ?>" <?= isset($_GET['kejuruan_id']) && $_GET['kejuruan_id'] == $ujian['id'] ? 'selected' : '' ?>>
                                <?= $ujian['kejuruan'] ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                </form> 
                </div>
                
                <div style="overflow-x:auto; height:auto; margin-top: 10px; font-size: 12px;">
                    <div class="table-grid">
                        <div class="table-header">No</div>
                        <div class="table-header">Soal</div>
                        <div class="table-header">Jawaban A</div>
                        <div class="table-header">Jawaban B</div>
                        <div class="table-header">Jawaban C</div>
                        <div class="table-header">Jawaban D</div>
                        <div class="table-header">Kunci Jawaban</div>
                        <div class="table-header">Aksi</div>

                        <?php
                        if ($questions && $questions->num_rows > 0) {
                            $no = 1;
                            while ($question = $questions->fetch_assoc()) {
                                $choices = $conn->query("SELECT * FROM choices WHERE question_id = " . $question['id']);
                                $choices_array = [];
                                $correct_choice = null;

                                while ($choice = $choices->fetch_assoc()) {
                                    $choices_array[] = $choice;
                                    if ($choice['is_correct']) {
                                        $correct_choice = count($choices_array); // 1-based index for A, B, C, D
                                    }
                                }

                                echo "<div class='table-cell'>" . $no++ . "</div>";
                                echo "<div class='table-cell' title='" . htmlspecialchars($question['question_text'], ENT_QUOTES) . "'>" . htmlspecialchars(mb_strimwidth($question['question_text'], 0, 100, "..."), ENT_QUOTES) . "</div>";

                                for ($i = 0; $i < 4; $i++) {
                                    echo "<div class='table-cell'>" . ($choices_array[$i]['choice_text'] ?? "-") . "</div>";
                                }

                                // Display correct choice as A/B/C/D
                                echo "<div class='table-cell'>" . ($correct_choice ? chr(64 + $correct_choice) : "-") . "</div>";
                                echo "<div class='table-cell'>";
                                echo "<button onclick=\"edit(
                                    '{$question['id']}', 
                                    '{$question['question_text']}', 
                                    ['" . implode("','", array_column($choices_array, 'choice_text')) . "'], 
                                    '" . ($correct_choice ? chr(64 + $correct_choice) : "-") . "'
                                )\" class='editbtn' ><span class='icon'><ion-icon name='create-outline'></ion-icon></span></button>";
                                echo "<button onclick=\"confirmDelete('{$question['id']}', '{$kejuruan_id}')\" class='trashbtn'><span class='icon'><ion-icon name='trash-outline'></ion-icon></ion-icon></span></a>";
                                echo "</div>";
                            }
                        } else {
                            echo "<div class='table-cell' colspan='8'>Tidak ada data</div>";
                        }
                        ?>
                    </div>
                </div>
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
                <a href="logout.php">
                    <span class="icon"><ion-icon name="exit-outline"></ion-icon></span>
                    <span class="title">Logout</span>
                </a>
            </li>
        </ul>
    </div>
        <script>
            function edit(questionId, questionText, choices, correctAnswer) {
                Swal.fire({
                    title: "<strong>Edit Soal</strong>",
                    html: `
                        <form id="editForm">
                            <textarea id="question_text" name="question_text" required>${questionText}</textarea>
                            <br>
                            <label>A:</label>
                            <input type="text" name="choices[]" value="${choices[0] || ''}" required>
                            <br>
                            <label>B:</label>
                            <input type="text" name="choices[]" value="${choices[1] || ''}" required>
                            <br>
                            <label>C:</label>
                            <input type="text" name="choices[]" value="${choices[2] || ''}" required>
                            <br>
                            <label>D:</label>
                            <input type="text" name="choices[]" value="${choices[3] || ''}" required>
                            <br>
                            <label>Kunci Jawaban:</label>
                            <select name="correct_answer" required>
                                <option value="A" ${correctAnswer === "A" ? "selected" : ""}>A</option>
                                <option value="B" ${correctAnswer === "B" ? "selected" : ""}>B</option>
                                <option value="C" ${correctAnswer === "C" ? "selected" : ""}>C</option>
                                <option value="D" ${correctAnswer === "D" ? "selected" : ""}>D</option>
                            </select>
                            <br><br>
                        </form>
                    `,
                    showCancelButton: true,
                    confirmButtonText: "Simpan",
                    cancelButtonText: "Batal",
                    preConfirm: () => {
                        const formData = new FormData(document.getElementById("editForm"));
                        formData.append("id", questionId);

                        return fetch("edit_question.php", {
                            method: "POST",
                            body: formData,
                        })
                        .then((response) => {
                            if (!response.ok) {
                                throw new Error(response.statusText);
                            }
                            return response.json();
                        })
                        .catch((error) => {
                            Swal.showValidationMessage(`Gagal menyimpan perubahan: ${error}`);
                        });
                    },
                }).then((result) => {
                    if (result.isConfirmed) {
                        Swal.fire({
                            title: "Berhasil",
                            text: "Soal berhasil diperbarui.",
                            icon: "success",
                        }).then(() => {
                            location.reload();
                        });
                    }
                });
            }

            function confirmDelete(questionId, kejuruanId) {
                Swal.fire({
                    title: "Apakah Anda yakin?",
                    text: "Soal ini akan dihapus secara permanen!",
                    icon: "warning",
                    showCancelButton: true,
                    confirmButtonColor: "#4946a0",
                    cancelButtonColor: "#d33",
                    confirmButtonText: "Ya, Hapus",
                    cancelButtonText: "Batal",
                }).then((result) => {
                    if (result.isConfirmed) {
                        fetch(`delete_question.php?id=${questionId}&kejuruan_id=${kejuruanId}`, {
                            method: "GET",
                        })
                        .then((response) => {
                            if (!response.ok) {
                                throw new Error(response.statusText);
                            }
                            return response.json();
                        })
                        .then(() => {
                            Swal.fire({
                                title: "Berhasil",
                                text: "Soal berhasil dihapus.",
                                icon: "success",
                            }).then(() => {
                                location.reload();
                            });
                        })
                        .catch((error) => {
                            Swal.fire({
                                title: "Gagal",
                                text: `Gagal menghapus soal: ${error}`,
                                icon: "error",
                            });
                        });
                    }
                });
            }
        </script>
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
