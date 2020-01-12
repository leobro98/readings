<?php
function Redirect ($page) {
    $host  = $_SERVER['HTTP_HOST'];
    $uri   = rtrim(dirname($_SERVER['PHP_SELF']), '/\\');
    header("Location: http://$host$uri/$page", true, 301);
    exit;
}

function GetUrl ($page) {
    $host  = $_SERVER['HTTP_HOST'];
    $uri   = rtrim(dirname($_SERVER['PHP_SELF']), '/\\');
    return "http://$host$uri/$page";
}

function CurrentPage () {
    return basename($_SERVER['PHP_SELF']);
}

function FormatSqlDate ($mySqlDate) {
    $typedDate = new DateTime($mySqlDate);
    return $typedDate->format('d.m.Y');
}

function Check ($reading, $consumption) {
    if ($consumption < 0)
        return '<strong>' . $reading . '</strong>';
    else
        return $reading;
}

function Check2 ($total, $consumption1, $consumption2) {
    if ($consumption1 < 0 || $consumption2 < 0)
        return '<strong>' . $total . '</strong>';
    else
        return $total;
}

function Check4 ($total, $consumption1, $consumption2, $consumption3, $consumption4) {
    if ($consumption1 < 0 || $consumption2 < 0 || $consumption3 < 0 || $consumption4 < 0)
        return '<strong>' . $total . '</strong>';
    else
        return $total;
}
?>
