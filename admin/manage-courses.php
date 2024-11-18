<?php
// Fetch all courses with instructor information and student count
$query = "SELECT c.*, u.fullname AS instructor_name, COUNT(e.enrollment_id) AS enrolled_students 
          FROM courses c 
          JOIN users u ON c.user_id = u.user_id 
          LEFT JOIN enrollments e ON c.course_id = e.course_id
          GROUP BY c.course_id
          ORDER BY c.course_id DESC";
$result = mysqli_query($connect, $query);

// Handle course deletion
if (isset($_POST['delete_course'])) {
    $course_id = mysqli_real_escape_string($connect, $_POST['course_id']);
    $delete_query = "DELETE FROM courses WHERE course_id = '$course_id'";
    if (mysqli_query($connect, $delete_query)) {
        $_SESSION['success'] = "Course deleted successfully.";
    } else {
        $_SESSION['error'] = "Error deleting course: " . mysqli_error($connect);
    }
    echo "<script>location.href = 'main.php?page=manage-courses'</script>";
    exit();
}

if (isset($_POST['add_course'])) {
    $course_name = mysqli_real_escape_string($connect, $_POST['course_name']);
    $course_photo = $_FILES['course_photo'];
    $course_duration_hours = isset($_POST['course_duration_hours']) ? (int)$_POST['course_duration_hours'] : 0;
    $course_duration_minutes = isset($_POST['course_duration_minutes']) ? (int)$_POST['course_duration_minutes'] : 0;
    $batch_name = mysqli_real_escape_string($connect, $_POST['batch_name']);
    $start_date = mysqli_real_escape_string($connect, $_POST['start_date']);
    $end_date = isset($_POST['end_date']) ? mysqli_real_escape_string($connect, $_POST['end_date']) : null;
    $capacity = isset($_POST['capacity']) ? (int)$_POST['capacity'] : 0;
    $instructor_id = mysqli_real_escape_string($connect, $_POST['instructor_id']);

    // Convert course duration to total minutes
    $total_minutes = ($course_duration_hours * 60) + $course_duration_minutes;

    // Check if course code is provided, if not generate a unique one
    $course_code = isset($_POST['course_code']) ? $_POST['course_code'] : strtoupper(substr(md5(uniqid(mt_rand(), true)), 0, 6));

    // Handle file upload
    if (isset($course_photo)) {
        $fileTempPath = $course_photo['tmp_name'];
        $fileName = $course_photo['name'];
        $fileSize = $course_photo['size'];
        $fileType = $course_photo['type'];
        $fileNameComponents = explode(".", $fileName);
        $fileExtension = strtolower(end($fileNameComponents));
        // Define allowed file extensions
        $allowedFileExtensions = array('jpg', 'png', 'jpeg');
        if (in_array($fileExtension, $allowedFileExtensions)) {
            $newFileName = md5(time() . $fileName) . '.' . $fileExtension;
            $targetDir = '../uploads/course-photo/';
            $targetFilePath = $targetDir . $newFileName;
            if (move_uploaded_file($fileTempPath, $targetFilePath)) {
                $course_img = $targetFilePath;
                $sqlAddCourse = "INSERT INTO courses (user_id, course_name, course_img, course_duration, course_code) 
                                 VALUES ('$instructor_id', '$course_name', '$course_img', '$total_minutes', '$course_code')";
                $resultAddCourse = mysqli_query($connect, $sqlAddCourse);

                if ($resultAddCourse) {
                    // Get the ID of the newly inserted course
                    $course_id = mysqli_insert_id($connect);

                    // Insert batch into the database
                    $sqlAddBatch = "INSERT INTO batches (course_id, batch_name, start_date, end_date, capacity) 
                                    VALUES ('$course_id', '$batch_name', '$start_date', '$end_date', '$capacity')";
                    $resultAddBatch = mysqli_query($connect, $sqlAddBatch);

                    if($resultAddBatch){
                        $_SESSION['success'] = "Course and Batch added successfully.";
                    } else {
                        $_SESSION['error'] = "Error adding batch: " . mysqli_error($connect);
                    }
                } else {
                    $_SESSION['error'] = "Error adding course: " . mysqli_error($connect);
                }
            } else {
                $_SESSION['error'] = "There was an error moving the uploaded file.";
            }
        } else {
            $_SESSION['error'] = "Upload failed. Allowed file types: " . implode(', ', $allowedFileExtensions);
        }
    } else {
        $_SESSION['error'] = "There was an error uploading the file.";
    }
    echo "<script>location.href = 'main.php?page=manage-courses'</script>";

}

// Handle course edit
if (isset($_POST['edit_course'])) {
    $course_id = mysqli_real_escape_string($connect, $_POST['course_id']);
    $course_name = mysqli_real_escape_string($connect, $_POST['course_name']);
    $course_code = mysqli_real_escape_string($connect, $_POST['course_code']);
    $course_duration_hours = isset($_POST['course_duration_hours']) ? (int)$_POST['course_duration_hours'] : 0;
    $course_duration_minutes = isset($_POST['course_duration_minutes']) ? (int)$_POST['course_duration_minutes'] : 0;
    $instructor_id = mysqli_real_escape_string($connect, $_POST['instructor_id']);
    $batch_name = mysqli_real_escape_string($connect, $_POST['batch_name']);
    $start_date = mysqli_real_escape_string($connect, $_POST['start_date']);
    $end_date = isset($_POST['end_date']) ? mysqli_real_escape_string($connect, $_POST['end_date']) : null;
    $capacity = isset($_POST['capacity']) ? (int)$_POST['capacity'] : 0;

    $total_minutes = ($course_duration_hours * 60) + $course_duration_minutes;

    // Handle file upload if a new photo is provided
    if (!empty($_FILES['course_photo']['name'])) {
        $course_photo = $_FILES['course_photo'];
        $fileTempPath = $course_photo['tmp_name'];
        $fileName = $course_photo['name'];
        $fileSize = $course_photo['size'];
        $fileType = $course_photo['type'];
        $fileNameComponents = explode(".", $fileName);
        $fileExtension = strtolower(end($fileNameComponents));
        $allowedFileExtensions = array('jpg', 'png', 'jpeg');
        if (in_array($fileExtension, $allowedFileExtensions)) {
            $newFileName = md5(time() . $fileName) . '.' . $fileExtension;
            $targetDir = '../uploads/course-photo/';
            $targetFilePath = $targetDir . $newFileName;
            if (move_uploaded_file($fileTempPath, $targetFilePath)) {
                $course_img = $targetFilePath;
                $update_query = "UPDATE courses SET course_name = '$course_name', course_code = '$course_code', 
                                 course_duration = '$total_minutes', user_id = '$instructor_id', course_img = '$course_img' 
                                 WHERE course_id = '$course_id'";
            } else {
                $_SESSION['error'] = "There was an error uploading the new course photo.";
                echo "<script>location.href = 'main.php?page=manage-courses'</script>";
                exit();
            }
        } else {
            $_SESSION['error'] = "Upload failed. Allowed file types: " . implode(', ', $allowedFileExtensions);
            echo "<script>location.href = 'main.php?page=manage-courses'</script>";
            exit();
        }
    } else {
        $update_query = "UPDATE courses SET course_name = '$course_name', course_code = '$course_code', 
                         course_duration = '$total_minutes', user_id = '$instructor_id' 
                         WHERE course_id = '$course_id'";
    }

    if (mysqli_query($connect, $update_query)) {
        // Update batch information
        $update_batch_query = "UPDATE batches SET batch_name = '$batch_name', start_date = '$start_date', 
                               end_date = " . ($end_date ? "'$end_date'" : "NULL") . ", capacity = '$capacity' 
                               WHERE course_id = '$course_id'";
        if (mysqli_query($connect, $update_batch_query)) {
            $_SESSION['success'] = "Course and batch updated successfully.";
        } else {
            $_SESSION['error'] = "Error updating batch: " . mysqli_error($connect);
        }
    } else {
        $_SESSION['error'] = "Error updating course: " . mysqli_error($connect);
    }
    echo "<script>location.href = 'main.php?page=manage-courses';</script>";
    exit();
}

// Fetch instructors for the dropdown
$instructors_query = "SELECT user_id, fullname FROM users WHERE role = 'instructor'";
$instructors_result = mysqli_query($connect, $instructors_query);

?>

<div class="container mt-4">
    <h2>Manage Courses</h2>
    <hr>
    
    <!-- Add Course Modal Button -->
    <button type="button" class="button-primary p-2 rounded mb-3" data-toggle="modal" data-target="#addCourseModal">
        Add New Course
    </button>
    
    <?php
    if (isset($_SESSION['success'])) {
        echo "<div class='alert alert-success'>" . $_SESSION['success'] . "</div>";
        unset($_SESSION['success']);
    }
    if (isset($_SESSION['error'])) {
        echo "<div class='alert alert-danger'>" . $_SESSION['error'] . "</div>";
        unset($_SESSION['error']);
    }
    ?>

    <!-- Add Course Modal -->
    <div class="modal fade" id="addCourseModal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content">
                <div class="modal-header modified-bg-primary text-white">
                    <h5 class="modal-title" id="staticBackdropLabel">Add a New Course</h5>
                    <button type="button" class="btn-close btn-close-white" data-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form method="post" enctype="multipart/form-data">
                        <div class="row">
                            <div class="col-md-6">
                                <h6 class="mb-3">Course Details</h6>
                                <div class="mb-3">
                                    <label class="form-label">Course Code</label>
                                    <input type="text" class="form-control" placeholder="Ex. ABC123" name="course_code" required>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Course Name</label>
                                    <input type="text" class="form-control" placeholder="Enter course name" name="course_name" required>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Duration</label>
                                    <div class="row">
                                        <div class="col-6">
                                            <input type="number" class="form-control" placeholder="Hours" name="course_duration_hours" min="0" required>
                                        </div>
                                        <div class="col-6">
                                            <input type="number" class="form-control" placeholder="Minutes" name="course_duration_minutes" min="0" max="59">
                                        </div>
                                    </div>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Course Photo</label>
                                    <input type="file" class="form-control" name="course_photo" accept="image/*" required>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Instructor</label>
                                    <select class="form-control" name="instructor_id" required>
                                        <option value="">Select Instructor</option>
                                        <?php while ($instructor = mysqli_fetch_assoc($instructors_result)) : ?>
                                            <option value="<?php echo $instructor['user_id']; ?>"><?php echo htmlspecialchars($instructor['fullname']); ?></option>
                                        <?php endwhile; ?>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <h6 class="mb-3">Batch Details</h6>
                                <div class="mb-3">
                                    <label class="form-label">Batch Name</label>
                                    <input type="text" class="form-control" placeholder="Ex. Batch 1 - 2024" name="batch_name" required>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Start Date</label>
                                    <input type="date" class="form-control" name="start_date" required>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">End Date</label>
                                    <input type="date" class="form-control" name="end_date">
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Capacity</label>
                                    <input type="number" class="form-control" name="capacity" min="1" required>
                                </div>
                            </div>
                        </div>
                        <div class="text-center mt-4">
                            <button type="submit" class="btn btn-primary" name="add_course">Add Course</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <div class="table-responsive">
        <table class="table table-striped table-hover">
            <thead class="thead-dark">
                <tr>
                    <th>Course Name</th>
                    <th>Instructor</th>
                    <th>Course Code</th>
                    <th>Duration (hours)</th>
                    <th>Enrolled Students</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = mysqli_fetch_assoc($result)) : ?>
                    <tr>
                        <td><?php echo htmlspecialchars($row['course_name']); ?></td>
                        <td><?php echo htmlspecialchars($row['instructor_name']); ?></td>
                        <td><?php echo htmlspecialchars($row['course_code']); ?></td>
                        <td><?php echo htmlspecialchars($row['course_duration']); ?></td>
                        <td><?php echo htmlspecialchars($row['enrolled_students']); ?></td>
                        <td>
                            <div class="btn-group" role="group">
                                <a href="view-course.php?id=<?php echo $row['course_id']; ?>" class="btn btn-sm btn-info">View</a>
                                <button type="button" class="btn btn-sm btn-warning" data-toggle="modal" data-target="#editCourseModal<?php echo $row['course_id']; ?>">Edit</button>
                                <!-- Button trigger modal -->
                                <button type="button" class="btn btn-sm btn-danger" data-bs-toggle="modal" data-bs-target="#exampleModal">
                                Delete
                                </button>
                            </div>
                        </td>
                    </tr>

                    
                    <!-- Delete Modal -->
                    <div class="modal fade" id="exampleModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
                    <div class="modal-dialog modal-dialog-centered">
                        <div class="modal-content">
                        <div class="modal-header">
                            <h1 class="modal-title fs-5" id="exampleModalLabel">Delete</h1>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <p>Are you sure you want to delete this course?</p>
                            <form method="POST" class="d-inline">
                                    <input type="hidden" name="course_id" value="<?php echo $row['course_id']; ?>">
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                        <button type="submit" name="delete_course" class="btn btn-danger">Delete</button>
                                    </div>
                            </form>
                        </div>
                        </div>
                    </div>
                    </div>

                    <!-- Edit Course Modal -->
                    <div class="modal fade" id="editCourseModal<?php echo $row['course_id']; ?>" tabindex="-1" role="dialog" aria-labelledby="editCourseModalLabel<?php echo $row['course_id']; ?>" aria-hidden="true">
                        <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
                            <div class="modal-content">
                                <div class="modal-header modified-bg-primary text-white">
                                    <h5 class="modal-title" id="editCourseModalLabel<?php echo $row['course_id']; ?>">Edit Course</h5>
                                    <button type="button" class="btn-close btn-close-white" data-dismiss="modal" aria-label="Close"></button>
                                </div>
                                <form method="POST" enctype="multipart/form-data">
                                    <div class="modal-body">
                                        <input type="hidden" name="course_id" value="<?php echo $row['course_id']; ?>">
                                        <div class="row">
                                            <div class="col-md-6">
                                                <h6 class="mb-3">Course Details</h6>
                                                <div class="form-group">
                                                    <label for="course_name">Course Name</label>
                                                    <input type="text" class="form-control" id="course_name" name="course_name" value="<?php echo htmlspecialchars($row['course_name']); ?>" required>
                                                </div>
                                                <div class="form-group">
                                                    <label for="course_code">Course Code</label>
                                                    <input type="text" class="form-control" id="course_code" name="course_code" value="<?php echo htmlspecialchars($row['course_code']); ?>" required>
                                                </div>
                                                <div class="form-group">
                                                    <label for="course_duration">Duration</label>
                                                    <div class="row">
                                                        <div class="col-6">
                                                            <input type="number" class="form-control" id="course_duration_hours" name="course_duration_hours" min="0" value="<?php echo floor($row['course_duration'] / 60); ?>" required>
                                                        </div>
                                                        <div class="col-6">
                                                            <input type="number" class="form-control" id="course_duration_minutes" name="course_duration_minutes" min="0" max="59" value="<?php echo $row['course_duration'] % 60; ?>">
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="form-group">
                                                    <label for="course_photo">Course Photo</label>
                                                    <input type="file" class="form-control-file" id="course_photo" name="course_photo" accept="image/*">
                                                    <small class="form-text text-muted">Leave empty to keep the current photo.</small>
                                                </div>
                                                <div class="form-group">
                                                    <label for="instructor_id">Instructor</label>
                                                    <select class="form-control" id="instructor_id" name="instructor_id" required>
                                                        <?php 
                                                        mysqli_data_seek($instructors_result, 0);
                                                        while ($instructor = mysqli_fetch_assoc($instructors_result)) : 
                                                        ?>
                                                            <option value="<?php echo $instructor['user_id']; ?>" <?php echo ($instructor['user_id'] == $row['user_id']) ? 'selected' : ''; ?>>
                                                                <?php echo htmlspecialchars($instructor['fullname']); ?>
                                                            </option>
                                                        <?php endwhile; ?>
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <h6 class="mb-3">Batch Details</h6>
                                                <?php
                                                // Fetch batch details for this course
                                                $batch_query = "SELECT * FROM batches WHERE course_id = '{$row['course_id']}' LIMIT 1";
                                                $batch_result = mysqli_query($connect, $batch_query);
                                                $batch = mysqli_fetch_assoc($batch_result);
                                                ?>
                                                <div class="form-group">
                                                    <label for="batch_name">Batch Name</label>
                                                    <input type="text" class="form-control" id="batch_name" name="batch_name" value="<?php echo htmlspecialchars($batch['batch_name']); ?>" required>
                                                </div>
                                                <div class="form-group">
                                                    <label for="start_date">Start Date</label>
                                                    <input type="date" class="form-control" id="start_date" name="start_date" value="<?php echo htmlspecialchars($batch['start_date']); ?>" required>
                                                </div>
                                                <div class="form-group">
                                                    <label for="end_date">End Date</label>
                                                    <input type="date" class="form-control" id="end_date" name="end_date" value="<?php echo htmlspecialchars($batch['end_date']); ?>">
                                                </div>
                                                <div class="form-group">
                                                    <label for="capacity">Capacity</label>
                                                    <input type="number" class="form-control" id="capacity" name="capacity" min="1" value="<?php echo htmlspecialchars($batch['capacity']); ?>" required>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                                        <button type="submit" name="edit_course" class="button-primary p-2 rounded">Save changes</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Make sure to include Bootstrap JS and jQuery for the modal to work -->
<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.3/dist/umd/popper.min.js"></script>
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>



