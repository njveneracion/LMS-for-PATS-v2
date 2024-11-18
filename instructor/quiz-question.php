<?php
$toastMessageSuccess = "";
$courseID = $_GET['courseID'];
if (isset($_GET['quizContent']) && isset($_GET['quizName'])) {
    $quizID = $_GET['quizContent']; 
    $quizName = $_GET['quizName']; 


     // Handle question deletion
     if (isset($_POST['delete_question'])) {
        $questionID = mysqli_real_escape_string($connect, $_POST['question_id']);
        $sqlDeleteQuestion = "DELETE FROM quiz_questions WHERE question_id = '$questionID'";
        if (mysqli_query($connect, $sqlDeleteQuestion)) {
            logActivity($_SESSION['userID'], "Deleted a question from the quiz", $quizName);
            $toastMessageSuccess = "Question deleted successfully";
        } else {
            $toastMessageSuccess = "Error deleting question: " . mysqli_error($connect);
        }
    }

    // Handle question editing
    if (isset($_POST['edit_question'])) {
        $questionID = mysqli_real_escape_string($connect, $_POST['question_id']);
        $questionName = mysqli_real_escape_string($connect, $_POST['question_name']);
        $optionA = mysqli_real_escape_string($connect, $_POST['option_a']);
        $optionB = mysqli_real_escape_string($connect, $_POST['option_b']);
        $optionC = mysqli_real_escape_string($connect, $_POST['option_c']);
        $optionD = mysqli_real_escape_string($connect, $_POST['option_d']);
        $correctOption = mysqli_real_escape_string($connect, $_POST['correct_option']);

        $sqlUpdateQuestion = "UPDATE quiz_questions SET 
                              question_name = '$questionName', 
                              option_a = '$optionA', 
                              option_b = '$optionB', 
                              option_c = '$optionC', 
                              option_d = '$optionD', 
                              correct_option = '$correctOption' 
                              WHERE question_id = '$questionID'";
        
        if (mysqli_query($connect, $sqlUpdateQuestion)) {
            logActivity($_SESSION['userID'], "Updated a question from the quiz", $quizName);
            $toastMessageSuccess = "Question updated successfully";
        } else {
            $toastMessageSuccess = "Error updating question: " . mysqli_error($connect);
        }
    }

    
    
    if (isset($_POST['add_question'])) {
        // Sanitize and validate input data
        $questionName = mysqli_real_escape_string($connect, $_POST['question_name']);
        $optionA = mysqli_real_escape_string($connect, $_POST['option_a']);
        $optionB = mysqli_real_escape_string($connect, $_POST['option_b']);
        $optionC = mysqli_real_escape_string($connect, $_POST['option_c']);
        $optionD = mysqli_real_escape_string($connect, $_POST['option_d']);
        $correctOption = mysqli_real_escape_string($connect, $_POST['correct_option']);
        $quizID = mysqli_real_escape_string($connect, $_POST['quiz_id']);
        
        // Validate required fields
        if (empty($questionName) || empty($optionA) || empty($optionB) || empty($optionC) || empty($optionD) || empty($correctOption) || empty($quizID)) {
            die("All fields are required.");
        }
    
        // Insert question into the database
        $sqlAddQuestion = "INSERT INTO quiz_questions (quiz_id, question_name, option_a, option_b, option_c, option_d, correct_option) 
                           VALUES ('$quizID', '$questionName', '$optionA', '$optionB', '$optionC', '$optionD', '$correctOption')";
        $resultAddQuestion = mysqli_query($connect, $sqlAddQuestion);
    
        // Check if the insertion was successful
        if ($resultAddQuestion) {
            logActivity($_SESSION['userID'], "Added a question to the quiz", $quizName);
            echo '<div class="alert alert-success alert-dismissible fade show " role="alert">
                <strong>Success!</strong> Question added successfully.
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>';
        } else {
            echo '<div class="alert alert-danger alert-dismissible fade show " role="alert">
                <strong>Error!</strong> Failed to add question: ' . mysqli_error($connect) . '
                 <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>';
        }
    }
    
    ?>

    <style>
        .btn-primary, .bg-primary {
        background-color: #0f6fc5 !important;
        border-color: #0f6fc5 !important;
        }
        .btn-outline-primary {
            color: #0f6fc5 !important;
            border-color: #0f6fc5 !important;
        }
        .btn-outline-primary:hover {
            background-color: #0f6fc5 !important;
            color: #ffffff !important;
        }
        .text-primary {
            color: #0f6fc5 !important;
        }
    </style>

    <!--Table for quiz questions -->
    <div class="container-fluid mt-4">
        <a href="main.php?page=course-content&viewContent=<?= $courseID; ?> " class="button-primary text-decoration-none text-center p-2 rounded"><i class="fa-solid fa-arrow-left-long"></i> Go back</a>
        <h2 class="mb-4 fs-4 mt-3">Quiz Questions: <span class="text-primary"><?= htmlspecialchars($quizName); ?></span></h2>
        <div class="card shadow-sm">
            <div class="card-body table-responsive">
                <table class="table table-hover">
                    <thead class="table-primary">
                        <tr>
                            <th>Question</th>
                            <th>Options</th>
                            <th>Correct</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $qNum = 0;
                        $sqlQuizQuestion = "SELECT * FROM quiz_questions WHERE quiz_id = '$quizID'";
                        $resultQuizQuestion = mysqli_query($connect, $sqlQuizQuestion);

                        while ($row = mysqli_fetch_assoc($resultQuizQuestion)) {
                            $qNum++;
                        ?>
                            <tr>
                                <td class="text-break"><?= $qNum; ?>. <?= htmlspecialchars($row['question_name']); ?></td>
                                <td>
                                    <small class="d-block text-break">
                                        A: <?= htmlspecialchars($row['option_a']); ?><br>
                                        B: <?= htmlspecialchars($row['option_b']); ?><br>
                                        C: <?= htmlspecialchars($row['option_c']); ?><br>
                                        D: <?= htmlspecialchars($row['option_d']); ?>
                                    </small>
                                </td>
                                <td><?= $row['correct_option']; ?></td>
                                <td>
                                    <div class="btn-group" role="group">
                                        <button type="button" class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#editQuestionModal<?= $row['question_id']; ?>">
                                            <i class="fa-solid fa-pen-to-square"></i>
                                        </button>
                                        <form method="POST" onsubmit="return confirm('Are you sure you want to delete this question?');">
                                            <input type="hidden" name="question_id" value="<?= $row['question_id']; ?>">
                                            <button type="submit" name="delete_question" class="btn btn-sm btn-outline-danger">
                                                <i class="fa-solid fa-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>

                             <!-- Edit Question Modal -->
                             <div class="modal fade" id="editQuestionModal<?= $row['question_id']; ?>" tabindex="-1" aria-labelledby="editQuestionModalLabel<?= $row['question_id']; ?>" aria-hidden="true">
                                <div class="modal-dialog">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h5 class="modal-title" id="editQuestionModalLabel<?= $row['question_id']; ?>">Edit Question</h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                        </div>
                                        <div class="modal-body">
                                            <form method="POST">
                                                <input type="hidden" name="question_id" value="<?= $row['question_id']; ?>">
                                                <div class="mb-3">
                                                    <label for="question_name<?= $row['question_id']; ?>" class="form-label">Question:</label>
                                                    <textarea name="question_name" id="question_name<?= $row['question_id']; ?>" class="form-control" rows="3" required><?= htmlspecialchars($row['question_name']); ?></textarea>
                                                </div>
                                                <div class="mb-3">
                                                    <label for="option_a<?= $row['question_id']; ?>" class="form-label">Option A:</label>
                                                    <input type="text" name="option_a" id="option_a<?= $row['question_id']; ?>" class="form-control" value="<?= htmlspecialchars($row['option_a']); ?>" required>
                                                </div>
                                                <div class="mb-3">
                                                    <label for="option_b<?= $row['question_id']; ?>" class="form-label">Option B:</label>
                                                    <input type="text" name="option_b" id="option_b<?= $row['question_id']; ?>" class="form-control" value="<?= htmlspecialchars($row['option_b']); ?>" required>
                                                </div>
                                                <div class="mb-3">
                                                    <label for="option_c<?= $row['question_id']; ?>" class="form-label">Option C:</label>
                                                    <input type="text" name="option_c" id="option_c<?= $row['question_id']; ?>" class="form-control" value="<?= htmlspecialchars($row['option_c']); ?>" required>
                                                </div>
                                                <div class="mb-3"></div>
                                                    <label for="option_d<?= $row['question_id']; ?>" class="form-label">Option D:</label>
                                                    <input type="text" name="option_d" id="option_d<?= $row['question_id']; ?>" class="form-control" value="<?= htmlspecialchars($row['option_d']); ?>" required>
                                                </div>
                                                <div class="mb-3 p-3">
                                                    <label for="correct_option<?= $row['question_id']; ?>" class="form-label">Correct Option:</label>
                                                    <select name="correct_option" id="correct_option<?= $row['question_id']; ?>" class="form-select">
                                                        <option value="A" <?= $row['correct_option'] == 'A' ? 'selected' : ''; ?>>A</option>
                                                        <option value="B" <?= $row['correct_option'] == 'B' ? 'selected' : ''; ?>>B</option>
                                                        <option value="C" <?= $row['correct_option'] == 'C' ? 'selected' : ''; ?>>C</option>
                                                        <option value="D" <?= $row['correct_option'] == 'D' ? 'selected' : ''; ?>>D</option>
                                                    </select>
                                                    <button type="submit" name="edit_question" class="btn btn-primary mt-3 w-100">Save Changes</button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Form to add a question to the quiz -->
    <div class="container-fluid mt-4 mb-5">
        <div class="card shadow-sm">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0 fs-5">Add a New Question</h5>
            </div>
            <div class="card-body">
                <form method="post">
                    <!-- Question Textarea -->
                    <div class="mb-3">
                        <label for="question_name" class="form-label">Question:</label>
                        <textarea name="question_name" id="question_name" class="form-control" rows="3" required></textarea>
                    </div>
                    
                    <!-- Options -->
                    <div class="row">
                        <div class="col-12 col-md-6 mb-3">
                            <label for="option_a" class="form-label">Option A:</label>
                            <input type="text" name="option_a" id="option_a" class="form-control" required>
                        </div>
                        <div class="col-12 col-md-6 mb-3">
                            <label for="option_b" class="form-label">Option B:</label>
                            <input type="text" name="option_b" id="option_b" class="form-control" required>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-12 col-md-6 mb-3">
                            <label for="option_c" class="form-label">Option C:</label>
                            <input type="text" name="option_c" id="option_c" class="form-control" required>
                        </div>
                        <div class="col-12 col-md-6 mb-3">
                            <label for="option_d" class="form-label">Option D:</label>
                            <input type="text" name="option_d" id="option_d" class="form-control" required>
                        </div>
                    </div>
                    
                    <!-- Correct Option Select -->
                    <div class="mb-3">
                        <label for="correct_option" class="form-label">Correct Option:</label>
                        <select name="correct_option" id="correct_option" class="form-select">
                            <option value="A">A</option>
                            <option value="B">B</option>
                            <option value="C">C</option>
                            <option value="D">D</option>
                        </select>
                    </div>
                    
                    <!-- Hidden Field for Quiz ID -->
                    <input type="hidden" name="quiz_id" value="<?= htmlspecialchars($quizID); ?>">
                    
                    <!-- Submit Button -->
                    <div class="d-grid">
                        <button type="submit" name="add_question" class="btn btn-primary">Add Question</button>
                    </div>
                </form>
            </div>
        </div>
    </div>   
<?php
}

// Display toast message
if (!empty($toastMessageSuccess)) {
    echo "<script>
        document.addEventListener('DOMContentLoaded', function() {
            var toast = new bootstrap.Toast(document.getElementById('successToast'));
            document.getElementById('toastMessage').innerText = '$toastMessageSuccess';
            toast.show();
        });
    </script>";
}
?>

<!-- Toast for success message -->
<div class="position-fixed bottom-0 end-0 p-3" style="z-index: 11">
    <div id="successToast" class="toast" role="alert" aria-live="assertive" aria-atomic="true">
        <div class="toast-header bg-success text-white">
            <strong class="me-auto">Success</strong>
            <button type="button" class="btn-close" data-bs-dismiss="toast" aria-label="Close"></button>
        </div>
        <div class="toast-body" id="toastMessage"></div>
    </div>
</div>




