<?php
include '../config/db.php';

if (isset($_POST['update'])) {
    $title = $_POST['title'];
    $subtitle = $_POST['subtitle'];
    $button_text = $_POST['button_text'];

    // Handle file upload
    if (isset($_FILES['background_image']) && $_FILES['background_image']['error'] == 0) {
        $target_dir = "../uploads/hero-bg/";
        $target_file = $target_dir . basename($_FILES["background_image"]["name"]);
        move_uploaded_file($_FILES["background_image"]["tmp_name"], $target_file);
        $background_image = $target_file;
    } else {
        $background_image = $_POST['existing_background_image'];
    }

    $sql = "UPDATE hero_section SET title = '$title', subtitle = '$subtitle', button_text = '$button_text', background_img = '$background_image' WHERE id = 1";
    $result = mysqli_query($connect, $sql);

    if ($result) {
        echo '<div class="alert alert-success alert-dismissible fade show" role="alert">
            Hero section updated successfully!
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>';
    } else {
        echo '<div class="alert alert-success alert-dismissible fade show" role="alert">Failed to update hero section!
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>';
    }
}

// Fetch existing data
$sql = "SELECT * FROM hero_section WHERE id = 1";
$result = mysqli_query($connect, $sql);
$row = mysqli_fetch_assoc($result);
$title = $row['title'];
$subtitle = $row['subtitle'];
$button_text = $row['button_text'];
$background_image = $row['background_img'];
?>

<style>
    .hero-section {
        background: linear-gradient(rgba(0,0,0,0.6), rgba(0,0,0,0.6)), url('<?php echo htmlspecialchars($background_image); ?>');
        background-size: cover;
        background-position: center;
        height: 100vh;
        color: white;
        display: grid;
        place-items: center;

    }
</style>

<div class="container mt-5">
    <h2 class="mb-4">Edit Hero Section</h2>
    <form method="post" action="" enctype="multipart/form-data">
        <div class="form-group">
            <label for="title">Title:</label>
            <input type="text" class="form-control" id="title" name="title" oninput="updatePreview()" value="<?php echo htmlspecialchars($title); ?>">
        </div>
        <div class="form-group">
            <label for="subtitle">Subtitle:</label>
            <textarea class="form-control" id="subtitle" name="subtitle" oninput="updatePreview()"><?php echo htmlspecialchars($subtitle); ?></textarea>
        </div>
        <div class="form-group">
            <label for="button_text">Button Text:</label>
            <input type="text" class="form-control" id="button_text" name="button_text" value="<?php echo htmlspecialchars($button_text); ?>" oninput="updatePreview()">
        </div>
        <div class="form-group mt-3">
            <label for="background_image">Background Image:</label>
            <input type="file" class="form-control-file" id="background_image" name="background_image" onchange="updatePreviewImage()">
            <input type="hidden" name="existing_background_image" value="<?php echo htmlspecialchars($background_image); ?>">
        </div>
        <button type="submit" class="btn btn-primary mt-3" name="update">Update</button>
    </form>

    <h2 class="mt-5">Preview</h2>
    <section class="hero-section text-center mt-4" id="home">
        <div class="container">
            <h1 class="display-4 fw-bold" id="preview-title"><?php echo htmlspecialchars($title); ?></h1>
            <p class="lead" id="preview-subtitle"><?php echo htmlspecialchars($subtitle); ?></p>
            <a class="btn btn-primary button-primary btn-lg mt-3" id="preview-button"><?php echo htmlspecialchars($button_text); ?></a>
        </div>
    </section>
</div>


<script>
function updatePreview() {
    document.getElementById('preview-title').innerText = document.getElementById('title').value;
    document.getElementById('preview-subtitle').innerText = document.getElementById('subtitle').value;
    document.getElementById('preview-button').innerText = document.getElementById('button_text').value;
}

function updatePreviewImage() {
    const file = document.getElementById('background_image').files[0];
    const reader = new FileReader();
    reader.onloadend = function() {
        document.getElementById('home').style.backgroundImage = 'url(' + reader.result + ')';
    }
    if (file) {
        reader.readAsDataURL(file);
    }
}
</script>

<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>