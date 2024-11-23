<?php
if (isset($_POST['add'])) {
    $testimonial = $_POST['testimonial'];
    $author = $_POST['author'];

    $sql = "INSERT INTO testimonials (testimonial, author) VALUES ('$testimonial', '$author')";
    $result = mysqli_query($connect, $sql);

    if ($result) {
        echo '<div class="alert alert-success alert-dismissible fade show container mt-3" role="alert">
            Testimonial added successfully!
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>';
    } else {
        echo '<div class="alert alert-danger alert-dismissible fade show container mt-3" role="alert">Failed to add testimonial!
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>';
    }
}

if (isset($_POST['delete'])) {
    $id = $_POST['delete'];
    $sql = "DELETE FROM testimonials WHERE id=$id";
    $result = mysqli_query($connect, $sql);

    if ($result) {
        echo '<div class="alert alert-success alert-dismissible fade show container mt-3" role="alert">
            Testimonial deleted successfully!
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>';
    } else {
        echo '<div class="alert alert-danger alert-dismissible fade show container mt-3" role="alert">Failed to delete testimonial!
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>';
    }
}

if (isset($_POST['update'])) {
    $id = $_POST['id'];
    $testimonial = $_POST['testimonial'];
    $author = $_POST['author'];

    $sql = "UPDATE testimonials SET testimonial='$testimonial', author='$author' WHERE id=$id";
    $result = mysqli_query($connect, $sql);

    if ($result) {
        echo '<div class="alert alert-success alert-dismissible fade show container mt-3" role="alert">
            Testimonial updated successfully!
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>';
    } else {
        echo '<div class="alert alert-danger alert-dismissible fade show container mt-3" role="alert">Failed to update testimonial!
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>';
    }
}

// Fetch testimonials
$sql = "SELECT * FROM testimonials";
$testimonials = $connect->query($sql);
?>

<div class="container mt-5">
    <h2 class="text-center mb-4">Add Testimonial</h2>
    <form method="POST" class="bg-light p-5 rounded shadow-sm">
        <div class="form-group mb-3">
            <label for="testimonial" class="form-label">Testimonial</label>
            <textarea class="form-control" name="testimonial" id="testimonial" rows="4" required></textarea>
        </div>
        <div class="form-group mb-3">
            <label for="author" class="form-label">Author</label>
            <input class="form-control" type="text" name="author" id="author" required>
        </div>
        <button type="submit" class="btn btn-primary btn-block" name="add">Submit</button>
    </form>

    <div class="mt-5">
        <h3 class="text-center mb-4">Live Preview</h3>
        <div class="bg-light p-5 rounded shadow-sm">
            <p id="previewTestimonial" class="card-text"></p>
            <footer id="previewAuthor" class="blockquote-footer"></footer>
        </div>
    </div>

    <div class="mt-5">
        <h3 class="text-center mb-4">All Testimonials</h3>
        <?php if ($testimonials->num_rows > 0): ?>
            <div class="list-group">
                <?php while($row = $testimonials->fetch_assoc()): ?>
                    <div class="list-group-item d-flex justify-content-between align-items-center">
                        <div>
                            <p class="mb-3"><?php echo $row['testimonial']; ?></p>
                            <footer class="blockquote-footer"><?php echo $row['author']; ?></footer>
                        </div>
                        <div class="d-flex gap-1">
                            <button class="btn btn-sm btn-warning" data-id="<?php echo $row['id']; ?>" data-testimonial="<?php echo $row['testimonial']; ?>" data-author="<?php echo $row['author']; ?>">Edit</button>
                            <form method="POST">
                                <input type="hidden" name="delete" value="<?php echo $row['id']; ?>">
                                <button type="submit" class="btn btn-sm btn-danger">Delete</button>
                            </form>
                        </div>
                    </div>
                <?php endwhile; ?>
            </div>
        <?php else: ?>
            <p class="text-center">No testimonials found.</p>
        <?php endif; ?>
    </div>
</div>


<!-- Edit Testimonial Modal -->
<div class="modal fade" id="editTestimonialModal" tabindex="-1" aria-labelledby="editTestimonialModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editTestimonialModalLabel">Edit Testimonial</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="editTestimonialForm" method="POST">
                    <input type="hidden" name="id" id="editTestimonialId">
                    <div class="form-group mb-3">
                        <label for="editTestimonial" class="form-label">Testimonial</label>
                        <textarea class="form-control" name="testimonial" id="editTestimonial" rows="4" required></textarea>
                    </div>
                    <div class="form-group mb-3">
                        <label for="editAuthor" class="form-label">Author</label>
                        <input class="form-control" type="text" name="author" id="editAuthor" required>
                    </div>
                    <button type="submit" class="btn btn-primary btn-block" name="update">Update</button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const testimonialInput = document.getElementById('testimonial');
        const authorInput = document.getElementById('author');
        const previewTestimonial = document.getElementById('previewTestimonial');
        const previewAuthor = document.getElementById('previewAuthor');

        testimonialInput.addEventListener('input', function() {
            previewTestimonial.textContent = testimonialInput.value;
        });

        authorInput.addEventListener('input', function() {
            previewAuthor.textContent = authorInput.value;
        });

        const editButtons = document.querySelectorAll('.btn-warning');
        editButtons.forEach(button => {
            button.addEventListener('click', function(event) {
                const id = this.getAttribute('data-id');
                const testimonial = this.getAttribute('data-testimonial');
                const author = this.getAttribute('data-author');

                document.getElementById('editTestimonialId').value = id;
                document.getElementById('editTestimonial').value = testimonial;
                document.getElementById('editAuthor').value = author;

                const editModal = new bootstrap.Modal(document.getElementById('editTestimonialModal'));
                editModal.show();
            });
        });
    });
</script>