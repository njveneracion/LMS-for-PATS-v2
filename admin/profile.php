<?php
$userID = $_SESSION['userID'];
$sqlProfile = "SELECT * FROM users WHERE user_id = '$userID'";
$resultProfile = mysqli_query($connect, $sqlProfile);
$userData = mysqli_fetch_assoc($resultProfile);
$profilePic = $userData['profile_picture'] ?? 'default.jpg';

$refreshPage = false;

if (isset($_POST['updateProfile'])) {
    $fullname = $_POST['fullname'];

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

// Fetch activity logs
$sqlActivityLogs = "SELECT action, description, created_at FROM activity_logs WHERE user_id = '$userID' ORDER BY created_at DESC LIMIT 10";
$resultActivityLogs = mysqli_query($connect, $sqlActivityLogs);
$activityLogs = mysqli_fetch_all($resultActivityLogs, MYSQLI_ASSOC);

// Fetch system statistics
$sqlTotalUsers = "SELECT COUNT(*) as total FROM users";
$sqlTotalCourses = "SELECT COUNT(*) as total FROM courses";
$sqlTotalEnrollments = "SELECT COUNT(*) as total FROM enrollments";

$resultTotalUsers = mysqli_query($connect, $sqlTotalUsers);
$resultTotalCourses = mysqli_query($connect, $sqlTotalCourses);
$resultTotalEnrollments = mysqli_query($connect, $sqlTotalEnrollments);

$totalUsers = mysqli_fetch_assoc($resultTotalUsers)['total'];
$totalCourses = mysqli_fetch_assoc($resultTotalCourses)['total'];
$totalEnrollments = mysqli_fetch_assoc($resultTotalEnrollments)['total'];
?>

<style>

    body {
        font-family: 'Arial', sans-serif;
        background-color: #f8f9fa;
        color: var(--text-color);
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
        border-bottom: none;
        font-weight: bold;
        color: white;
    }

    .list-group-item {
        border: none;
        padding: 0.75rem 1.25rem;
        border-left: 4px solid transparent;
        transition: background-color 0.3s ease, border-left-color 0.3s ease;
    }

    .list-group-item:hover {
        background-color: var(--primary-light);
        border-left-color: var(--primary-color);
    }

    .badge {
        font-size: 0.8em;
        background-color: var(--primary-color);
    }

    .btn-primary {
        background-color: var(--primary-color);
        border-color: var(--primary-color);
    }

    .btn-primary:hover {
        background-color: #0d5ca3;
        border-color: #0d5ca3;
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
                <div class="card-header">
                    <i class="bi bi-person-lines-fill"></i> Profile Picture
                </div>
                <div class="card-body profile-container text-center">
                    <img src="../uploads/profile-pictures/<?= $profilePic ?>" alt="Profile Picture" class="mb-3">
                    <h3 class="text-capitalize"><?= $userData['fullname'] ?></h3>
                    <p class="text-muted">System Administrator</p>
                    <button class="btn btn-primary mt-2" data-bs-toggle="modal" data-bs-target="#profileModal">
                        <i class="bi bi-pencil-square"></i> Edit Profile
                    </button>
                </div>
            </div>
        </div>
        <div class="col-md-8">
            <div class="card mb-4">
                <div class="card-header">
                    <i class="bi bi-person-lines-fill"></i> Personal Information
                </div>
                <div class="card-body">
                    <ul class="list-group list-group-flush">
                        <li class="list-group-item"><strong>Full Name:</strong> <?= $userData['fullname'] ?></li>
                        <li class="list-group-item"><strong>Username:</strong> <?= $userData['username'] ?></li>
                        <li class="list-group-item"><strong>Email:</strong> <?= $userData['email'] ?></li>
                        <li class="list-group-item"><strong>Role:</strong> System Administrator</li>
                        <li class="list-group-item"><strong>Account Created:</strong> <?= $userData['created_at'] ?></li>
                    </ul>
                </div>
            </div>
            <div class="card mb-4">
                <div class="card-header">
                    <i class="bi bi-graph-up"></i> System Statistics
                </div>
                <div class="card-body">
                    <ul class="list-group list-group-flush">
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            Total Users
                            <span class="badge rounded-pill"><?= $totalUsers ?></span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            Total Courses
                            <span class="badge rounded-pill"><?= $totalCourses ?></span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            Total Enrollments
                            <span class="badge rounded-pill"><?= $totalEnrollments ?></span>
                        </li>
                    </ul>
                </div>
            </div>
            <div class="card mb-4">
                <div class="card-header">
                    <i class="bi bi-clock-history"></i> Recent Activity
                </div>
                <div class="card-body">
                    <ul class="list-group list-group-flush">
                        <?php foreach ($activityLogs as $log): ?>
                            <li class="list-group-item">
                                <strong><?= $log['action'] ?>:</strong> <?= $log['description'] ?>
                                <small class="text-muted d-block"><?= $log['created_at'] ?></small>
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
                    <label class="form-label">Full Name</label>
                    <input type="text" name="fullname" class="form-control mb-3" value="<?= $userData['fullname'] ?>" required>

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


