<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>Database Connection Test</h1>";

echo "Attempting to include auth/db_connect.php...<br>";
try {
    require_once 'auth/db_connect.php';
    echo "Success: auth/db_connect.php included.<br>";
} catch (Exception $e) {
    echo "Caught exception: " . $e->getMessage() . "<br>";
}

if (isset($conn)) {
    echo "Success: \$conn variable is set.<br>";
    if ($conn->connect_error) {
        echo "Connection Error: " . $conn->connect_error . "<br>";
    } else {
        echo "Database connected successfully!<br>";
        
        $res = $conn->query("SELECT DATABASE()");
        $row = $res->fetch_row();
        echo "Current Database: " . $row[0] . "<br>";
        
        echo "Checking tables...<br>";
        $res = $conn->query("SHOW TABLES");
        while($row = $res->fetch_row()) {
            echo "Table: " . $row[0] . "<br>";
        }
    }
} else {
    echo "Error: \$conn variable is NOT set.<br>";
}
?>
