<?php
$org_klammern = array("[","|","]");
$tmp_klammern = array("-k_op~","-k_gr~","-k_cl~");
$org_saved = array("^[","^|","^]");
$tmp_saved = array("-ge_op~","-ge_gr~","-ge_cl~");
$css_new['.aligncenter'] = '
/* -------------------------------------------------------- */
/* [zentriert|...] */
/* --------------- */
.aligncenter {
    text-align:center;
}';

$css_new['.alignleft'] = '
/* -------------------------------------------------------- */
/* [links|...] */
/* ----------- */
.alignleft {
    text-align:left;
}';

$css_new['.alignright'] = '
/* -------------------------------------------------------- */
/* [rechts|...] */
/* ------------ */
.alignright {
    text-align:right;
}';

$css_new['.alignjustify'] = '
/* -------------------------------------------------------- */
/* [block|...] */
/* ----------- */
.alignjustify {
    text-align:justify;
}';

$css_new['.tableofcontents'] = '
/* -------------------------------------------------------- */
/* {TABLEOFCONTENTS} */
/* ----------------- */
div.tableofcontents ul ul {
    /*padding-left:15px;*/
}
div.tableofcontents li.blind {
    list-style-type:none;
    list-style-image:none;
}';

$css_new['#searchfieldset'] = '
fieldset#searchfieldset {
   border:none;
   margin:0px;
   padding:0px;
}';

$css_replace['div.imagesubtitle'] = 'span.imagesubtitle';
$css_replace['div.leftcontentimage'] = 'span.leftcontentimage';
$css_replace['div.rightcontentimage'] = 'span.rightcontentimage';
$css_replace['em.deadlink'] = '.deadlink';
$css_replace['em.highlight'] = '.highlight';

$oldkey_newkey = array(
                    'gallerymaxheight' => 'maxheight',
                    'gallerymaxwidth' => 'maxwidth',
                    'galleryusethumbs' => 'usethumbs',
                    'gallerypicsperrow' => 'picsperrow',
                    'targetblank_gallery' => 'target',
                    'Deutsch' => 'deDE',
                    'English' => 'enEN',
                    'France' => 'frFR',
                    'Italian' => 'itIT',
                    'Portuguese' => 'ptBR',
                    'Hrvatski' => 'hrHr'
                    );
global $GALLERY, $SYNTAX, $gallery_plugin;
$gallery_plugin = false;
function update() {

    $head = "";
    $html = "";
    $update_submit = "";
    $update_pages = "";

    if(!isset($_POST['update_cms'])) {
        $updates = testUpdate();
        if($updates['conf'] !== "empty")
            $html .= contend_template(getLanguageValue("install_update_conf_help"),$updates['conf']);
        if($updates['kategorien'] !== "empty")
            $html .= contend_template(getLanguageValue("install_update_kategorien_help"),$updates['kategorien']);
        if($updates['galerien'] !== "empty")
            $html .= contend_template(getLanguageValue("install_update_galerien_help"),$updates['galerien']);
        if($updates['plugins'] !== "empty")
            $html .= contend_template(getLanguageValue("install_update_plugins_help"),$updates['plugins']);
        if($updates['layouts'] !== "empty")
            $html .= contend_template(getLanguageValue("install_update_layouts_help"),$updates['layouts']);

        if(strlen($html) > 1) {
            $head = contend_template(installHelp("install_update_help"),"");
            $update_button = false;
            foreach($updates as $status) {
                if($status === false) {
                    $update_button = false;
                    $head = contend_template(getLanguageValue("install_update_error"),false).$head;
                    break;
                }
                if($status === true)
                    $update_button = true;
            }
            if($update_button === true) {
                if($updates['kategorien'] === true)
                    $update_pages = '<input type="checkbox" value="true" name="update_pages" id="update_pages"><label for="update_pages">'.getLanguageValue("install_update_files_in_pages").'</label><br /><br />';
                $update_submit = $update_pages.'<input type="submit" name="update_cms" value="'.getLanguageValue("install_update_buttom").'" />';
                $update_submit = contend_template($update_submit,"");
            }
        } else
            $head = contend_template(installHelp("install_update_no_help"),"");
    } elseif(isset($_POST['update_cms'])) {
        makeUpdate();
        $head = contend_template(installHelp("install_update_successful"),true);
    }
    return array(true,$head.$html.$update_submit,true);
}

# $test_art = true -> sobalt irgendwas zum updaten gefunden wurde return true
# $test_art = conf, galerien, kategorien, plugins, layouts -> 
function testUpdate($test_art = false) {
    $update_staus = array();
    $update_staus['conf'] = "empty";
    if(is_dir(BASE_DIR.'update')) {
        if(isFileRW(BASE_DIR.'update')) {
            foreach(getDirAsArray(BASE_DIR.'update',array(".conf"),"none") as $datei) {
                if(!isFileRW(BASE_DIR.'update/'.$datei)) {
                    $update_staus['conf'] = false;
                    break;
                }
                if($test_art === true or $test_art === "conf")
                    return true;
                $update_staus['conf'] = true;
                break;
            }
        } else
            $update_staus['conf'] = false;
    }

    $update_staus['galerien'] = "empty";
    if(is_dir(BASE_DIR.GALLERIES_DIR_NAME)) {
        foreach(getDirAsArray(BASE_DIR.GALLERIES_DIR_NAME,"dir","none") as $datei) {
            foreach(getDirAsArray(BASE_DIR.GALLERIES_DIR_NAME.'/'.$datei,"file","none") as $img) {
                if($img == 'texte.conf') {
                    continue;
                }
                if(true === isUpdateFileName($img,"file")) {
                    if($test_art === true or $test_art === "galerien")
                        return true;
                    $update_staus['galerien'] = true;
                    break;
                }
            }
            if(isUpdateFileName($datei)) {
                $update_staus['galerien'] = true;
                break;
            }
        }
    }
    $update_staus['kategorien'] = "empty";
    if(is_dir(BASE_DIR.'kategorien')) {
        foreach(getDirAsArray(BASE_DIR.CONTENT_DIR_NAME,"dir","none") as $cat) {
            if(true === isUpdateFileName($cat,"catpage")) {
                if($test_art === true or $test_art === "kategorien")
                    return true;
                $update_staus['kategorien'] = true;
                break;
            }
            foreach(getDirAsArray(BASE_DIR.CONTENT_DIR_NAME.'/'.$cat,"file","none") as $page) {
                if(true === isUpdateFileName($page,"catpage")) {
                    if($test_art === true or $test_art === "kategorien")
                        return true;
                    $update_staus['kategorien'] = true;
                    break;
                }
                if(is_dir(BASE_DIR.CONTENT_DIR_NAME.'/'.$cat.'/'.CONTENT_FILES_DIR_NAME) and is_readable(BASE_DIR.CONTENT_DIR_NAME.'/'.$cat.'/'.CONTENT_FILES_DIR_NAME)) {
                    foreach(getDirAsArray(BASE_DIR.CONTENT_DIR_NAME.'/'.$cat.'/'.CONTENT_FILES_DIR_NAME,"file","none") as $file) {
                        if(true === isUpdateFileName($file,"file")) {
                            if($test_art === true or $test_art === "kategorien")
                                return true;
                            $update_staus['kategorien'] = true;
                            break;
                        }
                    }
                }
            }
        }
    }

    $update_staus['plugins'] = "empty";
    if(is_dir(BASE_DIR.PLUGIN_DIR_NAME)) {
        foreach(getDirAsArray(BASE_DIR.PLUGIN_DIR_NAME,"dir","none") as $datei) {
            if(is_file(BASE_DIR.PLUGIN_DIR_NAME.'/'.$datei.'/plugin.conf')) {
                if($test_art === true or $test_art === "plugins")
                    return true;
                $update_staus['plugins'] = true;
                break;
            }
        }
    }
    $update_staus['layouts'] = "empty";
    if(is_dir(BASE_DIR.LAYOUT_DIR_NAME)) {
        foreach(getDirAsArray(BASE_DIR.LAYOUT_DIR_NAME,"dir","none") as $datei) {
            if(is_file(BASE_DIR.LAYOUT_DIR_NAME.'/'.$datei.'/layoutsettings.conf')
                    or true === isUpdateFileName($datei)) {
                if($test_art === true or $test_art === "layouts")
                    return true;
                $update_staus['layouts'] = true;
                break;
            }
        }
    }
    if($test_art === true)
        return false;
    return $update_staus;
}

function makeUpdate() {
    $update_page_files = array();

    if(is_file(BASE_DIR.PLUGIN_DIR_NAME.'/Galerie/plugin.conf')) {
        updateConf(BASE_DIR.PLUGIN_DIR_NAME.'/Galerie/plugin.conf');
    }
    if(is_file(BASE_DIR.PLUGIN_DIR_NAME.'/Galerie/plugin.conf.php')) {
        global $gallery_plugin;
        $gallery_plugin = new Properties(BASE_DIR.PLUGIN_DIR_NAME.'/Galerie/plugin.conf.php');
    }
    if(is_dir(BASE_DIR.'update')) {
        $cms_confs = array("basic","main","passwords","gallery","version","logindata","downloads","catpage","syntax","user");
        $files = getDirAsArray(BASE_DIR.'update',array(".conf"),"none");
        if(count($files) > 0) {
            global $GALLERY, $SYNTAX;
            $SYNTAX = new Properties(BASE_DIR_CMS.CONF_DIR_NAME.'/syntax.conf.php');
            $GALLERY = new Properties(BASE_DIR_CMS.CONF_DIR_NAME.'/gallery.conf.php');
            foreach($files as $datei) {
                if(in_array(str_replace(".conf","",$datei),$cms_confs)) {
                    updateCMSConf($datei);
                    mo_unlink(BASE_DIR.'update/'.$datei);
                }
            }
        }
    }

    if(is_dir(BASE_DIR.'galerien') and is_readable(BASE_DIR.'galerien')) {
        foreach(getDirAsArray(BASE_DIR.GALLERIES_DIR_NAME,"dir","none") as $datei) {
            $tmp_conf = false;
            if(is_file(BASE_DIR.GALLERIES_DIR_NAME.'/'.$datei.'/texte.conf')) {
                updateConf(BASE_DIR.GALLERIES_DIR_NAME.'/'.$datei.'/texte.conf');
            } else
                newConf(BASE_DIR.GALLERIES_DIR_NAME.'/'.$datei."/texte.conf.php");
            if(is_file(BASE_DIR.GALLERIES_DIR_NAME.'/'.$datei.'/texte.conf.php')) {
                $tmp_conf = new Properties(BASE_DIR.GALLERIES_DIR_NAME.'/'.$datei."/texte.conf.php");
            }
            $update_text = array();
            foreach(getDirAsArray(BASE_DIR.GALLERIES_DIR_NAME.'/'.$datei,"file","none") as $img) {
                if($img == 'texte.conf') continue;
                if(false !== ($newname = isUpdateFileName($img,"file",true))) {
                    $status = updateRename($img,$newname,BASE_DIR.GALLERIES_DIR_NAME."/".$datei."/");
                    if($status) {
                        $newname = $status;
                    }
                    if($tmp_conf !== false and $tmp_conf->keyExists($img)) {
                        $tmp_conf->set($newname,toUtf($tmp_conf->get($img)));
                        $tmp_conf->delete($img);
                    }
                    $update_page_files[$img] = $newname;
                    if(is_file(BASE_DIR.GALLERIES_DIR_NAME."/".$datei."/".PREVIEW_DIR_NAME."/".$img))
                        updateRename($img,$newname,BASE_DIR.GALLERIES_DIR_NAME."/".$datei."/".PREVIEW_DIR_NAME."/");
                }
            }
            unset($tmp_conf);
            if(false !== ($newname = isUpdateFileName($datei,false,true)))
                updateRename($datei,$newname,BASE_DIR.GALLERIES_DIR_NAME."/");
        }
    }

    if(is_dir(BASE_DIR.'kategorien')) {
        $sort_array = array();
        $cats = getDirAsArray(BASE_DIR.CONTENT_DIR_NAME,"dir","sort");
        $cats = sort_cat_page($cats,BASE_DIR.CONTENT_DIR_NAME,"dir");
        foreach($cats as $cat) {
#echo $cat."<br />\n";
            if(false !== ($newname = isUpdateFileName($cat,"catpage",true))) {
                updateRename($cat,$newname,BASE_DIR.CONTENT_DIR_NAME."/");
                $cat = $newname;
            }
            $sort_array[$cat] = "null";
            if(substr($cat, -(EXT_LENGTH)) == EXT_LINK)
                continue;
            $sort_array[$cat] = array();
            $pages = getDirAsArray(BASE_DIR.CONTENT_DIR_NAME.'/'.$cat,"file","sort");
            $pages = sort_cat_page($pages,BASE_DIR.CONTENT_DIR_NAME.'/'.$cat,"file");
            foreach($pages as $page) {
                if(false !== ($newname = isUpdateFileName($page,"catpage",true))) {
                    updateRename($page,$newname,BASE_DIR.CONTENT_DIR_NAME."/".$cat."/");
                    $page = $newname;
                }
                $sort_array[$cat][$page] = "null";
            }
            if(is_dir(BASE_DIR.CONTENT_DIR_NAME.'/'.$cat.'/'.CONTENT_FILES_DIR_NAME)) {
                foreach(getDirAsArray(BASE_DIR.CONTENT_DIR_NAME.'/'.$cat.'/'.CONTENT_FILES_DIR_NAME,"file","none") as $file) {
                    if(false !== ($newname = isUpdateFileName($file,"file",true))) {
                        $status = updateRename($file,$newname,BASE_DIR.CONTENT_DIR_NAME."/".$cat."/".CONTENT_FILES_DIR_NAME."/");
                        if($status)
                            $newname = $status;
                        $tmp_file = cleanUploadFile(changeMoziloOldSpecialChars($file,false));
                        $update_page_files[rawurldecode($cat)][$tmp_file] = $newname;
                    }
                }
            }
        }
        $sort_array = var_export($sort_array,true);
        file_put_contents(SORT_CAT_PAGE,"<?php if(!defined('IS_CMS')) die();\n\$cat_page_sort_array = ".$sort_array.";\n?>",LOCK_EX);
        if(isset($_POST['update_pages']) and $_POST['update_pages'] == "true")
            updatePages($update_page_files);
        }

    if(is_dir(BASE_DIR.'plugins')) {
        foreach(getDirAsArray(BASE_DIR.PLUGIN_DIR_NAME,"dir","none") as $datei) {
            if(is_file(BASE_DIR.PLUGIN_DIR_NAME.'/'.$datei.'/plugin.conf')) {
                updateConf(BASE_DIR.PLUGIN_DIR_NAME.'/'.$datei.'/plugin.conf');
            }
            if($datei == "CONTACT") {
                makeCONTACTSetings(BASE_DIR.PLUGIN_DIR_NAME.'/'.$datei.'/plugin.conf.php');
            }
        }
    }

    if(is_dir(BASE_DIR.'layouts')) {
        foreach(getDirAsArray(BASE_DIR.LAYOUT_DIR_NAME,"dir","none") as $datei) {
            if(is_file(BASE_DIR.LAYOUT_DIR_NAME.'/'.$datei.'/layoutsettings.conf')
                    and is_file(BASE_DIR.LAYOUT_DIR_NAME.'/'.$datei.'/template.html')) {
                updateTemplate(BASE_DIR.LAYOUT_DIR_NAME.'/'.$datei.'/');
            }
            if(is_dir(BASE_DIR.LAYOUT_DIR_NAME.'/'.$datei.'/css'))
                updateTemplateCSS(BASE_DIR.LAYOUT_DIR_NAME.'/'.$datei.'/');
            if(false !== ($newname = isUpdateFileName($datei,false,true))) {
                updateRename($datei,$newname,BASE_DIR.LAYOUT_DIR_NAME."/");
            }
        }
    }
}

function updateRename($old,$new,$dir) {
    if(!file_exists($dir.$old)) return false;
    if($old == $new) return false;
    $return = false;
    if(file_exists($dir.$new)) {
        $nr = 0;
        $exist = true;
        while(!$exist) {
            $nr++;
            $exist = file_exists($dir.$nr."_".$new);
        }
        $new = $nr."_".$new;
        $return = $nr."_".$new;
    }
#echo "rename=".$dir.$old." -> ".$dir.$new."<br />\n";
    rename($dir.$old,$dir.$new);
    return $return;
}

function isUpdateFileName($file,$type = false,$getnewname = false) {
    $tmpfile = $file;
    if($type == "catpage") {
        $oldext = array(".txt",".hid",".tmp",".lnk");
        if(strlen($file) > 3 and ctype_digit(substr($file,0,2)) and $file[2] == "_") {
            if(!$getnewname) return true;
            $file = substr($file,3);
        }
        if(strlen($file) > 4) {
            $test = substr($file,-4);
            if(in_array($test,$oldext)) {
                if(!$getnewname) return true;
                $file = $file.".php";
            }
        }
    } elseif($type == "file") {
        if($tmpfile != ($file = cleanUploadFile(changeMoziloOldSpecialChars($file,false)))) {
            if(!$getnewname) return true;
            if(strlen($file) > 0)
                return $file;
        }
        return false;
    }
    if($tmpfile != ($file = changeMoziloOldSpecialChars($file))) {
        if(!$getnewname) return true;
        if(strlen($file) > 0)
            return $file;
    }
    return false;
}

function changeMoziloOldSpecialChars($text,$urlcodet = true) {
    global $specialchars;
    $text = rawurldecode($text);
    $text = preg_replace("/-nbsp~/", " ", $text);
    // @, ?
    $text = preg_replace("/-at~/", "@", $text);
    $text = preg_replace("/-ques~/", "?", $text);
    // Alle mozilo-Entities in HTML-Entities umwandeln!
    $text = preg_replace("/-([^-~]+)~/U", "&$1;", $text);
    // & escapen 
    //$text = preg_replace("/&+(?!(.+);)/U", "&amp;", $text);
    $text = html_entity_decode($text,ENT_COMPAT,'ISO-8859-1');
    $text = toUtf($text);
    if($urlcodet) {
        $text = $specialchars->replaceSpecialChars($text,false);
        $text = str_replace("/","%2F",$text);
    }
    return $text;
}

function toUtf($string) {
    if(!check_utf8($string)) {
        if(function_exists("iconv")) {
            $string = iconv('cp1252', CHARSET.'//IGNORE',$string);
#            $string = iconv('ISO-8859-1', CHARSET.'//IGNORE',$string);
        } elseif(function_exists("mb_convert_encoding")) {
            $string = mb_convert_encoding($string, CHARSET);
        } elseif(function_exists("utf8_encode")) {
            $string = utf8_encode($string);
        }
    }
    return $string;
}

function check_utf8($str) {
    $len = strlen($str);
    for($i = 0; $i < $len; $i++){
        $c = ord($str[$i]);
        if ($c > 128) {
            if (($c > 247)) return false;
            elseif ($c > 239) $bytes = 4;
            elseif ($c > 223) $bytes = 3;
            elseif ($c > 191) $bytes = 2;
            else return false;
            if (($i + $bytes) > $len) return false;
            while ($bytes > 1) {
                $i++;
                $b = ord($str[$i]);
                if ($b < 128 || $b > 191) return false;
                $bytes--;
            }
        }
    }
    return true;
} // end of check_utf8

function updateCMSConf($file) {
    $tmp_file = str_replace(array(".conf",".conf.php"),"",$file);

    $old_conf = getTextConf(BASE_DIR.'update/'.$file);
    $syntax = false;
    if($tmp_file == "syntax")
        $syntax = true;
    setFromOldToNew($old_conf,$syntax);
}

function setFromOldToNew($old_conf,$syntax = false) {
    global $CMS_CONF, $ADMIN_CONF, $GALLERY, $SYNTAX, $oldkey_newkey, $specialchars, $gallery_plugin;
    foreach($old_conf as $key => $value) {
        if($key == "language" or $key == "chmodnewfilesatts" or $key == "cmslanguage" or $key == "modrewrite") continue;
        if($syntax) {
            $SYNTAX->set($key,$value);
            continue;
        }
        if(array_key_exists($key,$oldkey_newkey))
            $key = $oldkey_newkey[$key];
        if($key == "defaultcat" and false !== ($tmp = isUpdateFileName($value,"catpage",true)))
            $value = $tmp;
        $value = changeMoziloOldSpecialChars($value,false);
        $value = $specialchars->replaceSpecialChars($value,false);
        if($CMS_CONF->keyExists($key)) {
            $CMS_CONF->set($key,$value);
        } elseif($ADMIN_CONF->keyExists($key)) {
            $ADMIN_CONF->set($key,$value);
        } elseif($GALLERY->keyExists($key)) {
            $GALLERY->set($key,$value);
        } elseif($gallery_plugin and ($key == "usethumbs" or $key == "picsperrow" or $key == "target")) {
            if($key == "target") {
                if($value == "true")
                    $value = "_blank";
                else
                    $value = "_self";
            }
            $gallery_plugin->set($key,$value);
        }
    }
}

function updateConf($file) {
    global $page_protect;
    $new = getTextConf($file);

    $new = $page_protect.serialize($new);
    if(false === (file_put_contents($file,$new,LOCK_EX))) {
        echo "kann datei nicht schreiben ".$file."\n";
        die();
    }
    rename($file,$file.".php");
}

function makeCONTACTSetings($file) {
    if(is_file($file))
        $CONTACT = new Properties($file);
    else return;
    if(!$CONTACT->get('formularmail')) {
        global $ADMIN_CONF;
        if($ADMIN_CONF->get('adminmail'))
            $CONTACT->set('formularmail',str_replace("%40","@",$ADMIN_CONF->get('adminmail')));
    }
    if(is_file(BASE_DIR.'update/formular.conf')) {
        $tmp_conf = getTextConf(BASE_DIR.'update/formular.conf');
        mo_unlink(BASE_DIR.'update/formular.conf');
        foreach($tmp_conf as $name => $value) {
            $tmp_set = explode(",",$value);
            $tmp_name = "";
            $tmp_show = "";
            $tmp_mandatory = "";
            if($tmp_set[(count($tmp_set) - 1)] and $tmp_set[(count($tmp_set) - 1)] == "true")
                $tmp_mandatory = "true";
            if($tmp_set[(count($tmp_set) - 2)] and $tmp_set[(count($tmp_set) - 2)] == "true")
                $tmp_show = "true";
            if(count($tmp_set) == 3)
                $tmp_name = $tmp_set[0];
            $CONTACT->set('titel_'.$name,$tmp_name);
            $CONTACT->set('titel_'.$name.'_show',$tmp_show);
            $CONTACT->set('titel_'.$name.'_mandatory',$tmp_mandatory);
        }
    }
    if(is_file(BASE_DIR.'update/aufgaben.conf')) {
        $tmp_conf = getTextConf(BASE_DIR.'update/aufgaben.conf');
        mo_unlink(BASE_DIR.'update/aufgaben.prop');
        $tmp_set = array();
        foreach($tmp_conf as $name => $value) {
            $tmp_set[] = $name.' = '.$value;
        }
        if(count($tmp_set) > 0)
            $CONTACT->set('contactformcalcs',implode('<br />',$tmp_set));
    }
}


function getTextConf($file) {
    $new = array();
    $old = file($file);
    foreach($old as $zeile) {
        if(preg_match("/^#/",$zeile) || preg_match("/^\s*$/",$zeile)) continue;
        if(preg_match("/^([^=]*)=(.*)/",$zeile,$matches)) {
            $new[toUtf(trim($matches[1]))] = toUtf(trim($matches[2]));
        }
    }
    return $new;
}

function updatePages($update_page_files) {
    global $page_protect, $page_protect_search;
    global $org_klammern, $tmp_klammern, $org_saved, $tmp_saved;

    foreach(getDirAsArray(BASE_DIR.CONTENT_DIR_NAME,"dir","sort") as $cat) {
        if(substr($cat, -(EXT_LENGTH)) == EXT_LINK)
            continue;
        // Alle Inhaltseiten der aktuellen Kategorie einlesen 
        foreach(getDirAsArray(BASE_DIR.CONTENT_DIR_NAME."/".$cat,"file","sort") as $page) {
            if(substr($page, -(EXT_LENGTH)) == EXT_LINK)
                continue;
            // Inhalt auslesen
            $content = file_get_contents(BASE_DIR.CONTENT_DIR_NAME."/".$cat."/".$page);
            $newcontent = toUtf($content);
            $newcontent = $page_protect.str_replace($page_protect_search,"",$newcontent);
            $newcontent = str_replace($org_saved,$tmp_saved,$newcontent);
            # [liste1 - 3
            $newcontent = str_replace(array("[liste1|","[liste2|","[liste3|"),"[liste|",$newcontent);
            # um diese Attribute geht es
            $allowed_attributes = array("galerie","kategorie","seite","datei","bild","bildlinks","bildrechts","include");
            $finisch = false;
            while(!$finisch) {
                $tmp_newcontent = $newcontent;
                preg_match_all("/\[(".implode('|',$allowed_attributes).")\|(.*)\]/Umis",$newcontent,$matche);
                $newcontent = changeFileContent($newcontent,$matche,$cat,$update_page_files);
                preg_match_all("/\[(".implode('|',$allowed_attributes).")\=.*\|(.*)\]/Umis",$newcontent,$matche);
                $newcontent = changeFileContent($newcontent,$matche,$cat,$update_page_files);
                if($tmp_newcontent == $newcontent)
                    $finisch = true;
            }
            $newcontent = str_replace($tmp_saved,$org_saved,$newcontent);
            $newcontent = str_replace($tmp_klammern,$org_klammern,$newcontent);
            if($content !== $newcontent) {
                file_put_contents(BASE_DIR.CONTENT_DIR_NAME.'/'.$cat.'/'.$page,$newcontent,LOCK_EX);
            }
        }
    }
}

function changeFileContent($content,$matche,$cat,$update_page_files) {
    global $org_klammern, $tmp_klammern;
    if(count($matche[0]) < 1) return $content;
        $cat = rawurldecode($cat);
    $search = array();
    $replace = array();
    foreach($matche[0] as $key => $value) {
        if(substr($matche[2][$key],0,strlen(FILE_START)) == FILE_START and substr($matche[2][$key],-(strlen(FILE_END))) == FILE_END)
            continue;
        $search[$key] = $value;
        $tmp = FILE_START.$matche[2][$key].FILE_END;
        if($matche[1][$key] == "galerie") {
            $value = str_replace(array("[galerie","]"),array("{Galerie","}"),$value);
        } elseif($matche[1][$key] == "datei" or $matche[1][$key] == "bild" or $matche[1][$key] == "bildlinks" or $matche[1][$key] == "bildrechts") {
            $cat_page = explode(":",$matche[2][$key]);
            $tmp_cat = $cat;
            $tmp_file = $cat_page[0];
            if(count($cat_page) > 1) {
                $tmp_cat = $cat_page[0];
                $tmp_file = $cat_page[1];
            }
            if(isset($update_page_files[$tmp_cat][$tmp_file]))
                $tmp_file = $update_page_files[$tmp_cat][$tmp_file];
            $tmp = FILE_START.$tmp_cat.":".$tmp_file.FILE_END;
        } elseif($matche[1][$key] != "kategorie" and !strstr($matche[2][$key],":")) {
            $tmp = FILE_START.$cat.":".$matche[2][$key].FILE_END;
        }
        $replace[$key] = str_replace($org_klammern,$tmp_klammern,str_replace($matche[2][$key],$tmp,$value));
    }
    $content = str_replace($search,$replace,$content);
    return $content;
}

function updateTemplateCSS($dir) {
    global $css_new, $css_replace;
    $tmp_css_new = $css_new;
    $css_files = getDirAsArray($dir.'css',array(".css"),"none");
    if(count($css_files) < 1) return;
    foreach($css_files as $file) {
        $content = file_get_contents($dir.'css/'.$file);
        $tmp_content = $content;
        foreach($tmp_css_new as $key => $value) {
            if(strpos($content,$key) !== false)
                unset($tmp_css_new[$key]);
            elseif(count($css_files) == 1) {
                $tmp_content .= $tmp_css_new[$key];
                unset($tmp_css_new[$key]);
            }
        }
        foreach($css_replace as $key => $value) {
            $tmp_content = str_replace($key,$value,$tmp_content);
        }
        if($tmp_content != $content)
            file_put_contents($dir.'css/'.$file,$tmp_content,LOCK_EX);
    }
    if(isset($tmp_css_new) and count($tmp_css_new) > 0) {
        if(in_array("style.css",$css_files))
            $file = 'style.css';
        else
            $file = $css_files[0];
        $content = file_get_contents($dir.'css/'.$file);
        foreach($tmp_css_new as $key => $value) {
            $content .= $tmp_css_new[$key];
        }
        file_put_contents($dir.'css/'.$file,$content,LOCK_EX);
    }
}

function updateTemplate($dir) {
    $content = file_get_contents($dir.'template.html');
    $content = toUtf($content);
    $usesubmenu = "";
    if(!strstr($content,'<!-- usesubmenu = ') and file_exists($dir.'layoutsettings.conf')) {
        $old = getTextConf($dir.'layoutsettings.conf');
        if(isset($old['usesubmenu']))
            $usesubmenu = '<!-- usesubmenu = '.$old['usesubmenu'].' -->'."\n";
        else
            $usesubmenu = '<!-- usesubmenu = 0 -->'."\n";
        mo_unlink($dir.'layoutsettings.conf');
    }

    $search = array(
                str_replace(BASE_DIR,"",$dir),
                'layouts/{LAYOUT_DIR}',
                'ISO-8859-1',
                'iso-8859-1',
                '{CSS_FILE}',
                '{FAVICON_FILE}'
                );
    $replace = array(
                '{LAYOUT_DIR}',
                '{LAYOUT_DIR}',
                '{CHARSET}',
                '{CHARSET}',
                '{LAYOUT_DIR}/css/style.css',
                '{LAYOUT_DIR}/favicon.ico'
                );
    $content = str_replace($search,$replace,$content);
    $content = toUtf($content);
    file_put_contents($dir.'template.html',$usesubmenu.$content,LOCK_EX);
}

?>
