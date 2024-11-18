<?php
include '../config/db.php';

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    $_SESSION['error'] = "Invalid course ID.";
    header("Location: sa_main.php?page=sa_manage_courses");
    exit();
}

$course_id = mysqli_real_escape_string($connect, $_GET['id']);

// Fetch course details
$query = "SELECT c.*, u.fullname AS instructor_name, b.batch_name, b.start_date, b.end_date, b.capacity,
          COUNT(e.enrollment_id) AS enrolled_students
          FROM courses c 
          JOIN users u ON c.user_id = u.user_id 
          LEFT JOIN batches b ON c.course_id = b.course_id
          LEFT JOIN enrollments e ON c.course_id = e.course_id
          WHERE c.course_id = '$course_id'
          GROUP BY c.course_id";

$result = mysqli_query($connect, $query);

if (mysqli_num_rows($result) == 0) {
    $_SESSION['error'] = "Course not found.";
    header("Location: sa_main.php?page=sa_manage_courses");
    exit();
}

$course = mysqli_fetch_assoc($result);

// Calculate course duration in hours and minutes
$duration_hours = floor($course['course_duration'] / 60);
$duration_minutes = $course['course_duration'] % 60;

// Fetch learning materials
$learning_materials_query = "SELECT * FROM learning_materials WHERE course_id = '$course_id' ORDER BY created_at DESC";
$learning_materials_result = mysqli_query($connect, $learning_materials_query);

?>

<?php
// Add this function at the top of your file or in a separate utilities file
function formatDate($dateString) {
    if (!$dateString) return 'Not set';
    $date = new DateTime($dateString);
    return $date->format('F j, Y'); // e.g., "January 1, 2023"
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Course - <?php echo htmlspecialchars($course['course_name']); ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Montserrat:ital,wght@0,100..900;1,100..900&family=Poppins:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&display=swap');
        /* Remove border radius from buttons */
        *{
            border-radius: 0 !important; /* Removes the border radius */
            font-family: 'Poppins', sans-serif;
        }

        :root {
            --primary-color: #0056b3;
            --secondary-color: #007bff;
            --accent-color: #e6f2ff;
            --text-color: #333333;
        }
        body {
            background-color: #f0f8ff;
            color: var(--text-color);
        }
        .course-header {
            background-color: var(--primary-color);
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
            overflow: hidden;
            color: white;
        }
        .course-image {
            height: 200px;
            object-fit: cover;
            border: 5px solid white;
        }
        .course-details, .content-table {
            background-color: white;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        .btn-view-description {
            background-color: var(--secondary-color);
            color: white;
            transition: all 0.3s ease;
        }
        .btn-view-description:hover {
            background-color: #0056b3;
            color: white;
        }
        .table thead {
            background-color: var(--accent-color);
        }
        .table-hover tbody tr:hover {
            background-color: #f1f8ff;
        }
        .btn-outline-info, .btn-outline-warning, .btn-outline-primary {
            border-color: var(--secondary-color);
            color: var(--secondary-color);
        }
        .btn-outline-info:hover, .btn-outline-warning:hover, .btn-outline-primary:hover {
            background-color: var(--secondary-color);
            color: white;
        }
        .learning-materials {
            background-color: white;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
            margin-bottom: 20px;
        }
        .learning-material-item {
            border-bottom: 1px solid #e0e0e0;
            padding: 15px;
        }
        .learning-material-item:last-child {
            border-bottom: none;
        }
        .learning-material-title {
            font-weight: bold;
            color: var(--primary-color);
        }
        .learning-material-description {
            font-size: 0.9em;
            color: #666;
        }
    </style>
</head>
<body>
    <div class="container my-5">
        <div class="course-header mb-4 p-4">
            <div class="row align-items-center">
                <div class="col-md-4 mb-3 mb-md-0">
                    <img src="../uploads/<?php echo htmlspecialchars($course['course_img']); ?>" alt="Course Image" class="img-fluid rounded course-image w-100">
                </div>
                <div class="col-md-8">
                    <h2 class="mb-3"><?php echo htmlspecialchars($course['course_name']); ?></h2>
                    <p class="mb-2"><strong>Course Code:</strong> <?php echo htmlspecialchars($course['course_code']); ?></p>
                    <p class="mb-2"><strong>Instructor:</strong> <?php echo htmlspecialchars($course['instructor_name']); ?></p>
                    <p class="mb-3"><strong>Duration:</strong> <?php echo $duration_hours . ' hours ' . $duration_minutes . ' minutes'; ?></p>
                    <button class="btn btn-view-description" data-bs-toggle="modal" data-bs-target="#descriptionModal">
                        <i class="fas fa-info-circle me-2"></i>View Description
                    </button>
                </div>
            </div>
        </div>

        <div class="row mb-4">
            <div class="col-md-6 mb-4 mb-md-0">
                <div class="course-details p-4">
                    <h4 class="mb-3 text-primary">Batch Information</h4>
                    <p><strong>Batch Name:</strong> <?php echo htmlspecialchars($course['batch_name']); ?></p>
                    <p><strong>Start Date:</strong> <?php echo formatDate($course['start_date']); ?></p>
                    <p><strong>End Date:</strong> <?php echo formatDate($course['end_date']); ?></p>
                </div>
            </div>
            <div class="col-md-6">
                <div class="course-details p-4">
                    <h4 class="mb-3 text-primary">Enrollment Information</h4>
                    <p><strong>Capacity:</strong> <?php echo htmlspecialchars($course['capacity']); ?></p>
                    <p><strong>Enrolled Students:</strong> <?php echo htmlspecialchars($course['enrolled_students']); ?></p>
                </div>
            </div>
        </div>

        <!-- Learning Materials Section -->
        <div class="learning-materials p-4 mb-4">
            <h3 class="mb-4 text-primary">Learning Materials</h3>
            <?php
            if (mysqli_num_rows($learning_materials_result) > 0) {
                while ($material = mysqli_fetch_assoc($learning_materials_result)) {
                    ?>
                    <div class="learning-material-item">
                        <div class="learning-material-title"><?php echo htmlspecialchars($material['title']); ?></div>
                        <div class="learning-material-description"><?php echo nl2br(htmlspecialchars($material['description'])); ?></div>
                        <?php if ($material['file_path']): ?>
                            <a href="../instructor/learning_materials/<?php echo htmlspecialchars($material['file_path']); ?>" class="btn btn-sm btn-primary mt-2" target="_blank">
                                <i class="fas fa-file-download me-1"></i> View File
                            </a>
                        <?php endif; ?>
                    </div>
                    <?php
                }
            } else {
                echo "<p>No learning materials available for this course.</p>";
            }
            ?>
        </div>

        <div class="content-table p-4">
            <h3 class="mb-4 text-primary">Course Content</h3>
            <div class="table-responsive">
                <?php
                $content_query = "SELECT 
                    'Material' AS type, material_id AS id, material_title AS title, material_desc AS description, material_file AS file
                    FROM course_material WHERE course_id = '$course_id'
                    UNION ALL
                    SELECT 'Quiz' AS type, quiz_id AS id, quiz_name AS title, quiz_description AS description, '' AS file
                    FROM quiz WHERE course_id = '$course_id'
                    UNION ALL
                    SELECT 'Task Sheet' AS type, task_sheet_id AS id, task_sheet_title AS title, task_sheet_description AS description, task_sheet_file AS file
                    FROM task_sheets WHERE course_id = '$course_id'
                    UNION ALL
                    SELECT 
                    CASE WHEN assessment_type = 'pre' THEN 'Pre-assessment' ELSE 'Post-assessment' END AS type,
                    assessment_id AS id, assessment_title AS title, assessment_description AS description, '' AS file
                    FROM assessments WHERE course_id = '$course_id'
                    ORDER BY type, title";
                
                $content_result = mysqli_query($connect, $content_query);
                
                if (mysqli_num_rows($content_result) > 0) {
                    ?>
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Title</th>
                                <th>Description</th>
                                <th>Type</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            while ($content = mysqli_fetch_assoc($content_result)) {
                                echo "<tr>";
                                echo "<td>" . htmlspecialchars($content['title']) . "</td>";
                                echo "<td>" . htmlspecialchars($content['description']) . "</td>";
                                echo "<td>" . htmlspecialchars($content['type']) . "</td>";
                                echo "</tr>";
                            }
                            ?>
                        </tbody>
                    </table>
                <?php
                } else {
                    ?>
                    <div class="text-center py-5">
                        <i class="bi bi-inbox fs-1 text-secondary mb-3"></i>
                        <h5 class="text-secondary">No course content available yet</h5>
                        <p class="text-muted">Content will appear here once added to the course.</p>
                    </div>
                <?php
                }
                ?>
            </div>
        </div>

        <div class="text-center mt-4">
            <a href="main.php?page=manage-courses" class="btn btn-secondary">
                <i class="fas fa-arrow-left me-2"></i>Back to Courses
            </a>
        </div>
    </div>

    <!-- Description Modal -->
    <div class="modal fade" id="descriptionModal" tabindex="-1" aria-labelledby="descriptionModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title" id="descriptionModalLabel">Course Description</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <?php echo nl2br(htmlspecialchars($course['course_desc'])); ?>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>