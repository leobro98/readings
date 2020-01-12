<?php
require ('doc-parts.php');
require ('func.php');

DocHead("Readings - login", "readings.js");
?>

<h2>Sisselogimine</h2>
<br />
<br />

<form method="post" action="period.php">

<table border="0" align="center">
    <tr>
        <td>Kasutaja tunnus:</td>
        <td><input type="text" name="ky" style="width: 10em; text-align: left" id="ky" /></td>
    </tr>
    <tr>
        <td>Parool:</td>
        <td><input type="password" name="password" style="width: 10em" /></td>
    </tr>
</table>
<br />

<div id="buttons">
<input type="submit" value="Logi sisse" />
</div>
</form>

<?php
DocumentEnd('ky', false);
?>
