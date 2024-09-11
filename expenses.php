<?php //TO DOO dodanie wydatku do bazy danych, ustawić aktualną datę po kliknięciu w Cencel
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
	
		// Pobieranie danych z tabeli expenses_category_assigned_to_users
		$stmt = $pdo->prepare("SELECT * FROM expenses_category_assigned_to_users WHERE user_id = ?");
		$stmt->execute([$user_id_form_db]);
	
		if ($stmt->rowCount() > 0) {
			$expenses_category_user_data = $stmt->fetchAll();
			unset($_SESSION['expenses_category_error']);
		} else {
			$_SESSION['expenses_category_error'] = '<span class="error">Unable to load category names</span>';
		}
	
		// Pobieranie danych z tabeli payment_methods_assigned_to_users
		$stmt = $pdo->prepare("SELECT * FROM payment_methods_assigned_to_users WHERE user_id = ?");
		$stmt->execute([$user_id_form_db]);
	
		if ($stmt->rowCount() > 0) {
			$payment_methods_user_data = $stmt->fetchAll();
			unset($_SESSION['payment_methods_error']);
		} else {
			$_SESSION['payment_methods_error'] = '<span class="error">Unable to load payment methods</span>';
		}
	} catch (PDOException $e) {
		echo "Error: " . $e->getMessage();
	}
	
	if ($_SERVER['REQUEST_METHOD'] == 'POST') {
		$validation_passed = true;
		// Pobieranie danych z formularza
		$amount = filter_var($_POST['amount'], FILTER_VALIDATE_FLOAT);
		$date = $_POST['date'];
		$paymentMethod = isset($_POST['paymentMethod']) ? $_POST['paymentMethod'] : null; // Pobranie metody płatności
		$category = isset($_POST['category']) ? $_POST['category'] : null; // Pobranie kategorii
		$comment = isset($_POST['comment']) ? $_POST['comment'] : null;

		// Walidacja amount
		if (!is_numeric($amount) || $amount <= 0) {
			$validation_passed = false;
			$_SESSION['e_amount'] = "Invalid amount. It must be a positive number.";
		}

		// Walidacja date
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
		
		// Walidacja metody płatności
		if (!isset($_POST['paymentMethod']) || !in_array($_POST['paymentMethod'], array_column($payment_methods_user_data, 'name'))) {
			$validation_passed = false;
			$_SESSION['e_paymentMethod'] = "Invalid payment method.";
		}

		// Walidacja kategorii
		if (!isset($_POST['category']) || !in_array($_POST['category'], array_column($expenses_category_user_data, 'name'))) {
			$validation_passed = false;
			$_SESSION['e_category'] = "Invalid category selected.";
		}

		// Walidacja komentarza (opcjonalne)
		$comment = isset($_POST['comment']) ? htmlspecialchars($_POST['comment'], ENT_QUOTES, 'UTF-8') : null;
		
		$_SESSION['fr_amount'] = $amount;
		$_SESSION['fr_date'] = $date;
		$_SESSION['fr_payment_method'] = $paymentMethod;
		$_SESSION['fr_category'] = $category;
		$_SESSION['fr_comment'] = $comment;
		
		try {	
			if ($validation_passed) {    
				// Hurra, wszystkie testy zaliczone, dodajemy gracza do bazy
				$stmt = $pdo->prepare("
				INSERT INTO expenses (user_id, expense_category_assigned_to_user_id, payment_method_assigned_to_user_id, amount, date_of_expense, expense_comment) 
				VALUES (?, 
					(SELECT id FROM expenses_category_assigned_to_users WHERE user_id = ? AND name = ? LIMIT 1), 
					(SELECT id FROM payment_methods_assigned_to_users WHERE user_id = ? AND name = ? LIMIT 1), 
					?, ?, ?
				)");

				if ($stmt->execute([$user_id_form_db, $user_id_form_db, $category, $user_id_form_db, $paymentMethod, $amount, $date, $comment])){    
					echo "Expense added successfully!";
					
					unset($_SESSION['fr_amount']);
					unset($_SESSION['fr_date']);
					unset($_SESSION['fr_payment_method']);
					unset($_SESSION['fr_category']);
					unset($_SESSION['fr_comment']);
					//echo  "Kategoria: ".$category.", Metoda płatności: ". $paymentMethod;///////////////////////////////////////////////////////////
					header('Location: expenses.php');
					exit();
				} else {    
					throw new Exception("Failed to add expense." . $e->getMessage());
				}
			}
		} catch (Exception $e) {
			echo '<span class="error">Server error! We apologize for the inconvenience and ask you to add expenses later!</span>';
			//echo '<br />Developer Information: ' . htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8');
		}
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
                    <h1 class="mb-4">Add Expense</h1>
                    <form method="post">
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
                            <label class="form-label">Payment method:</label>
                            <div>
								<?php if (isset($payment_methods_user_data)):
									$i = 1;
									foreach ($payment_methods_user_data as $payment_method): ?>
										<div class="form-check">
											<input class="form-check-input" type="radio" name="paymentMethod" id="paymentMethod<?php echo $i; ?>" required value="<?php echo $payment_method['name']; ?>" 
											<?php 
												if (isset($_SESSION['fr_payment_method']) && $_SESSION['fr_payment_method'] === $payment_method['name']) {
													echo 'checked';
													unset($_SESSION['fr_payment_method']);
												}
											?>>
											<label class="form-check-label" for="paymentMethod<?php echo $i; ?>"><?php echo $payment_method['name']; ?></label>
										</div>
										<?php $i++;
									endforeach;
								endif; ?>
							</div>

							<?php
								if (isset($_SESSION['e_paymentMethod'])) {
									echo '<div class="error">'.htmlspecialchars($_SESSION['e_paymentMethod'], ENT_QUOTES, 'UTF-8').'</div>';
									unset($_SESSION['e_paymentMethod']);
								}
							?>
                        </div>
						
                        <div class="mb-3">
							<label for="category" class="form-label">Category:</label>
							<select class="form-select" id="category" name="category" required>
								<option value="" disabled <?php echo !isset($_SESSION['fr_category']) ? 'selected' : ''; ?>>Select category</option>
								<?php
								foreach ($expenses_category_user_data as $expenses_category) {
									// Sprawdzamy, czy dana kategoria była wcześniej wybrana
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
                        <button type="reset" id="cancelButton" class="btn btn-secondary">Cancel</button>
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
        
        <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
        <script src="https://unpkg.com/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.9.0/js/bootstrap-datepicker.min.js"></script>
        <script src="./js/main.js"></script>
    </body>
</html>