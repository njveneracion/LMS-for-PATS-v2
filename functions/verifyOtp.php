<?php
include '../config/db.php';
session_start();
$theme = json_decode(file_get_contents('../admin/theme.json'), true);


use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require '../vendor/autoload.php';

function sendOTP($email, $otp){
    $mail = new PHPMailer(true); // Create a new PHPMailer instance

    try {
        // Server settings
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com'; // Set the SMTP server to send through
        $mail->SMTPAuth   = true;                   // Enable SMTP authentication
        $mail->Username   = 'njvenxxviii@gmail.com'; // SMTP username
        $mail->Password   = 'scgx smdi dqea ljux'; // SMTP password
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS; // Enable TLS encryption; `PHPMailer::ENCRYPTION_SMTPS` also accepted
        $mail->Port       = 587; // TCP port to connect to

        // Recipients
        $mail->setFrom('patscabanatuan@gmail.com', 'PATS LMS');
        $mail->addAddress($email); // Add a recipient

        // Content
        $mail->isHTML(true); // Set email format to HTML
        $mail->Subject = 'Your OTP for Email Verification';
        $mail->Body    = "Your OTP for email verification is: <strong>$otp</strong>. This OTP is valid for 15 minutes.";

        $mail->send();
    } catch (Exception $e) {
        // Handle the error
        $_SESSION['registration_error'] = "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
        header('Location: ../auth/register.php');
        exit();
    }
}

$email = isset($_POST['email']) ? mysqli_real_escape_string($connect, $_POST['email']) : '';

if (isset($_POST['verifyOtpBtn']) || isset($_POST['resendOtpBtn'])) {
    // Ensure email is provided
    if (empty($email)) {
        $_SESSION['otp_error'] = 'Email is required.';
        header('Location: verifyOtp.php');
        exit();
    }

    // Query to find the user
    $sql = "SELECT * FROM users WHERE email = '$email'";
    $result = mysqli_query($connect, $sql);

    if (!$result) {
        die('Query Error: ' . mysqli_error($connect)); // For debugging
    }

    $user = mysqli_fetch_assoc($result);

    if ($user) {
        $storedOtp = $user['otp'];
        $otpExpiration = $user['otp_expiration'];
        $createdAt = $user['created_at'];
        $isVerified = $user['is_verified'];
        $now = date('Y-m-d H:i:s');

        // Check if the account is already verified
        if ($isVerified) {
            $_SESSION['otp_error'] = 'Account is already verified.';
            header('Location: verifyOtp.php');
            exit();
        }

        if (strtotime($now) > strtotime($createdAt . ' + 5 minutes')) {
            // Delete account if more than 5 minutes have passed
            $sqlDelete = "DELETE FROM users WHERE email = '$email'";
            mysqli_query($connect, $sqlDelete);
            $_SESSION['otp_error'] = 'Account expired and deleted. Please register again.';
            header('Location: verifyOtp.php');
            exit();
        }

        if (isset($_POST['verifyOtpBtn'])) {
            $otp = $_POST['otp'];

            if ($otp === $storedOtp && strtotime($otpExpiration) > time()) {
                $sqlUpdate = "UPDATE users SET is_verified = 1, otp = NULL, otp_expiration = NULL WHERE email = '$email'";
                mysqli_query($connect, $sqlUpdate);
                $_SESSION['otp_success'] = 'Email verified successfully!, you can now log in';
                header('Location: verifyOtp.php'); // Redirect to login page or another page after success
                exit();
            } else {
                $_SESSION['otp_error'] = 'Invalid or expired OTP.';
            }
        } elseif (isset($_POST['resendOtpBtn'])) {
            // Generate new OTP and expiration
            $otp = rand(100000, 999999);
            $otp_expiration = date('Y-m-d H:i:s', strtotime('+5 minutes'));

            $sqlUpdate = "UPDATE users SET otp = '$otp', otp_expiration = '$otp_expiration' WHERE email = '$email'";
            if (mysqli_query($connect, $sqlUpdate)) {
                sendOTP($email, $otp);
                $_SESSION['otp_success'] = 'New OTP sent. Please check your email.';
            } else {
                $_SESSION['otp_error'] = 'Failed to resend OTP. Please try again.';
            }
        }
    } else {
        $_SESSION['otp_error'] = 'User not found.';
    }

    header('Location: verifyOtp.php');
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>OTP Verification</title>
    <link rel="stylesheet" href="../assets/css/main.css">
    <link rel="stylesheet" href="../assets/css/color.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>

        :root {
            --primary-color: <?= $theme['primaryColor'] ?>;
            --secondary-color: <?= $theme['secondaryColor'] ?>;
            --background-color: <?= $theme['backgroundColor'] ?>;
            --text-color: <?= $theme['textColor'] ?>;
        }
        * { border-radius: 0 !important; /* Removes the border radius */ }
        .container { max-width: 500px; }
        @media (max-width: 576px) {
            .resend-btn {
                font-size: 0.875rem;
                right: 5px;
            }
        }
        .error-message {
            color: #dc3545;
            font-size: 0.875em;
            margin-top: 0.25rem;
        }
    </style>
</head>
<body>
<div class="container mt-5 p-3">
    <div class="card">
        <div class="card-body">
            <h2 class="mb-3">Verify Your Email</h2>
            <div id="formErrors" class="alert alert-danger" style="display: none;"></div>
            <?php
            if (isset($_SESSION['otp_error'])) {
                echo '<div class="alert alert-danger" role="alert">' . $_SESSION['otp_error'] . '</div>';
                unset($_SESSION['otp_error']);
            }
            if (isset($_SESSION['otp_success'])) {
                echo '<div class="alert alert-success" role="alert">' . $_SESSION['otp_success'] . '</div>';
                unset($_SESSION['otp_success']);
            }
            ?>
            <form method="POST" action="verifyOtp.php" id="otpForm">
                <div class="mb-3">
                    <label for="email" class="form-label">Email Address</label>
                    <input type="email" class="form-control p-3" id="email" name="email" value="<?php echo htmlspecialchars($email ?? ''); ?>" required>
                    <small id="emailHelp" class="form-text" style="display: none;"></small>
                </div>
                <label for="otp" class="form-label">Enter OTP</label>
                <div class="mb-3 input-group">
                    <input type="text" class="form-control p-3" style="border-right: none;" id="otp" name="otp">
                    <button type="submit" class="btn p-3" style="border-color: #dee2e6; border-left: none; color: #0f6fc5;" name="resendOtpBtn" value="Resend OTP">Resend</button>
                </div>
                <small id="otpHelp" class="form-text" style="display: none;"></small>
                <button type="submit" class="button-primary w-100 p-3 mb-2" name="verifyOtpBtn">Verify OTP</button>
            </form>
            <a href="../auth/login.php" class="button-outline-primary text-decoration-none text-center form-control p-3">Go back to log in</a>
        </div>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
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

document.getElementById('otp').addEventListener('input', function() {
    const otpRegex = /^\d{6}$/;
    if (this.value.trim() !== '') {
        if (!validateInput(this, otpRegex, 'otpHelp')) {
            document.getElementById('otpHelp').textContent = 'Please enter a valid 6-digit OTP.';
        }
    } else {
        document.getElementById('otpHelp').style.display = 'none';
    }
});

document.getElementById('otpForm').addEventListener('submit', function(e) {
    const email = document.getElementById('email');
    const otp = document.getElementById('otp');
    const emailRegex = /^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/;
    const otpRegex = /^\d{6}$/;
    const formErrors = document.getElementById('formErrors');
    let errors = [];

    if (!emailRegex.test(email.value)) {
        errors.push('Please enter a valid email address.');
    }

    if (otp.value.trim() !== '' && !otpRegex.test(otp.value)) {
        errors.push('Please enter a valid 6-digit OTP.');
    }

    if (errors.length > 0) {
        e.preventDefault();
        formErrors.innerHTML = errors.map(error => `<div>${error}</div>`).join('');
        formErrors.style.display = 'block';
    } else {
        formErrors.style.display = 'none';
    }
});
</script>
</body>
</html>
