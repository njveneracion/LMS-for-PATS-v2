<?php
include '../config/db.php';
include 'course-content.php';

if (isset($_GET['courseID'])) {
    $courseID = mysqli_real_escape_string($connect, $_GET['courseID']);
    echo "<h3>Course Announcements</h3>";
    displayAnnouncements($connect, $courseID);
} else {
    echo "<p>Error: Course ID not provided.</p>";
}
