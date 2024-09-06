<?php
$dsn = 'mysql:host=192.168.2.150;dbname=kde_test2';
$username = 'kde';
$password = 'kde';

try {
    $conn = new PDO($dsn, $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo "Connected successfully";
} catch (PDOException $exception) {
    echo "Connection error: " . $exception->getMessage();
}
