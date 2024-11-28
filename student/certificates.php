<?php
// Ensure the user is logged in and is a student
if (!isset($_SESSION['userID']) || $_SESSION['role'] !== 'student') {
    header("Location: ../auth/login.php");
    exit();
}

$studentID = $_SESSION['userID'];

// Fetch all certificates for the student
$sql = "SELECT c.certificate_id, c.certificate_path, c.generated_at, 
               co.course_name, co.course_code
        FROM certificates c
        JOIN courses co ON c.course_id = co.course_id
        WHERE c.student_id = ? AND c.is_verified = 1
        ORDER BY c.generated_at DESC";

$stmt = $connect->prepare($sql);
$stmt->bind_param("i", $studentID);
$stmt->execute();
$result = $stmt->get_result();
$theme = json_decode(file_get_contents('../admin/theme.json'), true);
?>
<style>
.btn-primary {
    background-color: <?= $theme["primaryColor"]?> !important;
    border-color: <?= $theme["primaryColor"]?> !important;
}
</style>

<div class="container-fluid mt-4">
    <div class="row mb-4">
        <div class="col">
            <h2 style="color:<?= $theme["primaryColor"]?>"><i class="fas fa-certificate me-2"></i>My Certificates</h2>
        </div>
    </div>

    <?php if ($result->num_rows > 0): ?>
        <div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 row-cols-xl-4 g-4">
            <?php while ($row = $result->fetch_assoc()): ?>
                <div class="col">
                    <div class="card h-100 shadow-sm certificate-card">
                        <div class="card-body d-flex flex-column">
                            <div class="certificate-icon mb-3" style="color:<?= $theme["primaryColor"]?>">
                                <i class="fas fa-award fa-3x  "></i>
                            </div>
                            <h5 class="card-title"><?= htmlspecialchars($row['course_name']) ?></h5>
                            <h6 class="card-subtitle mb-2 text-muted"><?= htmlspecialchars($row['course_code']) ?></h6>
                            <p class="card-text mt-auto">
                                <small class="text-muted">
                                    <i class="far fa-calendar-alt me-1"></i>
                                    Generated on: <?= date('F j, Y', strtotime($row['generated_at'])) ?>
                                </small>
                            </p>
                        </div>
                        <div class="card-footer bg-transparent border-top-0">
                            <div class="d-grid gap-2">
                                <a href="<?= htmlspecialchars($row['certificate_path']) ?>" class="btn btn-primary btn-sm" target="_blank">
                                    <i class="fas fa-eye me-1"></i> View Certificate
                                </a>
                                <a href="<?= htmlspecialchars($row['certificate_path']) ?>" class="btn btn-outline-secondary btn-sm" download>
                                    <i class="fas fa-download me-1"></i> Download
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endwhile; ?>
        </div>
    <?php else: ?>
        <div class="alert alert-info" role="alert">
            <i class="fas fa-info-circle me-2"></i> You haven't earned any certificates yet. Keep learning and complete your courses to earn certificates!
        </div>
    <?php endif; ?>
</div>

<style>
    .certificate-card {
        transition: transform 0.2s, box-shadow 0.2s;
        border: none;
        border-radius: 15px;
        overflow: hidden;
    }
    .certificate-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 10px 20px rgba(0,0,0,0.1);
    }
    .card-footer {
        background-color: transparent;
        border-top: 1px solid rgba(0,0,0,.125);
        padding-top: 1rem;
    }
    .certificate-icon {
        text-align: center;
    }
    .card-title {
        font-size: 1.1rem;
        font-weight: bold;
    }
    .card-subtitle {
        font-size: 0.9rem;
    }
    @media (max-width: 576px) {
        .container-fluid {
            padding-left: 10px;
            padding-right: 10px;
        }
    }
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const cards = document.querySelectorAll('.certificate-card');
    cards.forEach(card => {
        card.addEventListener('mouseenter', function() {
            this.style.transform = 'translateY(-10px)';
            this.style.boxShadow = '0 15px 30px rgba(0,0,0,0.15)';
        });
        card.addEventListener('mouseleave', function() {
            this.style.transform = 'translateY(0)';
            this.style.boxShadow = '0 5px 15px rgba(0,0,0,0.1)';
        });
    });
});
</script>
