<?php
include '../config/db.php';

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
    $templateWidth = $_POST['template_width'];
    $templateHeight = $_POST['template_height'];

    // Handle template image upload
    if (isset($_FILES['template_image']) && $_FILES['template_image']['error'] == 0) {
        $uploadDir = '../certificates/templates/';
        $uploadFile = $uploadDir . basename($_FILES['template_image']['name']);
        
        if (move_uploaded_file($_FILES['template_image']['tmp_name'], $uploadFile)) {
            $templateImagePath = $uploadFile;
        } else {
            $_SESSION['error_message'] = "Failed to upload template image.";
        }
    } else {
        $templateImagePath = $_POST['template_image_path'];
    }

    // Handle font file upload
    if (isset($_FILES['font_file']) && $_FILES['font_file']['error'] == 0) {
        $uploadDir = '../certificates/fonts/';
        $uploadFile = $uploadDir . basename($_FILES['font_file']['name']);
        
        if (move_uploaded_file($_FILES['font_file']['tmp_name'], $uploadFile)) {
            $fontPath = $uploadFile;
        } else {
            $_SESSION['error_message'] = "Failed to upload font file.";
        }
    } else {
        $fontPath = $_POST['font_path'];
    }

    // Check if a template exists
    $sqlCheckTemplate = "SELECT * FROM certificate_templates WHERE id = 1";
    $resultCheckTemplate = mysqli_query($connect, $sqlCheckTemplate);

    if (mysqli_num_rows($resultCheckTemplate) > 0) {
        // Update the template details in the database
        $sqlUpdate = "UPDATE certificate_templates SET 
                        template_image_path = ?, 
                        font_path = ?,  
                        text_color = ?, 
                        student_name_x = ?, 
                        student_name_y = ?, 
                        course_name_x = ?, 
                        course_name_y = ?, 
                        completion_date_x = ?, 
                        completion_date_y = ?, 
                        qr_code_x = ?, 
                        qr_code_y = ?, 
                        student_name_font_size = ?, 
                        course_name_font_size = ?, 
                        completion_date_font_size = ?,
                        template_width = ?,
                        template_height = ?
                      WHERE id = 1"; // Assuming you have a default template with id 1
        $stmt = $connect->prepare($sqlUpdate);
        $stmt->bind_param("sssiisiiiiiiiiii",  $templateImagePath, $fontPath,  $textColor, $studentNameX, $studentNameY, $courseNameX, $courseNameY, $completionDateX, $completionDateY, $qrCodeX, $qrCodeY, $studentNameFontSize, $courseNameFontSize, $completionDateFontSize, $templateWidth, $templateHeight);
        $stmt->execute();

        if ($stmt->affected_rows > 0) {
            $_SESSION['success_message'] = "Template updated successfully!";
        } else {
            $_SESSION['error_message'] = "Failed to update template.";
        }
    } else {
        // Insert a new template
        $sqlInsert = "INSERT INTO certificate_templates ( template_image_path, font_path, text_color, student_name_x, student_name_y, course_name_x, course_name_y, completion_date_x, completion_date_y, qr_code_x, qr_code_y, student_name_font_size, course_name_font_size, completion_date_font_size, template_width, template_height) 
                      VALUES ( ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $connect->prepare($sqlInsert);
        $stmt->bind_param("sssiisiiiiiiiiii", $templateImagePath, $fontPath,  $textColor, $studentNameX, $studentNameY, $courseNameX, $courseNameY, $completionDateX, $completionDateY, $qrCodeX, $qrCodeY, $studentNameFontSize, $courseNameFontSize, $completionDateFontSize, $templateWidth, $templateHeight);
        $stmt->execute();

        if ($stmt->affected_rows > 0) {
            $_SESSION['success_message'] = "Template inserted successfully!";
        } else {
            $_SESSION['error_message'] = "Failed to insert template.";
        }
    }
}

// Fetch the current template details
$sqlTemplate = "SELECT * FROM certificate_templates WHERE id = 1"; // Assuming you have a default template with id 1
$resultTemplate = mysqli_query($connect, $sqlTemplate);
$template = mysqli_fetch_assoc($resultTemplate);
?>

<div class="container mt-4 d-flex gap-5">
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
                <input type="file" class="form-control" id="template_image" name="template_image" accept="image/*" onchange="updatePreviewImage()">
                <input type="hidden" name="template_image_path" value="<?= htmlspecialchars($template['template_image_path']); ?>">
            </div>
            <div class="mb-3">
                <label for="font_file" class="form-label">Font File</label>
                <input type="file" class="form-control" id="font_file" name="font_file" accept=".ttf,.otf" onchange="updatePreviewFont()">
                <input type="hidden" name="font_path" value="<?= htmlspecialchars($template['font_path']); ?>">
            </div>
            <div class="mb-3">
                <label for="text_color" class="form-label">Text Color (Hex)</label>
                <input type="color" class="form-control" id="text_color" name="text_color" value="<?= htmlspecialchars($template['text_color']); ?>" required>
            </div>

             <div class="mb-3">
                <label for="student_name_font_size" class="form-label">Student Name Font Size</label>
                <input type="number" id="student_name_font_size" name="student_name_font_size" step="0.01" value="<?= htmlspecialchars($template['student_name_font_size']); ?>" >
            </div>
            <div class="mb-3">
                <label for="student_name_x" class="form-label">Student Name X Position</label>
                <input type="number" id="student_name_x" name="student_name_x" step="0.01" value="<?= htmlspecialchars($template['student_name_x']); ?>" >
            </div>
            <div class="mb-3">
                <label for="student_name_y" class="form-label">Student Name Y Position</label>
                <input type="number" id="student_name_y" name="student_name_y" step="0.01" value="<?= htmlspecialchars($template['student_name_y']); ?>" >
            </div>
            <div class="mb-3">
                <label for="course_name_font_size" class="form-label">Course Name Font Size</label>
                <input type="number" id="course_name_font_size" name="course_name_font_size" step="0.01" value="<?= htmlspecialchars($template['course_name_font_size']); ?>" >
            </div>
            <div class="mb-3">
                <label for="course_name_x" class="form-label">Course Name X Position</label>
                <input type="number" id="course_name_x" name="course_name_x" step="0.01" value="<?= htmlspecialchars($template['course_name_x']); ?>" >
            </div>
            <div class="mb-3">
                <label for="course_name_y" class="form-label">Course Name Y Position</label>
                <input type="number" id="course_name_y" name="course_name_y" step="0.01" value="<?= htmlspecialchars($template['course_name_y']); ?>" >
            </div>
            <div class="mb-3">
                <label for="completion_date_font_size" class="form-label">Completion Date Font Size</label>
                <input type="number" id="completion_date_font_size" name="completion_date_font_size" step="0.01" value="<?= htmlspecialchars($template['completion_date_font_size']); ?>" >
            </div>
            <div class="mb-3">
                <label for="completion_date_x" class="form-label">Completion Date X Position</label>
                <input type="number" id="completion_date_x" name="completion_date_x" step="0.01" value="<?= htmlspecialchars($template['completion_date_x']); ?>" >
            </div>
            <div class="mb-3">
                <label for="completion_date_y" class="form-label">Completion Date Y Position</label>
                <input type="number" id="completion_date_y" name="completion_date_y" step="0.01" value="<?= htmlspecialchars($template['completion_date_y']); ?>" >
            </div>
            <div class="mb-3">
                <label for="qr_code_x" class="form-label">QR Code X Position</label>
                <input type="number" id="qr_code_x" name="qr_code_x" step="0.01" value="<?= htmlspecialchars($template['qr_code_x']); ?>" >
            </div>
            <div class="mb-3">
                <label for="qr_code_y" class="form-label">QR Code Y Position</label>
                <input type="number" id="qr_code_y" name="qr_code_y" step="0.01" value="<?= htmlspecialchars($template['qr_code_y']); ?>" >
            </div>
            <div class="mb-3">
                <label for="template_width" class="form-label">Template Width</label>
                <input type="number" class="form-control" id="template_width" name="template_width" value="<?= htmlspecialchars($template['template_width']); ?>" required>
            </div>
            <div class="mb-3">
                <label for="template_height" class="form-label">Template Height</label>
                <input type="number" class="form-control" id="template_height" name="template_height" value="<?= htmlspecialchars($template['template_height']); ?>" required>
            </div>
           
            <button type="submit" class="btn btn-primary">Save changes</button>
        </form>
    </div>
    <div>
        <h3 class="mt-5">Live Preview</h3>
        <div id="certificatePreview" style="position: relative; width: 800px; height: 600px; background-image: url('<?= htmlspecialchars($template['template_image_path']); ?>'); background-size: cover;">
            <div id="previewStudentName" draggable="true" ondragstart="drag(event)" style="position: absolute; color: <?= htmlspecialchars($template['text_color']); ?>; font-size: <?= htmlspecialchars($template['student_name_font_size']); ?>px; left: <?= htmlspecialchars($template['student_name_x']); ?>px; top: <?= htmlspecialchars($template['student_name_y']); ?>px;">Student Name</div>
            <div id="previewCourseName" draggable="true" ondragstart="drag(event)" style="position: absolute; color: <?= htmlspecialchars($template['text_color']); ?>; font-size: <?= htmlspecialchars($template['course_name_font_size']); ?>px; left: <?= htmlspecialchars($template['course_name_x']); ?>px; top: <?= htmlspecialchars($template['course_name_y']); ?>px;">Course Name</div>
            <div id="previewCompletionDate" draggable="true" ondragstart="drag(event)" style="position: absolute; color: <?= htmlspecialchars($template['text_color']); ?>; font-size: <?= htmlspecialchars($template['completion_date_font_size']); ?>px; left: <?= htmlspecialchars($template['completion_date_x']); ?>px; top: <?= htmlspecialchars($template['completion_date_y']); ?>px;">Completion Date</div>
            <div id="previewQRCode" draggable="true" ondragstart="drag(event)" style="position: absolute; left: <?= htmlspecialchars($template['qr_code_x']); ?>px; top: <?= htmlspecialchars($template['qr_code_y']); ?>px;">
                <img src="" alt="QR Code" style="width: 50px; height: 50px;">
            </div>
        </div>
    </div>
</div>

<script>
let draggedElement;

function drag(event) {
    draggedElement = event.target;
    event.dataTransfer.setData("text/plain", null); //  for Firefox
}

document.getElementById('certificatePreview').addEventListener('dragover', function(event) {
    event.preventDefault();
});

document.getElementById('certificatePreview').addEventListener('drop', function(event) {
    event.preventDefault();
    const rect = event.target.getBoundingClientRect();
    const x = event.clientX - rect.left;
    const y = event.clientY - rect.top;
    draggedElement.style.left = x + 'px';
    draggedElement.style.top = y + 'px';

    // Round off the values to the nearest number
    const roundedX = Math.round(x);
    const roundedY = Math.round(y);

    // Update the form inputs with the new positions
    if (draggedElement.id === 'previewStudentName') {
        document.getElementById('student_name_x').value = roundedX;
        document.getElementById('student_name_y').value = roundedY;
    } else if (draggedElement.id === 'previewCourseName') {
        document.getElementById('course_name_x').value = roundedX;
        document.getElementById('course_name_y').value = roundedY;
    } else if (draggedElement.id === 'previewCompletionDate') {
        document.getElementById('completion_date_x').value = roundedX;
        document.getElementById('completion_date_y').value = roundedY;
    } else if (draggedElement.id === 'previewQRCode') {
        document.getElementById('qr_code_x').value = roundedX;
        document.getElementById('qr_code_y').value = roundedY;
    }
});

document.querySelectorAll('#templateForm input').forEach(input => {
    input.addEventListener('input', updatePreview);
});

function updateTemplateSize() {
    const width = document.getElementById('template_width').value;
    const height = document.getElementById('template_height').value;
    const previewContainer = document.getElementById('certificatePreview');

    previewContainer.style.width = width + 'px';
    previewContainer.style.height = height + 'px';
}



function updatePreview() {
    const studentNameElement = document.getElementById('previewStudentName');
    const courseNameElement = document.getElementById('previewCourseName');
    const completionDateElement = document.getElementById('previewCompletionDate');

    studentNameElement.style.left = Math.round(parseFloat(document.getElementById('student_name_x').value)) + 'px';
    studentNameElement.style.top = Math.round(parseFloat(document.getElementById('student_name_y').value)) + 'px';
    studentNameElement.style.fontSize = Math.round(parseFloat(document.getElementById('student_name_font_size').value)) + 'px';
    studentNameElement.style.color = document.getElementById('text_color').value;

    courseNameElement.style.left = Math.round(parseFloat(document.getElementById('course_name_x').value)) + 'px';
    courseNameElement.style.top = Math.round(parseFloat(document.getElementById('course_name_y').value)) + 'px';
    courseNameElement.style.fontSize = Math.round(parseFloat(document.getElementById('course_name_font_size').value)) + 'px';
    courseNameElement.style.color = document.getElementById('text_color').value;

    completionDateElement.style.left = Math.round(parseFloat(document.getElementById('completion_date_x').value)) + 'px';
    completionDateElement.style.top = Math.round(parseFloat(document.getElementById('completion_date_y').value)) + 'px';
    completionDateElement.style.fontSize = Math.round(parseFloat(document.getElementById('completion_date_font_size').value)) + 'px';
    completionDateElement.style.color = document.getElementById('text_color').value;

    document.getElementById('previewQRCode').style.left = Math.round(parseFloat(document.getElementById('qr_code_x').value)) + 'px';
    document.getElementById('previewQRCode').style.top = Math.round(parseFloat(document.getElementById('qr_code_y').value)) + 'px';
}

function updatePreviewFont() {
    const file = document.getElementById('font_file').files[0];
    if (file) {
        const reader = new FileReader();
        reader.onloadend = function() {
            const fontFace = new FontFace('customFont', reader.result);
            fontFace.load().then(function(loadedFontFace) {
                document.fonts.add(loadedFontFace);
                document.getElementById('previewStudentName').style.fontFamily = 'customFont';
                document.getElementById('previewCourseName').style.fontFamily = 'customFont';
                document.getElementById('previewCompletionDate').style.fontFamily = 'customFont';
            });
        }
        reader.readAsArrayBuffer(file);
    }
}

function updatePreviewImage() {
    const file = document.getElementById('template_image').files[0];
    const reader = new FileReader();
    reader.onloadend = function() {
        document.getElementById('certificatePreview').style.backgroundImage = 'url(' + reader.result + ')';
    }
    if (file) {
        reader.readAsDataURL(file);
    }
}

updateTemplateSize();
</script>