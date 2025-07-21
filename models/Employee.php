<?php
class Employee {
    private $conn;
    private $table_name = "employees";

    // Object properties
    public $id;
    public $first_name;
    public $last_name;
    public $email;
    public $phone;
    public $dob;
    public $job_title;
    public $employment_type;
    public $hire_date;
    public $salary;
    public $bank_name;
    public $account_number;
    public $created_at;
    public $updated_at;

    public function __construct($pdo) {
        $this->conn = $pdo;
    }

    // Get all employees
    public function getAll() {
        $query = "SELECT * FROM " . $this->table_name;
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt;
    }

    public function getById($id) {
    $query = "SELECT * FROM employees WHERE id = :id";
    $stmt = $this->conn->prepare($query);
    $stmt->bindParam(':id', $id);
    $stmt->execute();
    return $stmt->fetch(PDO::FETCH_ASSOC);
}


    // Create employee
    public function create() {
        $query = "INSERT INTO " . $this->table_name . " 
        (first_name, last_name, email, phone, dob, job_title, employment_type, hire_date, salary, bank_name, account_number)
        VALUES 
        (:first_name, :last_name, :email, :phone, :dob, :job_title, :employment_type, :hire_date, :salary, :bank_name, :account_number)";

        $stmt = $this->conn->prepare($query);

        // Sanitize input
        $this->first_name = htmlspecialchars(strip_tags($this->first_name));
        $this->last_name = htmlspecialchars(strip_tags($this->last_name));
        $this->email = htmlspecialchars(strip_tags($this->email));
        $this->phone = htmlspecialchars(strip_tags($this->phone));
        $this->dob = htmlspecialchars(strip_tags($this->dob));
        $this->job_title = htmlspecialchars(strip_tags($this->job_title));
        $this->employment_type = htmlspecialchars(strip_tags($this->employment_type));
        $this->hire_date = htmlspecialchars(strip_tags($this->hire_date));
        $this->salary = htmlspecialchars(strip_tags($this->salary));
        $this->bank_name = htmlspecialchars(strip_tags($this->bank_name));
        $this->account_number = htmlspecialchars(strip_tags($this->account_number));

        // Bind parameters
        $stmt->bindParam(":first_name", $this->first_name);
        $stmt->bindParam(":last_name", $this->last_name);
        $stmt->bindParam(":email", $this->email);
        $stmt->bindParam(":phone", $this->phone);
        $stmt->bindParam(":dob", $this->dob);
        $stmt->bindParam(":job_title", $this->job_title);
        $stmt->bindParam(":employment_type", $this->employment_type);
        $stmt->bindParam(":hire_date", $this->hire_date);
        $stmt->bindParam(":salary", $this->salary);
        $stmt->bindParam(":bank_name", $this->bank_name);
        $stmt->bindParam(":account_number", $this->account_number);

        if ($stmt->execute()) {
            return true;
        }

        return false;
    }
}
?>
