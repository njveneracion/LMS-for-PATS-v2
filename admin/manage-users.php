<?php
include '../config/db.php';

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'add':
                $fullname = $_POST['fullname'];
                $email = $_POST['email'];
                $username = $_POST['username'];
                $password = $_POST['password'];
                $role = $_POST['role'];
                
                // Hash password
                $salt = '!Gu355+hEp45sW0rd@^;';
                $hashedPassword = hash('gost', $password . $salt);
                
                $sql = "INSERT INTO users (fullname, email, username, password, role, is_verified) VALUES (?, ?, ?, ?, ?, 1)";
                $stmt = $connect->prepare($sql);
                $stmt->bind_param("sssss", $fullname, $email, $username, $hashedPassword, $role);
                if ($stmt->execute()) {
                    logActivity($_SESSION['userID'], 'Add User', "Added new $role: $fullname");
                    $successMessage = "New $role added successfully.";
                } else {
                    $errorMessage = "Error adding new user: " . $connect->error;
                }
                break;
            
            case 'edit':
                $userId = $_POST['user_id'];
                $fullname = $_POST['fullname'];
                $email = $_POST['email'];
                $username = $_POST['username'];
                $role = $_POST['role'];
                
                $sql = "UPDATE users SET fullname = ?, email = ?, username = ?, role = ? WHERE user_id = ?";
                $stmt = $connect->prepare($sql);
                $stmt->bind_param("ssssi", $fullname, $email, $username, $role, $userId);
                if ($stmt->execute()) {
                    logActivity($_SESSION['userID'], 'Edit User', "Updated user: $fullname");
                    $successMessage = "User updated successfully.";
                } else {
                    $errorMessage = "Error updating user: " . $connect->error;
                }
                break;
            
            case 'delete':
                $userId = $_POST['user_id'];
                $sql = "DELETE FROM users WHERE user_id = ?";
                $stmt = $connect->prepare($sql);
                $stmt->bind_param("i", $userId);
                if ($stmt->execute()) {
                    logActivity($_SESSION['userID'], 'Delete User', "Deleted user ID: $userId");
                    $successMessage = "User deleted successfully.";
                } else {
                    $errorMessage = "Error deleting user: " . $connect->error;
                }
                break;
        }
    }
}

// Fetch all users with their course information
$sql = "SELECT u.*, 
        GROUP_CONCAT(DISTINCT CASE WHEN u.role = 'student' THEN c.course_name ELSE NULL END) AS enrolled_courses,
        GROUP_CONCAT(DISTINCT CASE WHEN u.role = 'instructor' THEN c.course_name ELSE NULL END) AS created_courses
        FROM users u
        LEFT JOIN enrollments e ON u.user_id = e.user_id
        LEFT JOIN courses c ON (u.role = 'student' AND e.course_id = c.course_id) OR (u.role = 'instructor' AND c.user_id = u.user_id)
        WHERE u.role IN ('student', 'instructor')
        GROUP BY u.user_id
        ORDER BY u.role, u.fullname";
$result = $connect->query($sql);
$users = $result->fetch_all(MYSQLI_ASSOC);
?>

<div class="container-fluid mt-4">
    <h2>Manage Users</h2>
    <hr>

    <?php if (isset($successMessage)): ?>
        <div class="alert alert-success" role="alert">
            <?php echo $successMessage; ?>
        </div>
    <?php endif; ?>

    <?php if (isset($errorMessage)): ?>
        <div class="alert alert-danger" role="alert">
            <?php echo $errorMessage; ?>
        </div>
    <?php endif; ?>

    <!-- Add User Form -->
    <div class="card mb-4">
        <div class="card-body">
            <h5 class="card-title">Add New User</h5>
            <form method="POST">
                <input type="hidden" name="action" value="add">
                <div class="row">
                    <div class="col-md-4 mb-3">
                        <input type="text" class="form-control" name="fullname" placeholder="Full Name" required>
                    </div>
                    <div class="col-md-4 mb-3">
                        <input type="email" class="form-control" name="email" placeholder="Email" required>
                    </div>
                    <div class="col-md-4 mb-3">
                        <input type="text" class="form-control" name="username" placeholder="Username" required>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-4 mb-3">
                        <input type="password" class="form-control" name="password" placeholder="Password" required>
                    </div>
                    <div class="col-md-4 mb-3">
                        <select class="form-control" name="role" required>
                            <option value="">Select Role</option>
                            <option value="student">Student</option>
                            <option value="instructor">Instructor</option>
                        </select>
                    </div>
                    <div class="col-md-4 mb-3">
                        <button type="submit" class="button-primary rounded p-2">Add User</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Users Table -->
    <div class="card">
        <div class="card-body">
            <h5 class="card-title">User List</h5>
            <!-- Add this div for the search input -->
            <div class="mb-3">
                <input type="text" id="userSearch" class="form-control" placeholder="Search users...">
            </div>
            <div class="table-responsive">
                <table class="table table-hover" id="usersTable">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Full Name</th>
                            <th>Email</th>
                            <th>Username</th>
                            <th>Role</th>
                            <th>Courses</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($users as $user): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($user['user_id']); ?></td>
                            <td><?php echo htmlspecialchars($user['fullname']); ?></td>
                            <td><?php echo htmlspecialchars($user['email']); ?></td>
                            <td><?php echo htmlspecialchars($user['username']); ?></td>
                            <td><?php echo ucfirst(htmlspecialchars($user['role'])); ?></td>
                            <td>
                                <?php
                                if ($user['role'] == 'student') {
                                    echo htmlspecialchars($user['enrolled_courses'] ?: 'No courses enrolled');
                                } else {
                                    echo htmlspecialchars($user['created_courses'] ?: 'No courses created');
                                }
                                ?>
                            </td>
                            <td>
                                <button class="btn btn-sm btn-info mb-1" onclick="viewUser(<?php echo htmlspecialchars(json_encode($user)); ?>)">View</button>
                                <button class="btn btn-sm btn-primary mb-1" onclick="editUser(<?php echo htmlspecialchars(json_encode($user)); ?>)">Edit</button>
                                <button class="btn btn-sm btn-danger mb-1" onclick="deleteUser(<?php echo $user['user_id']; ?>, '<?php echo htmlspecialchars($user['fullname']); ?>')">Delete</button>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Edit User Modal -->
<div class="modal fade" id="editUserModal" tabindex="-1" aria-labelledby="editUserModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editUserModalLabel">Edit User</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="editUserForm" method="POST">
                    <input type="hidden" name="action" value="edit">
                    <input type="hidden" name="user_id" id="editUserId">
                    <div class="mb-3">
                        <label for="editFullname" class="form-label">Full Name</label>
                        <input type="text" class="form-control" id="editFullname" name="fullname" required>
                    </div>
                    <div class="mb-3">
                        <label for="editEmail" class="form-label">Email</label>
                        <input type="email" class="form-control" id="editEmail" name="email" required>
                    </div>
                    <div class="mb-3">
                        <label for="editUsername" class="form-label">Username</label>
                        <input type="text" class="form-control" id="editUsername" name="username" required>
                    </div>
                    <div class="mb-3">
                        <label for="editRole" class="form-label">Role</label>
                        <select class="form-control" id="editRole" name="role" required>
                            <option value="student">Student</option>
                            <option value="instructor">Instructor</option>
                        </select>
                    </div>
                    <button type="submit" class="btn btn-primary">Save Changes</button>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Delete User Modal -->
<div class="modal fade" id="deleteUserModal" tabindex="-1" aria-labelledby="deleteUserModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="deleteUserModalLabel">Confirm Delete</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                Are you sure you want to delete this user: <span id="deleteUserName"></span>?
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <form id="deleteUserForm" method="POST">
                    <input type="hidden" name="action" value="delete">
                    <input type="hidden" name="user_id" id="deleteUserId">
                    <button type="submit" class="btn btn-danger">Delete</button>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- View User Modal -->
<div class="modal fade" id="viewUserModal" tabindex="-1" aria-labelledby="viewUserModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="viewUserModalLabel">User Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-4 text-center mb-3">
                        <img id="viewUserProfilePicture" src="" alt="Profile Picture"  style="width: 150px; height: 150px; border-radius: 50%; object-fit: cover;">
                    </div>
                    <div class="col-md-8">
                        <p><strong>Full Name:</strong> <span id="viewUserFullname"></span></p>
                        <p><strong>Username:</strong> <span id="viewUserUsername"></span></p>
                        <p><strong>Email:</strong> <span id="viewUserEmail"></span></p>
                        <p><strong>Role:</strong> <span id="viewUserRole"></span></p>
                        <p><strong>Verified:</strong> <span id="viewUserVerified"></span></p>
                        <p><strong>Created At:</strong> <span id="viewUserCreatedAt"></span></p>
                    </div>
                </div>
                <div class="row mt-3">
                    <div class="col-12">
                        <h6>Courses:</h6>
                        <ul id="viewUserCourses"></ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function editUser(user) {
    document.getElementById('editUserId').value = user.user_id;
    document.getElementById('editFullname').value = user.fullname;
    document.getElementById('editEmail').value = user.email;
    document.getElementById('editUsername').value = user.username;
    document.getElementById('editRole').value = user.role;
    
    // Show the modal
    new bootstrap.Modal(document.getElementById('editUserModal')).show();
}

function deleteUser(userId, fullname) {
    document.getElementById('deleteUserId').value = userId;
    document.getElementById('deleteUserName').textContent = fullname;
    
    // Show the modal
    new bootstrap.Modal(document.getElementById('deleteUserModal')).show();
}

function viewUser(user) {
    document.getElementById('viewUserProfilePicture').src = user.profile_picture ? '../uploads/profile-pictures/' + user.profile_picture : '../uploads/profile-pictures/default.png';
    document.getElementById('viewUserFullname').textContent = user.fullname;
    document.getElementById('viewUserUsername').textContent = user.username;
    document.getElementById('viewUserEmail').textContent = user.email;
    document.getElementById('viewUserRole').textContent = user.role.charAt(0).toUpperCase() + user.role.slice(1);
    document.getElementById('viewUserVerified').textContent = user.is_verified ? 'Yes' : 'No';
    document.getElementById('viewUserCreatedAt').textContent = user.created_at;

    const coursesList = document.getElementById('viewUserCourses');
    coursesList.innerHTML = '';
    if (user.role === 'student') {
        const enrolledCourses = user.enrolled_courses ? user.enrolled_courses.split(',') : [];
        enrolledCourses.forEach(course => {
            const li = document.createElement('li');
            li.textContent = course.trim();
            coursesList.appendChild(li);
        });
    } else if (user.role === 'instructor') {
        const createdCourses = user.created_courses ? user.created_courses.split(',') : [];
        createdCourses.forEach(course => {
            const li = document.createElement('li');
            li.textContent = course.trim();
            coursesList.appendChild(li);
        });
    }

    if (coursesList.children.length === 0) {
        const li = document.createElement('li');
        li.textContent = user.role === 'student' ? 'No courses enrolled' : 'No courses created';
        coursesList.appendChild(li);
    }
    
    // Show the modal
    new bootstrap.Modal(document.getElementById('viewUserModal')).show();
}

// Add this new function for searching
function searchUsers() {
    var input, filter, table, tr, td, i, txtValue;
    input = document.getElementById("userSearch");
    filter = input.value.toUpperCase();
    table = document.getElementById("usersTable");
    tr = table.getElementsByTagName("tr");

    for (i = 0; i < tr.length; i++) {
        td = tr[i].getElementsByTagName("td");
        for (var j = 0; j < td.length; j++) {
            if (td[j]) {
                txtValue = td[j].textContent || td[j].innerText;
                if (txtValue.toUpperCase().indexOf(filter) > -1) {
                    tr[i].style.display = "";
                    break;
                } else {
                    tr[i].style.display = "none";
                }
            }
        }
    }
}

// Add event listener for the search input
document.getElementById('userSearch').addEventListener('keyup', searchUsers);

</script>