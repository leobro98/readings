<?php
function ConnectToDb () {
    $db = 'admin_readings';
    $dbUser = 'admin_lb';
    $dbPass = 'prettysure';

    $connection = mysqli_connect('localhost', $dbUser, $dbPass, $db);
	if (!$connection) {
		echo "Error: Unable to connect to MySQL." . PHP_EOL;
		echo "ErrNo: " . mysqli_connect_errno() . PHP_EOL;
		echo "Error: " . mysqli_connect_error() . PHP_EOL;
		exit;
	}
	mysqli_set_charset($connection, 'utf8');
	return $connection;
}

function CheckPasswordAndGetKyRecord($kyCodeUnsafe, $passwordUnsafe) {
	$passOk = false;

	if (isset($kyCodeUnsafe, $passwordUnsafe)) {
		$connection = ConnectToDb();
		$ky = htmlspecialchars($kyCodeUnsafe);
		$password = htmlspecialchars($passwordUnsafe);
		$kyRecord = mysqli_query($connection, "SELECT * FROM KY WHERE KyCode = '$ky'");
		
		if (! $kyRecord)
			ReportInvalidQuery($connection);
		else if (mysqli_num_rows($kyRecord) > 0) {
			$storedPassword = mysqli_result($kyRecord, 0, 'Password');

			if ($password == $storedPassword)
				$passOk = true;
		}
	}
	if (! $passOk) {
		Redirect('login.php');
	}
	return $kyRecord;
}

function GetFirstMissingFlat($connection, $periodId) {
	// list of non-entered flats
	$missedFlats = mysqli_query($connection,
		"SELECT FlatId + 1 AS MissingFlat " .
		"FROM Reading AS R " .
		"WHERE PeriodId = $periodId " .
			"AND (" .
				"SELECT FlatId " .
				"FROM Reading " .
				"WHERE PeriodId = $periodId " .
					"AND FlatId > R.FlatId " .
				"ORDER BY FlatId LIMIT 1" .
			") > FlatId + 1 " .
		"ORDER BY FlatId LIMIT 1");
	if ($missedFlats && mysqli_num_rows($missedFlats) > 0) {
		$nextId = mysqli_result($missedFlats, 0, 'MissingFlat');
	} else {
		$nextId = 0;
	}
	
	return $nextId;
}

function mysqli_result($result, $row, $field=0) {
    $result->data_seek($row);
    $dataRow = $result->fetch_array();
    return $dataRow[$field];
}

function SqlString ($string) {
    if (isset($string) && $string != '')
        return "'" . $string . "'";
    else
        return 'NULL';
}

function SqlNumber ($numericString) {
    if (isset($numericString) && $numericString != '')
        return $numericString;
    else
        return 'NULL';
}

function SqlDate ($dateString) {
    if (strlen($dateString) == 0)
        return 'NULL';

    // presumes that the date is in the format "d.M.yy" or "d.M.yyyy"
    $dateParts = explode('.', $dateString);

    $day = $dateParts[0];
    if ( strlen ($day) == 1)
        $day = '0' . $day;
    
    $month = $dateParts[1];
    if ( strlen ($month) == 1)
        $month = '0' . $month;
    
    $year = $dateParts[2];
    if ( strlen ($year) == 2)
        $year = '20' . $year;
    
    return "'" . $year . '-' . $month . '-' . $day . "'";
}

function ReportInvalidQuery($connection) {
    echo '<br />Invalid query: ' . mysqli_error($connection) . '<br />';
}
?>
