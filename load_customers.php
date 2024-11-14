<?php
$mysqli = new mysqli("localhost", "root", "", "test");

if ($mysqli->connect_error) {
    die("Connection failed: " . $mysqli->connect_error);
}

$sql = "SELECT ID, CustName, Email, Status FROM CusMaster";
$result = $mysqli->query($sql);

if (!$result) {
    die("Error in SQL query: " . $mysqli->error);
}

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        echo "<tr>
                <td><input type='checkbox' name='selected[]' value='{$row['ID']}'></td>
                <td>{$row['ID']}</td>
                <td>{$row['CustName']}</td>
                <td>{$row['Email']}</td>
                <td>{$row['Status']}</td>
              </tr>";
    }
} else {
    echo "<tr><td colspan='5'>No customers found</td></tr>";
}

$mysqli->close();
?>
