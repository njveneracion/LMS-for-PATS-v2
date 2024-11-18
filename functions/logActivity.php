<?php
function logActivity($userId, $action, $description) {
    global $connect; // Assuming $connect is your database connection

    // Prepare the SQL statement
    $sql = "INSERT INTO activity_logs (user_id, action, description) VALUES (?, ?, ?)";

    // Prepare the statement
    if ($stmt = mysqli_prepare($connect, $sql)) {
        // Bind variables to the prepared statement as parameters
        mysqli_stmt_bind_param($stmt, "iss", $userId, $action, $description);

        // Execute the statement
        if (mysqli_stmt_execute($stmt)) {
            // Successfully logged the activity
            return true;
        } else {
            // Error logging the activity
            echo "Error: " . mysqli_error($connect);
            return false;
        }
    } else {
        // Error preparing the statement
        echo "Error: " . mysqli_error($connect);
        return false;
    }
}