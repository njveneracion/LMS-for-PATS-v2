<?php
include '../config/db.php';

function handleDiscussions($connect, $courseID, $userID) {
    if (isset($_POST['post_message'])) {
        $message = mysqli_real_escape_string($connect, $_POST['message']);
        $parentID = isset($_POST['parent_id']) ? intval($_POST['parent_id']) : null;
        $sql = "INSERT INTO discussions (course_id, user_id, message, parent_id) VALUES (?, ?, ?, ?)";
        $stmt = $connect->prepare($sql);
        $stmt->bind_param("iisi", $courseID, $userID, $message, $parentID);
        $stmt->execute();
    }

    $sql = "SELECT d.*, u.fullname, u.role, u.profile_picture
            FROM discussions d 
            JOIN users u ON d.user_id = u.user_id 
            WHERE d.course_id = ? AND d.parent_id IS NULL
            ORDER BY d.created_at DESC";
    $stmt = $connect->prepare($sql);
    $stmt->bind_param("i", $courseID);
    $stmt->execute();
    $result = $stmt->get_result();

    ?>
    <div class="discussion-board">
        <h3 class="mb-4"><i class="fas fa-comments me-2"></i>Course Q&A</h3>
        <form method="post" action="" class="mb-4">
            <div class="form-group">
                <textarea name="message" class="form-control" rows="3" required placeholder="Ask a question..."></textarea>
            </div>
            <button type="submit" name="post_message" class="btn btn-primary mt-2">
                <i class="fas fa-question-circle me-2"></i>Ask Question
            </button>
        </form>

        <div class="questions">
            <?php 
            while ($row = $result->fetch_assoc()) {
                displayMessage($connect, $row, $userID, $courseID);
            }
            ?>
        </div>
    </div>
    <?php
}

function displayMessage($connect, $message, $currentUserID, $courseID, $depth = 0) {
    ?>
    <div class="question-card mb-3 <?php echo $depth > 0 ? 'answer' : ''; ?>">
        <div class="d-flex">
            <?php if ($depth > 0): ?>
                <div class="reply-line"></div>
            <?php endif; ?>
            <div class="flex-grow-1">
                <div class="d-flex align-items-start">
                    <img src="../uploads/profile-pictures/<?= htmlspecialchars($message['profile_picture'] ?? 'default.jpg') ?>" alt="Profile Picture" class="rounded-circle me-2" style="width: 40px; height: 40px; object-fit: cover;">
                    <div class="flex-grow-1">
                        <h6 class="mb-0"><?= htmlspecialchars($message['fullname']) ?> 
                            <small class="text-muted"><?= htmlspecialchars($message['role']) ?></small>
                        </h6>
                        <p class="mb-1"><?= nl2br(htmlspecialchars($message['message'])) ?></p>
                    
                        <small class="text-muted"><i class="far fa-clock me-1"></i><?= date('M j, Y, g:i a', strtotime($message['created_at'])) ?></small>
                   
                        <button class="btn btn-link btn-sm p-0 ms-2 reply-toggle mb-2" data-target="reply-form-<?= $message['discussion_id'] ?>">
                            <i class="fas fa-reply me-1"></i>Reply
                        </button>
                        
                        <form method="post" action="" class="mt-2 reply-form" id="reply-form-<?= $message['discussion_id'] ?>" style="display: none;">
                            <input type="hidden" name="parent_id" value="<?= $message['discussion_id'] ?>">
                            <div class="form-group">
                                <textarea name="message" class="form-control" rows="2" required placeholder="Write your answer..."></textarea>
                            </div>
                            <button type="submit" name="post_message" class="btn btn-sm btn-primary mt-2">
                                <i class="fas fa-paper-plane me-1"></i>Submit Answer
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php
    $sql = "SELECT d.*, u.fullname, u.role, u.profile_picture
            FROM discussions d 
            JOIN users u ON d.user_id = u.user_id 
            WHERE d.parent_id = ?
            ORDER BY d.created_at ASC";
    $stmt = $connect->prepare($sql);
    $stmt->bind_param("i", $message['discussion_id']);
    $stmt->execute();
    $replies = $stmt->get_result();

    if ($replies->num_rows > 0) {
        echo '<div class="replies ms-4">';
        while ($reply = $replies->fetch_assoc()) {
            displayMessage($connect, $reply, $currentUserID, $courseID, $depth + 1);
        }
        echo '</div>';
    }
}

if (isset($_GET['courseID']) && isset($_SESSION['userID'])) {
    $courseID = $_GET['courseID'];
    $userID = $_SESSION['userID'];
    handleDiscussions($connect, $courseID, $userID);
}
?>

<style>
.discussion-board {
    max-width: 800px;
    margin: 0 auto;
}
.question-card {
    background-color: #f8f9fa;
    border-radius: 8px;
    padding: 15px;
    margin-bottom: 15px;
}
.answer {
    background-color: #e9ecef;
}
.reply-toggle {
    color: #6c757d;
    text-decoration: none;
}
.reply-toggle:hover {
    color: #007bff;
}
.replies {
    position: relative;
}
.reply-line {
    position: absolute;
    left: -20px;
    top: 0;
    bottom: 0;
    width: 2px;
    background-color: #ced4da;
}
.reply-line::before {
    content: '';
    position: absolute;
    top: 20px;
    left: 0;
    width: 15px;
    height: 2px;
    background-color: #ced4da;
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('.reply-toggle').forEach(button => {
        button.addEventListener('click', function(e) {
            e.preventDefault();
            const formId = this.getAttribute('data-target');
            const form = document.getElementById(formId);
            form.style.display = form.style.display === 'none' ? 'block' : 'none';
        });
    });
});
</script>