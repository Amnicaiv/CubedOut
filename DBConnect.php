<?php 

function Connect(){
	$user 	  = "hY3k6Wrshn";
	$password = "GKzAhYbKal";
	$server   = "remotemysql.com";
	$database = "hY3k6Wrshn";

	$conection = mysqli_connect($server,$user,$password) or die ("error al conectar ".mysql_error());
	mysqli_select_db($conection,$database);

	return $conection;
}

 ?>