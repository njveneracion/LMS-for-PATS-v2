<div class="container-fluid">
    <h1 class="text-center mb-4">Course Quizzes</h1>
    <hr>
    
    <?php
    if (isset($_GET['page']) && $_GET['page'] == 'quiz') {
        $userID = $_SESSION['userID'];
        $sqlViewCourses = "SELECT * FROM courses WHERE user_id = '$userID'";
        $resultViewCourses = mysqli_query($connect, $sqlViewCourses);
        ?>
        <div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 g-4">
            <?php
            while ($row = mysqli_fetch_assoc($resultViewCourses)) {
                $courseID = $row['course_id'];
                $courseName = $row['course_name'];
                $courseImg = $row['course_img'];
                ?>
                <div class="col">
                    <div class="card h-100 shadow-sm">
                        <img src="<?= $courseImg; ?>" class="card-img-top" alt="<?= $courseName; ?>" style="height: 200px; object-fit: cover;">
                        <div class="card-body">
                            <h5 class="card-title"><?= $courseName; ?></h5>
                            <button class="btn btn-primary mt-2" type="button" data-bs-toggle="collapse" data-bs-target="#quizCollapse<?= $courseID ?>" aria-expanded="false" aria-controls="quizCollapse<?= $courseID ?>">
                                View Quizzes
                            </button>
                        </div>
                        <div class="collapse" id="quizCollapse<?= $courseID ?>">
                            <div class="card-footer bg-transparent">
                                <?php
                                $sqlShowQuiz = "SELECT * FROM quiz WHERE course_id = '$courseID'";
                                $resultShowQuiz = mysqli_query($connect, $sqlShowQuiz);
                        
                                if (mysqli_num_rows($resultShowQuiz) > 0) {
                                    ?>
                                    <div class="accordion" id="quizAccordion<?= $courseID ?>">
                                        <?php
                                        $quizCount = 0;
                                        while ($quizRow = mysqli_fetch_assoc($resultShowQuiz)) {
                                            $quizID = $quizRow['quiz_id'];
                                            $quizName = $quizRow['quiz_name'];
                                            $quizCount++;
                                            ?>
                                            <div class="accordion-item">
                                                <h2 class="accordion-header" id="heading<?= $quizID ?>">
                                                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapse<?= $quizID ?>" aria-expanded="false" aria-controls="collapse<?= $quizID ?>">
                                                        <?= $quizName; ?>
                                                    </button>
                                                </h2>
                                                <div id="collapse<?= $quizID ?>" class="accordion-collapse collapse" aria-labelledby="heading<?= $quizID ?>" data-bs-parent="#quizAccordion<?= $courseID ?>">
                                                    <div class="accordion-body">
                                                        <a href="main.php?page=quiz-question&courseID=<?= $courseID; ?>&quizContent=<?= $quizID; ?>&quizName=<?= $quizName; ?>" class="btn btn-outline-primary btn-sm">
                                                            View Quiz Details
                                                        </a>
                                                    </div>
                                                </div>
                                            </div>
                                            <?php
                                        }
                                        ?>
                                    </div>
                                    <?php
                                } else {
                                    echo "<p class='text-muted'>No quizzes available for this course.</p>";
                                }
                                ?>
                            </div>
                        </div>
                    </div>
                </div>
                <?php
            }
            ?>
        </div>
        <?php
    }
    ?> 
</div>

<style>
    .card {
        transition: transform 0.2s;
    }
    .card:hover {
        transform: translateY(-5px);
    }
    .accordion-button:not(.collapsed) {
        background-color: #e7f1ff;
        color: #0c63e4;
    }

    @media (max-width: 768px) {
        .card-body > button {
            width: 100%;
        }

        .accordion-body > a {
            width: 100%;
        }
    }
</style>
