<?php
require_once __DIR__ . '/../models/SalaryCircle.php';
require_once __DIR__ . '/../models/SalaryPayout.php';
require_once __DIR__ . '/../models/Employee.php';
require_once __DIR__ . '/../config/Database.php';
require_once __DIR__ . '/../utils/ResponseHandler.php';

class SalaryCycleController
{
    private $salaryCycle;
    private $employee;
    private $salaryPayout;

    public function __construct()
    {
        $database = new Database();
        $db = $database->getConnection();
        $this->salaryCycle = new SalaryCycle($db);
        $this->employee = new Employee($db);
        $this->salaryPayout = new SalaryPayout($db);
    }

    public function generateCycle()
    {
        $data = json_decode(file_get_contents("php://input"));
        $month = $data->month ?? null;
        $year  = $data->year ?? null;

        if (!$month || !$year) {
            return ResponseHandler::sendError(400, "Month and year are required.");
        }

        // Prevent duplicate cycle
        if ($this->salaryCycle->existsForMonthYear($month, $year)) {
            return ResponseHandler::sendError(409, "Salary cycle already exists for $month/$year.");
        }

        $employees = $this->employee->getAll();
        $releaseDate = date("$year-$month-t");

        // Create the salary cycle
        $this->salaryCycle->cycle_month = $month;
        $this->salaryCycle->cycle_year = $year;
        $this->salaryCycle->salary_release_date = $releaseDate;

        if (!$this->salaryCycle->create()) {
            return ResponseHandler::sendError(500, "Failed to create salary cycle.");
        }

        $cycleId = $this->salaryCycle->getLastInsertedId();

        $totalSalary = 0;
        $payouts = [];

        foreach ($employees as $emp) {
            $baseSalary = $emp['salary'];
            $tax = $baseSalary * 0.05;
            $benefits_total = $baseSalary * 0.03;
            $deductions_total = $tax + 100; // 5% tax + ₦100 flat
            $netSalary = $baseSalary + $benefits_total - $deductions_total;

            $totalSalary += $netSalary;
            $status = 'unpaid'; // always unpaid when generating

            $payoutData = [
                'salary_cycle_id' => $cycleId,
                'employee_id' => $emp['id'],
                'base_salary' => $baseSalary,
                'tax_amount' => $tax,
                'benefits_total' => $benefits_total,
                'deductions_total' => $deductions_total,
                'net_salary' => $netSalary,
                'release_date' => $releaseDate,
                'status' => $status
            ];

            // Save payout to DB
            $this->salaryPayout->create($payoutData);


            // Add to response array
            $payouts[] = [
                'employee_id' => $emp['id'],
                'base_salary' => $baseSalary,
                'tax_amount' => $tax,
                'benefits_total' => $benefits_total,
                'deductions_total' => $deductions_total,
                'net_salary' => $netSalary,
                'release_date' => $releaseDate
            ];
        }

        // Update cycle with total salary
        $this->salaryCycle->updateTotalSalary($cycleId, $totalSalary);

        return ResponseHandler::sendSuccess([
            'salary_cycle_id' => $cycleId,
            'month' => $month,
            'year' => $year,
            'release_date' => $releaseDate,
            'total_salary' => $totalSalary,
            'payouts' => $payouts  // ← Return array of employee payouts
        ], "Salary cycle generated and individual payouts recorded.");
    }


    public function getAllCycles()
    {
        $stmt = $this->salaryCycle->getAll();
        $cycles = $stmt->fetchAll(PDO::FETCH_ASSOC);
        return ResponseHandler::sendSuccess($cycles);
    }

    public function getCycleByMonthYear($month, $year)
    {
        $cycle = $this->salaryCycle->getByMonthYear($month, $year);
        if ($cycle) {
            return ResponseHandler::sendSuccess($cycle);
        }
        return ResponseHandler::sendError(404, "Cycle not found.");
    }
    public function getPayoutsByCycle($cycle_id)
    {
        $payouts = $this->salaryPayout->getByCycleId($cycle_id);
        $this->salaryPayout->updateStatusesBasedOnDate(); // auto update unpaid → paid

        return ResponseHandler::sendSuccess($payouts);
    }

    public function deleteCycle($cycle_id)
    {
        $success = $this->salaryCycle->delete($cycle_id);
        if ($success) {
            return ResponseHandler::sendSuccess([], "Salary cycle and payouts deleted.");
        }
        return ResponseHandler::sendError(500, "Failed to delete cycle.");
    }
}
