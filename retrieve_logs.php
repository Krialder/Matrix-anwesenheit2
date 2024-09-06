<?php

class Database 
{
    private $host = '192.168.2.150';
    private $db_name = 'kde_test2';
    private $username = 'kde';
    private $password = 'kde';
    public $conn;

    // Establish a database connection
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
            echo json_encode(array("message" => "Connection error: " . $exception->getMessage()));
            exit();
        }
        return $this->conn;
    }
}

class User 
{
    private $conn;
    private $table_name = "users";

    // Constructor to initialize database connection
    public function __construct($db) 
    {
        $this->conn = $db;
    }

    // Retrieve user data by username
    public function getUserByUsername($username) 
    {
        $query = "SELECT * FROM " . $this->table_name . " WHERE name = :username";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':username', $username);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
}

class LoginAttempt 
{
    private $conn;
    private $table_name = "login_attempts";

    // Constructor to initialize database connection
    public function __construct($db) 
    {
        $this->conn = $db;
    }

    // Retrieve login attempts by RFID
    public function getAttempts($rfid) 
    {
        $query = "SELECT * FROM " . $this->table_name . " WHERE rfid = :rfid";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':rfid', $rfid);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // Update login attempts
    public function updateAttempts($rfid, $attempts, $block_until = null, $block_count = 0) 
    {
        $query = "UPDATE " . $this->table_name . " SET attempts = :attempts, last_attempt = NOW(), block_until = :block_until, block_count = :block_count WHERE rfid = :rfid";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':rfid', $rfid);
        $stmt->bindParam(':attempts', $attempts);
        $stmt->bindParam(':block_until', $block_until);
        $stmt->bindParam(':block_count', $block_count);
        $stmt->execute();
    }

    // Reset login attempts
    public function resetAttempts($rfid) 
    {
        $query = "UPDATE " . $this->table_name . " SET attempts = 5, block_until = NULL, block_count = 0 WHERE rfid = :rfid";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':rfid', $rfid);
        $stmt->execute();
    }

    // Create a new login attempt record
    public function createAttempt($rfid) 
    {
        $query = "INSERT INTO " . $this->table_name . " (rfid) VALUES (:rfid)";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':rfid', $rfid);
        $stmt->execute();
    }
}

class Log 
{
    private $conn;
    private $table_name = "logs";

    // Constructor to initialize database connection
    public function __construct($db) 
    {
        $this->conn = $db;
    }

    // Retrieve all logs
    public function getLogs() 
    {
        $query = "SELECT * FROM " . $this->table_name . " ORDER BY created_at DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();

        $logs = array();
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) 
        {
            $logs[] = $row;
        }
        return $logs;
    }
}

// Handle POST request for login
if ($_SERVER['REQUEST_METHOD'] === 'POST') 
{
    $database = new Database();
    $db = $database->getConnection();

    $username = $_POST['username'];
    $password = $_POST['password'];

    $user = new User($db);
    $userData = $user->getUserByUsername($username);

    if (!$userData) 
    {
        echo json_encode(array("message" => "Username does not exist."));
        exit;
    }

    $rfid = $userData['rfid'];
    $loginAttempt = new LoginAttempt($db);
    $attemptData = $loginAttempt->getAttempts($rfid);

    if (!$attemptData) 
    {
        $loginAttempt->createAttempt($rfid);
        $attemptData = $loginAttempt->getAttempts($rfid);
    }

    $current_time = new DateTime();
    $block_until = new DateTime($attemptData['block_until']);
    $last_attempt = new DateTime($attemptData['last_attempt']);

    // Check if user is blocked
    if ($current_time < $block_until) 
    {
        echo json_encode(array("message" => "User is blocked. Try again later."));
        exit;
    }

    // Reset attempts if last attempt was more than 2 minutes ago
    if ($current_time->getTimestamp() - $last_attempt->getTimestamp() > 120) 
    {
        $loginAttempt->resetAttempts($rfid);
        $attemptData = $loginAttempt->getAttempts($rfid);
    }

    // Check credentials
    if ($username === 'user' && $password === 'pass') 
    {
        $loginAttempt->resetAttempts($rfid);
        echo json_encode(array("message" => "Login successful."));
    } 
    else 
    {
        $attempts = $attemptData['attempts'] - 1;
        $block_count = $attemptData['block_count'];

        // Block user if no attempts left
        if ($attempts <= 0) 
        {
            $block_count++;
            $block_until = $block_count >= 3 ? '9999-12-31 23:59:59' : (new DateTime())->modify('+1 minute')->format('Y-m-d H:i:s');
            $loginAttempt->updateAttempts($rfid, 5, $block_until, $block_count);
            echo json_encode(array("message" => "User is blocked. Try again later."));
        } 
        else 
        {
            $loginAttempt->updateAttempts($rfid, $attempts);
            echo json_encode(array("message" => "Invalid credentials. Attempts left: $attempts"));
        }
    }
} 
// Handle GET request for logs
else if ($_SERVER['REQUEST_METHOD'] === 'GET') 
{
    $database = new Database();
    $db = $database->getConnection();

    $log = new Log($db);
    $logs = $log->getLogs();

    echo json_encode($logs);
} 
else 
{
    echo json_encode(array("message" => "Invalid request method."));
}

?>