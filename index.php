<?php
	session_start();
	
	if ((!isset($_SESSION['loggedIn'])) && (!$_SESSION['loggedIn']==true)){
		header('Location: login.php');
		exit();
	}
?>

<!DOCTYPE html>
<html lang="en">

<head id="header"></head>

    <body class="d-flex flex-column min-vh-100">
        <div id="navbar"></div>
        <!-- Main content -->
    <main class="container mt-5">
        <h1 class="text-center mb-5">Welcome to CloudBudget <?php echo " - ".$_SESSION['username']?></h1>
        
        <!-- User Dashboard -->
        <div class="row">
            <div class="col-lg-4 mb-4">
                <div class="card text-center">
                    <div class="card-header card-header-font-size">
                        Add Income
                    </div>
                    <div class="card-body">
                        <p class="card-text ">Track your income with ease.</p> 
                        <a href="incomes.php" class="btn btn-primary d-inline-flex align-items-center justify-content-center"><svg class="bi me-2" width="24" height="24"><use xlink:href="#incomes"></use></svg>Add Income</a>
                    </div>
                </div>
            </div>

            <div class="col-lg-4 mb-4">
                <div class="card text-center">
                    <div class="card-header card-header-font-size">
                        Add Expense
                    </div>
                    <div class="card-body">
                        <p class="card-text">Record your expenses to manage your budget.</p>
                        <a href="expenses.php" class="btn btn-primary d-inline-flex align-items-center justify-content-center"><svg class="bi me-2" width="24" height="24"><use xlink:href="#expense"></use></svg>Add Expense</a>
                    </div>
                </div>
            </div>

            <div class="col-lg-4 mb-4">
                <div class="card text-center">
                    <div class="card-header card-header-font-size">
                        Check Balance
                    </div>
                    <div class="card-body">
                        <p class="card-text">View your income vs. expense balance.</p>
                        <a href="balance.php" class="btn btn-primary d-inline-flex align-items-center justify-content-center"><svg class="me-2 bi" width="24" height="24"><use xlink:href="#table"></use></svg>Check Balance</a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Settings shortcut -->
        <div class="row mt-5">
            <div class="col text-center">
                <a href="settings.php" class="btn btn-secondary btn-lg d-inline-flex align-items-center justify-content-center"><svg class="me-2 bi" width="24" height="24"><use xlink:href="#settings"></use></svg>Go to Settings</a>
            </div>
        </div>

    </main>

    <footer class="text-center py-4 mt-auto">
        <p>&copy; 2024 CloudBudget - Manage Your Finances Easily</p>
    </footer>
        
        <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
        <script src="https://unpkg.com/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.9.0/js/bootstrap-datepicker.min.js"></script>
        <script src="./js/main.js"></script>
    </body>
</html>