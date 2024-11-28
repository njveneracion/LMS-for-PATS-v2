<?php
include '../config/db.php';

if (!isset($_SESSION['userID']) || $_SESSION['role'] !== 'instructor') {
    header("Location: ../login.php");
    exit();
}

$theme = json_decode(file_get_contents('../admin/theme.json'), true);

$instructorID = $_SESSION['userID'];

// Fetch courses created by the instructor
$courseQuery = "SELECT course_id, course_name FROM courses WHERE user_id = ?";
$stmt = $connect->prepare($courseQuery);
$stmt->bind_param("i", $instructorID);
$stmt->execute();
$courseResult = $stmt->get_result();

if(isset($_POST['submit_reply'])){
    $reply_message = $_POST['reply_message'];
    $parent_id = $_POST['parent_id'];
    $course_id = $_POST['course_id'];

    $replyQuery = "INSERT INTO discussions (user_id, course_id, message, parent_id) VALUES (?, ?, ?, ?)";
    $stmt = $connect->prepare($replyQuery);
    $stmt->bind_param("iiss", $instructorID, $course_id, $reply_message, $parent_id);
    $stmt->execute();
}

if(isset($_POST['submit_delete'])){
    $discussion_id = $_POST['discussion_id'];
    $course_id = $_POST['course_id'];
    $is_reply = isset($_POST['is_reply']) ? $_POST['is_reply'] : 0;

    // If it's a reply, we only delete the specific reply
    // If it's a main discussion, we delete the main discussion and all its replies
    if ($is_reply) {
        $deleteQuery = "DELETE FROM discussions WHERE discussion_id = ? AND course_id = ?";
    } else {
        $deleteQuery = "DELETE FROM discussions WHERE (discussion_id = ? OR parent_id = ?) AND course_id = ?";
    }

    $stmt = $connect->prepare($deleteQuery);
    
    if ($is_reply) {
        $stmt->bind_param("ii", $discussion_id, $course_id);
    } else {
        $stmt->bind_param("iii", $discussion_id, $discussion_id, $course_id);
    }

    $deleteResult = $stmt->execute();
    
    if($deleteResult){
        $_SESSION['deleteSuccess'] = $is_reply ? "Reply deleted successfully." : "Discussion deleted successfully.";
    } else {
        $_SESSION['deleteError'] = $is_reply ? "Reply deletion failed." : "Discussion deletion failed.";
    }

}

// Function to display discussions and replies
function displayDiscussions($connect, $courseID) {
    $discussionQuery = "SELECT d.*, u.fullname, u.role, u.profile_picture 
                        FROM discussions d 
                        JOIN users u ON d.user_id = u.user_id 
                        WHERE d.course_id = ? AND d.parent_id IS NULL 
                        ORDER BY d.created_at DESC";
    $stmt = $connect->prepare($discussionQuery);
    $stmt->bind_param("i", $courseID);
    $stmt->execute();
    $discussionResult = $stmt->get_result();

    if ($discussionResult->num_rows > 0) {
        while ($discussion = $discussionResult->fetch_assoc()) {
            echo '<div class="discussion-card">';
            echo '<div class="discussion-header">';
            echo '<div class="d-flex align-items-center">';
            echo '<img src="../uploads/profile-pictures/' . htmlspecialchars($discussion['profile_picture']) . '" alt="Profile" class="profile-picture">';
            echo '<div>';
            echo '<span class="user-name">' . htmlspecialchars($discussion['fullname']) . '</span>';
            echo '<span class="user-role ms-2">(' . htmlspecialchars($discussion['role']) . ')</span>';
            echo '</div>';
            echo '</div>';
            echo '</div>';
            echo '<div class="discussion-body">';
            echo '<div class="discussion-content">' . nl2br(htmlspecialchars($discussion['message'])) . '</div>';
            echo '<div class="discussion-meta">Posted on ' . date('M j, Y, g:i a', strtotime($discussion['created_at'])) . '</div>';

            echo '<hr>';
            
            
            echo '<div class="discussion-actions mt-3 d-flex justify-content-end">';
            echo '<form method="post" style="display: inline;">';
            echo '<input type="hidden" name="parent_id" value="' . $discussion['discussion_id'] . '">';
            echo '<input type="hidden" name="course_id" value="' . $courseID . '">';
            echo '<button type="submit" class="btn btn-sm btn-primary" name="show_reply_form"><i class="fa-solid fa-pen-to-square"></i></button>';
            echo '</form>';

          
            echo '<form method="post" style="display: inline;" onsubmit="return confirm(\'Are you sure you want to delete this discussion and all its replies?\');">';
            echo '<input type="hidden" name="discussion_id" value="' . $discussion['discussion_id'] . '">';
            echo '<input type="hidden" name="course_id" value="' . $courseID . '">';
            echo '<button type="submit" class="btn btn-sm btn-danger ms-2" name="submit_delete"><i class="fa-solid fa-trash"></i></button>';
            echo '</form>';
            echo '</div>';
            
            // Show reply form if the button was clicked
            if (isset($_POST['show_reply_form']) && $_POST['parent_id'] == $discussion['discussion_id']) {
                echo '<div class="reply-form mt-3">';
                echo '<form method="post">';
                echo '<input type="hidden" name="parent_id" value="' . $discussion['discussion_id'] . '">';
                echo '<input type="hidden" name="course_id" value="' . $courseID . '">';
                echo '<textarea class="form-control mb-2" name="reply_message" rows="3" required></textarea>';
                echo '<div class="d-flex justify-content-end"><button type="submit" class="btn btn-primary" name="submit_reply"><i class="fa-solid fa-paper-plane"></i> Reply</button></div>';
                echo '</form>';
                echo '</div>';
            }
            
            // Display replies
            displayReplies($connect, $discussion['discussion_id'], $courseID);
            
            echo '</div>';
            echo '</div>';
        }
    } else {
        echo '<p class="text-center">No discussions yet.</p>';
    }
}

// Function to display replies
function displayReplies($connect, $parentID, $courseID) {
    $replyQuery = "SELECT d.*, u.fullname, u.role, u.profile_picture 
                   FROM discussions d 
                   JOIN users u ON d.user_id = u.user_id 
                   WHERE d.parent_id = ?
                   ORDER BY d.created_at ASC";
    $replyStmt = $connect->prepare($replyQuery);
    $replyStmt->bind_param("i", $parentID);
    $replyStmt->execute();
    $replyResult = $replyStmt->get_result();

    if ($replyResult->num_rows > 0) {
        echo '<div class="reply-section">';
        echo '<h6 class="mb-3">Replies:</h6>';
        while ($reply = $replyResult->fetch_assoc()) {
            echo '<div class="reply-card">';
            echo '<div class="d-flex align-items-center mb-2">';
            echo '<img src="../uploads/profile-pictures/' . htmlspecialchars($reply['profile_picture']) . '" alt="Profile" class="reply-profile-picture me-2 rounded-circle">';
            echo '<div>';
            echo '<span class="user-name">' . htmlspecialchars($reply['fullname']) . '</span>';
            echo '<span class="user-role ms-2">(' . htmlspecialchars($reply['role']) . ')</span>';
            echo '</div>';
            echo '</div>';
            echo '<div class="discussion-content">' . nl2br(htmlspecialchars($reply['message'])) . '</div>';
            echo '<div class="discussion-meta">Posted on ' . date('M j, Y, g:i a', strtotime($reply['created_at'])) . '</div>';
           
            echo '<div class="d-flex justify-content-end"><button type="button" class="btn btn-sm btn-danger mt-2" data-bs-toggle="modal" data-bs-target="#deleteReplyModal"><i class="fa-solid fa-trash"></i></button></div>';
            
            echo '</div>';

            ?>
            <!-- Modal -->
            <div class="modal fade" id="deleteReplyModal" tabindex="-1" aria-labelledby="deleteReplyModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                <div class="modal-header">
                    <h1 class="modal-title fs-5" id="exampleModalLabel">Delete Reply</h1>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                <p>Are you sure you want to delete this reply?</p>
                </div>
                <div class="modal-footer">
                    <form method="post">
                    <input type="hidden" name="discussion_id" value="<?php echo $reply['discussion_id']; ?>">
                    <input type="hidden" name="course_id" value="<?php echo $courseID; ?>">
                    <input type="hidden" name="is_reply" value="1">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-danger" name="submit_delete">Delete</button>
                  </form>
                </div>
                </div>
            </div>
            </div>
            <?php
        }
        echo '</div>';

    }
}
?>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
<style>
    .discussion-container {
        max-width: 1200px;
        margin: 0 auto;
        padding: 2rem 1rem;
    }
    .course-list {
        background-color: #f8f9fa;
        border-radius: 10px;
        padding: 1.5rem;
        box-shadow: 0 0 10px rgba(0,0,0,0.1);
    }
    .course-list h3 {
        color: #2c3e50;
        font-weight: 600;
        margin-bottom: 1rem;
    }
    .list-group-item {
        border: none;
        background-color: transparent;
        padding: 0.5rem 0;
    }
    .list-group-item a {
        color: #3498db;
        text-decoration: none;
        transition: color 0.3s ease;
    }
    .list-group-item a:hover {
        color: #2980b9;
    }
    .discussion-card {
        background-color: #ffffff;
        border-radius: 10px;
        box-shadow: 0 0 15px rgba(0,0,0,0.1);
        margin-bottom: 1.5rem;
        overflow: hidden;
    }
    .discussion-header {
        background-color: #f8f9fa;
        padding: 1rem;
        border-bottom: 1px solid #e9ecef;
    }
    .discussion-body {
        padding: 1.5rem;
    }
    .profile-picture {
        width: 40px;
        height: 40px;
        object-fit: cover;
        border-radius: 50%;
        margin-right: 1rem;
    }
    .user-name {
        font-weight: 600;
        color: #2c3e50;
    }
    .user-role {
        font-size: 0.875rem;
        color: #7f8c8d;
    }
    .discussion-content {
        margin-top: 1rem;
        color: #34495e;
    }
    .discussion-meta {
        font-size: 0.875rem;
        color: #95a5a6;
        margin-top: 0.5rem;
    }
    .reply-section {
        margin-top: 1.5rem;
        padding-top: 1rem;
        border-top: 1px solid #e9ecef;
    }
    .reply-card {
        background-color: #f8f9fa;
        border-radius: 8px;
        padding: 1rem;
        margin-bottom: 1rem;
    }
    .reply-profile-picture {
        width: 30px;
        height: 30px;
    }
</style>


<div class="discussion-container">
    <h2 class="mb-4">Course Discussions</h2>
    
    <div class="row">
        <div class="col-md-4 mb-4">
            <div class="course-list">
                <h3>Your Courses</h3>
                <ul class="list-group">
                    <?php while ($course = $courseResult->fetch_assoc()): ?>
                        <li class="list-group-item">
                            <a href="?page=discussion&course_id=<?php echo $course['course_id']; ?>" >
                                <i class="fas fa-book-open me-2" style="color: <?= $theme['primaryColor']?>"></i><span style="color: <?= $theme['primaryColor']?>"><?php echo htmlspecialchars($course['course_name']); ?></span>
                            </a>
                        </li>
                    <?php endwhile; ?>
                </ul>
            </div>
        </div>
        
        <div class="col-md-8">
            <?php
            if (isset($_GET['course_id'])) {
                $courseID = $_GET['course_id'];
                displayDiscussions($connect, $courseID);
            } else {
                echo '<p class="text-center">Select a course to view discussions.</p>';
            }
            ?>
        </div>
    </div>
</div>
