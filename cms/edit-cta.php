<?php
    if (isset($_POST['update'])){
        $heading = $_POST['heading'];
        $subheading = $_POST['subheading'];
        $button_text = $_POST['button_text'];
      
         // Check if the CTA content already exists
        $sql = "SELECT COUNT(*) as count FROM cta_content WHERE id=1";
        $result = $connect->query($sql);
        $row = $result->fetch_assoc();

        if ($row['count'] > 0) {
            // Update existing row
            $sql = "UPDATE cta_content SET heading='$heading', subheading='$subheading', button_text='$button_text' WHERE id=1";
        } else {
            // Insert new row
            $sql = "INSERT INTO cta_content (id, heading, subheading, button_text) VALUES (1, '$heading', '$subheading', '$button_text')";
        }

        if ($connect->query($sql) === TRUE) {
            echo "<div class='alert alert-success alert-dismissible fade show container' role='alert'>CTA content updated successfully!
                <button type='button' class='btn-close' data-bs-dismiss='alert' aria-label='Close'></button>
            </div>";
        } else {
            echo "<div class='alert alert-danger alert-dismissible fade show' role='alert'>Error updating CTA content: " . $connect->error . "
                <button type='button' class='btn-close' data-bs-dismiss='alert' aria-label='Close'></button>
            </div>";
        }
    }

    // Fetch CTA content for display
    $sql = "SELECT heading, subheading, button_text FROM cta_content WHERE id=1";
    $result = mysqli_query($connect, $sql);
    $cta = mysqli_fetch_assoc($result);

    // Set default values if no data is found
    if (!$cta) {
        $cta = [
            'heading' => 'Ready to Enhance Your Skills Online?',
            'subheading' => 'Enroll now in our TESDA-certified online courses and develop 21st century skills.',
            'button_text' => 'Start Your Online Learning Journey'
        ];
    }
?>


<div class="container mt-5">
    <h2 class="text-center mb-4">Edit CTA Content</h2>
    <form method="post">
        <div class="mb-3">
            <label for="heading" class="form-label">Heading:</label>
            <input type="text" class="form-control" name="heading" id="heading" value="<?= htmlspecialchars($cta['heading']) ?>" required oninput="updatePreview()">
        </div>
        <div class="mb-3">
            <label for="subheading" class="form-label">Subheading:</label>
            <textarea class="form-control" name="subheading" id="subheading" rows="3" required oninput="updatePreview()"><?= htmlspecialchars($cta['subheading']) ?></textarea>
        </div>
        <div class="mb-3">
            <label for="button_text" class="form-label">Button Text:</label>
            <input type="text" class="form-control" name="button_text" id="button_text" value="<?= htmlspecialchars($cta['button_text']) ?>" required oninput="updatePreview()">
        </div>
        <button type="submit" name="update" class="btn btn-primary">Update CTA</button>
    </form>
</div>

<div class="container mt-5">
    <h2 class="text-center mb-4">Live Preview</h2>
    <!-- CTA Section -->
    <section class="cta-section text-center">
        <h2 class="fw-bold mb-4" id="preview-heading"><?= htmlspecialchars($cta['heading']) ?></h2>
        <p class="lead mb-4" id="preview-subheading"><?= htmlspecialchars($cta['subheading']) ?></p>
        <a href="../auth/register.php" class="btn btn-primary button-primary btn-lg" id="preview-button"><?= htmlspecialchars($cta['button_text']) ?></a>
    </section>
</div>

<script>
    function updatePreview() {
        document.getElementById('preview-heading').innerText = document.getElementById('heading').value;
        document.getElementById('preview-subheading').innerText = document.getElementById('subheading').value;
        document.getElementById('preview-button').innerText = document.getElementById('button_text').value;
    }
</script>