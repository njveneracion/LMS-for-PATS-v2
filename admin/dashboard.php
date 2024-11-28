<?php
// Query to get count of active users
$activeUsersQuery = "SELECT 
                        SUM(CASE WHEN u.role = 'student' THEN 1 ELSE 0 END) AS active_students,
                        SUM(CASE WHEN u.role = 'instructor' THEN 1 ELSE 0 END) AS active_instructors,
                        COUNT(*) AS total_active
                     FROM active_sessions AS a 
                     JOIN users AS u ON a.user_id = u.user_id 
                     WHERE u.role IN ('student', 'instructor')";
$activeUsersResult = mysqli_query($connect, $activeUsersQuery);
$activeUsersData = mysqli_fetch_assoc($activeUsersResult);

// Query to get total number of students and instructors
$usersQuery = "SELECT 
                SUM(CASE WHEN role = 'student' THEN 1 ELSE 0 END) AS total_students,
                SUM(CASE WHEN role = 'instructor' THEN 1 ELSE 0 END) AS total_instructors
               FROM users";
$usersResult = mysqli_query($connect, $usersQuery);
$usersData = mysqli_fetch_assoc($usersResult);

// Query to get total number of courses
$coursesQuery = "SELECT COUNT(*) AS total FROM courses";
$coursesResult = mysqli_query($connect, $coursesQuery);
$coursesData = mysqli_fetch_assoc($coursesResult);

// Query to get total number of enrollments
$enrollmentsQuery = "SELECT COUNT(*) AS total FROM enrollments";
$enrollmentsResult = mysqli_query($connect, $enrollmentsQuery);
$enrollmentsData = mysqli_fetch_assoc($enrollmentsResult);

// Query for student distribution by course
$studentDistributionQuery = "SELECT c.course_name, COUNT(e.user_id) as student_count 
                             FROM courses c 
                             LEFT JOIN enrollments e ON c.course_id = e.course_id 
                             GROUP BY c.course_id";
$studentDistributionResult = mysqli_query($connect, $studentDistributionQuery);
$studentDistributionData = [];
while ($row = mysqli_fetch_assoc($studentDistributionResult)) {
    $studentDistributionData[] = $row;
}

// Query for user registration trend (last 7 days)
$registrationTrendQuery = "SELECT 
                               DATE(created_at) as date, 
                               COUNT(*) as count 
                           FROM users 
                           WHERE created_at >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)
                           GROUP BY DATE(created_at) 
                           ORDER BY DATE(created_at)";
$registrationTrendResult = mysqli_query($connect, $registrationTrendQuery);
$registrationTrendData = [];

// Initialize an array with the last 7 days, setting count to 0
for ($i = 6; $i >= 0; $i--) {
    $date = date('Y-m-d', strtotime("-$i days"));
    $registrationTrendData[$date] = ['date' => $date, 'count' => 0];
}

// Fill in the actual counts
while ($row = mysqli_fetch_assoc($registrationTrendResult)) {
    $registrationTrendData[$row['date']] = $row;
}

// Convert to indexed array and ensure all dates are present
$registrationTrendData = array_values($registrationTrendData);

// Query for recent activity logs
$activityLogsQuery = "SELECT u.fullname, a.action, a.created_at
                      FROM activity_logs a
                      JOIN users u ON a.user_id = u.user_id
                      ORDER BY a.created_at DESC
                      LIMIT 10";
$activityLogsResult = mysqli_query($connect, $activityLogsQuery);
$activityLogsData = [];
while ($row = mysqli_fetch_assoc($activityLogsResult)) {
    $activityLogsData[] = $row;
}

// Close the database connection
mysqli_close($connect);
?>

<style>
    body {
        font-family: 'Poppins', sans-serif;
        background-color: var(--background-color);
        color: var(--text-color);
    }

    .dashboard-card {
        border: none;
        border-radius: 16px;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.05);
        transition: all 0.3s ease;
        background: var(--sidebar-color);
        overflow: hidden;
    }

    .dashboard-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 8px 25px rgba(0, 0, 0, 0.1);
    }

    .card-title {
        font-weight: 600;
        font-size: 1.1rem;
        color: var(--text-color);
        margin-bottom: 1.5rem;
    }

    /* Stats Cards */
    .stats-card {
        padding: 1.5rem;
        position: relative;
        overflow: hidden;
    }

    .stats-card::before {
        content: '';
        position: absolute;
        top: 0;
        right: 0;
        width: 100px;
        height: 100%;
        background: linear-gradient(45deg, transparent, rgba(255,255,255,0.1));
    }

    .stats-card i {
        font-size: 2rem;
        margin-bottom: 1rem;
        opacity: 0.9;
    }

    .stats-card h2 {
        font-size: 2.2rem;
        font-weight: 700;
        margin-bottom: 0.5rem;
    }

    /* Charts */
    .chart-container {
        position: relative;
        height: 350px;
        padding: 1rem;
    }

    /* Activity List */
    .list-group-item {
        border-left: none;
        border-right: none;
        border-top: none;
        padding: 1rem 1.25rem;
        margin-bottom: 0;
        border-bottom: 1px solid rgba(0,0,0,0.05);
        transition: background-color 0.3s ease;
    }

    .list-group-item:hover {
        background-color: rgba(15, 111, 197, 0.05);
    }

    .list-group-item strong {
        color: var(--primary-color);
        font-weight: 600;
    }

    .activity-timestamp {
        font-size: 0.85rem;
        color: var(--text-color);
    }

    /* Custom Scrollbar */
    ::-webkit-scrollbar {
        width: 8px;
    }

    ::-webkit-scrollbar-track {
        background: #f1f1f1;
    }

    ::-webkit-scrollbar-thumb {
        background: #888;
        border-radius: 4px;
    }

    /* Animation */
    @keyframes fadeIn {
        from { opacity: 0; transform: translateY(20px); }
        to { opacity: 1; transform: translateY(0); }
    }

    .dashboard-card {
        animation: fadeIn 0.6s ease-out forwards;
    }
</style>

<div class="container-fluid mt-4">
    <h1 class="mb-4">Dashboard</h1>
    <div class="row">
        <div class="col-md-6 col-lg-3 mb-4">
            <div class="card dashboard-card bg-primary text-white">
                <div class="card-body">
                    <h5 class="card-title text-white">Total Students</h5>
                    <h2><i class="fa-solid fa-user-graduate"></i> <?php echo $usersData['total_students']; ?></h2>
                </div>
            </div>
        </div>
        <div class="col-md-6 col-lg-3 mb-4">
            <div class="card dashboard-card bg-success text-white">
                <div class="card-body">
                    <h5 class="card-title text-white">Total Instructors</h5>
                    <h2><i class="fa-solid fa-chalkboard-teacher"></i> <?php echo $usersData['total_instructors']; ?></h2>
                </div>
            </div>
        </div>
        <div class="col-md-6 col-lg-3 mb-4">
            <div class="card dashboard-card bg-info text-white">
                <div class="card-body">
                    <h5 class="card-title text-white">Total Courses</h5>
                    <h2><i class="fa-solid fa-book"></i> <?php echo $coursesData['total']; ?></h2>
                </div>
            </div>
        </div>
        <div class="col-md-6 col-lg-3 mb-4">
            <div class="card dashboard-card bg-warning text-white">
                <div class="card-body">
                    <h5 class="card-title text-white">Total Enrollments</h5>
                    <h2><i class="fa-solid fa-user-check"></i> <?php echo $enrollmentsData['total']; ?></h2>
                </div>
            </div>
        </div>
    </div>
    
    <div class="row mt-4">
        <div class="col-md-6 mb-4">
            <div class="card dashboard-card">
                <div class="card-body">
                    <h5 class="card-title">Student Distribution by Course</h5>
                    <div class="chart-container">
                        <canvas id="studentDistributionChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-6 mb-4">
            <div class="card dashboard-card">
                <div class="card-body">
                    <h5 class="card-title">User Registration Trend (Last 7 Days)</h5>
                    <div class="chart-container">
                        <canvas id="registrationTrendChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="row mt-4">
        <div class="col-md-6 mb-4">
            <div class="card dashboard-card">
                <div class="card-body">
                    <h5 class="card-title">Active Users</h5>
                    <div class="chart-container">
                        <canvas id="activeUsersChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-6 mb-4">
            <div class="card dashboard-card">
                <div class="card-body">
                    <h5 class="card-title">Recent Activity</h5>
                    <ul class="list-group list-group-flush">
                        <?php foreach ($activityLogsData as $log): ?>
                            <li class="list-group-item">
                                <strong><?php echo htmlspecialchars($log['fullname']); ?></strong>
                                <?php echo htmlspecialchars($log['action']); ?>
                                <small class="text-muted float-end"><?php echo date('M d, Y H:i', strtotime($log['created_at'])); ?></small>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    // Student Distribution Bar Chart
    let ctxDistribution = document.getElementById('studentDistributionChart').getContext('2d');
    new Chart(ctxDistribution, {
        type: 'bar',
        data: {
            labels: <?php echo json_encode(array_column($studentDistributionData, 'course_name')); ?>,
            datasets: [{
                label: 'Number of Students',
                data: <?php echo json_encode(array_column($studentDistributionData, 'student_count')); ?>,
                backgroundColor: ' rgba(15, 111, 197, 0.8)',
                borderColor: ' rgba(15, 111, 197, 1)',
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: false
                }
            },
            scales: {
                x: {
                    ticks: {
                        autoSkip: false,
                        maxRotation: 90,
                        minRotation: 0
                    }
                },
                y: {
                    beginAtZero: true,
                    title: {
                        display: true,
                        text: 'Number of Students'
                    }
                }
            }
        }
    });

    // User Registration Trend Line Chart
    let ctxRegistrationTrend = document.getElementById('registrationTrendChart').getContext('2d');
    new Chart(ctxRegistrationTrend, {
        type: 'line',
        data: {
            labels: <?php echo json_encode(array_column($registrationTrendData, 'date')); ?>,
            datasets: [{
                label: 'New Users',
                data: <?php echo json_encode(array_column($registrationTrendData, 'count')); ?>,
                 borderColor: 'rgba(<?= hexdec(substr($theme['primaryColor'], 1, 2)) ?>, <?= hexdec(substr($theme['primaryColor'], 3, 2)) ?>, <?= hexdec(substr($theme['primaryColor'], 5, 2)) ?>, 1)',
                    backgroundColor: 'rgba(<?= hexdec(substr($theme['primaryColor'], 1, 2)) ?>, <?= hexdec(substr($theme['primaryColor'], 3, 2)) ?>, <?= hexdec(substr($theme['primaryColor'], 5, 2)) ?>, 0.2)',
                tension: 0.1,
                fill: true
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: { y: { beginAtZero: true } }
        }
    });

    // Active Users Doughnut Chart
    let ctxActiveUsers = document.getElementById('activeUsersChart').getContext('2d');
    new Chart(ctxActiveUsers, {
        type: 'doughnut',
        data: {
            labels: ['Active Students', 'Active Instructors', 'Inactive Users'],
            datasets: [{
                data: [
                    <?php echo $activeUsersData['active_students']; ?>,
                    <?php echo $activeUsersData['active_instructors']; ?>,
                    <?php echo $usersData['total_students'] + $usersData['total_instructors'] - $activeUsersData['total_active']; ?>
                ],
                 backgroundColor: [
                        'rgba(<?= hexdec(substr($theme['primaryColor'], 1, 2)) ?>, <?= hexdec(substr($theme['primaryColor'], 3, 2)) ?>, <?= hexdec(substr($theme['primaryColor'], 5, 2)) ?>, 0.8)',
                        'rgba(54, 162, 235, 0.8)',
                        'rgba(201, 203, 207, 0.8)'
                    ]
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
        }
    });
</script>
