<?php
class User {
    private $conn;
    private $table_name = "users";

    // User Properties
    public $id;
    public $full_name;
    public $email;
    public $phone;
    public $password;
    public $company_name;
    public $job_title;
    public $industry;
    public $company_size;
    public $created_at;

    public function __construct($db) {
        $this->conn = $db;
    }

    public function create() {
        $query = "INSERT INTO " . $this->table_name . "
                 (full_name, email, phone, password, company_name, job_title, industry, company_size, created_at)
                 VALUES (:full_name, :email, :phone, :password, :company_name, :job_title, :industry, :company_size, NOW())";

        $stmt = $this->conn->prepare($query);

        // Sanitize
        $this->full_name = htmlspecialchars(strip_tags($this->full_name));
        $this->email = htmlspecialchars(strip_tags($this->email));
        $this->phone = htmlspecialchars(strip_tags($this->phone));
        $this->company_name = htmlspecialchars(strip_tags($this->company_name));
        $this->job_title = htmlspecialchars(strip_tags($this->job_title));
        $this->industry = htmlspecialchars(strip_tags($this->industry));
        $this->company_size = htmlspecialchars(strip_tags($this->company_size));
        $this->password = password_hash($this->password, PASSWORD_DEFAULT);

        // Bind values
        $stmt->bindParam(":full_name", $this->full_name);
        $stmt->bindParam(":email", $this->email);
        $stmt->bindParam(":phone", $this->phone);
        $stmt->bindParam(":password", $this->password);
        $stmt->bindParam(":company_name", $this->company_name);
        $stmt->bindParam(":job_title", $this->job_title);
        $stmt->bindParam(":industry", $this->industry);
        $stmt->bindParam(":company_size", $this->company_size);

        if ($stmt->execute()) {
            return $this->conn->lastInsertId();
        }
        return false;
    }

    public function findByEmail($email) {
        $query = "SELECT * FROM " . $this->table_name . " WHERE email = :email LIMIT 1";
        $stmt = $this->conn->prepare($query);

        $email = htmlspecialchars(strip_tags($email));
        $stmt->bindParam(":email", $email);

        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
}
?>