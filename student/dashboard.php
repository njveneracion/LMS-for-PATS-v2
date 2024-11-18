<?php
// At the beginning of your PHP script, after you've established the database connection
$studentID = $_SESSION['userID'];

// Count enrolled courses
$sqlEnrolledCourses = "SELECT COUNT(*) as count FROM enrollments WHERE user_id = '$studentID'";
$resultEnrolledCourses = mysqli_query($connect, $sqlEnrolledCourses);
$enrolledCoursesCount = mysqli_fetch_assoc($resultEnrolledCourses)['count'];

// Count completed courses
$sqlCompletedCourses = "SELECT COUNT(*) as count FROM enrollments WHERE user_id = '$studentID' AND status = 'Completed'";
$resultCompletedCourses = mysqli_query($connect, $sqlCompletedCourses);
$completedCoursesCount = mysqli_fetch_assoc($resultCompletedCourses)['count'];

// Get recent progress
$sqlRecentProgress = "SELECT sp.*, c.course_name, cm.material_title, q.quiz_name
                      FROM student_progress sp
                      LEFT JOIN courses c ON sp.course_id = c.course_id
                      LEFT JOIN course_material cm ON sp.content_id = cm.material_id AND sp.content_type = 'Material'
                      LEFT JOIN quiz q ON sp.content_id = q.quiz_id AND sp.content_type = 'Quiz'
                      WHERE sp.student_id = '$studentID'
                      ORDER BY sp.completed_at DESC
                      LIMIT 5";
$resultRecentProgress = mysqli_query($connect, $sqlRecentProgress);
?>

<div class="container-fluid dashboard-container mt-4">
    <div class="row mb-4">
        <div class="col-lg-8">
            <div class="welcome-card bg-primary text-white p-4 rounded-lg shadow">
                <h2><i class="fas fa-graduation-cap me-2"></i>Welcome, <?= htmlspecialchars($_SESSION['fullname']); ?></h2>
                <p class="lead mb-0">Ready to continue your learning journey?</p>
            </div>
        </div>
        <div class="col-lg-4">
            <div class="quick-stats bg-white p-4 rounded-lg shadow">
                <h5 class="text-primary mb-3"><i class="fas fa-chart-line me-2"></i>Quick Stats</h5>
                <div class="row">
                    <div class="col-6">
                        <div class="stat-item text-center">
                            <i class="fas fa-book text-primary fa-3x mb-2"></i>
                            <h3 class="mb-1"><?= $enrolledCoursesCount; ?></h3>
                            <p class="text-muted mb-0">Enrolled Courses</p>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="stat-item text-center">
                            <i class="fas fa-certificate text-primary fa-3x mb-2"></i>
                            <h3 class="mb-1"><?= $completedCoursesCount; ?></h3>
                            <p class="text-muted mb-0">Completed Courses</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row mb-4">
        <div class="col-lg-6">
            <div class="card shadow-sm">
                <div class="card-header bg-white border-0">
                    <h5 class="card-title mb-0"><i class="fas fa-tasks text-primary me-2"></i>Recent Progress</h5>

                </div>
                <div class="card-body p-0">
                    <ul class="list-group list-group-flush">
                        <?php while ($progress = mysqli_fetch_assoc($resultRecentProgress)): ?>
                            <li class="list-group-item">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h6 class="mb-0"><?= htmlspecialchars($progress['course_name']); ?></h6>
                                        <small class="text-muted">
                                            <?= $progress['content_type'] == 'Material' ? $progress['material_title'] : $progress['quiz_name']; ?>
                                        </small>
                                    </div>
                                    <span class="badge bg-success">Completed</span>
                                </div>
                            </li>
                        <?php endwhile; ?>

                        <?php if (mysqli_num_rows($resultRecentProgress) == 0): ?>
                            <li class="list-group-item text-center">No recent progress</li>
                        <?php endif; ?>
                    </ul>
                </div>
            </div>
        </div>
        <div class="col-lg-6">
            <!-- You can add another card here, e.g., Upcoming Deadlines or Announcements -->
        </div>
    </div>

    <div class="card shadow-sm">
        <div class="card-header bg-white border-0 d-flex justify-content-between align-items-center">
            <h5 class="card-title mb-0"><i class="fas fa-list-alt text-primary me-2"></i>Available Courses</h5>
            <div class="btn-group" role="group" aria-label="Course view options">
                <button type="button" class="btn btn-outline-primary active" id="gridView"><i class="fas fa-th-large"></i></button>
                <button type="button" class="btn btn-outline-primary" id="listView"><i class="fas fa-list"></i></button>
            </div>
        </div>
        <div class="card-body">
            <div class="row courses" id="coursesContainer">
                <?php
                $sqlShowCourses = "
                    SELECT c.course_id, c.course_name, c.course_img, c.course_desc, u.fullname AS instructor_name,
                           e.enrollment_id, b.end_date
                    FROM courses c
                    JOIN users u ON c.user_id = u.user_id
                    LEFT JOIN enrollments e ON c.course_id = e.course_id AND e.user_id = '$studentID'
                    LEFT JOIN batches b ON e.batch_id = b.batch_id
                ";
                $resultShowCourses = mysqli_query($connect, $sqlShowCourses);

                if (mysqli_num_rows($resultShowCourses) == 0) {
                    echo '<div class="col-12"><div class="alert alert-info">No available courses</div></div>';
                } else {
                    while ($rowCourses = mysqli_fetch_assoc($resultShowCourses)) {
                        $courseID = $rowCourses['course_id'];
                        $courseName = $rowCourses['course_name'];
                        $courseImg = $rowCourses['course_img'];
                        $courseDesc = $rowCourses['course_desc'];
                        $instructorName = $rowCourses['instructor_name'];
                        $isEnrolled = !is_null($rowCourses['enrollment_id']);
                        $hasActiveBatches = is_null($rowCourses['end_date']) || $rowCourses['end_date'] >= date('Y-m-d');
                        ?>
                        <div class="col-md-6 col-lg-4 mb-4 course-item">
                            <div class="card h-100 course-card <?= !$hasActiveBatches ? 'ended' : 'ongoing'; ?>">
                                <div class="course-image-wrapper">
                                    <img src="../instructor/<?= htmlspecialchars($courseImg); ?>" class="card-img-top course-image" alt="<?= htmlspecialchars($courseName); ?>">
                                    <?php if ($isEnrolled): ?>
                                        <span class="badge bg-success position-absolute top-0 end-0 m-2">Enrolled</span>
                                    <?php endif; ?>
                                </div>
                                <div class="card-body d-flex flex-column">
                                    <h5 class="card-title"><?= htmlspecialchars($courseName); ?></h5>
                                    <p class="card-text course-desc flex-grow-1"><?= htmlspecialchars($courseDesc); ?></p>
                                    <div class="mt-auto">
                                        <p class="card-text instructor-name">
                                            <small class="text-muted">
                                                <i class="fas fa-chalkboard-teacher me-1"></i>
                                                <strong>Instructor:</strong> <?= htmlspecialchars($instructorName); ?>
                                            </small>
                                        </p>
                                        <?php if ($hasActiveBatches): ?>
                                            <a href="?page=course-details&viewContent=<?= $courseID; ?>" class="btn btn-primary mt-2">
                                                <i class="fas fa-eye me-1"></i> View Course
                                            </a>
                                        <?php else: ?>
                                            <p class="ended-message mt-2">
                                                <i class="fas fa-calendar-times me-1"></i> This course has ended.
                                            </p>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php 
                    }
                } 
                ?>
            </div>
        </div>
    </div>
</div>

<style>
body {
    background-color: #f8f9fa;
}

.dashboard-container {
    max-width: 1200px;
    margin: 0 auto;
}

.welcome-card {
    background: linear-gradient(45deg, #007bff, #0056b3);
}

.quick-stats {
    border-left: 4px solid #007bff;
}

.courses {
    transition: all 0.3s ease;
}

.course-card {
    transition: all 0.3s ease;
    border: none;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
}

.course-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 6px 12px rgba(0, 0, 0, 0.15);
}

.course-image-wrapper {
    position: relative;
    height: 200px;
    overflow: hidden;
}

.course-image {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.card-title {
    font-size: 1.1rem;
    font-weight: bold;
    color: #333;
}

.course-desc {
    font-size: 0.9rem;
    color: #666;
    display: -webkit-box;
    -webkit-line-clamp: 3;
    -webkit-box-orient: vertical;
    overflow: hidden;
}

.instructor-name {
    font-size: 0.85rem;
}

.ended-message {
    color: #721c24;
    background-color: #f8d7da;
    border: 1px solid #f5c6cb;
    padding: 5px 10px;
    border-radius: 5px;
    text-align: center;
    font-size: 0.85rem;
}

.btn-primary {
    background-color: #007bff;
    border: none;
    transition: background-color 0.3s ease;
}

.btn-primary:hover {
    background-color: #0056b3;
}

@media (max-width: 768px) {
    .course-image-wrapper {
        height: 180px;
    }
}

.courses.list-view {
    display: flex;
    flex-direction: column;
}

.courses.list-view .course-item {
    width: 100%;
    max-width: 100%;
}

.courses.list-view .course-card {
    flex-direction: row;
    align-items: center;
}

.courses.list-view .course-image-wrapper {
    width: 200px;
    height: 150px;
}

.courses.list-view .card-body {
    display: flex;
    flex-direction: row;
    align-items: center;
    justify-content: space-between;
    padding: 1rem;
}

.courses.list-view .card-title,
.courses.list-view .course-desc {
    margin-bottom: 0;
}

.courses.list-view .course-desc {
    flex: 1;
    padding: 0 1rem;
}

@media (max-width: 768px) {
    .courses.list-view .course-card {
        flex-direction: column;
    }

    .courses.list-view .course-image-wrapper {
        width: 100%;
        height: 180px;
    }

    .courses.list-view .card-body {
        flex-direction: column;
        align-items: flex-start;
    }

    .courses.list-view .course-desc {
        padding: 1rem 0;
    }
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const gridViewBtn = document.getElementById('gridView');
    const listViewBtn = document.getElementById('listView');
    const coursesContainer = document.getElementById('coursesContainer');

    gridViewBtn.addEventListener('click', function() {
        coursesContainer.classList.remove('list-view');
        gridViewBtn.classList.add('active');
        listViewBtn.classList.remove('active');
    });

    listViewBtn.addEventListener('click', function() {
        coursesContainer.classList.add('list-view');
        listViewBtn.classList.add('active');
        gridViewBtn.classList.remove('active');
    });
});
</script>
