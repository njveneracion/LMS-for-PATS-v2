<?php
// Start output buffering
ob_start();

$instructorID = $_SESSION['userID'];  // Assuming the instructor's ID is stored in the session

// Fetch all courses created by the instructor
$sqlCourses = "
    SELECT course_id, course_name
    FROM courses 
    WHERE user_id = $instructorID
";
$resultCourses = mysqli_query($connect, $sqlCourses);

if (isset($_POST['sendMessage'])) {
    $message = mysqli_real_escape_string($connect, $_POST['message']);
    $students = $_POST['students']; // Array of selected student IDs
    $courseID = $_POST['course_id'];

    if (!empty($students) && !empty($message)) {
        foreach ($students as $studentID) {
            // Insert the message into the notifications table
            $sql = "INSERT INTO notifications (user_id, instructor_id, course_id, message, status, created_at)
                    VALUES ('$studentID', '$instructorID', '$courseID', '$message', 'unread', NOW())";
            mysqli_query($connect, $sql);
        }
        $_SESSION['sendSuccess'] = "Message sent successfully!";
    } else {
        $_SESSION['sendError'] = "Please select at least one student and enter a message.";
    }
}

if (isset($_POST['deleteMessage'])) {
    $messageID = mysqli_real_escape_string($connect, $_POST['message_id']);
    $sqlDelete = "DELETE FROM notifications WHERE id = $messageID AND instructor_id = $instructorID";
    if (mysqli_query($connect, $sqlDelete)) {
        $_SESSION['deleteSuccess'] = "Message deleted successfully!";
    } else {
        $_SESSION['deleteError'] = "Error deleting message: " . mysqli_error($connect);
    }
}
?>

<div class="container-fluid mt-4">
    <!-- Notification area -->
    <div id="notificationArea"></div>

    <div class="row">
        <!-- Course Selection Sidebar -->
        <div class="col-md-3 mb-3">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">Select Course</h5>
                </div>
                <div class="card-body">
                    <form action="" method="POST">
                        <select class="form-select" name="course_id" id="course" onchange="this.form.submit()">
                            <option value="">Select a course</option>
                            <?php
                            // Populate the dropdown with courses
                            if ($resultCourses) {
                                while ($course = mysqli_fetch_assoc($resultCourses)) {
                                    $courseID = $course['course_id'];
                                    $courseName = $course['course_name'];
                                    echo "<option value='$courseID'>" . htmlspecialchars($courseName) . "</option>";
                                }
                            } else {
                                echo "<option value=''>No courses available</option>";
                            }
                            ?>
                        </select>
                    </form>
                </div>
            </div>
        </div>

        <!-- Main Content Area -->
        <div class="col-md-9">
            <?php
            if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['course_id'])) {
                $selectedCourseID = $_POST['course_id'];

                // Fetch students and course name for the selected course
                $sqlStudents = "
                    SELECT users.user_id, users.fullname, users.profile_picture, courses.course_name
                    FROM users
                    INNER JOIN course_registrations
                        ON users.user_id = course_registrations.student_id
                    INNER JOIN courses
                        ON course_registrations.course_id = courses.course_id
                    WHERE course_registrations.course_id = $selectedCourseID
                ";

                $resultStudents = mysqli_query($connect, $sqlStudents);
            ?>
            <div class="card">
                <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Message Students</h5>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="selectAll">
                        <label class="form-check-label text-white" for="selectAll">Select all</label>
                    </div>
                </div>
                <div class="card-body">
                    <form method="POST" id="messageForm">
                        <input type="hidden" name="course_id" value="<?php echo htmlspecialchars($selectedCourseID); ?>">
                        
                        <div class="row">
                            <!-- Student List -->
                            <div class="col-md-4 border-end">
                                <div class="student-list">
                                    <?php
                                    if ($resultStudents && mysqli_num_rows($resultStudents) > 0) {
                                        while ($student = mysqli_fetch_assoc($resultStudents)) {
                                            $studentID = $student['user_id'];
                                            $fullname = $student['fullname'];
                                            $profilePic = $student['profile_picture'] ? $student['profile_picture'] : 'default.jpg'; // Use a default image if no profile pic
                                            echo "<div class='form-check mb-2 d-flex align-items-center'>
                                                    <input class='form-check-input' type='checkbox' name='students[]' value='$studentID' id='student_$studentID'>
                                                    <label class='form-check-label d-flex align-items-center' for='student_$studentID'>
                                                        <img src='../uploads/profile-pictures/$profilePic' alt='$fullname' class='rounded-circle mx-2' style='width: 30px; height: 30px; object-fit: cover;'>
                                                        $fullname
                                                    </label>
                                                  </div>";
                                        }
                                    } else {
                                        echo "<p>No students enrolled in this course.</p>";
                                    }
                                    ?>
                                </div>
                            </div>
                            
                            <!-- Message Area -->
                            <div class="col-md-8">
                                <div class="form-group">
                                    <textarea class="form-control mb-3" name="message" id="message" rows="5" placeholder="Type your message here..." required></textarea>
                                </div>
                                <button type="submit" class="btn btn-primary" name="sendMessage">Send Message</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Message History -->
            <div class="card mt-4">
                <div class="card-header bg-secondary text-white">
                    <h5 class="mb-0">Message History</h5>
                </div>
                <div class="card-body">
                    <div class="accordion" id="messageAccordion">
                        <?php
                        mysqli_data_seek($resultStudents, 0); // Reset the result pointer
                        while ($student = mysqli_fetch_assoc($resultStudents)) {
                            $studentID = $student['user_id'];
                            $fullname = $student['fullname'];
                            $profilePic = $student['profile_picture'] ? $student['profile_picture'] : 'default_profile.jpg';
                            echo "<div class='accordion-item'>
                                    <h2 class='accordion-header' id='heading_$studentID'>
                                        <button class='accordion-button collapsed d-flex align-items-center' type='button' data-bs-toggle='collapse' data-bs-target='#collapse_$studentID' aria-expanded='false' aria-controls='collapse_$studentID'>
                                            <img src='../uploads/profile-pictures/$profilePic' alt='$fullname' class='rounded-circle me-2' style='width: 30px; height: 30px; object-fit: cover;'>
                                            $fullname
                                        </button>
                                    </h2>
                                    <div id='collapse_$studentID' class='accordion-collapse collapse' aria-labelledby='heading_$studentID' data-bs-parent='#messageAccordion'>
                                        <div class='accordion-body' id='messageList_$studentID'>";
                            
                            // Fetch messages for this student
                            $sqlMessages = "SELECT id, message, created_at FROM notifications WHERE user_id = $studentID AND instructor_id = $instructorID AND course_id = $selectedCourseID ORDER BY created_at DESC";
                            $resultMessages = mysqli_query($connect, $sqlMessages);

                            if ($resultMessages && mysqli_num_rows($resultMessages) > 0) {
                                while ($msg = mysqli_fetch_assoc($resultMessages)) {
                                    $messageID = $msg['id'];
                                    $message = htmlspecialchars($msg['message']);
                                    $createdAt = $msg['created_at'];
                                    echo "<div class='message-item mb-2' id='message_$messageID'>
                                            <div class='d-flex justify-content-between'>
                                                <small class='text-muted'>$createdAt</small>
                                                <button type='button' class='btn btn-danger btn-sm delete-message' data-bs-toggle='modal' data-bs-target='#deleteModal' data-message-id='$messageID'>Delete</button>
                                            </div>
                                            <p class='mb-0'>$message</p>
                                          </div>";
                                }
                            } else {
                                echo "<p class='no-messages'>No messages sent to this student yet.</p>";
                            }

                            echo "      </div>
                                    </div>
                                  </div>";
                        }
                        ?>
                    </div>
                </div>
            </div>

            <?php
            }
            ?>
        </div>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div class="modal fade" id="deleteModal" tabindex="-1" aria-labelledby="deleteModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="deleteModalLabel">Confirm Delete</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form method="POST" action="">
                <div class="modal-body">
                    Are you sure you want to delete this message?
                    <input type="hidden" name="message_id" id="messageIdToDelete">
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" name="deleteMessage" class="btn btn-danger">Delete</button>
                </div>
            </form>
        </div>
    </div>
</div>

<style>
.student-list {
    max-height: 300px;
    overflow-y: auto;
}
.message-item {
    background-color: #f8f9fa;
    border-radius: 8px;
    padding: 10px;
}
.alert-dismissible {
    position: fixed;
    top: 20px;
    right: 20px;
    z-index: 9999;
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function () {
    document.getElementById('selectAll').addEventListener('change', function () {
        var checkboxes = document.querySelectorAll('input[name="students[]"]');
        checkboxes.forEach(function (checkbox) {
            checkbox.checked = this.checked;
        }, this);
    });

    // Function to show notification
    function showNotification(message, type) {
        const notificationArea = document.getElementById('notificationArea');
        const alert = document.createElement('div');
        alert.className = `alert alert-${type} alert-dismissible fade show`;
        alert.setAttribute('role', 'alert');
        alert.innerHTML = `
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        `;
        notificationArea.appendChild(alert);

        // Automatically remove the alert after 5 seconds
        setTimeout(() => {
            alert.remove();
        }, 5000);
    }

    // Check for success message in session and display notification
    <?php if (isset($_SESSION['sendSuccess'])): ?>
        showNotification("<?php echo $_SESSION['sendSuccess']; ?>", 'success');
        <?php unset($_SESSION['sendSuccess']); ?>
    <?php endif; ?>

    // Check for error message in session and display notification
    <?php if (isset($_SESSION['sendError'])): ?>
        showNotification("<?php echo $_SESSION['sendError']; ?>", 'danger');
        <?php unset($_SESSION['sendError']); ?>
    <?php endif; ?>

    <?php if (isset($_SESSION['deleteSuccess'])): ?>
        showNotification("<?php echo $_SESSION['deleteSuccess']; ?>", 'success');
        <?php unset($_SESSION['deleteSuccess']); ?>
    <?php endif; ?>

    <?php if (isset($_SESSION['deleteError'])): ?>
        showNotification("<?php echo $_SESSION['deleteError']; ?>", 'danger');
        <?php unset($_SESSION['deleteError']); ?>
    <?php endif; ?>

    document.getElementById('messageForm').addEventListener('submit', function (e) {
        var checkboxes = document.querySelectorAll('input[name="students[]"]:checked');
        if (checkboxes.length === 0) {
            // Prevent form submission
            e.preventDefault();
            showNotification('Please select at least one student to send the message.', 'warning');
        }
    });

    // Handle delete button click
    var deleteModal = document.getElementById('deleteModal');
    deleteModal.addEventListener('show.bs.modal', function (event) {
        var button = event.relatedTarget;
        var messageId = button.getAttribute('data-message-id');
        document.getElementById('messageIdToDelete').value = messageId;
    });
});
</script>
<?php
// Flush the output buffer and send any buffered output
ob_end_flush();
?>

