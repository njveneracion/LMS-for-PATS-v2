<?php
$studentID = $_SESSION['userID'];
$taskSheetID = isset($_GET['taskSheetID']) ? mysqli_real_escape_string($connect, $_GET['taskSheetID']) : null;
$courseID = isset($_GET['courseID']) ? mysqli_real_escape_string($connect, $_GET['courseID']) : null;

if (!$taskSheetID || !$courseID) {
    echo "Invalid request.";
    exit();
}

// Fetch task sheet details
$sqlTaskSheet = "SELECT * FROM task_sheets WHERE task_sheet_id = '$taskSheetID' AND course_id = '$courseID'";
$resultTaskSheet = mysqli_query($connect, $sqlTaskSheet);
$taskSheet = mysqli_fetch_assoc($resultTaskSheet);

if (!$taskSheet) {
    echo "Task sheet not found.";
    exit();
}

// Handle task sheet submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_task'])) {
    $submission = mysqli_real_escape_string($connect, $_POST['submission']);
    $uploadedFile = null;

    // Handle file upload
    if (isset($_FILES['task_file']) && $_FILES['task_file']['error'] == 0) {
        $allowedExtensions = ['doc', 'docx', 'ppt', 'pptx', 'pdf', 'jpg', 'jpeg', 'png', 'gif', 'mp4', 'mov', 'avi'];
        $fileExtension = strtolower(pathinfo($_FILES['task_file']['name'], PATHINFO_EXTENSION));

        if (in_array($fileExtension, $allowedExtensions)) {
            $uploadDir = '../uploads/students-submissions/';
            $uploadedFile = $uploadDir . uniqid() . '_' . $_FILES['task_file']['name'];

            if (move_uploaded_file($_FILES['task_file']['tmp_name'], $uploadedFile)) {
                $uploadedFile = mysqli_real_escape_string($connect, $uploadedFile);
            } else {
                $error = "Error uploading file.";
            }
        } else {
            $error = "Invalid file type. Allowed types: doc, docx, ppt, pptx, pdf, jpg, jpeg, png, gif, mp4, mov, avi";
        }
    }

    if (!isset($error)) {
        // Start transaction
        mysqli_begin_transaction($connect);
        
        try {
            // Insert or update task sheet submission
            $sqlSubmit = "INSERT INTO task_sheet_submissions (student_id, task_sheet_id, submission, file_path, status)
                          VALUES (?, ?, ?, ?, 'pending')
                          ON DUPLICATE KEY UPDATE submission = ?, file_path = ?, submitted_at = CURRENT_TIMESTAMP, status = 'pending'";
            $stmtSubmit = mysqli_prepare($connect, $sqlSubmit);
            mysqli_stmt_bind_param($stmtSubmit, "iissss", $studentID, $taskSheetID, $submission, $uploadedFile, $submission, $uploadedFile);
            mysqli_stmt_execute($stmtSubmit);
            
            // Update student progress
            $sqlProgress = "INSERT INTO student_progress (student_id, course_id, content_id, content_type, is_completed, completed_at)
                            VALUES (?, ?, ?, 'Task Sheet', 0, NULL)
                            ON DUPLICATE KEY UPDATE is_completed = 0, completed_at = NULL";
            $stmtProgress = mysqli_prepare($connect, $sqlProgress);
            mysqli_stmt_bind_param($stmtProgress, "iii", $studentID, $courseID, $taskSheetID);
            mysqli_stmt_execute($stmtProgress);
            
            // Commit transaction
            mysqli_commit($connect);

            echo "<script type='text/javascript'>
            window.location.href = 'main.php?page=task-sheet&taskSheetID=$taskSheetID&courseID=$courseID';
            </script>";
            
            $message = "Task sheet submitted successfully!";

            // Fetch necessary details for the notification
            $sqlGetDetails = "SELECT ts.task_sheet_title, c.course_name, c.user_id AS instructor_id, u.fullname
                              FROM task_sheets ts
                              JOIN courses c ON ts.course_id = c.course_id
                              JOIN users u ON u.user_id = ?
                              WHERE ts.task_sheet_id = ?";
            $stmtGetDetails = mysqli_prepare($connect, $sqlGetDetails);
            mysqli_stmt_bind_param($stmtGetDetails, "ii", $studentID, $taskSheetID);
            mysqli_stmt_execute($stmtGetDetails);
            $resultDetails = mysqli_stmt_get_result($stmtGetDetails);
            $details = mysqli_fetch_assoc($resultDetails);

            if ($details && isset($details['instructor_id'])) {
                $timestamp = date('Y-m-d H:i:s');
                $notification_message = "New submission on $timestamp: {$details['fullname']} has submitted the task sheet '{$details['task_sheet_title']}' for the course '{$details['course_name']}'.";
                $insert_notification = "INSERT INTO notifications (user_id, recipient_type, course_id, message, status) 
                                        VALUES (?, 'instructor', ?, ?, 'unread')";
                $stmt_notification = mysqli_prepare($connect, $insert_notification);
                mysqli_stmt_bind_param($stmt_notification, "iis", $details['instructor_id'], $courseID, $notification_message);
                
                if (mysqli_stmt_execute($stmt_notification)) {
                    $_SESSION['notification_message'] = "Notification created successfully!";
                } else {
                    // Handle notification creation error
                    error_log("Error creating notification: " . mysqli_error($connect));
                }
            } else {
                error_log("Error: Could not fetch necessary details for notification creation.");
            }
        } catch (Exception $e) {
            // Rollback transaction on error
            mysqli_rollback($connect);
            $error = "Error submitting task sheet: " . $e->getMessage();
        }
    }


}

// Fetch existing submission
$sqlExistingSubmission = "SELECT * FROM task_sheet_submissions 
                          WHERE student_id = '$studentID' AND task_sheet_id = '$taskSheetID'";
$resultExistingSubmission = mysqli_query($connect, $sqlExistingSubmission);
$existingSubmission = mysqli_fetch_assoc($resultExistingSubmission);

// Fetch submission status and feedback
$sqlSubmissionStatus = "SELECT status, feedback FROM task_sheet_submissions WHERE student_id = ? AND task_sheet_id = ? ORDER BY submitted_at DESC LIMIT 1";
$stmtSubmissionStatus = mysqli_prepare($connect, $sqlSubmissionStatus);
mysqli_stmt_bind_param($stmtSubmissionStatus, "ii", $studentID, $taskSheetID);
mysqli_stmt_execute($stmtSubmissionStatus);
$resultSubmissionStatus = mysqli_stmt_get_result($stmtSubmissionStatus);
$submissionStatus = mysqli_fetch_assoc($resultSubmissionStatus);
?>

<div class="container ">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card shadow-sm">
                <div class="card-body">
                    <h1 class="card-title text-primary mb-4"><?php echo htmlspecialchars($taskSheet['task_sheet_title']); ?></h1>
                    
                    <div class="alert alert-info mb-4">
                        <h5 class="alert-heading">Task Description</h5>
                        <p class="mb-0"><?php echo htmlspecialchars($taskSheet['task_sheet_description']); ?></p>
                    </div>
                    
                    <?php if (isset($message)): ?>
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            <?php echo $message; ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    <?php endif; ?>
                    
                    <?php if (isset($error)): ?>
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <?php echo $error; ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    <?php endif; ?>
                    
                    <?php if ($taskSheet['task_sheet_file']): ?>
                        <div class="mb-4">
                            <h5 class="mb-3">Task Sheet File</h5>
                            <?php
                            $file_path = "../uploads/task-sheets/" . $taskSheet['task_sheet_file'];
                            $file_extension = strtolower(pathinfo($file_path, PATHINFO_EXTENSION));
                            
                            switch ($file_extension) {
                                case 'pdf':
                                    echo "<embed src='$file_path' type='application/pdf' width='100%' height='600px' />";
                                    break;
                                case 'doc':
                                case 'docx':
                                case 'ppt':
                                case 'pptx':
                                    // Provide a download link for Office documents
                                    $file_name = basename($file_path);
                                    echo "<p>Preview not available for Office documents. You can download the file to view it.</p>";
                                    echo "<a href='$file_path' class='btn btn-primary' download='$file_name'>Download $file_extension file</a>";
                                    break;
                                case 'jpg':
                                case 'jpeg':
                                case 'png':
                                case 'gif':
                                    echo "<img src='$file_path' class='img-fluid' alt='Task Sheet Image'>";
                                    break;
                                case 'mp4':
                                case 'webm':
                                case 'ogg':
                                    echo "<video width='100%' height='auto' controls><source src='$file_path' type='video/$file_extension'>Your browser does not support the video tag.</video>";
                                    break;
                                default:
                                    echo "<p>Unsupported file type. <a href='$file_path' download>Download the file</a> to view it.</p>";
                            }
                            ?>
                        </div>
                    <?php endif; ?>
                    
                    <?php if ($existingSubmission): ?>
                        <div class="card mb-4 bg-light">
                            <div class="card-body">
                                <h4 class="card-title">Submission Information</h4>
                                <ul class="list-group list-group-flush">
                                    <li class="list-group-item d-flex justify-content-between align-items-center">
                                        <span><strong>Last submitted:</strong></span>
                                        <span><?php echo $existingSubmission['submitted_at']; ?></span>
                                    </li>
                                    <li class="list-group-item d-flex justify-content-between align-items-center">
                                        <span><strong>Status:</strong></span>
                                        <span>
                                            <?php
                                            if ($submissionStatus) {
                                                switch ($submissionStatus['status']) {
                                                    case 'passed':
                                                        echo '<span class="badge bg-success">Completed</span>';
                                                        break;
                                                    case 'failed':
                                                        echo '<span class="badge bg-danger">Failed</span>';
                                                        break;
                                                    case 'pending':
                                                        echo '<span class="badge bg-warning text-dark">Pending</span>';
                                                        break;
                                                    default:
                                                        echo '<span class="badge bg-secondary">Not Graded</span>';
                                                }
                                            } else {
                                                echo '<span class="badge bg-secondary">Not Submitted</span>';
                                            }
                                            ?>
                                        </span>
                                    </li>
                                    <?php if ($existingSubmission['file_path']): ?>
                                        <li class="list-group-item">
                                            <strong>Uploaded file:</strong>
                                            <a href="<?php echo $existingSubmission['file_path']; ?>" target="_blank" class="btn btn-sm btn-outline-secondary mt-2">
                                                <i class="fas fa-file-download me-2"></i>View File
                                            </a>
                                        </li>
                                    <?php endif; ?>
                                    <?php if ($submissionStatus && $submissionStatus['status'] !== 'pending'): ?>
                                        <li class="list-group-item">
                                            <strong>Feedback:</strong>
                                            <p class="mt-2 mb-0"><?php echo htmlspecialchars($submissionStatus['feedback']); ?></p>
                                        </li>
                                    <?php endif; ?>
                                </ul>
                            </div>
                        </div>
                    <?php endif; ?>
                    
                    <form method="POST" enctype="multipart/form-data" class="needs-validation" novalidate>
                        <div class="mb-4">
                            <label for="submission" class="form-label fw-bold">Your Submission:</label>
                            <textarea class="form-control" id="submission" name="submission" rows="10" required><?php echo htmlspecialchars($existingSubmission['submission'] ?? ''); ?></textarea>
                            <div class="invalid-feedback">Please provide your submission.</div>
                        </div>
                        <div class="mb-4">
                            <label for="task_file" class="form-label fw-bold">Upload File (optional):</label>
                            <input type="file" class="form-control" id="task_file" name="task_file">
                            <small class="form-text text-muted">Allowed file types: doc, docx, ppt, pptx, pdf, jpg, jpeg, png, gif, mp4, mov, avi</small>
                        </div>
                        <button type="submit" name="submit_task" class="btn btn-primary">
                            <i class="fas fa-paper-plane me-2"></i>Submit Task
                        </button>
                    </form>
                    
                    <div class="mt-4">
                        <a href="main.php?page=course-content&viewContent=<?php echo $courseID; ?>" class="btn btn-secondary">
                            <i class="fas fa-arrow-left me-2"></i>Back to Course
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    body {
        background-color: #f8f9fa;
    }
    .card {
        border: none;
        border-radius: 15px;
        box-shadow: 0 0 20px rgba(0, 0, 0, 0.1);
    }
    .btn-primary {
        background-color: #007bff;
        border-color: #007bff;
    }
    .btn-primary:hover {
        background-color: #0056b3;
        border-color: #0056b3;
    }
    .list-group-item {
        background-color: transparent;
    }
</style>


<script>
// Example starter JavaScript for disabling form submissions if there are invalid fields
(function () {
  'use strict'

  // Fetch all the forms we want to apply custom Bootstrap validation styles to
  var forms = document.querySelectorAll('.needs-validation')

  // Loop over them and prevent submission
  Array.prototype.slice.call(forms)
    .forEach(function (form) {
      form.addEventListener('submit', function (event) {
        if (!form.checkValidity()) {
          event.preventDefault()
          event.stopPropagation()
        }

        form.classList.add('was-validated')
      }, false)
    })
})()
</script>
