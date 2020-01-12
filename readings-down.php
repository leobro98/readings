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
    echo "Periood ei ole antud.<br />";
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
    echo 'Sellist perioodi ei ole.<br />';
    mysqli_close($connection);
    exit();
}
else {
    $year  = mysqli_result($periodRow, 0, 'Year');
    $month = mysqli_result($periodRow, 0, 'Month');
}

// read readings
$newReadings = mysqli_query($connection, "SELECT * FROM Reading WHERE PeriodId = $periodId ORDER BY FlatId");
if (! $newReadings) {
    ReportInvalidQuery();
    mysqli_close($connection);
    exit();
}
if (mysqli_num_rows($newReadings) == 0) {
    echo 'Perioodil ei ole andmeid.<br />';
    mysqli_close($connection);
    exit();
}
else {
    header('Content-type: application/octet-stream');//
    header('Content-Description: File Transfer');
    // supply a recommended filename and force the browser to display the save dialog
    header('Content-Disposition: attachment; filename="' . $ky . '-readings.csv"');
    header('Content-Transfer-Encoding: binary');
    header('Expires: -1');
    header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
    header('Pragma: public');
    ob_clean();
    flush();

    if (isset($_GET['dec']) && $_GET['dec'] == 'est') {
        echo "$ky;$year;$month\r\n";
        // Estonian decimal symbol and list separator
        echo "Korter;KylmKookAlg;KylmKookLopp;SoeKookAlg;SoeKookLopp;KylmVannAlg;KylmVannLopp;SoeVannAlg;SoeVannLopp;GaasAlg;GaasLopp;ElekterAlg;ElekterLopp;Elanike\r\n";
        
        for ($i = 0; $i < mysqli_num_rows($newReadings); $i++) {
            echo     mysqli_result($newReadings, $i, 'FlatId')          . ';';

            echo Est(mysqli_result($newReadings, $i, 'ColdKitchStart')) . ';';
            echo Est(mysqli_result($newReadings, $i, 'ColdKitchEnd'))   . ';';
            echo Est(mysqli_result($newReadings, $i, 'HotKitchStart'))  . ';';
            echo Est(mysqli_result($newReadings, $i, 'HotKitchEnd'))    . ';';

            echo Est(mysqli_result($newReadings, $i, 'ColdBathStart'))  . ';';
            echo Est(mysqli_result($newReadings, $i, 'ColdBathEnd'))    . ';';
            echo Est(mysqli_result($newReadings, $i, 'HotBathStart'))   . ';';
            echo Est(mysqli_result($newReadings, $i, 'HotBathEnd'))     . ';';

            echo Est(mysqli_result($newReadings, $i, 'GasStart'))       . ';';
            echo Est(mysqli_result($newReadings, $i, 'GasEnd'))         . ';';
            echo Est(mysqli_result($newReadings, $i, 'ElectrStart'))    . ';';
            echo Est(mysqli_result($newReadings, $i, 'ElectrEnd'))      . ';';

            echo Est(mysqli_result($newReadings, $i, 'People'));
            echo "\r\n";
            }
        }
    else if ($_GET['dec'] == 'n') {
        echo "$ky;$year;$month\r\n";
        // English decimal symbol, but Estonian list separator - for Natalia
        echo "Korter;KylmKookAlg;KylmKookLopp;SoeKookAlg;SoeKookLopp;KylmVannAlg;KylmVannLopp;SoeVannAlg;SoeVannLopp;GaasAlg;GaasLopp;ElekterAlg;ElekterLopp;Elanike\r\n";
        
        for ($i = 0; $i < mysqli_num_rows($newReadings); $i++) {
            echo mysqli_result($newReadings, $i, 'FlatId')         . ';';

            echo mysqli_result($newReadings, $i, 'ColdKitchStart') . ';';
            echo mysqli_result($newReadings, $i, 'ColdKitchEnd')   . ';';
            echo mysqli_result($newReadings, $i, 'HotKitchStart')  . ';';
            echo mysqli_result($newReadings, $i, 'HotKitchEnd')    . ';';

            echo mysqli_result($newReadings, $i, 'ColdBathStart')  . ';';
            echo mysqli_result($newReadings, $i, 'ColdBathEnd')    . ';';
            echo mysqli_result($newReadings, $i, 'HotBathStart')   . ';';
            echo mysqli_result($newReadings, $i, 'HotBathEnd')     . ';';

            echo mysqli_result($newReadings, $i, 'GasStart')       . ';';
            echo mysqli_result($newReadings, $i, 'GasEnd')         . ';';
            echo mysqli_result($newReadings, $i, 'ElectrStart')    . ';';
            echo mysqli_result($newReadings, $i, 'ElectrEnd')      . ';';

            echo mysqli_result($newReadings, $i, 'People');
            echo "\r\n";
        }
    }
    else {
        echo "$ky,$year,$month\r\n";
        // English settings
        echo "Korter,KylmKookAlg,KylmKookLopp,SoeKookAlg,SoeKookLopp,KylmVannAlg,KylmVannLopp,SoeVannAlg,SoeVannLopp,GaasAlg,GaasLopp,ElekterAlg,ElekterLopp,Elanike\r\n";
        
        for ($i = 0; $i < mysqli_num_rows($newReadings); $i++) {
            echo mysqli_result($newReadings, $i, 'FlatId')         . ',';

            echo mysqli_result($newReadings, $i, 'ColdKitchStart') . ',';
            echo mysqli_result($newReadings, $i, 'ColdKitchEnd')   . ',';
            echo mysqli_result($newReadings, $i, 'HotKitchStart')  . ',';
            echo mysqli_result($newReadings, $i, 'HotKitchEnd')    . ',';

            echo mysqli_result($newReadings, $i, 'ColdBathStart')  . ',';
            echo mysqli_result($newReadings, $i, 'ColdBathEnd')    . ',';
            echo mysqli_result($newReadings, $i, 'HotBathStart')   . ',';
            echo mysqli_result($newReadings, $i, 'HotBathEnd')     . ',';

            echo mysqli_result($newReadings, $i, 'GasStart')       . ',';
            echo mysqli_result($newReadings, $i, 'GasEnd')         . ',';
            echo mysqli_result($newReadings, $i, 'ElectrStart')    . ',';
            echo mysqli_result($newReadings, $i, 'ElectrEnd')      . ',';

            echo mysqli_result($newReadings, $i, 'People');
            echo "\r\n";
        }
    }
}
?>
