<?php
include '../config/db.php';
require '../vendor/autoload.php'; // Composer autoload

use Endroid\QrCode\QrCode;
use Endroid\QrCode\Writer\PngWriter;

function generateCertificate($studentID, $studentName, $courseID) {
    require('../certificates/fpdf186/fpdf.php');
    global $connect;

    // Fetch template details
    $sqlTemplate = "SELECT * FROM certificate_templates WHERE id = 1"; // Assuming you have a default template with id 1
    $resultTemplate = mysqli_query($connect, $sqlTemplate);
    $template = mysqli_fetch_assoc($resultTemplate);

    // Check if the certificate already exists
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

    
    $time = time();
    $imagePath = $template['template_image_path'];
    $outputImagePath = "../certificates/download-certificates/$time.png";
    $outputPdfPath = "../certificates/download-certificates/$time.pdf";

    // Set the canvas dimensions
    $canvasWidth = 800;
    $canvasHeight = 600;

    // Load the image and get its dimensions
    $image = imagecreatefrompng($imagePath);
    $imageWidth = imagesx($image);
    $imageHeight = imagesy($image);

    // Resize the image to the canvas size
    $resizedImage = imagecreatetruecolor($canvasWidth, $canvasHeight);
    imagecopyresampled($resizedImage, $image, 0, 0, 0, 0, $canvasWidth, $canvasHeight, $imageWidth, $imageHeight);
    $font = $template['font_path'];
    $color = $template['text_color']; // Assuming the color is stored in hex format, e.g., "#FF5733"
    list($r, $g, $b) = sscanf($color, "#%02x%02x%02x");
    $colorAllocated = imagecolorallocate($resizedImage, $r, $g, $b);

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

    // Function to center text
    function centerText($image, $text, $fontSize, $x, $y, $color, $font) {
        $bbox = imagettfbbox($fontSize, 0, $font, $text);
        $textWidth = $bbox[2] - $bbox[0];
        $textHeight = $bbox[1] - $bbox[7];
        $x = $x - ($textWidth / 2);
        $y = $y + ($textHeight / 2);
        imagettftext($image, $fontSize, 0, $x, $y, $color, $font, $text);
    }

    // Add student's name
    centerText($resizedImage, $studentName, $template['student_name_font_size'], $template['student_name_x'], $template['student_name_y'], $colorAllocated, $font);

    // Add course name
    centerText($resizedImage, $courseName, $template['course_name_font_size'], $template['course_name_x'], $template['course_name_y'], $colorAllocated, $font);

    // Add completion date
    $completionDateText = date('F j, Y', strtotime($completionDate));
    centerText($resizedImage, $completionDateText, $template['completion_date_font_size'], $template['completion_date_x'], $template['completion_date_y'], $colorAllocated, $font);

    // Resize dimensions for the QR code
    $newQRWidth = 100;  // Desired width
    $newQRHeight = 100; // Desired height

    // Create a new true color image with the desired dimensions
    $resizedQRImage = imagecreatetruecolor($newQRWidth, $newQRHeight);

    // Load the original QR code image
    $qrImage = imagecreatefrompng($qrCodePath);

    // Get the original dimensions of the QR code
    $qrWidth = imagesx($qrImage);
    $qrHeight = imagesy($qrImage);

    // Copy and resize the QR code image to the new true color image
    imagecopyresampled($resizedQRImage, $qrImage, 0, 0, 0, 0, $newQRWidth, $newQRHeight, $qrWidth, $qrHeight);

    $qrX = $template['qr_code_x'];
    $qrY = $template['qr_code_y'];

    imagecopy($resizedImage, $resizedQRImage, $qrX, $qrY, 0, 0, $newQRWidth, $newQRHeight);

    // Save the final certificate image
    imagepng($resizedImage, $outputImagePath);
    imagedestroy($image);
    imagedestroy($resizedImage);
    imagedestroy($qrImage);
    imagedestroy($resizedQRImage);

    // Create PDF
    $pdf = new FPDF();
    $pdf->AddPage('L', [$canvasWidth, $canvasHeight]);
    $pdf->Image($outputImagePath, 0, 0, $canvasWidth, $canvasHeight);
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
        <a href="../student/main.php?page=course-content&viewContent=<?= $courseID; ?>" class="goback">Go back</a>
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