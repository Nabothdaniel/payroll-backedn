<?php
require_once __DIR__ . '/../models/SalaryPayout.php';
require_once __DIR__ . '/../utils/ResponseHandler.php';
require_once __DIR__ . '/../config/Database.php';


class ConfirmSalaries {
    private $salaryPayout;

    public function __construct() {
        $database = new Database();
        $db = $database->getConnection();
        $this->salaryPayout = new SalaryPayout($db);
    }

    public function autoConfirmSalaries() {
        // Automatically mark unpaid salaries as paid if release_date has passed
        $updated = $this->salaryPayout->updatePaidStatuses();

        if ($updated) {
            return ResponseHandler::sendSuccess(null, "Eligible salary payouts marked as paid.");
        } else {
            return ResponseHandler::sendError(500, "Failed to update salary statuses.");
        }
    }

    public function confirmSalaryForEmployee($employee_id) {
    $confirmed = $this->salaryPayout->confirmSalaryByEmployeeId($employee_id);

    if ($confirmed) {
        return ResponseHandler::sendSuccess(null, "Salary payout marked as paid for employee ID: {$employee_id}");
    } else {
        return ResponseHandler::sendError(404, "No unpaid salary found for the employee or update failed.");
    }
}

}
?>
