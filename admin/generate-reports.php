<?php
// Default number of results per page
$default_results_per_page = 10;

// Determine the number of results per page
if (isset($_POST['results_per_page'])) {
    $results_per_page = $_POST['results_per_page'];
} else {
    $results_per_page = $default_results_per_page;
}

// Determine the page number
if (isset($_GET['pages'])) {
    $page = $_GET['pages'];
} else {
    $page = 1;
}

if (isset($_GET['edit']) && $_GET['edit'] == 'edit-pdf-header') {
    include '../cms/edit-pdf-header.php';
    return;
}

// Calculate the starting result on the current page
$start_from = ($page - 1) * $results_per_page;

// Fetch all courses
$course_query = "SELECT course_id, course_name FROM courses";
$course_result = mysqli_query($connect, $course_query);

// Determine the report type
$report_type = isset($_GET['report']) ? $_GET['report'] : 'all_students';

// Fetch students based on the selected report type
if ($report_type == 'all_students') {
    $student_query = "SELECT user_id, fullname AS student_name FROM users WHERE role = 'student' ORDER BY user_id ASC LIMIT $start_from, $results_per_page";
    $count_query = "SELECT COUNT(user_id) AS total FROM users WHERE role = 'student'";
} elseif ($report_type == 'enrolled_students') {
    $student_query = "SELECT s.user_id, s.fullname AS student_name, c.course_name, c.course_code 
                      FROM users s 
                      JOIN enrollments e ON s.user_id = e.user_id 
                      JOIN courses c ON e.course_id = c.course_id 
                      WHERE s.role = 'student'
                      ORDER BY s.user_id ASC LIMIT $start_from, $results_per_page";
    $count_query = "SELECT COUNT(s.user_id) AS total 
                    FROM users s 
                    JOIN enrollments e ON s.user_id = e.user_id 
                    JOIN courses c ON e.course_id = c.course_id 
                    WHERE s.role = 'student'";
} elseif ($report_type == 'specific_course') {
    $selected_course = isset($_POST['course_id']) ? $_POST['course_id'] : '';
    $student_query = "SELECT s.user_id, s.fullname AS student_name, c.course_name, c.course_code 
                      FROM users s 
                      JOIN enrollments e ON s.user_id = e.user_id 
                      JOIN courses c ON e.course_id = c.course_id 
                      WHERE s.role = 'student'";

    if ($selected_course) {
        $student_query .= " AND c.course_id = '$selected_course'";
        $count_query = "SELECT COUNT(s.user_id) AS total 
                        FROM users s 
                        JOIN enrollments e ON s.user_id = e.user_id 
                        JOIN courses c ON e.course_id = c.course_id 
                        WHERE s.role = 'student' AND c.course_id = '$selected_course'";
    } else {
        $count_query = "SELECT COUNT(s.user_id) AS total 
                        FROM users s 
                        JOIN enrollments e ON s.user_id = e.user_id 
                        JOIN courses c ON e.course_id = c.course_id 
                        WHERE s.role = 'student'";
    }

    $student_query .= " ORDER BY s.user_id ASC LIMIT $start_from, $results_per_page";
} elseif ($report_type == 'completed_courses') {
    $student_query = "SELECT s.user_id, s.fullname AS student_name, c.course_name, c.course_code 
                      FROM users s 
                      JOIN enrollments e ON s.user_id = e.user_id 
                      JOIN courses c ON e.course_id = c.course_id 
                      WHERE s.role = 'student' AND e.status = 'completed'
                      ORDER BY s.user_id ASC LIMIT $start_from, $results_per_page";
    $count_query = "SELECT COUNT(s.user_id) AS total 
                    FROM users s 
                    JOIN enrollments e ON s.user_id = e.user_id 
                    JOIN courses c ON e.course_id = c.course_id 
                    WHERE s.role = 'student' AND e.status = 'completed'";
} elseif ($report_type == 'students_with_certificates') {
    $student_query = "SELECT DISTINCT s.user_id, s.fullname AS student_name, c.course_name, c.course_code 
                      FROM users s 
                      JOIN enrollments e ON s.user_id = e.user_id 
                      JOIN courses c ON e.course_id = c.course_id 
                      JOIN certificates cert ON s.user_id = cert.student_id 
                      WHERE s.role = 'student'
                      ORDER BY s.user_id ASC LIMIT $start_from, $results_per_page";
    $count_query = "SELECT COUNT(DISTINCT s.user_id) AS total 
                    FROM users s 
                    JOIN enrollments e ON s.user_id = e.user_id 
                    JOIN courses c ON e.course_id = c.course_id 
                    JOIN certificates cert ON s.user_id = cert.student_id 
                    WHERE s.role = 'student'";
} elseif ($report_type == 'all_instructors') {
    $student_query = "SELECT user_id, fullname AS instructor_name FROM users WHERE role = 'instructor' ORDER BY user_id ASC LIMIT $start_from, $results_per_page";
    $count_query = "SELECT COUNT(user_id) AS total FROM users WHERE role = 'instructor'";
} elseif ($report_type == 'instructors_with_courses') {
    $student_query = "SELECT i.user_id, i.fullname AS instructor_name, c.course_name, c.course_code 
                      FROM users i 
                      JOIN courses c ON i.user_id = c.user_id 
                      WHERE i.role = 'instructor'
                      ORDER BY i.user_id ASC LIMIT $start_from, $results_per_page";
    $count_query = "SELECT COUNT(i.user_id) AS total 
                    FROM users i 
                    JOIN courses c ON i.user_id = c.user_id 
                    WHERE i.role = 'instructor'";
}

$student_result = mysqli_query($connect, $student_query);
$count_result = mysqli_query($connect, $count_query);
$row = mysqli_fetch_assoc($count_result);
$total_records = $row['total'];
$total_pages = ceil($total_records / $results_per_page);
?>

    <style>
        :root {
            --primary-color: <?= $theme['primaryColor'] ?>;
            --secondary-color: <?= $theme['secondaryColor'] ?>;
            --background-color: <?= $theme['backgroundColor'] ?>;
            --text-color: <?= $theme['textColor'] ?>;
            --card-bg: #FFF;
        }

        body {
            font-family: 'Poppins', sans-serif;
            background-color: var(--background-color);
            color: var(--text-color);
        }

        .card {
            background-color: var(--card-bg);
            border: none;
            border-radius: 16px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.05);
            transition: all 0.3s ease;
        }

        .card-title {
            font-weight: 600;
            font-size: 1.1rem;
            color: var(--text-color);
            margin-bottom: 1.5rem;
        }

        .table thead {
            background-color: var(--primary-color);
            color: white;
        }

        .table-hover tbody tr:hover {
            background-color: rgba(15, 111, 197, 0.05);
        }

        .pagination .page-item.active .page-link {
            background-color: var(--primary-color);
            border-color: var(--primary-color);
        }

        .dropdown-menu {
            background-color: var(--card-bg);
        }

        .dropdown-item {
            color: var(--text-color);
        }

        .dropdown-item:hover {
            background-color: var(--primary-color);
            color: white;
        }

        .nav-pills .nav-link {
            color: var(--text-color);
            transition: all 0.3s ease;
        }

        .nav-pills .nav-link.active {
            background-color: var(--primary-color);
            color: white;
        }

    </style>
 <div class="container mt-5">
        <div class="d-flex align-items-center gap-3">
            <h1 class="">Generate Reports</h1>
            <div class="dropdown">
                <button class="btn btn-secondary" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                    Settings <i class="fa-solid fa-gear"></i>
                </button>
                <ul class="dropdown-menu">
                    <li><a class="dropdown-item" href="main.php?page=generate-reports&edit=edit-pdf-header">Edit PDF Page Header</a></li>
                </ul>
            </div>
        </div>
        <hr>
        <nav>
            <ul class="nav nav-pills mb-4">
                <li class="nav-item">
                    <a class="nav-link <?php echo $report_type == 'all_students' ? 'active' : ''; ?>" href="?page=generate-reports&report=all_students">All Students</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?php echo $report_type == 'enrolled_students' ? 'active' : ''; ?>" href="?page=generate-reports&report=enrolled_students">Enrolled Students</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?php echo $report_type == 'specific_course' ? 'active' : ''; ?>" href="?page=generate-reports&report=specific_course">Enrolled Students in Specific Course</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?php echo $report_type == 'completed_courses' ? 'active' : ''; ?>" href="?page=generate-reports&report=completed_courses">Completed Courses</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?php echo $report_type == 'students_with_certificates' ? 'active' : ''; ?>" href="?page=generate-reports&report=students_with_certificates">Students with Certificates</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?php echo $report_type == 'all_instructors' ? 'active' : ''; ?>" href="?page=generate-reports&report=all_instructors">All Instructors</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?php echo $report_type == 'instructors_with_courses' ? 'active' : ''; ?>" href="?page=generate-reports&report=instructors_with_courses">Instructors with Courses</a>
                </li>
            </ul>
        </nav>

        <form method="post" action="" class="mb-4">
            <div class="form-group">
                <label for="results_per_page">Results per page:</label>
                <select name="results_per_page" id="results_per_page" class="form-control" onchange="this.form.submit()">
                    <option value="5" <?php if ($results_per_page == 5) echo 'selected'; ?>>5</option>
                    <option value="10" <?php if ($results_per_page == 10) echo 'selected'; ?>>10</option>
                    <option value="30" <?php if ($results_per_page == 30) echo 'selected'; ?>>30</option>
                    <option value="50" <?php if ($results_per_page == 50) echo 'selected'; ?>>50</option>
                </select>
            </div>
        </form>

        <?php if ($report_type == 'specific_course'): ?>
            <form method="post" action="" class="mb-4">
                <div class="form-group">
                    <label for="course_id">Select Course:</label>
                    <select name="course_id" id="course_id" class="form-control" onchange="this.form.submit()">
                        <option value="">All Courses</option>
                        <?php while ($course = mysqli_fetch_assoc($course_result)): ?>
                            <option value="<?php echo $course['course_id']; ?>" <?php if ($course['course_id'] == $selected_course) echo 'selected'; ?>>
                                <?php echo $course['course_name']; ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>
            </form>
        <?php endif; ?>

        <table class="table table-bordered table-hover">
            <thead class="thead-dark">
                <tr>
                    <th><?php echo $report_type == 'all_instructors' || $report_type == 'instructors_with_courses' ? 'Instructor ID' : 'Student ID'; ?></th>
                    <th><?php echo $report_type == 'all_instructors' || $report_type == 'instructors_with_courses' ? 'Instructor Name' : 'Student Name'; ?></th>
                    <?php if ($report_type != 'all_students' && $report_type != 'all_instructors'): ?>
                        <th>Course Name</th>
                        <th>Course Code</th>
                    <?php endif; ?>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = mysqli_fetch_assoc($student_result)): ?>
                    <tr>
                        <td><?php echo $row['user_id']; ?></td>
                        <td><?php echo $report_type == 'all_instructors' || $report_type == 'instructors_with_courses' ? $row['instructor_name'] : $row['student_name']; ?></td>
                        <?php if ($report_type != 'all_students' && $report_type != 'all_instructors'): ?>
                            <td><?php echo $row['course_name']; ?></td>
                            <td><?php echo $row['course_code']; ?></td>
                        <?php endif; ?>
                    </tr>
                <?php endwhile; ?>
                <?php
                    if (mysqli_num_rows($student_result) == 0) {
                        echo "<tr><td colspan='4'>No records found.</td></tr>";
                    }
                ?>
            </tbody>
        </table>

        <!-- Pagination Controls -->
        <nav aria-label="Page navigation">
            <ul class="pagination justify-content-center">
                <?php if ($page > 1): ?>
                    <li class="page-item">
                        <a class="page-link" href="main.php?page=generate-reports&report=<?php echo $report_type; ?>&pages=<?php echo $page - 1; ?>" aria-label="Previous">
                            <span aria-hidden="true">&laquo;</span>
                        </a>
                    </li>
                <?php endif; ?>

                <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                    <li class="page-item <?php if ($i == $page) echo 'active'; ?>">
                        <a class="page-link" href="main.php?page=generate-reports&report=<?php echo $report_type; ?>&pages=<?php echo $i; ?>"><?php echo $i; ?></a>
                    </li>
                <?php endfor; ?>

                <?php if ($page < $total_pages): ?>
                    <li class="page-item">
                        <a class="page-link" href="main.php?page=generate-reports&report=<?php echo $report_type; ?>&pages=<?php echo $page + 1; ?>" aria-label="Next">
                            <span aria-hidden="true">&raquo;</span>
                        </a>
                    </li>
                <?php endif; ?>
            </ul>
        </nav>

        <div class="d-flex gap-1">
            <form method="post" action="../functions/generateReport.php">
                <input type="hidden" name="report_type" value="<?php echo $report_type; ?>">
                <input type="hidden" name="course_id" value="<?php echo $selected_course; ?>">
                <input type="hidden" name="page" value="<?php echo $page; ?>">
                <input type="hidden" name="results_per_page" value="<?php echo $results_per_page; ?>">
                <button type="submit" name="generate_report" class="btn btn-outline-success">Download as Excel</button>
            </form>

            <form method="post" action="../functions/generatePDF.php">
                <input type="hidden" name="report_type" value="<?php echo $report_type; ?>">
                <input type="hidden" name="course_id" value="<?php echo $selected_course; ?>">
                <input type="hidden" name="page" value="<?php echo $page; ?>">
                <input type="hidden" name="results_per_page" value="<?php echo $results_per_page; ?>">
                <button type="submit" name="generate_pdf" class="btn btn-outline-danger">Download as PDF</button>
            </form>
        </div>
    </div>