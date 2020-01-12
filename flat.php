<?php
require ('doc-parts.php');
require ('sql-func.php');
require ('func.php');

$connection = ConnectToDb();
$ky = htmlspecialchars($_POST['ky']);
$password = htmlspecialchars($_POST['password']);

$kyRecord = CheckPasswordAndGetKyRecord($ky, $password);

$kyName     = mysqli_result($kyRecord, 0, "Name");
$flats      = mysqli_result($kyRecord, 0, "Flats");
$isKitch    = mysqli_result($kyRecord, 0, "Kitchen");
$isKitchHot = mysqli_result($kyRecord, 0, "KitchenHot");
$isBath     = mysqli_result($kyRecord, 0, "Bath");
$isBathHot  = mysqli_result($kyRecord, 0, "BathHot");
$isGas      = mysqli_result($kyRecord, 0, "Gas");
$isPeople   = mysqli_result($kyRecord, 0, "People");

if (! isset($_POST["flatId"]))
    Redirect('period.php');

$flatId = htmlspecialchars($_POST["flatId"]);
$flatReadings = mysqli_query($connection, "SELECT * FROM " .
    "(SELECT Reading.*, Period.Year, Period.Month FROM Reading " .
	"INNER JOIN Period ON Reading.PeriodId = Period.PeriodId " .
	"WHERE Period.KyCode = '$ky' " .
	"    AND Reading.FlatId = $flatId " .
    "ORDER BY Reading.PeriodId DESC " .
    "LIMIT 24) TwoYears " .
    "ORDER BY PeriodId");
if (! $flatReadings)
	ReportInvalidQuery();
if (mysqli_num_rows($flatReadings) == 0)
	$flatExists = false;
else
	$flatExists = true;

DocHead("Korteri n&auml;idud - $kyName", 'readings.js');
$seeBottom = false;
?>

<h1>Korteri n&auml;idud</h1>
<h2><?=$kyName?></h2>

<div id="params">
    <div style="margin-bottom: 7px">Korter: <?=$flatId?></div>
    <form method="post" action="period.php">
        <input type="hidden" name="ky" value="<?=$ky?>" />
        <input type="hidden" name="password" value="<?=$password?>" />

		<span class="scrn">
			<input type="submit" value="Vali teine korter" />
		</span>
    </form>
<?php
    if (! $flatExists) {
?>
        <br /><br />
        Sellist korteri andmeid ei ole salvestatud.
</div>
<?php
        DocumentEnd('', $seeBottom);
        mysqli_close($connection);
        exit();
    }
?>
</div>
<br />

<table border="0" cellspacing="3" align="center" class="php">
    <tr>
        <th rowspan="3">Aasta</th>
        <th rowspan="3">Kuu</th>
<?php
$waterColumnCount = 0;

if ($isKitch) {
	if ($isKitchHot) {
		echo '        <th colspan="4">K&ouml;&ouml;k</th>' . "\r\n";
		$waterColumnCount += 4;
	} else {
		echo '        <th colspan="2">K&ouml;&ouml;k</th>' . "\r\n";
		$waterColumnCount += 2;
	}
}
if ($isBath) {
	if ($isBathHot) {
		echo '        <th colspan="4">Vannituba</th>' . "\r\n";
		$waterColumnCount += 4;
	} else {
		echo '        <th colspan="2">Vannituba</th>' . "\r\n";
		$waterColumnCount += 2;
	}
}
if ($isKitch || $isBath) {
	if ($isKitchHot || $isBathHot) {
		echo '        <th colspan="2">Veekulu</th>' . "\r\n";
	} else {
		echo '        <th>Veekulu</th>' . "\r\n";
	}
}
if ($isGas) {
	echo '        <th colspan="2" rowspan="2">Gaas</th>' . "\r\n";
	echo '        <th rowspan="3">Gaasi<br />kulu</th>' . "\r\n";
}
if ($isPeople) {
	echo '        <th rowspan="3">Elanike</th>' . "\r\n";
}
echo '        <th rowspan="3" class="button-cell"></th>' . "\r\n";
echo "    </tr>\r\n";
echo "    <tr>\r\n";
if ($isKitch) {
	echo '        <th colspan="2">K&uuml;lm vesi</th>' . "\r\n";
	if ($isKitchHot) {
		echo '        <th colspan="2">Soe vesi</th>' . "\r\n";
	}
}
if ($isBath) {
	echo '        <th colspan="2">K&uuml;lm vesi</th>' . "\r\n";
	if ($isBathHot) {
		echo '        <th colspan="2">Soe vesi</th>' . "\r\n";
	}
}
if ($isKitch || $isBath) {
	echo '        <th rowspan="2">Kokku</th>' . "\r\n";
	if ($isKitchHot || $isBathHot) {
		echo '        <th rowspan="2">s.h. soe</th>' . "\r\n";
	}
}
echo "    </tr>\r\n";
echo "    <tr>\r\n";
if ($isKitch) {
	echo "        <th>Algn&auml;it</th>\r\n";
	echo "        <th>L&otilde;ppn&auml;it</th>\r\n";
	if ($isKitchHot) {
		echo "        <th>Algn&auml;it</th>\r\n";
		echo "        <th>L&otilde;ppn&auml;it</th>\r\n";
	}
}
if ($isBath) {
	echo "        <th>Algn&auml;it</th>\r\n";
	echo "        <th>L&otilde;ppn&auml;it</th>\r\n";
	if ($isBathHot) {
		echo "        <th>Algn&auml;it</th>\r\n";
		echo "        <th>L&otilde;ppn&auml;it</th>\r\n";
	}
}
if ($isGas) {
	echo "        <th>Algn&auml;it</th>\r\n";
	echo "        <th>L&otilde;ppn&auml;it</th>\r\n";
}
echo "    </tr>\r\n";

$totalCold = 0;
$totalHot  = 0;
$totalGas = 0;
$totalPeople = 0;

$readingsCount = mysqli_num_rows($flatReadings);
for ($i = 0; $i < $readingsCount; $i++) {
	$coldKitchVolume = 0;
	$hotKitchVolume  = 0;
	$coldBathVolume = 0;
	$hotBathVolume  = 0;

	$month = mysqli_result($flatReadings, $i, 'Month');
	if ($month == 1 || $i == 0) {
		$classAttr = 'class="dark"';
		$year = mysqli_result($flatReadings, $i, 'Year');
	} else {
		$classAttr = '';
		$year = '';
	}

	echo "    <tr $classAttr>\r\n";
	echo "        <td>$year</td>\r\n";
	echo "        <td>$month</td>\r\n";
	if ($isKitch) {
		$coldKitchVolume = round(mysqli_result($flatReadings, $i, 'ColdKitchEnd') - mysqli_result($flatReadings, $i, 'ColdKitchStart'), 2);
		$hotKitchVolume  = round(mysqli_result($flatReadings, $i, 'HotKitchEnd') - mysqli_result($flatReadings, $i, 'HotKitchStart'), 2);
		
		echo "        <td>" . Check(mysqli_result($flatReadings, $i, 'ColdKitchStart'), $coldKitchVolume) . "</td>\r\n";
		echo "        <td>" . Check(mysqli_result($flatReadings, $i, 'ColdKitchEnd'),   $coldKitchVolume) . "</td>\r\n";
		if ($isKitchHot) {
			echo "        <td>" . Check(mysqli_result($flatReadings, $i, 'HotKitchStart'),  $hotKitchVolume)  . "</td>\r\n";
			echo "        <td>" . Check(mysqli_result($flatReadings, $i, 'HotKitchEnd'),    $hotKitchVolume)  . "</td>\r\n";
		}
	}
	if ($isBath) {
		$coldBathVolume = round( mysqli_result($flatReadings, $i, 'ColdBathEnd') - mysqli_result($flatReadings, $i, 'ColdBathStart'), 2);
		$hotBathVolume  = round( mysqli_result($flatReadings, $i, 'HotBathEnd') - mysqli_result($flatReadings, $i, 'HotBathStart'), 2);

		echo "        <td>" . Check(mysqli_result($flatReadings, $i, 'ColdBathStart'), $coldBathVolume) . "</td>\r\n";
		echo "        <td>" . Check(mysqli_result($flatReadings, $i, 'ColdBathEnd'),   $coldBathVolume) . "</td>\r\n";
		if ($isBathHot) {
			echo "        <td>" . Check(mysqli_result($flatReadings, $i, 'HotBathStart'),  $hotBathVolume)  . "</td>\r\n";
			echo "        <td>" . Check(mysqli_result($flatReadings, $i, 'HotBathEnd'),    $hotBathVolume)  . "</td>\r\n";
		}
	}
	if ($isKitch || $isBath) {
		$coldVolume = $coldKitchVolume + $hotKitchVolume + $coldBathVolume + $hotBathVolume;
		$hotVolume  = $hotKitchVolume + $hotBathVolume;
		$totalCold += $coldVolume;
		$totalHot  += $hotVolume;
		
		echo "        <td>" . Check4($coldVolume, $coldKitchVolume, $hotKitchVolume, $coldBathVolume, $hotBathVolume) . "</td>\r\n";
		if ($isKitchHot || $isBathHot) {
			echo "        <td>" . Check2($hotVolume, $hotKitchVolume, $hotBathVolume) . "</td>\r\n";
		}
	}
	if ($isGas) {
		$gasVolume = mysqli_result($flatReadings, $i, 'GasEnd') - mysqli_result($flatReadings, $i, 'GasStart');
		if (mysqli_result($flatReadings, $i, 'GasEnd') == null)
			$gasVolume = null;
		$totalGas += $gasVolume;
		
		echo "        <td>" . Check(mysqli_result($flatReadings, $i, 'GasStart'), $gasVolume) . "</td>\r\n";
		echo "        <td>" . Check(mysqli_result($flatReadings, $i, 'GasEnd'),   $gasVolume) . "</td>\r\n";
		echo "        <td>" . Check($gasVolume, $gasVolume) . "</td>\r\n";
	}
	if ($isPeople) {
		echo "        <td>" . mysqli_result($flatReadings, $i, 'People') . "</td>\r\n";
		$totalPeople += mysqli_result($flatReadings, $i, 'People');
	}
	echo "    </tr>\r\n";
}

$span = $waterColumnCount + 2;
$averageCold = round($totalCold / $readingsCount, 1);
$averageHot = round($totalHot / $readingsCount, 1);
$averageGas = round($totalGas / $readingsCount, 1);
$averagePeople = round($totalPeople / $readingsCount, 0);

echo "    <tr id=\"kokku\">\r\n";
echo "        <td colspan=\"$span\">Keskmine:</td>\r\n";
if ($isKitch || $isBath) {
	echo "        <td>$averageCold</td>\r\n";
	if ($isKitchHot || $isBathHot) {
		echo "        <td>$averageHot</td>\r\n";
	}
}
if ($isGas) {
	echo "        <td colspan=\"2\"></td>\r\n";
	echo "        <td>$averageGas</td>\r\n";
}
if ($isPeople) {
	echo "        <td>$averagePeople</td>\r\n";
}
echo "</table>\r\n";

DocumentEnd('', $seeBottom);
mysqli_close($connection);
?>
