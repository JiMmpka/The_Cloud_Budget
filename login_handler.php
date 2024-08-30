<?php
	session_start();
	
	if ((!isset($_POST['email'])) || (!isset($_POST['password']))){
		header('Location: login.php');
		exit();
    }
	require_once "connect.php";

    try{
        $connection = new mysqli($host, $db_user, $db_password, $db_name);
        
        if ($connection->connect_errno!=0){
            throw new Exception("", $connection->connect_errno);
        } else {
            $email = $_POST['email'];
            $password = $_POST['password'];
            
            $email = htmlentities($email, ENT_QUOTES, "UTF-8");
        
            if ($result = $connection->query(
            sprintf("SELECT * FROM users WHERE email='%s'",
            mysqli_real_escape_string($connection, $email))))
            {
                $how_much_users = $result->num_rows;
                if($how_much_users > 0){
                    $user_data = $result->fetch_assoc();
                    
                    if (password_verify($password, $user_data['password'])){
                        $_SESSION['loggedIn'] = true;
                        $_SESSION['id'] = $user_data['id'];
                        $_SESSION['username'] = $user_data['username'];
                        $_SESSION['email'] = $user_data['email'];
                        
                        unset($_SESSION['error']);
                        $result->free_result();
                        header('Location: index.php');
                    } else {
                        $_SESSION['error'] = '<span class="error">Incorrect email or password!</span>';
                        header('Location: login.php');
                    }
                } else {
                    $_SESSION['error'] = '<span class="error">Incorrect email or password!</span>';
                    header('Location: login.php');	
                }	
            }
            $connection->close();
        }
    } catch (Exception $e) {
        echo "Error: " . $e->getCode();
    }
?>