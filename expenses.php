<?php
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Pobieranie danych z formularza
    $amount = $_POST['amount'];
    $date = $_POST['date'];
	$paymentMethod = isset($_POST['paymentMethod']) ? $_POST['paymentMethod'] : null; // Pobranie metody płatności
    $category = isset($_POST['category']) ? $_POST['category'] : null; // Pobranie kategorii
    $comment = isset($_POST['comment']) ? $_POST['comment'] : null;

    // Walidacja amount
    if (!is_numeric($amount) || $amount <= 0) {
        $_SESSION['e_amount'] = "Invalid amount. It must be a positive number.";
        exit();
    }

    // Walidacja date
    $date_regex = '/^\d{4}-\d{2}-\d{2}$/';
    if (!preg_match($date_regex, $date)) {
        $_SESSION['e_date'] = "Invalid date format. Please use yyyy-mm-dd.";
        exit();
    }

    $date_parts = explode('-', $date);
    if (!checkdate($date_parts[1], $date_parts[2], $date_parts[0])) {
        $_SESSION['e_date'] = "Invalid date. Please enter a valid date.";
        exit();
    }
	
    $_SESSION['fr_amount'] = $amount;
    $_SESSION['fr_date'] = $date;
    $_SESSION['fr_payment_method'] = $paymentMethod;
    $_SESSION['fr_category'] = $category;
    $_SESSION['fr_comment'] = $comment;

    // Przetwarzanie danych
    // np. dodanie do bazy danych

    echo "Expense added successfully!";
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
                    <form>
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
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="paymentMethod" id="paymentMethod1" value="Cash" <?php
										if ((isset($_SESSION['fr_payment_method'])) && ($_SESSION['fr_payment_method'] === 'Cash')) {
										  'checked';
										  unset($_SESSION['fr_payment_method']);
										}
									?>>
                                    <label class="form-check-label" for="paymentMethod1">Cash</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="paymentMethod" id="paymentMethod2" value="Debit card" <?php
										if ((isset($_SESSION['fr_payment_method'])) && ($_SESSION['fr_payment_method'] === 'Debit card')) {
										  'checked';
										  unset($_SESSION['fr_payment_method']);
										}
									?>>
                                    <label class="form-check-label" for="paymentMethod2">Debit card</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="paymentMethod" id="paymentMethod3" value="Credit card" <?php
										if ((isset($_SESSION['fr_payment_method'])) && ($_SESSION['fr_payment_method'] === 'Credit card')) {
										  'checked';
										  unset($_SESSION['fr_payment_method']);
										}
									?>>	
                                    <label class="form-check-label" for="paymentMethod3">Credit card</label>
                                </div>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="category" class="form-label">Category:</label>
                            <select class="form-select" id="category" name="category" required> 
                                <option value="" disabled selected>Select category</option>
                                <option value="Food">Food</option>
                                <option value="Housing">Housing</option>
                                <option value="Transportation">Transportation</option>
                                <option value="Telecommunications">Telecommunications</option>
                                <option value="Healthcare">Healthcare</option>
                                <option value="Clothing">Clothing</option>
                                <option value="Hygiene">Hygiene</option>
                                <option value="Children">Children</option>
                                <option value="Entertainment">Entertainment</option>
                                <option value="Travel">Travel</option>
                                <option value="Training">Training</option>
                                <option value="Books">Books</option>
                                <option value="Savings">Savings</option>
                                <option value="For the golden autumn, i.e. retirement">For the golden autumn, i.e. retirement</option>
                                <option value="Debt repayment">Debt repayment</option>
                                <option value="Donation">Donation</option>
                                <option value="Other expenses">Other expenses</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="comment" class="form-label">Comment (optional):</label>
                            <input type="text" class="form-control" id="comment" name="comment" placeholder="Enter comment">
                        </div>
                        <button type="submit" class="btn btn-primary">Add</button>
                        <button type="reset" class="btn btn-secondary">Cancel</button>
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