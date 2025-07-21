<?php
require_once __DIR__ . '/../models/Employee.php';
require_once __DIR__ . '/../utils/ResponseHandler.php';
require_once __DIR__ . '/../config/Database.php';

class EmployeeController {
    private $db;
    private $employee;

    public function __construct() {
        $database = new Database();
        $this->db = $database->getConnection();
        $this->employee = new Employee($this->db);
    }

    public function getAll() {
        try {
            $result = $this->employee->getAll();
            $employees = $result->fetchAll(PDO::FETCH_ASSOC);
            
            if($employees) {
                ResponseHandler::sendSuccess($employees);
            } else {
                ResponseHandler::sendSuccess([], "No employees found");
            }
        } catch(Exception $e) {
            ResponseHandler::sendError(500, "Error retrieving employees: " . $e->getMessage());
        }
    }

    public function create() {
        $data = json_decode(file_get_contents("php://input"));

        // Basic validation
        $requiredFields = [
            'first_name', 'last_name', 'email', 'phone', 'dob',
            'job_title', 'employment_type', 'hire_date', 'salary',
            'bank_name', 'account_number'
        ];

        foreach ($requiredFields as $field) {
            if (empty($data->$field)) {
                ResponseHandler::sendError(400, "Missing field: $field");
                return;
            }
        }

        // Assign fields
        $this->employee->first_name        = $data->first_name;
        $this->employee->last_name         = $data->last_name;
        $this->employee->email             = $data->email;
        $this->employee->phone             = $data->phone;
        $this->employee->dob               = $data->dob;
        $this->employee->job_title         = $data->job_title;
        $this->employee->employment_type   = $data->employment_type;
        $this->employee->hire_date         = $data->hire_date;
        $this->employee->salary            = $data->salary;
        $this->employee->bank_name         = $data->bank_name;
        $this->employee->account_number    = $data->account_number;

        try {
            if ($this->employee->create()) {
                ResponseHandler::sendSuccess([
                    "first_name"       => $data->first_name,
                    "last_name"        => $data->last_name,
                    "email"            => $data->email,
                    "job_title"        => $data->job_title,
                    "employment_type"  => $data->employment_type,
                    "salary"           => $data->salary,
                ], "Employee created successfully");
            } else {
                ResponseHandler::sendError(500, "Unable to create employee.");
            }
        } catch(Exception $e) {
            ResponseHandler::sendError(500, "Error creating employee: " . $e->getMessage());
        }
    }
}
?>
