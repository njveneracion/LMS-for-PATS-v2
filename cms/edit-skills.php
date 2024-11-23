<?php
include '../config/db.php';

$s_header = '';
$s_sub_header = '';
$s_icon = '';
$s_title = '';
$s_description = '';
$iconCategories = [
    'General' => [
        'fa-brain', 'fa-comments', 'fa-users', 'fa-lightbulb', 
        'fa-apple-alt', 'fa-archway', 'fa-balance-scale', 'fa-basketball-ball', 
        'fa-bell', 'fa-bicycle', 'fa-binoculars', 'fa-birthday-cake', 
        'fa-blender', 'fa-bolt', 'fa-bomb', 'fa-book', 
        'fa-book-open', 'fa-briefcase', 'fa-broom', 'fa-brush',
        'fa-bug', 'fa-building', 'fa-bullhorn', 'fa-bullseye', 
        'fa-bus', 'fa-calculator', 'fa-calendar', 'fa-camera'
    ],
    'Technology' => [
        'fa-laptop-code', 'fa-chart-bar', 'fa-project-diagram', 'fa-tasks', 
        'fa-desktop', 'fa-database', 'fa-cogs', 'fa-cogs', 
        'fa-compass', 'fa-compress', 'fa-cube', 'fa-cubes', 
        'fa-edit', 'fa-code', 'fa-cogs', 'fa-robot', 
        'fa-terminal', 'fa-microchip', 'fa-keyboard', 'fa-plug'
    ],
    'Nature' => [
        'fa-tree', 'fa-leaf', 'fa-flower', 'fa-seedling', 
        'fa-sun', 'fa-sunrise', 'fa-sunset', 'fa-cloud', 
        'fa-snowflake', 'fa-wind', 'fa-water', 'fa-mountain',
        'fa-frog', 'fa-paw', 'fa-bug', 'fa-dog'
    ],
    'Transportation' => [
        'fa-car', 'fa-bicycle', 'fa-bus', 'fa-truck', 
        'fa-motorcycle', 'fa-ambulance', 'fa-plane', 'fa-ship', 
        'fa-taxi', 'fa-subway', 'fa-train', 'fa-bus-alt'
    ],
    'Medical' => [
        'fa-hospital', 'fa-stethoscope', 'fa-prescription', 'fa-first-aid',
        'fa-pills', 'fa-syringe', 'fa-heartbeat', 'fa-disease',
        'fa-band-aid', 'fa-tooth', 'fa-medkit', 'fa-hands-helping'
    ],
    'Business' => [
        'fa-briefcase', 'fa-suitcase', 'fa-handshake', 'fa-dollar-sign', 
        'fa-credit-card', 'fa-file', 'fa-clipboard', 'fa-calculator', 
        'fa-chart-line', 'fa-piggy-bank', 'fa-cogs', 'fa-tasks'
    ],
    'Food & Drink' => [
        'fa-coffee', 'fa-pizza-slice', 'fa-apple-alt', 'fa-carrot', 
        'fa-burger', 'fa-drumstick-bite', 'fa-wine-glass', 'fa-cocktail', 
        'fa-beer', 'fa-utensils', 'fa-ice-cream', 'fa-cookie'
    ],
    'Entertainment' => [
        'fa-film', 'fa-gamepad', 'fa-music', 'fa-theater-masks', 
        'fa-tv', 'fa-video', 'fa-camera', 'fa-microphone',
        'fa-headphones', 'fa-video-slash', 'fa-podcast', 'fa-dice'
    ],
    'Health & Wellness' => [
        'fa-running', 'fa-heart', 'fa-bicycle', 'fa-dumbbell', 
        'fa-walking', 'fa-weight', 'fa-yoga', 'fa-hands-helping', 
        'fa-smile', 'fa-sun', 'fa-teeth', 'fa-tooth'
    ],
    'Social' => [
        'fa-users', 'fa-user', 'fa-user-plus', 'fa-user-circle', 
        'fa-user-friends', 'fa-user-times', 'fa-user-lock', 'fa-user-slash',
        'fa-comments', 'fa-comment-dots', 'fa-share-alt', 'fa-share',
        'fa-thumbs-up', 'fa-thumbs-down', 'fa-heart', 'fa-smile-beam'
    ],
    'Sports' => [
        'fa-basketball-ball', 'fa-football-ball', 'fa-baseball-ball', 'fa-tennis-ball',
        'fa-volleyball-ball', 'fa-bowling-ball', 'fa-golf-ball', 'fa-hockey-puck',
        'fa-soccer-ball', 'fa-table-tennis', 'fa-futbol', 'fa-gym'
    ],
    'Weather' => [
        'fa-sun', 'fa-cloud', 'fa-snowflake', 'fa-wind', 
        'fa-cloud-sun', 'fa-cloud-showers-heavy', 'fa-thunderstorm', 'fa-rainbow',
        'fa-sunrise', 'fa-sunset', 'fa-cloud-sun-rain', 'fa-snowman'
    ],
    'Miscellaneous' => [
        'fa-bolt', 'fa-lightbulb', 'fa-paint-brush', 'fa-magic', 
        'fa-gem', 'fa-fire', 'fa-star', 'fa-heart',
        'fa-gift', 'fa-rocket', 'fa-key', 'fa-wrench', 
        'fa-tools', 'fa-calendar-alt', 'fa-anchor', 'fa-bug'
    ],
    'Symbols' => [
        'fa-check', 'fa-exclamation', 'fa-times', 'fa-plus', 
        'fa-minus', 'fa-cogs', 'fa-circle', 'fa-square', 
        'fa-heart', 'fa-check-circle', 'fa-ban', 'fa-warning', 
        'fa-arrow-left', 'fa-arrow-right', 'fa-arrow-up', 'fa-arrow-down'
    ]
];

// Check if the record exists
$sqlCheck = "SELECT * FROM skills";
$resultCheck = mysqli_query($connect, $sqlCheck);
$row = mysqli_fetch_assoc($resultCheck);
$s_id = $row['id'];
$s_header = $row['header'];
$s_sub_header = $row['sub_header'];
$s_icon = $row['icon'];
$s_title = $row['title'];
$s_description = $row['description'];

if (isset($_POST['update_header'])) {
    $header = $_POST['header'];
    $sub_header = $_POST['sub_header'];

    if (mysqli_num_rows($resultCheck) > 0) {
        // Record exists, perform update
        $sql = "UPDATE skills SET header = '$header', sub_header = '$sub_header' WHERE id = 34";
        $result = mysqli_query($connect, $sql);

        if ($result) {
            echo '<div class="alert alert-success alert-dismissible fade show container" role="alert">
                Header and Subheader updated successfully!
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>';
        } else {
            echo '<div class="alert alert-danger alert-dismissible fade show container" role="alert">Failed to update Header and Subheader!
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>';
        }

    } else {
        // Record does not exist, perform insert
        $sql = "INSERT INTO skills (header, sub_header) VALUES ('$header', '$sub_header')";
    }

    // reload the page
    echo "<meta http-equiv='refresh' content='0'>";  
}

if (isset($_POST['add'])) {
    $icon = $_POST['icon'];
    $title = $_POST['title'];
    $description = $_POST['description'];

    // Perform insert
    $sql = "INSERT INTO skills (icon, title, description) VALUES ('$icon', '$title', '$description')";

    $result = mysqli_query($connect, $sql);

    if ($result) {
        echo '<div class="alert alert-success alert-dismissible fade show container" role="alert">
            Icon, Title, and Description added successfully!
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>';
    } else {
        echo '<div class="alert alert-danger alert-dismissible fade show container" role="alert">Failed to add Icon, Title, and Description!
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>';
    }
}

if (isset($_POST['delete_id'])) {
    $id = $_POST['delete_id'];

    // Perform delete
    $sql = "DELETE FROM skills WHERE id = $id";

    $result = mysqli_query($connect, $sql);

    if ($result) {
        echo '<div class="alert alert-success alert-dismissible fade show container" role="alert">
            Skill deleted successfully!
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>';
    } else {
        echo '<div class="alert alert-danger alert-dismissible fade show container" role="alert">Failed to delete Skill!
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>';
    }
}
?>

<div class="container mt-5 mb-5">
    <h2 class="fw-semibold text-center mb-4">Edit Skill Section</h2>
    
    <!-- Form for Header and Subheader -->
    <div class="card mb-5">
        <div class="card-header">
            <h5 class="card-title mb-0">Header and Subheader</h5>
        </div>
        <div class="card-body">
            <form method="post" action="" enctype="multipart/form-data" class="needs-validation" novalidate>
                <div class="row mb-3">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="header" class="form-label">Header</label>
                            <input type="text" class="form-control" id="header" name="header" value="<?= $s_header; ?>" placeholder="Enter header" required>
                            <div class="invalid-feedback">
                                Please provide a header.
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="sub_header" class="form-label">Sub Header</label>
                            <textarea class="form-control" id="sub_header" name="sub_header" rows="3" placeholder="Enter sub header" required><?= $s_sub_header; ?></textarea>
                            <div class="invalid-feedback">
                                Please provide a sub header.
                            </div>
                        </div>
                    </div>
                </div>
                <div class="text-center">
                    <button type="submit" class="btn btn-primary" name="update_header">
                        <i class="fas fa-save"></i> Update Header and Subheader
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Form for Icon, Title, and Description -->
    <div class="card">
        <div class="card-header">
            <h5 class="card-title mb-0">Icon, Title, and Description</h5>
        </div>
        <div class="card-body">
            <form method="post" action="" enctype="multipart/form-data" class="needs-validation" novalidate>
                <div class="row mb-3">
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="icon" class="form-label">Icon</label>
                            <div class="input-group">
                                <input type="text" class="form-control" id="icon" name="icon" readonly required>
                                <button type="button" class="btn btn-secondary" data-bs-toggle="modal" data-bs-target="#iconModal">
                                    <i class="fas fa-icons"></i> Select Icon
                                </button>
                                <div class="invalid-feedback">
                                    Please select an icon.
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="title" class="form-label">Title</label>
                            <input type="text" class="form-control" id="title" name="title" placeholder="Enter title" required>
                            <div class="invalid-feedback">
                                Please provide a title.
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="description" class="form-label">Description</label>
                            <textarea class="form-control" id="description" name="description" rows="3" placeholder="Enter description" required></textarea>
                            <div class="invalid-feedback">
                                Please provide a description.
                            </div>
                        </div>
                    </div>
                </div>
                <div class="text-center">
                    <button type="submit" class="btn btn-primary" name="add">
                        <i class="fas fa-plus"></i> Add Icon, Title, and Description
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Icon Selection Modal -->
<div class="modal fade" id="iconModal" tabindex="-1" aria-labelledby="iconModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="iconModalLabel">Select Icon</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="form-group mb-3">
                    <input type="text" class="form-control" id="iconSearch" placeholder="Search for an icon...">
                </div>
                <div class="form-group mb-3">
                    <select class="form-select" id="iconCategory">
                        <option value="all">All Categories</option>
                        <?php foreach ($iconCategories as $category => $icons) { ?>
                            <option value="<?= $category; ?>"><?= $category; ?></option>
                        <?php } ?>
                    </select>
                </div>
                <div class="row" id="iconContainer">
                    <?php
                    foreach ($iconCategories as $category => $icons) {
                        foreach ($icons as $icon) {
                            echo '<div class="col-3 text-center mb-3 icon-item" data-category="' . $category . '">';
                            echo '<i class="fas ' . $icon . ' icon-select" style="font-size: 2rem; cursor: pointer;" data-icon="' . $icon . '"></i>';
                            echo '</div>';
                        }
                    }
                    
                    ?>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Live Preview Section -->
<section class="py-5 bg-light">
    <div class="container">
        <h2 class="fw-bold text-center mb-5" id="preview-header"><?= $s_header ?: 'Default Header'; ?></h2>
        <p class="lead text-center mb-5" id="preview-sub-header"><?= $s_sub_header ?: 'Default Subheader'; ?></p>

        <div class="row g-4">
           <?php
            // Fetch all records from the skills table
            $sqlFetchAll = "SELECT * FROM skills";
            $resultFetchAll = mysqli_query($connect, $sqlFetchAll);

             if (mysqli_num_rows($resultFetchAll) > 0) {
                while ($row = mysqli_fetch_assoc($resultFetchAll)) {
                    if ($row['id'] == 34) {
                        continue; // Skip rendering the card with ID 34
                    }
                    $id = $row['id'];
                    $icon = $row['icon'];
                    $title = $row['title'];
                    $description = $row['description'];
                    echo '<div class="col-md-3 col-sm-6" data-aos="fade-up">';
                    echo '    <div class="card h-100 border-0 shadow-sm hover-card">';
                    echo '        <div class="card-body text-center">';
                    echo '            <i class="fas ' . $icon . ' feature-icon modified-text-primary mb-3" style="font-size: 2.5rem;"></i>';
                    echo '            <h3 class="h5 card-title">' . $title . '</h3>';
                    echo '            <p class="card-text small">' . $description . '</p>';
                    echo '            <form method="post" action="" class="delete-skill-form">';
                    echo '                <input type="hidden" name="delete_id" value="' . $id . '">';
                    echo '                <button type="submit" class="btn btn-danger btn-sm">Delete</button>';
                    echo '            </form>';
                    echo '        </div>';
                    echo '    </div>';
                    echo '</div>';
                }
            } else {
                echo '<p class="text-center">No skills added yet.</p>';
            }
            ?>
        </div>
    </div>
</section>

<script>
document.addEventListener('DOMContentLoaded', function() {
    function updatePreview() {
        const header = document.getElementById('header');
        const subHeader = document.getElementById('sub_header');
        const title = document.getElementById('title');
        const description = document.getElementById('description');
        const icon = document.getElementById('icon');
        const previewHeader = document.getElementById('preview-header');
        const previewSubHeader = document.getElementById('preview-sub-header');
        const previewTitle = document.getElementById('preview-title');
        const previewDescription = document.getElementById('preview-description');
        const previewIcon = document.getElementById('preview-icon');

        if (header && previewHeader) {
            previewHeader.innerText = header.value;
        }
        if (subHeader && previewSubHeader) {
            previewSubHeader.innerText = subHeader.value;
        }
        if (title && previewTitle) {
            previewTitle.innerText = title.value;
        }
        if (description && previewDescription) {
            previewDescription.innerText = description.value;
        }
        if (icon && previewIcon) {
            previewIcon.className = 'fas ' + icon.value + ' feature-icon modified-text-primary mb-3';
        }
    }

    // Add event listeners to form fields for real-time updates
    document.getElementById('header').addEventListener('input', updatePreview);
    document.getElementById('sub_header').addEventListener('input', updatePreview);
    document.getElementById('title').addEventListener('input', updatePreview);
    document.getElementById('description').addEventListener('input', updatePreview);
    document.getElementById('icon').addEventListener('input', updatePreview);

    // Handle icon selection
    document.querySelectorAll('.icon-select').forEach(function(icon) {
        icon.addEventListener('click', function() {
            const selectedIcon = this.getAttribute('data-icon');
            document.getElementById('icon').value = selectedIcon;
            updatePreview();
            // Close the modal
            var modalElement = document.getElementById('iconModal');
            var modal = bootstrap.Modal.getInstance(modalElement);
            if (modal) {
                modal.hide();
            }
        });
    });

    document.getElementById('iconCategory').addEventListener('change', function() {
        const selectedCategory = this.value;
        document.querySelectorAll('.icon-item').forEach(function(item) {
            if (selectedCategory === 'all' || item.getAttribute('data-category') === selectedCategory) {
                item.style.display = 'block';
            } else {
                item.style.display = 'none';
            }
        });
    });

    document.getElementById('iconSearch').addEventListener('input', function() {
        const searchValue = this.value.toLowerCase();
        document.querySelectorAll('.icon-item').forEach(function(item) {
            const iconClass = item.querySelector('.icon-select').getAttribute('data-icon');
            if (iconClass.toLowerCase().includes(searchValue)) {
                item.style.display = 'block';
            } else {
                item.style.display = 'none';
            }
        });
    });
});
</script>