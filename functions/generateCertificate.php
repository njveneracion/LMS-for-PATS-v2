<?php
include '../config/db.php';
require '../vendor/autoload.php'; // Composer autoload

use Endroid\QrCode\QrCode;
use Endroid\QrCode\Writer\PngWriter;


function generateCertificate($studentID, $studentName, $courseID) {
    require('../certificates/fpdf186/fpdf.php');

    // Check if the certificate already exists
    global $connect;
    $sqlCheckCert = "SELECT certificate_path FROM certificates WHERE student_id = '$studentID' AND course_id = '$courseID'";
    $resultCheckCert = mysqli_query($connect, $sqlCheckCert);
    
    if (mysqli_num_rows($resultCheckCert) > 0) {
        // Certificate already exists, return the existing path
        $row = mysqli_fetch_assoc($resultCheckCert);
        return $row['certificate_path'];
    }

    // Fetch course details
    $sqlViewCourses = "SELECT course_name FROM courses WHERE course_id = '$courseID' LIMIT 1";
    $resultViewCourses = mysqli_query($connect, $sqlViewCourses);
    if (mysqli_num_rows($resultViewCourses) > 0){
        $row = mysqli_fetch_assoc($resultViewCourses);
        $courseName = $row['course_name'];
    }
   

    // Fetch course completion date (assuming it's stored in the database)
    $sqlCompletionDate = "SELECT completion_date FROM enrollments WHERE user_id = '$studentID' AND course_id = '$courseID'";
    $resultCompletionDate = mysqli_query($connect, $sqlCompletionDate);
    $completionDate = '';

    if (mysqli_num_rows($resultCompletionDate) > 0) {
        $row = mysqli_fetch_assoc($resultCompletionDate);
        $completionDate = $row['completion_date'];
    }

    $font = "../certificates/Radley-Regular.ttf";
    $time = time();
    $imagePath = "../certificates/ecert-template.png";
    $outputImagePath = "../certificates/download-certificates/$time.png";
    $outputPdfPath = "../certificates/download-certificates/$time.pdf";

    // QR code data with student information and completion date
    $qrData = json_encode([
        'studentID' => $studentID,
        'studentName' => $_SESSION['fullname'],
        'courseID' => $courseID,
        'courseName' => $courseName,
        'completionDate' => $completionDate
    ]);

    // Generate QR Code
    $qrCode = new QrCode($qrData);
    $writer = new PngWriter();
    $qrResult = $writer->write($qrCode);

    $qrCodePath = "../certificates/download-certificates/$time-qr.png";
    $qrResult->saveToFile($qrCodePath);

    // Create image
    $image = imagecreatefrompng($imagePath);
    $color = imagecolorallocate($image, 33, 52, 104);
    
    $imageWidth = imagesx($image);
    $imageHeight = imagesy($image);

    // Center the student's name
    $fontSize = 110;
    $bbox = imagettfbbox($fontSize, 0, $font, $studentName);
    $textWidth = $bbox[2] - $bbox[0];
    $x = ($imageWidth - $textWidth) / 2;
    $y = 600; // Adjust this value to move the name up or down
    imagettftext($image, $fontSize, 0, $x, $y, $color, $font, $studentName);

    // Add course name
    $fontSizeCourse = 32;
    $bbox = imagettfbbox($fontSizeCourse, 0, $font, $courseName);
    $textWidth = $bbox[2] - $bbox[0];
    $x = ($imageWidth - $textWidth) / 2;
    $y += 200; // Adjust this value to control the space between name and course
    imagettftext($image, $fontSizeCourse, 0, $x, $y, $color, $font, $courseName);

    // Add completion date
    $fontSizeDate = 32;
    $completionDateText = date('F j, Y', strtotime($completionDate));
    $bbox = imagettfbbox($fontSizeDate, 0, $font, $completionDateText);
    $textWidth = $bbox[2] - $bbox[0];
    $y += 60; // Adjust this value to control the space between course and date
    imagettftext($image, $fontSizeDate, 0, 1000, $y, $color, $font, $completionDateText);

    // Resize dimensions for the QR code
    $newQRWidth = 170;  // Desired width
    $newQRHeight = 170; // Desired height

    // Create a new true color image with the desired dimensions
    $resizedQRImage = imagecreatetruecolor($newQRWidth, $newQRHeight);

    // Load the original QR code image
    $qrImage = imagecreatefrompng($qrCodePath);

    // Get the original dimensions of the QR code
    $qrWidth = imagesx($qrImage);
    $qrHeight = imagesy($qrImage);

    // Copy and resize the QR code image to the new true color image
    imagecopyresampled($resizedQRImage, $qrImage, 0, 0, 0, 0, $newQRWidth, $newQRHeight, $qrWidth, $qrHeight);

    $qrX = $imageWidth - $newQRWidth - 50; // x-axis
    $qrY = imagesy($image) - $newQRHeight - 50; // y-axis

    imagecopy($image, $resizedQRImage, $qrX, $qrY, 0, 0, $newQRWidth, $newQRHeight);

    // Save the final certificate image
    imagepng($image, $outputImagePath);
    imagedestroy($image);
    imagedestroy($qrImage);
    imagedestroy($resizedQRImage);

    // Create PDF
    $pdf = new FPDF();
    $pdf->AddPage('L', 'A5');
    $pdf->Image($outputImagePath, 0, 0, 210, 148);
    $pdf->Output('F', $outputPdfPath);

    // Save certificate information to the database
    $sqlInsertCert = "INSERT INTO certificates (student_id, course_id, certificate_path, generated_at) 
                       VALUES ('$studentID', '$courseID', '$outputPdfPath', NOW())";
    mysqli_query($connect, $sqlInsertCert);

    return $outputPdfPath; // Return the newly created PDF path
}

if(isset($_GET['courseID']) && isset($_GET['studentID'])){
    $courseID = $_GET['courseID'];
    $studentID = $_GET['studentID'];

    // Generate or fetch the existing certificate
    $pdfPath = generateCertificate($studentID, "Student Name", $courseID);
    $imgPath = str_replace(".pdf", ".png", $pdfPath); // Assuming the image has the same filename but with .jpg extension

    ?>
    <div class="certificate-container card">
        <img class="certificate" src="<?php echo $imgPath; ?>" alt="Certificate"/>
        <p>
            <a href="<?php echo $pdfPath; ?>" download="<?php echo basename($pdfPath); ?>">
                <button class="btn-cert">Download Certificate</button>
            </a>
        </p>
        <a href="main.php?page=course-content&viewContent=<?= $courseID; ?>" class="goback">Go back</a>
    </div>
    <?php
}
?>
<style>
.certificate-container {
    display: flex;
    flex-direction: column;
    align-items: center;
    padding: 30px;
    background-color: #ffffff;
    border-radius: 15px;
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
    max-width: 90%;
    margin: 40px auto;
    transition: all 0.3s ease;
}

.certificate-container:hover {
    box-shadow: 0 15px 40px rgba(0, 0, 0, 0.15);
}

.certificate {
    width: 100%;
    max-width: 800px;
    height: auto;
    transition: transform 0.3s ease-in-out;
    border-radius: 10px;
    box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
}

.certificate:hover {
    transform: scale(1.03);
}

.btn-cert {
    padding: 12px 24px;
    font-size: 18px;
    text-align: center;
    border: none;
    background: #0f6fc5;
    color: white;
    border-radius: 8px;
    cursor: pointer;
    transition: all 0.3s ease;
    margin-top: 20px;
    font-weight: 600;
}

.btn-cert:hover {
    background: #0f6fc5;
;
    transform: translateY(-2px);
    box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
}

.goback {
    text-decoration: none;
    font-family: 'Arial', sans-serif;
    font-size: 16px;
    color: #4CAF50;
    padding: 10px 15px;
    border-radius: 5px;
    transition: all 0.3s ease;
    margin-top: 15px;
    font-weight: 500;
}

.goback:hover {
    background-color: #f1f1f1;
    color: #45a049;
}

@media (max-width: 768px) {
    .certificate-container {
        padding: 20px;
        margin: 20px auto;
    }

    .certificate {
        max-width: 100%;
    }

    .btn-cert {
        width: 100%;
        font-size: 16px;
    }

    .goback {
        font-size: 14px;
    }
}
</style>
