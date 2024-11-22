
<?php
include '../functions/generateCertificate.php';
include 'course-discussions.php';


if (isset($_GET['viewContent'])) {
    $courseID = mysqli_real_escape_string($connect, $_GET['viewContent']);
    $studentID = $_SESSION['userID'];

    // Fetch course details
    $sqlViewCourses = "SELECT * FROM courses WHERE course_id = '$courseID' LIMIT 1";
    $resultViewCourses = mysqli_query($connect, $sqlViewCourses);
    $rowCourses = mysqli_fetch_assoc($resultViewCourses);

    // Handle completion of content
    if (isset($_POST['complete-btn'])) {
        handleContentCompletion($connect);
    }

    if ($rowCourses) {
        displayCourseContent($connect, $courseID, $studentID, $rowCourses);
    }
}

function handleContentCompletion($connect) {
    $studentID = mysqli_real_escape_string($connect, $_POST['studentID']);
    $courseID = mysqli_real_escape_string($connect, $_POST['courseID']);
    $contentID = mysqli_real_escape_string($connect, $_POST['contentID']);
    $type = mysqli_real_escape_string($connect, $_POST['type']);


    // If it's not a task sheet or if it's a passed task sheet, proceed with marking as completed
    $sqlInsert = "INSERT INTO student_progress (student_id, course_id, content_id, content_type, is_completed, completed_at)
                  VALUES (?, ?, ?, ?, TRUE, NOW())
                  ON DUPLICATE KEY UPDATE is_completed = TRUE, completed_at = NOW()";
    $stmtInsert = mysqli_prepare($connect, $sqlInsert);
    mysqli_stmt_bind_param($stmtInsert, "iiis", $studentID, $courseID, $contentID, $type);
    
    if (mysqli_stmt_execute($stmtInsert)) {
        echo "<script type='text/javascript'>
              alert('Content marked as completed successfully.');
              window.location.href = 'main.php?page=course-content&viewContent=$courseID';
              </script>";
    } else {
        echo "<script type='text/javascript'>
              alert('Error marking content as completed. Please try again.');
              window.location.href = 'main.php?page=course-content&viewContent=$courseID';
              </script>";
    }
    exit();
}

function displayCourseContent($connect, $courseID, $studentID, $course) {
    $isCompleted = checkCourseCompletion($connect, $courseID, $studentID);
    
    // Fetch current enrollment status
    $sqlEnrollmentStatus = "SELECT status FROM enrollments WHERE user_id = '$studentID' AND course_id = '$courseID'";
    $resultEnrollmentStatus = mysqli_query($connect, $sqlEnrollmentStatus);
    $enrollmentStatus = mysqli_fetch_assoc($resultEnrollmentStatus)['status'];
    
    ?>
    <div class="container-fluid">
        <div class="row">
            <!-- Main content area -->
            <div class="col-md-8">
                <?php if ($isCompleted && $enrollmentStatus === 'Completed'): ?>
                    <div class="alert alert-success text-center" role="alert">
                        Congratulations, you completed this course!
                        <img src="../assets/gif/medalnobg.gif" alt="Medal" style="width: 50px; height: 50px;">
                    </div>  
                <?php endif; ?>
                
                <div class="content-display-area" id="content-display-area">
                     <!-- This area will be updated dynamically with JavaScript -->
                    <h2>Welcome to <?= htmlspecialchars($course['course_name']) ?></h2>
                    <p>Click on a lesson in the sidebar to start learning.</p>
                    <hr>
                </div>
                
                <div class="mt-4">
                    <ul class="nav nav-tabs" id="myTab" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active" id="discussion-tab" data-bs-toggle="tab" data-bs-target="#discussion" type="button" role="tab" aria-controls="discussion" aria-selected="true">Discussion</button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="learning-materials-tab" data-bs-toggle="tab" data-bs-target="#learning-materials" type="button" role="tab" aria-controls="learning-materials" aria-selected="false">Learning Materials</button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="announcements-tab" data-bs-toggle="tab" data-bs-target="#announcements" type="button" role="tab" aria-controls="announcements" aria-selected="false">Announcements</button>
                        </li>
                    </ul>
                    <div class="tab-content" id="myTabContent">
                        <div class="tab-pane fade show active" id="discussion" role="tabpanel" aria-labelledby="discussion-tab">
                            <!-- Add your discussion forum content here -->
                            <?php handleDiscussions($connect, $courseID, $studentID); ?>
                        </div>
                        <div class="tab-pane fade" id="learning-materials" role="tabpanel" aria-labelledby="learning-materials-tab">
                            <h3>Learning Materials</h3>
                            <?php displayLearningMaterials($connect, $courseID); ?>
                        </div>
                        <div class="tab-pane fade" id="announcements" role="tabpanel" aria-labelledby="announcements-tab">
                            <h3>Course Announcements</h3>
                            <?php displayAnnouncements($connect, $courseID); ?>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Sidebar with course content -->
            <div class="col-md-4">
                <!-- Add progress bar here -->
                <?php 
                displayCertificateArea($connect, $courseID, $studentID);
                displayProgressBar($connect, $courseID, $studentID); 
                ?>
                <hr>
                <label class="fs-3 fw-semibold mb-3">Lectures</label>
                <div class="content-list">
                <?php
                $sqlCombined = "SELECT * FROM (
                    SELECT material_id AS id, material_title AS title, material_desc AS description,
                        material_file AS file, 'Material' AS type, created_at
                    FROM course_material WHERE course_id = '$courseID'
                    UNION ALL
                    SELECT quiz_id AS id, quiz_name AS title, quiz_description AS description,
                        '' AS file, 'Quiz' AS type, created_at
                    FROM quiz WHERE course_id = '$courseID'
                    UNION ALL
                    SELECT task_sheet_id AS id, task_sheet_title AS title, task_sheet_description AS description,
                        task_sheet_file AS file, 'Task Sheet' AS type, created_at
                    FROM task_sheets WHERE course_id = '$courseID'
                    UNION ALL
                    SELECT assessment_id AS id, assessment_title AS title, assessment_description AS description,
                        '' AS file, CONCAT(assessment_type, '-assessment') AS type, created_at
                    FROM assessments WHERE course_id = '$courseID'
                ) AS combined ORDER BY created_at ASC";

                $resultCombined = mysqli_query($connect, $sqlCombined);
                $prevItemID = 0;

                while ($row = mysqli_fetch_assoc($resultCombined)) {
                    displayContentItem($connect, $row, $courseID, $studentID, $prevItemID);
                    $prevItemID = $row['id'];
                }
                ?>
                </div>
            </div>
        </div>
    </div>
    <?php
}

function displayContentItem($connect, $item, $courseID, $studentID, &$prevItemID) {
    $id = htmlspecialchars($item['id']);
    $title = htmlspecialchars($item['title']);
    $description = htmlspecialchars($item['description']);
    $type = htmlspecialchars($item['type']);
    $file = htmlspecialchars($item['file']);

    $isAccessible = isItemAccessible($connect, $courseID, $studentID, $prevItemID, $id);
    $isCompleted = isItemCompleted($connect, $courseID, $studentID, $id, $type);

    $accessClass = $isAccessible ? 'accessible' : 'locked';
    $completedClass = $isCompleted ? 'completed' : '';
    ?>
    <div class="content-item <?= $accessClass ?> <?= $completedClass ?>">
        <div class="item-header" data-bs-toggle="collapse" data-bs-target="#collapse-<?= $id ?>" aria-expanded="false" aria-controls="collapse-<?= $id ?>">
            <div class="item-info">
                <span class="item-type"><?= $type ?></span>
                <h3 class="item-title"><?= $title ?></h3>
            </div>
            <div class="item-status">
                <span class="status-badge <?= $isCompleted ? 'completed' : 'not-completed' ?>">
                    <?= $isCompleted ? 'Completed' : 'Not Completed' ?>
                </span>
                <span class="toggle-icon">▼</span>
            </div>
        </div>
        <div id="collapse-<?= $id ?>" class="collapse">
            <div class="item-body">
                <p><?= $description ?></p>
                <?php if ($isAccessible): ?>
                    <?php if ($type === 'Material'): ?>
                        <a href="#content-display-area" class="btn btn-primary view-content" 
                                data-content-id="<?= $id ?>" 
                                data-content-type="<?= $type ?>"
                                data-course-id="<?= $courseID ?>">
                            View Material
                        </a>
                    <?php elseif ($type === 'Quiz'): ?>
                        <button class="btn btn-primary take-quiz" 
                                data-quiz-id="<?= $id ?>" 
                                data-course-id="<?= $courseID ?>"
                                <?= $isCompleted ? 'disabled' : '' ?>>
                            <?= $isCompleted ? 'Completed' : 'Take Quiz' ?>
                        </button>
                    <?php elseif ($type === 'Task Sheet'): ?>
                         <button class="btn btn-primary view-task-sheet" 
                                data-task-sheet-id="<?= $id ?>" 
                                data-course-id="<?= $courseID ?>">
                            View Task Sheet
                        </button>
                    <?php elseif (strpos($type, 'assessment') !== false): ?>
                        <button class="btn btn-primary take-assessment" 
                                data-assessment-id="<?= $id ?>" 
                                data-course-id="<?= $courseID ?>"
                                data-assessment-type="<?= $type ?>"
                                <?= $isCompleted ? 'disabled' : '' ?>>
                            <?= $isCompleted ? 'Completed' : 'Take ' . ucfirst($type) ?>
                        </button>
                    <?php endif; ?>
                <?php else: ?>
                    <p class="locked-message">This content is locked. Complete the previous items to unlock.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <?php
}

function isItemAccessible($connect, $courseID, $studentID, $prevItemID, $currentItemID) {
    if ($currentItemID == $prevItemID || $prevItemID == 0) {
        return true;
    }
    $prevCompletedQuery = "SELECT id FROM student_progress
                           WHERE student_id = '$studentID'
                           AND course_id = '$courseID'
                           AND content_id = '$prevItemID'
                           AND is_completed = TRUE";
    $prevCompletedResult = mysqli_query($connect, $prevCompletedQuery);
    return mysqli_num_rows($prevCompletedResult) > 0;
}

function isItemCompleted($connect, $courseID, $studentID, $contentID, $type) {
    if ($type === 'Task Sheet') {
        $sql = "SELECT status FROM task_sheet_submissions 
                WHERE student_id = ? AND task_sheet_id = ? AND status = 'passed'";
        $stmt = $connect->prepare($sql);
        $stmt->bind_param("ii", $studentID, $contentID);
    } else {
        $sql = "SELECT is_completed FROM student_progress 
                WHERE student_id = ? AND course_id = ? AND content_id = ? AND content_type = ?";
        $stmt = $connect->prepare($sql);
        $stmt->bind_param("iiis", $studentID, $courseID, $contentID, $type);
    }
    
    $stmt->execute();
    $result = $stmt->get_result();
    return $result->num_rows > 0;
}

function checkCourseCompletion($connect, $courseID, $studentID) {
    $sqlCheckCompletion = "SELECT 
                            (SELECT COUNT(*) FROM (
                                SELECT material_id AS id FROM course_material WHERE course_id = '$courseID'
                                UNION ALL
                                SELECT quiz_id AS id FROM quiz WHERE course_id = '$courseID'
                                UNION ALL
                                SELECT task_sheet_id AS id FROM task_sheets WHERE course_id = '$courseID'
                                UNION ALL
                                SELECT assessment_id AS id FROM assessments WHERE course_id = '$courseID'
                            ) AS all_items) AS total_items,
                            COUNT(DISTINCT sp.content_id) AS completed_items
                           FROM student_progress sp
                           WHERE sp.student_id = '$studentID'
                           AND sp.course_id = '$courseID'
                           AND sp.is_completed = TRUE";

    $resultCheckCompletion = mysqli_query($connect, $sqlCheckCompletion);
    $completionRow = mysqli_fetch_assoc($resultCheckCompletion);
    $totalItems = $completionRow['total_items'];
    $completedItems = $completionRow['completed_items'];

    error_log("Total Items: $totalItems, Completed Items: $completedItems");

    if ($totalItems > 0 && $totalItems == $completedItems) {
        $updated = updateEnrollmentStatus($connect, $studentID, $courseID);
        if ($updated) {
            // Check if a completion notification has already been sent
            $check_notification = "SELECT id FROM notifications 
                                   WHERE user_id = ? AND course_id = ? AND message LIKE 'Congratulations! You have completed the course%'";
            $stmt_check = mysqli_prepare($connect, $check_notification);
            mysqli_stmt_bind_param($stmt_check, 'ii', $studentID, $courseID);
            mysqli_stmt_execute($stmt_check);
            mysqli_stmt_store_result($stmt_check);

            if (mysqli_stmt_num_rows($stmt_check) == 0) {
                // Create notification only if it hasn't been sent before
                $notification_message = "Congratulations! You have completed the course. Please wait for the instructor to verify your completion.";
                $insert_notification = "INSERT INTO notifications (user_id, recipient_type, course_id, message, status) 
                                        VALUES (?, ?, ?, ?, 'unread')";
                $stmt_notification = mysqli_prepare($connect, $insert_notification);
                $recipient_type = 'student';
                mysqli_stmt_bind_param($stmt_notification, 'isis', $studentID, $recipient_type, $courseID, $notification_message);
                
                if (mysqli_stmt_execute($stmt_notification)) {
                    $_SESSION['notification_message'] = "Notification created successfully!";
                } else {
                    // Handle notification creation error
                    error_log("Error creating notification: " . mysqli_error($connect));
                }

                generateStudentCertificate($connect, $studentID, $courseID);
            }

            error_log("Course completed, enrollment status updated");
            return true;
        } else {
            error_log("Failed to update enrollment status");
        }
    }

    return false;
}

function updateEnrollmentStatus($connect, $studentID, $courseID) {
    $sqlEnroll = "UPDATE enrollments SET status = 'Completed', completion_date = NOW() 
                  WHERE user_id = '$studentID' AND course_id = '$courseID'";
    $result = mysqli_query($connect, $sqlEnroll);
    
    if ($result) {
        if (mysqli_affected_rows($connect) > 0) {
            error_log("Enrollment status updated successfully");
            return true;
        } else {
            error_log("No rows were updated. Checking current status.");
            $sqlCheck = "SELECT status FROM enrollments WHERE user_id = '$studentID' AND course_id = '$courseID'";
            $resultCheck = mysqli_query($connect, $sqlCheck);
            if ($row = mysqli_fetch_assoc($resultCheck)) {
                error_log("Current enrollment status: " . $row['status']);
                if ($row['status'] !== 'Completed') {
                    // Force update if status is not 'Completed'
                    $sqlForceUpdate = "UPDATE enrollments SET status = 'Completed', completion_date = NOW() 
                                       WHERE user_id = '$studentID' AND course_id = '$courseID'";
                    mysqli_query($connect, $sqlForceUpdate);
                    error_log("Forced enrollment status update");
                }
                return true;
            } else {
                error_log("No enrollment record found. Creating one.");
                $sqlInsert = "INSERT INTO enrollments (user_id, course_id, status, enrollment_date,completion_date) 
                              VALUES ('$studentID', '$courseID', 'Completed', NOW(), NOW())";
                return mysqli_query($connect, $sqlInsert);
            }
        }
    } else {
        error_log("Error updating enrollment status: " . mysqli_error($connect));
        return false;
    }
}

function generateStudentCertificate($connect, $studentID, $courseID) {
    $sqlGetStudentName = "SELECT fullname FROM users WHERE user_id = '$studentID'";
    $resultStudentName = mysqli_query($connect, $sqlGetStudentName);
    $studentNameRow = mysqli_fetch_assoc($resultStudentName);
    $studentName = htmlspecialchars($studentNameRow['fullname']);

    generateCertificate($studentID, $studentName, $courseID);
}
function displayProgressBar($connect, $courseID, $studentID) {
    $sqlProgress = "SELECT 
        COUNT(DISTINCT all_items.id) AS total_items,
        SUM(CASE WHEN sp.is_completed = TRUE THEN 1 ELSE 0 END) AS completed_items
    FROM (
        SELECT material_id AS id, 'Material' AS type FROM course_material WHERE course_id = ?
        UNION ALL
        SELECT quiz_id AS id, 'Quiz' AS type FROM quiz WHERE course_id = ?
        UNION ALL
        SELECT task_sheet_id AS id, 'Task Sheet' AS type FROM task_sheets WHERE course_id = ?
        UNION ALL
        SELECT assessment_id AS id, CONCAT(assessment_type, '-assessment') AS type FROM assessments WHERE course_id = ?
    ) AS all_items
    LEFT JOIN (
        SELECT DISTINCT content_id, content_type, is_completed
        FROM student_progress
        WHERE student_id = ? AND course_id = ?
    ) sp ON all_items.id = sp.content_id AND sp.content_type = all_items.type";

    $stmt = mysqli_prepare($connect, $sqlProgress);
    mysqli_stmt_bind_param($stmt, "iiiiii", $courseID, $courseID, $courseID, $courseID, $studentID, $courseID);
    mysqli_stmt_execute($stmt);
    $resultProgress = mysqli_stmt_get_result($stmt);
    $progressRow = mysqli_fetch_assoc($resultProgress);

    $totalItems = $progressRow['total_items'];
    $completedItems = $progressRow['completed_items'];
    $progressPercentage = ($totalItems > 0) ? ($completedItems / $totalItems) * 100 : 0;

    ?>
    <div class="progress mb-3">
        <div class="progress-bar" role="progressbar" style="width: <?= $progressPercentage ?>%;" 
            aria-valuenow="<?= $progressPercentage ?>" aria-valuemin="0" aria-valuemax="100">
            <?= round($progressPercentage) ?>%
        </div>
    </div>
    <p class="text-muted mb-4">Progress: <?= $completedItems ?> / <?= $totalItems ?> Items completed</p>
    <?php
}

function displayCertificateArea($connect, $courseID, $studentID) {
    $sqlEnrollment = "SELECT status FROM enrollments WHERE user_id = '$studentID' AND course_id = '$courseID'";
    $resultEnrollment = mysqli_query($connect, $sqlEnrollment);
    $enrollment = mysqli_fetch_assoc($resultEnrollment);

    if ($enrollment && $enrollment['status'] == 'Completed') {
        $sqlCertificate = "SELECT * FROM certificates WHERE student_id = '$studentID' AND course_id = '$courseID' AND is_verified = 1";
        $resultCertificate = mysqli_query($connect, $sqlCertificate);
        
        if (mysqli_num_rows($resultCertificate) > 0) {
            $certificate = mysqli_fetch_assoc($resultCertificate);
            ?>
            <div class="card mb-4">
                <div class="card-body">
                    <h5 class="card-title">Course Certificate</h5>
                    <p class="card-text">Congratulations! You have completed this course.</p>
                    <p>Certificate issued on: <?= htmlspecialchars($certificate['generated_at']) ?></p>
                    <a href="../functions/generateCertificate.php?courseID=<?= $courseID ?>&studentID=<?= $studentID ?>" 
                       class="btn btn-primary" target="_blank">
                        View Certificate
                    </a>
                </div>
            </div>
            <?php
        } else {
            ?>
            <div class="alert alert-info" role="alert">
                Your course completion is being verified. The certificate will be available once verified by the instructor.
            </div>
            <?php
        }
    }
}

function displayAnnouncements($connect, $courseID) {
    $sql = "SELECT a.*, u.fullname as instructor_name 
            FROM announcements a 
            JOIN users u ON a.instructor_id = u.user_id 
            WHERE a.course_id = ? 
            ORDER BY a.created_at DESC";
    
    $stmt = $connect->prepare($sql);
    $stmt->bind_param("i", $courseID);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            ?>
            <div class="announcement-item mb-4">
                <h5><?= htmlspecialchars($row['title']) ?></h5>
                <p class="text-muted">
                    <small>
                        Posted by <?= htmlspecialchars($row['instructor_name']) ?> 
                        on <?= date('F j, Y, g:i a', strtotime($row['created_at'])) ?>
                    </small>
                </p>
                <p><?= nl2br(htmlspecialchars($row['content'])) ?></p>
            </div>
            <?php
        }
    } else {
        echo "<p>No announcements yet.</p>";
    }
    $stmt->close();
}

function displayLearningMaterials($connect, $courseID) {
    $sql = "SELECT * FROM learning_materials WHERE course_id = ? ORDER BY created_at DESC";
    
    $stmt = $connect->prepare($sql);
    $stmt->bind_param("i", $courseID);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            ?>
            <div class="learning-material-item mb-4">
                <h5><?= htmlspecialchars($row['title']) ?></h5>
                <p><?= nl2br(htmlspecialchars($row['description'])) ?></p>
                <?php if ($row['file_path']): ?>
                    <a href="../uploads/learning-materials/<?= htmlspecialchars($row['file_path']) ?>" class="btn btn-primary btn-sm" target="_blank">
                        View Material
                    </a>
                <?php endif; ?>
            </div>
            <?php
        }
    } else {
        echo "<p>No learning materials available yet.</p>";
    }
    $stmt->close();
}

?>

<style>
@media (max-width: 767px) {
    #sidebarToggle{
        width: 40px;
    }

    .content-list > .content-item > .item-header{
        flex-wrap: wrap;
    }
}

.content-display-area {
    background-color: #f8f9fa;
    border: 1px solid #dee2e6;
    border-radius: 0.25rem;
    padding: 1rem;
    min-height: 400px;
}

.nav-tabs {
    margin-bottom: 1rem;
}

.tab-content {
    background-color: #fff;
    border: 1px solid #dee2e6;
    border-top: none;
    border-radius: 0 0 0.25rem 0.25rem;
    padding: 1rem;
}

.content-item {
    border: 1px solid #dee2e6;
    margin-bottom: 10px;
    border-radius: 4px;
}

.item-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 10px 15px;
    cursor: pointer;
    background-color: #f8f9fa;
}

.item-info {
    display: flex;
    align-items: center;
}

.item-type {
    font-size: 0.8em;
    background-color: #e9ecef;
    padding: 2px 6px;
    border-radius: 4px;
    margin-right: 10px;
}

.item-title {
    margin: 0;
    font-size: 1em;
}

.item-status {
    display: flex;
    align-items: center;
}

.status-badge {
    font-size: 0.8em;
    padding: 2px 6px;
    border-radius: 4px;
    margin-right: 10px;
}

.status-badge.completed {
    background-color: #d4edda;
    color: #155724;
}

.status-badge.not-completed {
    background-color: #f8d7da;
    color: #721c24;
}

.toggle-icon {
    font-size: 0.8em;
}

.item-body {
    padding: 15px;
    border-top: 1px solid #dee2e6;
}

.content-item.locked .item-header {
    opacity: 0.7;
}

.locked-message {
    color: #721c24;
}

.btn {
    margin-top: 10px;
}

.progress {
    height: 25px;
}

.progress-bar {
    font-size: 0.9rem;
    line-height: 25px;
}

.announcement-item {
    background-color: #f8f9fa;
    border: 1px solid #e9ecef;
    border-radius: 0.25rem;
    padding: 1rem;
}

.announcement-item h5 {
    color: #007bff;
}

.learning-material-item {
    background-color: #f8f9fa;
    border: 1px solid #e9ecef;
    border-radius: 0.25rem;
    padding: 1rem;
}

.learning-material-item h5 {
    color: #007bff;
}
</style>

<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11.0.19/dist/sweetalert2.min.css">
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.0.19/dist/sweetalert2.all.min.js"></script>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const contentDisplayArea = document.querySelector('.content-display-area');
    const viewContentButtons = document.querySelectorAll('.view-content');
    const takeQuizButtons = document.querySelectorAll('.take-quiz');
    
    viewContentButtons.forEach(button => {
        button.addEventListener('click', function() {
            const contentId = this.getAttribute('data-content-id');
            const contentType = this.getAttribute('data-content-type');
            const courseID = this.getAttribute('data-course-id');

            if (contentType === 'Material') {
                fetch(`get-content.php?id=${contentId}&type=${contentType}&courseID=${courseID}`)
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            contentDisplayArea.innerHTML = data.data.content;
                            
                            // Add "Mark as done" button
                            const markAsDoneBtn = document.createElement('button');
                            markAsDoneBtn.textContent = data.data.isCompleted ? 'Completed' : 'Mark as done';
                            markAsDoneBtn.className = `btn ${data.data.isCompleted ? 'btn-secondary' : 'btn-primary'}`;
                            markAsDoneBtn.disabled = data.data.isCompleted;
                            markAsDoneBtn.addEventListener('click', function() {
                                markContentAsCompleted(contentId, contentType, courseID);
                            });
                            const buttonContainer = document.getElementById('markAsDoneButtonContainer');
                            if (buttonContainer) {
                                buttonContainer.appendChild(markAsDoneBtn);
                            }
                        } else {
                            contentDisplayArea.innerHTML = `<p>Error: ${data.message}</p>`;
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        contentDisplayArea.innerHTML = '<p>Error loading content. Please try again.</p>';
                    });
            }
        });
    });

    takeQuizButtons.forEach(button => {
        button.addEventListener('click', function() {
            const quizId = this.getAttribute('data-quiz-id');
            const courseId = this.getAttribute('data-course-id');
            
            // Show confirmation dialog
            Swal.fire({
                title: 'Ready to take the quiz?',
                html: '<p>Please note:</p>' +
                      '<ul>' +
                      '<li>The passing score is 70%</li>' +
                      '<li>Make sure to review all material before starting</li>' +
                      '<li>You cannot pause or restart the quiz once begun</li>' +
                      '</ul>',
                icon: 'info',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Yes, start the quiz!',
                cancelButtonText: 'No, I need to review'
            }).then((result) => {
                if (result.isConfirmed) {
                    // Redirect to quiz page
                    window.location.href = `main.php?page=quiz-page&quizID=${quizId}&courseID=${courseId}`;
                }
            });
        });
    });

    const takeAssessmentButtons = document.querySelectorAll('.take-assessment');
    
    takeAssessmentButtons.forEach(button => {
        button.addEventListener('click', function() {
            const assessmentId = this.getAttribute('data-assessment-id');
            const courseId = this.getAttribute('data-course-id');
            const assessmentType = this.getAttribute('data-assessment-type');
            
            let title, html;
            
            if (assessmentType === 'pre-assessment') {
                title = 'Ready to take the Pre-Assessment?';
                html = '<p>Please note:</p>' +
                    '<ul>' +
                    '<li>This assessment will help us understand your initial knowledge level</li>' +
                    '<li>There is no passing or failing score for this assessment</li>' +
                    '<li>Your answers will help tailor the course to your needs</li>' +
                    '</ul>';
            } else if (assessmentType === 'post-assessment') {
                title = 'Ready to take the Post-Assessment?';
                html = '<p>Please note:</p>' +
                    '<ul>' +
                    '<li>Make sure you have reviewed all course materials before starting</li>' +
                    '<li>This assessment will evaluate your understanding of the course content</li>' +
                    '<li>You need to score at least 70% to pass this assessment</li>' +
                    '<li>You can retake the assessment if you don\'t pass</li>' +
                    '</ul>';
            } else {
                // Default message if the assessment type is neither pre nor post
                title = `Ready to take the ${assessmentType}?`;
                html = '<p>Please make sure you are prepared before starting the assessment.</p>';
            }

            // Show confirmation dialog
            Swal.fire({
                title: title,
                html: html,
                icon: 'info',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Yes, start the assessment!',
                cancelButtonText: 'No, I need more time'
            }).then((result) => {
                if (result.isConfirmed) {
                    // Redirect to assessment page
                    window.location.href = `main.php?page=assessment-page&assessmentID=${assessmentId}&courseID=${courseId}&type=${assessmentType}`;
                }
            });
        });
    });

    function markContentAsCompleted(contentId, contentType, courseID) {
        const formData = new FormData();
        formData.append('action', 'complete');
        formData.append('studentID', '<?php echo $_SESSION['userID']; ?>');
        formData.append('courseID', courseID);
        formData.append('contentID', contentId);
        formData.append('type', contentType);

        fetch('get-content.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const markAsDoneBtn = contentDisplayArea.querySelector('button');
                markAsDoneBtn.textContent = 'Completed';
                markAsDoneBtn.classList.remove('btn-primary');
                markAsDoneBtn.classList.add('btn-secondary');
                markAsDoneBtn.disabled = true;
                window.location.reload();
            } else {
                alert('Error: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('An error occurred. Please try again.');
        });
    }

    const viewTaskSheetButtons = document.querySelectorAll('.view-task-sheet');

     viewTaskSheetButtons.forEach(button => {
        button.addEventListener('click', function() {
            const taskSheetId = this.getAttribute('data-task-sheet-id');
            const courseId = this.getAttribute('data-course-id');
            window.location.href = `main.php?page=task-sheet&taskSheetID=${taskSheetId}&courseID=${courseId}`;
        });
    });

    function refreshAnnouncements() {
        const announcementsTab = document.getElementById('announcements');
        if (announcementsTab) {
            fetch(`get-announcements.php?courseID=<?= $courseID ?>`)
                .then(response => response.text())
                .then(html => {
                    announcementsTab.innerHTML = html;
                })
                .catch(error => console.error('Error refreshing announcements:', error));
        }
    }

    function refreshLearningMaterials() {
        const learningMaterialsTab = document.getElementById('learning-materials');
        if (learningMaterialsTab) {
            fetch(`get-learning-materials.php?courseID=<?= $courseID ?>`)
                .then(response => response.text())
                .then(html => {
                    learningMaterialsTab.innerHTML = html;
                })
                .catch(error => console.error('Error refreshing learning materials:', error));
        }
    }

    // Refresh announcements and learning materials every 5 minutes
    setInterval(() => {
        refreshAnnouncements();
        refreshLearningMaterials();
    }, 300000);

    // Refresh announcements and learning materials when the tab is clicked
    document.getElementById('announcements-tab').addEventListener('click', refreshAnnouncements);
    document.getElementById('learning-materials-tab').addEventListener('click', refreshLearningMaterials);
});
</script>
