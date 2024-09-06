<?php

class Database 
{
    private $host;
    private $db_name;
    private $username;
    private $password;
    public $conn;

    public function __construct() 
    {
        $this->host = '192.168.2.150';
        $this->db_name = 'kde_test2';
        $this->username = 'kde';
        $this->password = 'kde';
    }

    public function getConnection() 
    {
        $this->conn = null;
        try 
        {
            $this->conn = new PDO("mysql:host=" . $this->host . ";dbname=" . $this->db_name, $this->username, $this->password);
            $this->conn->exec("set names utf8");
        } 
        catch(PDOException $exception) 
        {
            error_log("Connection error: " . $exception->getMessage());
            echo json_encode(array("message" => "Database connection error."));
            exit();
        }
        return $this->conn;
    }
}

class User 
{
    private $conn;
    private $table_name = "users";

    public $username;
    public $password;

    public function __construct($db) 
    {
        $this->conn = $db;
    }

    public function usernameExists() 
    {
        $query = "SELECT * FROM " . $this->table_name . " WHERE name = :username";
        $stmt = $this->conn->prepare($query);

        $this->username = htmlspecialchars(strip_tags($this->username));
        $stmt->bindParam(':username', $this->username);

        $stmt->execute();

        return $stmt->rowCount() > 0;
    }

    public function login() 
    {
        $query = "SELECT * FROM " . $this->table_name . " WHERE name = :username";
        $stmt = $this->conn->prepare($query);

        $this->username = htmlspecialchars(strip_tags($this->username));
        $this->password = htmlspecialchars(strip_tags($this->password));

        $stmt->bindParam(':username', $this->username);

        $stmt->execute();

        if ($stmt->rowCount() > 0) 
        {
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            if (password_verify($this->password, $row['password'])) 
            {
                return true;
            }
        }
        return false;
    }
}

class Log 
{
    private $conn;
    private $table_name = "logs";

    public function __construct($db) 
    {
        $this->conn = $db;
    }

    public function logAttempt($username) 
    {
        $query = "INSERT INTO " . $this->table_name . " (username, attempt_time) VALUES (:username, NOW())";
        $stmt = $this->conn->prepare($query);

        $username = htmlspecialchars(strip_tags($username));
        $stmt->bindParam(':username', $username);

        $stmt->execute();
    }

    public function getRecentAttempts($username) 
    {
        $query = "SELECT COUNT(*) as attempt_count FROM " . $this->table_name . " WHERE username = :username AND attempt_time > (NOW() - INTERVAL 2 MINUTE)";
        $stmt = $this->conn->prepare($query);

        $username = htmlspecialchars(strip_tags($username));
        $stmt->bindParam(':username', $username);

        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        return $row['attempt_count'];
    }

    public function lockoutUser($username) 
    {
        $query = "INSERT INTO " . $this->table_name . " (username, attempt_time, lockout) VALUES (:username, NOW(), 1)";
        $stmt = $this->conn->prepare($query);

        $username = htmlspecialchars(strip_tags($username));
        $stmt->bindParam(':username', $username);

        $stmt->execute();
    }

    public function isUserLockedOut($username) 
    {
        $query = "SELECT COUNT(*) as lockout_count FROM " . $this->table_name . " WHERE username = :username AND lockout = 1 AND attempt_time > (NOW() - INTERVAL 1 MINUTE)";
        $stmt = $this->conn->prepare($query);

        $username = htmlspecialchars(strip_tags($username));
        $stmt->bindParam(':username', $username);

        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        return $row['lockout_count'] > 0;
    }

    public function resetAttempts($username) 
    {
        $query = "DELETE FROM " . $this->table_name . " WHERE username = :username AND attempt_time < (NOW() - INTERVAL 2 MINUTE)";
        $stmt = $this->conn->prepare($query);

        $username = htmlspecialchars(strip_tags($username));
        $stmt->bindParam(':username', $username);

        $stmt->execute();
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') 
{
    $database = new Database();
    $db = $database->getConnection();

    if ($db === null) {
        echo json_encode(array("message" => "Failed to connect to the server."));
        exit();
    }

    $user = new User($db);
    $log = new Log($db);

    $username = $_POST['username'];
    $password = $_POST['password'];

    $user->username = $username;
    $user->password = $password;

    if ($log->isUserLockedOut($username)) 
    {
        echo json_encode(array("message" => "You are locked out. Please try again after 1 minute."));
        exit();
    }

    if (!$user->usernameExists()) 
    {
        echo json_encode(array("message" => "Wrong Username."));
        exit();
    }

    $log->logAttempt($username);

    $attempts = $log->getRecentAttempts($username);

    if ($attempts >= 5) 
    {
        $log->lockoutUser($username);
        echo json_encode(array("message" => "Too many attempts. You are locked out for 1 minute."));
    } 
    else 
    {
        if ($user->login()) 
        {
            echo json_encode(array("message" => "Login successful."));
        } 
        else 
        {
            echo json_encode(array("message" => "Invalid username or password."));
        }
    }

    $log->resetAttempts($username);
} 
else 
{
    echo json_encode(array("message" => "Invalid request method."));
}
?>