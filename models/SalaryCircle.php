<?php
// models/SalaryCycle.php

class SalaryCycle {
    private $conn;
    private $table = "salary_cycles";

    public $id;
    public $cycle_month;
    public $cycle_year;
    public $total_salary;
    public $salary_release_date;

    public function __construct($db) {
        $this->conn = $db;
    }

    public function create() {
        $query = "INSERT INTO $this->table (cycle_month, cycle_year, total_salary, salary_release_date)
                  VALUES (:month, :year, 0, :release_date)";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":month", $this->cycle_month);
        $stmt->bindParam(":year", $this->cycle_year);
        $stmt->bindParam(":release_date", $this->salary_release_date);

        if ($stmt->execute()) {
            $this->id = $this->conn->lastInsertId();
            return true;
        }

        return false;
    }

    public function getLastInsertedId() {
        return $this->id;
    }

    public function existsForMonthYear($month, $year) {
    $query = "SELECT id FROM salary_cycles WHERE cycle_month = :month AND cycle_year = :year LIMIT 1";
    $stmt = $this->conn->prepare($query);
    $stmt->bindParam(':month', $month);
    $stmt->bindParam(':year', $year);
    $stmt->execute();
    return $stmt->rowCount() > 0;
}


    public function updateTotalSalary($cycleId, $amount) {
        $query = "UPDATE $this->table SET total_salary = :total WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":total", $amount);
        $stmt->bindParam(":id", $cycleId);
        return $stmt->execute();
    }
      public function getAll() {
        $query = "SELECT * FROM $this->table ORDER BY cycle_year DESC, cycle_month DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getByMonthYear($month, $year) {
    $query = "SELECT * FROM $this->table WHERE cycle_month = :month AND cycle_year = :year LIMIT 1";
    $stmt = $this->conn->prepare($query);
    $stmt->bindParam(":month", $month);
    $stmt->bindParam(":year", $year);
    $stmt->execute();
    return $stmt->fetch(PDO::FETCH_ASSOC); // returns one record or false
}

    public function delete($id) {
    // First delete all payouts
    $stmt = $this->conn->prepare("DELETE FROM salary_payouts WHERE salary_cycle_id = :id");
    $stmt->bindParam(":id", $id);
    $stmt->execute();

    // Then delete the cycle
    $stmt2 = $this->conn->prepare("DELETE FROM $this->table WHERE id = :id");
    $stmt2->bindParam(":id", $id);
    return $stmt2->execute();
}

}
