<?php
$instructorID = $_SESSION['userID'];

// Fetch task sheet submissions
$sql = "SELECT ts.task_sheet_id, ts.task_sheet_title, tss.submission_id, tss.student_id, u.username, tss.submitted_at, tss.file_path, tss.status
        FROM task_sheets ts
        JOIN task_sheet_submissions tss ON ts.task_sheet_id = tss.task_sheet_id
        JOIN users u ON tss.student_id = u.user_id
        JOIN courses c ON ts.course_id = c.course_id
        WHERE c.user_id = ?
        ORDER BY tss.submitted_at DESC";

$stmt = $connect->prepare($sql);
$stmt->bind_param("i", $instructorID);
$stmt->execute();
$result = $stmt->get_result();

// Handle grading submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['grade_submission'])) {
    $submissionID = mysqli_real_escape_string($connect, $_POST['submission_id']);
    $status = mysqli_real_escape_string($connect, $_POST['status']);
    $feedback = mysqli_real_escape_string($connect, $_POST['feedback']);

    // Start transaction
    mysqli_begin_transaction($connect);

    try {
        // Update the task sheet submission
        $sqlUpdateSubmission = "UPDATE task_sheet_submissions 
                                SET status = ?, feedback = ? 
                                WHERE submission_id = ?";
        $stmtUpdateSubmission = mysqli_prepare($connect, $sqlUpdateSubmission);
        mysqli_stmt_bind_param($stmtUpdateSubmission, "ssi", $status, $feedback, $submissionID);
        mysqli_stmt_execute($stmtUpdateSubmission);

        // Get the student_id, course_id, and task_sheet_id for this submission
        $sqlGetDetails = "SELECT ts.student_id, t.course_id, ts.task_sheet_id 
                          FROM task_sheet_submissions ts
                          JOIN task_sheets t ON ts.task_sheet_id = t.task_sheet_id
                          WHERE ts.submission_id = ?";
        $stmtGetDetails = mysqli_prepare($connect, $sqlGetDetails);
        mysqli_stmt_bind_param($stmtGetDetails, "i", $submissionID);
        mysqli_stmt_execute($stmtGetDetails);
        $resultDetails = mysqli_stmt_get_result($stmtGetDetails);
        $details = mysqli_fetch_assoc($resultDetails);

        if ($details) {
            if ($status === 'passed') {
                // If passed, update progress as completed
                $sqlUpdateProgress = "INSERT INTO student_progress 
                                      (student_id, course_id, content_id, content_type, is_completed, completed_at)
                                      VALUES (?, ?, ?, 'Task Sheet', 1, NOW())
                                      ON DUPLICATE KEY UPDATE is_completed = 1, completed_at = NOW()";
            } else {
                // If failed, update progress as not completed
                $sqlUpdateProgress = "INSERT INTO student_progress 
                                      (student_id, course_id, content_id, content_type, is_completed, completed_at)
                                      VALUES (?, ?, ?, 'Task Sheet', 0, NULL)
                                      ON DUPLICATE KEY UPDATE is_completed = 0, completed_at = NULL";
            }
            
            $stmtUpdateProgress = mysqli_prepare($connect, $sqlUpdateProgress);
            mysqli_stmt_bind_param($stmtUpdateProgress, "iii", $details['student_id'], $details['course_id'], $details['task_sheet_id']);
            mysqli_stmt_execute($stmtUpdateProgress);

            $logs = logActivity($instructorID, "Graded task sheet submission", $submissionID);
            if (mysqli_stmt_execute($stmtUpdateProgress) && $logs) {
                $message = "Task sheet graded successfully!";
                
                // Create notification for the student
                $notification_message = "Your task sheet submission for '" . $details['task_sheet_title'] . "' has been graded. Status: " . ucfirst($status);
                $insert_notification = "INSERT INTO notifications (user_id, instructor_id, course_id, message, status) 
                                        VALUES (?, ?, ?, ?, 'unread')";
                $stmt_notification = mysqli_prepare($connect, $insert_notification);
                mysqli_stmt_bind_param($stmt_notification, "iiis", $details['student_id'], $instructorID, $details['course_id'], $notification_message);
                
                if (mysqli_stmt_execute($stmt_notification)) {
                    $_SESSION['notification_message'] = "Notification sent to student.";
                } else {
                    error_log("Error creating notification: " . mysqli_error($connect));
                }
                
                echo "<script>
                window.location.href = 'main.php?page=grade-task-sheets';
                </script>";        
            }
        }

        // Commit transaction
        mysqli_commit($connect);
        echo "<script>
        window.location.href = 'i_main.php?page=i_grade_task_sheets';
        </script>"; 
        $message = "Task sheet graded successfully!";
    } catch (Exception $e) {
        // Rollback transaction on error
        mysqli_rollback($connect);
        $error = "Error grading task sheet: " . $e->getMessage();
    }
}


?>

<div class="container mt-4">
    <h2 class="mb-4">Grade Task Sheet Submissions</h2>
    <div class="card shadow-sm">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead class="table-primary">
                        <tr>
                            <th>Task Sheet</th>
                            <th>Student</th>
                            <th>Submitted At</th>
                            <th>File</th>
                            <th>Status</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($row = $result->fetch_assoc()): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($row['task_sheet_title']); ?></td>
                                <td><?php echo htmlspecialchars($row['username']); ?></td>
                                <td><?php echo date('M d, Y H:i', strtotime($row['submitted_at'])); ?></td>
                                <td>
                                    <a href="../student/<?php echo htmlspecialchars($row['file_path']); ?>" target="_blank" class="btn btn-sm btn-outline-primary">
                                        <i class="fas fa-file-alt me-1"></i> View
                                    </a>
                                </td>
                                <td>
                                    <span class="badge bg-<?php echo $row['status'] == 'passed' ? 'success' : ($row['status'] == 'failed' ? 'danger' : 'secondary'); ?>">
                                        <?php echo ucfirst(htmlspecialchars($row['status'])); ?>
                                    </span>
                                </td>
                                <td>
                                    <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#gradeModal<?php echo $row['submission_id']; ?>">
                                        <i class="fas fa-check-circle me-1"></i> Grade
                                    </button>
                                </td>
                            </tr>
                            <!-- Grade Modal -->
                            <div class="modal fade" id="gradeModal<?php echo $row['submission_id']; ?>" tabindex="-1" aria-labelledby="gradeModalLabel" aria-hidden="true">
                                <div class="modal-dialog modal-dialog-centered">
                                    <div class="modal-content">
                                        <div class="modal-header bg-light">
                                            <h5 class="modal-title" id="gradeModalLabel">Grade Submission</h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                        </div>
                                        <form method="POST">
                                            <div class="modal-body">
                                                <input type="hidden" name="submission_id" value="<?php echo $row['submission_id']; ?>">
                                                <div class="mb-3">
                                                    <label for="status" class="form-label">Status</label>
                                                    <select class="form-select" id="status" name="status" required>
                                                        <option value="passed">Passed</option>
                                                        <option value="failed">Failed</option>
                                                    </select>
                                                </div>
                                                <div class="mb-3">
                                                    <label for="feedback" class="form-label">Feedback</label>
                                                    <textarea class="form-control" id="feedback" name="feedback" rows="3"></textarea>
                                                </div>
                                            </div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                                <button type="submit" name="grade_submission" class="btn btn-primary">
                                                    <i class="fas fa-save me-1"></i> Submit Grade
                                                </button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        <?php endwhile; ?>

                        <?php if ($result->num_rows === 0): ?>
                            <tr>
                                <td colspan="6" class="text-center">No task sheet submissions found.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>