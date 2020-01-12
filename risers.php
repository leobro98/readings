<?php
require ('doc-parts.php');
require ('sql-func.php');
require ('func.php');

$kyRecord = CheckPasswordAndGetKyRecord($_POST['ky'], $_POST['password']);
$ky       = mysqli_result($kyRecord, 0, "KyCode");
$password = mysqli_result($kyRecord, 0, 'Password');
$kyName   = mysqli_result($kyRecord, 0, 'Name');
$flats    = mysqli_result($kyRecord, 0, 'Flats');

if (! isset($_POST['year'], $_POST['month'])) {
    Redirect('period.php');
}
$year  = htmlspecialchars($_POST['year']);
$month = htmlspecialchars($_POST['month']);

$connection = ConnectToDb();

if (isset($_POST["periodId"])) {
    // insert or update readings (the request is made from this page's form)
    $periodId = htmlspecialchars($_POST["periodId"]);
    //TODO: prevent SQL injection!
    $locked   = htmlspecialchars($_POST["locked"]);
    $periodExists = true;
    
	for ($i = 0; $i < $flats; $i++) {
		if (isset($_POST["start$i"]) && isset($_POST["end$i"])) {
			$riserId = $i;
			$start   = str_replace(',', '.', htmlspecialchars($_POST["start$i"]));
			$end     = str_replace(',', '.', htmlspecialchars($_POST["end$i"]));
			
			if ($riserId != '' && $start != '' && $end != '') {
				$insertResult = mysqli_query($connection, "INSERT INTO RiserReading VALUES (" .
					$periodId         . ", " .
					$riserId          . ", " .
					SqlNumber($start) . ", " .
					SqlNumber($end)   . ")"
					);
				if (! $insertResult) {
					// the key exists, then update the existing record
					$updateResult = mysqli_query($connection, "UPDATE RiserReading SET" .
						"   Start = " . SqlNumber($start) . "," .
						"   End = "   . SqlNumber($end)   .
						" WHERE PeriodId = $periodId AND" .
						"   RiserId = $riserId");
					if (! $updateResult)
						ReportInvalidQuery();
				}
			}
		}
	}
	
	if (isset($_POST["houseStart"]) && isset($_POST["houseEnd"])) {
		$start = str_replace(',', '.', htmlspecialchars($_POST["houseStart"]));
		$end   = str_replace(',', '.', htmlspecialchars($_POST["houseEnd"]));
		
		$insertResult = mysqli_query($connection, "INSERT INTO HouseReading VALUES ('" .
			$ky               . "', " .
			$periodId         . ", " .
			SqlNumber($start) . ", " .
			SqlNumber($end)   . ")"
			);
		if (! $insertResult) {
			// the key exists, then update the existing record
			$updateResult = mysqli_query($connection, "UPDATE HouseReading SET" .
				"   Start = " . SqlNumber($start) . "," .
				"   End = "   . SqlNumber($end)   .
				" WHERE PeriodId = $periodId AND" .
				"   KyCode = '$ky'");
			if (! $updateResult)
				ReportInvalidQuery();
		}
	}
	
	if (isset($_POST['edit']))
		$editId = htmlspecialchars($_POST['edit']);
}
else {
    // find the period and show readings (the request is made from the period page)
    $period = mysqli_query($connection, "SELECT * FROM Period WHERE KyCode = '$ky' AND Year = $year AND Month = $month");
    if (! $period)
        ReportInvalidQuery();
    if (mysqli_num_rows($period) == 0)
        $periodExists = false;
    else {
        $periodExists = true;
        $periodId = mysqli_result($period, 0, 'PeriodId');
        $locked   = mysqli_result($period, 0, 'Locked');
    }
}

DocHead("N&auml;idud - $kyName", "readings.js");
$seeBottom = false;
?>

<h1>N&auml;idud</h1>
<h2><?=$kyName?></h2>

<div id="params">
    Aasta: <?=$year?>  &nbsp;&nbsp;
    Kuu:   <?=$month?> &nbsp;&nbsp;
    <form method="post" action="period.php" id="formPeriod">
        <input type="hidden" name="ky" value="<?=$ky?>" />
        <input type="hidden" name="password" value="<?=$password?>" />

        <input type="submit" value="Vali teine periood" /> &nbsp;&nbsp;
		<span class="scrn">
			<a href="risers-down.php?ky=<?=$ky?>&period=<?=$periodId?>">Lae alla (eng)</a> &nbsp;&nbsp;
			<a href="risers-down.php?ky=<?=$ky?>&period=<?=$periodId?>&dec=est">Lae alla (est)</a>
		</span>
    </form>
<?php
    if (! $periodExists) {
?>
        <br /><br />
        Sellist perioodi ei ole salvestatud.
</div>
<?php
        DocumentEnd('', $seeBottom);
        mysqli_close($connection);
        exit();
    }
	
	$newReadings = mysqli_query($connection, 
			"SELECT R.RiserId, R.Name,
				COALESCE(RR.Start,
					(SELECT End FROM RiserReading WHERE PeriodId =
						(SELECT PeriodId FROM Period 
						 WHERE ((Year = $year AND Month < $month) OR Year < $year) AND KyCode = '$ky'
						 ORDER BY Year DESC, Month DESC
						 LIMIT 1)
					AND RiserId = R.RiserId)
				) AS Start,
				RR.End
			FROM
				(SELECT * FROM Riser WHERE KyCode = '$ky') AS R
					LEFT JOIN
				(SELECT * FROM RiserReading WHERE PeriodId = $periodId) AS RR
					ON R.RiserId = RR.RiserId
			ORDER BY R.RiserId");
	$houseReadings = mysqli_query($connection, 
			"SELECT
				COALESCE(HR.Start,
					(SELECT End FROM HouseReading WHERE PeriodId =
						(SELECT PeriodId FROM Period 
						 WHERE ((Year = $year AND Month < $month) OR Year < $year) AND KyCode = '$ky'
						 ORDER BY Year DESC, Month DESC
						 LIMIT 1)
					AND KyCode = '$ky')
				) AS Start,
				HR.End
			FROM
				(SELECT * FROM KY WHERE KyCode = '$ky') AS K
					LEFT JOIN
				(SELECT * FROM HouseReading WHERE PeriodId = $periodId) AS HR
					ON K.KyCode = HR.KyCode");
	mysqli_close($connection);
?>

</div>
<br />

<form method="post" action="<?=CurrentPage()?>" onsubmit="setNoWarning()" name="formReadings">
    <input type="hidden" name="ky"       value="<?=$ky?>" />
    <input type="hidden" name="password" value="<?=$password?>" />
    <input type="hidden" name="year"     value="<?=$year?>" />
    <input type="hidden" name="month"    value="<?=$month?>" />
    <input type="hidden" name="periodId" value="<?=$periodId?>" />
    <input type="hidden" name="locked"   value="<?=$locked?>" />

<table border="0" cellspacing="3" align="center" class="php">
    <tr>
        <th>P&uuml;stik</th>
        <th>Algn&auml;it</th>
        <th>L&otilde;ppn&auml;it</th>
        <th>Kulu</th>
        <th id="edit"></th>
    </tr>
<?php
    $total = 0;
    $totalWrong = 0;
    $riserCount = mysqli_num_rows($newReadings);
	$filledCount = 0;
	$firstInput = true;
	
    for ($i = 0; $i < $riserCount; $i++) {
        echo "    <tr>\r\n";
        echo '        <td align="right">' . mysqli_result($newReadings, $i, 'Name') . '</td>' . "\r\n";
		
		$id      = mysqli_result($newReadings, $i, 'RiserId');
		$start   = mysqli_result($newReadings, $i, 'Start');
		$end     = mysqli_result($newReadings, $i, 'End');
		$volume  = round($end - $start, 2);
		$filled  = false;
		$inputId = '';
		
		if (($end == null || $editId == $id) && ! $locked) {
			if ($firstInput) {
				$inputId = ' id="select"';
				$firstInput = false;
			}
			echo '        <td><input type="text" class="riser" name="start' . $id . '" value="' . $start .
					'"' . $inputId . ' /></td>' . "\r\n";
			echo '        <td><input type="text" class="riser" name="end'   . $id . '" value="' . $end   .
					'" /></td>' . "\r\n";
		}
		else {
			$filled = true;
			$filledCount++;
			echo '        <td>' . Check($start, $volume) . '</td>' . "\r\n";
			echo '        <td>' . Check($end,   $volume) . '</td>' . "\r\n";
		}
		
		if ($volume < 0)
			$totalWrong = -1;
		$total += $volume;
		echo '        <td>' . Check($volume, $volume) . '</td>' . "\r\n";
		
		if (! $locked && $filled) {
			echo '        <td class="img">' . "\r\n";
			echo '            <img src="edit.gif" onclick="makeEditable(' . $id
					. ')" width="15" height="13" alt="Redigeeri" title="Redigeeri" />' . "\r\n";
			echo '        </td>' . "\r\n";
		} else {
			echo '        <td class="img"></td>' . "\r\n";
		}
        
        echo "    </tr>\r\n";
    }
?>
    <tr id="total">
<?php
    if ($filledCount < $riserCount) {
?>
        <td><strong>Kokku <span class="scrn"><?=$filledCount?></span></strong></td>
        <script type="text/javascript" language="JavaScript">var warning = true;</script>
<?php
    } else {
?>
        <td>Kokku <span class="scrn"><?=$filledCount?></span></td>
<?php
    }
?>
        <td colspan="2"></td>
        <td><?=Check($total, $totalWrong)?></td>
		<td></td>
    </tr>
    <tr>
		<td colspan="5">&nbsp;</td>
    </tr>
	<tr id="house">
		<td>Maja &uuml;ldveem&otilde;&otilde;tja</td>
<?php
	$houseStart  = mysqli_result($houseReadings, 0, 'Start');
	$houseEnd    = mysqli_result($houseReadings, 0, 'End');
	$houseVolume = round($houseEnd - $houseStart, 2);
	$filled = false;
	
	if (($houseEnd == null || $editId == 'house') && ! $locked) {
		echo '        <td><input type="text" class="riser" name="houseStart" value="' . $houseStart .
				'" /></td>' . "\r\n";
		echo '        <td><input type="text" class="riser" name="houseEnd"   value="' . $houseEnd   .
				'" /></td>' . "\r\n";
	}
	else {
		$filled = true;
		echo '        <td>' . Check($houseStart, $houseVolume) . '</td>' . "\r\n";
		echo '        <td>' . Check($houseEnd,   $houseVolume) . '</td>' . "\r\n";
	}
	
	echo '        <td>' . Check($houseVolume, $houseVolume) . '</td>' . "\r\n";
	
	if (! $locked && $filled) {
		echo '        <td class="img">' . "\r\n";
		echo '            <img src="edit.gif" onclick="makeEditable(' . "'house'"
				. ')" width="15" height="13" alt="Redigeeri" title="Redigeeri" />' . "\r\n";
		echo '        </td>' . "\r\n";
	} else {
		echo '        <td class="img"></td>' . "\r\n";
	}
?>
    </tr>
</table>
<br />

<?php
if (! $locked) {
?>
<div id="buttons">
    <input type="submit" value="Salvesta" />
</div>
<?php
}
?>

	<input type="hidden" name="edit" />
</form>

<?php
DocumentEnd('select', $seeBottom);
?>
