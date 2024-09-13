<?php
	session_start();
	
	if ((!isset($_SESSION['loggedIn'])) && (!$_SESSION['loggedIn']==true)){
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
	try {
		$pdo = new PDO($dsn, $config['user'], $config['password'], $options);
	
		$user_id_form_db = $_SESSION['id'];
	
		$stmt = $pdo->prepare("SELECT * FROM incomes_category_assigned_to_users WHERE user_id = ?");
		$stmt->execute([$user_id_form_db]);
	
		if ($stmt->rowCount() > 0) {
			$incomes_category_user_data = $stmt->fetchAll();
			unset($_SESSION['incomes_category_error']);
		} else {
			$_SESSION['incomes_category_error'] = '<span class="error">Unable to load category names</span>';
		}
	} catch (PDOException $e) {
		echo "Error: " . $e->getMessage();
	}
	
	if ($_SERVER['REQUEST_METHOD'] == 'POST') {
		$validation_passed = true;

		$amount = filter_var($_POST['amount'], FILTER_VALIDATE_FLOAT);
		$date = $_POST['date'];
		$category = isset($_POST['category']) ? $_POST['category'] : null;
		$comment = isset($_POST['comment']) ? $_POST['comment'] : null;

		if (!is_numeric($amount) || $amount <= 0) {
			$validation_passed = false;
			$_SESSION['e_amount'] = "Invalid amount. It must be a positive number.";
		}

		$date_regex = '/^\d{4}-\d{2}-\d{2}$/';
		if (!preg_match($date_regex, $date)) {
			$validation_passed = false;
			$_SESSION['e_date'] = "Invalid date format. Please use yyyy-mm-dd.";
		}

		$date_parts = explode('-', $date);
		if (!checkdate($date_parts[1], $date_parts[2], $date_parts[0])) {
			$validation_passed = false;
			$_SESSION['e_date'] = "Invalid date. Please enter a valid date.";
		}

		if (!isset($_POST['category']) || !in_array($_POST['category'], array_column($incomes_category_user_data, 'name'))) {
			$validation_passed = false;
			$_SESSION['e_category'] = "Invalid category selected.";
		}

		$comment = isset($_POST['comment']) ? htmlspecialchars($_POST['comment'], ENT_QUOTES, 'UTF-8') : null;
				
		try {	
			if ($validation_passed) {    
				$stmt = $pdo->prepare("
				INSERT INTO incomes (user_id, income_category_assigned_to_user_id, amount, date_of_income, income_comment) 
				VALUES (?, 
					(SELECT id FROM incomes_category_assigned_to_users WHERE user_id = ? AND name = ? LIMIT 1),  
					?, ?, ?
				)");

				if ($stmt->execute([$user_id_form_db, $user_id_form_db, $category, $amount, $date, $comment])){    			
					$_SESSION['success_message'] = "Income added successfully!";
					
					unset($_SESSION['fr_amount']);
					unset($_SESSION['fr_date']);
					unset($_SESSION['fr_category']);
					unset($_SESSION['fr_comment']);
				} else {    
					$_SESSION['fr_amount'] = $amount;
					$_SESSION['fr_date'] = $date;
					$_SESSION['fr_category'] = $category;
					$_SESSION['fr_comment'] = $comment;
					throw new Exception("Failed to add income.");
					//throw new Exception("Failed to add income.". $e->getMessage());
				}
			}
		} catch (Exception $e) {
			echo '<span class="error">Server error! We apologize for the inconvenience and ask you to add incomes later!</span>';
			//echo '<br />Developer Information: ' . htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8');
		}
	}

	if (isset($_SESSION['success_message'])) {
		$successMessage = $_SESSION['success_message'];
		unset($_SESSION['success_message']);
	}
?>

<!DOCTYPE html>
<html lang="en">

<head id="header"></head>

    <body>

        <div id="navbar"></div>

        <main>
            <div class="container mt-5">
                <div class="form-container">
                    <h1 class="mb-4">Add Income</h1>
                    <form method="post" id="form">
                        <div class="mb-3">
                            <label for="amount" class="form-label">Amount:</label>
                            <input type="number" step="0.01" min="0.01" class="form-control" id="amount" name= "amount" placeholder="Enter amount" required value="<?php
								if (isset($_SESSION['fr_amount'])) {
								  echo htmlspecialchars($_SESSION['fr_amount'], ENT_QUOTES, 'UTF-8');
								  unset($_SESSION['fr_amount']);
								}
							?>">
							
							<?php
								if (isset($_SESSION['e_amount'])) {
									echo '<div class="error">'.htmlspecialchars($_SESSION['e_amount'], ENT_QUOTES, 'UTF-8').'</div>';
									unset($_SESSION['e_amount']);
								}
							?>
						</div>
                        <div class="mb-3">
                            <label for="date" class="form-label">Date:</label>
                            <div class="input-group">
                                <input type="text" class="form-control" id="date" name="date" pattern="(?:19|20)\d{2}-(0[1-9]|1[0-2])-(0[1-9]|[12]\d|3[01])" placeholder="yyyy-mm-dd" required value="<?php
									if (isset($_SESSION['fr_date'])) {
									  echo htmlspecialchars($_SESSION['fr_date'], ENT_QUOTES, 'UTF-8');
									  unset($_SESSION['fr_date']);
									}
								?>">
                                <button type="button" class="btn btn-outline-secondary" id="openDateModal">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-calendar3" viewBox="0 0 16 16">
                                        <path d="M14 0H2a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V2a2 2 0 0 0-2-2M1 3.857C1 3.384 1.448 3 2 3h12c.552 0 1 .384 1 .857v10.286c0 .473-.448.857-1 .857H2c-.552 0-1-.384-1-.857z"/>
                                        <path d="M6.5 7a1 1 0 1 0 0-2 1 1 0 0 0 0 2m3 0a1 1 0 1 0 0-2 1 1 0 0 0 0 2m3 0a1 1 0 1 0 0-2 1 1 0 0 0 0 2m-9 3a1 1 0 1 0 0-2 1 1 0 0 0 0 2m3 0a1 1 0 1 0 0-2 1 1 0 0 0 0 2m3 0a1 1 0 1 0 0-2 1 1 0 0 0 0 2m3 0a1 1 0 1 0 0-2 1 1 0 0 0 0 2m-9 3a1 1 0 1 0 0-2 1 1 0 0 0 0 2m3 0a1 1 0 1 0 0-2 1 1 0 0 0 0 2m3 0a1 1 0 1 0 0-2 1 1 0 0 0 0 2"/>
                                    </svg>
                                </button>
								<?php
									if (isset($_SESSION['e_date'])) {
										echo '<div class="error">'.htmlspecialchars($_SESSION['e_date'], ENT_QUOTES, 'UTF-8').'</div>';
										unset($_SESSION['e_date']);
									}
								?>
                            </div>
                        </div>
						
                        <div class="mb-3">
							<label for="category" class="form-label">Category:</label>
							<select class="form-select" id="category" name="category" required>
								<option value="" disabled <?php echo !isset($_SESSION['fr_category']) ? 'selected' : ''; ?>>Select category</option>
								<?php
								foreach ($incomes_category_user_data as $expenses_category) {
									$selected = isset($_SESSION['fr_category']) && $_SESSION['fr_category'] == $expenses_category['name'] ? 'selected' : '';
									echo '<option value="' . htmlspecialchars($expenses_category['name'], ENT_QUOTES, 'UTF-8') . '" ' . $selected . '>' . htmlspecialchars($expenses_category['name'], ENT_QUOTES, 'UTF-8') . '</option>';
								}
								unset($_SESSION['fr_category']);
								?>
								
							</select>
							
							<?php
								if (isset($_SESSION['e_category'])) {
									echo '<div class="error">'.htmlspecialchars($_SESSION['e_category'], ENT_QUOTES, 'UTF-8').'</div>';
									unset($_SESSION['e_category']);
								}
							?>
						</div>

						<div class="mb-3">
							<label for="comment" class="form-label">Comment (optional):</label>
							<input type="text" class="form-control" id="comment" name="comment" placeholder="Enter comment"
								value="<?php
								if (isset($_SESSION['fr_comment'])) {
									echo htmlspecialchars($_SESSION['fr_comment'], ENT_QUOTES, 'UTF-8');
									unset($_SESSION['fr_comment']);
								}
								?>">
						</div>

                        <button type="submit" class="btn btn-primary">Add</button>
                        <button type="button" class="btn btn-secondary" id="cancelButton">Cancel</button>
                    </form>
                </div>
            </div>
            <div class="bottom-spacing"></div>
        </main>
        
        <!-- Modal for date picker -->
        <div class="modal fade" id="dateModal" tabindex="-1" aria-labelledby="dateModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-body">
                        <div id="modalDatepicker"></div>
                    </div>
                </div>
            </div>
        </div>
		
		<!-- Modal for success message -->
		<div class="modal fade" id="successModal" tabindex="-1" role="dialog" aria-labelledby="successModalLabel" aria-hidden="true">
			<div class="modal-dialog modal-dialog-centered" style="min-width:300px" role="document"> 
				<div class="modal-content">
					<div class="modal-body d-flex justify-content-center align-items-center modal-dimensions">
						<?php if (isset($successMessage)): ?>
							<p class="text-center success-message" id="successMessage"><?php echo $successMessage; ?></p>
						<?php endif; ?>
					</div>
					<div class="modal-footer justify-content-center">
						<button type="button" class="btn btn-secondary" data-dismiss="modal" onclick="$('#successModal').modal('hide');">Close</button>
					</div>
				</div>
			</div>
		</div>
		
		<script>
			var successMessage = "<?php echo isset($successMessage) ? $successMessage : ''; ?>";
		</script>
        
        <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
        <script src="https://unpkg.com/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.9.0/js/bootstrap-datepicker.min.js"></script>
        <script src="./js/main.js"></script>
    </body>
</html>