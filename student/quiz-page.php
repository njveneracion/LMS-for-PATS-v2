<?php
include 'quiz-result.php';
if (isset($_GET['quizID']) && isset($_GET['courseID'])) {
    $quizID = mysqli_real_escape_string($connect, $_GET['quizID']);
    $courseID = mysqli_real_escape_string($connect, $_GET['courseID']);
    $studentID = $_SESSION['userID'];

    // Fetch quiz details
    $sqlQuizDetails = "SELECT * FROM quiz WHERE quiz_id = '$quizID' LIMIT 1";
    $resultQuizDetails = mysqli_query($connect, $sqlQuizDetails);

    // Fetch quiz questions
    $sqlQuizQuestions = "SELECT * FROM quiz_questions WHERE quiz_id = '$quizID'";
    $resultQuizQuestions = mysqli_query($connect, $sqlQuizQuestions);

    

    if ($rowQuiz = mysqli_fetch_assoc($resultQuizDetails)) {
        $quizTitle = htmlspecialchars($rowQuiz['quiz_name']);

        // Handle quiz submission
        if (isset($_POST['submit-quiz'])) {
            $answers = [];

            while ($rowQuestion = mysqli_fetch_assoc($resultQuizQuestions)) {
                $questionID = $rowQuestion['question_id'];
                $correctAnswer = htmlspecialchars($rowQuestion['correct_option']);
                $selectedOption = isset($_POST["question_$questionID"]) ? mysqli_real_escape_string($connect, $_POST["question_$questionID"]) : '';
                     // Map the selected option to a letter
                $selectedOptionLetter = '';
                switch ($selectedOption) {
                    case $rowQuestion['option_a']:
                        $selectedOptionLetter = 'A';
                        break;
                    case $rowQuestion['option_b']:
                        $selectedOptionLetter = 'B';
                        break;
                    case $rowQuestion['option_c']:
                        $selectedOptionLetter = 'C';
                        break;
                    case $rowQuestion['option_d']:
                        $selectedOptionLetter = 'D';
                        break;
                }

                // Store the student's answer in the database
                $answers[] = "('$studentID', '$quizID', '$questionID', '$selectedOptionLetter', '$correctAnswer')";
            }

            if (!empty($answers)) {
                $sqlInsertAnswers = "INSERT INTO student_answers (student_id, quiz_id, question_id, student_answer, correct_answer) VALUES " . implode(', ', $answers);
                mysqli_query($connect, $sqlInsertAnswers);
            }

            displayQuizResult($quizID, $courseID, $studentID, $connect);
            exit();
        }
        ?>
        <div class="quiz-wrapper">
            <h1 class="quiz-title"><?= $quizTitle; ?></h1>
            <form method="post">
                <?php
                $totalQuestions = mysqli_num_rows($resultQuizQuestions);
                $questionIndex = 1;

                while ($rowQuestion = mysqli_fetch_assoc($resultQuizQuestions)) {
                    $questionID = $rowQuestion['question_id'];
                    $questionName = htmlspecialchars($rowQuestion['question_name']);
                    $optionA = htmlspecialchars($rowQuestion['option_a']);
                    $optionB = htmlspecialchars($rowQuestion['option_b']);
                    $optionC = htmlspecialchars($rowQuestion['option_c']);
                    $optionD = htmlspecialchars($rowQuestion['option_d']);
                    ?>

                    <div class="quiz-question" data-question-index="<?= $questionIndex; ?>">
                        <p><strong>Question <?= $questionIndex; ?>:</strong> <?= $questionName; ?></p>
                        <div>
                            <label><input type="radio" name="question_<?= $questionID; ?>" value="<?= $optionA; ?>" required> <?= $optionA; ?></label>
                        </div>
                        <div>
                            <label><input type="radio" name="question_<?= $questionID; ?>" value="<?= $optionB; ?>" required> <?= $optionB; ?></label>
                        </div>
                        <div>
                            <label><input type="radio" name="question_<?= $questionID; ?>" value="<?= $optionC; ?>" required> <?= $optionC; ?></label>
                        </div>
                        <div>
                            <label><input type="radio" name="question_<?= $questionID; ?>" value="<?= $optionD; ?>" required> <?= $optionD; ?></label>
                        </div>
                    </div>
                    <hr>
                    <?php
                    $questionIndex++;
                }
                ?>
                <input type="submit" name="submit-quiz" value="Submit Quiz" class="btn btn-primary form-control">
                <div class="progress-bar" style="height: 20px">
                    <div class="progress-bar-fill bg-primary"></div>
                    <div class="progress-bar-text">0%</div>
                </div>
            </form>
        </div>
        <?php
    } else {
        echo "<p style='text-align: center;'>Quiz not found.</p>";
    }
} else {
    echo "<p style='text-align: center;'>Invalid request.</p>";
}
?>

<script>
            document.addEventListener('DOMContentLoaded', function () {
                const totalQuestions = <?= $totalQuestions; ?>;
                const progressBarFill = document.querySelector('.progress-bar-fill');
                const progressBarText = document.querySelector('.progress-bar-text');
                const questionDivs = document.querySelectorAll('.quiz-question');

                function updateProgressBar() {
                    let answeredQuestions = 0;

                    questionDivs.forEach(div => {
                        const inputs = div.querySelectorAll('input[type="radio"]');
                        inputs.forEach(input => {
                            if (input.checked) {
                                answeredQuestions++;
                            }
                        });
                    });

                    const progress = (answeredQuestions / totalQuestions) * 100;
                    progressBarFill.style.width = progress + '%';
                    progressBarText.textContent = Math.round(progress) + '%';
                }

                questionDivs.forEach(div => {
                    const inputs = div.querySelectorAll('input[type="radio"]');
                    inputs.forEach(input => {
                        input.addEventListener('change', updateProgressBar);
                    });
                });
            });
</script>


<style>
.quiz-wrapper {
    max-width: 800px;
    margin: 20px auto;
    background-color: #f8f9fa;
    border-radius: 10px;
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
    padding: 20px;
}
.quiz-title {
    text-align: center;
    font-size: 24px;
    font-weight: bold;
    margin-bottom: 20px;
    color: #343a40;
}
.quiz-question {
    margin-bottom: 20px;
}
.quiz-question p {
    font-size: 18px;
    font-weight: 500;
    color: #495057;
}
.quiz-question div {
    margin-bottom: 10px;
}
.quiz-question input[type="radio"] {
    margin-right: 10px;
}
.quiz-question label {
    font-size: 16px;
    color: #6c757d;
    cursor: pointer;
}


.btn-primary {
    display: block;
    width: 100%;
    padding: 10px;
    background-color: #007bff;
    border: none;
    border-radius: 5px;
    color: white;
    font-size: 16px;
    cursor: pointer;
}
.btn-primary:hover {
    background-color: #0056b3;
}
.progress-bar {
    margin-top: 20px;
    background-color: #e9ecef;
    border-radius: 10px;
    height: 20px;
    width: 100%;
    position: relative;
    text-align: center;
    color: white;
    font-weight: bold;
    line-height: 20px; /* Matches the height of the progress bar */
}
.progress-bar-fill {
    background-color: #007bff;
    height: 100%;
    width: 0;
    border-radius: 10px;
    transition: width 0.4s ease-in-out;
    position: relative;
    z-index: 1;
}
.progress-bar-text {
    position: absolute;
    width: 100%;
    z-index: 2;
    left: 0;
    top: 0;
}
</style>
