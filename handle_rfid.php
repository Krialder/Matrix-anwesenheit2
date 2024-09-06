<?php

class Database 
{
    private $host = '192.168.2.150';
    private $db_name = 'kde_test2';
    private $username = 'kde';
    private $password = 'kde';
    public $conn;

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
            echo "Connection error: " . $exception->getMessage();
        }
        return $this->conn;
    }
}

class User 
{
    private $conn;
    private $table_name = "users";

    public $rfid;
    public $name;
    public $classification_id;

    public function __construct($db) 
    {
        $this->conn = $db;
    }

    public function create() 
    {
        $query = "INSERT INTO " . $this->table_name . " (rfid, name, classification_id) VALUES (:rfid, :name, :classification_id)";
        $stmt = $this->conn->prepare($query);

        $this->rfid = htmlspecialchars(strip_tags($this->rfid));
        $this->name = htmlspecialchars(strip_tags($this->name));
        $this->classification_id = htmlspecialchars(strip_tags($this->classification_id));

        $stmt->bindParam(':rfid', $this->rfid);
        $stmt->bindParam(':name', $this->name);
        $stmt->bindParam(':classification_id', $this->classification_id);

        if ($stmt->execute()) 
        {
            return true;
        }
        return false;
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') 
{
    $database = new Database();
    $db = $database->getConnection();

    $user = new User($db);

    $user->rfid = $_POST['rfid'];
    $user->name = $_POST['name'];
    $user->classification_id = $_POST['classification_id'];

    if ($user->create()) 
    {
        echo json_encode(array("message" => "User was created."));
    } 
    else 
    {
        echo json_encode(array("message" => "Unable to create user."));
    }
} 
else 
{
    echo json_encode(array("message" => "Invalid request method."));
}

?>