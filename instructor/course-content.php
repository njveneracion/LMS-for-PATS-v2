<?php
// Handle Task Sheet submission
if(isset($_POST['addTaskSheet'])){
    $courseID = $_GET['viewContent'];
    $taskSheetTitle = mysqli_real_escape_string($connect, $_POST['taskSheetTitle']);
    $taskSheetDesc = mysqli_real_escape_string($connect, $_POST['taskSheetDesc']);
    $taskSheetFile = $_FILES['taskSheetFile'];

    // File upload logic (similar to material upload)
    $targetDirectory = "../uploads/task-sheets/";
    $fileName = basename($taskSheetFile["name"]);
    $targetFilePath = $targetDirectory . $fileName;
    $fileType = pathinfo($targetFilePath, PATHINFO_EXTENSION);

    if (!empty($taskSheetFile["name"])) {
        $allowedTypes = array("pdf", "doc", "docx");
        if (in_array($fileType, $allowedTypes)) {
            if (move_uploaded_file($taskSheetFile["tmp_name"], $targetFilePath)) {
                $sqlAddTaskSheet = "INSERT INTO task_sheets (task_sheet_id, course_id, task_sheet_title, task_sheet_description, task_sheet_file, created_at) 
                                    VALUES ('$uniqueID', '$courseID', '$taskSheetTitle', '$taskSheetDesc', '$fileName', NOW())";
                $resultAddTaskSheet = mysqli_query($connect, $sqlAddTaskSheet);
                if($resultAddTaskSheet){
                    logActivity($_SESSION['userID'], 'Add Task Sheets', "Added new task sheet: $taskSheetTitle");
                    $toastMessage = "Task Sheet Added Successfully!";
                }
            } else {
                $toastMessage = "Sorry, there was an error uploading your file.";
            }
        } else {
            $toastMessage = "Sorry, only PDF and DOC files are allowed for Task Sheets.";
        }
    } else {
        $toastMessage = "Please select a file to upload.";
    }
}

// Handle Pre-assessment submission
if(isset($_POST['addPreAssessment'])){
    $courseID = $_GET['viewContent'];
    $preAssessmentTitle = mysqli_real_escape_string($connect, $_POST['preAssessmentTitle']);
    $preAssessmentDesc = mysqli_real_escape_string($connect, $_POST['preAssessmentDesc']);

    $sqlAddPreAssessment = "INSERT INTO assessments (assessment_id, course_id, assessment_title, assessment_description, assessment_type, created_at) 
                            VALUES ('$uniqueID', '$courseID', '$preAssessmentTitle', '$preAssessmentDesc', 'pre', NOW())";
    $resultAddPreAssessment = mysqli_query($connect, $sqlAddPreAssessment);
    if($resultAddPreAssessment){
        $toastMessage = "Pre-assessment Added Successfully!";
    } else {
        $toastMessage = "Error adding Pre-assessment: " . mysqli_error($connect);
    }
}

// Handle Post-assessment submission
if(isset($_POST['addPostAssessment'])){
    $courseID = $_GET['viewContent'];
    $postAssessmentTitle = mysqli_real_escape_string($connect, $_POST['postAssessmentTitle']);
    $postAssessmentDesc = mysqli_real_escape_string($connect, $_POST['postAssessmentDesc']);

    $sqlAddPostAssessment = "INSERT INTO assessments (assessment_id, course_id, assessment_title, assessment_description, assessment_type, created_at) 
                             VALUES ('$uniqueID', '$courseID', '$postAssessmentTitle', '$postAssessmentDesc', 'post', NOW())";
    $resultAddPostAssessment = mysqli_query($connect, $sqlAddPostAssessment);
    if($resultAddPostAssessment){
        $toastMessage = "Post-assessment Added Successfully!";
    } else {
        $toastMessage = "Error adding Post-assessment: " . mysqli_error($connect);
    }
}

if (isset($_GET['delete']) && isset($_GET['type'])) {
    $deleteId = mysqli_real_escape_string($connect, $_GET['delete']);
    $courseID = mysqli_real_escape_string($connect, $_GET['viewContent']);
    $type = mysqli_real_escape_string($connect, $_GET['type']);

    switch ($type) {
        case 'Material':
            $deleteSql = "DELETE FROM course_material WHERE material_id = '$deleteId' AND course_id = '$courseID'";
            break;
        case 'Quiz':
            $deleteSql = "DELETE FROM quiz WHERE quiz_id = '$deleteId' AND course_id = '$courseID'";
            break;
        case 'Task Sheet':
            $deleteSql = "DELETE FROM task_sheets WHERE task_sheet_id = '$deleteId' AND course_id = '$courseID'";
            break;
        case 'Pre-assessment':
            $deleteSql = "DELETE FROM assessments WHERE assessment_id = '$deleteId' AND course_id = '$courseID' AND assessment_type = 'pre'";
            break;
        case 'Post-assessment':
            $deleteSql = "DELETE FROM assessments WHERE assessment_id = '$deleteId' AND course_id = '$courseID' AND assessment_type = 'post'";
            break;
        default:
            $toastMessage = "Invalid content type!";
            break;
    }

    if (isset($deleteSql)) {
        $deleteResult = mysqli_query($connect, $deleteSql);
        if ($deleteResult) {
            $toastMessage = ucfirst($type) . " deleted successfully!";
        } else {
            $toastMessage = "Error deleting " . strtolower($type) . ": " . mysqli_error($connect);
        }
    }
}

if(isset($_POST['addMaterial'])){
    $courseID = $_GET['viewContent'];
    $materialTitle = $_POST['materialTitle'];
    $materialDesc = $_POST['materialDesc'];
    $materialFile = $_FILES['file'];

    // File upload syntax
    $targetDirectory = "../uploads/course-materials/"; // Directory where you want to store uploads
    $fileName = basename($materialFile["name"]);
    $targetFilePath = $targetDirectory . $fileName;
    $fileType = pathinfo($targetFilePath, PATHINFO_EXTENSION);

    // Check if file is selected
    if (!empty($materialFile["name"])) {
        // Allow certain file formats based on the selected file type
        switch ($fileType) {
            case 'mp4':
            case 'mov':
            case 'avi':
                $allowedTypes = ["mp4", "mov", "avi"];
                break;
            case 'jpg':
            case 'jpeg':
            case 'png':
            case 'gif':
                $allowedTypes = ["jpg", "jpeg", "png", "gif"];
                break;
            case 'pdf':
            case 'txt':
            case 'docx':
            case 'pptx':
                $allowedTypes = ["pdf", "txt", "docx", "pptx"];
                break;
            default:
                $allowedTypes = [];
                break;
        }
        
        if (in_array($fileType, $allowedTypes)) {
            // Upload file
            if (move_uploaded_file($_FILES["file"]["tmp_name"], $targetFilePath)) {
                // Insertion query
                $sqlAddMaterial = "INSERT INTO course_material (material_id, course_id, material_title, material_desc, material_file, created_at) VALUES ('$uniqueID', '$courseID', '$materialTitle', '$materialDesc', '$fileName', NOW())";
                $resultAddMaterial = mysqli_query($connect, $sqlAddMaterial);
                if($resultAddMaterial){
                    $toastMessage = "Material Added Successfully!";
                }
            } else {
                echo "Sorry, there was an error uploading your file.";
            }
        } else {
            echo "Sorry, only allowed file types for selected option are allowed.";
        }
    } else {
        echo "Please select a file to upload.";
    }
}

if(isset($_POST['editContent'])) {
    $editId = mysqli_real_escape_string($connect, $_POST['editContentId']);
    $editType = mysqli_real_escape_string($connect, $_POST['editContentType']);
    $editTitle = mysqli_real_escape_string($connect, $_POST['editContentTitle']);
    $editDescription = mysqli_real_escape_string($connect, $_POST['editContentDescription']);

    $fileUpdated = false;
    $newFileName = '';

    if (($editType === 'Material' || $editType === 'Task Sheet') && !empty($_FILES['editContentFile']['name'])) {
        $targetDirectory = ($editType === 'Material') ? "course_materials/" : "task_sheets/";
        $fileName = basename($_FILES['editContentFile']['name']);
        $targetFilePath = $targetDirectory . $fileName;
        $fileType = pathinfo($targetFilePath, PATHINFO_EXTENSION);

        $allowedTypes = ['pdf', 'doc', 'docx', 'ppt', 'pptx', 'txt'];
        if (in_array(strtolower($fileType), $allowedTypes)) {
            if (move_uploaded_file($_FILES['editContentFile']['tmp_name'], $targetFilePath)) {
                $fileUpdated = true;
                $newFileName = $fileName;
            } else {
                $toastMessage = "Sorry, there was an error uploading your file.";
            }
        } else {
            $toastMessage = "Sorry, only PDF, DOC, DOCX, and TXT files are allowed.";
        }
    }

    switch ($editType) {
        case 'Material':
            $updateSql = "UPDATE course_material SET material_title = '$editTitle', material_desc = '$editDescription'";
            if ($fileUpdated) {
                $updateSql .= ", material_file = '$newFileName'";
            }
            $updateSql .= " WHERE material_id = '$editId'";
            break;
        case 'Quiz':
            $updateSql = "UPDATE quiz SET quiz_name = '$editTitle', quiz_description = '$editDescription' WHERE quiz_id = '$editId'";
            break;
        case 'Task Sheet':
            $updateSql = "UPDATE task_sheets SET task_sheet_title = '$editTitle', task_sheet_description = '$editDescription'";
            if ($fileUpdated) {
                $updateSql .= ", task_sheet_file = '$newFileName'";
            }
            $updateSql .= " WHERE task_sheet_id = '$editId'";
            break;
        case 'Pre-assessment':
            $updateSql = "UPDATE assessments SET assessment_title = '$editTitle', assessment_description = '$editDescription' WHERE assessment_id = '$editId' AND assessment_type = 'pre'";
            break;
        case 'Post-assessment':
            $updateSql = "UPDATE assessments SET assessment_title = '$editTitle', assessment_description = '$editDescription' WHERE assessment_id = '$editId' AND assessment_type = 'post'";
            break;
    }

    if (isset($updateSql)) {
        $updateResult = mysqli_query($connect, $updateSql);
        if ($updateResult) {
            $toastMessage = ucfirst($editType) . " updated successfully!";
        } else {
            $toastMessage = "Error updating " . strtolower($editType) . ": " . mysqli_error($connect);
        }
    }

}

if (isset($_GET['viewContent'])) {
    $courseID = mysqli_real_escape_string($connect, $_GET['viewContent']);

    $sqlViewCourses = "SELECT * FROM courses WHERE course_id = '$courseID' LIMIT 1";
    $resultViewCourses = mysqli_query($connect, $sqlViewCourses);

    if (isset($_POST['addCourseDesc'])) {
        $courseDescription = mysqli_real_escape_string($connect, $_POST['course-description']);

        $sqlAddDesc = "UPDATE courses SET course_desc = '$courseDescription' WHERE course_id = '$courseID'";
        $resultAddDesc = mysqli_query($connect, $sqlAddDesc);

        if ($resultAddDesc) {
            echo "  <script type='text/javascript'>
                        window.location.href = 'main.php?page=course-content&viewContent=" . urlencode($courseID) . "';
                    </script>";
            exit();
        } else {
            echo "Error: " . mysqli_error($connect);
        }
    }

    if ($rowCourses = mysqli_fetch_assoc($resultViewCourses)) {
        $courseName = htmlspecialchars($rowCourses['course_name']);
        $courseImg = htmlspecialchars($rowCourses['course_img']);
        $courseDesc = htmlspecialchars($rowCourses['course_desc']);

        // Determine the initial modal title
        $modalTitle = empty($courseDesc) ? 'Add description' : 'Edit description';

    if(isset($_POST['quiz_btn'])){
        $quizName = $_POST['quiz_name'];
        $quizDesc = $_POST['quiz_description'];

        $sqlAddQuiz =  "INSERT INTO quiz (quiz_id, course_id, quiz_name, quiz_description) VALUES ('$uniqueID', '$courseID', '$quizName', '$quizDesc')";

        $resultAddQuiz = mysqli_query($connect, $sqlAddQuiz);
        if($resultAddQuiz == 1){
            logActivity($_SESSION['userID'], 'Add Quiz', "Added new quiz: $quizName");
            $toastMessage = "Quiz added successfully!";
        } else {
            echo "Error: " + $sqlAddQuiz + "<br>" + $error;
        }
    }

    // Handle Learning Material submission
    if(isset($_POST['addLearningMaterial'])){
        $courseID = $_GET['viewContent'];
        $materialTitle = mysqli_real_escape_string($connect, $_POST['materialTitle']);
        $materialDesc = mysqli_real_escape_string($connect, $_POST['materialDesc']);
        $materialFile = $_FILES['materialFile'];

        $targetDirectory = "../uploads/learning-materials/";
        $fileName = basename($materialFile["name"]);
        $targetFilePath = $targetDirectory . $fileName;
        $fileType = pathinfo($targetFilePath, PATHINFO_EXTENSION);

        if (!empty($materialFile["name"])) {
            $allowedTypes = ["pdf", "doc", "docx", "ppt", "pptx", "txt"];
            if (in_array($fileType, $allowedTypes)) {
                if (move_uploaded_file($materialFile["tmp_name"], $targetFilePath)) {
                    $sqlAddMaterial = "INSERT INTO learning_materials (material_id, course_id, title, description, file_path, created_at) 
                                       VALUES ('$uniqueID', '$courseID', '$materialTitle', '$materialDesc', '$fileName', NOW())";
                    $resultAddMaterial = mysqli_query($connect, $sqlAddMaterial);
                    if($resultAddMaterial){
                        logActivity($_SESSION['userID'], 'Add Learning Material', "Added new learning material: $materialTitle");
                        $toastMessage = "Learning Material Added Successfully!";
                    }
                } else {
                    $toastMessage = "Sorry, there was an error uploading your file.";
                }
            } else {
                $toastMessage = "Sorry, only PDF, DOC, DOCX, PPT, PPTX, and TXT files are allowed for Learning Materials.";
            }
        } else {
            $toastMessage = "Please select a file to upload.";
        }
    }

    // Handle Learning Material deletion
    if (isset($_GET['delete']) && $_GET['type'] == 'LearningMaterial') {
        $deleteId = mysqli_real_escape_string($connect, $_GET['delete']);
        $courseID = mysqli_real_escape_string($connect, $_GET['viewContent']);

        $deleteSql = "DELETE FROM learning_materials WHERE material_id = '$deleteId' AND course_id = '$courseID'";
        $deleteResult = mysqli_query($connect, $deleteSql);
        if ($deleteResult) {
            $toastMessage = "Learning Material deleted successfully!";
        } else {
            $toastMessage = "Error deleting Learning Material: " . mysqli_error($connect);
        }
    }

    // Handle Learning Material edit
    if(isset($_POST['editLearningMaterial'])) {
        $editId = mysqli_real_escape_string($connect, $_POST['editMaterialId']);
        $editTitle = mysqli_real_escape_string($connect, $_POST['editMaterialTitle']);
        $editDescription = mysqli_real_escape_string($connect, $_POST['editMaterialDescription']);

        $updateSql = "UPDATE learning_materials SET title = '$editTitle', description = '$editDescription' WHERE material_id = '$editId'";
        
        if (!empty($_FILES['editMaterialFile']['name'])) {
            $targetDirectory = "../uploads/learning-materials/";
            $fileName = basename($_FILES['editMaterialFile']['name']);
            $targetFilePath = $targetDirectory . $fileName;
            $fileType = pathinfo($targetFilePath, PATHINFO_EXTENSION);

            $allowedTypes = ['pdf', 'doc', 'docx', 'ppt', 'pptx', 'txt'];
            if (in_array(strtolower($fileType), $allowedTypes)) {
                if (move_uploaded_file($_FILES['editMaterialFile']['tmp_name'], $targetFilePath)) {
                    $updateSql = "UPDATE learning_materials SET title = '$editTitle', description = '$editDescription', file_path = '$fileName' WHERE material_id = '$editId'";
                } else {
                    $toastMessage = "Sorry, there was an error uploading your file.";
                }
            } else {
                $toastMessage = "Sorry, only PDF, DOC, DOCX, PPT, PPTX, and TXT files are allowed.";
            }
        }

        $updateResult = mysqli_query($connect, $updateSql);
        if ($updateResult) {
            $toastMessage = "Learning Material updated successfully!";
        } else {
            $toastMessage = "Error updating Learning Material: " . mysqli_error($connect);
        }
    }

    // Fetch learning materials separately
    $sqlLearningMaterials = "SELECT * FROM learning_materials WHERE course_id = '$courseID' ORDER BY created_at ASC";
    $resultLearningMaterials = mysqli_query($connect, $sqlLearningMaterials);

?>
<div class="container-fluid px-4">
    <div class="row g-4">
        <div class="col-12">
            <div class="card shadow-sm mb-4">
                <div class="card-body">
                    <div class="course-header d-flex flex-column flex-md-row align-items-center">
                        <img src="<?= $courseImg; ?>" alt="Course Image" class="img-fluid rounded mb-3 mb-md-0 me-md-4" style="max-width: 300px;">
                        <div class="course-info flex-grow-1">
                            <h2 class="course-name"><?= $courseName; ?></h2>
                            <p class="course-description"><?= $courseDesc; ?></p>
                            <button class="btn btn-outline-secondary btn-sm mt-2" data-bs-toggle="modal" data-bs-target="#addDesc">
                                <i class="fa-solid fa-pen-to-square me-2"></i><?= empty($courseDesc) ? 'Add description' : 'Edit description' ?>
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- New section for Learning Materials -->
            <div class="card shadow-sm mb-4">
                <div class="card-body">
                    <h4 class="card-title mb-4">Learning Materials</h4>
                    <div class="d-flex flex-wrap gap-2 mb-4">
                        <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#addLearningMaterial">
                            <i class="fa-solid fa-circle-plus me-2"></i>Add learning material
                        </button>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-hover table-striped align-middle">
                            <thead class="table-primary">
                                <tr>
                                    <th>Title</th>
                                    <th>Description</th>
                                    <th>File</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                while ($row = mysqli_fetch_assoc($resultLearningMaterials)) {
                                    $id = htmlspecialchars($row['material_id']);
                                    $title = htmlspecialchars($row['title']);
                                    $description = htmlspecialchars($row['description']);
                                    $file = htmlspecialchars($row['file_path']);
                                ?>
                                    <tr>
                                        <td><?= $title; ?></td>
                                        <td><?= $description; ?></td>
                                        <td><?= $file ? "<a href='learning_materials/$file'>$file</a>" : "N/A"; ?></td>
                                        <td>
                                            <div class="btn-group" role="group">
                                                <a href="../uploads/learning-materials/<?= $file; ?>" class="btn btn-info btn-sm" target="_blank" title="View"><i class="fa-solid fa-eye"></i></a>
                                                <button class="btn btn-primary btn-sm" onclick="editLearningMaterial('<?= $id; ?>', '<?= addslashes($title); ?>', '<?= addslashes($description); ?>', '<?= $file; ?>')" title="Edit"><i class="fa-solid fa-edit"></i></button>
                                                <button class="btn btn-danger btn-sm" onclick="confirmDelete('<?= $id; ?>', '<?= $courseID; ?>', 'LearningMaterial')" title="Delete"><i class="fa-solid fa-trash"></i></button>
                                            </div>
                                        </td>
                                    </tr>
                                <?php } ?>
                            </tbody>
                        </table>             
                    </div>
                </div>
            </div>

            <div class="card shadow-sm">
                <div class="card-body">
                    <h4 class="card-title mb-4">Course Content</h4>
                    <div class="d-flex flex-wrap gap-2 mb-4 add-buttons">
                        <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#addMaterial">
                            <i class="fa-solid fa-circle-plus me-2"></i>Add material
                        </button>
                        <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#addQuiz">
                            <i class="fa-solid fa-circle-plus me-2"></i>Add quiz
                        </button>
                        <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#addTaskSheet">
                            <i class="fa-solid fa-circle-plus me-2"></i>Add task sheet
                        </button>
                        <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#addPreAssessment">
                            <i class="fa-solid fa-circle-plus me-2"></i>Add pre-assessment
                        </button>
                        <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#addPostAssessment">
                            <i class="fa-solid fa-circle-plus me-2"></i>Add post-assessment
                        </button>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-hover table-striped align-middle">
                            <thead class="table-primary">
                                <tr>
                                    <th>Title</th>
                                    <th>Description</th>
                                    <th>File</th>
                                    <th>Type</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $sqlCombined = "SELECT material_id AS id,
                                                        material_title AS title,
                                                        material_desc AS description,
                                                        material_file AS file,
                                                        'Material' AS type,
                                                        created_at
                                                FROM course_material WHERE course_id = '$courseID'
                                                UNION ALL
                                                SELECT quiz_id AS id,
                                                        quiz_name AS title,
                                                        quiz_description AS description,
                                                        '' AS file,
                                                        'Quiz' AS type,
                                                        created_at
                                                FROM quiz WHERE course_id = '$courseID'
                                                UNION ALL
                                                SELECT task_sheet_id AS id,
                                                        task_sheet_title AS title,
                                                        task_sheet_description AS description,
                                                        task_sheet_file AS file,
                                                        'Task Sheet' AS type,
                                                        created_at
                                                FROM task_sheets WHERE course_id = '$courseID'
                                                UNION ALL
                                                SELECT assessment_id AS id,
                                                        assessment_title AS title,
                                                        assessment_description AS description,
                                                        '' AS file,
                                                        CASE WHEN assessment_type = 'pre' THEN 'Pre-assessment' ELSE 'Post-assessment' END AS type,
                                                        created_at
                                                FROM assessments WHERE course_id = '$courseID'
                                                ORDER BY created_at ASC";
                                            
                                $resultCombined = mysqli_query($connect, $sqlCombined);
                                            
                                while ($row = mysqli_fetch_assoc($resultCombined)) {
                                    $id = htmlspecialchars($row['id']);
                                    $title = htmlspecialchars($row['title']);
                                    $description = htmlspecialchars($row['description']);
                                    $file = htmlspecialchars($row['file']);
                                    $type = htmlspecialchars($row['type']);
                                ?>
                                    <tr>
                                        <td><?= $title; ?></td>
                                        <td><?= $description; ?></td>
                                        <td><?= $file ? "<a href='../uploads/course-materials/$file'>$file</a>" : "N/A"; ?></td>
                                        <td><?= $type; ?></td>
                                        <td>
                                            <div class="btn-group" role="group">
                                                <?php if ($type === 'Material' || $type === 'Task Sheet') { ?>
                                                    <a href="<?= $type === 'Material' ? 'course_materials/' : 'task_sheets/'; ?><?= $file; ?>" class="btn btn-info btn-sm" target="_blank" title="View"><i class="fa-solid fa-eye"></i></a>
                                                <?php } ?>

                                                <?php if ($type === 'Quiz') { ?>
                                                    <a href="main.php?page=quiz-question&courseID=<?= $courseID; ?>&quizContent=<?= $id; ?>&quizName=<?= $title; ?>" class="btn btn-warning btn-sm" title="Manage Questions"><i class="fa-solid fa-file"></i></a>
                                                <?php } elseif ($type === 'Pre-assessment' || $type === 'Post-assessment') { ?>
                                                    <a href="main.php?page=assessment-question&courseID=<?= $courseID; ?>&assessmentContent=<?= $id; ?>&assessmentName=<?= $title; ?>&assessmentType=<?= $type; ?>" class="btn btn-info btn-sm" title="Manage Questions"><i class="fa-solid fa-file-alt"></i></a>
                                                <?php } ?>

                                                <button class="btn btn-primary btn-sm" onclick="editContent(<?= $id; ?>, '<?= $type; ?>', '<?= addslashes($title); ?>', '<?= addslashes($description); ?>', '<?= $file; ?>')" title="Edit"><i class="fa-solid fa-edit"></i></button>

                                                <button class="btn btn-danger btn-sm" onclick="confirmDelete(<?= $id; ?>, <?= $courseID; ?>, '<?= $type; ?>')" title="Delete">
                                                    <i class="fa-solid fa-trash"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                <?php } ?>
                            </tbody>
                        </table>             
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Script for delete confirmation -->
<script>
    function confirmDelete(id, courseID, type) {
    var modal = new bootstrap.Modal(document.getElementById('deleteConfirmModal'));
    var deleteBtn = document.getElementById('confirmDeleteBtn');
    deleteBtn.href = `main.php?page=course-content&viewContent=${courseID}&delete=${id}&type=${type}`;
    
    // Update modal text based on content type
    var modalBody = document.querySelector('#deleteConfirmModal .modal-body');
    modalBody.textContent = `Are you sure you want to delete this ${type.toLowerCase()}?`;
    
    modal.show();
}
</script>

<!-- Modal for confirmation of materials deletion -->
<div class="modal fade" id="deleteConfirmModal" tabindex="-1" aria-labelledby="deleteConfirmModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="deleteConfirmModalLabel">Confirm Deletion</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                Are you sure you want to delete this material?
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <a href="main.php?page=course-content&viewContent=<?= $courseID; ?>&delete=<?= $id; ?>" id="confirmDeleteBtn" class="btn btn-danger">Delete</a>
            </div>
        </div>
    </div>
</div>

<!-- Modal for description -->
<div class="modal fade" id="addDesc" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h1 class="modal-title fs-5" id="staticBackdropLabel"><?= $modalTitle; ?></h1>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form method="post">
                    <textarea name="course-description" class="form-control" placeholder="text here" rows="10"><?= htmlspecialchars($courseDesc); ?></textarea>
                    <input type="submit" name="addCourseDesc" value="Add" class="form-control btn btn-primary mt-2">
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Modal for material -->
<div class="modal fade" id="addMaterial" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h1 class="modal-title fs-5" id="staticBackdropLabel">
                    <i class="fas fa-file-upload me-2"></i>Add Material to <?= htmlspecialchars($courseName); ?>
                </h1>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form method="post" enctype="multipart/form-data">
                    <div class="mb-3">
                        <label for="materialTitle" class="form-label">Title</label>
                        <input type="text" class="form-control" id="materialTitle" name="materialTitle" placeholder="Enter material title" required>
                    </div>
                    <div class="mb-3">
                        <label for="materialDesc" class="form-label">Description</label>
                        <textarea name="materialDesc" id="materialDesc" rows="4" class="form-control" placeholder="Enter material description"></textarea>
                    </div>
                    <div class="mb-3">
                        <label for="fileType" class="form-label">Choose a file type:</label>
                        <select class="form-select" id="fileType" onchange="showInputMaterial()">
                            <option value="none">Select File Type</option>
                            <option value="video">Video</option>
                            <option value="file">File</option>
                            <option value="image">Image</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <input class="form-control" type="file" id="fileInput" name="file" style="display: none;">
                    </div>
                    <div class="d-flex justify-content-end">
                        <button type="button" class="btn btn-secondary me-2" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary" name="addMaterial">Add Material</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Modal for Task Sheet -->
<div class="modal fade" id="addTaskSheet" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h1 class="modal-title fs-5" id="staticBackdropLabel">
                    <i class="fas fa-file-alt me-2"></i>Add Task Sheet
                </h1>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form method="post" enctype="multipart/form-data">
                    <div class="mb-3">
                        <label for="taskSheetTitle" class="form-label">Title</label>
                        <input type="text" class="form-control" id="taskSheetTitle" name="taskSheetTitle" required>
                    </div>
                    <div class="mb-3">
                        <label for="taskSheetDesc" class="form-label">Description</label>
                        <textarea name="taskSheetDesc" id="taskSheetDesc" rows="4" class="form-control"></textarea>
                    </div>
                    <div class="mb-3">
                        <label for="taskSheetFile" class="form-label">Upload File</label>
                        <input class="form-control" type="file" id="taskSheetFile" name="taskSheetFile">
                    </div>
                    <div class="d-flex justify-content-end">
                        <button type="button" class="btn btn-secondary me-2" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary" name="addTaskSheet">Add Task Sheet</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Modal for Pre-assessment -->
<div class="modal fade" id="addPreAssessment" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h1 class="modal-title fs-5" id="staticBackdropLabel">
                    <i class="fas fa-clipboard-check me-2"></i>Add Pre-assessment
                </h1>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form method="post">
                    <div class="mb-3">
                        <label for="preAssessmentTitle" class="form-label">Title</label>
                        <input type="text" class="form-control" id="preAssessmentTitle" name="preAssessmentTitle" required>
                    </div>
                    <div class="mb-3">
                        <label for="preAssessmentDesc" class="form-label">Description</label>
                        <textarea name="preAssessmentDesc" id="preAssessmentDesc" rows="4" class="form-control"></textarea>
                    </div>
                    <div class="d-flex justify-content-end">
                        <button type="button" class="btn btn-secondary me-2" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary" name="addPreAssessment">Add Pre-assessment</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Modal for Post-assessment -->
<div class="modal fade" id="addPostAssessment" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h1 class="modal-title fs-5" id="staticBackdropLabel">
                    <i class="fas fa-clipboard-check me-2"></i>Add Post-assessment
                </h1>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form method="post">
                    <div class="mb-3">
                        <label for="postAssessmentTitle" class="form-label">Title</label>
                        <input type="text" class="form-control" id="postAssessmentTitle" name="postAssessmentTitle" required>
                    </div>
                    <div class="mb-3">
                        <label for="postAssessmentDesc" class="form-label">Description</label>
                        <textarea name="postAssessmentDesc" id="postAssessmentDesc" rows="4" class="form-control"></textarea>
                    </div>
                    <div class="d-flex justify-content-end">
                        <button type="button" class="btn btn-secondary me-2" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary" name="addPostAssessment">Add Post-assessment</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
function showInputMaterial() {
    var fileType = document.getElementById("fileType").value;
    var fileInput = document.getElementById("fileInput");
    
    if (fileType !== "none") {
        fileInput.style.display = "block";
    } else {
        fileInput.style.display = "none";
    }
}
</script>

<!-- Modal for quiz -->
<div class="modal fade" id="addQuiz" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h1 class="modal-title fs-5" id="staticBackdropLabel">
                    <i class="fas fa-quiz me-2"></i>Create New Quiz
                </h1>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form method="post" id="quizForm">
                    <div class="mb-3">
                        <label for="quiz_name" class="form-label">Quiz Name</label>
                        <input type="text" name="quiz_name" id="quiz_name" class="form-control" placeholder="Enter quiz name" required>
                    </div>
                    <div class="mb-3">
                        <label for="quiz_description" class="form-label">Description (Optional)</label>
                        <textarea name="quiz_description" id="quiz_description" class="form-control" rows="3" placeholder="Enter quiz description"></textarea>
                    </div>
                    <div class="d-flex justify-content-end">
                        <button type="button" class="btn btn-secondary me-2" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary" name="quiz_btn">Create Quiz</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Modal for Learning Material -->
<div class="modal fade" id="addLearningMaterial" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h1 class="modal-title fs-5" id="staticBackdropLabel">
                    <i class="fas fa-book me-2"></i>Add Learning Material
                </h1>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form method="post" enctype="multipart/form-data">
                    <div class="mb-3">
                        <label for="materialTitle" class="form-label">Title</label>
                        <input type="text" class="form-control" id="materialTitle" name="materialTitle" required>
                    </div>
                    <div class="mb-3">
                        <label for="materialDesc" class="form-label">Description</label>
                        <textarea name="materialDesc" id="materialDesc" rows="4" class="form-control"></textarea>
                    </div>
                    <div class="mb-3">
                        <label for="materialFile" class="form-label">Upload File</label>
                        <input class="form-control" type="file" id="materialFile" name="materialFile" required>
                    </div>
                    <div class="d-flex justify-content-end">
                        <button type="button" class="btn btn-secondary me-2" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary" name="addLearningMaterial">Add Learning Material</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Modal for editing Learning Material -->
<div class="modal fade" id="editLearningMaterialModal" tabindex="-1" aria-labelledby="editLearningMaterialModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editLearningMaterialModalLabel">Edit Learning Material</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="editLearningMaterialForm" method="post" enctype="multipart/form-data">
                    <input type="hidden" id="editMaterialId" name="editMaterialId">
                    <div class="mb-3">
                        <label for="editMaterialTitle" class="form-label">Title</label>
                        <input type="text" class="form-control" id="editMaterialTitle" name="editMaterialTitle" required>
                    </div>
                    <div class="mb-3">
                        <label for="editMaterialDescription" class="form-label">Description</label>
                        <textarea class="form-control" id="editMaterialDescription" name="editMaterialDescription" rows="3"></textarea>
                    </div>
                    <div class="mb-3">
                        <label for="editMaterialFile" class="form-label">File</label>
                        <input type="file" class="form-control" id="editMaterialFile" name="editMaterialFile">
                        <small class="form-text text-muted">Current file: <span id="currentMaterialFile"></span></small>
                    </div>
                    <button type="submit" class="btn btn-primary" name="editLearningMaterial">Save changes</button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
function editLearningMaterial(id, title, description, file) {
    document.getElementById('editMaterialId').value = id;
    document.getElementById('editMaterialTitle').value = title;
    document.getElementById('editMaterialDescription').value = description;
    document.getElementById('currentMaterialFile').textContent = file ? file : 'No file currently';
    
    var modal = new bootstrap.Modal(document.getElementById('editLearningMaterialModal'));
    modal.show();
}
</script>

<!-- Toast Container Success -->
<div class="position-fixed bottom-0 end-0 p-3" style="z-index: 11">
    <div id="liveToast" class="toast" role="alert" aria-live="assertive" aria-atomic="true">
        <div class="toast-body text-bg-primary">
            <?php echo $toastMessage; ?>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        var toastMessage = "<?php echo $toastMessage; ?>";
        if (toastMessage) {
            var toastElement = document.getElementById('liveToast');
            var toast = new bootstrap.Toast(toastElement, {
                delay: 3000
            });
            toast.show();
        }
    });
</script>

<script src="../assets/js/showInputMaterials.js"></script>

<style>
    a {
        text-decoration: none;
        cursor: pointer;
    }

    .course-header {
        text-align: center;
    }

    .course-header img {
        object-fit: cover;
        height: 200px;
    }

    .course-name {
        font-size: 1.8rem;
        font-weight: bold;
        margin-bottom: 0.5rem;
    }

    .course-description {
        color: #6c757d;
        margin-bottom: 1rem;
    }

    @media (max-width: 768px) {
         .course-header img {
            max-width: 100%;
            height: auto;
        }

        .course-name {
            font-size: 1.5rem;
        }

       .add-buttons{
        flex-direction: column;
       }

       .add-buttons .btn{
        padding: 10px 20px;
       }
    }

            .btn-primary, .bg-primary {
        background-color: #0f6fc5 !important;
        border-color: #0f6fc5 !important;
        }
        .btn-outline-primary {
            color: #0f6fc5 !important;
            border-color: #0f6fc5 !important;
        }
        .btn-outline-primary:hover {
            background-color: #0f6fc5 !important;
            color: #ffffff !important;
        }
        .text-primary {
            color: #0f6fc5 !important;
        }
</style>

<script>
function editContent(id, type, title, description, file) {
    document.getElementById('editContentId').value = id;
    document.getElementById('editContentType').value = type;
    document.getElementById('editContentTitle').value = title;
    document.getElementById('editContentDescription').value = description;
    document.getElementById('currentFile').textContent = file ? file : 'No file currently';
    
    // Show/hide file input based on content type
    var fileInputContainer = document.getElementById('fileInputContainer');
    if (type === 'Material' || type === 'Task Sheet') {
        fileInputContainer.style.display = 'block';
    } else {
        fileInputContainer.style.display = 'none';
    }
    
    var modal = new bootstrap.Modal(document.getElementById('editContentModal'));
    modal.show();
}
</script>

<!-- Modal for editing content -->
<div class="modal fade" id="editContentModal" tabindex="-1" aria-labelledby="editContentModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editContentModalLabel">Edit Content</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="editContentForm" method="post" enctype="multipart/form-data">
                    <input type="hidden" id="editContentId" name="editContentId">
                    <input type="hidden" id="editContentType" name="editContentType">
                    <div class="mb-3">
                        <label for="editContentTitle" class="form-label">Title</label>
                        <input type="text" class="form-control" id="editContentTitle" name="editContentTitle" required>
                    </div>
                    <div class="mb-3">
                        <label for="editContentDescription" class="form-label">Description</label>
                        <textarea class="form-control" id="editContentDescription" name="editContentDescription" rows="3"></textarea>
                    </div>
                    <div id="fileInputContainer" class="mb-3">
                        <label for="editContentFile" class="form-label">File</label>
                        <input type="file" class="form-control" id="editContentFile" name="editContentFile">
                        <small class="form-text text-muted">Current file: <span id="currentFile"></span></small>
                    </div>
                    <button type="submit" class="btn btn-primary" name="editContent">Save changes</button>
                </form>
            </div>
        </div>
    </div>
</div>

<?php
    }
}
?>



