<?php
$studentID = $_SESSION['userID']; // Get the current user's ID from session

// Query to get the courses the user is enrolled in along with completion status
$sqlEnrolledCourses = " 
    SELECT courses.course_id, courses.course_name, courses.course_img, courses.course_desc, courses.course_duration, enrollments.status
    FROM enrollments 
    JOIN courses ON enrollments.course_id = courses.course_id
    WHERE enrollments.user_id = '$studentID'
";

$resultEnrolledCourses = mysqli_query($connect, $sqlEnrolledCourses);
?>
<style>
<?php
    $theme = json_decode(file_get_contents('../admin/theme.json'), true);
?>
.btn-primary {
    background-color: <?= $theme["primaryColor"]?> !important;
    border-color: <?= $theme["primaryColor"]?> !important;
}

.text-primary {
    color: <?= $theme["primaryColor"]?> !important;
}
</style>
<div class="container-fluid card p-3">
    <h2 class="text-center">My Enrolled Courses</h2>
    <hr>
    <div class="courses">
        <?php
        if (mysqli_num_rows($resultEnrolledCourses) > 0) {
            while ($row = mysqli_fetch_assoc($resultEnrolledCourses)) {
                $courseID = $row['course_id'];
                $courseName = htmlspecialchars($row['course_name']);
                $courseImg = $row['course_img'];
                $courseDesc = htmlspecialchars($row['course_desc']);
                $courseDuration = $row['course_duration'];
                $status = $row['status'];
                
                // Convert course duration to hours and minutes
                $hours = floor($courseDuration / 60);
                $minutes = $courseDuration % 60;
                $formattedDuration = sprintf("%d hours %d minutes", $hours, $minutes);
                ?>
                <div class="course-item card shadow-sm mb-4">
                    <img src="../instructor/<?= $courseImg; ?>" alt="<?= $courseName; ?>" class="card-img-top">
                    <div class="card-body">
                        <h5 class="card-title"><?= $courseName; ?></h5>
                        <p class="card-text"><?= $courseDesc; ?></p>
                        <label class="text-secondary d-flex align-items-center mt-2">
                            <i class="fa-solid fa-clock text-primary"></i>
                            <span class="ms-2"><?= $formattedDuration; ?></span>
                        </label>
                        <a href="?page=course-content&viewContent=<?= $courseID; ?>" class="btn btn-primary mt-3 mb-5 w-100">View Course</a>
                    </div>
                    <div class="status <?= ($status == 'Completed') ? 'completed' : 'in-progress'; ?>">
                        <?= $status == 'Completed' ? 'Completed' : 'In Progress'; ?>
                    </div>
                </div>
                <?php
            }
        } else {
            echo "<p class='text-center'>You are not enrolled in any courses yet.</p>";
        }
        ?>
    </div>
</div>

<style>
.courses {
    display: flex;
    flex-wrap: wrap; 
    gap: 15px; 
    justify-content: start;
}

.course-item {
    position: relative;
    background-color: #fff;
    border-radius: 8px;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
    overflow: hidden;
    max-width: 300px; 
    width: 100%; 
}

.course-item img {
    width: 100%;
    height: 150px;
    object-fit: cover;
    border-bottom: 1px solid #ddd;
}

.course-item .card-body {
    padding: 15px;
}

.course-item .card-title {
    font-size: 1.1rem; /* Adjust font size */
    margin-bottom: 10px;
}

.course-item .card-text {
    font-size: 0.9rem; /* Adjust font size */
    color: #666;
}

.course-item .btn {
    margin-top: 10px;
    font-size: 0.9rem; /* Adjust font size */
}

@media (max-width: 768px) {
    .course-item {
        max-width: 100%; /* Full width on smaller screens */
    }
    
    .course-item img {
        height: auto; /* Adjust image height */
    }
}


.status {
    position: absolute;
    bottom: 10px;
    right: 10px;
    background-color: #f8f9fa; /* Light background for the badge */
    padding: 5px 10px;
    border-radius: 20px;
    font-size: 0.9rem;
    font-weight: bold;
    text-align: center;
}

.status.completed {
    background-color: #d4edda; /* Light green for completed */
    color: #155724; /* Dark green text */
    border: 1px solid #c3e6cb; /* Green border */
}

.status.in-progress {
    background-color: #f8d7da; /* Light red for ongoing */
    color: #721c24; /* Dark red text */
    border: 1px solid #f5c6cb; /* Red border */
}
</style>