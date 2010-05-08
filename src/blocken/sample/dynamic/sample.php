<?php
$bIsAuth      = true;
$iCacheExpire = 10;
$sContentType = 'html';

$objTemplate = loadTemplate();
$objTemplate->setVariable( 'time', date( 'Y/m/d H:i:s' ) );
$objTemplate->show();
?>
