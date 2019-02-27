<?php 

ob_start();

try {

	// PDO: PHP Database Object, (mysql link, username, password)
	$con = new PDO("mysql:dbname=xi_search;host=localhost", "root", "");
	// convert error to warning, and continue executing
	$con->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
} catch(PDOException $exc) {
	echo "Connection failed: " . $exc->getMessage();
}

 ?>