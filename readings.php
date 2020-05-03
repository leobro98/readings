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

if (! isset($_POST['year']) || ! isset($_POST['month']))
    Redirect('period.php');

$year  = htmlspecialchars($_POST['year']);
$month = htmlspecialchars($_POST['month']);

if (isset($_POST["periodId"])) {
    // the request is made from this page
    $periodId = htmlspecialchars($_POST["periodId"]);
    //TODO: prevent SQL injection!
    $locked   = htmlspecialchars($_POST["locked"]);
    $finished = htmlspecialchars($_POST["finished"]);
    $periodExists = true;

    if ($_POST['action'] == 'delete') {
        $deletedId = $_POST['deletedId'];
        $deleteQuery = mysqli_query($connection, "DELETE FROM Reading WHERE PeriodId = $periodId AND FlatId = $deletedId LIMIT 1");
        if (! $deleteQuery)
            ReportInvalidQuery();
    } else if ($_POST['action'] == 'finish') {
        $finishQuery = mysqli_query($connection, "UPDATE Period SET Finished = -1 WHERE PeriodId = $periodId");
        if ($finishQuery)
            $finished = true;
        else
            ReportInvalidQuery();
    } else if (( ! isset($_POST['action']) || $_POST['action'] != 'fill' ) && isset($_POST['FlatId'])) {
        // insert or update readings
        
        $flatId         = htmlspecialchars($_POST['FlatId']);
        $coldKitchStart = str_replace(',', '.', htmlspecialchars($_POST['ColdKitchStart']));
        $coldKitchEnd   = str_replace(',', '.', htmlspecialchars($_POST['ColdKitchEnd']));
        $hotKitchStart  = str_replace(',', '.', htmlspecialchars($_POST['HotKitchStart']));
        $hotKitchEnd    = str_replace(',', '.', htmlspecialchars($_POST['HotKitchEnd']));
        $coldBathStart  = str_replace(',', '.', htmlspecialchars($_POST['ColdBathStart']));
        $coldBathEnd    = str_replace(',', '.', htmlspecialchars($_POST['ColdBathEnd']));
        $hotBathStart   = str_replace(',', '.', htmlspecialchars($_POST['HotBathStart']));
        $hotBathEnd     = str_replace(',', '.', htmlspecialchars($_POST['HotBathEnd']));
        $gasStart       = str_replace(',', '.', htmlspecialchars($_POST['GasStart']));
        $gasEnd         = str_replace(',', '.', htmlspecialchars($_POST['GasEnd']));
        $electrStart    = str_replace(',', '.', htmlspecialchars($_POST['ElectrStart']));
        $electrEnd      = str_replace(',', '.', htmlspecialchars($_POST['ElectrEnd']));
        $people         = str_replace(',', '.', htmlspecialchars($_POST['People']));
        
        if (        $flatId         != '' && (
                    $coldKitchStart != '' && $coldKitchEnd != '' ||
                    $hotKitchStart  != '' && $hotKitchEnd  != '' ||
                    $coldBathStart  != '' && $coldBathEnd  != '' ||
                    $hotBathStart   != '' && $hotBathEnd   != '' ||
                    $gasStart       != '' && $gasEnd       != '' ||
                    $electrStart    != '' && $electrEnd    != '' ||
                    $people         != '')) {
            
            $insertResult = mysqli_query($connection, "INSERT INTO Reading VALUES (" .
                $periodId                  . ", " .
                $flatId                    . ", " .
                SqlNumber($coldKitchStart) . ", " .
                SqlNumber($coldKitchEnd)   . ", " .
                SqlNumber($hotKitchStart)  . ", " .
                SqlNumber($hotKitchEnd)    . ", " .
                SqlNumber($coldBathStart)  . ", " .
                SqlNumber($coldBathEnd)    . ", " .
                SqlNumber($hotBathStart)   . ", " .
                SqlNumber($hotBathEnd)     . ", " .
                SqlNumber($gasStart)       . ", " .
                SqlNumber($gasEnd)         . ", " .
                SqlNumber($electrStart)    . ", " .
                SqlNumber($electrEnd)      . ", " .
                SqlNumber($people)         . ")"
                );
            if (! $insertResult) {
                // the key exists, then update existing record
                $updateResult = mysqli_query($connection, "UPDATE Reading SET "  .
                    "ColdKitchStart = "  . SqlNumber($coldKitchStart) . ", " .
                    "ColdKitchEnd = "    . SqlNumber($coldKitchEnd)   . ", " .
                    "HotKitchStart = "   . SqlNumber($hotKitchStart)  . ", " .
                    "HotKitchEnd = "     . SqlNumber($hotKitchEnd)    . ", " .
                    "ColdBathStart = "   . SqlNumber($coldBathStart)  . ", " .
                    "ColdBathEnd = "     . SqlNumber($coldBathEnd)    . ", " .
                    "HotBathStart = "    . SqlNumber($hotBathStart)   . ", " .
                    "HotBathEnd = "      . SqlNumber($hotBathEnd)     . ", " .
                    "GasStart = "        . SqlNumber($gasStart)       . ", " .
                    "GasEnd = "          . SqlNumber($gasEnd)         . ", " .
                    "ElectrStart = "     . SqlNumber($electrStart)    . ", " .
                    "ElectrEnd = "       . SqlNumber($electrEnd)      . ", " .
                    "People = "          . SqlNumber($people)         .
                    " WHERE PeriodId = " . $periodId                  . " AND" .
                    " FlatId = "         . $flatId
                    );
                if (! $updateResult)
                    ReportInvalidQuery();
            }
        } else if ($flatId != '') {
            $_POST['action'] = 'fill';
        }
    }
} else {
    // show readings (the request is made from the period page)
    $period = mysqli_query($connection, "SELECT * FROM Period WHERE KyCode = '$ky' AND Year = $year AND Month = $month");
    if (! $period)
        ReportInvalidQuery();
    if (mysqli_num_rows($period) == 0)
        $periodExists = false;
    else {
        $periodExists = true;
        $periodId = mysqli_result($period, 0, "PeriodId");
        $locked   = mysqli_result($period, 0, "Locked");
        $finished = mysqli_result($period, 0, "Finished");
    }
}

DocHead("Readings - $kyName", 'readings.js');
$seeBottom = false;
?>

<h1>Readings</h1>
<h2><?=$kyName?></h2>

<div id="params">
    Year:  <?=$year?>  &nbsp;&nbsp;
    Month: <?=$month?> &nbsp;&nbsp;
    <form method="post" action="period.php" id="formPeriod">
        <input type="hidden" name="ky" value="<?=$ky?>" />
        <input type="hidden" name="password" value="<?=$password?>" />

        <div style="margin-top: 10px">
            <input type="submit" value="Choose another period" style="margin-right: 10px" />
            <span class="scrn">
                <a href="readings-down.php?ky=<?=$ky?>&period=<?=$periodId?>">Download (eng)</a> &nbsp;&nbsp;
                <a href="readings-down.php?ky=<?=$ky?>&period=<?=$periodId?>&dec=est">Download (est)</a>
            </span>
        </div>
    </form>
<?php
    if (! $periodExists) {
        echo "        <br /><br />\r\n";
        echo "        This period is not created.\r\n";
        echo "</div>\r\n";
        DocumentEnd('', $seeBottom);
        mysqli_close($connection);
        exit();
    }
    $newReadings = mysqli_query($connection, "SELECT * FROM Reading WHERE PeriodId = $periodId ORDER BY FlatId");
?>

</div>
<br />

<form method="post" action="<?=CurrentPage()?>" onsubmit="setNoWarning()" id="formReadings">
    <input type="hidden" name="ky"       value="<?=$ky?>" />
    <input type="hidden" name="password" value="<?=$password?>" />
    <input type="hidden" name="year"     value="<?=$year?>" />
    <input type="hidden" name="month"    value="<?=$month?>" />
    <input type="hidden" name="periodId" value="<?=$periodId?>" />
    <input type="hidden" name="locked"   value="<?=$locked?>" />
    <input type="hidden" name="finished" value="<?=$finished?>" />
    <input type="hidden" name="action"    />
    <input type="hidden" name="deletedId" />

<table border="0" cellspacing="3" align="center" class="php">
    <tr>
        <th rowspan="3">Flat</th>
<?php
    $waterColumnCount = 0;
    if ($isKitch) {
        if ($isKitchHot) {
?>
        <th colspan="4">Kitchen</th>
<?php
        $waterColumnCount += 4;
        } else {
?>
        <th colspan="2">Kitchen</th>
<?php
            $waterColumnCount += 2;
        }
    }
    if ($isBath) {
        if ($isBathHot) {
?>
        <th colspan="4">Bathroom</th>
<?php
        $waterColumnCount += 4;
        } else {
?>
        <th colspan="2">Bathroom</th>
<?php
            $waterColumnCount += 2;
        }
    }
    if ($isKitch || $isBath) {
        if ($isKitchHot || $isBathHot) {
?>
        <th colspan="2">Water consump.</th>
<?php
        } else {
?>
        <th>Water</th>
<?php
        }
    }
    if ($isGas) {
?>
        <th colspan="2" rowspan="2">Gas</th>
        <th rowspan="3">Gas<br />consump.</th>
<?php
    }
    if ($isPeople) {
?>
        <th rowspan="3">People</th>
<?php
    }
?>
        <th rowspan="3" class="button-cell"></th>
    </tr>
    <tr>
<?php
    if ($isKitch) {
?>
        <th colspan="2">Cold water</th>
<?php
        if ($isKitchHot) {
?>
        <th colspan="2">Hot water</th>
<?php
        }
    }
    if ($isBath) {
?>
        <th colspan="2">Cold water</th>
<?php
        if ($isBathHot) {
?>
        <th colspan="2">Hot water</th>
<?php
        }
    }
    if ($isKitch || $isBath) {
?>
        <th rowspan="2">Total</th>
<?php
        if ($isKitchHot || $isBathHot) {
?>
        <th rowspan="2">Incl. hot</th>
<?php
        }
    }
?>
    </tr>
    <tr>
<?php
    if ($isKitch) {
?>
        <th>Start</th>
        <th>End</th>
<?php
        if ($isKitchHot) {
?>
        <th>Start</th>
        <th>End</th>
<?php
        }
    }
    if ($isBath) {
?>
        <th>Start</th>
        <th>End</th>
<?php
        if ($isBathHot) {
?>
        <th>Start</th>
        <th>End</th>
<?php
        }
    }
    if ($isGas) {
?>
        <th>Start</th>
        <th>End</th>
<?php
    }
?>
    </tr>
<?php
    $totalCold   = 0;
    $totalHot    = 0;
    $totalGas    = 0;
    $totalPeople = 0;

    $totalColdWrong = 0;
    $totalHotWrong  = 0;
    $totalGasWrong  = 0;
    
    $flatsEntered = mysqli_num_rows($newReadings);
    $curFlat = 0;

    for ($i = 0; $i < $flatsEntered; $i++) {
        if ($locked) {
            echo '    <tr';
        } else {
            echo '    <tr onmouseover="over(this)" onmouseout="out(this)"';
        }
        if (mysqli_result($newReadings, $i, 'ColdKitchStart') != null)
            echo ' class="dark"';
        echo ">\r\n";

        $coldKitchSpent = 0;
        $hotKitchSpent  = 0;
        $coldBathSpent  = 0;
        $hotBathSpent   = 0;
        $curFlat = mysqli_result($newReadings, $i, 'FlatId');
        
        echo '        <td>' . $curFlat . '</td>';
        if ($isKitch) {
            $coldKitchSpent = round(mysqli_result($newReadings, $i, 'ColdKitchEnd') - mysqli_result($newReadings, $i, 'ColdKitchStart'), 2);
            $hotKitchSpent  = round(mysqli_result($newReadings, $i, 'HotKitchEnd')  - mysqli_result($newReadings, $i, 'HotKitchStart'),  2);
            
            echo '<td>' . Check(mysqli_result($newReadings, $i, 'ColdKitchStart'), $coldKitchSpent) . '</td>';
            echo '<td>' . Check(mysqli_result($newReadings, $i, 'ColdKitchEnd'),   $coldKitchSpent) . '</td>';
            if ($isKitchHot) {
                echo '<td>' . Check(mysqli_result($newReadings, $i, 'HotKitchStart'), $hotKitchSpent)  . '</td>';
                echo '<td>' . Check(mysqli_result($newReadings, $i, 'HotKitchEnd'),   $hotKitchSpent)  . '</td>';
            }
        }
        if ($isBath) {
            $coldBathSpent = round(mysqli_result($newReadings, $i, 'ColdBathEnd') - mysqli_result($newReadings, $i, 'ColdBathStart'), 2);
            $hotBathSpent  = round(mysqli_result($newReadings, $i, 'HotBathEnd')  - mysqli_result($newReadings, $i, 'HotBathStart'),  2);

            echo '<td>' . Check(mysqli_result($newReadings, $i, 'ColdBathStart'), $coldBathSpent) . '</td>';
            echo '<td>' . Check(mysqli_result($newReadings, $i, 'ColdBathEnd'),   $coldBathSpent) . '</td>';
            if ($isBathHot) {
                echo '<td>' . Check(mysqli_result($newReadings, $i, 'HotBathStart'), $hotBathSpent) . '</td>';
                echo '<td>' . Check(mysqli_result($newReadings, $i, 'HotBathEnd'),   $hotBathSpent) . '</td>';
            }
        }
        if ($isKitch || $isBath) {
            $coldSpent = $coldKitchSpent + $hotKitchSpent + $coldBathSpent + $hotBathSpent;
            if ($coldKitchSpent < 0 || $hotKitchSpent < 0 || $coldBathSpent < 0 || $hotBathSpent < 0)
                $totalColdWrong = -1;
            $hotSpent = $hotKitchSpent + $hotBathSpent;
            if ($hotKitchSpent < 0 || $hotBathSpent < 0)
                $totalHotWrong = -1;
            $totalCold += $coldSpent;
            $totalHot  += $hotSpent;
            
            echo '<td>' . Check4($coldSpent, $coldKitchSpent, $hotKitchSpent, $coldBathSpent, $hotBathSpent) . '</td>';
            if ($isKitchHot || $isBathHot) {
                echo '<td>' . Check2($hotSpent, $hotKitchSpent, $hotBathSpent) . '</td>';
            }
        }
        if ($isGas) {
            $gasSpent = round(mysqli_result($newReadings, $i, 'GasEnd') - mysqli_result($newReadings, $i, 'GasStart'), 2);
            if (mysqli_result($newReadings, $i, 'GasEnd') == null)
                $gasSpent = null;
            if ($gasSpent < 0)
                $totalGasWrong = -1;
            $totalGas += $gasSpent;
            
            echo '<td>' . Check(mysqli_result($newReadings, $i, 'GasStart'), $gasSpent) . '</td>';
            echo '<td>' . Check(mysqli_result($newReadings, $i, 'GasEnd'),   $gasSpent) . '</td>';
            echo '<td>' . Check($gasSpent, $gasSpent) . '</td>';
        }
        if ($isPeople) {
            echo '<td>' . mysqli_result($newReadings, $i, 'People') . '</td>';
            $totalPeople += mysqli_result($newReadings, $i, 'People');
        }
        echo '<td class="button-cell"><img src="cut.gif" onclick="deleteRow(' . $curFlat .
            ')" alt="delete" title="Delete row" /></td>';
        echo "\r\n";
        echo "    </tr>\r\n";
    }
?>
    <tr id="kokku">
<?php
    if ($flatsEntered < $flats) {
?>
        <td><strong>Total <span class="scrn"><?=$flatsEntered?></span></strong></td>
        <script type="text/javascript" language="JavaScript">var warning = true;</script>
<?php
    } else {
?>
        <td>Total <span class="scrn"><?=$flatsEntered?></span></td>
<?php
    }
    if ($isKitch || $isBath) {
?>
        <td colspan="<?=$waterColumnCount?>"></td>
        <td><?=Check($totalCold, $totalColdWrong)?></td>
<?php
        if ($isKitchHot || $isBathHot) {
?>
        <td><?=Check($totalHot,  $totalHotWrong)?></td>
<?php
        }
    }
    if ($isGas) {
?>
        <td colspan="2"></td>
        <td><?=Check($totalGas, $totalGasWrong)?></td>
<?php
    }
    if ($isPeople) {
?>
        <td><?=$totalPeople?></td>
<?php
    }
?>
        <td class="button-cell"></td>
    </tr>
<?php
    if (! $locked) {
        if (isset($_POST['action']) && htmlspecialchars($_POST['action']) == 'fill' &&
                isset($_POST['FlatId'])) {
            $nextId = htmlspecialchars($_POST['FlatId']);
        } else if ($curFlat < $flats) {
            $nextId = $curFlat + 1;
        } else {
			$nextId = GetFirstMissingFlat($connection, $periodId);
        }
		
        $newSavedReadings = mysqli_query($connection, "SELECT * FROM Reading WHERE PeriodId = " . $periodId .
            " AND FlatId = " . $nextId);
        
		if ($newSavedReadings && mysqli_num_rows($newSavedReadings) > 0) {
            // this flat was already saved in this period, recall its readings
            $prefillColdKitchStart = mysqli_result($newSavedReadings, 0, "ColdKitchStart");
            $prefillColdKitchEnd   = mysqli_result($newSavedReadings, 0, "ColdKitchEnd");
            $prefillHotKitchStart  = mysqli_result($newSavedReadings, 0, "HotKitchStart");
            $prefillHotKitchEnd    = mysqli_result($newSavedReadings, 0, "HotKitchEnd");
            $prefillColdBathStart  = mysqli_result($newSavedReadings, 0, "ColdBathStart");
            $prefillColdBathEnd    = mysqli_result($newSavedReadings, 0, "ColdBathEnd");
            $prefillHotBathStart   = mysqli_result($newSavedReadings, 0, "HotBathStart");
            $prefillHotBathEnd     = mysqli_result($newSavedReadings, 0, "HotBathEnd");
            $prefillGasStart       = mysqli_result($newSavedReadings, 0, "GasStart");
            $prefillGasEnd         = mysqli_result($newSavedReadings, 0, "GasEnd");
            $prefillPeople         = mysqli_result($newSavedReadings, 0, "People");
        } else {
            // fill start readings from the end readings for the previous period
            $oldPeriod = mysqli_query($connection, "SELECT * FROM Period " .
                "WHERE ((Year = " . $year . " AND Month < " . $month . ") OR Year < " . $year .
                    ") AND KyCode = '" . $ky . "' " .
                "ORDER BY Year DESC, Month DESC  LIMIT 1");
            if ($oldPeriod && mysqli_num_rows($oldPeriod) > 0) {
                // we use the first only row because this is the very previous period
                $oldPeriodId = mysqli_result($oldPeriod, 0, 'PeriodId');
                $oldReadings = mysqli_query($connection, "SELECT * FROM Reading WHERE PeriodId = " . $oldPeriodId .
                    " AND FlatId = " . $nextId);
                if ($oldReadings && mysqli_num_rows($oldReadings) > 0) {
                    $prefillColdKitchStart = mysqli_result($oldReadings, 0, "ColdKitchEnd");
                    $prefillHotKitchStart  = mysqli_result($oldReadings, 0, "HotKitchEnd");
                    $prefillColdBathStart  = mysqli_result($oldReadings, 0, "ColdBathEnd");
                    $prefillHotBathStart   = mysqli_result($oldReadings, 0, "HotBathEnd");
                    $prefillGasStart       = mysqli_result($oldReadings, 0, "GasEnd");
                    $prefillColdKitchEnd   = null;
                    $prefillHotKitchEnd    = null;
                    $prefillColdBathEnd    = null;
                    $prefillHotBathEnd     = null;
                    $prefillGasEnd         = null;
                    $prefillPeople         = mysqli_result($oldReadings, 0, "People");
                }
            }
        }
?>
    <tr id="fill">
        <td><input type="text" name="FlatId" value="<?= $nextId ?>" id="flat" /></td>
<?php
    if ($isKitch) {
?>
        <td><input type="text" name="ColdKitchStart" value="<?=$prefillColdKitchStart?>" /></td>
        <td><input type="text" name="ColdKitchEnd"   value="<?=$prefillColdKitchEnd?>"   /></td>
<?php
        if ($isKitchHot) {
?>
        <td><input type="text" name="HotKitchStart"  value="<?=$prefillHotKitchStart?>"  /></td>
        <td><input type="text" name="HotKitchEnd"    value="<?=$prefillHotKitchEnd?>"    /></td>
<?php
        }
    }
    if ($isBath) {
?>
        <td><input type="text" name="ColdBathStart" value="<?=$prefillColdBathStart?>" /></td>
        <td><input type="text" name="ColdBathEnd"   value="<?=$prefillColdBathEnd?>"   /></td>
<?php
        if ($isBathHot) {
?>
        <td><input type="text" name="HotBathStart"  value="<?=$prefillHotBathStart?>"  /></td>
        <td><input type="text" name="HotBathEnd"    value="<?=$prefillHotBathEnd?>"    /></td>
<?php
        }
    }
    if ($isKitch || $isBath) {
        if ($isKitchHot || $isBathHot) {
?>
            <td colspan="2"></td>
<?php
        } else {
?>
            <td></td>
<?php
        }
    }
    if ($isGas) {
?>
        <td><input type="text" name="GasStart" value="<?=$prefillGasStart?>" /></td>
        <td><input type="text" name="GasEnd"   value="<?=$prefillGasEnd?>"   /></td>
        <td></td>
<?php
    }
    if ($isPeople) {
?>
        <td><input type="text" name="People"   value="<?=$prefillPeople?>"  /></td>
<?php
    }
?>
        <td><a href="#" onclick="prefill()">Fill data</a></td>
    </tr>
<?php
}
?>
</table>
<br />

<?php
if ($locked) {
?>
</form>
<?php
} else {
?>
    <div id="buttons">
        <input type="submit" value="Save" />
    </div>
</form>

<?php
if ($finished) {
?>
    <div id="message-box-outer">
        <div id="message-box-inner">
            <span id="message-label">Reported: Data are ready</span>
        </div>
    </div>
<?php
} else {
?>
    <form method="post" action="<?=CurrentPage()?>" onsubmit="setNoWarning()" id="formFinished">
        <input type="hidden" name="periodId" value="<?=$periodId?>" />
        <input type="hidden" name="year"     value="<?=$year?>" />
        <input type="hidden" name="month"    value="<?=$month?>" />
        <input type="hidden" name="ky"       value="<?=$ky?>" />
        <input type="hidden" name="password" value="<?=$password?>" />
        <input type="hidden" name="action"   value="finish" />

        <div id="message-box-outer">
            <div id="message-box-inner">
                <span id="message-label">Report to accountant:</span>
                <button type="submit" id="message-button">Data are ready</button>
            </div>
        </div>
    </form>
<?php
}
?>

<div id="comments">
    <p>Notes:</p>
    <ol style="padding-left: 20px">
        <li>You can enter flats in arbitrary order (see also note 3).</li>
        <li>If you want to change already saved flat readings, enter the flat number and 
            correct readings (see also note 3).</li>
        <li>If you want to pre-fill fields with already saved data, enter the flat number
            and click "Fill data".</li>
    </ol>
</div>

<?php
    $seeBottom = true;
}

DocumentEnd('flat', $seeBottom);
mysqli_close($connection);
?>
