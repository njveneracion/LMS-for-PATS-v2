<?php
include '../config/db.php';
require '../vendor/autoload.php'; // Composer autoload

// Ensure the user is logged in and is an admin
if (!isset($_SESSION['userID']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../auth/login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $fontPath = $_POST['font_path'];
    $textColor = $_POST['text_color'];
    $studentNameX = $_POST['student_name_x'];
    $studentNameY = $_POST['student_name_y'];
    $courseNameX = $_POST['course_name_x'];
    $courseNameY = $_POST['course_name_y'];
    $completionDateX = $_POST['completion_date_x'];
    $completionDateY = $_POST['completion_date_y'];
    $qrCodeX = $_POST['qr_code_x'];
    $qrCodeY = $_POST['qr_code_y'];
    $studentNameFontSize = $_POST['student_name_font_size'];
    $courseNameFontSize = $_POST['course_name_font_size'];
    $completionDateFontSize = $_POST['completion_date_font_size'];

    // Handle template image upload
    $templateImagePath = handleFileUpload('template_image', '../certificates/templates/', $_POST['template_image_path']);

    // Handle font file upload
    $fontPath = handleFileUpload('font_file', '../certificates/fonts/', $_POST['font_path']);

    // Check if a template already exists
    $sqlCheckTemplate = "SELECT * FROM certificate_templates WHERE id = 1";
    $resultCheckTemplate = mysqli_query($connect, $sqlCheckTemplate);

    if (mysqli_num_rows($resultCheckTemplate) > 0) {
        // Update the template details in the database
        $sqlUpdate = "UPDATE certificate_templates SET 
            template_image_path = '$templateImagePath',
            font_path = '$fontPath',
            text_color = '$textColor',
            student_name_x = $studentNameX,
            student_name_y = $studentNameY,
            course_name_x = $courseNameX,
            course_name_y = $courseNameY,
            completion_date_x = $completionDateX,
            completion_date_y = $completionDateY,
            qr_code_x = $qrCodeX,
            qr_code_y = $qrCodeY,
            student_name_font_size = $studentNameFontSize,
            course_name_font_size = $courseNameFontSize,
            completion_date_font_size = $completionDateFontSize
            WHERE id = 1";
        $resultUpdate = mysqli_query($connect, $sqlUpdate);

        if ($resultUpdate) {
            $_SESSION['success_message'] = "Template updated successfully!";
        } else {
            $_SESSION['error_message'] = "Failed to update template.";
        }
    } else {
        // Insert a new template
        $sqlInsert = "INSERT INTO certificate_templates (
            template_image_path, font_path, text_color, student_name_x, student_name_y, 
            course_name_x, course_name_y, completion_date_x, completion_date_y, 
            qr_code_x, qr_code_y, student_name_font_size, course_name_font_size, 
            completion_date_font_size) 
            VALUES (
            '$templateImagePath', '$fontPath', '$textColor', '$studentNameX', '$studentNameY', 
            '$courseNameX', '$courseNameY', '$completionDateX', '$completionDateY', 
            '$qrCodeX', '$qrCodeY', '$studentNameFontSize', '$courseNameFontSize', 
            '$completionDateFontSize')";
        $resultInsert = mysqli_query($connect, $sqlInsert);

        if ($resultInsert) {
            $_SESSION['success_message'] = "Template added successfully!";
        } else {
            $_SESSION['error_message'] = "Failed to add template.";
        }
    }
}

// Fetch the current template details
$sqlTemplate = "SELECT * FROM certificate_templates WHERE id = 1"; // Assuming you have a default template with id 1
$resultTemplate = mysqli_query($connect, $sqlTemplate);
$template = mysqli_fetch_assoc($resultTemplate);

function handleFileUpload($inputName, $uploadDir, $defaultPath) {
    if (isset($_FILES[$inputName]) && $_FILES[$inputName]['error'] == 0) {
        $uploadFile = $uploadDir . basename($_FILES[$inputName]['name']);
        if (move_uploaded_file($_FILES[$inputName]['tmp_name'], $uploadFile)) {
            return $uploadFile;
        } else {
            $_SESSION['error_message'] = "Failed to upload file.";
            return $defaultPath;
        }
    } else {
        return $defaultPath;
    }
}
?>

<div class="container-fluid mt-4 d-flex gap-5">
    <div class="card p-3">
        <h2 class="text-primary"><i class="fas fa-edit me-2"></i>Edit Certificate Template</h2>

        <?php if (isset($_SESSION['success_message'])): ?>
            <div class="alert alert-success">
                <?= $_SESSION['success_message']; unset($_SESSION['success_message']); ?>
            </div>
        <?php endif; ?>

        <?php if (isset($_SESSION['error_message'])): ?>
            <div class="alert alert-danger">
                <?= $_SESSION['error_message']; unset($_SESSION['error_message']); ?>
            </div>
        <?php endif; ?>

        <form method="post" id="templateForm" enctype="multipart/form-data">
            <div class="mb-3">
                <label for="template_image" class="form-label">Template Image</label>
                <input type="file" class="form-control" id="template_image" name="template_image" accept="image/png" onchange="updatePreview()">
                <input type="hidden" name="template_image_path" id="template_image_path" value="<?= htmlspecialchars($template['template_image_path']); ?>">
                <div class="text text-info">Please upload a PNG file for the template image.</div>
            </div>
            <div class="mb-3">
                <label for="font_file" class="form-label">Font File</label>
                <input type="file" class="form-control" id="font_file" name="font_file" accept=".ttf,.otf" onchange="updatePreview()">
                <input type="hidden" name="font_path" value="<?= htmlspecialchars($template['font_path']); ?>">
            </div>
            <div class="mb-3">
                <label for="text_color" class="form-label">Text Color (Hex)</label>
                <input type="color" class="form-control" id="text_color" name="text_color" value="<?= htmlspecialchars($template['text_color']); ?>" required onchange="updatePreview()">
            </div>
            <div class="mb-3">
                <label for="student_name_font_size" class="form-label">Student Name Font Size</label>
                <input type="number" id="student_name_font_size" name="student_name_font_size" step="0.01" value="<?= htmlspecialchars($template['student_name_font_size']); ?>" onchange="updatePreview()">
            </div>
            <div class="mb-3">
                <label for="student_name_x" class="form-label">Student Name X Position</label>
                <input type="number" id="student_name_x" name="student_name_x" step="0.01" value="<?= htmlspecialchars($template['student_name_x']); ?>" onchange="updatePreview()">
            </div>
            <div class="mb-3">
                <label for="student_name_y" class="form-label">Student Name Y Position</label>
                <input type="number" id="student_name_y" name="student_name_y" step="0.01" value="<?= htmlspecialchars($template['student_name_y']); ?>" onchange="updatePreview()">
            </div>
            <div class="mb-3">
                <label for="course_name_font_size" class="form-label">Course Name Font Size</label>
                <input type="number" id="course_name_font_size" name="course_name_font_size" step="0.01" value="<?= htmlspecialchars($template['course_name_font_size']); ?>" onchange="updatePreview()">
            </div>
            <div class="mb-3">
                <label for="course_name_x" class="form-label">Course Name X Position</label>
                <input type="number" id="course_name_x" name="course_name_x" step="0.01" value="<?= htmlspecialchars($template['course_name_x']); ?>" onchange="updatePreview()">
            </div>
            <div class="mb-3">
                <label for="course_name_y" class="form-label">Course Name Y Position</label>
                <input type="number" id="course_name_y" name="course_name_y" step="0.01" value="<?= htmlspecialchars($template['course_name_y']); ?>" onchange="updatePreview()">
            </div>
            <div class="mb-3">
                <label for="completion_date_font_size" class="form-label">Completion Date Font Size</label>
                <input type="number" id="completion_date_font_size" name="completion_date_font_size" step="0.01" value="<?= htmlspecialchars($template['completion_date_font_size']); ?>" onchange="updatePreview()">
            </div>
            <div class="mb-3">
                <label for="completion_date_x" class="form-label">Completion Date X Position</label>
                <input type="number" id="completion_date_x" name="completion_date_x" step="0.01" value="<?= htmlspecialchars($template['completion_date_x']); ?>" onchange="updatePreview()">
            </div>
            <div class="mb-3">
                <label for="completion_date_y" class="form-label">Completion Date Y Position</label>
                <input type="number" id="completion_date_y" name="completion_date_y" step="0.01" value="<?= htmlspecialchars($template['completion_date_y']); ?>" onchange="updatePreview()">
            </div>
            <div class="mb-3">
                <label for="qr_code_x" class="form-label">QR Code X Position</label>
                <input type="number" id="qr_code_x" name="qr_code_x" step="0.01" value="<?= htmlspecialchars($template['qr_code_x']); ?>" onchange="updatePreview()">
            </div>
            <div class="mb-3">
                <label for="qr_code_y" class="form-label">QR Code Y Position</label>
                <input type="number" id="qr_code_y" name="qr_code_y" step="0.01" value="<?= htmlspecialchars($template['qr_code_y']); ?>" onchange="updatePreview()">
            </div>
            <button type="submit" class="btn btn-primary">Save changes</button>
        </form>
    </div>
    <div>
        <!-- Live Preview Section -->
        <h2>Live Preview</h2>
        <div class="preview-container">
            <canvas id="certificate_preview" width="800" height="600"></canvas>
        </div>
    </div>
</div>

<script src="../assets/js/editCertificate.js"></script>