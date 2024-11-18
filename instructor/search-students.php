<?php
session_start();
include '../config/db.php';

// Sanitize inputs
$search = isset($_GET['search']) ? mysqli_real_escape_string($connect, $_GET['search']) : '';
$courseID = isset($_GET['courseID']) ? mysqli_real_escape_string($connect, $_GET['courseID']) : '';
$instructorID = $_SESSION['userID']; // Assuming the instructor's ID is stored in the session

// Modify the query to only show students enrolled in the instructor's courses
$sqlSearch = "
    SELECT u.user_id, u.fullname, u.profile_picture, cr.status, cr.pic_path, cr.birthCert_path, c.course_name
    FROM users u
    JOIN course_registrations cr ON u.user_id = cr.student_id
    JOIN courses c ON cr.course_id = c.course_id
    WHERE c.user_id = ? 
    AND (u.fullname LIKE ? OR u.user_id LIKE ?)
";

$params = array($instructorID, "%$search%", "%$search%");
$types = "iss";

if (!empty($courseID)) {
    $sqlSearch .= " AND cr.course_id = ?";
    $params[] = $courseID;
    $types .= "s";
}

$stmt = mysqli_prepare($connect, $sqlSearch);
mysqli_stmt_bind_param($stmt, $types, ...$params);
mysqli_stmt_execute($stmt);
$resultSearch = mysqli_stmt_get_result($stmt);

// Output search results
while ($row = mysqli_fetch_assoc($resultSearch)) {
    $id = $row['user_id'];
    $fullname = $row['fullname'];
    $profilePicture = $row['profile_picture'];
    $status = $row['status'];
    $courseName = $row['course_name'];
    $picPath = $row['pic_path'];
    $birthCertPath = $row['birthCert_path'];
    ?>

    <tr>
        <td><?php echo htmlspecialchars($id); ?></td>
        <td><?php echo htmlspecialchars($fullname); ?></td>
        <td>
            <?php if ($profilePicture) { ?>
                <img src="../assets/userProfilePicture/<?php echo htmlspecialchars($profilePicture); ?>" alt="Profile Picture" class="img-thumbnail" style="width: 100px;">
            <?php } else { ?>
                <img src="../assets/userProfilePicture/default.jpg" alt="Default Profile Picture" class="img-thumbnail" style="width: 100px;">
            <?php } ?>
        </td>
        <td class="
        <?php
            switch ($status) {
                case 'resubmit':
                    echo 'text-warning';
                    break;
                case 'approved':
                    echo 'text-success';
                    break;
                case 'pending':
                    echo 'text-primary';
                    break;
                case 'declined':
                    echo 'text-danger';
                    break;
            }
        ?>
        ">
        <?php switch ($status) {
            case 'resubmit':
                echo '<span class="badge text-bg-warning">Resubmit</span>';
                break;
            case 'approved':
                echo '<span class="badge text-bg-success">Approved</span>';
                break;
            case 'pending':
                echo '<span class="badge text-bg-primary">Pending</span>';
                break;
            case 'declined':
                echo '<span class="badge text-bg-danger">Declined</span>';
                break;
        } ?>
        </td>
        <td><?php echo htmlspecialchars($courseName); ?></td>
        <td>
            <a href="verify-registrations.php?id=<?= $id; ?>&courseID=<?= $courseID; ?>" class="btn btn-success btn-sm">View Registration</a>
        </td>
    </tr>

<?php
}

mysqli_stmt_close($stmt);
?>