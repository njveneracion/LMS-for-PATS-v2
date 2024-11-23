<?php
// Fetch all courses
$course_query = "SELECT course_id, course_name FROM courses";
$course_result = mysqli_query($connect, $course_query);

// Determine the report type
$report_type = isset($_GET['report']) ? $_GET['report'] : 'all_students';

// Fetch students based on the selected report type
if ($report_type == 'all_students') {
    $student_query = "SELECT user_id, fullname AS student_name FROM users WHERE role = 'student' ORDER BY user_id ASC";
} elseif ($report_type == 'enrolled_students') {
    $student_query = "SELECT s.user_id, s.fullname AS student_name, c.course_name, c.course_code 
                      FROM users s 
                      JOIN enrollments e ON s.user_id = e.user_id 
                      JOIN courses c ON e.course_id = c.course_id 
                      WHERE s.role = 'student'
                      ORDER BY s.user_id ASC";
} elseif ($report_type == 'specific_course') {
    $selected_course = isset($_POST['course_id']) ? $_POST['course_id'] : '';
    $student_query = "SELECT s.user_id, s.fullname AS student_name, c.course_name, c.course_code 
                      FROM users s 
                      JOIN enrollments e ON s.user_id = e.user_id 
                      JOIN courses c ON e.course_id = c.course_id 
                      WHERE s.role = 'student'";

    if ($selected_course) {
        $student_query .= " AND c.course_id = '$selected_course'";
    }

    $student_query .= " ORDER BY s.user_id ASC";
} elseif ($report_type == 'completed_courses') {
    $student_query = "SELECT s.user_id, s.fullname AS student_name, c.course_name, c.course_code 
                      FROM users s 
                      JOIN enrollments e ON s.user_id = e.user_id 
                      JOIN courses c ON e.course_id = c.course_id 
                      WHERE s.role = 'student' AND e.status = 'completed'
                      ORDER BY s.user_id ASC";
} elseif ($report_type == 'students_with_certificates') {
    $student_query = "SELECT DISTINCT s.user_id, s.fullname AS student_name, c.course_name, c.course_code 
                      FROM users s 
                      JOIN enrollments e ON s.user_id = e.user_id 
                      JOIN courses c ON e.course_id = c.course_id 
                      JOIN certificates cert ON s.user_id = cert.student_id 
                      WHERE s.role = 'student'
                      ORDER BY s.user_id ASC";
} elseif ($report_type == 'all_instructors') {
    $student_query = "SELECT user_id, fullname AS instructor_name FROM users WHERE role = 'instructor' ORDER BY user_id ASC";
} elseif ($report_type == 'instructors_with_courses') {
    $student_query = "SELECT i.user_id, i.fullname AS instructor_name, c.course_name, c.course_code 
                      FROM users i 
                      JOIN courses c ON i.user_id = c.user_id 
                      WHERE i.role = 'instructor'
                      ORDER BY i.user_id ASC";
}

$student_result = mysqli_query($connect, $student_query);
?>

<div class="container mt-5">
    <h1 class="mb-4">Generate Reports</h1>
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

    <table class="table table-bordered">
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

    <form method="post" action="../functions/generateReport.php">
        <input type="hidden" name="report_type" value="<?php echo $report_type; ?>">
        <input type="hidden" name="course_id" value="<?php echo $selected_course; ?>">
        <button type="submit" name="generate_report" class="btn btn-primary">Download as Excel</button>
    </form>
</div>