<?php if(!defined('IS_ADMIN') or !IS_ADMIN) die();

$debug = "";

function template() {
    global $CMS_CONF;
    global $specialchars;
    global $message;

global $debug;
    $template_manage_open = false;
    # templates löschen
    if(getRequestValue('template-all-del','post') and getRequestValue('template-del','post')) {
        template_del();
        $template_manage_open = true;
    }
    # template activ setzen
    if(!getRequestValue('template-all-del','post') and !getRequestValue('template-install','post') and getRequestValue('template-active','post')) {
$debug .= "active=".getRequestValue('template-active','post')."<br />\n";
        template_setactiv();
    }
    # hochgeladenes template installieren
    if(isset($_FILES["template-install-file"]["error"])
            and getRequestValue('template-install','post')
            and $_FILES["template-install-file"]["error"] == 0
            and strtolower(substr($_FILES["template-install-file"]["name"],-4)) == ".zip") {
$debug .= "install=".$_FILES["template-install-file"]["name"]."<br />\n";
        template_install();
        $template_manage_open = true;
    }
    # per FTP hochgeladenes template installieren
    elseif(($template_select = $specialchars->rebuildSpecialChars(getRequestValue('template-install-select','post'),false,false))
            and getRequestValue('template-install','post')
            and is_file(BASE_DIR.LAYOUT_DIR_NAME."/".$template_select) !== false
            and strtolower(substr($template_select,-4)) == ".zip") {
$debug .= "local install=".getRequestValue('template-install-select','post')."<br />\n";
        template_install($template_select);
        $template_manage_open = true;
    }

$showdebug = false;
if($showdebug and !empty($debug))
    $message .= returnMessage(false,$debug);

    $ACTIV_TEMPLATE = $CMS_CONF->get("cmslayout");
    $LAYOUT_DIR     = LAYOUT_DIR_NAME."/".$ACTIV_TEMPLATE.'/';

    if(getRequestValue('chancefiles') == "true") {
        require_once(BASE_DIR_ADMIN."jquery/File-Upload/upload.class.php");
    }

    if(false !== ($newfile = getRequestValue('newfile','post'))
            and false !== ($orgfile = getRequestValue('orgfile','post'))
            and false !== ($curent_dir = getRequestValue('curent_dir','post'))) {
        $dir = BASE_DIR.LAYOUT_DIR_NAME."/".str_replace('%2F','/',$curent_dir)."/";
        if(true !== ($error = moveFileDir($dir.$orgfile,$dir.$newfile,true))) {
            ajax_return("error",true,$error,true,"js-dialog-reload");
        }
        ajax_return("success",true);
    }

    if(getRequestValue('templateselectbox','post') == "true") {
        require_once(BASE_DIR_ADMIN.'editsite.php');
        # wir schiken die neue selectbox zurück
        echo '<span id="replace-item">'.returnTemplateSelectbox().'</span>';
        ajax_return("success",true);
    }

    if(getRequestValue('configtemplate','post') == "true") {
        if(false !== ($templatefile = BASE_DIR.getRequestValue('templatefile','post',false))
                and !file_exists($templatefile)) {
            ajax_return("error",true,returnMessage(false,getLanguageValue("error_no_file_dir")." ".$templatefile),true,true);
        }
        if(false !== ($content = getRequestValue('content','post',false))) {
            if(false === (mo_file_put_contents($templatefile,$content))) {
                ajax_return("error",true,returnMessage(false,getLanguageValue("editor_content_error_save")),true,true);
            }
            echo ajax_return("success",false);
        } else {
            if(false === ($syntax = get_contents_ace_edit($templatefile))) {
                ajax_return("error",true,returnMessage(false,getLanguageValue("editor_content_error_open")),true,true);
            }
            echo '<textarea id="page-content">'.$syntax.'</textarea>';
            echo ajax_return("success",false);
        }
        exit();
    }

    global $ADMIN_CONF;
    $show = $ADMIN_CONF->get("template");
    if(!is_array($show))
        $show = array();

    $html_manage = "";
    if(ROOT or in_array("template_manage",$show)) {
        $template_manage = array();
        $disabled = '';
        if(!function_exists('gzopen'))
            $disabled = ' disabled="disabled"';

        $template_install = array();
        foreach(getDirAsArray(BASE_DIR.LAYOUT_DIR_NAME,array(".zip")) as $zip_file) {
            $template_install[] = '<option value="'.mo_rawurlencode($zip_file).'">'.$zip_file.'</option>';
        }

        $template_install_html = "";
        if(count($template_install) > 0) {
            $template_install_html .= '<br /><select class="mo-install-select mo-select-div" name="template-install-select" size="1"'.$disabled.'>'
                    .'<option value="">'.getLanguageValue("template_select",true).'</option>'
                    .implode("",$template_install)
                .'</select>';
        }

        $template_manage["template_title_manage"][] = '<div class="mo-nowrap align-right ui-helper-clearfix">'
                .'<span class="align-left" style="float:left"><span class="mo-bold">'.getLanguageValue("template_text_filebutton").'</span><br />'.getLanguageValue("template_text_fileinfo").'</span>'
                .'<input type="file" id="js-template-install-file" name="template-install-file" class="mo-select-div"'.$disabled.' />'
                .$template_install_html
                .'<input type="submit" id="js-template-install-submit" name="template-install" value="'.getLanguageValue("template_button_install",true).'"'.$disabled.' /><br />'
                .'<input type="submit" id="js-template-del-submit" value="'.getLanguageValue("template_button_delete",true).'" class="mo-margin-top" />'
            .'</div>';

        foreach(getDirAsArray(BASE_DIR.LAYOUT_DIR_NAME,"dir","natcasesort") as $pos => $file) {
            $template_activ = '';
            $checkbox_del = '<input type="checkbox" name="template-del[]" value="'.$file.'" class="mo-checkbox" />';
            $radio_activ = '<input id="template-status'.$pos.'" name="template-active" type="radio" value="'.$file.'" class="mo-radio" /><label for="template-status'.$pos.'">'.getLanguageValue("template_input_set_active").'</label>';
            if($ACTIV_TEMPLATE == $file) {
                $checkbox_del = '&nbsp;';
                $radio_activ = "";
                $template_activ = ' mo-bold';
            }
            $template_manage["template_title_manage"][] = '<div class="mo-middle mo-tag-height-from-icon ui-helper-clearfix">'
                .'<span class="mo-nowrap  mo-padding-left'.$template_activ.'">'.$specialchars->rebuildSpecialChars($file,false,true).'</span>'
                .'<div style="float:right;">'.$checkbox_del.'</div>'
                .'<div style="float:right;width:30%;">'.$radio_activ.'</div>'
            .'</div>';
        }

        $multi_user = "";
        if(defined('MULTI_USER') and MULTI_USER)
            $multi_user = "&amp;multi=true";
        if(count($template_manage["template_title_manage"]) > 0) {
            $template_manage["template_title_manage"]["toggle"] = true;
            $html_manage = '<form id="js-template-manage" action="index.php?nojs=true&amp;action=template'.$multi_user.'" method="post" enctype="multipart/form-data">'.contend_template($template_manage).'</form>';
            # es wurde in der template verwaltung was gemacht dann soll die aufgeklapt bleiben
            if($template_manage_open)
                $html_manage = str_replace("display:none;","",$html_manage);
        }
    }

    $html_template = "";
    if(ROOT or in_array("template_edit",$show)) {
        $template = array();
        foreach(getDirAsArray(BASE_DIR.$LAYOUT_DIR,array(".html"),"natcasesort") as $file) {
            $template["template_title_html_css"][] = '<div class="js-tools-show-hide mo-middle mo-tag-height-from-icon ui-helper-clearfix">'
                .'<span class="js-filename mo-nowrap mo-padding-left">'.$file.'</span>'
                .'<img style="float:right;" class="js-tools-icon-show-hide js-edit-template js-html mo-tool-icon mo-icons-icon mo-icons-page-edit" src="'.ICON_URL_SLICE.'" alt="page-edit" hspace="0" vspace="0" />'
                .'<span class="js-edit-file-pfad" style="display:none;">'.$specialchars->replaceSpecialChars($LAYOUT_DIR.$file,true).'</span>'
            .'</div>';
        }

        foreach(getDirAsArray(BASE_DIR.$LAYOUT_DIR.'css',array(".css"),"natcasesort") as $file) {
            $template["template_title_html_css"][] = '<div class="js-tools-show-hide mo-middle mo-tag-height-from-icon ui-helper-clearfix">'
                .'<span class="js-filename mo-nowrap mo-padding-left"><span class="mo-bold mo-padding-right">css/</span>'.$file.'</span>'
                .'<img style="float:right;" class="js-tools-icon-show-hide js-edit-template js-css mo-tool-icon mo-icons-icon mo-icons-page-edit" src="'.ICON_URL_SLICE.'" alt="page-edit" hspace="0" vspace="0" />'
                .'<span class="js-edit-file-pfad" style="display:none;">'.$specialchars->replaceSpecialChars($LAYOUT_DIR.'css/'.$file,true).'</span>'
            .'</div>';
        }

        require_once(BASE_DIR_ADMIN."jquery/File-Upload/fileupload.php");
        $template_img = getFileUpload($CMS_CONF->get("cmslayout").'/grafiken');

        $html_img = get_template_truss('<li class="mo-li ui-corner-all">'.$template_img.'</li>',"template_title_grafiken",true);

        $html_template = get_template_truss('<li class="ui-corner-all">'.contend_template($template).$html_img.'</li>',"template_title_template",false);
        $html_template = str_replace("{TemplateName}",'<span style="font-weight:normal;">'.$specialchars->rebuildSpecialChars($CMS_CONF->get("cmslayout"),false,true).'</span>',$html_template);
    }

    $html_plugins = "";
    if(ROOT or in_array("template_plugin_css",$show)) {

        $show = $ADMIN_CONF->get("plugins");
        if(!is_array($show))
            $show = array();
        global $activ_plugins;
        $template_plugins = array();
        $template_plugins["template_title_plugins"] = array();
        foreach($activ_plugins as $plugin) {
            if(!ROOT and !in_array($plugin,$show))
                continue;
            if(!is_file(BASE_DIR.PLUGIN_DIR_NAME."/".$plugin."/plugin.css")) continue;
            $template_plugins["template_title_plugins"][] = '<div class="js-tools-show-hide mo-middle mo-tag-height-from-icon ui-helper-clearfix">'
                .'<span class="js-filename mo-nowrap mo-padding-left"><span class="mo-bold mo-padding-right">css/</span>'.$plugin.'</span>'
                .'<img style="float:right;" class="js-tools-icon-show-hide js-edit-template js-css mo-tool-icon mo-icons-icon mo-icons-page-edit" src="'.ICON_URL_SLICE.'" alt="page-edit" hspace="0" vspace="0" />'
                .'<span class="js-edit-file-pfad" style="display:none;">'.$specialchars->replaceSpecialChars(PLUGIN_DIR_NAME."/".$plugin."/plugin.css",true).'</span>'
            .'</div>';

        }
        if(count($template_plugins["template_title_plugins"]) > 0) {
            $template_plugins["template_title_plugins"]["toggle"] = true;
            $html_plugins = contend_template($template_plugins);
        }
    }
    $html_editor = "";
    if(!empty($html_template) or !empty($html_plugins))
        $html_editor = pageedit_dialog();
    return $html_manage.$html_template.$html_plugins.$html_editor;
}

function template_setactiv() {
    global $specialchars;
    global $CMS_CONF;

    $dir = BASE_DIR.LAYOUT_DIR_NAME."/";
    $new_activ_template = $specialchars->replaceSpecialChars(getRequestValue('template-active','post'),false);
    if($CMS_CONF->get("cmslayout") != $new_activ_template and is_dir($dir.$new_activ_template))
        $CMS_CONF->set("cmslayout",$new_activ_template);
}

function template_install($zip = false) {
    if(!function_exists('gzopen'))
        return;

    @set_time_limit(600);

    global $message, $specialchars;
global $debug;
    $dir = BASE_DIR.LAYOUT_DIR_NAME."/";
#    $zip_file = $dir.$specialchars->replaceSpecialChars($_FILES["template-install-file"]["name"],false);

#    if(true === (move_uploaded_file($_FILES["template-install-file"]["tmp_name"], $zip_file))) {
    if($zip === false)
        $zip_file = $dir.$specialchars->replaceSpecialChars($_FILES["template-install-file"]["name"],false);
    else {
        if(getChmod() !== false)
            setChmod($dir.$zip);
        $zip_file = $dir.$zip;
    }
$debug .= $zip_file."<br />";
#    if(true === (move_uploaded_file($_FILES["plugin-install-file"]["tmp_name"], $zip_file))) {
    if(($zip !== false
                and strlen($zip_file) > strlen($dir))
            or ($zip === false
                and true === (move_uploaded_file($_FILES["template-install-file"]["tmp_name"], $zip_file)))) {

        require_once(BASE_DIR_ADMIN."pclzip.lib.php");
        $archive = new PclZip($zip_file);

        if(0 != ($file_list = $archive->listContent())) {

            uasort($file_list,"helpUasort");

            $find = installFindTemplates($file_list,$archive,$zip_file);

            if(count($find) > 0) {
                foreach($find as $liste) {
                    if(strlen($liste['index']) > 0) {
$debug .= '<pre>';
$debug .= var_export($liste,true);
$debug .= '</pre>';
                        if(getChmod() !== false) {
                            $tmp1 = $archive->extractByIndex($liste['index']
                                    ,PCLZIP_OPT_PATH, $dir
                                    ,PCLZIP_OPT_ADD_PATH, $liste['name']
                                    ,PCLZIP_OPT_REMOVE_PATH, $liste['remove_dir']
                                    ,PCLZIP_OPT_SET_CHMOD, getChmod()
                                    ,PCLZIP_CB_PRE_EXTRACT, "PclZip_PreExtractCallBack"
                                    ,PCLZIP_OPT_REPLACE_NEWER);
                            setChmod($dir.$liste['name']);
                        } else {
                            $tmp1 = $archive->extractByIndex($liste['index']
                                    ,PCLZIP_OPT_PATH, $dir
                                    ,PCLZIP_OPT_ADD_PATH, $liste['name']
                                    ,PCLZIP_OPT_REMOVE_PATH, $liste['remove_dir']
                                    ,PCLZIP_CB_PRE_EXTRACT, "PclZip_PreExtractCallBack"
                                    ,PCLZIP_OPT_REPLACE_NEWER);
                        }
                    } else {
                        # die file strucktur im zip stimt nicht
                        $message .= returnMessage(false,getLanguageValue("error_zip_structure"));
                    }
                }
            } else {
                # die file strucktur im zip stimt nicht
                $message .= returnMessage(false,getLanguageValue("error_zip_structure"));
            }
        } else {
            # scheint kein gühltiges zip zu sein
            $message .= returnMessage(false,getLanguageValue("error_zip_nozip"));
        }
        unlink($zip_file);
    } else {
        # das zip konnte nicht hochgeladen werden
        $message .= returnMessage(false,getLanguageValue("error_file_upload"));
    }
}

function helpUasort($a,$b) {
    if($a['stored_filename'] == $b['stored_filename'])
        return 0;
    return (strlen($a['stored_filename']) < strlen($b['stored_filename'])) ? -1 : 1;
}

function template_del() {
    global $specialchars;
    global $message;
global $debug;

    $template_del = getRequestValue('template-del','post');
    if(is_array($template_del)) {
        foreach($template_del as $template) {
$debug .= "del=".$template."<br />\n";
            if(true !== ($error = deleteDir(BASE_DIR.LAYOUT_DIR_NAME."/".$specialchars->replaceSpecialChars($template,false))))
                $message .= $error;
        }
    } else {
        $message .= returnMessage(false,getLanguageValue("error_post_parameter"));
    }
}

function installFindTemplates($file_list,$archive,$zip_file,$no_subfolder = false) {
    global $specialchars;
    $find = array();
    $count_file_list = count($file_list);
    foreach($file_list as $pos => $tmp) {
        # fehler im zip keine ../ im pfad erlaubt
        if(false !== strpos($tmp["stored_filename"],"../"))
            continue;
        if(basename($tmp["stored_filename"]) == "template.html") {
            $name = dirname($tmp["stored_filename"]) == "." ? "" : basename(dirname($tmp["stored_filename"]));
            if(strlen($name) > 0 and $name[0] == ".")
                continue;
            if(strlen($name) < 1)
                $name = $specialchars->replaceSpecialChars(substr($zip_file,0,-4),false);
            if(strlen($name) < 1)
                continue;
            if(!isset($find[$name])) {
                $remove_dir = dirname($tmp["stored_filename"]);
                if($remove_dir and $remove_dir == ".")
                    $remove_dir = "";
                $index = array();
                foreach($file_list as $key => $tmp1) {
                    $test_dir = substr($tmp1["stored_filename"],0,(strlen($remove_dir)+1));
                    if($no_subfolder and strlen($test_dir) === 1 and $test_dir[0] !== "/")
                        $test_dir = "/";
                    if($test_dir == $remove_dir."/") {
                        $index[] = $tmp1["index"];
                        unset($file_list[$key]);
                    }
                }
                if(count($index) > 0) {
                    $find[$name]['name'] = $name;
                    $find[$name]['remove_dir'] = $remove_dir;
                    sort($index,SORT_NUMERIC);
                    $find[$name]['index'] = implode(",",$index);
                }
            }
        }
    }
    if(!$no_subfolder and $count_file_list == count($file_list)) {
        $find = installFindTemplates($file_list,$archive,$zip_file,true);
    }
    return $find;
}

function PclZip_PreExtractCallBack($p_event, &$p_header) {
    if(!$p_header['folder'] and !isValidDirOrFile(basename($p_header['filename'])))
        return 0;
    return 1;
}

?>