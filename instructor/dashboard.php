<?php
$userID = $_SESSION['userID'];
$toastMessage = "";
if (isset($_POST['addCourse'])) {
    $courseName = $_POST['courseName'];
    $coursePhoto = $_FILES['coursePhoto'];
    $courseDurationHours = isset($_POST['courseDurationHours']) ? (int)$_POST['courseDurationHours'] : 0;
    $courseDurationMinutes = isset($_POST['courseDurationMinutes']) ? (int)$_POST['courseDurationMinutes'] : 0;
    $batchName = $_POST['batchName'];
    $startDate = $_POST['startDate'];
    $endDate = isset($_POST['endDate']) ? $_POST['endDate'] : null;
    $capacity = isset($_POST['capacity']) ? (int)$_POST['capacity'] : 0;

    // Convert course duration to total minutes
    $totalMinutes = ($courseDurationHours * 60) + $courseDurationMinutes;

    // Check if course code is provided, if not generate a unique one
    $courseCode = isset($_POST['courseCode']) ? $_POST['courseCode'] : strtoupper(substr(md5(uniqid(mt_rand(), true)), 0, 6));

    // handle file upload
    if (isset($coursePhoto)) {
        $fileTempPath = $coursePhoto['tmp_name'];
        $fileName = $coursePhoto['name'];
        $fileSize = $coursePhoto['size'];
        $fileType = $coursePhoto['type'];
        $fileNameComponents = explode(".", $fileName);
        $fileExtension = strtolower(end($fileNameComponents));
        // define allowed file extensions
        $allowedFileExtensions = array('jpg', 'png', 'jpeg');
        if (in_array($fileExtension, $allowedFileExtensions)) {
            $newFileName = md5(time() . $fileName) . '.' . $fileExtension;
            $targetDir = '../uploads/course-photo/';
            $targetFilePath = $targetDir . $newFileName;
            if (move_uploaded_file($fileTempPath, $targetFilePath)) {
                $coursePhoto = $targetFilePath;
                $sqlAddCourse = "INSERT INTO courses (user_id, course_name, course_img, course_duration, course_code) VALUES ('$userID', '$courseName', '$coursePhoto', '$totalMinutes', '$courseCode')";
                $resultAddCourse = mysqli_query($connect, $sqlAddCourse);

                if ($resultAddCourse) {
                        // Get the ID of the newly inserted course
                        $courseID = mysqli_insert_id($connect);

                        // Insert batch into the database
                        $sqlAddBatch = "INSERT INTO batches (course_id, batch_name, start_date, end_date, capacity) 
                        VALUES ('$courseID', '$batchName', '$startDate', '$endDate', '$capacity')";
                        $resultAddBatch = mysqli_query($connect, $sqlAddBatch);

                        if($resultAddBatch){
                            $toastMessage = "Course and Batch added successfully.";
                        } else {
                            echo "Error: " . $sqlAddBatch . "<br/>" . mysqli_error($connect);
                        }
                    
                } else {
                    echo "Error: " . $sqlAddCourse . "<br/>" . mysqli_error($connect);
                }
            } else {
                echo "There was an error moving the uploaded file.";
            }
        } else {
            echo "Upload failed. Allowed file types: " . implode(', ', $allowedFileExtensions);
        }
    } else {
        echo "There was an error uploading the file.";
    }
}

$sqlShowCourses = "SELECT c.course_id, c.course_name, c.course_img, c.course_duration, c.course_code,
                   b.batch_name, b.start_date, b.end_date, b.capacity,
                   COUNT(e.enrollment_id) as enrolled_students
                   FROM courses c
                   LEFT JOIN batches b ON c.course_id = b.course_id
                   LEFT JOIN enrollments e ON b.batch_id = e.batch_id
                   WHERE c.user_id = '$userID'
                   GROUP BY c.course_id, b.batch_id";
                    //CURDATE() function in SQL to get the current date and compare it with the course's end date.

                    
$resultShowCourses = mysqli_query($connect, $sqlShowCourses);

// Add this query to fetch course analytics
$sqlCourseAnalytics = "SELECT c.course_id, c.course_name, 
                              COUNT(DISTINCT e.user_id) as enrolled_students,
                              AVG(CASE WHEN e.status = 'Completed' THEN 1 ELSE 0 END) * 100 as completion_rate,
                              COUNT(DISTINCT cm.material_id) as total_materials,
                              COUNT(DISTINCT ts.task_sheet_id) as total_task_sheets,
                              COUNT(DISTINCT q.quiz_id) as total_quizzes
                       FROM courses c
                       LEFT JOIN enrollments e ON c.course_id = e.course_id
                       LEFT JOIN course_material cm ON c.course_id = cm.course_id
                       LEFT JOIN task_sheets ts ON c.course_id = ts.course_id
                       LEFT JOIN quiz q ON c.course_id = q.course_id
                       WHERE c.user_id = '$userID'
                       GROUP BY c.course_id";
$resultCourseAnalytics = mysqli_query($connect, $sqlCourseAnalytics);

// Add this query to fetch recent announcements
$sqlRecentAnnouncements = "SELECT a.title, a.content, a.created_at, c.course_name
                           FROM announcements a
                           JOIN courses c ON a.course_id = c.course_id
                           WHERE a.instructor_id = '$userID'
                           ORDER BY a.created_at DESC
                           LIMIT 5";
$resultRecentAnnouncements = mysqli_query($connect, $sqlRecentAnnouncements);

// Add this query to fetch recent discussion activity
$sqlRecentDiscussions = "SELECT d.message, d.created_at, u.fullname, c.course_name
                         FROM discussions d
                         JOIN users u ON d.user_id = u.user_id
                         JOIN courses c ON d.course_id = c.course_id
                         WHERE c.user_id = '$userID'
                         ORDER BY d.created_at DESC
                         LIMIT 5";
$resultRecentDiscussions = mysqli_query($connect, $sqlRecentDiscussions);

// Add this query to fetch pending course registrations
$sqlPendingRegistrations = "SELECT cr.registration_id, u.fullname, c.course_name
                            FROM course_registrations cr
                            JOIN users u ON cr.student_id = u.user_id
                            JOIN courses c ON cr.course_id = c.course_id
                            WHERE c.user_id = '$userID' AND cr.status = 'pending'
                            ORDER BY cr.registration_id DESC";
$resultPendingRegistrations = mysqli_query($connect, $sqlPendingRegistrations);

// Add this function to handle course deletion
if (isset($_POST['deleteCourse'])) {
    $courseIdToDelete = $_POST['courseId'];
    $sqlDeleteCourse = "DELETE FROM courses WHERE course_id = ? AND user_id = ?";
    $stmt = mysqli_prepare($connect, $sqlDeleteCourse);
    mysqli_stmt_bind_param($stmt, "ii", $courseIdToDelete, $userID);
    if (mysqli_stmt_execute($stmt)) {
        $toastMessage = "Course deleted successfully.";
    } else {
        $toastMessage = "Error deleting course: " . mysqli_error($connect);
    }
}

// Add this code to handle course editing
if (isset($_POST['editCourse'])) {
    $courseId = $_POST['courseId'];
    $editCourseName = $_POST['editCourseName'];
    $editCourseCode = $_POST['editCourseCode'];
    $editCourseDuration = $_POST['editCourseDuration'];

    $updateQuery = "UPDATE courses SET course_name = ?, course_code = ?, course_duration = ? WHERE course_id = ? AND user_id = ?";
    $stmt = mysqli_prepare($connect, $updateQuery);
    mysqli_stmt_bind_param($stmt, "ssiii", $editCourseName, $editCourseCode, $editCourseDuration, $courseId, $userID);
    
    if (mysqli_stmt_execute($stmt)) {
        // Handle file upload if a new photo was provided
        if ($_FILES['editCoursePhoto']['size'] > 0) {
            $fileTempPath = $_FILES['editCoursePhoto']['tmp_name'];
            $fileName = $_FILES['editCoursePhoto']['name'];
            $fileNameComponents = explode(".", $fileName);
            $fileExtension = strtolower(end($fileNameComponents));
            $newFileName = md5(time() . $fileName) . '.' . $fileExtension;
            $targetDir = '../uploads/course-photo/';
            $targetFilePath = $targetDir . $newFileName;
            
            if (move_uploaded_file($fileTempPath, $targetFilePath)) {
                $updatePhotoQuery = "UPDATE courses SET course_img = ? WHERE course_id = ?";
                $stmtPhoto = mysqli_prepare($connect, $updatePhotoQuery);
                mysqli_stmt_bind_param($stmtPhoto, "si", $targetFilePath, $courseId);
                mysqli_stmt_execute($stmtPhoto);
            }
        }
        
        $toastMessage = "Course updated successfully.";
    } else {
        $toastMessage = "Error updating course: " . mysqli_error($connect);
    }
}
?>

<style>
    .dashboard-card {
        transition: transform 0.3s ease-in-out, box-shadow 0.3s ease-in-out;
    }
    .dashboard-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 10px 20px rgba(0,0,0,0.1);
    }
    .course-img {
        height: 150px;
        object-fit: cover;
    }
    .stats-icon {
        font-size: 2rem;
        opacity: 0.1;
        position: absolute;
        right: 10px;
        top: 10px;
    }
</style>

<div class="container-fluid py-4">
    <div class="row mb-4">
        <div class="col-md-3 mb-2">
            <div class="card dashboard-card h-100 bg-primary text-white">
                <div class="card-body modified-bg-primary">
                    <h5 class="card-title">Total Courses</h5>
                    <p class="card-text display-4"><?= mysqli_num_rows($resultShowCourses) ?></p>
                    <i class="fas fa-book stats-icon"></i>
                </div>
            </div>
        </div>
        <div class="col-md-3 mb-2">
            <div class="card dashboard-card h-100 bg-success text-white">
                <div class="card-body">
                    <h5 class="card-title">Total Students</h5>
                    <p class="card-text display-4">
                        <?php
                        $totalStudents = 0;
                        mysqli_data_seek($resultShowCourses, 0);
                        while ($row = mysqli_fetch_assoc($resultShowCourses)) {
                            $totalStudents += $row['enrolled_students'];
                        }
                        echo $totalStudents;
                        ?>
                    </p>
                    <i class="fas fa-users stats-icon"></i>
                </div>
            </div>
        </div>
        <div class="col-md-3 mb-2">
            <div class="card dashboard-card h-100 bg-warning text-white">
                <div class="card-body">
                    <h5 class="card-title">Pending Registrations</h5>
                    <p class="card-text display-4"><?= mysqli_num_rows($resultPendingRegistrations) ?></p>
                    <i class="fas fa-clock stats-icon"></i>
                </div>
            </div>
        </div>
        <div class="col-md-3 mb-2">
            <div class="card dashboard-card h-100 bg-info text-white">
                <div class="card-body">
                    <h5 class="card-title">Recent Discussions</h5>
                    <p class="card-text display-4"><?= mysqli_num_rows($resultRecentDiscussions) ?></p>
                    <i class="fas fa-comments stats-icon"></i>
                </div>
            </div>
        </div>
    </div>

    <div class="row mb-4">
        <div class="col-12">
            <div class="card dashboard-card">
                <div class="card-header modified-bg-primary">
                    <label class="fs-5 text-light fw-semibold">Your Courses</label>
                </div>
                <div class="card-body">
                    <button class="button-primary mb-4 rounded p-2" type="button" data-bs-toggle="modal" data-bs-target="#exampleModal">
                        <i class="fas fa-plus-circle me-2"></i>Add New Course
                    </button>
                    <div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 g-4">
                        <?php
                        mysqli_data_seek($resultShowCourses, 0);
                        while ($rowCourses = mysqli_fetch_assoc($resultShowCourses)) {
                            $courseID = $rowCourses['course_id'];
                            $courseName = $rowCourses['course_name'];
                            $courseImg = $rowCourses['course_img'];
                            $courseDuration = (int)$rowCourses['course_duration'];
                            $batchName = $rowCourses['batch_name'];
                            $startDate = $rowCourses['start_date'];
                            $endDate = $rowCourses['end_date'];
                            $capacity = (int)$rowCourses['capacity'];
                            $courseCode = $rowCourses['course_code']; 
                            $enrolledStudents = (int)$rowCourses['enrolled_students'];

                            $formattedStartDate = date('M j, Y', strtotime($startDate));
                            $formattedEndDate = $endDate ? date('M j, Y', strtotime($endDate)) : 'Ongoing';
                        
                            $hours = floor($courseDuration / 60);
                            $minutes = $courseDuration % 60;
                            $formattedDuration = sprintf("%dh %dm", $hours, $minutes);

                            $isAccessible = ($endDate >= date('Y-m-d') || $endDate === null);
                            $isFull = ($capacity > 0 && $enrolledStudents >= $capacity);
                            $isEnded = ($endDate !== null && $endDate < date('Y-m-d'));
                        ?>
                        <div class="col">
                            <div class="card h-100 shadow-sm dashboard-card <?= $isEnded ? 'bg-light' : ''; ?>">
                                <img src="<?= $courseImg; ?>" class="card-img-top course-img" alt="<?= $courseName; ?>">
                                <div class="card-body">
                                    <h5 class="card-title"><?= $courseName; ?></h5>
                                    <h6 class="card-subtitle mb-2 text-muted"><?= $courseCode; ?></h6>
                                    <p class="card-text">
                                        <small class="text-muted">
                                            <i class="fas fa-clock me-1"></i><?= $formattedDuration; ?><br>
                                            <i class="fas fa-users me-1"></i><?= $enrolledStudents; ?> / <?= $capacity ?: 'Unlimited'; ?> students
                                        </small>
                                    </p>
                                    <p class="card-text">
                                        <strong>Batch:</strong> <?= $batchName ?: 'No batch'; ?><br>
                                        <strong>Period:</strong> <?= $formattedStartDate; ?> - <?= $formattedEndDate; ?>
                                    </p>
                                    <div class="progress mb-2">
                                        <div class="progress-bar <?= $isFull ? 'bg-danger' : 'bg-success'; ?>" role="progressbar" 
                                             style="width: <?= $capacity ? ($enrolledStudents / $capacity * 100) : 100; ?>%"
                                             aria-valuenow="<?= $enrolledStudents; ?>" aria-valuemin="0" aria-valuemax="<?= $capacity ?: $enrolledStudents; ?>">
                                            <?= $enrolledStudents; ?> / <?= $capacity ?: 'Unlimited'; ?>
                                        </div>
                                    </div>
                                    <?php if ($isFull) { ?>
                                        <p class="text-danger mb-0"><strong>Course Full</strong></p>
                                    <?php } ?>
                                </div>
                                <div class="card-footer bg-transparent border-top-0 d-flex justify-content-between">
                                    <?php if ($isEnded) { ?>
                                        <button type="button" class="btn btn-danger btn-sm" data-bs-toggle="modal" data-bs-target="#deleteModal<?= $courseID; ?>">
                                            Delete Course
                                        </button>
                                    <?php } elseif ($isAccessible) { ?>
                                        <a href="?page=course-content&viewContent=<?= $courseID; ?>" class="btn btn-primary btn-sm" style="background-color: #0f6fc5; border-color: #0f6fc5;">View Course</a>
                                    <?php } elseif ($isFull) { ?>
                                        <button class="btn btn-secondary btn-sm" disabled>Course Full</button>
                                    <?php } else { ?>
                                        <span class="text-muted">Course Expired</span>
                                    <?php } ?>
                                    <button type="button" class="btn btn-outline-primary btn-sm" data-bs-toggle="modal" data-bs-target="#editModal<?= $courseID; ?>" style="color: #0f6fc5; border-color: #0f6fc5;">
                                        Edit Course
                                    </button>
                                </div>
                            </div>
                        </div>
                        <?php
                        }
                        ?>

                        <?php if (mysqli_num_rows($resultShowCourses) === 0) { ?>
                            <div class="col">
                                <div class="card h-100 shadow-sm">
                                    <div class="card-body text-center">
                                        <h5 class="card-title">No courses available</h5>
                                        <p class="card-text text-muted">You have not created any courses yet.</p>
                                        <button class="btn btn-primary" type="button" data-bs-toggle="modal" data-bs-target="#exampleModal">
                                            Create a New Course
                                        </button>
                                    </div>
                                </div>
                            </div>
                        <?php } ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row mb-4">
        <div class="col-md-6">
            <div class="card dashboard-card h-100">
                <div class="card-header modified-bg-primary">
                    <label class="fs-5 text-light fw-semibold">Course Analytics</label>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Course</th>
                                    <th>Students</th>
                                    <th>Completion</th>
                                    <th>Materials</th>
                                    <th>Tasks</th>
                                    <th>Quizzes</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while ($rowAnalytics = mysqli_fetch_assoc($resultCourseAnalytics)) { ?>
                                    <tr>
                                        <td><?= $rowAnalytics['course_name']; ?></td>
                                        <td><?= $rowAnalytics['enrolled_students']; ?></td>
                                        <td><?= number_format($rowAnalytics['completion_rate'], 2); ?>%</td>
                                        <td><?= $rowAnalytics['total_materials']; ?></td>
                                        <td><?= $rowAnalytics['total_task_sheets']; ?></td>
                                        <td><?= $rowAnalytics['total_quizzes']; ?></td>
                                    </tr>
                                <?php } ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card dashboard-card h-100">
                <div class="card-header modified-bg-primary">
                    <label class="fs-5 text-light fw-semibold">Recent Announcements</label>
                </div>
                <div class="card-body">
                    <div class="list-group">
                        <?php while ($rowAnnouncement = mysqli_fetch_assoc($resultRecentAnnouncements)) { ?>
                            <div class="list-group-item list-group-item-action">
                                <div class="d-flex w-100 justify-content-between">
                                    <h5 class="mb-1"><?= $rowAnnouncement['title']; ?></h5>
                                    <small><?= date('M j, Y', strtotime($rowAnnouncement['created_at'])); ?></small>
                                </div>
                                <p class="mb-1"><?= $rowAnnouncement['content']; ?></p>
                                <small>Course: <?= $rowAnnouncement['course_name']; ?></small>
                            </div>
                        <?php } ?>

                        <?php if (mysqli_num_rows($resultRecentAnnouncements) === 0) { ?>
                            <div class="list-group-item list-group-item-action">
                                <p class="text-muted">No recent announcements.</p>
                            </div>
                        <?php } ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row mb-4">
        <div class="col-md-6">
            <div class="card dashboard-card h-100">
                <div class="card-header modified-bg-primary">
                    <label class="fs-5 text-light fw-semibold">Recent Discussion Activity</label>
                </div>
                <div class="card-body"></div>
                    <div class="list-group">
                        <?php while ($rowDiscussion = mysqli_fetch_assoc($resultRecentDiscussions)) { ?>
                            <div class="list-group-item list-group-item-action">
                                <div class="d-flex w-100 justify-content-between">
                                    <h6 class="mb-1"><?= $rowDiscussion['fullname']; ?></h6>
                                    <small><?= date('M j, Y H:i', strtotime($rowDiscussion['created_at'])); ?></small>
                                </div>
                                <p class="mb-1"><?= $rowDiscussion['message']; ?></p>
                                <small>Course: <?= $rowDiscussion['course_name']; ?></small>
                            </div>
                        <?php } ?>

                        <?php if (mysqli_num_rows($resultRecentDiscussions) === 0) { ?> 
                            <div class="list-group list-group-item-action">
                                <p class="text-muted">No recent discussion activity.</p>
                            </div>
                        <?php } ?>

                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card dashboard-card h-100">
                <div class="card-header modified-bg-primary">
                    <label class="fs-5 text-light fw-semibold">Pending Course Registrations</label>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Student Name</th>
                                    <th>Course</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while ($rowRegistration = mysqli_fetch_assoc($resultPendingRegistrations)) { ?>
                                    <tr>
                                        <td><?= $rowRegistration['fullname']; ?></td>
                                        <td><?= $rowRegistration['course_name']; ?></td>
                                        <td>
                                            <a href="?page=i_students" class="btn btn-sm btn-success">View</a>
                                        </td>
                                    </tr>
                                <?php } ?>

                                <?php if (mysqli_num_rows($resultPendingRegistrations) === 0) { ?>
                                    <tr>
                                        <td colspan="3" class="text-muted">No pending registrations.</td>
                                    </tr>
                                <?php } ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal -->
<div class="modal fade" id="exampleModal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header modified-bg-primary text-white">
                <h5 class="modal-title" id="staticBackdropLabel">Add a New Course</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form method="post" enctype="multipart/form-data">
                    <div class="row">
                        <div class="col-md-6">
                            <h6 class="mb-3">Course Details</h6>
                            <div class="mb-3">
                                <label class="form-label">Course Code</label>
                                <input type="text" class="form-control" placeholder="Ex. ABC123" name="courseCode" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Course Name</label>
                                <input type="text" class="form-control" placeholder="Enter course name" name="courseName" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Duration</label>
                                <div class="row">
                                    <div class="col-6">
                                        <input type="number" class="form-control" placeholder="Hours" name="courseDurationHours" min="0" required>
                                    </div>
                                    <div class="col-6">
                                        <input type="number" class="form-control" placeholder="Minutes" name="courseDurationMinutes" min="0" max="59">
                                    </div>
                                </div>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Course Photo</label>
                                <input type="file" class="form-control" name="coursePhoto" accept="image/*" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <h6 class="mb-3">Batch Details</h6>
                            <div class="mb-3">
                                <label class="form-label">Batch Name</label>
                                <input type="text" class="form-control" placeholder="Ex. Batch 1 - 2024" name="batchName" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Start Date</label>
                                <input type="date" class="form-control" name="startDate" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">End Date</label>
                                <input type="date" class="form-control" name="endDate">
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Capacity</label>
                                <input type="number" class="form-control" name="capacity" min="1" required>
                            </div>
                        </div>
                    </div>
                    <div class="text-center mt-4">
                        <button type="submit" class="button-primary p-2 rounded" name="addCourse">Add Course</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Toast Container -->
<div class="position-fixed top-0 end-0 p-3" style="z-index: 11">
    <div id="liveToast" class="toast" role="alert" aria-live="assertive" aria-atomic="true">
        <div class="toast-body text-bg-success">
            <?php echo $toastMessage; ?>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        var toastMessage = "<?php echo $toastMessage; ?>";
        if (toastMessage) {
            var toastElement = document.getElementById('liveToast');
            var toast = new bootstrap.Toast(toastElement, {
                delay: 3000
            });
            toast.show();
        }
    });
</script>

<?php
mysqli_data_seek($resultShowCourses, 0);
while ($rowCourses = mysqli_fetch_assoc($resultShowCourses)) {
    $courseID = $rowCourses['course_id'];
    $courseName = $rowCourses['course_name'];
?>
    <!-- Delete Confirmation Modal -->
    <div class="modal fade" id="deleteModal<?= $courseID; ?>" tabindex="-1" aria-labelledby="deleteModalLabel<?= $courseID; ?>" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header" style="background-color: #0f6fc5; color: white;">
                    <h5 class="modal-title" id="deleteModalLabel<?= $courseID; ?>">Confirm Deletion</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    Are you sure you want to delete the course "<?= $courseName; ?>"?
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <form method="post">
                        <input type="hidden" name="courseId" value="<?= $courseID; ?>">
                        <button type="submit" name="deleteCourse" class="btn btn-danger">Delete Course</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Edit Course Modal -->
    <div class="modal fade" id="editModal<?= $courseID; ?>" tabindex="-1" aria-labelledby="editModalLabel<?= $courseID; ?>" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header" style="background-color: #0f6fc5; color: white;">
                    <h5 class="modal-title" id="editModalLabel<?= $courseID; ?>">Edit Course</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form method="post" enctype="multipart/form-data">
                        <input type="hidden" name="courseId" value="<?= $courseID; ?>">
                        <div class="mb-3">
                            <label class="form-label">Course Name</label>
                            <input type="text" class="form-control" name="editCourseName" value="<?= $courseName; ?>" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Course Code</label>
                            <input type="text" class="form-control" name="editCourseCode" value="<?= $rowCourses['course_code']; ?>" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Duration (in minutes)</label>
                            <input type="number" class="form-control" name="editCourseDuration" value="<?= $rowCourses['course_duration']; ?>" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Course Photo</label>
                            <input type="file" class="form-control" name="editCoursePhoto" accept="image/*">
                        </div>
                        <div class="text-center mt-4">
                            <button type="submit" class="btn btn-primary" name="editCourse" style="background-color: #0f6fc5; border-color: #0f6fc5;">Save Changes</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
<?php
}
?>
