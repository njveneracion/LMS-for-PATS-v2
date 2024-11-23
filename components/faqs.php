<?php
    // Fetch FAQs
$sql = "SELECT * FROM faqs";
$faqs = $connect->query($sql);
?>

<!-- FAQ Section -->
<section id="faqs">
    <h2 class="fw-bold mb-4">Frequently Asked Questions</h2>
    <div class="accordion" id="faqAccordion">
        <?php if ($faqs->num_rows > 0): ?>
            <?php while($row = $faqs->fetch_assoc()): ?>
                <div class="accordion-item">
                    <h2 class="accordion-header" id="faq<?php echo $row['id']; ?>">
                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapse<?php echo $row['id']; ?>" aria-expanded="false" aria-controls="collapse<?php echo $row['id']; ?>">
                            <?php echo $row['question']; ?>
                        </button>
                    </h2>
                    <div id="collapse<?php echo $row['id']; ?>" class="accordion-collapse collapse" aria-labelledby="faq<?php echo $row['id']; ?>" data-bs-parent="#faqAccordion">
                        <div class="accordion-body">
                            <?php echo $row['answer']; ?>
                        </div>
                    </div>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <p>No FAQs found.</p>
        <?php endif; ?>
    </div>
</section>