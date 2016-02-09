<?php
$servername = "localhost"; /*"jobocracy-mariadb.cj2dmqgpebvk.us-east-1.rds.amazonaws.com"*/
$username = "root"; /*"jobocracy_admin"*/
$password = "59Hk9akq9KstUx2L"; /*" "*/ // In passwords document
$dbname = "jobocracy";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);
// Check connection
if ($conn->connect_error) {
   die("Connection failed: " . $conn->connect_error);
}

?>
