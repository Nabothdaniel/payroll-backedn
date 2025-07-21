<?php
require_once __DIR__ . '/../models/User.php';
require_once __DIR__ . '/../utils/ResponseHandler.php';
require_once __DIR__ . '/../utils/JWTHandler.php';
require_once __DIR__ . '/../config/Database.php';

class AuthController {
    private $db;
    private $user;

    public function __construct() {
        $database = new Database();
        $this->db = $database->getConnection();
        $this->user = new User($this->db);
    }

   public function register() {
    $data = json_decode(file_get_contents("php://input"));

    // Enforce all fields are present
    $requiredFields = ['full_name', 'email', 'password', 'phone', 'company_name', 'job_title', 'industry', 'company_size'];

    foreach ($requiredFields as $field) {
        if (!isset($data->$field) || empty(trim($data->$field))) {
            ResponseHandler::sendError(400, ucfirst(str_replace("_", " ", $field)) . " is required.");
            return;
        }
    }

    // Check if email already exists
    if ($this->user->findByEmail($data->email)) {
        ResponseHandler::sendError(400, "Email already exists.");
        return;
    }

    // Assign values
    $this->user->full_name = $data->full_name;
    $this->user->email = $data->email;
    $this->user->password = $data->password;
    $this->user->phone = $data->phone;
    $this->user->company_name = $data->company_name;
    $this->user->job_title = $data->job_title;
    $this->user->industry = $data->industry;
    $this->user->company_size = $data->company_size;

    try {
        $user_id = $this->user->create();
        if ($user_id) {
            $token = JWTHandler::generateToken($user_id, $data->email);

            ResponseHandler::sendSuccess([
                "token" => $token,
                "email" => $data->email,
                "full_name" => $data->full_name
            ], "User registered successfully");
        } else {
            ResponseHandler::sendError(500, "Unable to create user.");
        }
    } catch (Exception $e) {
        ResponseHandler::sendError(500, "Error creating user: " . $e->getMessage());
    }
}

    public function login() {
        $data = json_decode(file_get_contents("php://input"));

        if (!isset($data->email) || !isset($data->password)) {
            ResponseHandler::sendError(400, "Email and password are required.");
            return;
        }

        try {
            $user = $this->user->findByEmail($data->email);

            if ($user && password_verify($data->password, $user['password'])) {
                $token = JWTHandler::generateToken($user['id'], $user['email']);

                ResponseHandler::sendSuccess([
                    "token" => $token,
                    "email" => $user['email'],
                    "full_name" => $user['full_name']
                ], "Login successful");
            } else {
                ResponseHandler::sendError(401, "Invalid credentials.");
            }
        } catch (Exception $e) {
            ResponseHandler::sendError(500, "Error during login: " . $e->getMessage());
        }
    }
}
