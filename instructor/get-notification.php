<?php
session_start();
include '../config/db.php';

$user_id = $_SESSION['userID'];

$query = "SELECT * FROM notifications 
          WHERE user_id = ? AND recipient_type = 'instructor' AND status = 'unread' 
          ORDER BY created_at DESC";
$stmt = $connect->prepare($query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$notifications = $result->fetch_all(MYSQLI_ASSOC);

header('Content-Type: application/json');
echo json_encode($notifications);