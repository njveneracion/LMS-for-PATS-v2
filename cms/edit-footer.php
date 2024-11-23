<?php
if (isset($_POST['save'])) {
    $about_us_content = $_POST['about_us'];
    $email = $_POST['email'];
    $phone = $_POST['phone'];
    $address = $_POST['address'];
    $footer_content = $_POST['footer_content'];

    // Check if About Us content exists
    $sql = "SELECT COUNT(*) as count FROM about_us";
    $result = $connect->query($sql);
    $row = $result->fetch_assoc();
    if ($row['count'] == 0) {
        // Insert new About Us content
        $sql = "INSERT INTO about_us (content) VALUES ('$about_us_content')";
    } else {
        // Update existing About Us content
        $sql = "UPDATE about_us SET content='$about_us_content' WHERE id=1";
    }
    $connect->query($sql);

    // Check if Contact Information exists
    $sql = "SELECT COUNT(*) as count FROM contact_info";
    $result = $connect->query($sql);
    $row = $result->fetch_assoc();
    if ($row['count'] == 0) {
        // Insert new Contact Information
        $sql = "INSERT INTO contact_info (email, phone, address) VALUES ('$email', '$phone', '$address')";
    } else {
        // Update existing Contact Information
        $sql = "UPDATE contact_info SET email='$email', phone='$phone', address='$address' WHERE id=1";
    }
    $connect->query($sql);

    // Update or Insert Social Links
    foreach ($_POST as $key => $value) {
        if (strpos($key, 'platform_') === 0) {
            $id = str_replace('platform_', '', $key);
            $platform = $_POST["platform_$id"];
            $url = $_POST["url_$id"];

            // Check if the social link exists by ID
            $sql = "SELECT COUNT(*) as count FROM social_links WHERE id=$id";
            $result = $connect->query($sql);
            $row = $result->fetch_assoc();
            if ($row['count'] == 0) {
                // Insert new social link
                $sql = "INSERT INTO social_links (platform, url) VALUES ('$platform', '$url')";
            } else {
                // Update existing social link
                $sql = "UPDATE social_links SET platform='$platform', url='$url' WHERE id=$id";
            }
            $connect->query($sql);
        }
    }

    // Check if Footer Text exists
    $sql = "SELECT COUNT(*) as count FROM footer_text";
    $result = $connect->query($sql);
    $row = $result->fetch_assoc();
    if ($row['count'] == 0) {
        // Insert new Footer Text
        $sql = "INSERT INTO footer_text (content) VALUES ('$footer_content')";
    } else {
        // Update existing Footer Text
        $sql = "UPDATE footer_text SET content='$footer_content' WHERE id=1";
    }
    $connect->query($sql);

    echo '<div class="alert alert-success alert-dismissible fade show container mt-3" role="alert">
        Footer content updated successfully!
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>';
}

if (isset($_POST['delete_social_link'])) {
    $id = $_POST['delete_social_link'];
    $sql = "DELETE FROM social_links WHERE id=$id";
    $connect->query($sql);

    echo '<div class="alert alert-success alert-dismissible fade show container mt-3" role="alert">
        Social link deleted successfully!
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>';
}

// Fetch About Us content
$sql = "SELECT content FROM about_us LIMIT 1";
$about_us_result = $connect->query($sql);
$about_us = $about_us_result->fetch_assoc();

// Fetch Contact Information
$sql = "SELECT email, phone, address FROM contact_info LIMIT 1";
$contact_info_result = $connect->query($sql);
$contact_info = $contact_info_result->fetch_assoc();

// Fetch Social Links
$sql = "SELECT * FROM social_links";
$social_links_result = $connect->query($sql);

// Fetch Footer Text
$sql = "SELECT content FROM footer_text LIMIT 1";
$footer_text_result = $connect->query($sql);
$footer_text = $footer_text_result->fetch_assoc();
?>

<div class="container mt-5">
    <h2 class="text-center mb-4">Edit Footer Content</h2>
    <form method="POST" class="bg-light p-5 rounded shadow-sm">
        <h5 class="text-uppercase fw-bold mb-4 modified-text-primary">About Us</h5>
        <div class="form-group mb-3">
            <textarea class="form-control" name="about_us" rows="4" required><?php echo $about_us['content']; ?></textarea>
        </div>
        
        <h5 class="text-uppercase fw-bold mb-4 modified-text-primary">Contact Information</h5>
        <div class="form-group mb-3">
            <label for="email" class="form-label">Email</label>
            <input class="form-control" type="email" name="email" value="<?php echo $contact_info['email']; ?>" required>
        </div>
        <div class="form-group mb-3">
            <label for="phone" class="form-label">Phone</label>
            <input class="form-control" type="text" name="phone" value="<?php echo $contact_info['phone']; ?>" required>
        </div>
        <div class="form-group mb-3">
            <label for="address" class="form-label">Address</label>
            <textarea class="form-control" name="address" rows="2" required><?php echo $contact_info['address']; ?></textarea>
        </div>
        
        <h5 class="text-uppercase fw-bold mb-4 modified-text-primary">Social Links</h5>
        <div id="social-links-container">
            <?php
            $social_links_result->data_seek(0); // Reset result pointer
            while($row = $social_links_result->fetch_assoc()): ?>
                <div class="form-group mb-3 d-flex align-items-center">
                    <div class="flex-grow-1">
                        <label for="platform_<?php echo $row['id']; ?>" class="form-label">Platform</label>
                        <select class="form-control mb-2" name="platform_<?php echo $row['id']; ?>" id="platform_<?php echo $row['id']; ?>" required>
                            <option value="Facebook" <?php echo $row['platform'] == 'Facebook' ? 'selected' : ''; ?>>Facebook</option>
                            <option value="Twitter" <?php echo $row['platform'] == 'Twitter' ? 'selected' : ''; ?>>Twitter</option>
                            <option value="Instagram" <?php echo $row['platform'] == 'Instagram' ? 'selected' : ''; ?>>Instagram</option>
                            <option value="LinkedIn" <?php echo $row['platform'] == 'LinkedIn' ? 'selected' : ''; ?>>LinkedIn</option>
                            <!-- Add more platforms as needed -->
                        </select>
                        <label for="url_<?php echo $row['id']; ?>" class="form-label">URL</label>
                        <div class="d-flex">
                            <input class="form-control me-2" type="url" name="url_<?php echo $row['id']; ?>" value="<?php echo $row['url']; ?>" required>
                            <button type="button" class="btn btn-danger btn-sm" data-bs-toggle="modal" data-bs-target="#deleteModal" data-id="<?php echo $row['id']; ?>">Delete</button>
                        </div>
                    </div>
                </div>
            <?php endwhile; ?>
        </div>
        <button type="button" class="btn btn-secondary mb-3" id="add-social-link">Add Social Link</button>
        
        <h5 class="text-uppercase fw-bold mb-4 modified-text-primary">Footer Text</h5>
        <div class="form-group mb-3">
            <textarea class="form-control" name="footer_content" rows="2" required><?php echo $footer_text['content']; ?></textarea>
        </div>
        
        <button type="submit" class="btn btn-primary btn-block" name="save">Save</button>
    </form>
</div>

<!-- Delete Confirmation Modal -->
<div class="modal fade" id="deleteModal" tabindex="-1" aria-labelledby="deleteModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="deleteModalLabel">Confirm Delete</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                Are you sure you want to delete this social link?
            </div>
            <div class="modal-footer">
                <form method="POST" id="deleteForm">
                    <input type="hidden" name="delete_social_link" id="deleteSocialLinkId">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-danger">Delete</button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const deleteModal = document.getElementById('deleteModal');
        deleteModal.addEventListener('show.bs.modal', function(event) {
            const button = event.relatedTarget;
            const id = button.getAttribute('data-id');
            const deleteSocialLinkId = document.getElementById('deleteSocialLinkId');
            deleteSocialLinkId.value = id;
        });

        const socialLinksContainer = document.getElementById('social-links-container');
        const addSocialLinkButton = document.getElementById('add-social-link');

        addSocialLinkButton.addEventListener('click', function() {
            const newId = Date.now(); // Use a unique ID for new entries
            const newSocialLink = `
                <div class="form-group mb-3 d-flex align-items-center">
                    <div class="flex-grow-1">
                        <label for="platform_${newId}" class="form-label">Platform</label>
                        <select class="form-control mb-2" name="platform_${newId}" id="platform_${newId}" required>
                            <option value="Facebook">Facebook</option>
                            <option value="Twitter">Twitter</option>
                            <option value="Instagram">Instagram</option>
                            <option value="LinkedIn">LinkedIn</option>
                            <!-- Add more platforms as needed -->
                        </select>
                        <label for="url_${newId}" class="form-label">URL</label>
                        <div class="d-flex">
                            <input class="form-control me-2" type="url" name="url_${newId}" required>
                            <button type="button" class="btn btn-danger btn-sm remove-social-link">Delete</button>
                        </div>
                    </div>
                </div>
            `;
            socialLinksContainer.insertAdjacentHTML('beforeend', newSocialLink);

            // Add event listener to the new delete button
            const newDeleteButton = socialLinksContainer.querySelector(`.remove-social-link:last-child`);
            newDeleteButton.addEventListener('click', function() {
                newDeleteButton.closest('.form-group').remove();
            });
        });

        // Add event listeners to existing delete buttons
        const deleteButtons = socialLinksContainer.querySelectorAll('.remove-social-link');
        deleteButtons.forEach(button => {
            button.addEventListener('click', function() {
                button.closest('.form-group').remove();
            });
        });
    });
</script>

<footer class="bg-body-secondary text-center text-lg-start text-dark mt-5">
    <div class="container p-4">
        <div class="row">
            <div class="col-lg-4 col-md-6 mb-4 mb-md-0">
                <h5 class="text-uppercase fw-bold mb-4 modified-text-primary">About Us</h5>
                <p>
                    <?php echo $about_us['content']; ?>
                </p>
            </div>
            
            <div class="col-lg-4 col-md-6 mb-4 mb-md-0">
                <h5 class="text-uppercase fw-bold mb-4 modified-text-primary">Contact Information</h5>
                <p><i class="fa-solid fa-envelope me-2 modified-text-primary"></i><?php echo $contact_info['email']; ?></p>
                <p><i class="fa-solid fa-phone me-2 modified-text-primary"></i><?php echo $contact_info['phone']; ?></p>
                <p><i class="fa-solid fa-location-dot me-2 modified-text-primary"></i><?php echo $contact_info['address']; ?></p>
            </div>
            <div class="col-lg-4 col-md-6 mb-4 mb-md-0">
                <h5 class="text-uppercase fw-bold mb-4 modified-text-primary">Follow Us</h5>
                <?php
                    $sqlFollow = "SELECT platform, url FROM social_links";
                    $social_links_result = $connect->query($sqlFollow);
                ?>
                <?php if ($social_links_result->num_rows > 0): ?>
                    <?php while($row = $social_links_result->fetch_assoc()): ?>
                        <a href="<?php echo $row['url']; ?>" class="me-4 btn btn-primary button-primary"><i class="fab fa-<?php echo strtolower($row['platform']); ?> modified-text-color"></i></a>
                    <?php endwhile; ?>
                <?php else: ?>
                    <p>No social links found.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <div class="text-center p-3 text-white modified-bg-primary">
        <?php echo $footer_text['content']; ?>
    </div>
</footer>