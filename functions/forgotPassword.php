<?php
require '../config/db.php';
session_start();

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require '../vendor/autoload.php';

function sendOTP($email, $otp){
    $mail = new PHPMailer(true);

    try {
        // Server settings
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com';
        $mail->SMTPAuth   = true;
        $mail->Username   = 'njvenxxviii@gmail.com';
        $mail->Password   = 'scgx smdi dqea ljux';
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = 587;

        // Recipients
        $mail->setFrom('patscabanatuan@gmail.com', 'PATS LMS');
        $mail->addAddress($email);

        // Content
        $mail->isHTML(true);
        $mail->Subject = 'Your OTP for Password Reset';
        $mail->Body    = "Your OTP for password reset is: <strong>$otp</strong>. This OTP is valid for 15 minutes.";

        $mail->send();
        return true;
    } catch (Exception $e) {
        $_SESSION['changePass_error'] = "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
        return false;
    }
}

if (isset($_POST['sendVerificationBtn'])) {
    $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
    
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $_SESSION['changePass_error'] = "Invalid email format";
    } else {
        $sql = "SELECT email FROM users WHERE email = ?";
        $stmt = mysqli_prepare($connect, $sql);
        mysqli_stmt_bind_param($stmt, "s", $email);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);

        if (mysqli_num_rows($result) > 0) {
            $otp = sprintf("%06d", mt_rand(100000, 999999));
            $expiration = date('Y-m-d H:i:s', strtotime('+15 minutes'));

            // Delete any existing tokens for this email
            $deleteSql = "DELETE FROM password_reset_tokens WHERE email = ?";
            $deleteStmt = mysqli_prepare($connect, $deleteSql);
            mysqli_stmt_bind_param($deleteStmt, "s", $email);
            mysqli_stmt_execute($deleteStmt);

            // Insert new token
            $insertSql = "INSERT INTO password_reset_tokens (email, token, expiration) VALUES (?, ?, ?)";
            $insertStmt = mysqli_prepare($connect, $insertSql);
            mysqli_stmt_bind_param($insertStmt, "sss", $email, $otp, $expiration);
            
            if (mysqli_stmt_execute($insertStmt) && sendOTP($email, $otp)) {
                $_SESSION['temp_email'] = $email;
                $_SESSION['changePass_success'] = "OTP sent to your email. Please check and enter below.";
                
                // Redirect to OTP verification step
                header('Location: forgotPassword.php?step=verify_otp');
                exit();
            } else {
                $_SESSION['changePass_error'] = "Failed to send OTP. Please try again.";
            }
        } else {
            $_SESSION['changePass_error'] = "Email address not found";
        }
    }
    
    header('Location: forgotPassword.php');
    exit();
}

if (isset($_POST['verifyOtpBtn'])) {
    $email = $_SESSION['temp_email'] ?? '';
    $enteredOtp = filter_input(INPUT_POST, 'otp', FILTER_SANITIZE_NUMBER_INT);
    
    if (empty($email) || empty($enteredOtp)) {
        $_SESSION['changePass_error'] = "Email or OTP is missing";
        header('Location: forgotPassword.php?step=verify_otp');
        exit();
    }

    $checkSql = "SELECT * FROM password_reset_tokens WHERE email = ?";
    $checkStmt = mysqli_prepare($connect, $checkSql);
    mysqli_stmt_bind_param($checkStmt, "s", $email);
    mysqli_stmt_execute($checkStmt);
    $checkResult = mysqli_stmt_get_result($checkStmt);

    if (mysqli_num_rows($checkResult) == 0) {
        $_SESSION['changePass_error'] = "No OTP found for this email. Please request a new OTP.";
    } else {
        $row = mysqli_fetch_assoc($checkResult);

        if ($row['token'] != $enteredOtp) {
            $_SESSION['changePass_error'] = "Invalid OTP. Please try again.";
        } elseif (strtotime($row['expiration']) <= time()) {
            $_SESSION['changePass_error'] = "OTP has expired. Please request a new OTP.";
        } else {
            // OTP is valid, proceed with password reset
            $deleteSql = "DELETE FROM password_reset_tokens WHERE email = ?";
            $deleteStmt = mysqli_prepare($connect, $deleteSql);
            mysqli_stmt_bind_param($deleteStmt, "s", $email);
            mysqli_stmt_execute($deleteStmt);

            $_SESSION['changePass_success'] = "OTP verified successfully. Please reset your password.";
            header('Location: forgotPassword.php?step=reset_password');
            exit();
        }
    }
    
    if (!isset($_SESSION['changePass_success'])) {
        header('Location: forgotPassword.php?step=verify_otp');
        exit();
    }
}

if (isset($_POST['changePasswordBtn'])) {
    $email = $_SESSION['temp_email'] ?? '';
    $newPassword = $_POST['newPassword']; // Don't sanitize password

    if (empty($email) || empty($newPassword)) {
        $_SESSION['changePass_error'] = "Please fill in all fields";
    } elseif (!preg_match('/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{8,}$/', $newPassword)) {
        $_SESSION['changePass_error'] = "Password must be at least 8 characters long and include uppercase, lowercase, number, and special character.";
    } else {
        $salt = '!Gu355+hEp45sW0rd@^;';
        $hashedPassword = hash('gost', $newPassword . $salt);

        $updateSql = "UPDATE users SET password = ? WHERE email = ?";
        $updateStmt = mysqli_prepare($connect, $updateSql);
        mysqli_stmt_bind_param($updateStmt, "ss", $hashedPassword, $email);
        
        if (mysqli_stmt_execute($updateStmt)) {
            $_SESSION['changePass_success'] = "Password updated successfully";
            unset($_SESSION['temp_email']);
            header('Location: ../auth/login.php');
            exit();
        } else {
            $_SESSION['changePass_error'] = "Failed to update password. Please try again";
        }
    }
    
    header('Location: forgotPassword.php?step=reset_password');
    exit();
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../assets/css/main.css">
    <link rel="stylesheet" href="../assets/css/color.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <title>Reset Password</title>
    <style>
        * { border-radius: 0 !important; }
        .container { max-width: 500px; }
        .error-message {
            color: #dc3545;
            font-size: 0.875em;
            margin-top: 0.25rem;
        }
        .form-text {
            font-size: 0.875em;
            margin-top: 0.25rem;
        }
    </style>
</head>
<body>
<div class="container mt-5 p-3">
    <div class="card">
        <div class="card-body">
            <h2 class="mb-3">Reset your password</h2>
            <div id="formErrors" class="alert alert-danger" style="display: none;"></div>
            <?php
            if (isset($_SESSION['changePass_error'])) {
                echo '<div class="alert alert-danger" role="alert">' . htmlspecialchars($_SESSION['changePass_error']) . '</div>';
                unset($_SESSION['changePass_error']);
            }
            if (isset($_SESSION['changePass_success'])) {
                echo '<div class="alert alert-success" role="alert">' . htmlspecialchars($_SESSION['changePass_success']) . '</div>';
                unset($_SESSION['changePass_success']);
            }

            $step = $_GET['step'] ?? '';

            if ($step === 'verify_otp') { ?>
                <form method="POST" id="otpForm">
                    <div class="mb-3">
                        <label for="otp" class="form-label">Enter OTP</label>
                        <input type="text" class="form-control p-3" id="otp" name="otp" required>
                        <small id="otpHelp" class="form-text" style="display: none;">Enter the 6-digit OTP sent to your email.</small>
                    </div>
                    <button type="submit" class="button-primary p-3 w-100" name="verifyOtpBtn">Verify OTP</button>
                </form>
            <?php } elseif ($step === 'reset_password') { ?>
                <form method="POST" id="passwordForm">
                    <div class="mb-3">
                        <label for="newPassword" class="form-label">New Password</label>
                        <input type="password" class="form-control p-3" id="newPassword" name="newPassword" required>
                        <small id="passwordHelp" class="form-text" style="display: none;">Password must be at least 8 characters long and include uppercase, lowercase, number, and special character.</small>
                    </div>
                    <button type="submit" class="button-primary w-100 p-3" name="changePasswordBtn">Change Password</button>
                </form>
            <?php } else { ?>
                <form method="POST" id="emailForm">
                    <div class="mb-3">
                        <input type="email" class="form-control p-3" id="email" name="email" placeholder="Enter your email" required>
                        <small id="emailHelp" class="form-text" style="display: none;">Enter a valid email address.</small>
                    </div>
                    <button type="submit" class="btn btn-primary button-primary w-100 p-3" name="sendVerificationBtn">Send Verification Code</button>
                </form>
            <?php } ?>
            <a href="../auth/login.php" class="button-outline-primary form-control text-decoration-none text-center mt-2 w-100 p-3">Go back to log in</a>
        </div>
    </div>
</div>

<script>
function validateInput(input, regex, helperId) {
    const helperText = document.getElementById(helperId);
    if (input.value.trim() !== '') {
        helperText.style.display = 'block';
        if (regex.test(input.value)) {
            helperText.style.color = 'green';
            helperText.textContent = 'Valid input';
            return true;
        } else {
            helperText.style.color = 'red';
            helperText.textContent = 'Invalid input';
            return false;
        }
    } else {
        helperText.style.display = 'none';
        return false;
    }
}

if (document.getElementById('email')) {
    document.getElementById('email').addEventListener('input', function() {
        const emailRegex = /^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/;
        if (this.value.trim() !== '') {
            if (!validateInput(this, emailRegex, 'emailHelp')) {
                document.getElementById('emailHelp').textContent = 'Please enter a valid email address.';
            }
        } else {
            document.getElementById('emailHelp').style.display = 'none';
        }
    });
}

if (document.getElementById('otp')) {
    document.getElementById('otp').addEventListener('input', function() {
        const otpRegex = /^\d{6}$/;
        if (this.value.trim() !== '') {
            if (!validateInput(this, otpRegex, 'otpHelp')) {
                document.getElementById('otpHelp').textContent = 'OTP must be 6 digits.';
            }
        } else {
            document.getElementById('otpHelp').style.display = 'none';
        }
    });
}

if (document.getElementById('newPassword')) {
    document.getElementById('newPassword').addEventListener('input', function() {
        const passwordRegex = /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{8,}$/;
        if (this.value.trim() !== '') {
            if (!validateInput(this, passwordRegex, 'passwordHelp')) {
                document.getElementById('passwordHelp').textContent = 'Password must be at least 8 characters long and include uppercase, lowercase, number, and special character.';
            }
        } else {
            document.getElementById('passwordHelp').style.display = 'none';
        }
    });
}

document.querySelectorAll('form').forEach(form => {
    form.addEventListener('submit', function(e) {
        const inputs = this.querySelectorAll('input[required]');
        let isValid = true;
        let errors = [];

        inputs.forEach(input => {
            if (input.value.trim() === '') {
                isValid = false;
                errors.push(`Please fill in the ${input.id} field.`);
            } else if (input.id === 'email') {
                isValid = isValid && /^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/.test(input.value);
                if (!isValid) errors.push('Please enter a valid email address.');
            } else if (input.id === 'otp') {
                isValid = isValid && /^\d{6}$/.test(input.value);
                if (!isValid) errors.push('OTP must be 6 digits.');
            } else if (input.id === 'newPassword') {
                isValid = isValid && /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{8,}$/.test(input.value);
                if (!isValid) errors.push('Password must be at least 8 characters long and include uppercase, lowercase, number, and special character.');
            }
        });

        if (!isValid) {
            e.preventDefault();
            const formErrors = document.getElementById('formErrors');
            formErrors.innerHTML = errors.map(error => `<div>${error}</div>`).join('');
            formErrors.style.display = 'block';
        }
    });
});
</script>

</body>
</html>