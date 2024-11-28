<?php
include '../config/db.php';
include '../functions/logActivity.php';
session_start();
$theme = json_decode(file_get_contents('theme.json'), true);

// Check if user is logged in
if (!isset($_SESSION['userID'])) {
    // Redirect to login page or handle unauthorized access
    header("Location: ../auth/login.php");
    exit();
}

$userID = $_SESSION['userID']; // Ensure you have the user ID in the session

// Determine the page to include
$page = isset($_GET['page']) ? $_GET['page'] : 'dashboard';

switch($page){
    case 'dashboard':
        $filename = 'dashboard.php';
        break;
    case 'activity-logs':
        $filename = 'activity-logs.php';
        break;
    case 'manage-users':
        $filename = 'manage-users.php';
        break;
    case 'manage-courses':
        $filename = 'manage-courses.php';
        break;
    case 'generate-reports':
        $filename = 'generate-reports.php';
        break;
    case 'profile':
        $filename = 'profile.php';
        break;
    case 'edit-certificate':
        $filename = 'edit-certificate.php';
        break;
    case 'edit-hero-section':
        $filename = '../cms/edit-hero.php';
        break;
    case 'edit-header':
        $filename = '../cms/edit-header.php';
        break;
    case 'edit-skills':
        $filename = '../cms/edit-skills.php';
        break;
    case 'edit-courses':
        $filename = '../cms/edit-courses.php';
        break;
    case 'edit-cta':
        $filename = '../cms/edit-cta.php';
        break;
    case 'edit-testimonials':
        $filename = '../cms/edit-testimonials.php';
        break;
    case 'edit-faqs':
        $filename = '../cms/edit-faqs.php';
        break;
    case 'edit-footer':
        $filename = '../cms/edit-footer.php';
        break;
    case 'edit-theme':
        $filename = '../cms/edit-theme.php';
        break;
    
    case 'logout':
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
$profilePic = $pic['profile_picture'] ?? 'default.png'; // Set default image if no profile picture exists


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
    <title>Administrator LMS PATS</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/side-nav.css">
    <link rel="stylesheet" href="../assets/css/color.css">
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
            border-radius: 0 !important;
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
    <nav class="navbar navbar-expand-lg fixed-top ">
        <div class="container-fluid ms-4 me-4">
            <button id="sidebarToggle" class="btn btn-outline-primary d-md-none ">
                <i class="fas fa-bars"></i>
            </button>
            <a class="navbar-brand">
                <img src="<?= $logo; ?>" alt="Pats Logo" style="height: 60px;">
            </a>
            <div class="d-flex align-items-center ms-auto">
                <div class="dropdown">
                    <button class="btn btn-link me-3" type="button" id="profileDropdown" data-bs-toggle="dropdown" aria-expanded="false">
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
                        <a href="?page=activity-logs">
                            <i class="fa-solid fa-gear icon modified-text-primary"></i>
                            <span class="text nav-text modified-text-primary">Activity Logs</span>
                        </a>
                    </li>

                    <li class="nav-link">
                        <a href="?page=manage-users">
                        <i class="fa-solid fa-users-gear icon modified-text-primary"></i>
                            <span class="text nav-text modified-text-primary">Manage Users</span>
                        </a>
                    </li>

                    <li class="nav-link">
                        <a href="?page=manage-courses">
                        <i class="fa-solid fa-book icon modified-text-primary"></i>
                            <span class="text nav-text modified-text-primary">Manage Courses</span>
                        </a>
                    </li>

                    <li class="nav-link">
                        <a href="?page=generate-reports">
                          <i class="fa-solid fa-chart-line icon modified-text-primary"></i>
                            <span class="text nav-text modified-text-primary">Generate Reports</span>
                        </a>
                    </li>

                    <li class="nav-link">
                        <a href="?page=profile">
                        <i class="fa-solid fa-user-shield icon modified-text-primary"></i>
                            <span class="text nav-text modified-text-primary">Profile</span>
                        </a>
                    </li>

                   <li class="nav-link">
                        <a href="?page=edit-certificate">
                            <i class="fa-solid fa-edit icon modified-text-primary"></i>
                            <span class="text nav-text modified-text-primary">Edit Certificate</span>
                        </a>
                    </li>

                    <li class="nav-item dropdown">
                        <a class="nav-link " role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="fa-solid fa-file-alt icon modified-text-primary"></i>
                            <span class="text nav-text modified-text-primary">Edit Landing Page</span>
                        </a>
                        <div class="dropdown-menu" aria-labelledby="navbarDropdown">
                            <a class="dropdown-item" href="?page=edit-header">Logo</a>
                            <a class="dropdown-item" href="?page=edit-hero-section">Hero Section</a>
                            <a class="dropdown-item" href="?page=edit-skills">Skills</a>
                            <a class="dropdown-item" href="?page=edit-courses">Courses</a>
                            <a class="dropdown-item" href="?page=edit-cta">CTA</a>
                            <a class="dropdown-item" href="?page=edit-testimonials">Testimonials</a>
                            <a class="dropdown-item" href="?page=edit-faqs">FAQS</a>
                            <a class="dropdown-item" href="?page=edit-footer">Footer</a>
                        </div>
                    </li>

                    <li class="nav-link">
                        <a href="?page=edit-theme">
                            <i class="fa-solid icon fa-droplet modified-text-primary"></i>
                            <span class="text nav-text modified-text-primary">Theme</span>
                        </a>
                    </li>

               

                    <hr>
                    <div class="container mt-4">
                        <div class="welcome-text">
                            <h4 id="greeting"></h4>
                            <p>Welcome to your admin profile. Here you can manage system settings and oversee all aspects of the learning management system.</p>
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
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/2.9.3/umd/popper.min.js"></script>
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
            const currentPage = window.location.search || '?page=sa_dashboard';
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
            // This is a placeholder function. Replace with actual AJAX call to your server.
            fetch('get_notifications.php')
                .then(response => response.json())
                .then(data => {
                    updateNotificationDropdown(data);
                    document.getElementById('notificationDot').style.display = data.length > 0 ? 'block' : 'none';
                })
                .catch(error => console.error('Error:', error));
        }

        // Function to update the notification dropdown
        function updateNotificationDropdown(notifications) {
            const dropdownMenu = document.querySelector('#notificationDropdown + .dropdown-menu');
            if (notifications.length === 0) {
                dropdownMenu.innerHTML = '<li><h6 class="dropdown-header">Notifications</h6></li><li><hr class="dropdown-divider"></li><li><a class="dropdown-item" href="#">No new notifications</a></li>';
            } else {
                let notificationHtml = '<li><h6 class="dropdown-header">Notifications</h6></li><li><hr class="dropdown-divider"></li>';
                notifications.forEach(notification => {
                    notificationHtml += `<li><a class="dropdown-item" href="${notification.link}">${notification.message}</a></li>`;
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


