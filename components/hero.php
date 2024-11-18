<?php
$sql = "SELECT * FROM hero_section";
$result = mysqli_query($connect, $sql);

if (mysqli_num_rows($result) > 0) {
    $row = mysqli_fetch_assoc($result);
    $title = $row['title'];
    $subtitle = $row['subtitle'];
    $button_text = $row['button_text'];
    $background_image = $row['background_img'];
}
?>

<style>
    .hero-section {
        background: linear-gradient(rgba(0,0,0,0.6), rgba(0,0,0,0.6)), url('<?php echo htmlspecialchars($background_image); ?>');
        background-size: cover;
        background-position: center;
        height: 100vh;
        color: white;
        display: grid;
        place-items: center;
    }
</style>

<section class="hero-section text-center" id="home">
    <div class="container">
        <h1 class="display-4 fw-bold" data-aos="fade-down" data-aos-delay="200"><?php echo htmlspecialchars($title); ?></h1>
        <p class="lead" data-aos="fade-up" data-aos-delay="400"><?php echo htmlspecialchars($subtitle); ?></p>
        <a href="../auth/login.php" class="btn btn-primary button-primary btn-lg mt-3" data-aos="zoom-in" data-aos-delay="600"><?php echo htmlspecialchars($button_text); ?></a>
    </div>
</section>