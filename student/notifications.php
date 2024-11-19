<?php
$user_id = $_SESSION['userID'];

// Function to fetch notifications
function getNotifications($connect, $user_id, $unread_only = false) {
    $query = "SELECT * FROM notifications WHERE user_id = ? AND recipient_type = 'student'";
    if ($unread_only) {
        $query .= " AND status = 'unread'";
    }
    $query .= " ORDER BY created_at DESC";
    
    $stmt = mysqli_prepare($connect, $query);
    mysqli_stmt_bind_param($stmt, "i", $user_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    return mysqli_fetch_all($result, MYSQLI_ASSOC);
}

// Handle marking a notification as read
if (isset($_POST['mark_read'])) {
    $notification_id = mysqli_real_escape_string($connect, $_POST['mark_read']);
    $update_query = "UPDATE notifications SET status = 'read' WHERE id = ? AND user_id = ?";
    $update_stmt = mysqli_prepare($connect, $update_query);
    mysqli_stmt_bind_param($update_stmt, "ii", $notification_id, $user_id);
    mysqli_stmt_execute($update_stmt);
    if (mysqli_stmt_affected_rows($update_stmt) > 0) {
        $_SESSION['notification_message'] = "Notification marked as read!";
    } else {
        $_SESSION['notification_message'] = "Failed to mark notification as read.";
    }
}

// Handle marking all notifications as read
if (isset($_POST['mark_all_read'])) {
    $update_query = "UPDATE notifications SET status = 'read' WHERE user_id = ? AND status = 'unread'";
    $update_stmt = mysqli_prepare($connect, $update_query);
    mysqli_stmt_bind_param($update_stmt, "i", $user_id);
    mysqli_stmt_execute($update_stmt);
    if (mysqli_stmt_affected_rows($update_stmt) > 0) {
        $_SESSION['notification_message'] = "All notifications marked as read!";
    } else {
        $_SESSION['notification_message'] = "No unread notifications to mark.";
    }
}

// Fetch all notifications for the main page view
$notifications = getNotifications($connect, $user_id);
?>

<div class="notifications-container">
    <div class="notifications-header">
        <h1>Notifications</h1>
        <form method="POST">
            <button type="submit" name="mark_all_read" class="btn-mark-all">Mark all as read</button>
        </form>
    </div>
    <?php
    if (isset($_SESSION['notification_message'])) {
        echo "<div class='alert'>" . $_SESSION['notification_message'] . "</div>";
        unset($_SESSION['notification_message']);
    }
    ?>
    <div id="notifications-list">
        <?php if (empty($notifications)): ?>
            <p class="no-notifications">No notifications found.</p>
        <?php else: ?>
            <?php foreach ($notifications as $notification): ?>
                <div class="notification-item <?php echo $notification['status'] == 'unread' ? 'unread' : ''; ?>">
                    <div class="notification-content">
                        <p class="notification-message"><?php echo htmlspecialchars($notification['message']); ?></p>
                        <span class="notification-date"><?php echo date('M d, Y H:i', strtotime($notification['created_at'])); ?></span>
                    </div>
                    <?php if ($notification['status'] == 'unread'): ?>
                        <form method="POST">
                            <button type="submit" name="mark_read" value="<?php echo $notification['id']; ?>" class="btn-mark-read" title="Mark as read">
                                ✓
                            </button>
                        </form>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>

<style>
body {
    font-family: Arial, sans-serif;
    line-height: 1.6;
    color: #333;
    background-color: #f4f4f4;
}
.notifications-container {
    max-width: 600px;
    margin: 20px auto;
    padding: 20px;
    background-color: #fff;
    border-radius: 5px;
    box-shadow: 0 2px 5px rgba(0,0,0,0.1);
}
.notifications-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 20px;
}
.notifications-header h1 {
    font-size: 24px;
    margin: 0;
}
.btn-mark-all {
    background-color: #f0f0f0;
    border: none;
    padding: 5px 10px;
    border-radius: 3px;
    cursor: pointer;
    transition: background-color 0.3s;
}
.btn-mark-all:hover {
    background-color: #e0e0e0;
}
.alert {
    background-color: #e7f3fe;
    border-left: 4px solid #2196F3;
    padding: 10px;
    margin-bottom: 15px;
}
.notification-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 10px;
    border-bottom: 1px solid #eee;
    transition: background-color 0.3s;
}
.notification-item:hover {
    background-color: #f9f9f9;
}
.notification-item.unread {
    background-color: #f0f8ff;
}
.notification-content {
    flex-grow: 1;
}
.notification-message {
    margin: 0 0 5px 0;
}
.notification-date {
    font-size: 0.8em;
    color: #888;
}
.btn-mark-read {
    background: none;
    border: none;
    color: #2196F3;
    font-size: 18px;
    cursor: pointer;
    padding: 5px;
}
.btn-mark-read:hover {
    color: #0b7dda;
}
.no-notifications {
    text-align: center;
    color: #888;
}
</style>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
$(document).ready(function() {
    console.log('Document ready');

    // Update notifications every 30 seconds
    setInterval(function() {
        location.reload();
    }, 30000);

    // Notify parent window that notifications have been viewed
    if (window.opener) {
        window.opener.postMessage('notificationsViewed', '*');
    }
});
</script>
