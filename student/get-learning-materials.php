<?php
include '../config/db.php';

if (isset($_GET['courseID'])) {
    $courseID = mysqli_real_escape_string($connect, $_GET['courseID']);
    displayLearningMaterials($connect, $courseID);
}

function displayLearningMaterials($connect, $courseID) {
    $sql = "SELECT * FROM learning_materials WHERE course_id = ? ORDER BY created_at DESC";
    
    $stmt = $connect->prepare($sql);
    $stmt->bind_param("i", $courseID);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        echo '<div class="learning-materials-container">';
        while ($row = $result->fetch_assoc()) {
            $icon = getFileIcon($row['file_path']);
            $uniqueId = 'material-' . $row['material_id']; // Assuming you have a material_id column
            ?>
            <div class="learning-material-item">
                <input type="checkbox" id="<?= $uniqueId ?>" class="material-toggle">
                <label for="<?= $uniqueId ?>" class="material-header">
                    <div class="material-icon-title">
                        <i class="<?= $icon ?>"></i>
                        <h5><?= htmlspecialchars($row['title']) ?></h5>
                    </div>
                    <span class="expand-btn">Expand</span>
                </label>
                <div class="material-content">
                    <p><?= nl2br(htmlspecialchars($row['description'])) ?></p>
                    <?php if ($row['file_path']): ?>
                        <a href="../uploads/learning-materials/<?= htmlspecialchars($row['file_path']) ?>" class="btn btn-primary btn-sm" target="_blank">
                            View Material
                        </a>
                    <?php endif; ?>
                </div>
            </div>
            <?php
        }
        echo '</div>';
    } else {
        echo "<p class='no-materials'>No learning materials available yet.</p>";
    }
    $stmt->close();
}

function getFileIcon($filename) {
    $extension = pathinfo($filename, PATHINFO_EXTENSION);
    switch (strtolower($extension)) {
        case 'pdf':
            return 'fas fa-file-pdf';
        case 'doc':
        case 'docx':
            return 'fas fa-file-word';
        case 'xls':
        case 'xlsx':
            return 'fas fa-file-excel';
        case 'ppt':
        case 'pptx':
            return 'fas fa-file-powerpoint';
        case 'jpg':
        case 'jpeg':
        case 'png':
        case 'gif':
            return 'fas fa-file-image';
        case 'mp4':
        case 'avi':
        case 'mov':
            return 'fas fa-file-video';
        default:
            return 'fas fa-file';
    }
}
?>

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">

<style>
.learning-materials-container {
    max-width: 800px;
    margin: 0 auto;
}

.learning-material-item {
    background-color: #ffffff;
    border: 1px solid #e0e0e0;
    border-radius: 8px;
    margin-bottom: 10px;
    overflow: hidden;
}

.material-toggle {
    display: none;
}

.material-header {
    padding: 12px 15px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    background-color: #f8f9fa;
    cursor: pointer;
}

.material-icon-title {
    display: flex;
    align-items: center;
    gap: 10px;
}

.material-icon-title i {
    font-size: 1.2em;
    color: #0f6fc5;
}

.material-header h5 {
    margin: 0;
    font-size: 1em;
    color: #333;
}

.expand-btn {
    background-color: #0f6fc5;
    color: white;
    padding: 5px 10px;
    border-radius: 4px;
    transition: background-color 0.3s ease;
}

.material-toggle:checked + .material-header .expand-btn {
    background-color: #0d5ca3;
}

.material-content {
    max-height: 0;
    overflow: hidden;
    transition: max-height 0.3s ease;
}

.material-toggle:checked + .material-header + .material-content {
    max-height: 1000px;
}

.material-content p {
    margin: 15px;
    color: #666;
    font-size: 0.9em;
}

.btn-primary {
    background-color: #0f6fc5;
    border-color: #0f6fc5;
    color: white;
    text-decoration: none;
    padding: 5px 10px;
    border-radius: 4px;
    display: inline-block;
    margin: 0 15px 15px;
    font-size: 0.9em;
}

.btn-primary:hover {
    background-color: #0d5ca3;
    border-color: #0d5ca3;
}

.no-materials {
    text-align: center;
    color: #666;
    font-style: italic;
}
</style>
