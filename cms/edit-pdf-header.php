<?php
include '../config/db.php'; // Include your database connection file

$message = '';
$message_type = '';

if (isset($_POST['upload'])) {
    $target_dir = "../uploads/pdf-headers/";
    $target_file = $target_dir . basename($_FILES["header_image"]["name"]);
    $uploadOk = 1;
    $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));

    // Check if image file is a actual image or fake image
    $check = getimagesize($_FILES["header_image"]["tmp_name"]);
    if ($check !== false) {
        $uploadOk = 1;
    } else {
        $message = "File is not an image.";
        $message_type = 'danger';
        $uploadOk = 0;
    }

    // Check file size
    if ($_FILES["header_image"]["size"] > 5000000) { // 5MB
        $message = "Sorry, your file is too large.";
        $message_type = 'danger';
        $uploadOk = 0;
    }

    // Allow certain file formats
    if ($imageFileType != "jpg" && $imageFileType != "png" && $imageFileType != "jpeg" && $imageFileType != "gif") {
        $message = "Sorry, only JPG, JPEG, PNG & GIF files are allowed.";
        $message_type = 'danger';
        $uploadOk = 0;
    }

    // Check if $uploadOk is set to 0 by an error
    if ($uploadOk == 0) {
        if (empty($message)) {
            $message = "Sorry, your file was not uploaded.";
            $message_type = 'danger';
        }
    // if everything is ok, try to upload file
    } else {
        if (move_uploaded_file($_FILES["header_image"]["tmp_name"], $target_file)) {
            $message = "The file ". htmlspecialchars(basename($_FILES["header_image"]["name"])). " has been uploaded.";
            $message_type = 'success';

            // Save file information to the database
            $file_name = basename($_FILES["header_image"]["name"]);
            $file_path = $target_file;

            // Check if the record exists
            $sql_check = "SELECT * FROM pdf_headers WHERE id = 1";
            $result_check = mysqli_query($connect, $sql_check);

            if (mysqli_num_rows($result_check) > 0) {
                // Update the existing record
                $sql = "UPDATE pdf_headers SET file_name = '$file_name', file_path = '$file_path' WHERE id = 1";
            } else {
                // Insert a new record
                $sql = "INSERT INTO pdf_headers (id, file_name, file_path) VALUES (1, '$file_name', '$file_path')";
            }

            if (mysqli_query($connect, $sql)) {
                $message .= " File information saved to the database.";
                $message_type = 'success';
            } else {
                $message = "Error: " . $sql . "<br>" . mysqli_error($connect);
                $message_type = 'danger';
            }
        } else {
            $message = "Sorry, there was an error uploading your file.";
            $message_type = 'danger';
        }
    }
}

// Fetch existing data
$sql = "SELECT * FROM pdf_headers WHERE id = 1";
$result = mysqli_query($connect, $sql);
$row = mysqli_fetch_assoc($result);
$background_image = $row['file_path'] ?? '';

?>

<style>
#preview {
    display: none;
    margin-top: 20px;
    width: 300mm; /* Match the PDF width */
    height: 40mm; /* Match the PDF height */
    object-fit: cover; /* Ensure the image covers the area without distortion */
}
.current-preview {
    margin-top: 20px;
    width: 300mm; /* Match the PDF width */
    height: 40mm; /* Match the PDF height */
    object-fit: cover; /* Ensure the image covers the area without distortion */
}
</style>

<div class="container mt-5">
<a href="main.php?page=generate-reports" class="btn btn-outline-secondary mb-3">Go Back</a>
<h1 class="mb-4">Edit PDF Header</h1>
<?php if (!empty($message)): ?>
    <div class="alert alert-<?php echo $message_type; ?> alert-dismissible fade show" role="alert">
        <?php echo $message; ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
<?php endif; ?>
<form method="post" enctype="multipart/form-data" class="form-inline">
    <div class="form-group mb-2">
        <label for="header_image" class="mr-2">Upload PDF Header Image:</label>
        <input type="file" name="header_image" id="header_image" class="form-control" accept="image/*" required onchange="previewImage(event)">
    </div>
    <button type="submit" name="upload" class="btn btn-primary mb-2 ml-2">Upload</button>
</form>
<h2 class="mt-5">Current Header Image</h2>
<?php if ($background_image): ?>
    <img src="<?php echo htmlspecialchars($background_image); ?>" alt="Current Header Image" class="current-preview">
<?php else: ?>
    <p>No header image uploaded yet.</p>
<?php endif; ?>
<h2 class="mt-5">Live Preview</h2>
<img id="preview" src="#" alt="Image Preview">
</div>

<script>
    function previewImage(event) {
            var reader = new FileReader();
            reader.onload = function() {
                var output = document.getElementById('preview');
                output.src = reader.result;
                output.style.display = 'block';
            }
            reader.readAsDataURL(event.target.files[0]);
    }
</script>