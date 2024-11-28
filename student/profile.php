<?php
$userID = $_SESSION['userID'];
$sqlProfile = "SELECT profile_picture, fullname FROM users WHERE user_id = '$userID'";
$resultProfile = mysqli_query($connect, $sqlProfile);
$userData = mysqli_fetch_assoc($resultProfile);
$profilePic = $userData['profile_picture'] ?? 'default.png';
$fullname = $userData['fullname'] ?? '';

$refreshPage = false; // Add this line at the beginning of your PHP code

if (isset($_POST['updateProfile'])) {

    $fullname = $_POST['fullname'];
    // Update user's fullname
    $sqlUpdate = "UPDATE users SET fullname = '$fullname' WHERE user_id = '$userID'";
    if (mysqli_query($connect, $sqlUpdate)) {
        $toastMessage = "Profile updated successfully!";
        $refreshPage = true;
    } else {
        $toastMessage = "Failed to update profile.";
    }
    // Handle profile picture upload
    if ($_FILES['profilePic']['size'] > 0) {
        $targetDirectory = "../uploads/profile-pictures/";
        $fileName = basename($_FILES['profilePic']['name']);
        $targetFilePath = $targetDirectory . $fileName;
        $fileType = strtolower(pathinfo($targetFilePath, PATHINFO_EXTENSION));

        // Allowed file types
        $allowedTypes = ['jpg', 'jpeg', 'png', 'gif'];

        if (in_array($fileType, $allowedTypes)) {
            if (move_uploaded_file($_FILES['profilePic']['tmp_name'], $targetFilePath)) {
                $sqlUpdate = "UPDATE users SET profile_picture = '$fileName' WHERE user_id = '$userID'";
                if (mysqli_query($connect, $sqlUpdate)) {
                    $toastMessage = "Profile picture updated successfully!";
                    $profilePic = $fileName;
                    $refreshPage = true;
                } else {
                    $toastMessage = "Failed to update profile picture.";
                }
            } else {
                $toastMessage = "Failed to upload the image.";
            }
        } else {
            $toastMessage = "Invalid file type. Only JPG, JPEG, PNG, & GIF files are allowed.";
        }
    }
}

// Fetch user details from course_registrations
$sqlUserDetails = "SELECT cr.first_name, cr.middle_name, cr.last_name, cr.extension, 
                          cr.date_of_birth, cr.place_of_birth, cr.civil_status, cr.sex, 
                          cr.mobile_number, cr.email_address, cr.highest_education_attainment, 
                          cr.is_pwd, cr.disability_type, u.email
                   FROM course_registrations cr
                   JOIN users u ON cr.student_id = u.user_id
                   WHERE cr.student_id = '$userID'
                   ORDER BY cr.created_at DESC
                   LIMIT 1";
$resultUserDetails = mysqli_query($connect, $sqlUserDetails);
$userDetails = mysqli_fetch_assoc($resultUserDetails);

// If no course registration found, fetch basic info from users table
if (!$userDetails) {
    $sqlUserBasic = "SELECT fullname, email FROM users WHERE user_id = '$userID'";
    $resultUserBasic = mysqli_query($connect, $sqlUserBasic);
    $userDetails = mysqli_fetch_assoc($resultUserBasic);
}

// Fetch enrolled courses
$sqlCourses = "SELECT c.course_name, cr.status
               FROM course_registrations cr
               JOIN courses c ON cr.course_id = c.course_id
               WHERE cr.student_id = '$userID'";
$resultCourses = mysqli_query($connect, $sqlCourses);
$courses = mysqli_fetch_all($resultCourses, MYSQLI_ASSOC);

// Fetch completed courses
$sqlCompletedCourses = "SELECT c.course_name
                        FROM enrollments e
                        JOIN courses c ON e.course_id = c.course_id
                        WHERE e.user_id = '$userID' AND e.status = 'Completed'";
$resultCompletedCourses = mysqli_query($connect, $sqlCompletedCourses);
$completedCourses = mysqli_fetch_all($resultCompletedCourses, MYSQLI_ASSOC);
?>

<style>
    :root {
        --primary-color: <?= $theme['primaryColor'] ?>;
        --secondary-color: <?= $theme['secondaryColor'] ?>;
        --background-color: <?= $theme['backgroundColor'] ?>;
        --text-color: <?= $theme['textColor'] ?>;
    }

    .bg-primary {
        background-color: var(--primary-color) !important;
    }

    .btn-primary {
        background-color: var(--primary-color) !important;
        border-color: var(--primary-color) !important;
    }

    .profile-container img {
        border-radius: 50%;
        object-fit: cover;
        height: 200px !important;
        width: 200px !important;
        border: 5px solid var(--primary-color);
        transition: transform 0.3s ease;
        box-shadow: 0 4px 8px rgba(0,0,0,0.1);
    }
    .profile-container img:hover {
        transform: scale(1.05);
    }
    .toast-container {
        position: fixed;
        top: 20px;
        right: 20px;
        z-index: 10000;
    }
    .bio-text {
        white-space: pre-wrap;
    }
    .card {
        border: none;
        border-radius: 15px;
        box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        transition: all 0.3s ease;
    }
    .card:hover {
        transform: translateY(-5px);
        box-shadow: 0 6px 12px rgba(0,0,0,0.15);
    }
    .card-header {
        background-color: var(--primary-color);
        color: white;
        border-bottom: none;
        font-weight: bold;
    }
    .list-group-item {
        border: none;
        padding: 0.75rem 1.25rem;
    }
    .badge {
        font-size: 0.8em;
    }
    .progress {
        height: 10px;
        border-radius: 5px;
    }
    .progress-bar {
        background-color: var(--primary-color);
    }
    @media (max-width: 768px) {
        .profile-container {
            flex-direction: column;
            align-items: center;
        }
    }
</style>

<div class="container mt-4">
    <div class="row">
        <div class="col-md-4">
            <div class="card mb-4">
                <div class="card-header bg-primary text-white">
                    <i class="bi bi-person-lines-fill"></i> Profile Picture
                </div>
                <div class="card-body profile-container text-center">
                    <img src="../uploads/profile-pictures/<?php echo (!empty($profilePic)) ? $profilePic : 'default.png'; ?>" alt="Profile Picture" class="mb-3">
                    <h3 class="text-capitalize">
                        <?= $fullname; ?>
                    </h3>
                    <p class="text-muted">Student</p>
                    <button class="btn btn-primary mt-2" data-bs-toggle="modal" data-bs-target="#profileModal">
                        <i class="bi bi-pencil-square"></i> Edit Profile
                    </button>
                </div>
            </div>
        </div>
        <div class="col-md-8">
            <div class="card mb-4">
                <div class="card-header bg-primary text-white">
                    <i class="bi bi-person-lines-fill"></i> Personal Information
                </div>
                <div class="card-body">
                    <ul class="list-group list-group-flush">
                        <li class="list-group-item"><strong>Full Name:</strong> 
                            <?= $userDetails['first_name'] ?? '' ?> 
                            <?= $userDetails['middle_name'] ?? '' ?> 
                            <?= $userDetails['last_name'] ?? '' ?> 
                            <?= $userDetails['extension'] ?? '' ?>
                            <?= $userDetails['fullname'] ?? '' ?>
                        </li>
                        <li class="list-group-item"><strong>Email:</strong> <?= $userDetails['email'] ?? $userDetails['email_address'] ?? 'Not provided' ?></li>
                        <li class="list-group-item"><strong>Date of Birth:</strong> <?= $userDetails['date_of_birth'] ?? 'Not provided' ?></li>
                        <li class="list-group-item"><strong>Place of Birth:</strong> <?= $userDetails['place_of_birth'] ?? 'Not provided' ?></li>
                        <li class="list-group-item"><strong>Civil Status:</strong> <?= $userDetails['civil_status'] ?? 'Not provided' ?></li>
                        <li class="list-group-item"><strong>Sex:</strong> <?= $userDetails['sex'] ?? 'Not provided' ?></li>
                        <li class="list-group-item"><strong>Mobile Number:</strong> <?= $userDetails['mobile_number'] ?? 'Not provided' ?></li>
                        <li class="list-group-item"><strong>Highest Education:</strong> <?= $userDetails['highest_education_attainment'] ?? 'Not provided' ?></li>
                        <li class="list-group-item"><strong>PWD:</strong> <?= $userDetails['is_pwd'] ? 'Yes' : 'No' ?></li>
                        <?php if ($userDetails['is_pwd']): ?>
                            <li class="list-group-item"><strong>Disability Type:</strong> <?= $userDetails['disability_type'] ?? 'Not specified' ?></li>
                        <?php endif; ?>
                    </ul>
                </div>
            </div>
            <div class="card mb-4">
                <div class="card-header bg-primary text-white">
                    <i class="bi bi-check-circle"></i> Completed Courses
                </div>
                <div class="card-body">
                    <?php if (empty($completedCourses)): ?>
                        <p>You haven't completed any courses yet.</p>
                    <?php else: ?>
                        <ul class="list-group list-group-flush">
                            <?php foreach ($completedCourses as $course): ?>
                                <li class="list-group-item"><?= $course['course_name'] ?></li>
                            <?php endforeach; ?>
                        </ul>
                    <?php endif; ?>
                </div>
            </div>
            <div class="card mb-4">
                <div class="card-header bg-primary text-white">
                    <i class="bi bi-book-fill"></i> Enrolled Courses
                </div>
                <div class="card-body">
                    <ul class="list-group list-group-flush">
                        <?php foreach ($courses as $course): ?>
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                <?= $course['course_name'] ?>
                                <span class="badge bg-<?= $course['status'] == 'approved' ? 'success' : 'warning' ?> rounded-pill">
                                    <?= ucfirst($course['status']) ?>
                                </span>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Profile Edit Modal -->
<div class="modal fade" id="profileModal" tabindex="-1" aria-labelledby="profileModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="profileModalLabel">Edit Profile</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="profileForm" method="post" enctype="multipart/form-data">
                    <div class="mb-3">
                        <label for="fullname" class="form-label">Full Name</label>
                        <input type="text" name="fullname" class="form-control" value="<?php echo $fullname; ?>">
                    </div>
                    <div class="mb-3">
                        <label for="profilePic" class="form-label">Profile Picture</label>
                        <input type="file" name="profilePic" accept="image/*" class="form-control">
                    </div>
                    <button type="submit" class="btn btn-primary" name="updateProfile">Save Changes</button>
                </form>
            </div>
        </div>
    </div>
</div>

<?php if (isset($toastMessage)): ?>
<div class="toast-container">
    <div class="toast" role="alert" aria-live="assertive" aria-atomic="true">
        <div class="toast-header">
            <strong class="me-auto">Profile Update</strong>
            <button type="button" class="btn-close" data-bs-dismiss="toast" aria-label="Close"></button>
        </div>
        <div class="toast-body">
            <?= $toastMessage ?>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', (event) => {
    var toastElList = [].slice.call(document.querySelectorAll('.toast'))
    var toastList = toastElList.map(function(toastEl) {
        return new bootstrap.Toast(toastEl)
    })
    toastList.forEach(toast => toast.show())

    <?php if ($refreshPage): ?>
    // Add this code to refresh the page after 2 seconds if update was successful
    setTimeout(function() {
        window.location.href = 'main.php?page=profile';
    }, 2000);
    <?php endif; ?>
});
</script>
<?php endif; ?>