<?php
    session_start();
    include '../config/db.php';
    include '../functions/logActivity.php';
    include '../functions/generateUniqueID.php';

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
    <link rel="stylesheet" href="../assets/css/side-nav.css?v1">
    <link rel="stylesheet" href="../assets/css/color.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11.0.19/dist/sweetalert2.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.0.19/dist/sweetalert2.all.min.js"></script>
    
</head>
<style>
    *{
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
                            <i class="fa-solid fa-graduation-cap icon "></i>
                            <span class="text nav-text">Dashboard</span>
                        </a>
                    </li>

                    <li class="nav-link">
                        <a href="?page=students">
                            <i class="fa-solid fa-users icon"></i>
                            <span class="text nav-text">Students</span> 
                        </a>
                    </li>

                    <li class="nav-link">
                        <a href="?page=profile">
                           <i class="fa-solid fa-user-tie icon"></i>
                            <span class="text nav-text">Profile</span>
                        </a>
                    </li>  

                    <hr>

                    <li class="nav-link">
                        <a href="?page=quiz">
                            <i class="fa-solid fa-clipboard icon "></i>
                            <span class="text nav-text">Quiz</span>
                        </a>
                    </li>

                    <li class="nav-link">
                        <a href="?page=assessments">
                        <i class="fa-solid fa-file-alt icon "></i>
                            <span class="text nav-text">Assessments</span>
                        </a>
                    </li>

                    

                    <li class="nav-link">
                        <a href="?page=grade-task-sheets">
                        <i class="fa-solid fa-marker icon "></i>
                            <span class="text nav-text">Grade Task Sheet</span> 
                        </a>
                    </li>

                    <hr>

                    <li class="nav-link">
                        <a href="?page=send-message">
                            <i class="fa-solid fa-message icon"></i>
                            <span class="text nav-text">Send Message</span> 
                        </a>
                    </li>

                    <li class="nav-link">
                        <a href="?page=notifications">
                        <i class="fa-solid fa-envelope icon"></i>
                            <span class="text nav-text">Notifications</span> 
                        </a>
                    </li>


                    <li class="nav-link">
                        <a href="?page=announcements">
                            <i class="fa-solid fa-bullhorn icon"></i>
                            <span class="text nav-text">Announcement</span>
                        </a>
                    </li>

                    <li class="nav-link">
                        <a href="?page=discussion">
                            <i class="fa-solid fa-comments icon"></i>
                            <span class="text nav-text">Discussion</span>
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