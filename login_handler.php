<?php
	session_start();
	
	if ((!isset($_POST['email'])) || (!isset($_POST['password']))){
		header('Location: login.php');
		exit();
    }
	$config = require_once "connect.php";
	
	$dsn = "mysql:host=" . $config['host'] . ";dbname=" . $config['database'] . ";charset=utf8";
    $options = [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false
    ];

    try{
        $pdo = new PDO($dsn, $config['user'], $config['password'], $options);
        
		$email = $_POST['email'];
		$password = $_POST['password'];
		
		$email = htmlentities($email, ENT_QUOTES, "UTF-8");
				
		$stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
		$stmt->execute([$email]);
	
		if($stmt->rowCount() > 0){
			$user_data = $stmt->fetch();
			
			if (password_verify($password, $user_data['password'])){
				$_SESSION['loggedIn'] = true;
				$_SESSION['id'] = $user_data['id'];
				$_SESSION['username'] = $user_data['username'];
				$_SESSION['email'] = $user_data['email'];
				
				unset($_SESSION['error']);
				header('Location: index.php');
			} else {
				$_SESSION['error'] = '<span class="error">Incorrect email or password!</span>';
				header('Location: login.php');
			}
		} else {
			$_SESSION['error'] = '<span class="error">Incorrect email or password!</span>';
			header('Location: login.php');	
		}	
        
    } catch (PDOException  $e) {
        echo "Error: " . $e->getMessage();
    }
?>