
<?php
$message = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $instructorID = $_SESSION['userID'];

    if (isset($_POST['post_announcement'])) {
        $title = mysqli_real_escape_string($connect, $_POST['title']);
        $content = mysqli_real_escape_string($connect, $_POST['content']);
        $courseID = mysqli_real_escape_string($connect, $_POST['courseID']);

        $sqlInsertAnnouncement = "INSERT INTO announcements (instructor_id, title, content, course_id) VALUES ('$instructorID', '$title', '$content', '$courseID')";
        $resultAnnouncement = mysqli_query($connect, $sqlInsertAnnouncement);
        
        if($resultAnnouncement){
            $message = "<div class='alert alert-success alert-dismissible fade show'>Announcement posted successfully!
                        <button type='button' class='btn-close' data-bs-dismiss='alert' aria-label='Close'></button>
            </div>";
        } else {
            $message = "<div class='alert alert-dangeralert-dismissible fade show'>Error posting announcement: " . mysqli_error($connect) . "
                        <button type='button' class='btn-close' data-bs-dismiss='alert' aria-label='Close'></button>
            </div>";
        }
    } elseif (isset($_POST['edit_announcement'])) {
        $announcementID = mysqli_real_escape_string($connect, $_POST['announcement_id']);
        $title = mysqli_real_escape_string($connect, $_POST['title']);
        $content = mysqli_real_escape_string($connect, $_POST['content']);
        $courseID = mysqli_real_escape_string($connect, $_POST['courseID']);

        $sqlUpdateAnnouncement = "UPDATE announcements SET title = '$title', content = '$content', course_id = '$courseID' WHERE id = '$announcementID' AND instructor_id = '$instructorID'";
        $resultUpdate = mysqli_query($connect, $sqlUpdateAnnouncement);

        if($resultUpdate){
            $message = "<div class='alert alert-success alert-dismissible fade show'>Announcement updated successfully!
                        <button type='button' class='btn-close' data-bs-dismiss='alert' aria-label='Close'></button>
            </div>";
        } else {
            $message = "<div class='alert alert-danger alert-dismissible fade show'>Error updating announcement: " . mysqli_error($connect) . "
                        <button type='button' class='btn-close' data-bs-dismiss='alert' aria-label='Close'></button>
            </div>";
        }
    } elseif (isset($_POST['delete_announcement'])) {
        $announcementID = mysqli_real_escape_string($connect, $_POST['announcement_id']);

        $sqlDeleteAnnouncement = "DELETE FROM announcements WHERE id = '$announcementID' AND instructor_id = '$instructorID'";
        $resultDelete = mysqli_query($connect, $sqlDeleteAnnouncement);

        if($resultDelete){
            $message = "<div class='alert alert-success alert-dismissible fade show'>Announcement deleted successfully!
                        <button type='button' class='btn-close' data-bs-dismiss='alert' aria-label='Close'></button>          
                        </div>";
        } else {
            $message = "<div class='alert alert-danger alert-dismissible fade show'>Error deleting announcement: " . mysqli_error($connect) . "
                        <button type='button' class='btn-close' data-bs-dismiss='alert' aria-label='Close'></button>
                         </div>";
        }
    }
}

// Fetch all announcements created by the instructor
$instructorID = $_SESSION['userID'];
$sqlFetchAnnouncements = "SELECT a.*, c.course_name FROM announcements a 
                          JOIN courses c ON a.course_id = c.course_id 
                          WHERE a.instructor_id = '$instructorID' 
                          ORDER BY a.created_at DESC";
$resultAnnouncements = mysqli_query($connect, $sqlFetchAnnouncements);

// Fetch courses for dropdown
$sqlCourses = "SELECT course_id, course_name FROM courses WHERE user_id = '$instructorID'";
$resultCourses = mysqli_query($connect, $sqlCourses);
?>

<link rel="stylesheet" href="https://cdn.ckeditor.com/ckeditor5/43.3.1/ckeditor5.css" />
<div class="container mt-4">
    <div class="row">
        <div class="col-lg-6 mb-4">
            <div class="card">
                <div class="card-header modified-bg-primary text-light">
                    <h2 class="card-title">Create New Announcement</h2>
                </div>
                <div class="card-body">
                    <form method="post" action="">
                        <div class="mb-3">
                            <label for="title" class="form-label">Announcement Title</label>
                            <input type="text" class="form-control" id="title" name="title" required>
                        </div>
                        <div class="mb-3">
                            <label for="content" class="form-label">Announcement Content</label>
                            <textarea class="form-control" id="content" name="content" rows="6"></textarea>
                        </div>
                        <div class="mb-3">
                            <label for="courseID" class="form-label">Select Course</label>
                            <select class="form-select" id="courseID" name="courseID" required>
                                <option value="" disabled selected>Choose a course for this announcement</option>
                                <?php
                                while ($row = mysqli_fetch_assoc($resultCourses)) {
                                    echo "<option value='{$row['course_id']}'>{$row['course_name']}</option>";
                                }
                                ?>
                            </select>
                        </div>
                        <button type="submit" name="post_announcement" class="button-primary p-2 rounded">Post Announcement</button>
                    </form>
                    <div class="mt-3">
                        <?php echo $message; ?>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-6">
            <div class="card">
                <div class="card-header  modified-bg-primary text-light">
                    <h2 class="card-title">Your Announcements</h2>
                </div>
                <div class="card-body">
                    <?php
                    if (mysqli_num_rows($resultAnnouncements) > 0) {
                        while ($announcement = mysqli_fetch_assoc($resultAnnouncements)) {
                            echo "<div class='card mb-3'>";
                            echo "<div class='card-body'>";
                            echo "<h3 class='card-title'>{$announcement['title']}</h3>";
                            echo "<p class='card-text'><small class='text-muted'>Posted on: " . date('F j, Y, g:i a', strtotime($announcement['created_at'])) . " | Course: {$announcement['course_name']}</small></p>";
                            echo "<p class='card-text'>{$announcement['content']}</p>";
                            echo "<button type='button' class='btn btn-warning btn-sm' onclick='editAnnouncement({$announcement['id']}, " . json_encode($announcement['title']) . ", " . json_encode($announcement['content']) . ", {$announcement['course_id']})'>Edit</button>";
                            echo "<button type='button' class='btn btn-danger btn-sm ms-2' onclick='showDeleteModal({$announcement['id']})'>Delete</button>";
                            echo "</div>";
                            echo "</div>";
                        }
                    } else {
                        echo "<p>No announcements found.</p>";
                    }
                    ?>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Edit Announcement Modal -->
<div class="modal fade" id="editAnnouncementModal" tabindex="-1" aria-labelledby="editAnnouncementModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editAnnouncementModalLabel">Edit Announcement</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form method="post" action="">
                <div class="modal-body">
                    <input type="hidden" id="edit_announcement_id" name="announcement_id">
                    <div class="mb-3">
                        <label for="edit_title" class="form-label">Title</label>
                        <input type="text" class="form-control" id="edit_title" name="title" required>
                    </div>
                    <div class="mb-3">
                        <label for="edit_content" class="form-label">Content</label>
                        <textarea class="form-control" id="edit_content" name="content" rows="6" required></textarea>
                    </div>
                    <div class="mb-3">
                        <label for="edit_courseID" class="form-label">Course</label>
                        <select class="form-select" id="edit_courseID" name="courseID" required>
                            <?php
                            mysqli_data_seek($resultCourses, 0);
                            while ($row = mysqli_fetch_assoc($resultCourses)) {
                                echo "<option value='{$row['course_id']}'>{$row['course_name']}</option>";
                            }
                            ?>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" name="edit_announcement" class="btn btn-primary">Save changes</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Delete Announcement Modal -->
<div class="modal fade" id="deleteAnnouncementModal" tabindex="-1" aria-labelledby="deleteAnnouncementModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="deleteAnnouncementModalLabel">Confirm Deletion</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                Are you sure you want to delete this announcement?
            </div>
            <div class="modal-footer">
                <form method="post" action="">
                    <input type="hidden" id="delete_announcement_id" name="announcement_id">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" name="delete_announcement" class="btn btn-danger">Delete</button>
                </form>
            </div>
        </div>
    </div>
</div>

 <script src="https://cdn.ckeditor.com/ckeditor5/43.3.1/ckeditor5.umd.js"></script>
 <script>
            const {
                ClassicEditor,
                Essentials,
                Bold,
                Italic,
                Font,
                Paragraph
            } = CKEDITOR;

            ClassicEditor
                .create( document.querySelector( '#content' ), {
                    plugins: [ Essentials, Bold, Italic, Font, Paragraph ],
                    toolbar: [
                        'undo', 'redo', '|', 'bold', 'italic', '|',
                        'fontSize', 'fontFamily', 'fontColor', 'fontBackgroundColor'
                    ]
                } )
                .then( editor => {
                    console.log( 'Editor was initialized', editor );
                } )
                .catch( error => {
                    console.error( error.stack );
                } );

            ClassicEditor
                .create( document.querySelector( '#edit_content' ), {
                    plugins: [ Essentials, Bold, Italic, Font, Paragraph ],
                    toolbar: [
                        'undo', 'redo', '|', 'bold', 'italic', '|',
                        'fontSize', 'fontFamily', 'fontColor', 'fontBackgroundColor'
                    ]
                } )
                .then( editor => {
                    console.log( 'Editor was initialized', editor );
                } )
                .catch( error => {
                    console.error( error.stack );
                } );
        </script>

<script>
    function editAnnouncement(id, title, content, courseId) {
        document.getElementById('edit_announcement_id').value = id;
        document.getElementById('edit_title').value = title;
        document.getElementById('edit_content').value = content;
        document.getElementById('edit_courseID').value = courseId;
        var editModal = new bootstrap.Modal(document.getElementById('editAnnouncementModal'));
        editModal.show();
    }

    function showDeleteModal(id) {
        document.getElementById('delete_announcement_id').value = id;
        var deleteModal = new bootstrap.Modal(document.getElementById('deleteAnnouncementModal'));
        deleteModal.show();
    }
</script>
