<?php

function update() {

    $head = "";
    $html = "";
    $update_submit = "";

    if(!isset($_POST['update_cms'])) {
        $updates = checkUpdate();
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
                $update_submit = '<input type="checkbox" value="true" name="update_pages" id="update_pages"><label for="update_pages">'.getLanguageValue("install_update_files_in_pages").'</label>';
                $update_submit .= '<br /><br /><input type="submit" name="update_cms" value="'.getLanguageValue("install_update_buttom").'" />';
                $update_submit = contend_template($update_submit,"");
            }
        } else
            $head = contend_template(installHelp("install_update_no_help"),"");
    } elseif(isset($_POST['update_cms'])) {
        checkUpdate(true);
        $head = contend_template(installHelp("install_update_successful"),true);
    }
    return array(true,$head.$html.$update_submit,true);
}

function checkUpdate($update = false) {
#    if(isset($_POST['check_update']))
#        return true;
#echo "checkUpdate";
    $datei_array = array();
    $update_staus = array();
    $update_page_files = array();
    $update_staus['conf'] = "empty";
    if(is_dir(BASE_DIR.'update')) {
        if(isFileRW(BASE_DIR.'update')) {
            $conf_array = array("basic","logindata","gallery","main","syntax",);
            foreach(scandir(BASE_DIR.'update') as $datei) {
                if($datei[0] == ".") continue;
                if(!in_array(str_replace(".conf","",$datei),$conf_array)) {
                    continue;
                }
                if(!isFileRW(BASE_DIR.'update/'.$datei)) {
                    $update_staus['conf'] = false;
                    break;
                }
                $update_staus['conf'] = true;
if(!$update)
    break;
                if($update) {
                    updateCMSConf($datei);
                    unlink(BASE_DIR.'update/'.$datei);
                }
            }
        } else
            $update_staus['conf'] = false;
    }
    $update_staus['galerien'] = "empty";
    if(is_dir(BASE_DIR.'galerien') and is_readable(BASE_DIR.'galerien')) {
        if(isFileRW(BASE_DIR.'galerien')) {
            foreach(scandir(BASE_DIR.'galerien') as $datei) {
                $update_text = array();
                if($datei[0] == "." or !is_dir(BASE_DIR.'galerien/'.$datei)) continue;
                if(!isFileRW(BASE_DIR.'galerien/'.$datei)) {
                    $update_staus['galerien'] = false;
                    break;
                }
                foreach(scandir(BASE_DIR.'galerien/'.$datei) as $img) {
                    if($img[0] == ".") continue;
                    if(!isFileRW(BASE_DIR.'galerien/'.$datei.'/'.$img)) {
                        $update_staus['galerien'] = false;
                        break;
                    }
                    if($img == 'texte.conf') {
#                        $datei_array['galerien'][$datei]['conf'] = $img;
                        continue;
                    }
                    if($img != cleanUploadFile($img)) {
                    $update_staus['galerien'] = true;
if(!$update)
    break;
#                        $datei_array['galerien'][$datei][] = $img;
                        if($update) {
                            $clean = cleanUploadFile($img);
                            $status = updateRename("galerien/".$datei."/".$img,"galerien/".$datei."/".$clean);
                            if($status) {
                                $clean = $status;
                            }
                            $update_page_files[$img] = $clean;
                            $update_text[$img] = $clean;
                            if(is_file(BASE_DIR."galerien/".$datei."/vorschau/".$img))
                                updateRename("galerien/".$datei."/vorschau/".$img,"galerien/".$datei."/vorschau/".$clean);
                        }
                    }
                }
if(!$update and $update_staus['galerien'] === true)
    break;
                if($update) {
                    if(is_file(BASE_DIR.'galerien/'.$datei.'/texte.conf'))
                        updateConf(BASE_DIR.'galerien/'.$datei.'/texte.conf');
                    if(count($update_text) > 0)
                        renameGalleryTextConf($update_text,$datei);
                    unset($update_text);
                }
            }
        } else
            $update_staus['galerien'] = false;
    }
    $update_staus['kategorien'] = "empty";
    if(is_dir(BASE_DIR.'kategorien')) {
        if(isFileRW(BASE_DIR.'kategorien')) {
            foreach(scandir(BASE_DIR.'kategorien') as $cat) {
                $rename_cat = false;
                if($cat[0] == ".") continue;
                if(strlen($cat) > 3 and ctype_digit(substr($cat,0,2)) and $cat[2] == "_") {
                    $rename_cat = true;
if(!$update) {
#echo "cat=".$cat."<br />\n";
    $update_staus['kategorien'] = true;
    break;
}
                }
                if(is_dir(BASE_DIR.'kategorien/'.$cat)) {
                    if(!isFileRW(BASE_DIR.'kategorien/'.$cat) or !$update_staus['kategorien']) {
                        $update_staus['kategorien'] = false;
                        break;
                    }
#                    $update_staus['kategorien'] = true;
                    if(substr($cat,-4) == ".lnk") {
                        $datei_array['kategorien'][$cat] = null;
                        if($rename_cat and $update) {
                            updateRename("kategorien/".$cat,"kategorien/".substr($cat,3).".php");
                        }
                        continue;
                    }
                    $datei_array['kategorien'][$cat] = array();
                    foreach(scandir(BASE_DIR.'kategorien/'.$cat) as $page) {
                        $rename_page = false;
                        if(strlen($page) > 3 and ctype_digit(substr($page,0,2)) and $page[2] == "_") {
                            $rename_page = true;
if(!$update) {
#echo "page=".$page."<br />\n";
    $update_staus['kategorien'] = true;
    break;
}
                        }
                        if($page[0] == ".") continue;
                        if(!isFileRW(BASE_DIR.'kategorien/'.$cat.'/'.$page)) {
                            $update_staus['kategorien'] = false;
                            break;
                        }
                        if($page == "dateien" and is_readable(BASE_DIR.'kategorien/'.$cat.'/dateien')) {
                            foreach(scandir(BASE_DIR.'kategorien/'.$cat.'/dateien') as $file) {
                                if($file[0] == ".") continue;
                                if(!isFileRW(BASE_DIR.'kategorien/'.$cat.'/dateien/'.$file)) {
                                    $update_staus['kategorien'] = false;
                                    break;
                                }
                                if($file != cleanUploadFile($file)) {
if(!$update) {
#echo "file=".$file."<br />\n";
    $update_staus['kategorien'] = true;
    break;
}
                                    $datei_array['kategorien'][$cat]['dateien'][] = $file;
                                    if($update) {
                                        $clean = cleanUploadFile($file);
                                        $status = updateRename("kategorien/".$cat."/dateien/".$file,"kategorien/".$cat."/dateien/".$clean);
                                        if($status)
                                            $clean = $status;
                                $update_page_files[$file] = $clean;
                                    }
                                }
                            }
                        } elseif(substr($page,-4) != ".php") {
if(!$update) {
#echo "page=".$page."<br />\n";
    $update_staus['kategorien'] = true;
    break;
}
                            $datei_array['kategorien'][$cat]['page'][] = $page;
                            if($rename_page and $update) {
                                updateRename("kategorien/".$cat."/".$page,"kategorien/".$cat."/".substr($page,3).".php");
                            }
                        }
                    }
                    if($update and count($datei_array['kategorien'][$cat]) == 0)
                        unset($datei_array['kategorien'][$cat]);
                }
                if($rename_cat and $update) {
                    updateRename("kategorien/".$cat,"kategorien/".substr($cat,3));
                }
            }
            if(isset($datei_array['kategorien']) and count($datei_array['kategorien']) == 0) {
                $update_staus['kategorien'] = "empty";
                unset($datei_array['kategorien']);
            }
        } else
            $update_staus['kategorien'] = false;
    }
    if(isset($datei_array['kategorien']) and count($datei_array['kategorien']) > 0) {
        updatePages($datei_array['kategorien'],$update_page_files);
    }
    $update_staus['plugins'] = "empty";
    if(is_dir(BASE_DIR.'plugins')) {
        if(isFileRW(BASE_DIR.'plugins')) {
#echo "conf=".'plugins/'."<br />\n";
            foreach(scandir(BASE_DIR.'plugins') as $datei) {
                if($datei[0] == ".") continue;
                if(is_file(BASE_DIR.'plugins/'.$datei.'/plugin.conf')) {
                    if(!isFileRW(BASE_DIR.'plugins/'.$datei.'/plugin.conf')) {
                        $update_staus['plugins'] = false;
                        break;
                    }
                    $update_staus['plugins'] = true;
if(!$update)
    break;
                    if($update) {
#echo "conf=".'plugins/'.$datei.'/plugin.conf'."<br />\n";
                        updateConf(BASE_DIR.'plugins/'.$datei.'/plugin.conf');
                    }
                }
            }
        } else
            $update_staus['plugins'] = false;
    }
    $update_staus['layouts'] = "empty";
    if(is_dir(BASE_DIR.'layouts')) {
        if(isFileRW(BASE_DIR.'layouts')) {
            foreach(scandir(BASE_DIR.'layouts') as $datei) {
                if($datei[0] == ".") continue;
                if(is_file(BASE_DIR.'layouts/'.$datei.'/layoutsettings.conf')
                        and is_file(BASE_DIR.'layouts/'.$datei.'/template.html')) {
                    if(!isFileRW(BASE_DIR.'layouts/'.$datei.'/layoutsettings.conf')
                            and !isFileRW(BASE_DIR.'layouts/'.$datei.'/template.html')) {
                        $update_staus['layouts'] = false;
                        break;
                    }
                    $update_staus['layouts'] = true;
if(!$update)
    break;
                    if($update) {
#echo "conf=".'plugins/'.$datei.'/plugin.conf'."<br />\n";
                        updateTemplate(BASE_DIR.'layouts/'.$datei.'/');
                    }
                }
            }
        } else
            $update_staus['layouts'] = false;
    }
    if(!$update) {
#        $update_test = false;
        foreach($update_staus as $name => $test) {
#echo $name." = ".$test."<br />\n";
            if($test === true and $test !== false and $test !== 'empty') {
#echo "return true<br />\n";
                return true;
            }
        }
        return false;
    }
    return $update_staus;
}

function updateRename($old,$new) {
    $return = false;
    if(is_file(BASE_DIR.$new)) {
        $nr = 0;
        $exist = true;
        while(!$exist) {
            $nr++;
            $exist = is_file(BASE_DIR.$nr."_".$new);
        }
        $new = BASE_DIR.$nr."_".$new;
        $return = $nr."_".$new;
    }
    rename(BASE_DIR.$old,BASE_DIR.$new);
    return $return;
}

function cleanUploadFile($file) {
    $file = rawurldecode($file);
    $search = array("ä","ö","ü","Ä","Ö","Ü","ß"," ");
    $replace = array("ae","oe","ue","Ae","Oe","Ue","ss","_");
    # Also remove control characters and spaces (\x00..\x20) around the filename:
    $file = trim($file, ".\x00..\x20");
    $file = str_replace($search,$replace,$file);
    $file = preg_replace('/[^a-zA-Z0-9._-]/', "",$file);
    return $file;
}

function updateCMSConf($file) {
    $new = array();
    $old = file('update/'.$file);
    $tmp_array = array();
    $tmp_conf = null;
    $tmp_file = str_replace(array(".conf",".conf.php"),"",$file);
    foreach($old as $zeile) {
        if(preg_match("/^#/",$zeile) || preg_match("/^\s*$/",$zeile)) continue;
        if(preg_match("/^([^=]*)=(.*)/",$zeile,$matches))
            $new[trim($matches[1])] = trim($matches[2]);
    }
    if($tmp_file == "version" or $tmp_file == "loginpass") {
        return;
    } elseif($tmp_file == "syntax") {
        $tmp_conf = new Properties(BASE_DIR.CMS_DIR_NAME.'/'.CONF_DIR_NAME."/".$tmp_file.".conf.php");
        $tmp_array = array();
        setFromOldToNew($tmp_conf,$tmp_array,$new,true);
    } elseif(is_file(BASE_DIR.ADMIN_DIR_NAME.'/'.CONF_DIR_NAME."/".$tmp_file.".conf.php")) {
        $tmp_conf = new Properties(BASE_DIR.ADMIN_DIR_NAME.'/'.CONF_DIR_NAME."/".$tmp_file.".conf.php");
        $tmp_array = $tmp_conf->toArray();
        setFromOldToNew($tmp_conf,$tmp_array,$new);
    } elseif(is_file(BASE_DIR.CMS_DIR_NAME.'/'.CONF_DIR_NAME."/".$tmp_file.".conf.php")) {
        $tmp_conf = new Properties(BASE_DIR.CMS_DIR_NAME.'/'.CONF_DIR_NAME."/".$tmp_file.".conf.php");
        $tmp_array = $tmp_conf->toArray();
        setFromOldToNew($tmp_conf,$tmp_array,$new);
    }
    unset($tmp_conf);
}

function setFromOldToNew($tmp_conf,$tmp_array,$new,$syntax = false) {
    foreach($new as $key => $value) {
        if($key == "language" or $key == "chmodnewfilesatts" or $key == "cmslanguage" or $key == "modrewrite") continue;
        if(!$syntax and array_key_exists($key,$tmp_array)) {
            if($key == "defaultcat" and strlen($value) > 3)
                $value = substr($value,3);
            $tmp_conf->set($key,$value);
        }
        if($syntax) {
            $tmp_conf->set($key,$value);
        }
    }
}

function updateConf($file) {
    global $page_protect;
    $new = array();
    $old = file($file);
    foreach($old as $zeile) {
        if(preg_match("/^#/",$zeile) || preg_match("/^\s*$/",$zeile)) continue;
        if(preg_match("/^([^=]*)=(.*)/",$zeile,$matches)) {
            $new[trim($matches[1])] = trim($matches[2]);
        }
    }
    $new = $page_protect.serialize($new);
    if(false === (file_put_contents($file,$new,LOCK_EX))) {
        echo "kann datei nicht schreiben ".$file."\n";
        die();
    }
    rename($file,$file.".php");
}

function renameGalleryTextConf($update_text,$tmp_gallery) {
    if(!is_file(BASE_DIR.'galerien/'.$tmp_gallery."/texte.conf.php"))
        return;
    $tmp_conf = new Properties(BASE_DIR.'galerien/'.$tmp_gallery."/texte.conf.php");
    $tmp_array = $tmp_conf->toArray();
    foreach($tmp_array as $key => $value) {
        if(array_key_exists($key,$update_text)) {
#echo "txt=".$key." -> ".$update_text[$key]."<br />\n";
            $tmp_conf->set($update_text[$key],$value);
            $tmp_conf->delete($key);
        }
    }
    unset($tmp_conf,$tmp_array);
}

function updatePages($datei_array,$update_page_files) {
    global $page_protect;
    $page_replace = false;
    if(isset($_POST['update_pages']) and count($update_page_files) > 0) {
        $page_replace = true;
        $search = array_keys($update_page_files);
        $replace = array_values($update_page_files);
    }
#    if(isset($_POST['update_pages']) and $_POST['update_pages'])
    $sort_array = array();
    foreach($datei_array as $cat => $tmp) {
        $new_cat = substr($cat,3);
        $sort_array[$new_cat] = "null";
        if(isset($datei_array[$cat]['page'])) {
            $sort_array[$new_cat] = array();
            foreach($datei_array[$cat]['page'] as $page) {
                $new_page = substr($page,3);
                $sort_array[$new_cat][$new_page] = "null";
                if(!is_file(BASE_DIR.'kategorien/'.$new_cat.'/'.$new_page)) continue;
                $content = file_get_contents(BASE_DIR.'kategorien/'.$new_cat.'/'.$new_page);
                if($page_replace)
                    str_replace($search,$replace,$content);
                file_put_contents(BASE_DIR.'kategorien/'.$new_cat.'/'.$new_page,$page_protect.$content,LOCK_EX);
            }
        }
    }
    $sort_array = var_export($sort_array,true);
    file_put_contents(BASE_DIR."cms/SortCatPage.php","<?php if(!defined('IS_CMS')) die();\n\$cat_page_sort_array = ".$sort_array.";\n?>");
}

function updateTemplate($dir) {
    $old = file($dir.'layoutsettings.conf');
    $setting = false;
    foreach($old as $zeile) {
        if(preg_match("/^#/",$zeile) || preg_match("/^\s*$/",$zeile)) continue;
        if(preg_match("/^([^=]*)=(.*)/",$zeile,$matches)) {
            if(trim($matches[1]) == "usesubmenu") {
                $setting = '<!-- usesubmenu = '.trim($matches[2]).' -->'."\n";
                break;
            }
        }
    }
    if($setting !== false) {
        $content = file_get_contents($dir.'template.html');
        if(!strstr($content,'<!-- usesubmenu = '))
            file_put_contents($dir.'template.html',$setting.$content);
        unlink($dir.'layoutsettings.conf');
    }
}
?>