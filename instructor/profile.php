<?php
$userID = $_SESSION['userID'];
$sqlProfile = "SELECT * FROM users WHERE user_id = '$userID'";
$resultProfile = mysqli_query($connect, $sqlProfile);
$userData = mysqli_fetch_assoc($resultProfile);
$profilePic = $userData['profile_picture'] ?? 'default.png';

$theme = json_decode(file_get_contents('../admin/theme.json'), true);
$refreshPage = false;

if (isset($_POST['updateProfile'])) {
    $fullname = mysqli_real_escape_string($connect, $_POST['fullname']);

    $sqlUpdateProfile = "UPDATE users SET fullname = '$fullname' WHERE user_id = '$userID'";
    if (mysqli_query($connect, $sqlUpdateProfile)) {
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

// Fetch courses taught by the instructor
$sqlCourses = "SELECT course_id, course_name FROM courses WHERE user_id = '$userID'";
$resultCourses = mysqli_query($connect, $sqlCourses);
$courses = mysqli_fetch_all($resultCourses, MYSQLI_ASSOC);

// Fetch total students enrolled in instructor's courses
$sqlTotalStudents = "SELECT COUNT(DISTINCT e.user_id) as total_students 
                     FROM enrollments e 
                     JOIN courses c ON e.course_id = c.course_id 
                     WHERE c.user_id = '$userID'";
$resultTotalStudents = mysqli_query($connect, $sqlTotalStudents);
$totalStudents = mysqli_fetch_assoc($resultTotalStudents)['total_students'];

// Fetch recent activity logs
$sqlActivityLogs = "SELECT action, description, created_at 
                    FROM activity_logs 
                    WHERE user_id = '$userID' 
                    ORDER BY created_at DESC 
                    LIMIT 5";
$resultActivityLogs = mysqli_query($connect, $sqlActivityLogs);
$activityLogs = mysqli_fetch_all($resultActivityLogs, MYSQLI_ASSOC);
?>

<style>

    .profile-container img {
        border-radius: 50%;
        object-fit: cover;
        height: 200px !important;
        width: 200px !important;
        border: 5px solid <?= $theme['primaryColor'] ?>;
        transition: transform 0.3s ease;
        box-shadow: 0 4px 8px rgba(15, 111, 197, 0.2);
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
    .stats-card {
        transition: all 0.3s ease;
    }
    .stats-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 4px 8px rgba(15, 111, 197, 0.2);
    }
    .btn-primary {
        background-color: <?= $theme['primaryColor'] ?>;
        border-color: <?= $theme['primaryColor'] ?>;
    }

    .btn-primary:hover {
        background-color: <?= $theme['primaryColor'] ?>;
        border-color: <?= $theme['primaryColor'] ?>;
    }
    .text-primary {
        color: #0f6fc5 !important;
    }
</style>

<div class="container mt-4">
    <div class="row">
        <div class="col-md-4">
            <div class="card p-3 shadow-sm">
                <div class="profile-container text-center">
                    <img src="../uploads/profile-pictures/<?php echo (!empty($profilePic)) ? $profilePic : 'default.png'; ?>" alt="Profile Picture" class="mb-3">
                    <h3 class="text-capitalize"><?= $userData['fullname'] ?></h3>
                    <p class="text-muted text-capitalize"><?= $userData['role'] ?></p>
                    <button class="btn btn-primary mt-2" data-bs-toggle="modal" data-bs-target="#profileModal">
                        Edit Profile
                    </button>
                </div>
            </div>
        </div>
        <div class="col-md-8">
            <div class="card p-3 shadow-sm mb-4">
                <h4>Personal Information</h4>
                <p><strong>Username:</strong> <?= $userData['username'] ?></p>
                <p><strong>Email:</strong> <?= $userData['email'] ?></p>
                <p><strong>Joined:</strong> <?= date('F j, Y', strtotime($userData['created_at'])) ?></p>
            </div>
            <div class="card p-3 shadow-sm mb-4">
                <h4>Instructor Statistics</h4>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <div class="card stats-card bg-primary text-white">
                            <div class="card-body">
                                <h5 class="card-title">Total Courses</h5>
                                <p class="card-text display-4"><?= count($courses) ?></p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6 mb-3">
                        <div class="card stats-card bg-success text-white">
                            <div class="card-body">
                                <h5 class="card-title">Total Students</h5>
                                <p class="card-text display-4"><?= $totalStudents ?></p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="card p-3 shadow-sm mb-4">
                <h4>Courses Taught</h4>
                <ul class="list-group">
                    <?php foreach ($courses as $course): ?>
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            <?= $course['course_name'] ?>
                            <a href="main.php?page=course-content&viewContent=<?= $course['course_id'] ?>" class="btn btn-sm btn-outline-primary">View Details</a>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>
        </div>
    </div>
    <div class="row mt-4">
        <div class="col-12">
            <div class="card p-3 shadow-sm">
                <h4>Recent Activity</h4>
                <ul class="list-group">
                    <?php foreach ($activityLogs as $log): ?>
                        <li class="list-group-item">
                            <strong><?= $log['action'] ?>:</strong> <?= $log['description'] ?>
                            <small class="text-muted d-block"><?= date('F j, Y, g:i a', strtotime($log['created_at'])) ?></small>
                        </li>
                    <?php endforeach; ?>
                </ul>
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
                        <input type="text" class="form-control" id="fullname" name="fullname" value="<?= $userData['fullname'] ?>" required>
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