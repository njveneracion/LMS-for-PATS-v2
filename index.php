<?php
    include './config/db.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>LMS PATS</title>
    <link rel="stylesheet" href="./assets/css/main.css">
    <link rel="stylesheet" href="./assets/css/color.css">
    <link rel="stylesheet" href="https://unpkg.com/aos@next/dist/aos.css" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
    <script src="https://kit.fontawesome.com/7b2ef867fd.js" crossorigin="anonymous"></script>

</head>
<body>
    <?php include 'components/header.php'; ?>
    <?php include 'components/hero.php'; ?>
    <main class="container my-5">
        <?php include 'components/skills.php'; ?>
        <?php include 'components/features.php'; ?>
        <?php include 'components/courses.php'; ?>  
        <?php include 'components/cta.php'; ?>
        <?php include 'components/testimonials.php'; ?>
        <?php include 'components/faqs.php'; ?>
    </main>
    <?php include 'components/footer.php'; ?>

    <script src="https://unpkg.com/aos@next/dist/aos.js"></script>
    <script src="./assets/js/index.js"></script>
</body>
</html>