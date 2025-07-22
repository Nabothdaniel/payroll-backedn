<?php
// models/SalaryPayout.php

class SalaryPayout
{
    private $conn;
    private $table = "salary_payouts";

    public function __construct($db)
    {
        $this->conn = $db;
    }

    public function create($data)
    {
        $query = "INSERT INTO $this->table 
            (salary_cycle_id, employee_id, base_salary, tax_amount, benefits_total, deductions_total, net_salary, release_date)
            VALUES 
            (:salary_cycle_id, :employee_id, :base_salary, :tax_amount, :benefits_total, :deductions_total  , :net_salary, :release_date)";

        $stmt = $this->conn->prepare($query);

        $stmt->bindParam(":salary_cycle_id", $data['salary_cycle_id']);
        $stmt->bindParam(":employee_id", $data['employee_id']);
        $stmt->bindParam(":base_salary", $data['base_salary']);
        $stmt->bindParam(":tax_amount", $data['tax_amount']);
        $stmt->bindParam(":benefits_total", $data['benefits_total']);
        $stmt->bindParam(":deductions_total", $data['deductions_total']);
        $stmt->bindParam(":net_salary", $data['net_salary']);
        $stmt->bindParam(":release_date", $data['release_date']);

        return $stmt->execute();
    }

    public function existsForEmployeeAndCycle($employeeId, $month, $year)
    {
        $query = "
        SELECT sp.id 
        FROM salary_payouts sp 
        JOIN salary_cycles sc ON sp.salary_cycle_id = sc.id 
        WHERE sp.employee_id = :employeeId AND sc.cycle_month = :month AND sc.cycle_year = :year
        LIMIT 1
    ";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':employeeId', $employeeId);
        $stmt->bindParam(':month', $month);
        $stmt->bindParam(':year', $year);
        $stmt->execute();
        return $stmt->rowCount() > 0;
    }

    public function updatePaidStatuses()
    {
        $today = date('Y-m-d');

        $sql = "UPDATE salary_payouts 
            SET status = 'paid' 
            WHERE release_date <= :today 
              AND status = 'unpaid'";

        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':today', $today);
        return $stmt->execute();
    }

    public function confirmSalaryByEmployeeId($employee_id)
    {
        $query = "UPDATE salary_payouts 
              SET status = 'paid' 
              WHERE employee_id = :employee_id 
              AND status = 'unpaid' 
              AND release_date <= CURDATE()";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':employee_id', $employee_id);
        return $stmt->execute();
    }




    public function getByCycleId($salary_cycle_id)
    {
        $query = "SELECT * FROM salary_payouts WHERE salary_cycle_id = :salary_cycle_id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":salary_cycle_id", $salary_cycle_id);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}

