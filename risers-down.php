<?php
require ('doc-parts.php');
require ('sql-func.php');
require ('func.php');

function Est($sourceString) {
	return str_replace('.', ',', $sourceString);
}

$connection = ConnectToDb();

if (isset($_GET["period"]))
	$periodId = htmlspecialchars($_GET['period']);
else {
	echo "Period is not set.<br />";
	mysqli_close($connection);
	exit();
}
$ky = htmlspecialchars($_GET['ky']);

// read period row for additional control on the client side
$periodRow = mysqli_query($connection, "SELECT * FROM Period WHERE PeriodId = $periodId");
if (! $periodRow) {
    ReportInvalidQuery();
    mysqli_close($connection);
    exit();
}
if (mysqli_num_rows($periodRow) == 0) {
    echo 'The period does not exist.<br />';
    mysqli_close($connection);
    exit();
}
else {
    $year  = mysqli_result($periodRow, 0, 'Year');
    $month = mysqli_result($periodRow, 0, 'Month');
}

// read readings
$newReadings = mysqli_query($connection, "SELECT * FROM RiserReading WHERE PeriodId = $periodId ORDER BY RiserId");
if (! $newReadings) {
	ReportInvalidQuery();
	mysqli_close($connection);
	exit();
}
if (mysqli_num_rows($newReadings) == 0) {
    echo 'There is no data in the period.<br />';
	mysqli_close($connection);
	exit();
}
else {
	header('Content-type: application/octet-stream');//
	header('Content-Description: File Transfer');
	// supply a recommended filename and force the browser to display the save dialog
	header('Content-Disposition: attachment; filename="' . $ky . '-risers.csv"'); 
	header('Content-Transfer-Encoding: binary');
	header('Expires: -1');
	header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
	header('Pragma: public');
	ob_clean();
	flush();
	
	if ($_GET['dec'] == 'est') {
		// Estonian decimal symbol and list separator
		$separator = ';';
	}
	else if ($_GET['dec'] == 'n') {
		// English decimal symbol, but Estonian list separator - for Natalia
		$separator = ';';
	}
	else {
		// English settings
		$separator = ',';
	}
	
    echo $ky . $separator . $year . $separator . $month . "\r\n";
	echo 'RiserId' . $separator . 'Start' . $separator . "End\r\n";
	
	for ($i = 0; $i < mysqli_num_rows($newReadings); $i++) {
		echo mysqli_result($newReadings, $i, 'RiserId') . $separator;
		
		if ($_GET['dec'] == 'est') {
			echo Est(mysqli_result($newReadings, $i, 'Start')) . $separator;
			echo Est(mysqli_result($newReadings, $i, 'End'));
		}
		else {
			echo mysqli_result($newReadings, $i, 'Start') . $separator;
			echo mysqli_result($newReadings, $i, 'End');
		}
		echo "\r\n";
	}
	
	// row for house main reader
	$houseReadings = mysqli_query($connection, "SELECT * FROM HouseReading WHERE KyCode = '$ky' AND PeriodId = $periodId");
	if (! $houseReadings) {
		ReportInvalidQuery();
		mysqli_close($connection);
		exit();
	}
	if (mysqli_num_rows($houseReadings) > 0) {
		echo 'House' . $separator;
		
		if ($_GET['dec'] == 'est') {
			echo Est(mysqli_result($houseReadings, 0, 'Start')) . $separator;
			echo Est(mysqli_result($houseReadings, 0, 'End'));
		}
		else {
			echo mysqli_result($houseReadings, 0, 'Start') . $separator;
			echo mysqli_result($houseReadings, 0, 'End');
		}
		echo "\r\n";
	}
	
	mysqli_close($connection);
}
?>
