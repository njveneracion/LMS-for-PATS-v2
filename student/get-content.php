<?php
include '../config/db.php';
session_start();

header('Content-Type: application/json');

function sendJsonResponse($success, $message, $data = null) {
    echo json_encode(['success' => $success, 'message' => $message, 'data' => $data]);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'complete') {
    $studentID = mysqli_real_escape_string($connect, $_POST['studentID']);
    $courseID = mysqli_real_escape_string($connect, $_POST['courseID']);
    $contentID = mysqli_real_escape_string($connect, $_POST['contentID']);
    $type = mysqli_real_escape_string($connect, $_POST['type']);

    $sqlInsert = "INSERT INTO student_progress (student_id, course_id, content_id, content_type, is_completed, completed_at)
                  VALUES ('$studentID', '$courseID', '$contentID', '$type', TRUE, NOW())
                  ON DUPLICATE KEY UPDATE is_completed = TRUE, completed_at = NOW()";
    
    if (mysqli_query($connect, $sqlInsert)) {
        sendJsonResponse(true, 'Content marked as completed');
    } else {
        sendJsonResponse(false, 'Error updating progress: ' . mysqli_error($connect));
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['id']) && isset($_GET['type']) && isset($_GET['courseID'])) {
    $contentId = mysqli_real_escape_string($connect, $_GET['id']);
    $contentType = mysqli_real_escape_string($connect, $_GET['type']);
    $courseID = mysqli_real_escape_string($connect, $_GET['courseID']);

    $content = '';
    $isCompleted = false;

    switch ($contentType) {
        case 'Material':
            $sql = "SELECT * FROM course_material WHERE material_id = '$contentId'";
            $result = mysqli_query($connect, $sql);
            $row = mysqli_fetch_assoc($result);

            if ($row) {
                $file = $row['material_file'];
                $fileExtension = pathinfo($file, PATHINFO_EXTENSION);

                if (in_array($fileExtension, ['jpg', 'jpeg', 'png', 'gif'])) {
                    $content = "<img src='../uploads/course-materials/$file' class='img-fluid' alt='Material Image'>";
                } elseif (in_array($fileExtension, ['mp4', 'mov'])) {
                    $content = "<video controls class='w-100'>
                            <source src='../uploads/course-materials/$file' type='video/mp4'>
                            Your browser does not support the video tag.
                          </video>";
                } elseif (in_array($fileExtension, ['pdf', 'txt', 'docx', 'pptx'])) {
                    $content = "<embed src='../uploads/course-materials/$file' type='application/pdf' width='100%' height='600px'>";
                } else {
                    $content = "<p>Unsupported content type.</p>";
                }

                // Add a placeholder for the "Mark as done" button
                $content .= "<div id='markAsDoneButtonContainer'></div>";

                $sqlCheckCompleted = "SELECT * FROM student_progress 
                                      WHERE student_id = '{$_SESSION['userID']}' 
                                      AND course_id = '$courseID' 
                                      AND content_id = '$contentId' 
                                      AND content_type = '$contentType' 
                                      AND is_completed = TRUE";
                $resultCheckCompleted = mysqli_query($connect, $sqlCheckCompleted);
                $isCompleted = mysqli_num_rows($resultCheckCompleted) > 0;

                sendJsonResponse(true, 'Content fetched successfully', [
                    'content' => $content,
                    'isCompleted' => $isCompleted
                ]);
            } else {
                sendJsonResponse(false, 'Material not found');
            }
            break;

        case 'Quiz':
            $sql = "SELECT * FROM quiz WHERE quiz_id = '$contentId'";
            $result = mysqli_query($connect, $sql);
            $row = mysqli_fetch_assoc($result);

            if ($row) {
                $content = "<h3>{$row['quiz_name']}</h3>";
                $content .= "<p>{$row['quiz_description']}</p>";
                $content .= "<a href='main.php?page=quiz&courseID=$courseID&quizID=$contentId' class='btn btn-primary'>Start Quiz</a>";
            } else {
                sendJsonResponse(false, 'Quiz not found');
            }
            break;

        case 'Task Sheet':
            $sql = "SELECT * FROM task_sheets WHERE task_sheet_id = '$contentId'";
            $result = mysqli_query($connect, $sql);
            $row = mysqli_fetch_assoc($result);

            if ($row) {
                $content = "<h3>{$row['task_sheet_title']}</h3>";
                $content .= "<p>{$row['task_sheet_description']}</p>";
                $content .= "<embed src='../uploads/task-sheets/{$row['task_sheet_file']}' type='application/pdf' width='100%' height='600px'>";
                $content .= "<form method='post' enctype='multipart/form-data' class='mt-3'>
                                <input type='file' name='taskSubmission' required>
                                <button type='submit' name='submitTask' class='btn btn-primary'>Submit Task</button>
                             </form>";
            } else {
                sendJsonResponse(false, 'Task sheet not found');
            }
            break;

        case 'Pre-assessment':
        case 'Post-assessment':
            $sql = "SELECT * FROM assessments WHERE assessment_id = '$contentId' AND assessment_type = '$contentType'";
            $result = mysqli_query($connect, $sql);
            $row = mysqli_fetch_assoc($result);

            if ($row) {
                $content = "<h3>{$row['assessment_title']}</h3>";
                $content .= "<p>{$row['assessment_description']}</p>";
                $content .= "<a href='main.php?page=assessment&courseID=$courseID&assessmentID=$contentId&type=$contentType' class='btn btn-primary'>Start $contentType</a>";
            } else {
                sendJsonResponse(false, 'Assessment not found');
            }
            break;

        default:
            sendJsonResponse(false, 'Invalid content type');
    }

    sendJsonResponse(true, 'Content fetched successfully', [
        'content' => $content,
        'isCompleted' => $isCompleted,
        'contentType' => $contentType
    ]);
} else {
    sendJsonResponse(false, 'Invalid request');
}