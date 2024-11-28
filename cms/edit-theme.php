<?php
if (isset($_POST['save'])) {
    $theme = [
        'primaryColor' => $_POST['primaryColor'],
        'secondaryColor' => $_POST['secondaryColor'],
        'backgroundColor' => $_POST['backgroundColor'],
        'textColor' => $_POST['textColor']
    ];
    file_put_contents('theme.json', json_encode($theme));
    if (file_exists('theme.json')) {
        echo '<div class="alert alert-success alert-dismissible fade show" role="alert">
                <strong>Success!</strong> Theme saved successfully.
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>';
    } else {
        echo '<div class="alert alert-danger alert-dismissible fade show" role="alert">
                <strong>Error!</strong> Failed to save theme.
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>';
    }

    echo '<script>
            window.location.href = "main.php?page=edit-theme";
          </script>';
}

$theme = json_decode(file_get_contents('theme.json'), true);

$palettes = [
    ['primaryColor' => '#3498db', 'secondaryColor' => '#2ecc71', 'backgroundColor' => '#ecf0f1', 'textColor' => '#2c3e50'],
    ['primaryColor' => '#e74c3c', 'secondaryColor' => '#f39c12', 'backgroundColor' => '#f5f5f5', 'textColor' => '#333333'],
    ['primaryColor' => '#8e44ad', 'secondaryColor' => '#2980b9', 'backgroundColor' => '#ffffff', 'textColor' => '#34495e'],
    ['primaryColor' => '#1abc9c', 'secondaryColor' => '#16a085', 'backgroundColor' => '#bdc3c7', 'textColor' => '#2c3e50'],
    ['primaryColor' => '#d35400', 'secondaryColor' => '#c0392b', 'backgroundColor' => '#ecf0f1', 'textColor' => '#7f8c8d']
];
?>

<style>
    body {
        background-color: <?= $theme['backgroundColor'] ?>;
        color: <?= $theme['textColor'] ?>;
    }
    .primary {
        background-color: <?= $theme['primaryColor'] ?>;
    }
    .secondary {
        background-color: <?= $theme['secondaryColor'] ?>;
    }
</style>

<div class="container mt-5">
    <h3 class="mt-5">Choose a Palette</h3>
    <div class="row">
        <?php foreach ($palettes as $index => $palette): ?>
            <div class="col-md-2">
                <div class="card mb-3" onclick="applyPalette(<?= $index ?>)" style="cursor: pointer;">
                    <div class="card-body" style="background-color: <?= $palette['backgroundColor'] ?>; color: <?= $palette['textColor'] ?>;">
                        <div class="primary p-2 mb-2" style="background-color: <?= $palette['primaryColor'] ?>;">Primary</div>
                        <div class="secondary p-2" style="background-color: <?= $palette['secondaryColor'] ?>;">Secondary</div>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

    <h2>Edit Theme</h2>
    <form method="post" class="mt-4">
        <div class="d-flex">
            <div class="mb-3">
            <label for="primaryColor" class="form-label">Primary Color:</label>
            <input type="color" id="primaryColor" name="primaryColor" class="form-control form-control-color" value="<?= $theme['primaryColor'] ?>" onchange="updateTheme()">
            </div>
            <div class="mb-3">
                <label for="secondaryColor" class="form-label">Secondary Color:</label>
                <input type="color" id="secondaryColor" name="secondaryColor" class="form-control form-control-color" value="<?= $theme['secondaryColor'] ?>" onchange="updateTheme()">
            </div>
            <div class="mb-3">
                <label for="backgroundColor" class="form-label">Background Color:</label>
                <input type="color" id="backgroundColor" name="backgroundColor" class="form-control form-control-color" value="<?= $theme['backgroundColor'] ?>" onchange="updateTheme()">
            </div>
            <div class="mb-3">
                <label for="textColor" class="form-label">Text Color:</label>
                <input type="color" id="textColor" name="textColor" class="form-control form-control-color" value="<?= $theme['textColor'] ?>" onchange="updateTheme()">
            </div>
        </div>
        <button type="submit" name="save" class="btn btn-primary">Save Theme</button>
    </form>

    <h3 class="mt-5">Current Theme</h3>
    <div class="p-3" style="background-color: <?= $theme['backgroundColor'] ?>; color: <?= $theme['textColor'] ?>;">
        <div class="primary p-3 mb-3">Primary Color</div>
        <div class="secondary p-3 mb-3">Secondary Color</div>
        <div class="p-3 mb-3">Background and Text Color</div>
    </div>
</div>

<script>
    const palettes = <?= json_encode($palettes) ?>;

    function updateTheme() {
        const primaryColor = document.getElementById('primaryColor').value;
        const secondaryColor = document.getElementById('secondaryColor').value;
        const backgroundColor = document.getElementById('backgroundColor').value;
        const textColor = document.getElementById('textColor').value;

        document.body.style.backgroundColor = backgroundColor;
        document.body.style.color = textColor;
        document.querySelector('.primary').style.backgroundColor = primaryColor;
        document.querySelector('.secondary').style.backgroundColor = secondaryColor;
    }

    function applyPalette(index) {
        const palette = palettes[index];
        document.getElementById('primaryColor').value = palette.primaryColor;
        document.getElementById('secondaryColor').value = palette.secondaryColor;
        document.getElementById('backgroundColor').value = palette.backgroundColor;
        document.getElementById('textColor').value = palette.textColor;
        updateTheme();
    }
</script>