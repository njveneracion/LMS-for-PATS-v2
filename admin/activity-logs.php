<?php
// Define the date range you want to cover (e.g., the last 7 days)
$startDate = date('Y-m-d', strtotime('-7 days'));
$endDate = date('Y-m-d');

// Query to get the count of distinct users who logged in each day
$sqlLogs = "
    SELECT dates.log_date, COALESCE(COUNT(DISTINCT a.user_id), 0) as user_count 
    FROM (
        SELECT DATE_SUB('$endDate', INTERVAL n DAY) as log_date 
        FROM (SELECT 0 as n UNION ALL SELECT 1 UNION ALL SELECT 2 UNION ALL SELECT 3 UNION ALL SELECT 4 UNION ALL SELECT 5 UNION ALL SELECT 6) as nums
        WHERE DATE_SUB('$endDate', INTERVAL n DAY) >= '$startDate'
        ORDER BY log_date
    ) as dates
    LEFT JOIN activity_logs a ON DATE(a.created_at) = dates.log_date AND a.action = 'Login'
    GROUP BY dates.log_date
    ORDER BY dates.log_date
";
$resultLogs = $connect->query($sqlLogs);
$loginData = [];

if ($resultLogs->num_rows > 0) {
    while($row = $resultLogs->fetch_assoc()) {
        $loginData[] = $row;
    }
}

// Convert login data to JSON for JavaScript use
$loginDataJSON = json_encode(array_reverse($loginData)); // Reversing to show the oldest date first

// Modify the query to include more user details
$sqlActivityLogs = "
    SELECT al.*, u.username, u.email, u.fullname 
    FROM activity_logs al
    LEFT JOIN users u ON al.user_id = u.user_id
    ORDER BY al.created_at DESC
";
$resultActivityLogs = $connect->query($sqlActivityLogs);
$logs = [];

if ($resultActivityLogs->num_rows > 0) {
    while($row = $resultActivityLogs->fetch_assoc()) {
        $logs[] = $row;
    }
}

// Convert logs data to JSON if needed
$logsJSON = json_encode($logs);

$connect->close();
?>


<div class="container-fluid mt-4">
    <div class="row">
        <div class="col-12">
            <div class="card shadow-sm mb-4">
                <div class="card-body">
                    <h2 class="card-title">Activity Based on Date</h2>
                    <hr>
                    <div class="chart-container" style="position: relative; height: 50vh; width: 100%;">
                        <canvas id="userLoginChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-body">
                    <h2 class="card-title">All Activity Logs</h2>
                    <hr>
                    <div class="mb-3">
                        <input type="text" id="logSearch" class="form-control" placeholder="Search logs...">
                    </div>
                    <div class="table-responsive">
                        <table class="table table-hover table-striped" id="logTable">
                            <thead class="table-light">
                                <tr>
                                    <th>Date</th>
                                    <th>User ID</th>
                                    <th>Username</th>
                                    <th>Full Name</th>
                                    <th>Email</th>
                                    <th>Action</th>
                                    <th>Details</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($logs as $log): 
                                    $formattedDate = date('M j, Y, g:i A', strtotime($log['created_at'])); 
                                ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($formattedDate); ?></td>
                                    <td><?php echo htmlspecialchars($log['user_id']); ?></td>
                                    <td><?php echo htmlspecialchars($log['username']); ?></td>
                                    <td><?php echo htmlspecialchars($log['fullname']); ?></td>
                                    <td><?php echo htmlspecialchars($log['email']); ?></td>
                                    <td><?php echo htmlspecialchars($log['action']); ?></td>
                                    <td><?php echo htmlspecialchars($log['description']); ?></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Include Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    // Data for User Login Chart
    const loginData = <?php echo $loginDataJSON; ?>;
    const loginLabels = loginData.map(log => {
        const date = new Date(log.log_date);
        return date.toLocaleString('en-US', { month: 'short', day: 'numeric', year: 'numeric' });
    });
    const loginCounts = loginData.map(log => log.user_count);

    // Create User Login Chart
    const userLoginCtx = document.getElementById('userLoginChart').getContext('2d');
    const userLoginChart = new Chart(userLoginCtx, {
        type: 'line',
        data: {
            labels: loginLabels,
            datasets: [{
                label: 'Number of Users Logged In',
                data: loginCounts,
                backgroundColor: 'rgba(15, 111, 197, 0.2)',
                borderColor: 'rgba(15, 111, 197, 1)',
                borderWidth: 2,
                fill: true
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: true,
                    position: 'top',
                },
                tooltip: {
                    mode: 'index',
                    intersect: false,
                }
            },
            scales: {
                x: {
                    display: true,
                    title: {
                        display: true,
                        text: 'Date'
                    }
                },
                y: {
                    display: true,
                    title: {
                        display: true,
                        text: 'Number of Logins'
                    },
                    beginAtZero: true
                }
            }
        }
    });

    // Make the chart responsive
    function resizeChart() {
        userLoginChart.resize();
    }

    window.addEventListener('resize', resizeChart);

    // Add search functionality
    document.getElementById('logSearch').addEventListener('keyup', function() {
        const searchTerm = this.value.toLowerCase();
        const table = document.getElementById('logTable');
        const rows = table.getElementsByTagName('tr');

        for (let i = 1; i < rows.length; i++) {
            const row = rows[i];
            const cells = row.getElementsByTagName('td');
            let found = false;

            for (let j = 0; j < cells.length; j++) {
                const cellText = cells[j].textContent.toLowerCase();
                if (cellText.includes(searchTerm)) {
                    found = true;
                    break;
                }
            }

            row.style.display = found ? '' : 'none';
        }
    });
</script>