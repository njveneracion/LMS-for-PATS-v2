<?php
$toastMessageSuccess = "";
if (isset($_GET['page']) && $_GET['page'] == 'assessments') {
    $userID = $_SESSION['userID'];
    $sqlViewCourses = "SELECT * FROM courses WHERE user_id = '$userID'";
    $resultViewCourses = mysqli_query($connect, $sqlViewCourses);
    ?>
    <div class="container-fluid">
        <h1 class="text-center mb-4">Course Assessments</h1>
        <hr>
        
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
                            <button class="btn btn-primary mt-2" type="button" data-bs-toggle="collapse" data-bs-target="#assessmentCollapse<?= $courseID ?>" aria-expanded="false" aria-controls="assessmentCollapse<?= $courseID ?>">
                                View Assessments
                            </button>
                        </div>
                        <div class="collapse" id="assessmentCollapse<?= $courseID ?>">
                            <div class="card-footer bg-transparent">
                                <?php
                                $sqlShowAssessments = "SELECT * FROM assessments WHERE course_id = '$courseID'";
                                $resultShowAssessments = mysqli_query($connect, $sqlShowAssessments);
                        
                                if (mysqli_num_rows($resultShowAssessments) > 0) {
                                    ?>
                                    <div class="accordion" id="assessmentAccordion<?= $courseID ?>">
                                        <?php
                                        $assessmentCount = 0;
                                        while ($assessmentRow = mysqli_fetch_assoc($resultShowAssessments)) {
                                            $assessmentID = $assessmentRow['assessment_id'];
                                            $assessmentTitle = $assessmentRow['assessment_title'];
                                            $assessmentType = ucfirst($assessmentRow['assessment_type']);
                                            $assessmentCount++;
                                            ?>
                                            <div class="accordion-item">
                                                <h2 class="accordion-header" id="heading<?= $assessmentID ?>">
                                                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapse<?= $assessmentID ?>" aria-expanded="false" aria-controls="collapse<?= $assessmentID ?>">
                                                        <?= $assessmentTitle; ?> (<?= $assessmentType; ?>)
                                                    </button>
                                                </h2>
                                                <div id="collapse<?= $assessmentID ?>" class="accordion-collapse collapse" aria-labelledby="heading<?= $assessmentID ?>" data-bs-parent="#assessmentAccordion<?= $courseID ?>">
                                                    <div class="accordion-body">
                                                        <a href="main.php?page=assessment-question&courseID=<?= $courseID; ?>&assessmentContent=<?= $assessmentID; ?>&assessmentName=<?= urlencode($assessmentTitle); ?>&assessmentType=<?= strtolower($assessmentType); ?>" class="btn btn-outline-primary btn-sm">
                                                            Manage Questions
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
                                    echo "<p class='text-muted'>No assessments available for this course.</p>";
                                }
                                ?>
                                <button class="btn btn-success mt-3" type="button" data-bs-toggle="modal" data-bs-target="#createAssessmentModal<?= $courseID ?>">
                                    Create New Assessment
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Modal for creating new assessment -->
                <div class="modal fade" id="createAssessmentModal<?= $courseID ?>" tabindex="-1" aria-labelledby="createAssessmentModalLabel<?= $courseID ?>" aria-hidden="true">
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title" id="createAssessmentModalLabel<?= $courseID ?>">Create New Assessment for <?= $courseName ?></h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <div class="modal-body">
                                <form method="POST" action="i_main.php?page=i_assessments">
                                    <input type="hidden" name="course_id" value="<?= $courseID ?>">
                                    <div class="mb-3">
                                        <label for="assessment_title<?= $courseID ?>" class="form-label">Assessment Title</label>
                                        <input type="text" class="form-control" id="assessment_title<?= $courseID ?>" name="assessment_title" required>
                                    </div>
                                    <div class="mb-3">
                                        <label for="assessment_description<?= $courseID ?>" class="form-label">Description</label>
                                        <textarea class="form-control" id="assessment_description<?= $courseID ?>" name="assessment_description" rows="3"></textarea>
                                    </div>
                                    <div class="mb-3">
                                        <label for="assessment_type<?= $courseID ?>" class="form-label">Assessment Type</label>
                                        <select class="form-select" id="assessment_type<?= $courseID ?>" name="assessment_type" required>
                                            <option value="pre">Pre-Assessment</option>
                                            <option value="post">Post-Assessment</option>
                                        </select>
                                    </div>
                                    <button type="submit" name="create_assessment" class="btn btn-primary">Create Assessment</button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
                <?php
            }
            ?>
        </div>
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
    <?php
}

// Handle form submission for creating new assessment
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['create_assessment'])) {
    $courseID = mysqli_real_escape_string($connect, $_POST['course_id']);
    $assessmentTitle = mysqli_real_escape_string($connect, $_POST['assessment_title']);
    $assessmentDescription = mysqli_real_escape_string($connect, $_POST['assessment_description']);
    $assessmentType = mysqli_real_escape_string($connect, $_POST['assessment_type']);

    $sqlCreateAssessment = "INSERT INTO assessments (course_id, assessment_title, assessment_description, assessment_type) 
                            VALUES (?, ?, ?, ?)";
    $stmtCreateAssessment = $connect->prepare($sqlCreateAssessment);
    $stmtCreateAssessment->bind_param("isss", $courseID, $assessmentTitle, $assessmentDescription, $assessmentType);
    
    if ($stmtCreateAssessment->execute()) {
        $toastMessageSuccess = "Assessment created successfully.";
    } else {
        $toastMessageSuccess = "Error creating assessment: " . $connect->error;
    }
}
?>
