<?php
session_start();
include '../config/db.php';
include '../functions/logActivity.php';

$userID = $_SESSION['userID']; 


$timeout = 60 * 60; // 1 hour
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


    $sqlHeader = "SELECT * FROM header";
    $resultHeader = mysqli_query($connect, $sqlHeader);
    $header = mysqli_fetch_assoc($resultHeader);
    $logo = $header['logo'];
 $theme = json_decode(file_get_contents('../admin/theme.json'), true);
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

 * > .nav-link{
     border-radius: 10px;
}
  :root {
        --body-color: <?= $theme['backgroundColor'] ?>;
        --sidebar-color: #FFF;
        --primary-color: <?= $theme['primaryColor'] ?>;
        --primary-color-light: #F6F5FF;
        --toggle-color: #DDD;
        --text-color: <?= $theme['textColor'] ?>;
        --background-color: <?= $theme['backgroundColor'] ?>;
        --secondary-color: <?= $theme['secondaryColor'] ?>;
        --tran-03: all 0.2s ease;
        --tran-04: all 0.3s ease;
        --tran-05: all 0.3s ease;
        }

        body {
            font-family: 'Poppins', sans-serif;
            background-color: var(--background-color);
            padding-top: 80px;
        }

        /* ===== Sidebar ===== */
        .sidebar {
            position: fixed;
            top: 80px; /* Adjust based on your navbar height */
            left: 0;
            bottom: 0;
            height: 100%;
            width: 250px;
            padding: 10px 14px;
            background: var(--background-color);
            transition: var(--tran-05);
            z-index: 100;
            border-top-right-radius: 5px;
            overflow-y: auto;
            box-shadow: 2px 0 4px rgba(0,0,0,0.1);
        }

        /* ===== Reusable code - Here ===== */
        .sidebar li {
            height: 50px;
            list-style: none;
            display: flex;
            align-items: center;
            margin-top: 10px;
        }

        .header .image, .sidebar .icon {
            min-width: 60px;
            border-radius: 6px;
        }

        .sidebar .icon {
            min-width: 60px;
            border-radius: 6px;
            height: 100%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 20px;
        }

        .sidebar .text, .sidebar .icon {
            color: var(--text-color);
            transition: var(--tran-03);
        }

        .sidebar .text {
            font-size: 17px;
            font-weight: 500;
            white-space: nowrap;
            opacity: 1;
        }

        .sidebar.close .text {
            opacity: 0;
        }

        .header .image img {
            width: 60px;
            border-radius: 6px;
            margin-right: 20px;
        }

        .sidebar li a {
            list-style: none;
            height: 100%;
            background-color: transparent;
            display: flex;
            align-items: center;
            height: 100%;
            width: 100%;
            text-decoration: none;
            transition: var(--tran-03);
        }

        .sidebar li a:hover {
            background-color: var(--primary-color);
        }

        .sidebar li a:hover .icon, .sidebar li a:hover .text {
            color: var(--text-color);
        }

        .sidebar .menu-bar {
            height: calc(100% - 55px);
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            overflow-y: scroll;
        }

        .menu-bar::-webkit-scrollbar {
            display: none;
        }

        .menu-links {
            padding: 0;
            position: relative;
        }

        .navbar {
            background-color: var(--background-color);
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            height: 80px;
        }

        .navbar-brand, .nav-link {
            color: var(--text-color);
        }

        .sidebar .nav-link a {
            color: #333;
            transition: all 0.3s ease;
            padding: 15px 0px;
            height: 100%;
            width: 100%;
        }

        .sidebar .nav-link.active {
            background-color: var(--primary-color);
            border-radius: 10px;
        }

        .sidebar .nav-link.active a, .sidebar .nav-link.active i, .sidebar .nav-link.active span {
            color: white !important;
        }

        .sidebar .nav-link:hover a, .sidebar .nav-link:hover i, .sidebar .nav-link:hover span {
            color: white !important;
        }

        .sidebar .nav-link a {
            color: #333;
            text-decoration: none;
        }

        .sidebar .nav-link a:hover {
            color: white !important;
            border-radius: 10px;
        }

        .sidebar.nav-link.active {
            background-color: var(--background-color);
        }

        .content {
            transition: all 0.3s;
            padding: 20px;
        }

        .dropdown button img {
            height: 40px;
            width: 40px;
            border: 1px solid var(--primary-color);
            border-radius: 50%;
        }

        @media (min-width: 769px) {
            .content {
                margin-left: 250px;
            }
        }

        @media (max-width: 768px) {
            .sidebar {
                margin-left: -250px;
            }

            .sidebar.active {
                margin-left: 0;
            }

            .content {
                margin-left: 0;
            }

            #sidebarToggle {
                margin-left: -15px;
                margin-right: 10px;
                color: blue;
            }

            #sidebarToggle:hover {
                background-color: #3572EF;
                color: white;
            }

            .dropdown button img {
                margin-right: -40px;
            }
        }

        .welcome-text {
            background: linear-gradient(to right, var(--primary-color), var(--secondary-color));
            color: var(--text-color);
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 20px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            transition: transform 0.3s ease;
        }

        .welcome-text:hover {
            transform: translateY(-5px);
        }

        /* Remove border radius from buttons */
        * {
            font-family: 'Poppins', sans-serif;
        }

        section {
            padding-top: 80px;
        }

        .nav-link {
            color: #6c757d;
            transition: color 0.3s ease;
        }

        .nav-link:hover {
            color: var(--primary-color);
            font-weight: bold;
        }

        .navbar-nav .nav-link.active {
            color: var(--primary-color);
            font-weight: bold;
            transition: all 0.15s ease;
        }

        .login-btn {
            border: none;
            background-color: var(--primary-color);
            padding: 10px 30px;
            margin-top: 35px;
            color: white;
        }

        @media (max-width: 991px) {
            .navbar-nav .nav-item {
                text-align: center;
            }

            .navbar .btn {
                width: 100%;
                margin-top: 10px;
            }
        }

        @media (max-width: 767px) and (min-width: 320px) {
            .logo-text {
                font-size: 7px;
                margin-right: 20px;
            }

            .navbar-toggler {
                border: none;
            }

            .login-btn-toggle {
                margin-left: 0 !important;
            }

            .welcome-text {
                display: none;
            }

            .navbar-brand {
                width: 250px;
                height: 100%;
            }

            .navbar-brand img {
                margin-left: 10px;
                object-fit: contain;
            }
        }

        .hero-section {
            background-size: cover;
            background-position: center;
            color: white;
            padding: 230px 0;
            height: 100vh;
            display: grid;
            place-items: center;
        }

        .feature-icon {
            font-size: 3rem;
            margin-bottom: 1rem;
        }

        .cta-section {
            background-color: #f8f9fa;
            padding: 50px 0;
        }

        html {
            scroll-padding-top: 70px;
        }

        .navbar-nav .nav-link {
            position: relative;
            color: #000;
            transition: color 0.3s ease;
        }

        .navbar-nav .nav-link::after {
            content: '';
            position: absolute;
            width: 100%;
            height: 2px;
            bottom: 0;
            left: 0;
            background-color: var(--primary-color);
            transform: scaleX(0);
            transition: transform 0.3s ease;
        }

        .navbar-nav .nav-link:hover, .navbar-nav .nav-link.active {
            color: var(--primary-color);
        }

        .navbar-nav .nav-link:hover::after, .navbar-nav .nav-link.active::after {
            transform: scaleX(1);
        }

        .hover-card {
            transition: transform 0.3s ease-in-out, box-shadow 0.3s ease-in-out;
        }

        .hover-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15) !important;
        }

        .feature-icon {
            transition: transform 0.3s ease-in-out;
        }

        .hover-card:hover .feature-icon {
            transform: scale(1.1);
        }

        .feature-icon-circle {
            width: 100px;
            height: 100px;
            border-radius: 50% !important;
            background-color: #f8f9fa;
            box-shadow: 0 0 15px rgba(0,0,0,0.1);
            transition: all 0.3s ease;
        }

        .feature-icon-circle i {
            font-size: 2.5rem;
        }

        .feature-card:hover .feature-icon-circle {
            transform: scale(1.1) !important;
            box-shadow: 0 0 20px rgba(0,0,0,0.15) !important;
        }

        .feature-card {
            padding: 20px;
            border-radius: 10px !important;
            transition: all 0.3s ease;
        }

        .feature-card:hover {
            background-color: #ffffff;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }

        .button-primary {
            background-color: var(--primary-color) !important;
            color: var(--text-light) !important;
            transition: all 0.3s ease !important;
            border: none;
        }

        .button-primary:hover {
            background-color: color-mix(in srgb, var(--primary-color) 85%, black) !important;
        }

        .button-primary:active {
            background-color: color-mix(in srgb, var(--primary-color) 70%, black) !important;
            transform: scale(0.98) !important;
        }

        .button-outline-primary {
            border: 1px solid var(--primary-color) !important;
            color: var(--primary-color) !important;
            transition: all .15s ease !important;
        }

        .button-outline-primary:hover {
            background-color: var(--primary-color) !important;
            color: var(--text-light) !important;
        }

        .modified-text-primary {
            color: var(--primary-color) !important;
        }

        .modified-bg-primary {
            background-color: var(--primary-color) !important;
        }

</style>
<body>
    <nav class="navbar navbar-expand-lg fixed-top">
        <div class="container-fluid ms-4 me-4">
            <button id="sidebarToggle" class="btn btn-outline-primary d-md-none ">
                <i class="fas fa-bars"></i>
            </button>
            <a class="navbar-brand" href="index.php">
                <img src="<?= $logo; ?>" alt="Pats Logo" style="height: 60px; width: 100%">
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
                                    <a class="dropdown-item" href="?page=notifications">
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
                            <i class="fa-solid fa-graduation-cap icon modified-text-primary"></i>
                            <span class="text nav-text modified-text-primary">Dashboard</span>
                        </a>
                    </li>

                    <li class="nav-link">
                        <a href="?page=my-courses">
                            <i class="fa-solid fa-clipboard icon modified-text-primary"></i>
                            <span class="text nav-text modified-text-primary">My Courses</span>
                        </a>
                    </li>

                    <li class="nav-link">
                        <a href="?page=certificates">
                            <i class="fa-solid fa-certificate icon modified-text-primary"></i>
                            <span class="text nav-text modified-text-primary">My Certificates</span>
                        </a>
                    </li>

                    
                    <li class="nav-link">
                        <a href="?page=notifications">
                        <i class="fa-solid fa-envelope  icon modified-text-primary"></i>
                            <span class="text nav-text modified-text-primary">Notifications</span>
                        </a>
                    </li>

                  

                    <li class="nav-link">
                        <a href="?page=profile">
                            <i class="fa-solid fa-user icon modified-text-primary"></i>
                            <span class="text nav-text modified-text-primary">Profile</span>
                        </a>
                    </li>  

                    <hr>
                    <div class="container mt-4">
                        <div class="welcome-text">
                            <h4 id="greeting"></h4>
                            <p>Welcome to your student profile. Here you can manage your personal information and see the course content.</p>
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

            greetingElement.textContent = greeting + ", <?php echo $_SESSION['fullname']; ?>";
        }

        setGreeting();
        // Update greeting every minute
        setInterval(setGreeting, 60000);
        });

    </script>
    <script>
        // Function to check for new notifications
       function checkNewNotifications() {
            fetch('get-notification.php')
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
                        <a class="dropdown-item" href="?page=notifications&id=${notification.id}">
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
</body>
</html>

