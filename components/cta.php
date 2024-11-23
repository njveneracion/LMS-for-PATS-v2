<?php
   // Fetch CTA content for display
$sql = "SELECT heading, subheading, button_text FROM cta_content WHERE id=1";
$result = mysqli_query($connect, $sql);
$cta = mysqli_fetch_assoc($result);

// Set default values if no data is found
if (!$cta) {
    $cta = [
        'heading' => 'Ready to Enhance Your Skills Online?',
        'subheading' => 'Enroll now in our TESDA-certified online courses and develop 21st century skills.',
        'button_text' => 'Start Your Online Learning Journey',
    ];
}
?>


<!-- CTA Section -->
<section class="cta-section text-center">
    <h2 class="fw-bold mb-4"><?= htmlspecialchars($cta['heading']) ?></h2>
    <p class="lead mb-4"><?= htmlspecialchars($cta['subheading']) ?></p>
    <a href="../auth/register.php" class="btn btn-primary button-primary btn-lg rounded"><?= htmlspecialchars($cta['button_text']) ?></a>
</section>