<?php
include '../config/db.php';
session_start();

// Check if both id and courseID are provided in the URL
if (isset($_GET['id']) && isset($_GET['courseID'])) {
    $student_id = mysqli_real_escape_string($connect, $_GET['id']);
    $course_id = mysqli_real_escape_string($connect, $_GET['courseID']);

    // Fetch registration for the specific student and course
    $registrationsQuery = "
        SELECT cr.registration_id, cr.student_id, u.fullname, c.course_name, c.course_code, cr.status, cr.created_at as registration_date,
               cr.tvl_name, cr.scholarship_type, cr.trainer_name, cr.training_schedule,
               cr.pic_path, cr.birthCert_path, c.course_id,
               cr.first_name, cr.middle_name, cr.last_name, cr.extension,
               cr.date_of_birth, cr.place_of_birth, cr.civil_status, cr.sex, cr.mobile_number,
               cr.email_address, cr.highest_education_attainment, cr.is_pwd, cr.disability_type, cr.reason
        FROM course_registrations cr
        JOIN users u ON cr.student_id = u.user_id
        JOIN courses c ON cr.course_id = c.course_id
        WHERE c.user_id = ? AND cr.student_id = ? AND cr.course_id = ?
        LIMIT 1
    ";

    $stmt = mysqli_prepare($connect, $registrationsQuery);
    mysqli_stmt_bind_param($stmt, "iii", $_SESSION['userID'], $student_id, $course_id);
    mysqli_stmt_execute($stmt);
    $registrationsResult = mysqli_stmt_get_result($stmt);
} else {
    // If either id or courseID is not provided, redirect to an error page or the main page
    header("Location: main.php?page=students");
    exit();
}

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $registrationId = mysqli_real_escape_string($connect, $_POST['registration_id']);
    $action = mysqli_real_escape_string($connect, $_POST['action']);
    $message = isset($_POST['message']) ? mysqli_real_escape_string($connect, $_POST['message']) : '';
    $courseId = mysqli_real_escape_string($connect, $_POST['course_id']);
    $studentId = mysqli_real_escape_string($connect, $_POST['student_id']);
    $courseCode = mysqli_real_escape_string($connect, $_POST['course_code']);
    $courseName = mysqli_real_escape_string($connect, $_POST['course_name']);

    $updateQuery = "UPDATE course_registrations SET status = ? WHERE registration_id = ?";
    $stmt = mysqli_prepare($connect, $updateQuery);
    mysqli_stmt_bind_param($stmt, "si", $action, $registrationId);
    
    if (mysqli_stmt_execute($stmt)) {
        // Create notification
        $notificationQuery = "INSERT INTO notifications (user_id, recipient_type, course_id, message, status) 
                              VALUES (?, 'student', ?, ?, 'unread')";
        $stmt = mysqli_prepare($connect, $notificationQuery);
        
        switch ($action) {
            case 'approved':
               $notificationMessage = "Your course registration for {$courseName} has been approved. {$courseCode} is the enrollment key.";
                break;
            case 'declined':
                $notificationMessage = "Your course registration has been declined. Reason: $message";
                break;
            case 'resubmit':
                $notificationMessage = "Please resubmit your course registration. Reason: $message";
                break;
        }
        
        mysqli_stmt_bind_param($stmt, "iis", $studentId, $courseId, $notificationMessage);
        mysqli_stmt_execute($stmt);

        $_SESSION['verification_message'] = "Registration $action successfully.";
    } else {
        $_SESSION['verification_error'] = "Error updating registration.";
    }

    // Redirect to refresh the page
    header("Location: registrations.php?id=$studentId&courseID=$courseId");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verify Course Registration</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Montserrat:ital,wght@0,100..900;1,100..900&family=Poppins:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&display=swap');
        /* Remove border radius from buttons */
        *{
            border-radius: 0 !important; /* Removes the border radius */
            font-family: 'Poppins', sans-serif;
        }
        body {
            background-color: #f8f9fa;
        }
        .container {
            max-width: 1200px;
        }
        .card {
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
            border: none;
            border-radius: 15px;
            overflow: hidden;
            margin-bottom: 30px;
        }
        .card-header {
            background-color: #007bff;
            color: white;
            font-weight: bold;
            padding: 15px;
        }
        .card-body {
            padding: 25px;
        }
        .btn-action {
            margin-right: 10px;
            margin-bottom: 10px;
        }
        .info-section {
            background-color: #f1f3f5;
            border-radius: 10px;
            padding: 15px;
            margin-bottom: 20px;
        }
        .info-section h6 {
            color: #007bff;
            margin-bottom: 15px;
        }
        .document-image {
            max-width: 100%;
            height: auto;
            border-radius: 10px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        @media (max-width: 768px) {
            .card-body {
                padding: 15px;
            }
            .btn-action {
                width: 100%;
                margin-right: 0;
            }
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
</head>
<body>

<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>Verify Course Registration</h2>
        <a href="main.php?page=students" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Back to Students
        </a>
    </div>

    <?php if (isset($_SESSION['verification_message'])): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <?php 
            echo $_SESSION['verification_message'];
            unset($_SESSION['verification_message']);
            ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <?php if (isset($_SESSION['verification_error'])): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <?php 
            echo $_SESSION['verification_error'];
            unset($_SESSION['verification_error']);
            ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <?php while ($row = mysqli_fetch_assoc($registrationsResult)): ?>
        <div class="card mb-4">
            <div class="card-header bg-primary d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Registration for <?php echo htmlspecialchars($row['course_name']); ?></h5>
                <span class="badge bg-<?php echo getStatusColor($row['status']); ?>"><?php echo ucfirst(htmlspecialchars($row['status'])); ?></span>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <div class="info-section">
                            <h6  class="text-primary"><i class="fas fa-user-circle"></i> Personal Information</h6>
                            <p><strong>Name:</strong> <?php echo htmlspecialchars($row['first_name'] . ' ' . $row['middle_name'] . ' ' . $row['last_name'] . ' ' . $row['extension']); ?></p>
                            <p><strong>Date of Birth:</strong> <?php echo htmlspecialchars($row['date_of_birth']); ?></p>
                            <p><strong>Place of Birth:</strong> <?php echo htmlspecialchars($row['place_of_birth']); ?></p>
                            <p><strong>Civil Status:</strong> <?php echo htmlspecialchars($row['civil_status']); ?></p>
                            <p><strong>Sex:</strong> <?php echo htmlspecialchars($row['sex']); ?></p>
                            <p><strong>Mobile Number:</strong> <?php echo htmlspecialchars($row['mobile_number']); ?></p>
                            <p><strong>Email:</strong> <?php echo htmlspecialchars($row['email_address']); ?></p>
                            <p><strong>Education:</strong> <?php echo htmlspecialchars($row['highest_education_attainment']); ?></p>
                            <p><strong>PWD:</strong> <?php echo $row['is_pwd'] ? 'Yes' : 'No'; ?></p>
                            <?php if ($row['is_pwd']): ?>
                                <p><strong>Disability Type:</strong> <?php echo htmlspecialchars($row['disability_type']); ?></p>
                            <?php endif; ?>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="info-section">
                            <h6 class="text-primary"><i class="fas fa-book"></i> Course Information</h6>
                            <p><strong>TVL Name:</strong> <?php echo htmlspecialchars($row['tvl_name']); ?></p>
                            <p><strong>Scholarship Type:</strong> <?php echo htmlspecialchars($row['scholarship_type']); ?></p>
                            <p><strong>Trainer Name:</strong> <?php echo htmlspecialchars($row['trainer_name']); ?></p>
                            <p><strong>Training Schedule:</strong> <?php echo htmlspecialchars($row['training_schedule']); ?></p>
                            <p><strong>Registration Date:</strong> <?php echo htmlspecialchars($row['registration_date']); ?></p>
                        </div>
                        <div class="info-section">
                            <h6  class="text-primary"><i class="fas fa-comment"></i> Reason for Taking the Course</h6>
                            <p><?php echo htmlspecialchars($row['reason']); ?></p>
                        </div>
                    </div>
                </div>
                <div class="row mt-3">
                    <div class="col-md-6">
                        <h6  class="text-primary"><i class="fas fa-image"></i> 2x2 Picture</h6>
                        <img src="<?php echo htmlspecialchars($row['pic_path']); ?>" alt="2x2 Picture" class="document-image mb-3" height="192" width="192">
                    </div>
                    <div class="col-md-6">
                        <h6  class="text-primary"><i class="fas fa-file-alt"></i> Birth Certificate</h6>
                        <img src="<?php echo htmlspecialchars($row['birthCert_path']); ?>" alt="Birth Certificate" class="document-image mb-3">
                    </div>
                </div>
                <?php if ($row['status'] === 'pending'): ?>
                <div class="mt-3 d-flex flex-wrap justify-content-center">
                    <button class="btn btn-success btn-action" data-bs-toggle="modal" data-bs-target="#approveModal" 
                            data-registration-id="<?php echo $row['registration_id']; ?>" 
                            data-course-id="<?php echo $row['course_id']; ?>" 
                            data-student-id="<?php echo $row['student_id']; ?>"
                            data-course-code="<?php echo $row['course_code']; ?>"
                            data-course-name="<?php echo $row['course_name']; ?>">
                        <i class="fas fa-check"></i> Approve
                    </button>
                    <button class="btn btn-danger btn-action" onclick="declineRegistration(<?php echo $row['registration_id']; ?>, <?php echo $row['course_id']; ?>, <?php echo $row['student_id']; ?>)">
                        <i class="fas fa-times"></i> Decline
                    </button>
                    <button class="btn btn-warning btn-action" onclick="requestResubmission(<?php echo $row['registration_id']; ?>, <?php echo $row['course_id']; ?>, <?php echo $row['student_id']; ?>)">
                        <i class="fas fa-redo"></i> Request Resubmission
                    </button>
                </div>
                <?php endif; ?>
            </div>
        </div>
    <?php endwhile; ?>
</div>

<!-- Modal for decline and resubmission reasons -->
<div class="modal fade" id="reasonModal" tabindex="-1" aria-labelledby="reasonModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="reasonModalLabel">Provide Reason</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="reasonForm" method="post">
                    <input type="hidden" id="registration_id" name="registration_id">
                    <input type="hidden" id="course_id" name="course_id">
                    <input type="hidden" id="student_id" name="student_id">
                    <input type="hidden" id="action" name="action">
                    <div class="mb-3">
                        <label for="message" class="form-label">Reason:</label>
                        <textarea class="form-control" id="message" name="message" required></textarea>
                    </div>
                    <button type="submit" class="btn btn-primary">Submit</button>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Approve Modal -->
<div class="modal fade" id="approveModal" tabindex="-1" aria-labelledby="approveModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="approveModalLabel">Approve Registration</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        Are you sure you want to approve this student's registration?
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
        <button type="button" class="btn btn-success" id="confirmApprove">Approve</button>
      </div>
    </div>
  </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
function approveRegistration(registrationId, courseId, studentId, courseCode, courseName) {
    var form = document.createElement('form');
    form.method = 'post';
    form.innerHTML = `
        <input type="hidden" name="registration_id" value="${registrationId}">
        <input type="hidden" name="course_id" value="${courseId}">
        <input type="hidden" name="student_id" value="${studentId}">
        <input type="hidden" name="course_code" value="${courseCode}">
        <input type="hidden" name="course_name" value="${courseName}">
        <input type="hidden" name="action" value="approved">
    `;
    document.body.appendChild(form);
    form.submit();
}

function declineRegistration(registrationId, courseId, studentId) {
    $('#registration_id').val(registrationId);
    $('#course_id').val(courseId);
    $('#student_id').val(studentId);
    $('#action').val('declined');
    $('#reasonModalLabel').text('Reason for Declining');
    $('#reasonModal').modal('show');
}

function requestResubmission(registrationId, courseId, studentId) {
    $('#registration_id').val(registrationId);
    $('#course_id').val(courseId);
    $('#student_id').val(studentId);
    $('#action').val('resubmit');
    $('#reasonModalLabel').text('Reason for Resubmission');
    $('#reasonModal').modal('show');
}

var approveModal = document.getElementById('approveModal');
approveModal.addEventListener('show.bs.modal', function (event) {
    var button = event.relatedTarget;
    var registrationId = button.getAttribute('data-registration-id');
    var courseId = button.getAttribute('data-course-id');
    var studentId = button.getAttribute('data-student-id');
    var courseCode = button.getAttribute('data-course-code');
    var courseName = button.getAttribute('data-course-name');
    
    var confirmButton = approveModal.querySelector('#confirmApprove');
    confirmButton.onclick = function() {
        approveRegistration(registrationId, courseId, studentId, courseCode, courseName);
    };
});
</script>

<?php
function getStatusColor($status) {
    switch ($status) {
        case 'approved':
            return 'success';
        case 'declined':
            return 'danger';
        case 'resubmit':
            return 'warning';
        case 'pending':
        default:
            return 'info';
    }
}
?>

</body>
</html>


