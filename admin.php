<?php
require ('doc-parts.php');
require ('sql-func.php');
require ('func.php');

function isPeriodLocked($kyCode, $year, $month, $connection) {
    $lockQuery = mysqli_query($connection,
        "SELECT Locked FROM Period " .
        "WHERE Period.KyCode = '$kyCode' " .
            "AND Period.Year = $year " .
            "AND Period.Month = $month"
    );
    if (!$lockQuery)
        ReportInvalidQuery();
    
    return mysqli_result($lockQuery, 0, 'Locked');
}

function isPeriodFinished($kyCode, $year, $month, $connection) {
    $finishedQuery = mysqli_query($connection,
        "SELECT Finished FROM Period " .
        "WHERE Period.KyCode = '$kyCode' " .
            "AND Period.Year = $year " .
            "AND Period.Month = $month"
    );
    if (!$finishedQuery)
        ReportInvalidQuery();
    
    return mysqli_result($finishedQuery, 0, 'Finished');
}

function getReadingCount($kyCode, $year, $month, $connection) {
    $countQuery = mysqli_query($connection,
        "SELECT COUNT(*) AS Readings FROM Reading " .
            "INNER JOIN Period ON Period.PeriodId = Reading.PeriodId " .
        "WHERE Period.KyCode = '$kyCode' " .
            "AND Period.Year = $year " .
            "AND Period.Month = $month");
    if (!$countQuery)
        ReportInvalidQuery();
    $readingCount = mysqli_result($countQuery, 0, 'Readings');

    return $readingCount;
}

function getErrorCount($kyCode, $year, $month, $connection) {
    $countQuery = mysqli_query($connection, 
        "SELECT COUNT(*) AS Errors FROM Reading " .
            "INNER JOIN Period ON Period.PeriodId = Reading.PeriodId " .
        "WHERE Period.KyCode = '$kyCode' " .
            "AND Period.Year = $year " .
            "AND Period.Month = $month " .
            "AND (ColdKitchStart IS NULL AND ColdKitchEnd IS NOT NULL " .
                "OR ColdKitchStart > COALESCE(ColdKitchEnd, 0) " .
                "OR HotKitchStart IS NULL AND HotKitchEnd IS NOT NULL " .
                "OR HotKitchStart > COALESCE(HotKitchEnd, 0) " .
                "OR ColdBathStart IS NULL AND ColdBathEnd IS NOT NULL " .
                "OR ColdBathStart > COALESCE(ColdBathEnd, 0) " .
                "OR HotBathStart IS NULL AND HotBathEnd IS NOT NULL " .
                "OR HotBathStart > COALESCE(HotBathEnd, 0) " .
                "OR GasStart IS NULL AND GasEnd IS NOT NULL " .
                "OR GasStart > COALESCE(GasEnd, 0) " .
                "OR People < 0 " .
            ")");
    if (!$countQuery)
        ReportInvalidQuery();
    $errorCount = mysqli_result($countQuery, 0, 'Errors');

    return $errorCount;
}

DocHead('Administreerimine', 'readings.js');
?>

<h1>Administreerimine</h1>

<?php
$connection = ConnectToDb();

if(! isset($_GET['ky'])) {
    $kys = mysqli_query($connection, "SELECT * FROM KY ORDER BY KyCode");
    if (!$kys)
        ReportInvalidQuery();
    
    $prevMonthTime = mktime(0, 0, 0, date('n') - 1, 1, date('Y'));
    $prevYear = date('Y', $prevMonthTime) . "\r\n";
    $prevMonth = date('m', $prevMonthTime);
?>

<h2>Vali korteri&uuml;histu</h2>

<table border="0" cellspacing="3" align="center" id="ky">
  <tr>
    <th>Kood</th>
    <th>Nimetus</th>
    <th>Lukus</th>
    <th>Kortereid</th>
    <th>Sisestatud <?=$prevMonth?></th>
    <th>Vigu</th>
    <th>Valmis</th>
  </tr>
<?php
    for ($i = 0; $i < mysqli_num_rows($kys); $i++) {
        $kyCode = mysqli_result($kys, $i, "KyCode");
        $kyName = mysqli_result($kys, $i, "Name");
        $href = $_SERVER['PHP_SELF'] . '?ky=' . $kyCode;
        $locked = isPeriodLocked($kyCode, $prevYear, $prevMonth, $connection);
        $lock = $locked ? 'v' : '';
        $finished = isPeriodFinished($kyCode, $prevYear, $prevMonth, $connection);
        $finish = $finished ? '+' : '';
        if (!$locked && $finished)
            $todoClass = 'todo';
        else
            $todoClass = '';

        $readingCount = getReadingCount($kyCode, $prevYear, $prevMonth, $connection);
        $flatCount = mysqli_result($kys, $i, "Flats");
        if ($readingCount < $flatCount)
            $flatWarnClass = 'warn';
        else
            $flatWarnClass = '';

        $errorCount = getErrorCount($kyCode, $prevYear, $prevMonth, $connection);
        if ($errorCount > 0) {
            $errWarnClass = 'warn';
            $errors = $errorCount;
        } else {
            $errWarnClass = '';
            $errors = '';
        }

        echo '  <tr class="' . $todoClass . "\">\r\n";
        echo '    <td><a href="'       . $href . '">'          . $kyCode       . "</a></td>\r\n";
        echo '    <td><a href="'       . $href . '">'          . $kyName       . "</a></td>\r\n";
        echo '    <td class="result">'                         . $lock         . "</td>\r\n";
        echo '    <td class="result">'                         . $flatCount    . "</td>\r\n";
        echo '    <td class="result '  . $flatWarnClass . '">' . $readingCount . "</td>\r\n";
        echo '    <td class="result '  . $errWarnClass  . '">' . $errors       . "</td>\r\n";
        echo '    <td class="result">'                         . $finish       . "</td>\r\n";
        echo "  </tr>\r\n";
    }
?>
</table>

<style type="text/css">
    table#ky td.result {
        text-align: center;
    }
    .todo {
        background: #fff;
    }
</style>

<?php
    DocumentEnd('', false);
    mysqli_close($connection);
    exit();
}

$ky = htmlspecialchars($_GET['ky']);

if (isset($_POST["year"]) && isset($_POST["month"])) {
    $year = $_POST["year"];
    $month = $_POST["month"];
    $lockedValue   = $_POST["locked"] == "on" ? -1 : 0;
	
    $insertResult = mysqli_query($connection, "INSERT INTO Period (KyCode, Year, Month, Locked) VALUES ('" .
        $ky    . "', " .
        $year  . ", " .
        $month . ", " .
        $lockedValue . ")"
        );
    if (! $insertResult) {
        $updateResult = mysqli_query($connection, "UPDATE Period SET " .
            " Locked = " . $lockedValue .
            " WHERE KyCode = '" . $ky . "' AND" .
            " Year = " . $year . " AND" .
            " Month = " . $month
            );
        if (! $updateResult)
            ReportInvalidQuery();
    }
}
$kyRecord = mysqli_query($connection, "SELECT * FROM KY WHERE KyCode = '" . $ky . "'");
if ($kyRecord && mysqli_num_rows($kyRecord) > 0) {
    $flatCount = mysqli_result($kyRecord, 0, 'Flats');
    $isRisers  = mysqli_result($kyRecord, 0, 'Risers');
}
else
    ReportInvalidQuery();
?>

<div id="params">
    K&Uuml;: <?=$ky?>, <?=$flatCount?> korterit &nbsp;&nbsp;
    <a href="<?=$_SERVER['PHP_SELF']?>">Vali teine &uuml;histu</a>
<?php
    $periods = mysqli_query($connection, 
        "SELECT * FROM (" .
        "SELECT PeriodId, Year, Month, Locked, (" .
            "SELECT COUNT(*) FROM Reading WHERE PeriodId = Period.PeriodId" .
            ") AS Entered" .
        " FROM Period WHERE KyCode = '" . $ky .
        "' ORDER BY Year DESC, Month DESC" . 
        " LIMIT 12" .
        ") AS Last12 ORDER BY Year, Month"
        );
    if (!$periods)
        ReportInvalidQuery();
?>
</div>
<br />

<form method="post" action="<?=CurrentPage() . "?ky=" . $ky ?>">

<table border="0" cellspacing="3" align="center" id="period">
  <tr>
    <th>Aasta</th>
    <th>Kuu</th>
    <th>Lukk</th>
    <th>Sisestatud</th>
    <th>Vigu</th>
    <th>Lae fail</th>
<?php
    if ($isRisers)
        echo "    <th>Lae fail</th>\r\n";
?>
  </tr>
<?php
    for ($i = 0; $i < mysqli_num_rows($periods); $i++) {
        $year     = mysqli_result($periods, $i, 'Year');
        $month    = mysqli_result($periods, $i, 'Month');
		$periodId = mysqli_result($periods, $i, 'PeriodId');
		$isLocked = mysqli_result($periods, $i, 'Locked');
        $entered  = mysqli_result($periods, $i, 'Entered');
        $errCount = getErrorCount($ky, $year, $month, $connection);
        if ($errCount > 0)
            $errWarnClass = 'class="warn"';
        else
            $errWarnClass = '';
        $lock = $isLocked ? 'v' : '';
        
        echo "  <tr>\r\n";
        echo '    <td>' . $year . '</td>';
        echo '<td>' . $month . '</td>';
        echo '<td style="text-align:center">' . $lock . '</td>';
        if ($entered < $flatCount)
            $cellStart = '<td class="warn">';
        else
            $cellStart = '<td>';
        echo $cellStart . $entered . '</td>';
        if ($isLocked)
            echo '<td colspan="2">&nbsp;</td>';
        else {
            echo '<td ' . $errWarnClass . '>' . $errCount . ' vigu</td>';
            echo '<td><a href="readings-down.php?ky=' . $ky . '&period=' . $periodId . '&dec=n">Korterid</a></td>';
        }
        if ($isRisers) {
            if ($isLocked)
                echo '<td>&nbsp;</td>';
            else
                echo '<td><a href="risers-down.php?ky=' . $ky . '&period=' . $periodId . '&dec=n">P&uuml;stikud</a></td>';
        }
        echo "\r\n  </tr>\r\n";
    }

    $curDate = getdate();
  ?>
  <tr>
    <td><input type="text" name="year"  value="<?=$curDate['year']?>" /></td>
    <td><input type="text" name="month" value="<?=$curDate['mon']?>" id="month" /></td>
    <td style="text-align:center"><input type="checkbox" name="locked" /></td>
    <td></td>
    <td></td>
  </tr>
</table>
<br />

<div id="buttons">
    <input type="submit" value="Salvesta periood" />
</div>
</form>

<?php
    DocumentEnd('month', false);
    mysqli_close($connection);
?>
