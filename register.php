<?php
session_start();

if (isset($_POST['email'])) {
    $validation_passed = true;
    
    // Check the validity of the name
    $name = $_POST['name'];
    
    // Check the length of the username
    if ((strlen($name) < 3) || (strlen($name) > 20)) {
        $validation_passed = false;
        $_SESSION['e_name'] = "The name should be between 3 and 20 characters long";
    }
    
    if (!ctype_alnum($name)) {
        $validation_passed = false;
        $_SESSION['e_name'] = "The name can only consist of letters and numbers";
    }
    
    // Check the validity of the email address
    $email = $_POST['email'];
    $emailB = filter_var($email, FILTER_SANITIZE_EMAIL);
    
    if ((filter_var($emailB, FILTER_VALIDATE_EMAIL) === false) || ($emailB != $email)) {
        $validation_passed = false;
        $_SESSION['e_email'] = "Please provide a valid email address";
    }
    
    // Check the validity of the password
    $password1 = $_POST['password1'];
    $password2 = $_POST['password2'];
    
    if ((strlen($password1) < 8) || (strlen($password1) > 20)) {
        $validation_passed = false;
        $_SESSION['e_password'] = "Password must be between 8 and 20 characters long";
    }
    
    if ($password1 != $password2) {
        $validation_passed = false;
        $_SESSION['e_password'] = "The passwords provided are not identical";
    }    

    $hash_password = password_hash($password1, PASSWORD_DEFAULT);

    // Remember the entered data
    $_SESSION['fr_name'] = $name;
    $_SESSION['fr_email'] = $email;
    $_SESSION['fr_pass1'] = $password1;
    $_SESSION['fr_pass2'] = $password2;

    $config = require_once "connect.php";

    $dsn = "mysql:host=" . $config['host'] . ";dbname=" . $config['database'] . ";charset=utf8";
    $options = [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false
    ];
    
    try {
        $pdo = new PDO($dsn, $config['user'], $config['password'], $options);
    
        // Check if the email already exists
        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->execute([$email]);

        if ($stmt->rowCount() > 0) {    
            $validation_passed = false;
            $_SESSION['e_email'] = "There is already an account associated with this email address";
        }
            
        if ($validation_passed) {    
            // Hooray, all tests passed, let's add the player to the database
            $stmt = $pdo->prepare("INSERT INTO users (username, password, email) VALUES (?, ?, ?)");

            if ($stmt->execute([$name, $hash_password, $email])) {    
              $_SESSION['successful_registration'] = true;
				      $userId = $pdo->lastInsertId();
				
                // Start transaction
              $pdo->beginTransaction();

              try {
                $stmt = $pdo->prepare("INSERT INTO incomes_category_assigned_to_users (`user_id`, `name`) 
                            SELECT ?, `name` FROM incomes_category_default");
                $stmt->execute([$userId]);

                $stmt = $pdo->prepare("INSERT INTO expenses_category_assigned_to_users (`user_id`, `name`) 
                            SELECT ?, `name` FROM expenses_category_default");
                $stmt->execute([$userId]);

                $stmt = $pdo->prepare("INSERT INTO payment_methods_assigned_to_users (`user_id`, `name`) 
                            SELECT ?, `name` FROM payment_methods_default");
                $stmt->execute([$userId]);

                // Commit the transaction
                $pdo->commit();

                header('Location: registration_success.php');
                exit();
              } catch (Exception $e) {
                // Rollback the transaction in case of an error
                $pdo->rollBack();
                throw new Exception("Failed to assign categories: " . $e->getMessage());
              }
            } else {    
                throw new Exception("Failed to register user." . $e->getMessage());
            }
        }
    } catch (Exception $e) {
        echo '<span class="error">Server error! We apologize for the inconvenience and ask you to register at another time!</span>';
        //echo '<br />Developer Information: ' . htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8');
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

    <link rel="stylesheet" href="./css/auth.css">
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
                        if (isset($_SESSION['fr_name'])) {
                          echo htmlspecialchars($_SESSION['fr_name'], ENT_QUOTES, 'UTF-8');
                          unset($_SESSION['fr_name']);
                        }
                      ?>">
                      <label for="name" class="form-label">Name</label>

                      <?php
                        if (isset($_SESSION['e_name'])) {
                            echo '<div class="error">'.htmlspecialchars($_SESSION['e_name'], ENT_QUOTES, 'UTF-8').'</div>';
                            unset($_SESSION['e_name']);
                        }
                      ?>

                    </div>
                  </div>
                  <div class="col-12">
                    <div class="form-floating mb-3">

                      <input type="email" class="form-control" name="email" id="email" placeholder="name@example.com" required value="<?php
                        if (isset($_SESSION['fr_email'])) {
                          echo htmlspecialchars($_SESSION['fr_email'], ENT_QUOTES, 'UTF-8');
                          unset($_SESSION['fr_email']);
                        }
                      ?>">
                      <label for="email" class="form-label">Email</label>
                      <?php
                        if (isset($_SESSION['e_email'])) {
                            echo '<div class="error">'.htmlspecialchars($_SESSION['e_email'], ENT_QUOTES, 'UTF-8').'</div>';
                            unset($_SESSION['e_email']);
                        }
                      ?>

                    </div>
                  </div>
                  <div class="col-12">
                    <div class="form-floating mb-3">
                      
                      <input type="password" class="form-control" name="password1" id="password1" placeholder="Password" required value="<?php
                        if (isset($_SESSION['fr_pass1'])) {
                          echo htmlspecialchars($_SESSION['fr_pass1'], ENT_QUOTES, 'UTF-8');
                          unset($_SESSION['fr_pass1']);
                        }
                      ?>">
                      <label for="password1" class="form-label">Password</label>

                    </div>
                  </div>
                  <div class="col-12">
                    <div class="form-floating mb-3">
                      
                      <input type="password" class="form-control" name="password2" id="password2" placeholder="Confirm password" required value="<?php
                        if (isset($_SESSION['fr_pass2'])) {
                          echo htmlspecialchars($_SESSION['fr_pass2'], ENT_QUOTES, 'UTF-8');
                          unset($_SESSION['fr_pass2']);
                        }
                      ?>">
                      <label for="password2" class="form-label">Confirm password</label>
                      <?php
                        if (isset($_SESSION['e_password'])) {
                            echo '<div class="error">'.htmlspecialchars($_SESSION['e_password'], ENT_QUOTES, 'UTF-8').'</div>';
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

