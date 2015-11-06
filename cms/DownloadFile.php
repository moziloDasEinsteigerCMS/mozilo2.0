<?php if(!defined('IS_CMS')) die();

if(false !== ($filename = getRequestValue('file',"get"))
        and false !== ($file = $CatPage->get_pfadFile(CAT_REQUEST,$filename))) {
    # Infos zur Datei
    $len = filesize($file);
    $file_extension = strtolower(substr(strrchr($filename,"."),1));

    # abhängig von der Extension: Content-Type setzen
    switch($file_extension) {
          case "pdf":  $ctype = "application/pdf"; break;
          case "exe":  $ctype = "application/octet-stream"; break;
          case "zip":  $ctype = "application/zip"; break;
          case "doc":
          case "docx": $ctype = "application/msword"; break;
          case "xls":
          case "xlsx": $ctype = "application/vnd.ms-excel"; break;
          case "ppt":  $ctype = "application/vnd.ms-powerpoint"; break;
          case "gif":  $ctype = "image/gif"; break;
          case "png":  $ctype = "image/png"; break;
          case "jpeg":
          case "jpg":  $ctype = "image/jpg"; break;
          case "mp3":  $ctype = "audio/mpeg"; break;
          case "wav":  $ctype = "audio/x-wav"; break;
          case "mpeg":
          case "mpg":
          case "mpe":  $ctype = "video/mpeg"; break;
          case "mov":  $ctype = "video/quicktime"; break;
          case "avi":  $ctype = "video/x-msvideo"; break;
          case "txt":  $ctype = "text/plain"; break;
          case "htm":
          case "html": $ctype = "Content-type:text/html"; break;
          # PHP-Dateien dürfen nicht heruntergeladen werden
          case "php":  die("Fatal Error."); break;
          default:     $ctype = "application/force-download";
    }

    # Header schreiben
    header("Pragma: public");
    header("Expires: 0");
    header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
    header("Cache-Control: public");
    header("Content-Description: File Transfer");
    # oben ausgewählter Content-Type
    header("Content-Type: ".$ctype);
    # Datei direkt im Browser anzeigen (inline); Dateinamen setzen
    $header = "Content-Disposition: inline; filename=".$filename.";";
    # Mit "Content-Disposition: attachment" wird der Download über ein Downloadfenster erzwungen:
    if(false !== getRequestValue('dialog',"get"))
        $header = "Content-Disposition: attachment; filename=".$filename.";";
    header($header);
    header("Content-Transfer-Encoding: binary");
    header("Content-Length: ".$len);
    @readfile($file);
    exit;
}

?>