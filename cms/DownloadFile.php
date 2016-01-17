<?php if(!defined('IS_CMS')) die();
$file = BASE_DIR.CONTENT_DIR_NAME."/".$cat."/".CONTENT_FILES_DIR_NAME."/".$name;
$ext = strtolower(substr(strrchr($name,"."),1));
# PHP-Dateien dürfen nicht heruntergeladen werden oder Datei gibt es nicht
if(!file_exists($file) or $ext == "php") {
    header("HTTP/1.1 404 Not Found");
    exit;
}
# Header schreiben
header("Pragma: public");
header("Expires: 0");
header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
header("Cache-Control: public",false);
header("Content-Description: File Transfer");
header("Content-Type: ".getHeaderMimeType($ext,true));
# Datei direkt im Browser anzeigen (inline);
$disposition = "inline;";
# Mit "Content-Disposition: attachment" wird der Download über ein Downloadfenster erzwungen:
if(false !== getRequestValue('dialog',"get"))
    $disposition = "attachment;";
header("Content-Disposition: $disposition filename=\"".$name."\";");
header("Content-Transfer-Encoding: binary");
header("Content-Length: ".@filesize($file));
@readfile($file);
exit;
?>