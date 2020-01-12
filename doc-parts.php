<?php
function DocHead ($title, $jsFile) {
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.0//EN" "http://www.w3.org/TR/REC-html40/strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
    <meta name="description" content="Readings submission" />
    <meta name="author" content="L. Brodski" />
    <meta name="allow-search" content="no" />
    <title><?=$title?></title>
    <link rel="stylesheet" type="text/css" media="screen" href="readings.css" />
    <link rel="stylesheet" type="text/css" media="print" href="readings-prn.css" />
<?php
    if (empty($jsFile)) {
?>
</head>
<body>
<?php
    } else {
?>
    <script type="text/javascript" src="<?=$jsFile?>"></script>
</head>
<body onunload="OnUnload()">
<?php
    }
}

function DocumentEnd ($selectedId, $seeBottom) {
?>
<br />
<br />
<p class="c"><span class="scrn">&copy; 2009</span> Leonid Brodski</p>

<script type="text/javascript" language="JavaScript">
<?php
    if (! empty($selectedId)) {
?>
		selectElement('<?=$selectedId?>');
<?php
    }
    if ($seeBottom) {
?>
        window.scroll(0, 1000000);
<?php
    }
?>
</script>
</body>
</html>
<?php
}
?>
