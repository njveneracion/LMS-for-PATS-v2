<?php
include './config/db.php';

// Fetch all records from the skills table
$sql = "SELECT * FROM skills";
$result = mysqli_query($connect, $sql);

// Fetch header and sub-header
$data = mysqli_fetch_assoc($result);
$header = $data['header'];
$sub_header = $data['sub_header'];
?>

<!-- 21st Century Skills Section -->
<section class="py-5 bg-light">
    <div class="container">
        <h2 class="fw-bold text-center mb-5"><?= $header; ?></h2>
        <p class="lead text-center mb-5"><?= $sub_header; ?></p>

        <div class="row g-4">
            <?php
            // Reset the result pointer and fetch all records again
            mysqli_data_seek($result, 0);
            while ($data = mysqli_fetch_assoc($result)) {
                $title = $data['title'];
                $description = $data['description'];
                $icon = $data['icon'];
            ?>
                <div class="col-md-3 col-sm-6" data-aos="fade-up">
                    <div class="card h-100 border-0 shadow-sm hover-card">
                        <div class="card-body text-center">
                            <i class="fas <?= $icon; ?> feature-icon modified-text-primary mb-3" style="font-size: 2.5rem;"></i>
                            <h3 class="h5 card-title"><?= $title; ?></h3>
                            <p class="card-text small"><?= $description; ?></p>
                        </div>
                    </div>
                </div>
            <?php
            }
            ?>
            <?php
                // no data
                if (mysqli_num_rows($result) == 0) {
                    echo '<div class="col-md-12 text-center">No data found</div>';
                }
            ?>
        </div>
    </div>
</section>