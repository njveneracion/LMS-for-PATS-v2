<?php
include '../config/db.php';

$s_header = '';
$s_sub_header = '';
$s_icon = '';
$s_title = '';
$s_description = '';

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

if (isset($_POST['update'])) {
    $header = $_POST['header'];
    $sub_header = $_POST['sub_header'];
    $title = $_POST['title'];
    $description = $_POST['description'];
    $icon = $_POST['icon'];

    if (mysqli_num_rows($resultCheck) > 0) {
        // Record exists, perform update
        $sql = "UPDATE skills SET header = '$header', sub_header = '$sub_header', icon = '$icon', title = '$title', description = '$description' WHERE id = 1";
    } else {
        // Record does not exist, perform insert
        $sql = "INSERT INTO skills (header, sub_header, icon, title, description) VALUES ('$header', '$sub_header', '$icon', '$title', '$description')";
    }

    $result = mysqli_query($connect, $sql);

    if ($result) {
        echo '<div class="alert alert-success alert-dismissible fade show container" role="alert">
            Skill section updated successfully!
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>';
    } else {
        echo '<div class="alert alert-danger alert-dismissible fade show container" role="alert">Failed to update skill section!
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>';
    }
}
?>

<div class="container">
    <h2 class="fw-semibold">Edit Skill Section</h2>
    <form method="post" action="" enctype="multipart/form-data">
        <div class="form-group">
            <label for="header">Header</label>
            <input type="text" class="form-control" id="header" name="header" value="<?= $s_header; ?>">
        </div>
        <div class="form-group">
            <label for="subtitle">Sub Header</label>
            <textarea class="form-control" id="subtitle" name="sub_header"><?= $s_sub_header; ?></textarea>
        </div>
        <div class="form-group">
            <label for="icon">Icon</label>
            <div class="input-group">
                <input type="text" class="form-control" id="icon" name="icon" value="<?= $s_icon; ?>" readonly>
                <button type="button" class="btn btn-secondary" data-bs-toggle="modal" data-bs-target="#iconModal">Select Icon</button>
            </div>
        </div>
        <div class="form-group">
            <label for="title">Title</label>
            <input type="text" class="form-control" id="title" name="title" value="<?= $s_title; ?>">
        </div>
        <div class="form-group">
            <label for="description">Description</label>
            <textarea class="form-control" id="description" name="description"><?= $s_description; ?></textarea>
        </div>

        <button type="submit" class="btn btn-primary mt-3" name="update">Update</button>
    </form>
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
                <div class="row">
                    <?php
                    // List of FontAwesome icons
                    $icons = [
                        'fa-brain', 'fa-comments', 'fa-users', 'fa-lightbulb',
                        'fa-laptop-code', 'fa-chart-bar', 'fa-project-diagram', 'fa-tasks',
                        'fa-apple-alt', 'fa-archway', 'fa-balance-scale', 'fa-basketball-ball',
                        'fa-bell', 'fa-bicycle', 'fa-binoculars', 'fa-birthday-cake',
                        'fa-blender', 'fa-bolt', 'fa-bomb', 'fa-book', 'fa-book-open',
                        'fa-briefcase', 'fa-broom', 'fa-brush', 'fa-bug', 'fa-building',
                        'fa-bullhorn', 'fa-bullseye', 'fa-bus', 'fa-calculator', 'fa-calendar',
                        'fa-camera', 'fa-campground', 'fa-car', 'fa-carrot', 'fa-cat',
                        'fa-chalkboard', 'fa-chart-line', 'fa-check', 'fa-check-circle',
                        'fa-chess', 'fa-child', 'fa-church', 'fa-circle', 'fa-clipboard',
                        'fa-clock', 'fa-cloud', 'fa-coffee', 'fa-cog', 'fa-cogs',
                        'fa-compass', 'fa-compress', 'fa-cookie', 'fa-couch', 'fa-credit-card',
                        'fa-cube', 'fa-cubes', 'fa-cut', 'fa-database', 'fa-desktop',
                        'fa-dice', 'fa-dog', 'fa-dollar-sign', 'fa-dolly', 'fa-dove',
                        'fa-drafting-compass', 'fa-dragon', 'fa-drum', 'fa-dumbbell', 'fa-edit',
                        'fa-egg', 'fa-envelope', 'fa-equals', 'fa-eraser', 'fa-euro-sign',
                        'fa-exclamation', 'fa-exclamation-circle', 'fa-exclamation-triangle', 'fa-expand',
                        'fa-eye', 'fa-eye-dropper', 'fa-fan', 'fa-feather', 'fa-fighter-jet',
                        'fa-file', 'fa-film', 'fa-filter', 'fa-fire', 'fa-fire-extinguisher',
                        'fa-flag', 'fa-flask', 'fa-flushed', 'fa-folder', 'fa-football-ball',
                        'fa-frog', 'fa-frown', 'fa-futbol', 'fa-gamepad', 'fa-gas-pump',
                        'fa-gavel', 'fa-gem', 'fa-gift', 'fa-glass-martini', 'fa-globe',
                        'fa-golf-ball', 'fa-gopuram', 'fa-graduation-cap', 'fa-guitar', 'fa-hamburger',
                        'fa-hammer', 'fa-hand-holding', 'fa-hand-paper', 'fa-handshake', 'fa-hard-hat',
                        'fa-hashtag', 'fa-hat-wizard', 'fa-headphones', 'fa-heart', 'fa-heartbeat',
                        'fa-helicopter', 'fa-highlighter', 'fa-hiking', 'fa-hippo', 'fa-hockey-puck',
                        'fa-home', 'fa-horse', 'fa-hospital', 'fa-hotdog', 'fa-hotel',
                        'fa-hourglass', 'fa-house-damage', 'fa-hryvnia', 'fa-ice-cream', 'fa-icicles',
                        'fa-id-badge', 'fa-id-card', 'fa-image', 'fa-images', 'fa-inbox',
                        'fa-infinity', 'fa-info', 'fa-info-circle', 'fa-italic', 'fa-jedi',
                        'fa-joint', 'fa-journal-whills', 'fa-kaaba', 'fa-key', 'fa-keyboard',
                        'fa-khanda', 'fa-kiss', 'fa-kiss-beam', 'fa-kiss-wink-heart', 'fa-kiwi-bird',
                        'fa-landmark', 'fa-language', 'fa-laptop', 'fa-laugh', 'fa-leaf',
                        'fa-lemon', 'fa-less-than', 'fa-less-than-equal', 'fa-life-ring', 'fa-lightbulb',
                        'fa-link', 'fa-lira-sign', 'fa-list', 'fa-list-alt', 'fa-location-arrow',
                        'fa-lock', 'fa-lock-open', 'fa-long-arrow-alt-down', 'fa-long-arrow-alt-left', 'fa-long-arrow-alt-right',
                        'fa-long-arrow-alt-up', 'fa-low-vision', 'fa-luggage-cart', 'fa-magic', 'fa-magnet',
                        'fa-mail-bulk', 'fa-male', 'fa-map', 'fa-map-marked', 'fa-map-marker',
                        'fa-map-pin', 'fa-marker', 'fa-mars', 'fa-mars-double', 'fa-mars-stroke',
                        'fa-mars-stroke-h', 'fa-mars-stroke-v', 'fa-medal', 'fa-medkit', 'fa-meh',
                        'fa-memory', 'fa-menorah', 'fa-mercury', 'fa-microchip', 'fa-microphone',
                        'fa-microphone-alt', 'fa-microphone-slash', 'fa-microscope', 'fa-minus', 'fa-minus-circle',
                        'fa-minus-square', 'fa-mobile', 'fa-mobile-alt', 'fa-money-bill', 'fa-money-bill-alt',
                        'fa-money-bill-wave', 'fa-money-bill-wave-alt', 'fa-money-check', 'fa-money-check-alt', 'fa-monument',
                        'fa-moon', 'fa-mortar-pestle', 'fa-mosque', 'fa-motorcycle', 'fa-mountain',
                        'fa-mouse-pointer', 'fa-music', 'fa-network-wired', 'fa-neuter', 'fa-newspaper',
                        'fa-not-equal', 'fa-notes-medical', 'fa-object-group', 'fa-object-ungroup', 'fa-oil-can',
                        'fa-om', 'fa-otter', 'fa-outdent', 'fa-paint-brush', 'fa-paint-roller',
                        'fa-palette', 'fa-pallet', 'fa-paper-plane', 'fa-paperclip', 'fa-parachute-box',
                        'fa-paragraph', 'fa-parking', 'fa-passport', 'fa-pastafarianism', 'fa-paste',
                        'fa-pause', 'fa-paw', 'fa-peace', 'fa-pen', 'fa-pen-alt',
                        'fa-pen-fancy', 'fa-pen-nib', 'fa-pen-square', 'fa-pencil-alt', 'fa-pencil-ruler',
                        'fa-people-carry', 'fa-percent', 'fa-percentage', 'fa-phone', 'fa-phone-slash',
                        'fa-phone-square', 'fa-phone-volume', 'fa-piggy-bank', 'fa-pills', 'fa-place-of-worship',
                        'fa-plane', 'fa-plane-arrival', 'fa-plane-departure', 'fa-play', 'fa-play-circle',
                        'fa-plug', 'fa-plus', 'fa-plus-circle', 'fa-plus-square', 'fa-podcast',
                        'fa-poll', 'fa-poll-h', 'fa-poo', 'fa-poo-storm', 'fa-poop',
                        'fa-portrait', 'fa-pound-sign', 'fa-power-off', 'fa-pray', 'fa-praying-hands',
                        'fa-prescription', 'fa-prescription-bottle', 'fa-prescription-bottle-alt', 'fa-print', 'fa-procedures',
                        'fa-project-diagram', 'fa-puzzle-piece', 'fa-qrcode', 'fa-question', 'fa-question-circle',
                        'fa-quidditch', 'fa-quote-left', 'fa-quote-right', 'fa-quran', 'fa-radiation',
                        'fa-radiation-alt', 'fa-rainbow', 'fa-random', 'fa-receipt', 'fa-recycle',
                        'fa-redo', 'fa-redo-alt', 'fa-registered', 'fa-reply', 'fa-reply-all',
                        'fa-republican', 'fa-restroom', 'fa-retweet', 'fa-ribbon', 'fa-ring',
                        'fa-road', 'fa-robot', 'fa-rocket', 'fa-route', 'fa-rss',
                        'fa-rss-square', 'fa-ruble-sign', 'fa-ruler', 'fa-ruler-combined', 'fa-ruler-horizontal',
                        'fa-ruler-vertical', 'fa-running', 'fa-rupee-sign', 'fa-sad-cry', 'fa-sad-tear',
                        'fa-satellite', 'fa-satellite-dish', 'fa-save', 'fa-school', 'fa-screwdriver',
                        'fa-scroll', 'fa-sd-card', 'fa-search', 'fa-search-dollar', 'fa-search-location',
                        'fa-search-minus', 'fa-search-plus', 'fa-seedling', 'fa-server', 'fa-shapes',
                        'fa-share', 'fa-share-alt', 'fa-share-alt-square', 'fa-share-square', 'fa-shekel-sign',
                        'fa-shield-alt', 'fa-ship', 'fa-shipping-fast', 'fa-shoe-prints', 'fa-shopping-bag',
                        'fa-shopping-basket', 'fa-shopping-cart', 'fa-shower', 'fa-shuttle-van', 'fa-sign',
                        'fa-sign-in-alt', 'fa-sign-language', 'fa-sign-out-alt', 'fa-signal', 'fa-signature',
                        'fa-sim-card', 'fa-sitemap', 'fa-skating', 'fa-skiing', 'fa-skiing-nordic',
                        'fa-skull', 'fa-skull-crossbones', 'fa-slash', 'fa-sleigh', 'fa-sliders-h',
                        'fa-smile', 'fa-smile-beam', 'fa-smile-wink', 'fa-smog', 'fa-smoking',
                        'fa-smoking-ban', 'fa-sms', 'fa-snowboarding', 'fa-snowflake', 'fa-snowman',
                        'fa-snowplow', 'fa-socks', 'fa-solar-panel', 'fa-sort', 'fa-sort-alpha-down',
                        'fa-sort-alpha-down-alt', 'fa-sort-alpha-up', 'fa-sort-alpha-up-alt', 'fa-sort-amount-down', 'fa-sort-amount-down-alt',
                        'fa-sort-amount-up', 'fa-sort-amount-up-alt', 'fa-sort-down', 'fa-sort-numeric-down', 'fa-sort-numeric-down-alt',
                        'fa-sort-numeric-up', 'fa-sort-numeric-up-alt', 'fa-sort-up', 'fa-spa', 'fa-space-shuttle',
                        'fa-spell-check', 'fa-spider', 'fa-spinner', 'fa-splotch', 'fa-spray-can',
                        'fa-square', 'fa-square-full', 'fa-square-root-alt', 'fa-stamp', 'fa-star',
                        'fa-star-and-crescent', 'fa-star-half', 'fa-star-half-alt', 'fa-star-of-david', 'fa-star-of-life',
                        'fa-step-backward', 'fa-step-forward', 'fa-stethoscope', 'fa-sticky-note', 'fa-stop',
                        'fa-stop-circle', 'fa-stopwatch', 'fa-store', 'fa-store-alt', 'fa-stream',
                        'fa-street-view', 'fa-strikethrough', 'fa-stroopwafel', 'fa-subscript', 'fa-subway',
                        'fa-suitcase', 'fa-suitcase-rolling', 'fa-sun', 'fa-superscript', 'fa-surprise',
                        'fa-swatchbook', 'fa-swimmer', 'fa-swimming-pool', 'fa-synagogue', 'fa-sync',
                        'fa-sync-alt', 'fa-syringe', 'fa-table', 'fa-table-tennis', 'fa-tablet',
                        'fa-tablet-alt', 'fa-tablets', 'fa-tachometer-alt', 'fa-tag', 'fa-tags',
                        'fa-tape', 'fa-tasks', 'fa-taxi', 'fa-teeth', 'fa-teeth-open',
                        'fa-temperature-high', 'fa-temperature-low', 'fa-tenge', 'fa-terminal', 'fa-text-height',
                        'fa-text-width', 'fa-th', 'fa-th-large', 'fa-th-list', 'fa-theater-masks',
                        'fa-thermometer', 'fa-thermometer-empty', 'fa-thermometer-full', 'fa-thermometer-half', 'fa-thermometer-quarter',
                        'fa-thermometer-three-quarters', 'fa-thumbs-down', 'fa-thumbs-up', 'fa-thumbtack', 'fa-ticket-alt',
                        'fa-times', 'fa-times-circle', 'fa-tint', 'fa-tint-slash', 'fa-tired',
                        'fa-toggle-off', 'fa-toggle-on', 'fa-toilet', 'fa-toilet-paper', 'fa-toolbox',
                        'fa-tools', 'fa-tooth', 'fa-torah', 'fa-torii-gate', 'fa-tractor',
                        'fa-trademark', 'fa-traffic-light', 'fa-train', 'fa-tram', 'fa-transgender',
                        'fa-transgender-alt', 'fa-trash', 'fa-trash-alt', 'fa-trash-restore', 'fa-trash-restore-alt',
                        'fa-tree', 'fa-trophy', 'fa-truck', 'fa-truck-loading', 'fa-truck-monster',
                        'fa-truck-moving', 'fa-truck-pickup', 'fa-tshirt', 'fa-tty', 'fa-tv',
                        'fa-umbrella', 'fa-umbrella-beach', 'fa-underline', 'fa-undo', 'fa-undo-alt',
                        'fa-universal-access', 'fa-university', 'fa-unlink', 'fa-unlock', 'fa-unlock-alt',
                        'fa-upload', 'fa-user', 'fa-user-alt', 'fa-user-alt-slash', 'fa-user-astronaut',
                        'fa-user-check', 'fa-user-circle', 'fa-user-clock', 'fa-user-cog', 'fa-user-edit',
                        'fa-user-friends', 'fa-user-graduate', 'fa-user-injured', 'fa-user-lock', 'fa-user-md',
                        'fa-user-minus', 'fa-user-ninja', 'fa-user-nurse', 'fa-user-plus', 'fa-user-secret',
                        'fa-user-shield', 'fa-user-slash', 'fa-user-tag', 'fa-user-tie', 'fa-user-times',
                    ];
                    foreach ($icons as $icon) {
                        echo '<div class="col-3 text-center mb-3">';
                        echo '<i class="fas ' . $icon . ' icon-select" style="font-size: 2rem; cursor: pointer;" data-icon="' . $icon . '"></i>';
                        echo '</div>';
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
        <h2 class="fw-bold text-center mb-5" id="preview-header"><?= $s_header; ?></h2>
        <p class="lead text-center mb-5" id="preview-sub-header"><?= $s_sub_header; ?></p>

        <div class="row g-4">
            <div class="col-md-3 col-sm-6" data-aos="fade-up">
                <div class="card h-100 border-0 shadow-sm hover-card">
                    <div class="card-body text-center">
                        <i class="fas <?= $s_icon; ?> feature-icon modified-text-primary mb-3" style="font-size: 2.5rem;" id="preview-icon"></i>
                        <h3 class="h5 card-title" id="preview-title"><?= $s_title; ?></h3>
                        <p class="card-text small" id="preview-description"><?= $s_description; ?></p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<script>
function updatePreview() {
    document.getElementById('preview-header').innerText = document.getElementById('header').value;
    document.getElementById('preview-sub-header').innerText = document.getElementById('subtitle').value;
    document.getElementById('preview-title').innerText = document.getElementById('title').value;
    document.getElementById('preview-description').innerText = document.getElementById('description').value;
    document.getElementById('preview-icon').className = 'fas ' + document.getElementById('icon').value + ' feature-icon modified-text-primary mb-3';
}

// Add event listeners to form fields for real-time updates
document.getElementById('header').addEventListener('input', updatePreview);
document.getElementById('subtitle').addEventListener('input', updatePreview);
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
        var modal = bootstrap.Modal.getInstance(document.getElementById('iconModal'));
        modal.hide();
    });
});
</script>