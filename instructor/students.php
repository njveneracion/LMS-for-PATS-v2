<?php
include '../config/db.php';

// Fetch courses for the dropdown (only courses created by the current instructor)
$coursesQuery = "SELECT course_id, course_name FROM courses WHERE user_id = ?";
$stmt = mysqli_prepare($connect, $coursesQuery);
mysqli_stmt_bind_param($stmt, "i", $_SESSION['userID']);
mysqli_stmt_execute($stmt);
$coursesResult = mysqli_stmt_get_result($stmt);

// Sanitize inputs
$courseID = isset($_GET['courseID']) ? mysqli_real_escape_string($connect, $_GET['courseID']) : '';
$search = isset($_GET['search']) ? mysqli_real_escape_string($connect, $_GET['search']) : '';

// Construct query based on inputs
if ($courseID === '') {
    // Show students from all courses created by the instructor
    $studentsQuery = "
        SELECT u.user_id, u.fullname, u.profile_picture, cr.status, c.course_name
        FROM users u
        JOIN course_registrations cr ON u.user_id = cr.student_id
        JOIN courses c ON cr.course_id = c.course_id
        WHERE (u.fullname LIKE '%$search%' OR u.user_id LIKE '%$search%')
    ";
} else {
    // Show students from the selected course
    $studentsQuery = "
        SELECT u.user_id, u.fullname, u.profile_picture, cr.status, c.course_name
        FROM users u
        JOIN course_registrations cr ON u.user_id = cr.student_id
        JOIN courses c ON cr.course_id = c.course_id
        WHERE cr.course_id = '$courseID'
        AND (u.fullname LIKE '%$search%' OR u.user_id LIKE '%$search%')
    ";
}

$studentsResult = mysqli_query($connect, $studentsQuery);

// Handle form submission to generate report
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['generate_report'])) {
    $courseID = mysqli_real_escape_string($connect, $_POST['courseID']);
    $studentsQuery = "
        SELECT u.user_id, u.fullname, u.profile_picture, cr.status, c.course_name
        FROM users u
        JOIN course_registrations cr ON u.user_id = cr.student_id
        JOIN courses c ON cr.course_id = c.course_id
        WHERE cr.course_id = ?
    ";
    $stmt = mysqli_prepare($connect, $studentsQuery);
    mysqli_stmt_bind_param($stmt, "i", $courseID);
    mysqli_stmt_execute($stmt);
    $studentsResult = mysqli_stmt_get_result($stmt);

    // Generate CSV report
    if (isset($_POST['download_csv'])) {
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment;filename=students_report.csv');
        $output = fopen('php://output', 'w');
        fputcsv($output, array('User ID', 'Name', 'Profile Picture', 'Registration Status', 'Course Name'));
        while ($row = mysqli_fetch_assoc($studentsResult)) {
            fputcsv($output, $row);
        }
        fclose($output);
        exit;
    }
}

if (isset($_POST['generate_certificate'])) {
    $courseID = mysqli_real_escape_string($connect, $_POST['courseID']);
    $studentID = mysqli_real_escape_string($connect, $_POST['studentID']);
    $EcertUpdate = "UPDATE certificates SET is_verified = '1' WHERE course_id = '$courseID' AND student_id = '$studentID'";
    mysqli_query($connect, $EcertUpdate);

    if (mysqli_query($connect, $EcertUpdate)) {
        $_SESSION['certificate_success'] = "Certificate generated successfully!";

        $notification_message = "Your certificate for the course has been generated.";
        $insert_notification = "INSERT INTO notifications (user_id, recipient_type, course_id, message, status) 
                                 VALUES (?, 'student', ?, ?, 'unread')";
        $stmt_notification = mysqli_prepare($connect, $insert_notification);
        mysqli_stmt_bind_param($stmt_notification, "iis", $studentID, $courseID, $notification_message);
         
        if (mysqli_stmt_execute($stmt_notification)) {
            $_SESSION['notification_message'] = "Notification created successfully!";
        } else {
            // Handle notification creation error
            error_log("Error creating notification: " . mysqli_error($connect));
        }
    } else {
        // Handle certificate update error
        error_log("Error updating certificate: " . mysqli_error($connect));
        $_SESSION['certificate_error'] = "Error updating certificate status. Unverified";
    }
}
?>

<div class="container-fluid py-4">
    <div class="row">
        <div class="col-md-4 mb-3">
            <div class="card h-100 border-primary">
                <div class="card-header bg-primary text-white">
                    <h4 class="card-title fw-bold mb-0">Generate Report</h4>
                </div>
                <div class="card-body">
                    <form method="post" action="students.php">
                        <div class="mb-3">
                            <label for="courseID" class="form-label">Select Course</label>
                            <select id="courseID" name="courseID" class="form-select" required>
                                <option value="" disabled selected>Choose a course</option>
                                <?php while ($course = mysqli_fetch_assoc($coursesResult)) { ?>
                                    <option value="<?php echo htmlspecialchars($course['course_id']); ?>">
                                        <?php echo htmlspecialchars($course['course_name']); ?>
                                    </option>
                                <?php } ?>
                            </select>
                        </div>
                        <button type="submit" name="generate_report" class="btn btn-primary">Generate Report</button>
                        <button type="submit" name="download_csv" class="btn btn-secondary">Download CSV</button>
                    </form>
                </div>
            </div>
        </div>
        <div class="col-md-8 mb-3">
            <div class="card h-100 border-primary">
                <div class="card-header bg-primary text-white">
                    <h4 class="card-title fw-bold mb-0">List of Students</h4>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead class="table-primary">
                                <tr>
                                    <th>User ID</th>
                                    <th>Name</th>
                                    <th>Profile Picture</th>
                                    <th>Registration Status</th>
                                    <th>Course Name</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (isset($studentsResult)) {
                                    while ($student = mysqli_fetch_assoc($studentsResult)) { ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($student['user_id']); ?></td>
                                            <td><?php echo htmlspecialchars($student['fullname']); ?></td>
                                            <td><img src="<?php echo htmlspecialchars($student['profile_picture']); ?>" alt="Profile Picture" width="50"></td>
                                            <td><?php echo htmlspecialchars($student['status']); ?></td>
                                            <td><?php echo htmlspecialchars($student['course_name']); ?></td>
                                        </tr>
                                    <?php }
                                } else { ?>
                                    <tr>
                                        <td colspan="5" class="text-center">Select a course to generate the report.</td>
                                    </tr>
                                <?php } ?>
                            </tbody>
                        </table>
                    </div>
                    <?php
                    if (isset($_SESSION['registration_success'])) {
                        echo '<div id="success-alert" class="alert alert-success mt-3" role="alert">' . $_SESSION['registration_success'] . '</div>';
                        unset($_SESSION['registration_success']);
                    }
                    ?>
                </div>
            </div>
        </div>
    </div>

    <div class="card mt-3 border-primary">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead class="table-primary">
                        <tr>
                            <th>User ID</th>
                            <th>Name</th>
                            <th>Profile Picture</th>
                            <th>Registration Status</th>
                            <th>Course Name</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody id="userTableBody">
                        <!-- Students will be loaded here by AJAX -->
                    </tbody>
                </table>
            </div>
            <?php
            if (isset($_SESSION['registration_success'])) {
                echo '<div id="success-alert" class="alert alert-success mt-3" role="alert">' . $_SESSION['registration_success'] . '</div>';
                unset($_SESSION['registration_success']);
            }
            ?>
        </div>


    </div>

    <!-- New section for enrollment status and progress -->
    <div class="card mt-3 border-primary">
        <div class="card-header bg-primary text-white">
            <h4 class="card-title fw-bold d-flex justify-content-between align-items-center flex-rows">Enrollment Status and Progress</h4> 
        </div>
        <div class="card-body">
            <div class="input-group mb-3">
                <span class="input-group-text bg-primary text-white" style="height: 38px;"><i class="fas fa-search"></i></span>
                <input type="text" id="studentSearch" class="form-control w-50" placeholder="Search for students...">
            </div>
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead class="table-primary">
                        <tr>
                            <th>Student Name</th>
                            <th>Course Name</th>
                            <th>Enrollment Status</th>
                            <th>Progress</th>
                            <th>Action</th>
                            <th>Certificate Status</th>
                        </tr>
                    </thead>
                    <tbody id="enrollmentTableBody">
                        <?php
                        // Modify the enrollment query to only show students enrolled in the instructor's courses
                        $enrollmentQuery = "
                            SELECT u.fullname, c.course_name, e.status, e.user_id, e.course_id,
                                COUNT(DISTINCT all_items.id) AS total_items,
                                SUM(CASE WHEN sp.is_completed = TRUE THEN 1 ELSE 0 END) AS completed_items
                            FROM enrollments e
                            JOIN users u ON e.user_id = u.user_id
                            JOIN courses c ON e.course_id = c.course_id
                            LEFT JOIN (
                                SELECT material_id AS id, 'Material' AS type, course_id FROM course_material
                                UNION ALL
                                SELECT quiz_id AS id, 'Quiz' AS type, course_id FROM quiz
                                UNION ALL
                                SELECT task_sheet_id AS id, 'Task Sheet' AS type, course_id FROM task_sheets
                                UNION ALL
                                SELECT assessment_id AS id, CONCAT(assessment_type, '-assessment') AS type, course_id FROM assessments
                            ) AS all_items ON all_items.course_id = e.course_id
                            LEFT JOIN (
                                SELECT DISTINCT content_id, content_type, is_completed, student_id, course_id
                            ) sp ON all_items.id = sp.content_id 
                                AND sp.content_type = all_items.type 
                                AND sp.student_id = e.user_id 
                                AND sp.course_id = e.course_id
                            WHERE c.user_id = ?
                            GROUP BY e.user_id, e.course_id
                        ";
                        $stmt = mysqli_prepare($connect, $enrollmentQuery);
                        mysqli_stmt_bind_param($stmt, "i", $_SESSION['userID']);
                        mysqli_stmt_execute($stmt);
                        $enrollmentResult = mysqli_stmt_get_result($stmt);

                        // Add this query before the while loop to fetch certificate status
                        $certificateStatusQuery = "SELECT is_verified FROM certificates WHERE student_id = ? AND course_id = ?";
                        $stmtCertStatus = mysqli_prepare($connect, $certificateStatusQuery);

                        while ($row = mysqli_fetch_assoc($enrollmentResult)) {
                            $totalItems = $row['total_items'];
                            $completedItems = $row['completed_items'];
                            $progressPercentage = ($totalItems > 0) ? ($completedItems / $totalItems) * 100 : 0;

                            // Check certificate status
                            mysqli_stmt_bind_param($stmtCertStatus, "ii", $row['user_id'], $row['course_id']);
                            mysqli_stmt_execute($stmtCertStatus);
                            $certResult = mysqli_stmt_get_result($stmtCertStatus);
                            $certStatus = mysqli_fetch_assoc($certResult);
                            $isCertificateGenerated = ($certStatus && $certStatus['is_verified'] == 1);

                            echo "<tr class='student-row'>
                                    <td>{$row['fullname']}</td>
                                    <td>{$row['course_name']}</td>
                                    <td>{$row['status']}</td>
                                    <td>
                                        <div class='progress'>
                                            <div class='progress-bar' role='progressbar' style='width: {$progressPercentage}%' aria-valuenow='{$progressPercentage}' aria-valuemin='0' aria-valuemax='100'>" . round($progressPercentage, 2) . "%</div>
                                        </div>
                                    </td>
                                    <td>
                                        <form method='post'>
                                            <input type='hidden' name='courseID' value='{$row['course_id']}'>
                                            <input type='hidden' name='studentID' value='{$row['user_id']}'>
                                            <button type='submit' class='btn btn-warning btn-sm' name='generate_certificate' " . ($progressPercentage == 100 ? '' : 'disabled') . ">
                                                Generate E-Certificate
                                            </button>
                                        </form>
                                    </td>
                                    <td>";
                            
                            // Add certificate status indicator in the new column
                            if ($progressPercentage == 100) {
                                if ($isCertificateGenerated) {
                                    echo "<span class='certificate-status generated'><i class='fas fa-check'></i> Generated</span>";
                                } else {
                                    echo "<span class='certificate-status not-generated'><i class='fas fa-times'></i> Not Generated</span>";
                                }
                            } else {
                                echo "<span class='certificate-status not-completed'><i class='fas fa-times'></i> Course Not Completed</span>";
                            }
                            
                            echo "</td></tr>";
                        }

                        // Don't forget to close the prepared statement after the loop
                        mysqli_stmt_close($stmtCertStatus);
                        ?>


                        
                    </tbody>
                </table>
                <?php
            if (isset($_SESSION['certificate_success'])) {
                echo '<div id="success-alert" class="alert alert-success mt-3 alert-dismissible fade show" role="alert">' . $_SESSION['certificate_success'] . '
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button></div>';
                unset($_SESSION['certificate_success']);
            } 
            if (isset($_SESSION['certificate_error'])) {
                echo '<div id="error-alert" class="alert alert-danger mt-3 alert-dismissible fade show" role="alert">' . $_SESSION['certificate_error'] . '
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button></div>';
                unset($_SESSION['certificate_error']);
            }
            ?>
            </div>
        </div>
    </div>
</div>

<style>
/* CSS for fading out the alert */
.fade-out {
    opacity: 0;
    transition: opacity 0.5s ease-out; /* Adjust timing if needed */
}

/* CSS to hide the element after fading out */
.hidden {
    display: none;
}

:root {
    --primary-color: #007bff;
    --primary-light: #cce5ff;
    --primary-dark: #0056b3;
}

.card {
    box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
    border-color: var(--primary-color) !important;
}

.card-header {
    background-color: var(--primary-color) !important;
}

.table th {
    font-weight: 600;
}

.table-primary {
    background-color: var(--primary-light) !important;
}

.table td {
    vertical-align: middle;
}

.btn-success {
    transition: all 0.2s ease;
    background-color: var(--primary-color);
    border-color: var(--primary-color);
}

.btn-success:hover {
    transform: translateY(-2px);
    box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
    background-color: var(--primary-dark);
    border-color: var(--primary-dark);
}

.input-group-text {
    background-color: var(--primary-color) !important;
    border-color: var(--primary-color) !important;
}

.progress {
    height: 20px;
    background-color: #e9ecef;
    border-radius: 0.25rem;
}

.progress-bar {
    background-color: var(--primary-color);
    color: white;
    text-align: center;
    line-height: 20px;
    transition: width 0.6s ease;
}

@media (max-width: 767.98px) {
    .table-responsive {
        overflow-x: auto;
    }
}

#studentSearch {
    margin-bottom: 15px;
}

/* Add this to your existing styles */
.certificate-status {
    display: inline-block;
    padding: 5px 10px;
    border-radius: 4px;
    font-size: 14px;
    font-weight: bold;
}
.certificate-status.generated {
    background-color: #28a745;
    color: white;
}
.certificate-status.not-generated,
.certificate-status.not-completed {
    background-color: #dc3545;
    color: white;
}
.certificate-status i {
    margin-right: 5px;
}

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

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/js/all.min.js"></script>
<script>
    $(document).ready(function() {
        function loadStudents(courseID, searchQuery) {
            $.ajax({
                url: 'search-students.php',
                type: 'GET',
                data: { courseID: courseID, search: searchQuery },
                success: function(data) {
                    $('#userTableBody').html(data);
                }
            });
        }

        // Load students initially if a course is selected
        var initialCourseID = $('#courseDropdown').val();
        var initialSearchQuery = $('#searchInput').val();
        loadStudents(initialCourseID, initialSearchQuery);

        $('#courseDropdown').change(function() {
            var courseID = $(this).val();
            var searchQuery = $('#searchInput').val();
            loadStudents(courseID, searchQuery);
        });

        $('#searchInput').keyup(function() {
            var searchQuery = $(this).val();
            var courseID = $('#courseDropdown').val();
            loadStudents(courseID, searchQuery);
        });


        $("#studentSearch").on("keyup", function() {
            var value = $(this).val().toLowerCase();
            $("#enrollmentTableBody tr").filter(function() {
                $(this).toggle($(this).text().toLowerCase().indexOf(value) > -1)
            });
        });
    });
</script>
