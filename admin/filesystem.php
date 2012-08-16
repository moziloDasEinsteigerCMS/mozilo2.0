<?php if(!defined('IS_ADMIN') or !IS_ADMIN) die();

/* 
* 
* $Revision: 873 $
* $LastChangedDate: 2011-05-11 13:08:32 +0200 (Mi, 11. Mai 2011) $
* $Author: stefanbe $
*
*/


function dirsize($dir) {
    if(!is_dir($dir) or !is_readable($dir)) return false;
    $size = 0;
    $dh = opendir($dir);
    while(($entry = readdir($dh)) !== false) {
        if($entry == "." or $entry == "..")
            continue;
        if(is_file($dir."/".$entry))
            $size += filesize($dir."/".$entry);
        elseif(is_dir($dir."/".$entry))
            $size += dirsize($dir."/".$entry);
        else
            continue;
    }
    closedir($dh);
    return $size;
}

function convertFileSizeUnit($filesize) {
    if($filesize === false) return false;
    if ($filesize < 1024)
        return $filesize . "&nbsp;B";
    elseif ($filesize < 1048576)
        return round(($filesize/1024) , 2) . "&nbsp;KB";
    else
        return round(($filesize/1024/1024) , 2) . "&nbsp;MB";
}

# gibt die Rechte zurück ist $dir true wird das x bit gesetzt
function getChmod($dir = false) {
    if(USE_CHMOD === false)
        return false;
    global $ADMIN_CONF;
    $mode = $ADMIN_CONF->get("chmodnewfilesatts");
    if(strlen($mode) > 0) {
        if($dir === true) {
            // X-Bit setzen, um Verzeichniszugriff zu garantieren
            if(substr($mode,0,1) >= 2 and substr($mode,0,1) <= 6) $mode = $mode + 100;
            if(substr($mode,1,1) >= 2 and substr($mode,1,1) <= 6) $mode = $mode + 10;
            if(substr($mode,2,1) >= 2 and substr($mode,2,1) <= 6) $mode = $mode + 1;
        }
        return octdec($mode);
    }
    # Der server Vergibt die Rechte
    return false;
}

function setChmod($file) {
    if(USE_CHMOD === false)
        return true;
    // Existenz prüfen
    if(!file_exists($file))
        return returnMessage(false, getLanguageValue("error_no_file_dir"));
    if(is_dir($file))
        $chmod = getChmod(true);
    elseif(is_file($file))
        $chmod = getChmod();
    # rechte macht der server
    if($chmod === false)
        return true;
    if(true === (chmod($file, $chmod)))
        return true;
    return returnMessage(false, getLanguageValue("error_chmod"));
}

function setUserFilesChmod() {
    # der server kümert sich um die rechte
    if(false === getChmod())
        return true;

    $dirs = array(
            BASE_DIR_ADMIN.CONF_DIR_NAME."/" => true,
            BASE_DIR_CMS.CONF_DIR_NAME."/" => true,
            GALLERIES_DIR_REL => false,
            CONTENT_DIR_REL => false,
            BASE_DIR.PLUGIN_DIR_NAME."/" => true,
            BASE_DIR.LAYOUT_DIR_NAME."/" => false,
            BASE_DIR."backup/" => true
        );

    foreach($dirs as $dir => $onlyconf) {
        if(true !== ($error = setUserRecursivChmod($dir,$onlyconf)))
            return $error;
    }
    return true;
}

function setUserRecursivChmod($dir,$onlyconf = false) {
    // Existenz prüfen
    if(!file_exists($dir))
        return returnMessage(false, getLanguageValue("error_no_file_dir"));
    if(true !== ($error = setChmod($dir)))
        return $error;

    if(is_dir($dir) and false !== ($currentdir = opendir($dir))) {
        while(false !== ($file = readdir($currentdir))) {
            if($file[0] === '.') {
                continue;
            }
            if($onlyconf and !is_dir($dir.$file) and substr($file,-(strlen(".conf.php"))) != ".conf.php")
                continue;
            if(is_dir($dir.$file) and true !== ($error = setUserRecursivChmod($dir.$file."/",$onlyconf)))
                return $error;
            if(true !== ($error = setChmod($dir.$file)))
                return $error;
        }
        closedir($currentdir);
    }
    return true;
}

// Lösche ein Verzeichnis rekursiv
function deleteDir($path) {
    // Existenz prüfen
    if(!file_exists($path))
        return returnMessage(false, getLanguageValue("error_no_file_dir"));
    if(substr($path,-1) != "/")
        $path = $path."/";
    # alle dateien löschen
    $handle = opendir($path);
    while($currentelement = readdir($handle)) {
        if($currentelement[0] == ".")
            continue;
        // Verzeichnis: Rekursiver Funktionsaufruf
        if(is_dir($path.$currentelement)) {
            if(true !== ($error = deleteDir($path.$currentelement)))
                return $error;
        // Datei: löschen
        } else {
            if(true !== ($error = deleteFile($path.$currentelement)))
                return $error;
        }
    }
    closedir($handle);
    // Verzeichnis löschen
    if(true !== (@rmdir($path)))
        return returnMessage(false, getLanguageValue("error_del_dir"));
    return true;
}

function deleteFile($path) {
    // Existenz prüfen
    if(!file_exists($path))
        return returnMessage(false, getLanguageValue("error_no_file_dir"));
    if(true !== (@unlink($path)))
        return returnMessage(false, getLanguageValue("error_del_file"));
    return true;
}

function copyFile($org,$new) {
    // Existenz prüfen
    if(!file_exists($org))
        return returnMessage(false, getLanguageValue("error_no_file_dir"));
    if(file_exists($new))
        return returnMessage(false, getLanguageValue("error_exists_file_dir"));

    if(true !== (@copy($org,$new)))
        return returnMessage(false, getLanguageValue("error_copy_file"));
    if(true !== ($error = setChmod($new)))
        return $error;
    return true;
}

// ------------------------------------------------------------------------------
// Beim Ändert von cat, page, file und gallery namen wird in allen Inhalteseiten
// und template.html diese geändert
// ------------------------------------------------------------------------------
# wird nur von moveFileDir() aufgerufen
# in den plugin.conf.php's muss der cat, page/file, gallery name urlcodiert sein nur die
# trennung : nicht und es mus FILE_START und FILE_END benutzt werden
function updateFileNameInAll($old_name,$new_name) {
    # nur diese pfade werden unterstüzt
    # dir/kategorie/CAT
    # dir/kategorie/CAT/PAGE
    # dir/kategorie/CAT/dateien/FILE
    # dir/galerien/GALLERY

    # Kategorie Inhaltseite/Datei
    if(strstr($old_name,"/".CONTENT_DIR_NAME."/")) {
        $old_name = str_replace(CONTENT_DIR_REL,"",$old_name);
        $new_name = str_replace(CONTENT_DIR_REL,"",$new_name);
        # es ist eine Datei
        if(strstr($old_name,"/".CONTENT_FILES_DIR_NAME."/")) {
            $old_name = str_replace("/".CONTENT_FILES_DIR_NAME."/",":",$old_name);
            $new_name = str_replace("/".CONTENT_FILES_DIR_NAME."/",":",$new_name);
            $old_name_p = array(FILE_START.$old_name.FILE_END);
            $new_name_p = array(FILE_START.$new_name.FILE_END);
            $old_name = FILE_START.rawurldecode($old_name).FILE_END;
            $new_name = FILE_START.rawurldecode($new_name).FILE_END;
        # es wurde die Kategorie oder Inhaltseite geändert
        } else {
            # es wurde nur die Inhaltseite geändert
            if(strstr($old_name,"/")) {
                $old_name = str_replace("/",":",$old_name);
                $new_name = str_replace("/",":",$new_name);
                $old_name_p = array(FILE_START.$old_name.FILE_END);
                $new_name_p = array(FILE_START.$new_name.FILE_END);
                $old_name = FILE_START.rawurldecode($old_name).FILE_END;
                $new_name = FILE_START.rawurldecode($new_name).FILE_END;
            # es wurde die Kategorie geändert
            } else {
                $tmp_oldname = rawurldecode($old_name);
                $tmp_newname = rawurldecode($new_name);
                $tmp_oldname_p = $old_name;
                $tmp_newname_p = $new_name;
                $tmp_dir = CONTENT_DIR_REL.$new_name;
                $old_name = array(FILE_START.$tmp_oldname.FILE_END);
                $new_name = array(FILE_START.$tmp_newname.FILE_END);
                $old_name_p = array(FILE_START.$tmp_oldname_p.FILE_END);
                $new_name_p = array(FILE_START.$tmp_newname_p.FILE_END);
                # alle Inhaltseiten
                foreach(getDirAsArray($tmp_dir,array(EXT_PAGE,EXT_HIDDEN,EXT_DRAFT)) as $page) {
                    $page_tmp = str_replace(array(EXT_PAGE,EXT_HIDDEN,EXT_DRAFT),"",$page);
                    $old_name[] = FILE_START.$tmp_oldname.":".rawurldecode($page_tmp).FILE_END;
                    $new_name[] = FILE_START.$tmp_newname.":".rawurldecode($page_tmp).FILE_END;
                    $old_name_p[] = FILE_START.$tmp_oldname_p.":".$page_tmp.FILE_END;
                    $new_name_p[] = FILE_START.$tmp_newname_p.":".$page_tmp.FILE_END;
                }
                # alle Dateien
                foreach(getDirAsArray($tmp_dir."/".CONTENT_FILES_DIR_NAME,"file") as $file) {
                    $old_name[] = FILE_START.$tmp_oldname.":".rawurldecode($file).FILE_END;
                    $new_name[] = FILE_START.$tmp_newname.":".rawurldecode($file).FILE_END;
                    $old_name_p[] = FILE_START.$tmp_oldname_p.":".$file.FILE_END;
                    $new_name_p[] = FILE_START.$tmp_newname_p.":".$file.FILE_END;
                }
            }
        }
    # Gallery
    } elseif(strstr($old_name,"/".GALLERIES_DIR_NAME."/")) {
        $old_name = str_replace(GALLERIES_DIR_REL,"",$old_name);
        $new_name = str_replace(GALLERIES_DIR_REL,"",$new_name);
        $old_name_p = array(FILE_START.$tmp_oldname.FILE_END);
        $new_name_p = array(FILE_START.$tmp_newname.FILE_END);
        $old_name = FILE_START.rawurldecode($old_name).FILE_END;
        $new_name = FILE_START.rawurldecode($new_name).FILE_END;
    } else
        return;
#$sort_array = var_export($old_name,true);
#$sort_array .= var_export($new_name,true);
#file_put_contents(BASE_DIR_ADMIN."options.txt",$sort_array."\n",FILE_APPEND);
#file_put_contents(BASE_DIR_ADMIN."options.txt",$cat."/".$page."\n",FILE_APPEND);
/*if($plugin_dir == "SidebarPage") {
    $sort_array = var_export($old_name_p,true);
    file_put_contents(BASE_DIR_ADMIN."options.txt",$sort_array."\n",FILE_APPEND);
}*/
/*if($plugin_dir == "SidebarPage")
    file_put_contents(BASE_DIR_ADMIN."options.txt","new=".$new_name_p[$pos]."\n",FILE_APPEND);*/
/*if($plugin_dir == "SidebarPage")
    file_put_contents(BASE_DIR_ADMIN."options.txt","if=".$test." == ".$value."\n",FILE_APPEND);*/
/*if($plugin_dir == "SidebarPage")
    file_put_contents(BASE_DIR_ADMIN."options.txt","set=".$value."\n",FILE_APPEND);*/

    # Inhaltseiten
    foreach(getDirAsArray(CONTENT_DIR_REL,"dir") as $cat) {
        if(substr($cat, -(EXT_LENGTH)) == EXT_LINK)
            continue;
        foreach(getDirAsArray(CONTENT_DIR_REL.$cat,array(EXT_PAGE,EXT_HIDDEN,EXT_DRAFT)) as $page) {
            updateFileName(CONTENT_DIR_REL.$cat."/".$page,$old_name,$new_name);
        }
    }
    # alle template.html und gallerytemplate.html dateien
    foreach(getDirAsArray(BASE_DIR.LAYOUT_DIR_NAME,"dir") as $template_dir) {
        if(file_exists(BASE_DIR.LAYOUT_DIR_NAME."/".$template_dir."/template.html")) {
            updateFileName(BASE_DIR.LAYOUT_DIR_NAME."/".$template_dir."/template.html",$old_name,$new_name);
        }
        if(file_exists(BASE_DIR.LAYOUT_DIR_NAME."/".$template_dir."/gallerytemplate.html")) {
            updateFileName(BASE_DIR.LAYOUT_DIR_NAME."/".$template_dir."/gallerytemplate.html",$old_name,$new_name);
        }
    }
    # Plugins Conf
    foreach(getDirAsArray(BASE_DIR.PLUGIN_DIR_NAME,"dir") as $plugin_dir) {
        if(file_exists(BASE_DIR.PLUGIN_DIR_NAME."/".$plugin_dir."/plugin.conf.php")) {
            $tmp_conf = new Properties(BASE_DIR.PLUGIN_DIR_NAME."/".$plugin_dir."/plugin.conf.php");
            $tmp_conf_array = $tmp_conf->toArray();
            foreach($old_name_p as $pos => $test) {
                foreach($tmp_conf_array as $key => $value) {
                    if($test == $value) {
                        $tmp_conf->set($key,$new_name_p[$pos]);
                    }
                }
            }
            unset($tmp_conf,$tmp_conf_array);
        }
    }
}

function updateFileName($file,$old_name,$new_name) {
    $content = file_get_contents($file);
    $content_new = str_replace($old_name,$new_name,$content);
    # nur wenn sich was geändert hat inhalt schreiben
    if($content != $content_new) {
        file_put_contents($file,$content_new);
    }
}

function moveFileDir($org,$new,$datei_file = false) {
    // Existenz prüfen
    if(!file_exists($org))
        return returnMessage(false, getLanguageValue("error_no_file_dir"));
    if(file_exists($new))
        return returnMessage(false, getLanguageValue("error_exists_file_dir"));

    if($datei_file and cleanUploadFile(basename($new)) != basename($new)) {
        return returnMessage(false, getLanguageValue("error_datei_file_name"));
    }

    if(true !== (@rename($org,$new)))
        return returnMessage(false, getLanguageValue("error_move_file_dir"));
    updateFileNameInAll($org,$new);
    return true;
}

function mkdirMulti($dirs) { #error_exists_file_dir
    if(is_array($dirs)) {
        foreach($dirs as $dir) {
            // Existenz prüfen
            if(file_exists($dir))
                return returnMessage(false, getLanguageValue("error_exists_file_dir"));
            if(true !== (@mkdir($dir)))
                return returnMessage(false, getLanguageValue("error_mkdir"));
            if(true !== ($error = setChmod($dir)))
                return $error;
        }
    } else {
        // Existenz prüfen
        if(file_exists($dirs))
            return returnMessage(false, getLanguageValue("error_exists_file_dir"));
        if(true !== (@mkdir($dirs)))
            return returnMessage(false, getLanguageValue("error_mkdir"));
        if(true !== ($error = setChmod($dirs)))
            return $error;
    }
    return true;
}

function saveContentToPage($content, $page, $new = false) {
    // Existenz prüfen
    if($new and file_exists($page))
        return returnMessage(false, getLanguageValue("error_exists_file_dir"));
    global $page_protect;
    $chmod = false;
    # nee neue datei wird angelegt da brauchen wir chmod
    if(!is_file($page))
        $chmod = true;

    if(false === (file_put_contents($page, $page_protect.$content, LOCK_EX)))
        return returnMessage(false, getLanguageValue("editor_content_error_save"));

    if($chmod)
        return setChmod($page);
    return true;
}

function mo_file_put_contents($file,$content) {
    $setchmod = false;
    if(!is_file($file) and false !== ($chmod = getChmod()))
        $setchmod = true;
    if(false === (file_put_contents($file,$content, LOCK_EX)))
        return false;
    if($setchmod and false === (chmod($file, $chmod)))
        return false;
    return true;
}

function get_contents_ace_edit($file) {
    if(!file_exists($file) or false === ($content = file_get_contents($file)))
        return false;
    global $page_protect_search;
    $content = str_replace($page_protect_search,"",$content);
    $content = str_replace("&","&#38;",$content);
    return $content;
}

function newConf($file) {
    global $page_protect;
    if(false === (mo_file_put_contents($file,$page_protect.serialize(array()))))
        return false;
    return true;
}

# was ist mit gallery.php und ftp
# oder in CatPageClass.php wenn admin dan einfach umbennen?
# oder glogal in getDirAsArray()?
function cleanUploadFile($file) {
    global $specialchars;
    $file = $specialchars->rebuildSpecialChars($file, false, false);
    $search = array("ä","ö","ü","Ä","Ö","Ü","ß"," ");
    $replace = array("ae","oe","ue","Ae","Oe","Ue","ss","_");
    # Also remove control characters and spaces (\x00..\x20) around the filename:
    $file = trim($file, ".\x00..\x20");
    $file = str_replace($search,$replace,$file);
    $file = preg_replace('/[^a-zA-Z0-9._-]/', "",$file);
    return $file;
}
?>
