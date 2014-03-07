<?php
#!!!!!!! ist nur provisorisch muss die ganzen docu.php in index.php wandeln
$uri   = $_SERVER['HTTP_HOST'].rtrim(dirname($_SERVER['PHP_SELF']), '/\\');
header("Location: http://$uri/docu.php");
exit;
?>
