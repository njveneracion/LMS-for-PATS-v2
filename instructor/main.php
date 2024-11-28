<?php
    session_start();
    include '../config/db.php';
    include '../functions/logActivity.php';
    include '../functions/generateUniqueID.php';
     $theme = json_decode(file_get_contents('../admin/theme.json'), true);

    $uniqueID = generateSimpleUniqueID();
   
    $page = isset($_GET['page']) ? $_GET['page'] : 'dashboard';

    $userID = $_SESSION['userID'];

    // Clean up stale sessions
    $timeout = 60 * 60; // 1 hour
    $cleanupQuery = "DELETE FROM active_sessions WHERE last_activity < DATE_SUB(NOW(), INTERVAL ? SECOND)";
    $stmtCleanup = mysqli_prepare($connect, $cleanupQuery);
    mysqli_stmt_bind_param($stmtCleanup, "i", $timeout);
    mysqli_stmt_execute($stmtCleanup);

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

    switch($page){
        case 'dashboard':
            $filename = 'dashboard.php';
            break;
        case 'quiz':
            $filename = 'quiz.php';
            break;
        case 'assessments':
            $filename = 'assessments.php';
            break;
        case 'assessment-question':
            $filename = 'assessment-question.php';
            break;
        case 'quiz-question':
            $filename = 'quiz-question.php';
            break;
        case 'announcements':
            $filename = 'announcements.php';
            break;
        case 'discussion':
            $filename = 'discussions.php';
            break;
        case 'students':
            $filename = 'students.php';
            break;
        case 'send-message':
            $filename = 'send-message.php';
            break;
        case 'notifications':
            $filename = 'notifications.php';
            break;
        case 'grade-task-sheets':
            $filename = 'grade-task-sheets.php';
            break;
        case 'course-content':
            $filename = 'course-content.php';
            break;
        case 'profile':
            $filename = 'profile.php';
            break;
        case 'logout':
            $session_id = session_id();
            $query = "DELETE FROM active_sessions WHERE session_id = ?";
            $stmt = $connect->prepare($query);
            $stmt->bind_param("s", $session_id);
            $stmt->execute();    

            $action = 'Logout';
            $description = 'User logged out successfully.';
            logActivity($userID, $action, $description);
            session_unset(); 
            session_destroy(); 
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

    $sqlHeader = "SELECT * FROM header";
    $resultHeader = mysqli_query($connect, $sqlHeader);
    $header = mysqli_fetch_assoc($resultHeader);
    $logo = $header['logo'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>LMS Instructor Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/side-nav.css">
    <link rel="stylesheet" href="../assets/css/color.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11.0.19/dist/sweetalert2.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.0.19/dist/sweetalert2.all.min.js"></script>
    
</head>
<style>
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
                <div class="dropdown dropdown-center">
                    <button class="btn btn-link position-relative" type="button" id="notificationDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="fas fa-bell fa-lg text-secondary"></i>
                        <span id="notificationDot" class="position-absolute top-0 start-100 translate-middle p-1 bg-danger border border-light rounded-circle" style="display: none;"></span>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="notificationDropdown" style="width: 250px; max-height: 400px; overflow-y: auto;">
                        <li><h6 class="dropdown-header">Notifications</h6></li>
                        <li><hr class="dropdown-divider"></li>
                        <?php if (empty($notifications)): ?>
                            <li><a class="dropdown-item" href="#">No new notifications</a></li>
                        <?php else: ?>
                            <?php foreach ($notifications as $notification): ?>
                                <li>
                                    <a class="dropdown-item" href="?page=notifications&id=<?php echo $notification['id']; ?>">
                                        <?php echo htmlspecialchars($notification['message']); ?>
                                        <small class="text-muted d-block"><?php echo date('M d, Y H:i', strtotime($notification['created_at'])); ?></small>
                                    </a>
                                </li>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </ul>
                </div>
                <div class="dropdown">
                    <button class="btn btn-link me-3" type="button" id="profileDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                    <img src="../uploads/profile-pictures/<?php echo (!empty($profilePic)) ? $profilePic : 'default.png'; ?>" alt="profile photo" style="width: 40px; height: 40px; border-radius: 50%; object-fit: cover;">
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="profileDropdown">
                        <li><span class="dropdown-item-text"><?php echo $_SESSION['fullname']; ?></span></li>
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
                        <a href="?page=students">
                            <i class="fa-solid fa-users icon modified-text-primary"></i>
                            <span class="text nav-text modified-text-primary">Students</span> 
                        </a>
                    </li>

                    <li class="nav-link">
                        <a href="?page=profile">
                           <i class="fa-solid fa-user-tie icon modified-text-primary"></i>
                            <span class="text nav-text modified-text-primary">Profile</span>
                        </a>
                    </li>  

                    <hr>

                    <li class="nav-link">
                        <a href="?page=quiz">
                            <i class="fa-solid fa-clipboard icon modified-text-primary"></i>
                            <span class="text nav-text modified-text-primary">Quiz</span>
                        </a>
                    </li>

                    <li class="nav-link">
                        <a href="?page=assessments">
                        <i class="fa-solid fa-file-alt icon modified-text-primary"></i>
                            <span class="text nav-text modified-text-primary">Assessments</span>
                        </a>
                    </li>

                    

                    <li class="nav-link">
                        <a href="?page=grade-task-sheets">
                        <i class="fa-solid fa-marker icon modified-text-primary"></i>
                            <span class="text nav-text modified-text-primary">Grade Task Sheet</span> 
                        </a>
                    </li>

                    <hr>

                    <li class="nav-link">
                        <a href="?page=send-message">
                            <i class="fa-solid fa-message icon modified-text-primary"></i>
                            <span class="text nav-text modified-text-primary">Send Message</span> 
                        </a>
                    </li>

                    <li class="nav-link">
                        <a href="?page=notifications">
                        <i class="fa-solid fa-envelope icon modified-text-primary"></i>
                            <span class="text nav-text modified-text-primary">Notifications</span> 
                        </a>
                    </li>


                    <li class="nav-link">
                        <a href="?page=announcements">
                            <i class="fa-solid fa-bullhorn icon modified-text-primary"></i>
                            <span class="text nav-text modified-text-primary">Announcement</span>
                        </a>
                    </li>

                    <li class="nav-link">
                        <a href="?page=discussion">
                            <i class="fa-solid fa-comments icon modified-text-primary"></i>
                            <span class="text nav-text modified-text-primary">Discussion</span>
                        </a>
                    </li>

                  
                    
                    <hr>
                    <div class="container mt-4">
                        <div class="welcome-text">
                            <h3 id="greeting" class="text-light"></h3>
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
            const currentPage = window.location.search || '?page=i_dashboard';
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
                            <a class="dropdown-item" href="?page=inotifications&id=${notification.id}">
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