<?php if(!defined('IS_CMS')) die();

function cleanREQUEST($post_return) {
    foreach($post_return as $key => $value) {
#!!!!!! der key muss auch geprüft werden
        if(is_array($post_return[$key])) {
            $post_return[$key] = cleanREQUEST($post_return[$key]);
        } else {
            // Nullbytes abfangen!
            if (strpos("tmp".$value, "\x00") > 0 or strpos("tmp".$key, "\x00") > 0) {
                die();
            }

            # ein paar unötige sachen drumherum weg machen
            $key = trim($key, "\x00..\x20");# x20 = space
            # bei texten brauchen wir die bracks
            $value = str_replace(array("\r\n","\r","\n"),"-tmpbr_",$value);
            $value = trim($value, "\x00..\x19");# x20 = space
            $value = str_replace("-tmpbr_","\n",$value);

            # auf manchen Systemen mus ein stripslashes() gemacht werden
            if(strpos("tmp".$value,'\\') > 0
                and  addslashes(stripslashes($value)) == $value) {
                $value = stripslashes($value);
            }
            # auf manchen Systemen mus ein stripslashes() gemacht werden
            if(strpos("tmp".$key,'\\') > 0
                and  addslashes(stripslashes($key)) == $key) {
                $key = stripslashes($key);
            }
            if(function_exists("mb_convert_encoding")) {
                $value = @mb_convert_encoding($value,CHARSET,@mb_detect_encoding($value,"UTF-8,ISO-8859-1,ISO-8859-15",true));
            }
            $post_return[$key] = $value;
        }
    }
    return $post_return;
}

# gibt die POST oder GET value zurück wenn sie existiert ansonsten false
# um einen key aus einer array strücktur zu bekommen über gibt man den weg dahin als array
# z.B $_POST[sub1][sub2][sub3] = inhalt der dafür were $key = array('sub1','sub2','sub3')
# $art standart POST oder GET zurück
# $art = get nur get auswerten
# $art = post nur post auswerten
# ACHTUNG zu erhöung der sicherheit immer die $art angeben und nur drauf verzichten wenn es beides sein kann
function getRequestValue($key,$art = false,$clean = true) {
#!!!!!!!!! ein array erzeugen mit den abgefragten, wenn dann ne anfrage mit einer schon abgefragten rückgabe aus dem array dann brauch der ganze ratenschwanz nicht nochmal ausgeführt werden
    $return_value = false;
    if(is_array($key)) {
        if(!$art and array_key_exists($key[0],$_GET))
            $return_value = $_GET[$key[0]];
        elseif(!$art and array_key_exists($key[0],$_POST))
            $return_value = $_POST[$key[0]];
        elseif($art == "get" and array_key_exists($key[0],$_GET))
            $return_value = $_GET[$key[0]];
        elseif($art == "post" and array_key_exists($key[0],$_POST))
            $return_value = $_POST[$key[0]];
        else
            return false;
        unset($key[0]);
        foreach($key as $sub_key) {
            if(array_key_exists($sub_key,$return_value))
                $return_value = $return_value[$sub_key];
            else
                return false;
        }
        return $return_value;
    } else {
        if(!$art and array_key_exists($key,$_GET)) {
            $return_value = $_GET[$key];
        } elseif(!$art and array_key_exists($key,$_POST)) {
            $return_value = $_POST[$key];
        } elseif($art == "get" and array_key_exists($key,$_GET)) {
            $return_value = $_GET[$key];
        } elseif($art == "post" and array_key_exists($key,$_POST)) {
            $return_value = $_POST[$key];
        }
    }
    if(false !== $return_value) {
        if($clean)
            return cleanValue($return_value);
        return $return_value;
    }
    return false;
}

function cleanValue($value) {
    if(is_array($value)) {
        foreach($value as $key => $val) {
            $value[$key] = cleanValue($val);
        }
    } elseif(is_bool($value)) {
        return $value;
    } else {
        // Nullbytes abfangen!
        if (strpos("tmp".$value, "\x00") > 0) {
            die();
        }
        $value = rawurldecode($value);
        $value = stripslashes($value);
        $value = str_replace(array("\r\n","\r","\n"),"-tmpbr_",$value);
        $value = trim($value, "\x00..\x19");
        if(basename($value) != $value) {
            $value = str_replace(basename($value),trim(basename($value), "\x00..\x19"),$value);
        }
        $value = strip_tags($value);
        $value = str_replace("-tmpbr_","\n",$value);
        $value = mo_rawurlencode($value);
    }
    return $value;
}
// ------------------------------------------------------------------------------
// Handelt es sich um ein valides Verzeichnis / eine valide Datei?
// ------------------------------------------------------------------------------
function isValidDirOrFile($file) {
    # Alles, was einen Punkt vor der Datei hat
    if($file[0] === '.') {
        return false;
    }
    # keine PHP-Dateien auser die Kategorien und Inhaltseiten
    if(substr($file,-4) == ".php" and !in_array(substr($file,-(EXT_LENGTH)),array(EXT_PAGE,EXT_HIDDEN,EXT_DRAFT,EXT_LINK))) {
        return false;
    }
    # ...und der Rest
    if(in_array($file, array(
            "Thumbs.db", // Windows-spezifisch
            "__MACOSX", // Mac-spezifisch
            "settings" // Eclipse
            ))) {
        return false;
    }
    return true;
}

# $filetype = "dir" nur ordner
# $filetype = "file" nur dateien
# $filetype = "img" nur bilder
# $filetype = array(".txt",".hid",...) nur die mit dieser ext
#               Achtung Punkt nicht vergessen Gross/Kleinschreibung ist egal
# $filetype = false alle dateien user den .conf.php dateien
# $sort_type = "sort" (Default) oder "natcasesort" oder "none"
function getDirAsArray($dir,$filetype = false,$sort_type = "sort") {
    if($filetype == "img") {
        global $ALOWED_IMG_ARRAY;
        $filetype = $ALOWED_IMG_ARRAY;
    }
    # alle ext im array in kleinschreibung wandeln
    if(is_array($filetype))
        $filetype = array_map('strtolower', $filetype);
    $dateien = array();
    if(is_dir($dir) and false !== ($currentdir = opendir($dir))) {
        while(false !== ($file = readdir($currentdir))) {
            # keine gültige datei gleich zur nächsten datei
            if(!isValidDirOrFile($file))
                continue;
            # nur mit ext
            if(is_array($filetype)) {
                $ext1 = strtolower(substr($file,-(EXT_LENGTH)));
                $ext2 = strtolower(substr($file,strrpos($file,".")));
                if(in_array($ext2,$filetype) or in_array($ext1,$filetype)) {
                    $dateien[] = $file;
                }
            # nur dir oder file
            } elseif(filetype($dir."/".$file) == $filetype) {
                $dateien[] = $file;
            # alle
            } elseif(!$filetype) {
                $dateien[] = $file;
            }
        }
        closedir($currentdir);
        if($sort_type == "sort")
            sort($dateien);
        elseif($sort_type == "natcasesort")
            natcasesort($dateien);
        elseif(($sort_type == "sort_cat_page") and ($filetype == "file" or $filetype == "dir"))
            $dateien = sort_cat_page($dateien,$dir,$filetype);
    }
    return $dateien;
}

function sort_cat_page($dateien,$dir,$filetype) {
    global $cat_page_sort_array;
    if(!is_array($cat_page_sort_array) or count($cat_page_sort_array) < 1)
        return $dateien;

    if($filetype == "file") {
        $cat = basename($dir);
        if(isset($cat_page_sort_array[$cat])) {
            $files_array = $cat_page_sort_array[$cat];
        } else {
            return $dateien;
        }
    } else
        $files_array = $cat_page_sort_array;
    $ordered = array();
    $dateien = array_flip($dateien);
    foreach($files_array as $key => $value) {
        if(array_key_exists($key,$dateien)) {
            $ordered[] = $key;
            unset($dateien[$key]);
        }
    }
    $dateien = array_flip($dateien);
    return array_merge($ordered, $dateien);
}

function mo_rawurlencode($string) {
    $string = rawurlencode($string);
    $string = str_replace("~","%7E",$string);
    return $string;
}

function findPlugins() {
    $activ_plugins = array();
    $deactiv_plugins = array();
    $plugin_first = array();
    global $page_protect_search;
    // alle Plugins einlesen
    foreach(getDirAsArray(PLUGIN_DIR_REL,"dir") as $plugin) {
        # nach schauen ob das Plugin active ist
        if(file_exists(PLUGIN_DIR_REL.$plugin."/plugin.conf.php")
            and file_exists(PLUGIN_DIR_REL.$plugin."/index.php")) {
            if(false === ($conf_plugin = file_get_contents(PLUGIN_DIR_REL.$plugin."/plugin.conf.php")))
                die("Fatal Error Can't read file: ".$plugin."/plugin.conf.php");
            $conf_plugin = str_replace($page_protect_search,"",$conf_plugin);
            $conf_plugin = trim($conf_plugin);
            $conf_plugin = unserialize($conf_plugin);
            if(isset($conf_plugin["active"]) and $conf_plugin["active"] == "true") {
                # array fuehlen mit activen Plugin Platzhalter
                $activ_plugins[] = $plugin;
                if(isset($conf_plugin["plugin_first"]) and $conf_plugin["plugin_first"] === "true")
                    $plugin_first[] = $plugin;
            } else {
                # array fuehlen mit deactivierte Plugin Platzhalter
                $deactiv_plugins[] = $plugin;
            }
        # plugin gibts aber es gibt noch keine plugin.conf.php
        } elseif(file_exists(PLUGIN_DIR_REL.$plugin."/index.php")) {
            $deactiv_plugins[] = $plugin;
        }

        unset($conf_plugin);
    }
    return array($activ_plugins,$deactiv_plugins,$plugin_first);
}

function replaceFileMarker($file,$filesystem = true) {
    if(strpos($file,FILE_START) !== false and strpos($file,FILE_END) !== false) {
        if($filesystem) {
            preg_match_all('/'.FILE_START.'(.*)'.FILE_END.'/U',$file,$match);
            array_walk($match[1], 'helpFuncReplaceFileMarker');
            $file = str_replace($match[0],$match[1],$file);
            unset($match);
        } else
            $file = str_replace(array(FILE_START,FILE_END),"",$file);
    }
    return $file;
}

function helpFuncReplaceFileMarker(&$value, $key) {
    global $specialchars;
    $value = $specialchars->replaceSpecialChars($value,false);
    $value = str_replace("/","%2F",$value);
}

# diese function setzt die locale nur wenn es eine ??_??.utf8 gibt
# wird auf Windowsservern nicht gehen
function setTimeLocale($language) {
    global $CMS_CONF;
    $local = $CMS_CONF->get("cmslanguage").".utf8";
    $local = substr($local,0,2)."_".substr($local,2);
    $timezone = $language->getLanguageValue("_timezone");
    $tmp = @ini_get('date.timezone');
    if(false === @ini_set('date.timezone',$timezone))
        @ini_set('date.timezone',$tmp);
    $tmp = @date_default_timezone_get();
    if(false === @date_default_timezone_set($timezone))
        @date_default_timezone_set($tmp);
    $tmp = @setlocale(LC_TIME, "0");
    if(false === @setlocale(LC_TIME, $local))
        @setlocale(LC_TIME, $tmp);
}

function getHeaderMimeType($ext,$foceDownload = false) {
    # abhängig von der Extension: Content-Type setzen
    switch($ext) {
        case "pdf":  return "application/pdf";
        case "exe":  return "application/octet-stream";
        case "zip":  return "application/zip";
        case "rar":  return "application/x-rar-compressed";
        case "msi":  return "application/x-msdownload";
        case "cab":  return "application/vnd.ms-cab-compressed";
        case "doc":
        case "docx": return "application/msword";
        case "xls":
        case "xlsx": return "application/vnd.ms-excel";
        case "psd":  return "image/vnd.adobe.photoshop";
        case "ai":
        case "eps":
        case "ps":   return "application/postscript";
        case "rtf":  return "application/rtf";
        case "odt":  return "application/vnd.oasis.opendocument.text";
        case "ods":  return "application/vnd.oasis.opendocument.spreadsheet";
        case "ppt":  return "application/vnd.ms-powerpoint";
        case "gif":  return "image/gif";
        case "png":  return "image/png";
        case "jpe":
        case "jpeg":
        case "jpg":  return "image/jpg";
        case "bmp":  return "image/bmp";
        case "ico":  return "image/vnd.microsoft.icon";
        case "tiff":
        case "tif":  return "image/tiff";
        case "svg":
        case "svgz": return "image/svg+xml";
        case "mp3":  return "audio/mpeg";
        case "wav":  return "audio/x-wav";
        case "mpeg":
        case "mpg":
        case "mpe":  return "video/mpeg";
        case "qt":
        case "mov":  return "video/quicktime";
        case "avi":  return "video/x-msvideo";
        case "flv":  return "video/x-flv";
        case "txt":  return "text/plain";
        case "htm":
        case "html": return "text/html";
        case "css":  return "text/css";
        case "js":   return "application/javascript";
        case "json": return "application/json";
        case "xml":  return "application/xml";
        case "swf":  return "application/x-shockwave-flash";
        default:     return ($foceDownload ? "application/force-download" : "text/plain");
    }
}

function getMorTime() {
    @ini_set('max_execution_time', 120);
    @set_time_limit(120);
    if(function_exists('apache_reset_timeout'))
        @apache_reset_timeout();
}
?>