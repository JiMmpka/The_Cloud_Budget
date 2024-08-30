<?php
	session_start();
	
	if (isset($_POST['email'])){
		$validation_passed=true;
		
		//Sprawdź poprawność name'a
		$name = $_POST['name'];
		
		//Sprawdzenie długości nicka
		if ((strlen($name)<3) || (strlen($name)>20))
		{
			$validation_passed=false;
			$_SESSION['e_name']="The name should be between 3 and 20 characters long";
		}
		
		if (ctype_alnum($name)==false)
		{
			$validation_passed=false;
			$_SESSION['e_name']="The name can only consist of letters and numbers";
		}
		
		// Sprawdź poprawność adresu email
		$email = $_POST['email'];
		$emailB = filter_var($email, FILTER_SANITIZE_EMAIL);
		
		if ((filter_var($emailB, FILTER_VALIDATE_EMAIL)==false) || ($emailB!=$email))
		{
			$validation_passed=false;
			$_SESSION['e_email']="Please provide a valid email address";
		}
		
		//Sprawdź poprawność hasła
		$password1 = $_POST['password1'];
		$password2 = $_POST['password2'];
		
		if ((strlen($password1)<8) || (strlen($password1)>20))
		{
			$validation_passed=false;
			$_SESSION['e_password']="Password must be between 8 and 20 characters long";
		}
		
		if ($password1!=$password2)
		{
			$validation_passed=false;
			$_SESSION['e_password']="The passwords provided are not identical";
		}	

		$hash_password = password_hash($password1, PASSWORD_DEFAULT);

		//Zapamiętaj wprowadzone dane
		$_SESSION['fr_name'] = $name;
		$_SESSION['fr_email'] = $email;
		$_SESSION['fr_pass1'] = $password1;
		$_SESSION['fr_pass2'] = $password2;

		require_once "connect.php";
		mysqli_report(MYSQLI_REPORT_STRICT);
		
		try {
			$connection = new mysqli($host, $db_user, $db_password, $db_name);
			if ($connection->connect_errno!=0){
				throw new Exception(mysqli_connect_errno());
			} else {
        
				//Czy email już istnieje?
				$result = $connection->query("SELECT id FROM users WHERE email='$email'");
				
				if (!$result) throw new Exception($connection->error);
				
				$same_emails_in_DB = $result->num_rows;
				if($same_emails_in_DB > 0){
					$validation_passed=false;
					$_SESSION['e_email']="There is already an account associated with this email address";
				}		
				
				if ($validation_passed==true){
					//Hurra, wszystkie testy zaliczone, dodajemy gracza do bazy
					
					if ($connection->query("INSERT INTO users VALUES (NULL, '$name', '$hash_password', '$email')")){
						$_SESSION['successful_registration']=true;
						header('Location: registration_success.php');
					} else {
						throw new Exception($connection->error);
					}
				}
				$connection->close();
			}
		}
		catch(Exception $e){
			echo '<span class="error">Server error! We apologize for the inconvenience and ask you to register at another time!</span>';
			//echo '<br />Developer Information: '.$e;
		}		
	}
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
            <div class="card-body p-3 p-md-4 p-xl-5">
              <div class="row">
                <div class="col-12">
                  <div class="mb-5">
                    <div class="text-center mb-3">
                      <a href="#!">
                        <img class="img-fluid" src="./images/BC-logo.png" alt="Cloud Budget Logo">
                      </a>
                    </div>
                    <h2 class="h4 text-center">Registration</h2>
                    <h3 class="fs-6 fw-normal text-secondary text-center m-0">Enter your details to register</h3>
                  </div>
                </div>
              </div>

              <form method="post">
                <div class="row gy-3 overflow-hidden">
                  <div class="col-12">
                    <div class="form-floating mb-3">

                      <input type="text" class="form-control" name="name" id="name" placeholder="Name" required value="<?php
                        if (isset($_SESSION['fr_name']))
                        {
                          echo $_SESSION['fr_name'];
                          unset($_SESSION['fr_name']);
                        }
                      ?>">
                      <label for="name" class="form-label">Name</label>

                      <?php
                        if (isset($_SESSION['e_name']))
                        {
                          echo '<div class="error">'.$_SESSION['e_name'].'</div>';
                          unset($_SESSION['e_name']);
                        }
                      ?>

                    </div>
                  </div>
                  <div class="col-12">
                    <div class="form-floating mb-3">

                      <input type="email" class="form-control" name="email" id="email" placeholder="name@example.com" required value="<?php
                        if (isset($_SESSION['fr_email']))
                        {
                          echo $_SESSION['fr_email'];
                          unset($_SESSION['fr_email']);
                        }
                      ?>">
                      <label for="email" class="form-label">Email</label>

                      <?php
                        if (isset($_SESSION['e_email']))
                        {
                          echo '<div class="error">'.$_SESSION['e_email'].'</div>';
                          unset($_SESSION['e_email']);
                        }
                      ?>

                    </div>
                  </div>
                  <div class="col-12">
                    <div class="form-floating mb-3">
                      
                      <input type="password" class="form-control" name="password1" id="password1" placeholder="Password" required value="<?php
                        if (isset($_SESSION['fr_pass1']))
                        {
                          echo $_SESSION['fr_pass1'];
                          unset($_SESSION['fr_pass1']);
                        }
                      ?>">
                      <label for="password1" class="form-label">Password</label>

                    </div>
                  </div>
                  <div class="col-12">
                    <div class="form-floating mb-3">
                      
                      <input type="password" class="form-control" name="password2" id="password2" placeholder="Confirm password" required value="<?php
                        if (isset($_SESSION['fr_pass2']))
                        {
                          echo $_SESSION['fr_pass2'];
                          unset($_SESSION['fr_pass2']);
                        }
                      ?>">
                      <label for="password2" class="form-label">Confirm password</label>

                      <?php
                        if (isset($_SESSION['e_password']))
                        {
                          echo '<div class="error">'.$_SESSION['e_password'].'</div>';
                          unset($_SESSION['e_password']);
                        }
                      ?>		

                    </div>
                  </div>

                  <div class="col-12">
                    <div class="d-grid">
                      <button class="btn bsb-btn-xl btn-primary" type="submit">Sign up</button>
                    </div>
                  </div>
                </div>
              </form>

              <div class="row">
                <div class="col-12">
                  <hr class="mt-4 mb-4 border-secondary-subtle">
                  <p class="m-0 text-secondary text-center">Already have an account? <a href="./login.php" class="link-primary text-decoration-none">Sign in</a></p>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </section>
</body>
</html>

