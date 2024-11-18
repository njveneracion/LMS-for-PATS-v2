<?php
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Handle file upload
    if (isset($_FILES['logo']) && $_FILES['logo']['error'] == 0) {
        $logo = '../uploads/logo/' . basename($_FILES['logo']['name']);
        move_uploaded_file($_FILES['logo']['tmp_name'], $logo);
    } else {
        $logo = $_POST['current_logo'];
    }

    // Check if a row exists
    $sql = "SELECT COUNT(*) as count FROM header WHERE id=1";
    $result = $connect->query($sql);
    $row = $result->fetch_assoc();

    if ($row['count'] > 0) {
        // Update existing row
        $sql = "UPDATE header SET logo='$logo' WHERE id=1";
    } else {
        // Insert new row
        $sql = "INSERT INTO header (id, logo) VALUES (1, '$logo')";
    }

    if ($connect->query($sql) === TRUE) {
        echo "<div class='alert alert-success alert-dismissible fade show container' role='alert'>Logo updated successfully!
            <button type='button' class='btn-close' data-bs-dismiss='alert' aria-label='Close'></button>
        </div>";
    } else {
        echo "<div class='alert alert-danger alert-dismissible fade show' role='alert'>Error updating logo: " . $connect->error . "
            <button type='button' class='btn-close' data-bs-dismiss='alert' aria-label='Close'></button>
        </div>";
    }
}

$sql = "SELECT * FROM header WHERE id=1";
$result = $connect->query($sql);
$row = $result->fetch_assoc();
?>

<div class="container mt-5">
    <h1 class="mb-4">Edit Header</h1>
    <form method="post" action="" enctype="multipart/form-data" class="mb-5">
        <div class="form-group">
            <label for="logo" class="form-label">Logo</label>
            <input type="file" class="form-control-file form-control mb-2" id="logo" name="logo" onchange="updatePreviewImage()">
            <input type="hidden" name="current_logo" value="<?php echo isset($row['logo']) ? $row['logo'] : ''; ?>">
        </div>
        <button type="submit" class="btn btn-primary">Update</button>
    </form>

    <h2>Preview</h2>
    <header>
        <nav class="navbar navbar-expand-lg bg-body-secondary">
            <div class="container">
                <a class="navbar-brand" href="index.php">
                    <img id="preview-logo" src="<?php echo isset($row['logo']) ? $row['logo'] : ''; ?>" alt="Pats Logo" style="height: 60px; width: 100%">
                </a>
                <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
                    <span class="navbar-toggler-icon"></span>
                </button>

                <div class="collapse navbar-collapse justify-content-end" id="navbarSupportedContent">
                    <ul class="navbar-nav mb-2 mb-lg-0">
                        <li class="nav-item">
                            <a class="nav-link active" href="#home" data-section="home">Home</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="#courses" data-section="courses">Courses</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="#about" data-section="about">About</a>
                        </li>
                        <li class="nav-item ms-3 login-btn-toggle">
                            <a href="../auth/login.php" class="btn btn-primary button-primary px-4">Login</a>
                        </li>
                    </ul> 
                </div>
            </div>
        </nav>
    </header>
</div>

<script>
function updatePreviewImage() {
    const file = document.getElementById('logo').files[0];
    const reader = new FileReader();
    reader.onloadend = function() {
        document.getElementById('preview-logo').src = reader.result;
    }
    if (file) {
        reader.readAsDataURL(file);
    }
}
</script>