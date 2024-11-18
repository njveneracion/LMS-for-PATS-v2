<?php
session_start();
include '../config/db.php';
include '../functions/logActivity.php';

$userID = $_SESSION['userID']; 


$timeout = 30 * 60; // 30 minutes
$cleanupQuery = "DELETE FROM active_sessions WHERE last_activity < DATE_SUB(NOW(), INTERVAL ? SECOND)";
$stmtCleanup = mysqli_prepare($connect, $cleanupQuery);
mysqli_stmt_bind_param($stmtCleanup, "i", $timeout);
mysqli_stmt_execute($stmtCleanup);

function checkAndUnenrollInactiveStudents() {
    global $connect;

    // These define how long a student can be inactive before being considered for unenrollment, and how old an enrollment must be before it's checked for inactivity.
    // Set the inactivity time (in seconds)
    $inactivity_time = 1209600; // 14 days
    $minimum_enrollment_age = 86400; // 1 day

    error_log("Checking for inactive students. Inactivity threshold: $inactivity_time seconds");

    // Query to find users with inactivity time greater than the threshold
    $sql = "SELECT e.enrollment_id, e.user_id, e.course_id, e.batch_id, u.fullname,
                   CASE WHEN sp.completed_at IS NULL THEN 'Never' 
                        ELSE sp.completed_at END as last_activity
            FROM enrollments e
            JOIN users u ON e.user_id = u.user_id
            LEFT JOIN student_progress sp ON e.user_id = sp.student_id AND e.course_id = sp.course_id
            WHERE e.status = 'In Progress'
            AND TIMESTAMPDIFF(SECOND, e.enrollment_date, NOW()) > $minimum_enrollment_age
            AND (sp.completed_at IS NULL OR TIMESTAMPDIFF(SECOND, sp.completed_at, NOW()) > $inactivity_time)
            GROUP BY e.enrollment_id";

    error_log("Executing SQL: $sql");

    try {
        $result = mysqli_query($connect, $sql);
        
        if (!$result) {
            error_log("MySQL Error: " . mysqli_error($connect));
            return;
        }

        $inactiveCount = mysqli_num_rows($result);
        error_log("Found $inactiveCount inactive enrollments");

        if ($inactiveCount == 0) {
            error_log("No inactive students found.");
            return;
        }

        while ($row = mysqli_fetch_assoc($result)) {
            error_log("Inactive enrollment found: " . json_encode($row));
            deleteInactiveEnrollment($row['user_id'], $row['fullname'], $row['enrollment_id'], $row['course_id'], $row['batch_id']);
        }
    } catch (Exception $e) {
        error_log("Error in checkAndUnenrollInactiveStudents: " . $e->getMessage());
    }
}

function deleteInactiveEnrollment($userId, $fullname, $enrollmentId, $courseId, $batchId) {
    global $connect;

    error_log("Attempting to delete enrollment for $fullname (ID: $userId)");

    $connect->begin_transaction();

    try {
        // Delete from enrollments
        $delete_sql = "DELETE FROM enrollments WHERE enrollment_id = $enrollmentId";
        $result = mysqli_query($connect, $delete_sql);
        error_log("Deleting from enrollments. Affected rows: " . mysqli_affected_rows($connect));

        // Delete from course_registrations
        $delete_sql = "DELETE FROM course_registrations WHERE student_id = $userId AND course_id = $courseId";
        $result = mysqli_query($connect, $delete_sql);
        error_log("Deleting from course_registrations. Affected rows: " . mysqli_affected_rows($connect));

        // Delete from student_progress
        $delete_sql = "DELETE FROM student_progress WHERE student_id = $userId AND course_id = $courseId";
        $result = mysqli_query($connect, $delete_sql);
        error_log("Deleting from student_progress. Affected rows: " . mysqli_affected_rows($connect));

        $action = "Auto Unenroll and Delete";
        $description = "Student $fullname (ID: $userId) auto-unenrolled and deleted from course ID: $courseId, batch ID: $batchId due to inactivity.";
        logActivity($userId, $action, $description);

        $connect->commit();
        error_log("Successfully unenrolled inactive student: $fullname (ID: $userId) from course ID: $courseId, batch ID: $batchId");
    } catch (Exception $e) {
        $connect->rollback();
        error_log("Error deleting inactive enrollment: " . $e->getMessage());
    }
}

//i check yung student if inactive na
checkAndUnenrollInactiveStudents();

    // Determine the page to include
    $page = isset($_GET['page']) ? $_GET['page'] : 'dashboard';
  
    switch($page){
        case 'dashboard':
            $filename = 'dashboard.php';
            break;
        case 'profile':
            $filename = 'profile.php';
            break;
        case 'course-details':
            $filename = 'course-details.php';
            break;
        case 'my-courses':
            $filename = 'my-courses.php';
            break;
        case 'course-content':
            $filename = 'course-content.php';
            break;
        case 'quiz-page':
            $filename = 'quiz-page.php';
            break;
        case 'notifications':
            $filename = 'notifications.php';
            break;
        case 'certificates':
            $filename = 'certificates.php';
            break;
        case 'task-sheet':
            $filename = 'task-sheet.php';
            break;
        case 'assessment-page':
            $filename = 'assessment-page.php';
            break;
        case 'logout':

            $session_id = session_id();
            $query = "DELETE FROM active_sessions WHERE session_id = ?";
            $stmt = $connect->prepare($query);
            $stmt->bind_param("s", $session_id);
            $stmt->execute();    

            // Log the logout activity
            $action = 'Logout';
            $description = 'User logged out successfully.';
    
            // Log activity before destroying the session
            logActivity($userID, $action, $description);
    
            // Destroy the session
            session_unset(); // Clear all session variables
            session_destroy(); // Destroy the session
    
            // Redirect to homepage or login page
            header('Location: ../index.php');
            exit;
        default:
            $filename = 'dashboard.php';
            break;
    }

    $sqlProfile = "SELECT profile_picture FROM users WHERE user_id = '$userID'";
    $resultProfile = mysqli_query($connect, $sqlProfile);
    $pic = mysqli_fetch_assoc($resultProfile);
    $profilePic = $pic['profile_picture'] ?? 'default.png';

        // Add this function to check for new notifications
function checkNewNotifications($connect, $user_id) {
    $query = "SELECT * FROM notifications WHERE user_id = ? AND status = 'unread' ORDER BY created_at DESC";
    $stmt = $connect->prepare($query);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $notifications = $result->fetch_all(MYSQLI_ASSOC);
    return $notifications;
}

// Use this function to get notifications
$notifications = checkNewNotifications($connect, $_SESSION['userID']);

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>LMS Instructor Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/side-nav.css?v1">
    <link rel="stylesheet" href="../assets/css/color.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11.0.19/dist/sweetalert2.min.css">
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.0.19/dist/sweetalert2.all.min.js"></script>
</head>
<style>
    
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

        #notificationDot{
            top: 10px !important;
            left: 28px !important;
        }

        @media (max-width: 767px) and (min-width: 320px) {
            .navbar-brand{
                width: 180px;
                height: 100%;
            }
            .navbar-brand img{
                margin-left: 10px;
                object-fit: contain;
            }
        }
</style>
<body>
    <nav class="navbar navbar-expand-lg fixed-top">
        <div class="container-fluid ms-4 me-4">
            <button id="sidebarToggle" class="btn btn-outline-primary d-md-none ">
                <i class="fas fa-bars"></i>
            </button>
            <a class="navbar-brand" href="index.php">
                <img src="../assets/images/pats-logo.png" alt="Pats Logo" style="height: 60px; width: 100%">
            </a>
            <div class="d-flex align-items-center ms-auto">
                <div class="dropdown">
                    <button class="btn btn-link position-relative" type="button" id="notificationDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="fas fa-bell fa-lg text-secondary"></i>
                        <span id="notificationDot" class="position-absolute top-0 start-100 translate-middle p-1 bg-danger border border-light rounded-circle" style="display: none;"></span>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end dropdown-menu-lg-end" aria-labelledby="notificationDropdown" style="width: 250px; max-height: 700px; overflow-y: auto;">
                        <li><h6 class="dropdown-header">Notifications</h6></li>
                        <li><hr class="dropdown-divider"></li>
                        <?php if (empty($notifications)): ?>
                            <li><a class="dropdown-item" href="#">No new notifications</a></li>
                        <?php else: ?>
                            <?php foreach ($notifications as $notification): ?>
                                <li>
                                    <a class="dropdown-item" href="?page=s_notifications">
                                        <?php echo htmlspecialchars($notification['message']); ?>
                                        <small class="text-muted d-block"><?php echo date('M d, Y H:i', strtotime($notification['created_at'])); ?></small>
                                    </a>
                                </li>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </ul>
                </div>
                <div class="dropdown">
                    <button class="btn btn-link me-3 " type="button" id="profileDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                    <img src="../uploads/profile-pictures/<?php echo (!empty($profilePic)) ? $profilePic : 'default.png'; ?>" alt="profile photo" style="width: 40px; height: 40px; border-radius: 50%; object-fit: cover;">
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="profileDropdown">
                        <li><span class="dropdown-item-text"><?= $_SESSION['fullname']; ?></span></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item" href="?page=profile"><i class="fas fa-user-cog"></i> Profile</a></li>
                        <li><a class="dropdown-item" href="?page=logout"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
                    </ul>
                </div>
            </div>
        </div>
    </nav>

    <nav class="sidebar">
        <div class="menu-bar">
            <div class="menu">
                <ul class="menu-links">
                    <li class="nav-link">
                        <a href="?page=dashboard">
                            <i class="fa-solid fa-graduation-cap icon "></i>
                            <span class="text nav-text ">Dashboard</span>
                        </a>
                    </li>

                    <li class="nav-link">
                        <a href="?page=my-courses">
                            <i class="fa-solid fa-clipboard icon "></i>
                            <span class="text nav-text ">My Courses</span>
                        </a>
                    </li>

                    <li class="nav-link">
                        <a href="?page=certificates">
                            <i class="fa-solid fa-certificate icon"></i>
                            <span class="text nav-text">My Certificates</span>
                        </a>
                    </li>

                    
                    <li class="nav-link">
                        <a href="?page=notifications">
                        <i class="fa-solid fa-envelope  icon"></i>
                            <span class="text nav-text">Notifications</span>
                        </a>
                    </li>

                  

                    <li class="nav-link">
                        <a href="?page=profile">
                            <i class="fa-solid fa-user icon"></i>
                            <span class="text nav-text">Profile</span>
                        </a>
                    </li>  

                    <hr>
                    <div class="container mt-4">
                        <div class="welcome-text">
                            <h3 id="greeting"></h3>
                            <p>Welcome to your instructor profile. Here you can manage your personal information and manage the course content.</p>
                        </div>
                    </div>
                </ul>
            </div>
        </div>
    </nav>

    <div class="content flex-grow-1" id="content-area">
    <?php include $filename; ?>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../javascript/index.js"></script>
    <script>

            // JavaScript for adding the 'active' class on click
        document.querySelectorAll('.nav-link').forEach(link => {
            link.addEventListener('click', function() {
                document.querySelectorAll('.nav-link').forEach(nav => nav.classList.remove('active'));
                this.classList.add('active');
            });
        });

        // JavaScript for setting the 'active' class based on current URL
        window.addEventListener('DOMContentLoaded', function() {
            const currentPage = window.location.search || '?page=s_dashboard';
            document.querySelectorAll('.nav-link a').forEach(link => {
                if (link.getAttribute('href') === currentPage) {
                    link.parentElement.classList.add('active');
                }
            });
        });

        document.addEventListener('DOMContentLoaded', function() {
            const sidebarToggle = document.getElementById('sidebarToggle');
            const sidebar = document.querySelector('.sidebar');
            const content = document.querySelector('.content');

            sidebarToggle.addEventListener('click', function() {
                sidebar.classList.toggle('active');
                content.style.marginLeft = sidebar.classList.contains('active') ? '250px' : '0';
            });
        });

          //get the current time
          document.addEventListener('DOMContentLoaded', function() {
        function setGreeting() {
            const greetingElement = document.getElementById('greeting');
            if (!greetingElement) return;

            const hour = new Date().getHours();
            let greeting;

            if (hour >= 5 && hour < 12) {
                greeting = "Good morning";
            } else if (hour >= 12 && hour < 18) {
                greeting = "Good afternoon";
            } else {
                greeting = "Good evening";
            }

            greetingElement.textContent = greeting + ", <?php echo $_SESSION['fullname']; ?>!";
        }

        setGreeting();
        // Update greeting every minute
        setInterval(setGreeting, 60000);
        });

    </script>
    <script>
        // Function to check for new notifications
       function checkNewNotifications() {
            fetch('get_notification.php')
                .then(response => response.json())
                .then(data => {
                    updateNotificationDropdown(data);
                    // Show/hide the notification dot based on whether there are unread notifications
                    document.getElementById('notificationDot').style.display = data.length > 0 ? 'block' : 'none';
                })
                .catch(error => console.error('Error:', error));
        }

        function updateNotificationDropdown(notifications) {
        const dropdownMenu = document.querySelector('#notificationDropdown + .dropdown-menu');
        if (notifications.length === 0) {
            dropdownMenu.innerHTML = '<li><h6 class="dropdown-header">Notifications</h6></li><li><hr class="dropdown-divider"></li><li><a class="dropdown-item" href="#">No new notifications</a></li>';
        } else {
            let notificationHtml = '<li><h6 class="dropdown-header">Notifications</h6></li><li><hr class="dropdown-divider"></li>';
            notifications.forEach(notification => {
                notificationHtml += `
                    <li>
                        <a class="dropdown-item" href="?page=s_notifications&id=${notification.id}">
                            ${notification.message}
                            <small class="text-muted d-block">${new Date(notification.created_at).toLocaleString()}</small>
                        </a>
                    </li>`;
            });
            dropdownMenu.innerHTML = notificationHtml;
        }
    }

    // Check for new notifications every 30 seconds
    setInterval(checkNewNotifications, 30000);

    // Initial check when the page loads
    checkNewNotifications();
    </script>
    <script>
        //function autoReload() {
        //    // Reload the page every 10 seconds
        //    setTimeout(function() {
        //        location.reload();
        //    }, 10000);
        //}
//
        //// Call the function when the page loads
        //window.onload = autoReload;

    </script>
</body>
</html>

