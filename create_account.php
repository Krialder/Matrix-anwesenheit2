
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

    public $id;

    public function __construct($db) 
    {
        $this->conn = $db;
    }

    public function delete() 
    {
        $query = "DELETE FROM " . $this->table_name . " WHERE id = :id";
        $stmt = $this->conn->prepare($query);

        $this->id = htmlspecialchars(strip_tags($this->id));

        $stmt->bindParam(':id', $this->id);

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

    $user->id = $_POST['id'];

    if ($user->delete()) 
    {
        echo json_encode(array("message" => "User was deleted."));
    } 
    else 
    {
        echo json_encode(array("message" => "Unable to delete user."));
    }
} 
else 
{
    echo json_encode(array("message" => "Invalid request method."));
}

?>
