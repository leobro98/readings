<?php
require ('doc-parts.php');
require ('sql-func.php');
require ('func.php');

$kyRecord = CheckPasswordAndGetKyRecord($_POST['ky'], $_POST['password']);
$ky       = mysqli_result($kyRecord, 0, "KyCode");
$password = mysqli_result($kyRecord, 0, 'Password');
$kyName   = mysqli_result($kyRecord, 0, 'Name');
$hasRisers = mysqli_result($kyRecord, 0, 'Risers');

//calculate the previous month
$curDate = getdate();
$year = $curDate['year'];
$month   = $curDate['mon'];
if ($month > 1)
    $month = $month - 1;
else {
    $month   = 12;
    $year = $year - 1;
}

DocHead("Readings - $kyName", "readings.js");
?>

<h1>N&auml;idud</h1>
<h2><?=$kyName?></h2>

<form method="post" action="readings.php">
    <div id="params">
        Aasta: <input type="text" name="year"  value="<?=$year?>" /> &nbsp;&nbsp;
        Kuu:   <input type="text" name="month" value="<?=$month?>" id="month" />
    </div>
    <br />

    <div id="buttons">
        <input type="submit" value="Ava n&auml;idud" />
<?php
if ($hasRisers) {
?>
        <input type="button" value="Ava p&uuml;stikud" onclick="openPage('risers.php');" />
<?php
}
?>
    </div>

    <input type="hidden" name="ky" value="<?=$ky?>" />
    <input type="hidden" name="password" value="<?=$password?>" />
</form>

<form method="post" action="flat.php" id="statistics">
	<div class="space-top">
		<table border="0" cellspacing="3" align="center">
			<tr>
                <td>Korter:</td>
				<td><input type="text" name="flatId" id="flatId" /><td>
				<td><a href="javascript:void(0)" onclick="return openFlat();">Vaata korteri statistika</a><td>
			</tr>
		</table>

		<input type="hidden" name="ky" value="<?=$ky?>" />
		<input type="hidden" name="password" value="<?=$password?>" />
	</div>
</form>

<?php
DocumentEnd('month', false);
?>
