<?php if(!defined('IS_CMS')) die();
if(false !== ($name = getRequestValue('file',"get"))) {
    $file = $CatPage->get_pfadFile(CAT_REQUEST,$name);
    $ext = strtolower(substr(strrchr($name,"."),1));
    # PHP-Dateien dürfen nicht heruntergeladen werden oder Datei gibt es nicht
    if($file === false or $ext == "php") {
        header("HTTP/1.1 404 Not Found");
        exit;
    }
    # Header schreiben
    header("Pragma: public");
    header("Expires: 0");
    header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
    header("Cache-Control: public");
    header("Content-Description: File Transfer");
    # oben ausgewählter Content-Type
    header("Content-Type: ".getMimeType($ext,true));
    # Datei direkt im Browser anzeigen (inline);
    $disposition = "inline;";
    # Mit "Content-Disposition: attachment" wird der Download über ein Downloadfenster erzwungen:
    if(false !== getRequestValue('dialog',"get"))
        $disposition = "attachment;";
    header("Content-Disposition: ".$disposition." filename=".$name.";");
    header("Content-Transfer-Encoding: binary");
    header("Content-Length: ".filesize($file));
    @readfile($file);
    exit;
}
?>