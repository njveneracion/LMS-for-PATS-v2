<?php
if (isset($_POST['save'])) {
    $id = $_POST['id'];
    $question = $_POST['question'];
    $answer = $_POST['answer'];

    if ($id) {
        // Update FAQ
        $sql = "UPDATE faqs SET question='$question', answer='$answer' WHERE id=$id";
    } else {
        // Add new FAQ
        $sql = "INSERT INTO faqs (question, answer) VALUES ('$question', '$answer')";
    }

    $result = $connect->query($sql);

    if ($result) {
        echo '<div class="alert alert-success alert-dismissible fade show container mt-3" role="alert">
            FAQ saved successfully!
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>';
    } else {
        echo '<div class="alert alert-danger alert-dismissible fade show container mt-3" role="alert">Failed to save FAQ!
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>';
    }
}

if (isset($_POST['delete'])) {
    $id = $_POST['delete'];
    $sql = "DELETE FROM faqs WHERE id=$id";
    $result = $connect->query($sql);

    if ($result) {
        echo '<div class="alert alert-success alert-dismissible fade show container mt-3" role="alert">
            FAQ deleted successfully!
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>';
    } else {
        echo '<div class="alert alert-danger alert-dismissible fade show container mt-3" role="alert">Failed to delete FAQ!
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>';
    }
}

// Fetch FAQs
$sql = "SELECT * FROM faqs";
$faqs = $connect->query($sql);
?>

<div class="container mt-5">
    <h2 class="text-center mb-4">Add/Edit FAQ</h2>
    <form method="POST" class="bg-light p-5 rounded shadow-sm">
        <input type="hidden" name="id" id="faqId">
        <div class="form-group mb-3">
            <label for="question" class="form-label">Question</label>
            <input class="form-control" type="text" name="question" id="question" required>
        </div>
        <div class="form-group mb-3">
            <label for="answer" class="form-label">Answer</label>
            <textarea class="form-control" name="answer" id="answer" rows="4" required></textarea>
        </div>
        <button type="submit" class="btn btn-primary btn-block" name="save">Save</button>
    </form>

    <div class="mt-5">
        <h3 class="text-center mb-4">All FAQs</h3>
        <?php if ($faqs->num_rows > 0): ?>
            <div class="list-group">
                <?php while($row = $faqs->fetch_assoc()): ?>
                    <div class="list-group-item d-flex justify-content-between align-items-center">
                        <div>
                            <p class="mb-1"><strong><?php echo $row['question']; ?></strong></p>
                            <p><?php echo $row['answer']; ?></p>
                        </div>
                        <div class="d-flex gap-1">
                            <button class="btn btn-sm btn-warning" data-id="<?php echo $row['id']; ?>" data-question="<?php echo $row['question']; ?>" data-answer="<?php echo $row['answer']; ?>">Edit</button>
                            <form method="POST">
                                <input type="hidden" name="delete" value="<?php echo $row['id']; ?>">
                                <button type="submit" class="btn btn-sm btn-danger">Delete</button>
                            </form>
                        </div>
                    </div>
                <?php endwhile; ?>
            </div>
        <?php else: ?>
            <p class="text-center">No FAQs found.</p>
        <?php endif; ?>
    </div>
</div>

<!-- Edit FAQ Modal -->
<div class="modal fade" id="editFaqModal" tabindex="-1" aria-labelledby="editFaqModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editFaqModalLabel">Edit FAQ</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="editFaqForm" method="POST">
                    <input type="hidden" name="id" id="editFaqId">
                    <div class="form-group mb-3">
                        <label for="editQuestion" class="form-label">Question</label>
                        <input class="form-control" type="text" name="question" id="editQuestion" required>
                    </div>
                    <div class="form-group mb-3">
                        <label for="editAnswer" class="form-label">Answer</label>
                        <textarea class="form-control" name="answer" id="editAnswer" rows="4" required></textarea>
                    </div>
                    <button type="submit" class="btn btn-primary btn-block" name="save">Update</button>
                </form>
            </div>
        </div>
    </div>
</div>



<script>
    document.addEventListener('DOMContentLoaded', function() {
        const editButtons = document.querySelectorAll('.btn-warning');
        editButtons.forEach(button => {
            button.addEventListener('click', function(event) {
                const id = this.getAttribute('data-id');
                const question = this.getAttribute('data-question');
                const answer = this.getAttribute('data-answer');

                document.getElementById('editFaqId').value = id;
                document.getElementById('editQuestion').value = question;
                document.getElementById('editAnswer').value = answer;

                const editModal = new bootstrap.Modal(document.getElementById('editFaqModal'));
                editModal.show();
            });
        });
    });
</script>