<?php
require_once 'admin-api/db_connect.php';
echo 'Products table structure:' . PHP_EOL;
$result = $conn->query('DESCRIBE products');
while ($row = $result->fetch_assoc()) {
    echo $row['Field'] . ' - ' . $row['Type'] . PHP_EOL;
}
$conn->close();
?>