<?php
	session_start();

	if ((!isset($_SESSION['loggedIn'])) || ($_SESSION['loggedIn'] !== true)) {
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

	// Default settings for dates
    $start_date = isset($_POST['start_date']) ? $_POST['start_date'] : date('Y-m-01'); // Beginning of the month
    $end_date = isset($_POST['end_date']) ? $_POST['end_date'] : date('Y-m-t'); // End of the month

    // Getting data from the incomes table
    $stmt = $pdo->prepare("
        SELECT 
            i.date_of_income, 
            ic.name AS income_category_name, 
            i.amount, 
            i.income_comment 
        FROM 
            incomes i
        JOIN 
            incomes_category_assigned_to_users ic ON i.income_category_assigned_to_user_id = ic.id
        WHERE 
            i.user_id = ? AND 
            i.date_of_income BETWEEN ? AND ?
    ");
    
    $stmt->execute([$user_id_form_db, $start_date, $end_date]);
    $incomes = $stmt->fetchAll();

    // Retrieving data from the expenses table
    $stmt = $pdo->prepare("
        SELECT 
            e.date_of_expense, 
            ec.name AS expense_category_name, 
            e.amount, 
            e.expense_comment 
        FROM 
            expenses e
        JOIN 
            expenses_category_assigned_to_users ec ON e.expense_category_assigned_to_user_id = ec.id
        WHERE 
            e.user_id = ? AND 
            e.date_of_expense BETWEEN ? AND ?
    ");
    
    $stmt->execute([$user_id_form_db, $start_date, $end_date]);
    $expenses = $stmt->fetchAll();
	
	// Preparing data for the chart
    $expenseCategories = [];
    $backgroundColors = [];

    foreach ($expenses as $expense) {
        $categoryName = $expense['expense_category_name'];
        $amount = (float)$expense['amount'];

        if (!isset($expenseCategories[$categoryName])) {
            $expenseCategories[$categoryName] = $amount;
            $backgroundColors[] = sprintf('rgba(%d, %d, %d, 0.7)', rand(0, 255), rand(0, 255), rand(0, 255));
        } else {
            $expenseCategories[$categoryName] += $amount;
        }
    }

    // Sort categories by amount (optional)
    arsort($expenseCategories);

    // Preparing data to be passed to JavaScript
    $labels = array_keys($expenseCategories);
    $data = array_values($expenseCategories);

    $totalIncome = array_sum(array_column($incomes, 'amount'));
    $totalExpenses = array_sum(array_column($expenses, 'amount'));
    $balance = $totalIncome - $totalExpenses;

	} catch (PDOException $e) {
		echo "Error: " . htmlspecialchars($e->getMessage());
	}
?>

<!DOCTYPE html>
<html lang="en">
<head id="header"></head>
<body>
    <div id="navbar"></div>

    <main>
        <div class="container mt-5 container-balanse ">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2>Balance Sheet</h2>
                <button class="btn btn-primary" id="selectPeriodBtn" data-bs-toggle="modal" data-bs-target="#periodModal">Select Period</button>
            </div>

            <div class="row">
                <div class="col-lg-6">
                    <h3>Income</h3>
                    <div style="overflow-x: auto">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <th>Source</th>
                                    <th>Description</th>
                                    <th class="text-end">Amount</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($incomes as $income): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($income['date_of_income']); ?></td>
                                        <td><?php echo htmlspecialchars($income['income_category_name']); ?></td>
                                        <td><?php echo htmlspecialchars($income['income_comment']); ?></td>
                                        <td class="text-end"><?php echo number_format($income['amount'], 2); ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                            <tfoot>
                                <tr class="table-summary-color">
                                    <th colspan="3" class="text-end">Total Income:</th>
                                    <th class="text-end"><?php echo number_format(array_sum(array_column($incomes, 'amount')), 2); ?></th>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>

                <div class="col-lg-6 expenses-table">
                    <h3>Expenses</h3>
                    <div class="table-responsive">
                      <table class="table table-striped">
                          <thead>
                              <tr>
                                  <th>Date</th>
                                  <th>Category</th>
                                  <th>Description</th>
                                  <th class="text-end">Amount</th>
                              </tr>
                          </thead>
                          <tbody>
                              <?php foreach ($expenses as $expense): ?>
                                  <tr>
                                      <td><?php echo htmlspecialchars($expense['date_of_expense']); ?></td>
                                      <td><?php echo htmlspecialchars($expense['expense_category_name']); ?></td>
                                      <td><?php echo htmlspecialchars($expense['expense_comment']); ?></td>
                                      <td class="text-end"><?php echo number_format($expense['amount'], 2); ?></td>
                                  </tr>
                              <?php endforeach; ?>
                          </tbody>
                          <tfoot>
                              <tr class="table-summary-color">
                                  <th colspan="3" class="text-end ">Total Expenses:</th>
                                  <th class="text-end"><?php echo number_format(array_sum(array_column($expenses, 'amount')), 2); ?></th>
                              </tr>
                          </tfoot>
                      </table>
                    </div>
                </div>

            </div>

            <!-- Modal for selecting period -->
            <div class="modal fade" id="periodModal" tabindex="-1" aria-labelledby="periodModalLabel" aria-hidden="true">
                <div class="modal-dialog">
                    <form method="POST" action="">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title" id="periodModalLabel">Select Period</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>

                            <div class="modal-body p-3">
                                <div class="d-flex flex-column align-items-center">
                                    <div class="w-100 mb-3">
                                        <label for="start_date" class="form-label m-0">Start Date:</label>
                                        <input type="date" id="start_date" name="start_date" class="form-control" required value="<?php echo isset($_POST['start_date']) ? $_POST['start_date'] : ''; ?>">
                                    </div>
                                    <div class="w-100">
                                        <label for="end_date" class="form-label m-0">End Date:</label>
                                        <input type="date" id="end_date" name="end_date" class="form-control" required value="<?php echo isset($_POST['end_date']) ? $_POST['end_date'] : ''; ?>">
                                    </div>
                                </div>
                            </div>

                            <div class="modal-footer d-flex justify-content-center">
								<button type="submit" class="btn btn-primary">Apply</button> <!-- Submit the form -->
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>                    
                            </div>

                        </div>
                    </form> 
                </div> 
            </div>

            <!-- Summary Table -->
            <?php
            $totalIncome = array_sum(array_column($incomes, 'amount'));
            $totalExpenses = array_sum(array_column($expenses, 'amount'));
            ?>
			
            <!-- Summary Table -->
            <h3 class="mt-4">Summary</h3>
            <table class="table table-bordered">
                <tbody>
                    <tr >
                        <th>Total Income</th>
                        <td class="text-end"><?php echo number_format($totalIncome, 2); ?></td>
                    </tr>
                    <tr >
                        <th>Total Expenses</th>
                        <td class="text-end"><?php echo number_format($totalExpenses, 2); ?></td>
                    </tr> 
                    <?php 
                    $balance = $totalIncome - $totalExpenses; 
                    ?>
                    <tr class="table-summary-color">
                        <th>Balance</th>
                        <td class="text-end"><strong><?php echo number_format($balance, 2); ?></strong></td> 
                    </tr> 
                </tbody> 
            </table>

            <!-- Pie Chart -->
			<div class="row mt-4">
				<h3>Expenses Distribution</h3>
				<div class="col-lg-8 offset-lg-2">	
					<div style="position: relative; height: 50vh; width: 100%;">
						<canvas id="expensesChart"></canvas>
					</div>
				</div>
			</div>

            <script>
                const labels = <?php echo json_encode($labels); ?>;
                const chartData = <?php echo json_encode($data); ?>;
                const backgroundColors = <?php echo json_encode($backgroundColors); ?>;
            </script>

        </div>

        <!-- Bottom spacing for layout purposes -->
        <div class="bottom-spacing"></div>

    </main>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://unpkg.com/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="./js/main.js"></script>
    <script src="./js/balanse.js"></script>
</body>

</html>