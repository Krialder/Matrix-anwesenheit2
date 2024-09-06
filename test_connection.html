<!DOCTYPE html>
<html>
<head>
    <title>Database Connection Test</title>
</head>
<body>
    <h2>Database Connection Test</h2>
    <form method="post">
        <button type="submit" name="test_connection">Test Connection</button>
    </form>

    <?php
    if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['test_connection'])) {
        class Database {
            private $host = '192.168.2.150';
            private $db_name = 'kde_test2';
            private $username = 'kde';
            private $password = 'kde';
            public $conn;

            public function getConnection() {
                $this->conn = null;
                try {
                    $this->conn = new PDO("mysql:host=" . $this->host . ";dbname=" . $this->db_name, $this->username, $this->password);
                    $this->conn->exec("set names utf8");
                    echo "<p>Connection successful!</p>";
                } catch (PDOException $exception) {
                    echo "<p>Connection error: " . $exception->getMessage() . "</p>";
                }
                return $this->conn;
            }
        }

        $database = new Database();
        $database->getConnection();
    }
    ?>
</body>
</html>