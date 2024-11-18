<div class="container">
    <?php
    include_once '../functions/logActivity.php';
    $courseID = $_GET['viewContent'];
    $studentID = $_SESSION['userID'];

    // Get users current info
    $sql = "SELECT * FROM users WHERE user_id = '$studentID'";
    $result = mysqli_query($connect, $sql);
    $users = mysqli_fetch_assoc($result);

    // Assuming you have the full name from the form input
    $fullName = $users['fullname'];

    // Split the full name into parts
    $nameParts = explode(' ', $fullName);

    // Initialize variables
    $firstName = '';
    $middleName = '';
    $lastName = '';

    // Determine the number of parts in the name
    $numParts = count($nameParts);

    if ($numParts == 1) {
        // Only one part, assume it's the first name
        $firstName = $nameParts[0];
    } elseif ($numParts == 2) {
        // Two parts, assume it's first name and last name
        $firstName = $nameParts[0];
        $lastName = $nameParts[1];
    } else {
        // More than two parts
        $firstName = implode(' ', array_slice($nameParts, 0, $numParts - 2));
        $middleName = $nameParts[$numParts - 2];
        $lastName = $nameParts[$numParts - 1];
    }


    if (isset($_POST['registerButton'])) {
        // Process form submission
        $courseID = mysqli_real_escape_string($connect, $_POST['courseID']);
        $studentID = $_SESSION['userID'];

        // Check if a registration already exists
        $checkExistingQuery = "SELECT registration_id, status FROM course_registrations 
                               WHERE course_id = ? AND student_id = ?";
        $stmt = mysqli_prepare($connect, $checkExistingQuery);
        mysqli_stmt_bind_param($stmt, "ii", $courseID, $studentID);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);

        if ($row = mysqli_fetch_assoc($result)) {
            $existingRegistrationId = $row['registration_id'];
            $existingStatus = $row['status'];

            // If status is 'declined' or 'resubmit', allow update
            if ($existingStatus == 'declined' || $existingStatus == 'resubmit') {
                $action = "UPDATE";
            } else {
                // Registration exists and is not in a state that allows resubmission
                $_SESSION['errorMsg'] = 'You have already registered for this course.';
                // Redirect or exit to prevent further processing
                exit();
            }
        } else {
            $action = "INSERT";
        }

        // Sanitize and collect form data
        $tvlName = mysqli_real_escape_string($connect, $_POST['tvl_name']);
        $scholarshipType = mysqli_real_escape_string($connect, $_POST['scholarship_type']);
        $trainerName = mysqli_real_escape_string($connect, $_POST['trainer_name']);
        $trainingSchedule = mysqli_real_escape_string($connect, $_POST['training_schedule']);
        $firstName = mysqli_real_escape_string($connect, $_POST['firstName']);
        $middleName = mysqli_real_escape_string($connect, $_POST['middleName']);
        $middleInitial = mysqli_real_escape_string($connect, $_POST['middleInitial']);
        $lastName = mysqli_real_escape_string($connect, $_POST['lastName']);
        $extension = mysqli_real_escape_string($connect, $_POST['extension']);
        $dateOfBirth = mysqli_real_escape_string($connect, $_POST['dateOfBirth']);
        $placeOfBirth = mysqli_real_escape_string($connect, $_POST['placeOfBirth']);
        $civilStatus = mysqli_real_escape_string($connect, $_POST['civilStatus']);
        $sex = mysqli_real_escape_string($connect, $_POST['sex']);
        $mobileNumber = mysqli_real_escape_string($connect, $_POST['mobileNumber']);
        $emailAddress = mysqli_real_escape_string($connect, $_POST['emailAddress']);
        $educationAttainment = mysqli_real_escape_string($connect, $_POST['educationAttainment']);
        $isDisabled = mysqli_real_escape_string($connect, $_POST['isDisabled']);
        $disabilityType = $isDisabled == '1' ? mysqli_real_escape_string($connect, $_POST['disabilityType']) : '';
        $reason = mysqli_real_escape_string($connect, $_POST['reason']);

        // Handle file uploads
        $picturePath = '';
        $birthCertificatePath = '';
        if (isset($_FILES['pic']) && $_FILES['pic']['error'] == 0) {
            $picturePath = '../uploads/course-registrations/2x2-pic/' . uniqid() . '_' . $_FILES['pic']['name'];
            move_uploaded_file($_FILES['pic']['tmp_name'], $picturePath);
        }
        if (isset($_FILES['birthCertificate']) && $_FILES['birthCertificate']['error'] == 0) {
            $birthCertificatePath = '../uploads/course-registrations/birth-certificate/' . uniqid() . '_' . $_FILES['birthCertificate']['name'];
            move_uploaded_file($_FILES['birthCertificate']['tmp_name'], $birthCertificatePath);
        }

        if ($action == "INSERT") {
            // Insert new registration
            $sql = "INSERT INTO course_registrations (
                course_id, student_id, tvl_name, scholarship_type, trainer_name, training_schedule,
                first_name, middle_name, middle_initial, last_name, extension,
                date_of_birth, place_of_birth, civil_status, sex, mobile_number,
                email_address, highest_education_attainment, is_pwd, disability_type, reason,
                pic_path, birthCert_path, status, created_at
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'pending', NOW())";
        } else {
            // Update existing registration
            $sql = "UPDATE course_registrations SET
                tvl_name = ?, scholarship_type = ?, trainer_name = ?, training_schedule = ?,
                first_name = ?, middle_name = ?, middle_initial = ?, last_name = ?, extension = ?,
                date_of_birth = ?, place_of_birth = ?, civil_status = ?, sex = ?, mobile_number = ?,
                email_address = ?, highest_education_attainment = ?, is_pwd = ?, disability_type = ?, reason = ?,
                pic_path = ?, birthCert_path = ?, status = 'pending', updated_at = NOW()
                WHERE registration_id = ?";
        }

        $stmt = mysqli_prepare($connect, $sql);

        if ($action == "INSERT") {
            mysqli_stmt_bind_param($stmt, "iissssssssssssssssissss", 
                $courseID, $studentID, $tvlName, $scholarshipType, $trainerName, $trainingSchedule,
                $firstName, $middleName, $middleInitial, $lastName, $extension,
                $dateOfBirth, $placeOfBirth, $civilStatus, $sex, $mobileNumber,
                $emailAddress, $educationAttainment, $isDisabled, $disabilityType, $reason,
                $picturePath, $birthCertificatePath);
        } else {
            mysqli_stmt_bind_param($stmt, "ssssssssssssssssissssi", 
                $tvlName, $scholarshipType, $trainerName, $trainingSchedule,
                $firstName, $middleName, $middleInitial, $lastName, $extension,
                $dateOfBirth, $placeOfBirth, $civilStatus, $sex, $mobileNumber,
                $emailAddress, $educationAttainment, $isDisabled, $disabilityType, $reason,
                $picturePath, $birthCertificatePath, $existingRegistrationId);
        }

        if (mysqli_stmt_execute($stmt)) {
            $_SESSION['successMsg'] = 'Registration submitted successfully. Please wait for approval.';
        } else {
            $_SESSION['errorMsg'] = 'Registration failed. Please try again.';
        }

        mysqli_stmt_close($stmt);
    }

    // Helper function to get ID 

    // Query to count total materials
    $sqlCount = "SELECT COUNT(material_file) as total FROM course_material WHERE course_id = '$courseID'";
    $resultCount = mysqli_query($connect, $sqlCount);
    $totalMaterials = 0;
    if ($resultCount && $rowCount = mysqli_fetch_assoc($resultCount)) {
        $totalMaterials = $rowCount['total'];
    }

    // Query to count total quizzes
    $sqlQuizCount = "SELECT COUNT(*) as total FROM quiz WHERE course_id = '$courseID'";
    $resultQuizCount = mysqli_query($connect, $sqlQuizCount);
    $totalQuizzes = 0;
    if ($resultQuizCount && $rowQuizCount = mysqli_fetch_assoc($resultQuizCount)) {
        $totalQuizzes = $rowQuizCount['total'];
    }

    // Calculate total content (materials + quizzes)
    $totalContent = $totalMaterials + $totalQuizzes;

    // Query to check if the student is registered
    $sqlCheckRegistration = "SELECT * FROM course_registrations WHERE course_id = '$courseID' AND student_id = '$studentID'";
    $resultCheckRegistration = mysqli_query($connect, $sqlCheckRegistration);
    $isRegistered = mysqli_num_rows($resultCheckRegistration) > 0;

    // Query to get course details and batch information
    $sqlCourseDetails = "SELECT c.*, b.batch_id, b.batch_name, b.start_date, b.end_date, b.capacity,
                    (SELECT COUNT(*) FROM enrollments WHERE batch_id = b.batch_id) as enrolled_count,
                    CASE 
                        WHEN b.end_date < CURDATE() THEN 'Completed'
                        WHEN b.start_date > CURDATE() THEN 'Upcoming'
                        ELSE 'Ongoing'
                    END as course_status
                    FROM courses c
                    LEFT JOIN batches b ON c.course_id = b.course_id
                    WHERE c.course_id = '$courseID'";
    $resultCourseDetails = mysqli_query($connect, $sqlCourseDetails);

    // Combined query to get course materials and quizzes in order of addition
    $sqlShowCourses = "SELECT id, title, description, file, type, created_at 
                   FROM (
                        SELECT material_id AS id,
                               material_title AS title,
                               material_desc AS description,
                               material_file AS file,
                               'Material' AS type,
                               created_at
                        FROM course_material WHERE course_id = '$courseID'
                        UNION ALL
                        SELECT quiz_id AS id,
                               quiz_name AS title,
                               quiz_description AS description,
                               '' AS file,
                               'Quiz' AS type,
                               created_at
                        FROM quiz WHERE course_id = '$courseID'
                        UNION ALL
                        SELECT task_sheet_id AS id,
                               task_sheet_title AS title,
                               task_sheet_description AS description,
                               task_sheet_file AS file,
                               'Task Sheet' AS type,
                               created_at
                        FROM task_sheets WHERE course_id = '$courseID'
                        UNION ALL
                       SELECT assessment_id AS id,
                        assessment_title AS title,
                        assessment_description AS description,
                        '' AS file,
                        CASE WHEN assessment_type = 'pre' THEN 'Pre-assessment' ELSE 'Post-assessment' END AS type,
                        created_at
                        FROM assessments WHERE course_id = '$courseID'
                   ) AS combined_content
                   ORDER BY created_at ASC";

    $resultShowCourses = mysqli_query($connect, $sqlShowCourses);

    if (!$resultShowCourses) {
        die("Query failed: " . mysqli_error($connect));
    }

    if(isset($_POST['enrollButton'])){
        $enteredCourseCode = $_POST['courseCode'];
        $batchID = $_POST['batchID'];

        // Query to validate the entered course code
        $sqlCourse = "SELECT course_id FROM courses WHERE course_code = '$enteredCourseCode'";
        $resultCourse = mysqli_query($connect, $sqlCourse);
        $validCourse = false;

        if ($rowCourse = mysqli_fetch_assoc($resultCourse)) {
            if ($rowCourse['course_id'] == $courseID) {
                $validCourse = true;
            }
        }

        if ($validCourse) {
            // Check if the batch is full
            $sqlCheckCapacity = "SELECT b.capacity, COUNT(e.enrollment_id) as enrolled_count 
                             FROM batches b 
                             LEFT JOIN enrollments e ON b.batch_id = e.batch_id 
                             WHERE b.batch_id = '$batchID' 
                             GROUP BY b.batch_id";
            $resultCheckCapacity = mysqli_query($connect, $sqlCheckCapacity);
            $capacityRow = mysqli_fetch_assoc($resultCheckCapacity);
            
            if ($capacityRow['enrolled_count'] < $capacityRow['capacity']) {
                // Insert enrollment record
                $sqlEnroll = "INSERT INTO enrollments (user_id, course_id, batch_id, status) VALUES ('$studentID', '$courseID', '$batchID', 'In Progress')";
                $resultEnroll = mysqli_query($connect, $sqlEnroll);

                $action = 'Enroll';
                $description = 'Student enrolled in a course.';

                if ($resultEnroll) {
                   if(logActivity($studentID, $action, $description)){
                    echo "<script>window.location.href = 's_main.php?page=s_course_content&viewContent={$courseID}';</script>";
                    exit();
                   }
                    
                } else {
                    echo "Error: " . mysqli_error($connect);
                }
            } else {
                $errorMsg = "This batch has reached its maximum capacity. Enrollment is currently closed.";
            }
        } else {
            $errorMsg = "Invalid course code.";
        }
    }

    if ($courseRow = mysqli_fetch_assoc($resultCourseDetails)) {
        $courseName = $courseRow['course_name'];
        $courseImg = $courseRow['course_img'];
        $courseDesc = $courseRow['course_desc']; 
        $courseDuration = $courseRow['course_duration']; 
        $batchId = $courseRow['batch_id'];
        $batchName = $courseRow['batch_name'];
        $batchCapacity = $courseRow['capacity'];
        $enrolledCount = $courseRow['enrolled_count'];
        $courseStatus = $courseRow['course_status'];
        
        $hours = floor($courseDuration / 60);
        $minutes = $courseDuration % 60;
        $formattedDuration = sprintf("%d hours %d minutes", $hours, $minutes);

        // Fetch batches for this course
        $sqlShowBatches = "SELECT * FROM batches WHERE course_id = '$courseID' AND (end_date >= CURDATE() OR end_date IS NULL)";
        $resultShowBatches = mysqli_query($connect, $sqlShowBatches);

        // Query to check if the user is enrolled in the course
        $sqlCheckEnrollment = "SELECT * FROM enrollments WHERE user_id = '$studentID' AND course_id = '$courseID'";
        $resultCheckEnrollment = mysqli_query($connect, $sqlCheckEnrollment);
        $isEnrolled = mysqli_num_rows($resultCheckEnrollment) > 0;
        
        if($isEnrolled){
            $status = mysqli_fetch_assoc($resultCheckEnrollment);
            $enrollStatus = $status['status'];         
        }

        ?>

        <div class="cc-container mt-4">
            <div class="course-content">
                <div class="course-image">
                    <img src="<?= $courseImg; ?>" alt="<?= htmlspecialchars($courseName); ?>">
                </div>
                <div class="course-details">
                    <h2><?= htmlspecialchars($courseName); ?></h2>
                    <p class="course-description"><?= htmlspecialchars($courseDesc); ?></p>
                    <div class="course-meta">
                        <div class="meta-item">
                            <i class="fa-solid fa-bookmark text-primary"></i>
                            <span><?= $totalMaterials; ?> Lectures</span>
                        </div>
                        <div class="meta-item">
                            <i class="fa-solid fa-book text-primary"></i>
                            <span><?= $totalQuizzes; ?> Quizzes</span>
                        </div>
                        <div class="meta-item">
                            <i class="fa-solid fa-clock text-primary"></i>
                            <span><?= $formattedDuration; ?></span>
                        </div>
                        <div class="meta-item">
                            <i class="fa-solid fa-info-circle text-primary"></i>
                            <span>Status: <?= $courseStatus; ?></span>
                        </div>
                    </div>
                    <?php if($isEnrolled && $enrollStatus === 'In Progress'): ?>
                        <div class="alert alert-warning mt-3" role="alert">
                            <strong>In Progress</strong> You are already enrolled in this course.
                        </div> 
                        <a href="s_main.php?page=s_course_content&viewContent=<?= $courseID; ?>" class="btn btn-primary btn-block">Go to Course</a>
                    <?php elseif($isEnrolled && $enrollStatus === 'Completed'): ?>
                        <div class="alert alert-success mt-3" role="alert">
                            <strong>Completed</strong> You already completed this course.
                        </div>
                    <?php elseif($enrolledCount >= $batchCapacity || $courseStatus === 'Completed'): ?>
                        <div class="alert alert-danger mt-3" role="alert">
                            <strong><?= $courseStatus === 'Completed' ? 'Course Completed' : 'Batch Full' ?></strong> - 
                            <?= $courseStatus === 'Completed' ? 'This course has ended.' : 'This batch has reached its maximum capacity.' ?>
                        </div>
                    <?php else: ?>
                        <?php if (!$isRegistered): ?>
                            <button type="button" class="btn btn-outline-primary btn-block" data-bs-toggle="modal" data-bs-target="#exampleModal" <?= $courseStatus === 'Completed' ? 'disabled' : '' ?>>
                                Register
                            </button>
                        <?php else: ?>
                            <?php
                            if(isset($_SESSION['successMsg'])){ ?>
                                <div class="alert alert-warning alert-dismissible fade show" role="alert">
                                    <?= $_SESSION['successMsg']; ?> 
                                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                                </div> 
                                <?php
                                unset($_SESSION['successMsg']);
                            } elseif(isset($_SESSION['errorMsg'])){ ?>
                                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                    <?= $_SESSION['errorMsg']; ?> 
                                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                                </div> 
                                <?php
                                unset($_SESSION['errorMsg']);
                            }


                            $registrationStatus = mysqli_fetch_assoc($resultCheckRegistration)['status'];
                            if ($registrationStatus === 'pending'):
                            ?>
                                <div class="alert alert-info mt-3 d-flex justify-content-between align-items-center" role="alert">
                                    <strong>Pending</strong> - Registration is pending approval.
                                    <div class="spinner-border float-end" role="status">
                                     <span class="visually-hidden">Loading...</span>
                                    </div>
                                </div>
                                <?php elseif ($registrationStatus === 'declined'): ?>
                                    <div class="alert alert-danger mt-3" role="alert">
                                        <strong>Declined</strong> - Your registration has been declined.
                                    </div>
                                <?php elseif ($registrationStatus === 'resubmit'): ?>
                                    <div class="alert alert-danger mt-3" role="alert">
                                        <strong>Declined</strong> - Your registration has been declined.
                                    </div>
                                    <button type="button" class="btn btn-outline-primary btn-block" data-bs-toggle="modal" data-bs-target="#exampleModal" <?= $courseStatus === 'Completed' ? 'disabled' : '' ?>>
                                        Resubmit
                                    </button>
                                <?php elseif ($registrationStatus === 'approved'): ?>
                                    <form method="post" class="enrollment-form">
                                    <input type="hidden" name="courseID" value="<?= $courseID; ?>">              
                                    <input type="text" name="courseCode" class="form-control mb-2" placeholder="Course Code (ex. agri)" required>
                                    <select name="batchID" class="form-select mb-2" required>
                                        <option value="">Select a Batch</option>
                                        <?php while ($rowBatches = mysqli_fetch_assoc($resultShowBatches)): ?>
                                            <option value="<?= $rowBatches['batch_id']; ?>">
                                                <?= $rowBatches['batch_name']; ?> (<?= $rowBatches['start_date']; ?> - <?= $rowBatches['end_date']; ?>)
                                            </option>
                                        <?php endwhile; ?>
                                    </select>
                                    <input type="submit" class="btn btn-primary btn-block" value="Enroll" name="enrollButton">
                                </form>
                                <?php if(isset($errorMsg)): ?>
                                    <div class="alert alert-danger mt-3" role="alert">
                                        <?= htmlspecialchars($errorMsg); ?>
                                    </div>
                                <?php endif; ?>
                            <?php endif; ?>
                        <?php endif; ?>
                    <?php endif; ?>
                </div>
            </div>

            <div class="course-outline">
                <h3 class="outline-title">Course Outline</h3>
                <div class="outline-items">
                    <?php while ($row = mysqli_fetch_assoc($resultShowCourses)): 
                        $title = $row['title'];
                        $type = $row['type'];
                        $file = $row['file'];
                        
                        // Set icon based on content type and file extension
                        switch ($type) {
                            case 'Material':
                                $fileExtension = pathinfo($file, PATHINFO_EXTENSION);
                                switch (strtolower($fileExtension)) {
                                    case 'pdf':
                                        $icon = 'fa-solid fa-file-pdf';
                                        break;
                                    case 'doc':
                                    case 'docx':
                                        $icon = 'fa-solid fa-file-word';
                                        break;
                                    case 'xls':
                                    case 'xlsx':
                                        $icon = 'fa-solid fa-file-excel';
                                        break;
                                    case 'ppt':
                                    case 'pptx':
                                        $icon = 'fa-solid fa-file-powerpoint';
                                        break;
                                    case 'mp4':
                                    case 'avi':
                                    case 'mov':
                                        $icon = 'fa-solid fa-file-video';
                                        break;
                                    case 'mp3':
                                    case 'wav':
                                        $icon = 'fa-solid fa-file-audio';
                                        break;
                                    case 'jpg':
                                    case 'jpeg':
                                    case 'png':
                                    case 'gif':
                                        $icon = 'fa-solid fa-file-image';
                                        break;
                                    default:
                                        $icon = 'fa-solid fa-file';
                                }
                                break;
                            case 'Quiz':
                                $icon = 'fa-solid fa-question-circle';
                                break;
                            case 'Task Sheet':
                                $icon = 'fa-solid fa-list-check';
                                break;
                            case 'Pre-assessment':
                                $icon = 'fa-solid fa-list-check';
                                break;
                            case 'Post-assessment':
                                $icon = 'fa-solid fa-list-check';
                                break;
                            default:
                                $icon = 'fa-solid fa-circle-question';
                        }
                    ?>
                        <div class="outline-item">
                            <i class="<?= $icon; ?> text-secondary"></i>
                            <span><?= htmlspecialchars($title); ?></span>
                        </div>
                    <?php endwhile; ?>
                </div>
            </div>
        </div>
        <?php
    }
    ?>
</div>



<!-- Modal for Registration -->
<div class="modal fade" id="exampleModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-xl">
    <div class="modal-content">
      <div class="modal-header">
        <h1 class="modal-title fs-5" id="exampleModalLabel">Fill Out The Registration Form</h1>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <h5>Qualification of Training Applicants:</h5>
        <ul>
          <li>At least 18 years old</li>
          <li>High School Graduate / Senior High School Graduate</li>
          <li>Basic computer literacy</li>
          <li>Access to a computer with internet connection</li>
        </ul>
        <hr>
        <h5 class="mb-3">Registration Form</h5>
        <?php if($enrolledCount >= $batchCapacity || $courseStatus === 'Completed'): ?>
            <div class="alert alert-danger" role="alert">
                <strong><?= $courseStatus === 'Completed' ? 'Course Completed' : 'Batch Full' ?></strong> - 
                <?= $courseStatus === 'Completed' ? 'This course has ended. Registration is closed.' : 'This batch has reached its maximum capacity. Registration is currently closed.' ?>
            </div>
        <?php else: ?>
           
            <form method="post" enctype="multipart/form-data" action="">
                <input type="hidden" name="courseID" value="<?= $courseID; ?>">
                
                <div class="card mb-3">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0">Course Information</h5>
                    </div>
                    <div class="card-body">
                        <div class="row mb-3">
                            <div class="col-md-4">
                                <label class="form-label">Name of TVL</label>
                                <input type="text" name="tvl_name" required class="form-control">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Type of Scholarship</label>
                                <select name="scholarship_type" required class="form-select">
                                    <option value="">Select Scholarship</option>
                                    <option value="twsp">TWSP</option>
                                    <option value="ttsp">TTSP</option>
                                    <option value="pesfa">PESFA</option>
                                    <option value="step">STEP</option>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Name of Trainer</label>
                                <input type="text" name="trainer_name" required class="form-control">
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Training Hours</label>
                            <input type="text" name="training_schedule" required class="form-control">
                        </div>
                    </div>
                </div>

                <div class="card mb-3">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0">Personal Information</h5>
                    </div>
                    <div class="card-body">
                        <div class="row mb-3">
                            <div class="col-md-3">
                                <label class="form-label">First Name</label>
                                <input type="text" name="firstName" required class="form-control" value="<?php
                                echo $firstName;
                                ?>">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Middle Name</label>
                                <input type="text" name="middleName" class="form-control" value="<?php
                                echo $middleName;
                                ?>">
                            </div>
                            <div class="col-md-2">
                                <label class="form-label">Middle Initial</label>
                                <input type="text" name="middleInitial" maxlength="2" class="form-control">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Last Name</label>
                                <input type="text" name="lastName" required class="form-control" value="<?php
                                echo $lastName;
                                ?>">
                            </div>
                            <div class="col-md-1">
                                <label class="form-label">Extension</label>
                                <input type="text" name="extension" class="form-control">
                            </div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-md-4">
                                <label class="form-label">Date of Birth</label>
                                <input type="date" name="dateOfBirth" required class="form-control">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Place of Birth</label>
                                <input type="text" name="placeOfBirth" required class="form-control">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Civil Status</label>
                                <select name="civilStatus" required class="form-select">
                                    <option value="">Select Status</option>
                                    <option value="single">Single</option>
                                    <option value="married">Married</option>
                                    <option value="divorced">Divorced</option>
                                    <option value="widowed">Widowed</option>
                                </select>
                            </div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-md-4">
                                <label class="form-label">Sex</label>
                                <select name="sex" required class="form-select">
                                    <option value="">Select Sex</option>
                                    <option value="male">Male</option>
                                    <option value="female">Female</option>
                                    <option value="other">Other</option>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Mobile Number</label>
                                <input type="tel" name="mobileNumber" required class="form-control">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Email Address</label>
                                <input type="email" name="emailAddress" required class="form-control" value="<?= $users['email'] ?>">
                            </div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label">Highest Educational Attainment</label>
                                <select name="educationAttainment" required class="form-select">
                                    <option value="">Select Education Level</option>
                                    <option value="elementary">Elementary</option>
                                    <option value="highschool">High School</option>
                                    <option value="college">College</option>
                                    <option value="postgraduate">Post Graduate</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Person with Disability</label>
                                <div class="input-group">
                                    <select name="isDisabled" required class="form-select" onchange="toggleDisabilityType(this)">
                                        <option value="">Select</option>
                                        <option value="0">No</option>
                                        <option value="1">Yes</option>
                                    </select>
                                    <input type="text" name="disabilityType" class="form-control" placeholder="Type of Disability" disabled>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card mb-3">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0">Additional Information</h5>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label class="form-label">2x2 picture with white background</label>
                            <input type="file" name="pic" required class="form-control">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Birth Certificate</label>
                            <input type="file" name="birthCertificate" required class="form-control">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Reason for Taking the Course</label>
                            <textarea name="reason" class="form-control" rows="3" required></textarea>
                        </div>
                        <div class="form-check mb-3">
                            <input class="form-check-input" type="checkbox" name="agreeTerms" id="agreeTerms" value="1" required>
                            <label for="agreeTerms" class="form-check-label">
                                <h6>PRIVACY DISCLAIMER <i class="fa-solid fa-circle-exclamation text-danger"></i></h6>
                                <p class="text-justify">I hereby allow TESDA to use/post my contact details, name, email, cellphone/landline numbers and other information I provided which may be used for processing of my scholarship application, for employment opportunities and other purposes.</p>
                            </label>
                        </div>
                    </div>
                </div>
                
                <input type="submit" class="btn btn-primary form-control" value="Register" name="registerButton">
            </form>
        <?php endif; ?>
      </div>
    </div>
  </div>
</div>


<script>
function toggleDisabilityType(select) {
    var disabilityTypeInput = select.nextElementSibling;
    disabilityTypeInput.disabled = select.value !== "1";
    if (select.value !== "1") {
        disabilityTypeInput.value = "";
    }
}
</script>

<style>
.container {
    max-width: 1300px;
    margin: 0 auto;
    padding: 20px;
}

.cc-container {
    display: flex;
    flex-wrap: wrap;
    gap: 30px;
}

.course-content, .course-outline {
    flex: 1 1 400px;
    background: #fff;
    border-radius: 10px;
    box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
    transition: transform 0.3s ease;
}

.course-content:hover, .course-outline:hover {
    transform: translateY(-5px);
}

.course-image img {
    width: 100%;
    height: 300px;
    border-top-left-radius: 10px;
    border-top-right-radius: 10px;
    object-fit: cover;
}

.course-details {
    padding: 20px;
}

.course-details h2 {
    margin-bottom: 15px;
    color: #333;
}

.course-description {
    color: #666;
    margin-bottom: 20px;
}

.course-meta {
    display: flex;
    justify-content: center;
    margin-bottom: 20px;
    align-items: center;
}

.meta-item {
    display: flex;
    align-items: center;
    gap: 10px;
    flex: 1;
}

.meta-item i {
    font-size: 18px;
}

.btn-block {
    display: block;
    width: 100%;
    padding: 10px;
    margin-top: 15px;
}

.outline-title {
    padding: 20px;
    margin: 0;
    border-bottom: 1px solid #eee;
}

.outline-items {
    padding: 20px;
}

.outline-item {
    display: flex;
    align-items: center;
    gap: 15px;
    margin-bottom: 15px;
    padding: 10px;
    background-color: #f8f9fa;
    border-radius: 5px;
    transition: background-color 0.3s ease;
}

.outline-item:hover {
    background-color: #e9ecef;
}

.outline-item i {
    font-size: 20px;
}

.enrollment-form {
    margin-top: 20px;
}

@media (max-width: 768px) {
    .cc-container {
        flex-direction: column;
    }

    .course-content, .course-outline {
        max-width: 100%;
    }

    .course-meta {
        flex-direction: column;
        gap: 10px;
        align-items: start;
    }
}
</style>
