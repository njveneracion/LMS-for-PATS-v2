<?php
    $sql = "SELECT * FROM cms_courses";
    $result = mysqli_query($connect, $sql);
    $row = mysqli_fetch_assoc($result);
?>

<!-- Courses Section -->
<section class="mb-5" id="courses">
    <h2 class="fw-bold text-center mb-5"><?= htmlspecialchars($row['course_header']) ?></h2>
    <div class="row row-cols-1 row-cols-md-3 g-4 mt-2">
        <?php if ($result->num_rows > 0): ?>
            <?php while($row = $result->fetch_assoc()): ?>
                <div class="col" data-aos="zoom-in">
                    <div class="card h-100 ">
                        <img src="<?= htmlspecialchars($row['image']) ?>" class="card-img-top default-image-size" alt="image">
                        <div class="card-body">
                            <h5 class="card-title"><?= htmlspecialchars($row['title']) ?></h5>
                            <p class="card-text"><?= htmlspecialchars($row['description']) ?></p>
                        </div>
                    </div>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <p>No courses available.</p>
        <?php endif; ?>
    </div>
</section>

<style>
    .default-image-size {
        width: 100%;
        height: 200px;
        object-fit: cover;
    }
</style>