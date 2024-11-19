<?php
require '../vendor/autoload.php';
include '../config/db.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

if (isset($_POST['generate_report'])) {
    $report_type = isset($_POST['report_type']) ? $_POST['report_type'] : 'all_students';
    $course_id = isset($_POST['course_id']) ? $_POST['course_id'] : '';

    if ($report_type == 'all_students') {
        $query = "SELECT user_id, fullname AS student_name FROM users WHERE role = 'student' ORDER BY user_id ASC";
        $report_title = "All Students";
        $fileName = 'all_students.xlsx';
    } elseif ($report_type == 'enrolled_students') {
        $query = "SELECT s.user_id, s.fullname AS student_name, c.course_name, c.course_code 
                  FROM users s 
                  JOIN enrollments e ON s.user_id = e.user_id 
                  JOIN courses c ON e.course_id = c.course_id 
                  WHERE s.role = 'student'
                  ORDER BY s.user_id ASC";
        $report_title = "Enrolled Students";
        $fileName = 'enrolled_students.xlsx';
    } elseif ($report_type == 'specific_course') {
        $query = "SELECT s.user_id, s.fullname AS student_name, c.course_name, c.course_code 
                  FROM users s 
                  JOIN enrollments e ON s.user_id = e.user_id 
                  JOIN courses c ON e.course_id = c.course_id 
                  WHERE s.role = 'student'";

        if ($course_id) {
            $query .= " AND c.course_id = '$course_id'";
        }

        $query .= " ORDER BY s.user_id ASC";
        $report_title = "Enrolled Students in Specific Course";
        $fileName = 'enrolled_students_in_specific_course.xlsx';
    } elseif ($report_type == 'completed_courses') {
        $query = "SELECT s.user_id, s.fullname AS student_name, c.course_name, c.course_code 
                  FROM users s 
                  JOIN enrollments e ON s.user_id = e.user_id 
                  JOIN courses c ON e.course_id = c.course_id 
                  WHERE s.role = 'student' AND e.status = 'completed'
                  ORDER BY s.user_id ASC";
        $report_title = "Students Who Completed Courses";
        $fileName = 'students_who_completed_courses.xlsx';
    } elseif ($report_type == 'students_with_certificates') {
        $query = "SELECT s.user_id, s.fullname AS student_name, c.course_name, c.course_code 
                  FROM users s 
                  JOIN enrollments e ON s.user_id = e.user_id 
                  JOIN courses c ON e.course_id = c.course_id 
                  JOIN certificates cert ON s.user_id = cert.student_id 
                  WHERE s.role = 'student'
                  ORDER BY s.user_id ASC";
        $report_title = "Students with Certificates";
        $fileName = 'students_with_certificates.xlsx';
    } elseif ($report_type == 'all_instructors') {
        $query = "SELECT user_id, fullname AS instructor_name FROM users WHERE role = 'instructor' ORDER BY user_id ASC";
        $report_title = "All Instructors";
        $fileName = 'all_instructors.xlsx';
    } elseif ($report_type == 'instructors_with_courses') {
        $query = "SELECT i.user_id, i.fullname AS instructor_name, c.course_name, c.course_code 
                  FROM users i 
                  JOIN courses c ON i.user_id = c.user_id 
                  WHERE i.role = 'instructor'
                  ORDER BY i.user_id ASC";
        $report_title = "Instructors with Courses";
        $fileName = 'instructors_with_courses.xlsx';
    }

    $result = mysqli_query($connect, $query);

    $spreadsheet = new Spreadsheet();
    $sheet = $spreadsheet->getActiveSheet();

    // Add report title
    $sheet->setCellValue('A1', $report_title);
    $sheet->mergeCells('A1:D1');
    $sheet->getStyle('A1')->getFont()->setBold(true);
    $sheet->getStyle('A1')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

    // Add table headers
    $sheet->setCellValue('A2', $report_type == 'all_instructors' || $report_type == 'instructors_with_courses' ? 'Instructor ID' : 'Student ID');
    $sheet->setCellValue('B2', $report_type == 'all_instructors' || $report_type == 'instructors_with_courses' ? 'Instructor Name' : 'Student Name');
    if ($report_type != 'all_students' && $report_type != 'all_instructors') {
        $sheet->setCellValue('C2', 'Course Name');
        $sheet->setCellValue('D2', 'Course Code');
    }

    // Add table rows
    $rowNumber = 3;
    while ($row = mysqli_fetch_assoc($result)) {
        $sheet->setCellValue('A' . $rowNumber, $row['user_id']);
        $sheet->setCellValue('B' . $rowNumber, $report_type == 'all_instructors' || $report_type == 'instructors_with_courses' ? $row['instructor_name'] : $row['student_name']);
        if ($report_type != 'all_students' && $report_type != 'all_instructors') {
            $sheet->setCellValue('C' . $rowNumber, $row['course_name']);
            $sheet->setCellValue('D' . $rowNumber, $row['course_code']);
        }
        $rowNumber++;
    }

    $writer = new Xlsx($spreadsheet);

    // Redirect output to a client’s web browser (Excel)
    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    header('Content-Disposition: attachment;filename="' . $fileName . '"');
    header('Cache-Control: max-age=0');
    $writer->save('php://output');
    exit;
}
