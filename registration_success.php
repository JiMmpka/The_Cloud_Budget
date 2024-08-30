<?php
	session_start();
	
	if (!isset($_SESSION['successful_registration'])){
		header('Location: login.php');
		exit();
	} else{
		unset($_SESSION['successful_registration']);
	}
	
	//Usuwanie zmiennych pamiętających wartości wpisane do formularza
	if (isset($_SESSION['fr_name'])) unset($_SESSION['fr_name']);
	if (isset($_SESSION['fr_email'])) unset($_SESSION['fr_email']);
	if (isset($_SESSION['fr_pass1'])) unset($_SESSION['fr_pass1']);
	if (isset($_SESSION['fr_pass2'])) unset($_SESSION['fr_pass2']);
	
	//Usuwanie błędów rejestracji
	if (isset($_SESSION['e_name'])) unset($_SESSION['e_name']);
	if (isset($_SESSION['e_email'])) unset($_SESSION['e_email']);
	if (isset($_SESSION['e_password'])) unset($_SESSION['e_password']);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Claud Budget</title>
    <link rel="stylesheet" href="https://unpkg.com/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://unpkg.com/bs-brain@2.0.4/components/registrations/registration-7/assets/css/registration-7.css">

    <link rel="stylesheet" href="./css/registration.css">
    <link rel="stylesheet" href="./css/main.css">
</head>
<body>
    
  <!-- Registration Component -->
  <section class="bg-light pt-3 p-md-4 p-xl-5">
    <div class="container">
      <div class="row justify-content-center">
        <div class="col-12 col-md-9 col-lg-7 col-xl-6 col-xxl-5">
          <div class="card border border-light-subtle rounded-4">
            <div class="card-body p-3 p-md-4 p-xl-5 mb-4">
              <div class="text-center mb-3">
                <a href="./login.php">
                  <img class="img-fluid" src="./images/BC-logo.png" alt="Cloud Budget Logo">
                </a>
              </div>
              <h2 class="h4 text-center">You have successfully signed up!</h2>
              <h3 class="fs-6 fw-normal text-secondary text-center">You can now sign in to your account </h3>
              <div class="text-center mt-3">
                <a href="./login.php" class="link-primary text-decoration-none link-opacity-50-hover">Sign in</a>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </section>
</body>
</html>

