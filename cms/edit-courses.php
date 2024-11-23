<?php
    if (isset($_POST['add_header'])) {
        $header = $_POST['header'];

        $sql = "UPDATE cms_courses SET course_header='$header' WHERE id=1"; // Assuming you have a specific row for the header
        $result = mysqli_query($connect, $sql);

        if ($result) {
            echo '<div class="alert alert-success alert-dismissible fade show" role="alert">
                Header updated successfully!
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>';
        } else {
            echo '<div class="alert alert-danger alert-dismissible fade show" role="alert">Failed to update header!
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>';
        }
    }

    if (isset($_POST['add_courses'])) {
        $title = $_POST['title'];
        $description = $_POST['description'];

        // Handle file upload
        if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
            $target_dir = "../uploads/course-photo/";
            $target_file = $target_dir . basename($_FILES["image"]["name"]);
            move_uploaded_file($_FILES["image"]["tmp_name"], $target_file);
            $image = $target_file;
        } else {
            $image = '';
        }

        $sql = "INSERT INTO cms_courses (title, description, image) VALUES ('$title', '$description', '$image')";
        $result = mysqli_query($connect, $sql);

        if ($result) {
            echo '<div class="alert alert-success alert-dismissible fade show" role="alert">
                Course added successfully!
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>';
        } else {
            echo '<div class="alert alert-danger alert-dismissible fade show" role="alert">Failed to add course!
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>';
        }
    }

    if (isset($_POST['delete_course'])) {
        $course_id = $_POST['course_id'];

        $sql = "DELETE FROM cms_courses WHERE id='$course_id'";
        $result = mysqli_query($connect, $sql);

        if ($result) {
            echo '<div class="alert alert-success alert-dismissible fade show" role="alert">
                Course deleted successfully!
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>';
        } else {
            echo '<div class="alert alert-danger alert-dismissible fade show" role="alert">Failed to delete course!
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>';
        }
    }

    // Fetch all courses
    $sql = "SELECT * FROM cms_courses";
    $result = mysqli_query($connect, $sql);
    $row = mysqli_fetch_assoc($result);
    $header = $row['course_header'];
?>

<div class="container mt-5">
    <div class="row justify-content-center">
        <!-- Header Form -->
        <div class="col-md-6">
            <div class="card">
                <div class="card-header text-center">
                    <h2>Edit Course Header</h2>
                </div>
                <div class="card-body">
                    <form method="POST" id="header-form">
                        <div class="form-group">
                            <label for="header">Course Header</label>
                            <input type="text" class="form-control mb-1" id="header" name="header" placeholder="Enter course header">
                        </div>
                        <button type="submit" class="btn btn-primary btn-block mt-3" name="add_header">Update Header</button>
                    </form>
                </div>
            </div>
        </div>

        <!-- Course Form -->
        <div class="col-md-6">
            <div class="card">
                <div class="card-header text-center">
                    <h2>Edit Course</h2>
                </div>
                <div class="card-body">
                    <form method="POST" enctype="multipart/form-data" id="course-form">
                        <div class="form-group">
                            <label for="image">Course Image</label>
                            <input type="file" class="form-control" id="image" name="image">
                        </div>
                        <div class="form-group">
                            <label for="title">Course Title</label>
                            <input type="text" class="form-control mb-1" id="title" name="title" placeholder="Enter course title">
                        </div>
                        <div class="form-group">
                            <label for="description">Course Description</label>
                            <input type="text" class="form-control mb-1" id="description" name="description" placeholder="Enter course description">
                        </div>
                       
                        <button type="submit" class="btn btn-primary btn-block mt-3" name="add_courses">Add Course</button>
                    </form>
                </div>
            </div>
        </div>

        <!-- Live Preview -->
        <div class="col-md-6 mt-5">
            <div class="card">
                <div class="card-header text-center">
                    <h2>Live Preview</h2>
                </div>
                <div class="card-body">
                    <h2 class="fw-bold text-center mb-4" id="preview-header">Course Header</h2>
                    <div class="card h-100">
                        <img id="preview-image" src="" class="card-img-top" alt="Course Image">
                        <div class="card-body">
                            <h5 class="card-title" id="preview-title">Course Title</h5>
                            <p class="card-text" id="preview-description">Course Description</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Added Courses Section -->
    <div class="row mt-5">
        <h2 class="fw-bold text-center mb-4"><?= $header; ?></h2>
        <div class="row row-cols-1 row-cols-md-3 g-4">
            <?php if ($result->num_rows > 0): ?>
                <?php while($row = $result->fetch_assoc()): ?>
                    <div class="col" data-aos="zoom-in">
                        <div class="card h-100">
                            <img src="<?= htmlspecialchars($row['image']) ?>" class="card-img-top default-image-size" alt="image">
                            <div class="card-body">
                                <h5 class="card-title"><?= htmlspecialchars($row['title']) ?></h5>
                                <p class="card-text"><?= htmlspecialchars($row['description']) ?></p>
                                <form method="POST" class="mt-2">
                                    <input type="hidden" name="course_id" value="<?= $row['id'] ?>">
                                    <button type="submit" class="btn btn-danger btn-block btn-sm" name="delete_course">Delete</button>
                                </form>
                            </div>
                        </div>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <p>No courses available.</p>
            <?php endif; ?>
        </div>
    </div>
</div>

<style>
    .default-image-size {
        width: 100%;
        height: 200px;
        object-fit: cover;
    }
</style>

<script>
document.getElementById('header').addEventListener('input', function() {
    document.getElementById('preview-header').textContent = this.value;
});

document.getElementById('title').addEventListener('input', function() {
    document.getElementById('preview-title').textContent = this.value;
});

document.getElementById('description').addEventListener('input', function() {
    document.getElementById('preview-description').textContent = this.value;
});

document.getElementById('image').addEventListener('change', function(event) {
    const reader = new FileReader();
    reader.onload = function() {
        document.getElementById('preview-image').src = reader.result;
    }
    reader.readAsDataURL(event.target.files[0]);
});
</script>