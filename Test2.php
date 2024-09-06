<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Database Connection Test</title>
</head>
<body>
    <h1>Database Connection Test</h1>
    <?php
    // Enable error reporting
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    error_reporting(E_ALL);

    // Check if PDO extension is enabled
    if (!extension_loaded('pdo_mysql')) {
        die("PDO MySQL extension is not enabled.");
    }

    try 
    {
        // Create connection
        $dsn = 'mysql:host=192.168.2.150;dbname=kde_test2';
        $username = 'kde';
        $password = 'kde';
        $options = array(
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        );

        echo "Attempting to connect...<br>";
        $conn = new PDO($dsn, $username, $password, $options);
        echo "Connection successful.<br>";

        // Define the SQL query
        $sql = "SELECT users.id, users.rfid, users.name, categorization.name as classification 
                FROM users 
                JOIN categorization ON users.classification_id = categorization.id";

        echo "Preparing SQL statement...<br>";
        $stmt = $conn->prepare($sql);
        echo "Executing SQL statement...<br>";
        $stmt->execute();

        // Check if there are results
        if ($stmt->rowCount() > 0) {
            echo "Fetching results...<br>";
            // Output data of each row
            while ($row = $stmt->fetch()) {
                echo "id: " . $row["id"] . " - Name: " . $row["name"] . " - Classification: " . $row["classification"] . "<br>";
            }
        } else {
            echo "0 results";
        }
    } 
    catch (PDOException $e) 
    {
        echo "Connection failed: " . $e->getMessage();
    }
    ?>
</body>
</html>