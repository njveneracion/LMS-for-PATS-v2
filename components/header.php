<?php
   $sql = "SELECT * FROM header WHERE id=1";
    $result = $connect->query($sql);
    $row = $result->fetch_assoc(); 
?>


<header class="fixed-top">
    <nav class="navbar navbar-expand-lg bg-body-secondary">
        <div class="container">
            <a class="navbar-brand" href="index.php">
                <img src="<?php echo $row['logo'] ?>" alt="Pats Logo" style="height: 60px; width: 100%">
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>

            <div class="collapse navbar-collapse justify-content-end" id="navbarSupportedContent">
                <ul class="navbar-nav mb-2 mb-lg-0">
                    <li class="nav-item">
                        <a class="nav-link active" href="#home" data-section="home">Home</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#courses" data-section="courses">Courses</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#faqs" data-section="about">About</a>
                    </li>
                    <li class="nav-item ms-3 login-btn-toggle ">
                        <a href="../auth/login.php" class="btn btn-primary button-primary px-4">Login</a>
                    </li>
                </ul> 
            </div>
        </div>
    </nav>
</header>