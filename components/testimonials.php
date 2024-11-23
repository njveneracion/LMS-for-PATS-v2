<?php
// Fetch testimonials from the database
$sql = "SELECT * FROM testimonials";
$result = mysqli_query($connect, $sql);
?>

<section class="mb-5">
    <h2 class="fw-bold text-center mb-4">Success Stories from Our Online Learners</h2>
    <div id="testimonialCarousel" class="carousel slide" data-bs-ride="carousel">
        <div class="carousel-inner">
            <?php
            if ($result->num_rows > 0) {
                $active = true;
                while ($row = $result->fetch_assoc()) {
                    ?>
                    <div class="carousel-item <?php echo $active ? 'active' : ''; ?>">
                        <div class="card">
                            <div class="card-body">
                                <p class="card-text"><?php echo $row['testimonial']; ?></p>
                                <footer class="blockquote-footer"><?php echo $row['author']; ?></footer>
                            </div>
                        </div>
                    </div>
                    <?php
                    $active = false;
                }
            } else {
                echo "<p>No testimonials found.</p>";
            }
            ?>
        </div>
        <button class="carousel-control-prev" type="button" data-bs-target="#testimonialCarousel" data-bs-slide="prev">
            <span class="carousel-control-prev-icon" aria-hidden="true"></span>
            <span class="visually-hidden">Previous</span>
        </button>
        <button class="carousel-control-next" type="button" data-bs-target="#testimonialCarousel" data-bs-slide="next">
            <span class="carousel-control-next-icon" aria-hidden="true"></span>
            <span class="visually-hidden">Next</span>
        </button>
    </div>
</section>
