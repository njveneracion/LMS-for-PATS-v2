<?php
// Fetch About Us content
$sql = "SELECT content FROM about_us LIMIT 1";
$about_us_result = $connect->query($sql);
$about_us = $about_us_result->fetch_assoc();

// Fetch Contact Information
$sql = "SELECT email, phone, address FROM contact_info LIMIT 1";
$contact_info_result = $connect->query($sql);
$contact_info = $contact_info_result->fetch_assoc();

// Fetch Social Links
$sql = "SELECT platform, url FROM social_links";
$social_links_result = $connect->query($sql);

// Fetch Footer Text
$sql = "SELECT content FROM footer_text LIMIT 1";
$footer_text_result = $connect->query($sql);
$footer_text = $footer_text_result->fetch_assoc();
?>

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
            <div class="col-lg-2 col-md-6 mb-4 mb-md-0">
                <h5 class="text-uppercase fw-bold mb-4 modified-text-primary">Follow Us</h5>
                <?php while($row = $social_links_result->fetch_assoc()): ?>
                    <a href="<?php echo $row['url']; ?>" class="me-4 btn btn-primary button-primary rounded"><i class="fab fa-<?php echo strtolower($row['platform']); ?> modified-text-color"></i></a>
                <?php endwhile; ?>
            </div>
        </div>
    </div>
    <div class="text-center p-3 text-white modified-bg-primary">
         <?php echo $footer_text['content']; ?>
       
    </div>
</footer>