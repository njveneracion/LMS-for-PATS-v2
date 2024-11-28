<?php
include '../config/db.php';
require '../vendor/autoload.php'; // Composer autoload

use Fpdf\Fpdf;

class PDF extends FPDF
{
    // Page header
    function Header()
    {
        global $connect; // Ensure $connect is accessible within this function

        $sql = "SELECT file_path FROM pdf_headers ORDER BY id DESC LIMIT 1";
        $result = mysqli_query($connect, $sql);
        $row = mysqli_fetch_assoc($result);
        $logo = $row['file_path'];

        // Define the fixed dimensions for the header image
        $fixedWidth = 190; // Adjust the width as needed
        $fixedHeight = 30; // Adjust the height as needed

        // Logo
        $this->Image($logo, 10, 5, $fixedWidth, $fixedHeight); // Adjust the position as needed

        // Add a horizontal line just under the logo
        $this->SetY($fixedHeight + 5); // Adjust the position as needed
        $this->Line(10, $this->GetY(), $this->GetPageWidth() - 10, $this->GetY());

        // Set the position for the title
        $this->SetY($this->GetY() + 5); // Adjust the position as needed

        // Set font for the title
        $this->SetFont('Arial', 'B', 15);
    }

    // Page footer
    function Footer()
    {
        // Position at 1.5 cm from bottom
        $this->SetY(-15);
        // Arial italic 8
        $this->SetFont('Arial', 'I', 8);
        // Page number
        $this->Cell(0, 10, 'Page ' . $this->PageNo(), 0, 0, 'C');
        
        // Add the current date and time
        date_default_timezone_set('Asia/Singapore'); // Set to GMT+8
        $date = date('F j, Y, g:i A');
        $this->Cell(0, 10, 'Generated on: ' . $date, 0, 0, 'R');
    }
}

// Fetch data based on report type
$report_type = $_POST['report_type'];
$selected_course = $_POST['course_id'];
$page = isset($_POST['page']) ? (int)$_POST['page'] : 1;
$results_per_page = isset($_POST['results_per_page']) ? (int)$_POST['results_per_page'] : 10;

// Calculate the starting result on the current page
$start_from = ($page - 1) * $results_per_page;

// Fetch students based on the selected report type
if ($report_type == 'all_students') {
    $student_query = "SELECT user_id, fullname AS student_name FROM users WHERE role = 'student' ORDER BY user_id ASC LIMIT $start_from, $results_per_page";
} elseif ($report_type == 'enrolled_students') {
    $student_query = "SELECT s.user_id, s.fullname AS student_name, c.course_name, c.course_code 
                      FROM users s 
                      JOIN enrollments e ON s.user_id = e.user_id 
                      JOIN courses c ON e.course_id = c.course_id 
                      WHERE s.role = 'student'
                      ORDER BY s.user_id ASC LIMIT $start_from, $results_per_page";
} elseif ($report_type == 'specific_course') {
    $student_query = "SELECT s.user_id, s.fullname AS student_name, c.course_name, c.course_code 
                      FROM users s 
                      JOIN enrollments e ON s.user_id = e.user_id 
                      JOIN courses c ON e.course_id = c.course_id 
                      WHERE s.role = 'student'";

    if ($selected_course) {
        $student_query .= " AND c.course_id = '$selected_course'";
    }

    $student_query .= " ORDER BY s.user_id ASC LIMIT $start_from, $results_per_page";
} elseif ($report_type == 'completed_courses') {
    $student_query = "SELECT s.user_id, s.fullname AS student_name, c.course_name, c.course_code 
                      FROM users s 
                      JOIN enrollments e ON s.user_id = e.user_id 
                      JOIN courses c ON e.course_id = c.course_id 
                      WHERE s.role = 'student' AND e.status = 'completed'
                      ORDER BY s.user_id ASC LIMIT $start_from, $results_per_page";
} elseif ($report_type == 'students_with_certificates') {
    $student_query = "SELECT DISTINCT s.user_id, s.fullname AS student_name, c.course_name, c.course_code 
                      FROM users s 
                      JOIN enrollments e ON s.user_id = e.user_id 
                      JOIN courses c ON e.course_id = c.course_id 
                      JOIN certificates cert ON s.user_id = cert.student_id 
                      WHERE s.role = 'student'
                      ORDER BY s.user_id ASC LIMIT $start_from, $results_per_page";
} elseif ($report_type == 'all_instructors') {
    $student_query = "SELECT user_id, fullname AS instructor_name FROM users WHERE role = 'instructor' ORDER BY user_id ASC LIMIT $start_from, $results_per_page";
} elseif ($report_type == 'instructors_with_courses') {
    $student_query = "SELECT i.user_id, i.fullname AS instructor_name, c.course_name, c.course_code 
                      FROM users i 
                      JOIN courses c ON i.user_id = c.user_id 
                      WHERE i.role = 'instructor'
                      ORDER BY i.user_id ASC LIMIT $start_from, $results_per_page";
}

$student_result = mysqli_query($connect, $student_query);

$pdf = new PDF();
$pdf->AddPage();
$pdf->SetFont('Arial', 'B', 12);

// Set the title based on the report type
$title = '';
switch ($report_type) {
    case 'all_students':
        $title = 'All Students';
        break;
    case 'enrolled_students':
        $title = 'Enrolled Students';
        break;
    case 'specific_course':
        $title = 'Enrolled Students in Specific Course';
        break;
    case 'completed_courses':
        $title = 'Completed Courses';
        break;
    case 'students_with_certificates':
        $title = 'Students with Certificates';
        break;
    case 'all_instructors':
        $title = 'All Instructors';
        break;
    case 'instructors_with_courses':
        $title = 'Instructors with Courses';
        break;
}

// Add the title
$pdf->Cell(0, 10, $title, 0, 1, 'C');
$pdf->Ln(10);

// Add table headers
$pdf->Cell(40, 10, 'User ID', 1);
$pdf->Cell(60, 10, 'Name', 1);
if ($report_type != 'all_students' && $report_type != 'all_instructors') {
    $pdf->Cell(60, 10, 'Course Name', 1);
    $pdf->Cell(30, 10, 'Course Code', 1);
}
$pdf->Ln();

// Set font for table data
$pdf->SetFont('Arial', '', 12);

// Add table data
while ($row = mysqli_fetch_assoc($student_result)) {
    $pdf->Cell(40, 10, $row['user_id'], 1);
    $pdf->Cell(60, 10, $report_type == 'all_instructors' || $report_type == 'instructors_with_courses' ? $row['instructor_name'] : $row['student_name'], 1);
    if ($report_type != 'all_students' && $report_type != 'all_instructors') {
        $pdf->Cell(60, 10, $row['course_name'], 1);
        $pdf->Cell(30, 10, $row['course_code'], 1);
    }
    $pdf->Ln();
}

$pdf->Output();
?>