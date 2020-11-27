<!DOCTYPE html>
<html>
<head>
	<title></title>
</head>
<body>
<?php 
	include("DBConnect.php");
	$con = Connect();
	echo "...CONECTANDO A LA BASE";

	$sql = "SELECT * FROM Score 
			ORDER BY scoreValue DESC";
	$result = mysqli_query($con, $sql) or die("<b>Error:</b> Error al conseguir el tablero: <br/>" . mysqli_error($con));

	mysqli_close($con);
	$rank = 0;
	while($row = mysqli_fetch_assoc($result)){
		/*$rank++;
		$con = Connect();
		echo "...ACTUALIZANDO TABLERO";

		$sql = "call AssignRank('$row[nameScore]', $row[ScoreValue])";
		$result2 = mysqli_query($con, $sql) or die("<b>Error:</b> Error al conseguir el tablero: <br/>" . mysqli_error($con));

		mysqli_close($con);

		echo "<p>$rank)  $row[nameScore]--------------- $row[ScoreValue]</p>";*/
		echo " $row[nameScore]--------------- $row[ScoreValue]";
	}

 ?>

</body>
</html>