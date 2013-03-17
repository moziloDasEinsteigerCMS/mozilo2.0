<?php if(!defined('IS_ADMIN') or !IS_ADMIN) die();
function gallery() {
    global $specialchars;
    global $ADMIN_CONF;
    global $CatPage;
    global $GALLERY_CONF;

    if(false !== ($changeart = getRequestValue('changeart','post'))) {
        header('content-type: text/html; charset='.CHARSET.'');
        if($changeart == "gallery_new") {
            if(false !== ($galleryname = getRequestValue('galleryname','post'))) {
                echo newGallery($galleryname);
            }
            exit();
        } elseif($changeart == "gallery_del") {
            if(false !== ($galleryname = getRequestValue('galleryname','post'))) {
                echo deleteGallery($galleryname);
            }
            exit();
        } elseif($changeart == "gallery_rename") {
            if(false !== ($galleryname = getRequestValue('galleryname','post'))
                    and false !== ($gallerynewname = getRequestValue('gallerynewname','post'))) {
                echo renameGallery($galleryname,$gallerynewname);
            }
            exit();
        } elseif($changeart == "gallery_size") {
            if(false !== ($new_global_width = getRequestValue('new_global_width','post'))
                    and false !== ($new_global_height = getRequestValue('new_global_height','post'))
                    and (ctype_digit($new_global_width) or ctype_digit($new_global_height) or $new_global_width == "auto" or $new_global_height == "auto" or $new_global_width == "" or $new_global_height == "")) {
                $GALLERY_CONF->set("maxwidth",$new_global_width);
                $GALLERY_CONF->set("maxheight",$new_global_height);
                ajax_return("success",true);
            } elseif(false !== ($thumbnail_global_max_width = getRequestValue('thumbnail_global_max_width','post'))
                    and false !== ($thumbnail_global_max_height = getRequestValue('thumbnail_global_max_height','post'))
                    and (ctype_digit($thumbnail_global_max_width) or ctype_digit($thumbnail_global_max_height or $new_global_width == "" or $new_global_height == ""))) {
                $GALLERY_CONF->set("maxthumbwidth",$thumbnail_global_max_width);
                $GALLERY_CONF->set("maxthumbheight",$thumbnail_global_max_height);
                ajax_return("success",true);
            } else
                ajax_return("error",true,returnMessage(false,getLanguageValue("properties_error_save")),true,true);
        } elseif($changeart == "gallery_subtitle") {
            if(false !== ($subtitle = getRequestValue('subtitle','post',false))
                    and false !== ($curent_dir = getRequestValue('curent_dir','post'))
                    and false !== ($file = getRequestValue('file','post'))) {
                if(!is_file(GALLERIES_DIR_REL.$curent_dir."/texte.conf.php")
                        and false === (newConf(GALLERIES_DIR_REL.$curent_dir."/texte.conf.php"))) {
                    ajax_return("error",true,returnMessage(false,getLanguageValue("gallery_error_subtitle_conf")),true,true);
                }
                $tmp = new Properties(GALLERIES_DIR_REL.$curent_dir."/texte.conf.php");
                $tmp->set($file,$subtitle);
                ajax_return("success",true);
            }
            exit();
        } elseif($changeart == "file_rename") {
            if(false !== ($newfile = getRequestValue('newfile','post'))
                    and false !== ($orgfile = getRequestValue('orgfile','post'))
                    and false !== ($curent_dir = getRequestValue('curent_dir','post'))) {
                $dir = GALLERIES_DIR_REL.$curent_dir."/";
                if(true !== ($error = moveFileDir($dir.$orgfile,$dir.$newfile,true))) {
                    ajax_return("error",true,$error,true,"js-dialog-reload");
                }
                $dir = GALLERIES_DIR_REL.$curent_dir."/".PREVIEW_DIR_NAME."/";
                if(true !== ($error = moveFileDir($dir.$orgfile,$dir.$newfile,true))) {
                    ajax_return("error",true,$error,true,"js-dialog-reload");
                }
                $tmp = new Properties(GALLERIES_DIR_REL.$curent_dir."/texte.conf.php");
                $tmp->set($newfile,$tmp->get($orgfile));
                $tmp->delete($orgfile);
                ajax_return("success",true);
            }
            exit();
        } elseif($changeart == "gallery_ftp") {
            changeFromFtp();
        } else
            exit();
    }

    if(getRequestValue('chancefiles') == "true") {
        require_once(BASE_DIR_ADMIN."jquery/File-Upload/upload.class.php");
        exit();
    }

    $dircontent = getDirAsArray(GALLERIES_DIR_REL,"dir","sort");

    $pagecontent = "";

    require_once(BASE_DIR_ADMIN."jquery/File-Upload/fileupload.php");

    $pagecontent .= '<ul class="js-gallery mo-ul">';
    foreach ($dircontent as $pos => $currentgalerien) {
        $pagecontent .= '<li class="js-file-dir mo-li ui-widget-content ui-corner-all">';
        $pagecontent .= getFileUpload($currentgalerien,  $specialchars->rebuildSpecialChars($currentgalerien, false, true),getLanguageValue("images"));
        $pagecontent .= '</li>';
    }
    $pagecontent .= '</ul>';

    $new_gallery = '<ul class="js-new-gallery mo-ul new-gallery">';
    $new_gallery .= '<li class="js-file-dir mo-li ui-widget-content ui-corner-all">';
    $new_gallery .= getFileUpload($specialchars->rebuildSpecialChars(getLanguageValue("gallery_name_new"),false,true), getLanguageValue("gallery_name_new"),getLanguageValue("images")," mo-hidden");
    $new_gallery .= '</li>';
    $new_gallery .= '</ul>';

    $max_img = '<input type="text" name="new_global_width" value="'.$GALLERY_CONF->get('maxwidth').'" size="4" maxlength="4" class="mo-input-digit js-in-digit-auto" /> x <input type="text" name="new_global_height" value="'.$GALLERY_CONF->get('maxheight').'" size="4" maxlength="4" class="mo-input-digit js-in-digit-auto" /> '.getLanguageValue("pixels");

    $max_prev_img = '<input type="text" name="thumbnail_global_max_width" value="'.$GALLERY_CONF->get('maxthumbwidth').'" size="4" maxlength="4" class="mo-input-digit js-in-digit" /> x <input type="text" name="thumbnail_global_max_height" value="'.$GALLERY_CONF->get('maxthumbheight').'" size="4" maxlength="4" class="mo-input-digit js-in-digit" /> '.getLanguageValue("pixels");

    $titel = "gallery_help_conf";
    $template[$titel]["toggle"] = true;
    $template[$titel][] = array(getLanguageValue("gallery_scale"),$max_img);
    $template[$titel][] = array(getLanguageValue("gallery_scale_thumbs"),$max_prev_img);

    $ftp_form = '<form action="index.php?action='.ACTION.'" method="post">';
    $ftp_form .= '<input type="hidden" name="changeart" value="gallery_ftp" />';
    $ftp_form .= '<input type="submit" value="'.getLanguageValue("gallery_text_from_ftp_button").'" />';
    $ftp_form .= '</form>';

    $template[$titel][] = array(getLanguageValue("gallery_text_from_ftp"),$ftp_form);

    return array(contend_template($template).$pagecontent,$new_gallery);
}

function newGallery($galleryname) {
    if(isset($galleryname) and preg_match(ALLOWED_SPECIALCHARS_REGEX, $galleryname)) {
        if(true !== ($error = mkdirMulti(array(GALLERIES_DIR_REL.$galleryname,GALLERIES_DIR_REL.$galleryname."/".PREVIEW_DIR_NAME))))
            return ajax_return("error",false,$error,true,"js-dialog-reload");
        if(false === (newConf(GALLERIES_DIR_REL.$galleryname."/texte.conf.php")))
            return ajax_return("error",false,getLanguageValue("gallery_error_subtitle_conf"),true,true);
        return ajax_return("success",false);
    }
    return ajax_return("error",false,returnMessage(false,getLanguageValue("error_dir_file_name")),true,true);
}

function deleteGallery($galleryname) {
    if(true !== ($error = deleteDir(GALLERIES_DIR_REL.$galleryname)))
        return ajax_return("error",false,$error,true,"js-dialog-reload");
    return ajax_return("success",false);
}

function renameGallery($name,$newname) {
    if(true !== ($error = moveFileDir(GALLERIES_DIR_REL.$name,GALLERIES_DIR_REL.$newname)))
        return ajax_return("error",false,$error,true,"js-dialog-reload");
    return ajax_return("success",false);
}

function changeFromFtp() {
    global $message;
    global $specialchars;
    $success = false;
    $dirgallery = getDirAsArray(GALLERIES_DIR_REL,"dir");

    foreach ($dirgallery as $currentgalerien) {
        $change = false;
        if(true !== ($error = setChmod(GALLERIES_DIR_REL.$currentgalerien))) {
            $message .= returnMessage(false,$error);
            return;
        }
        $test_galerie = $specialchars->replaceSpecialChars($specialchars->rebuildSpecialChars($currentgalerien, false, false),false);
        if($test_galerie != $currentgalerien) {
            $nr = 0;
            $new_name = $test_galerie;
            while(in_array($new_name,$dirgallery)) {
                $new_name = "%23_".$nr."_".$test_galerie;
                $nr++;
            }
            if(true !== ($error = moveFileDir(GALLERIES_DIR_REL.$currentgalerien,GALLERIES_DIR_REL.$new_name))) {
                $message .= returnMessage(false,$error);
                return;
            }
            $change = true;
            $currentgalerien = $new_name;
        }
        if(!is_dir(GALLERIES_DIR_REL.$currentgalerien.'/'.PREVIEW_DIR_NAME)) {
            if(true !== ($error = mkdirMulti(GALLERIES_DIR_REL.$currentgalerien.'/'.PREVIEW_DIR_NAME))) {
                $message .= returnMessage(false,$error);
                return;
            }
            $change = true;
        }
        if(!file_exists(GALLERIES_DIR_REL.$currentgalerien."/texte.conf.php")) {
            if(false === (newConf(GALLERIES_DIR_REL.$currentgalerien."/texte.conf.php"))) {
                $message .= returnMessage(false,getLanguageValue("gallery_error_subtitle_conf"));
                return;
            }
            $change = true;
        }
        $dirimg = getDirAsArray(GALLERIES_DIR_REL.$currentgalerien,"img");
        foreach ($dirimg as $currentimg) {
            if(true !== ($error = setChmod(GALLERIES_DIR_REL.$currentgalerien."/".$currentimg))) {
                $message .= returnMessage(false,$error);
                return;
            }
            $test_img = cleanUploadFile($currentimg);
            if($test_img != $currentimg) {
                $nr = 0;
                $new_name = $test_img;
                while(in_array($new_name,$dirimg)) {
                    $new_name = "_".$nr."_".$test_img;
                    $nr++;
                }
                if(true !== ($error = moveFileDir(GALLERIES_DIR_REL.$currentgalerien."/".$currentimg,GALLERIES_DIR_REL.$currentgalerien."/".$new_name))) {
                    $message .= returnMessage(false,$error);
                    return;
                }
                $change = true;
                if(is_file(GALLERIES_DIR_REL.$currentgalerien.'/'.PREVIEW_DIR_NAME."/".$currentimg)) {
                    if(true !== ($error = moveFileDir(GALLERIES_DIR_REL.$currentgalerien.'/'.PREVIEW_DIR_NAME."/".$currentimg,GALLERIES_DIR_REL.$currentgalerien.'/'.PREVIEW_DIR_NAME."/".$new_name))) {
                        $message .= returnMessage(false,$error);
                        return;
                    }
                    $change = true;
                }
            }
        }
        if($change)
            $success .= "<b>".$specialchars->rebuildSpecialChars($currentgalerien,false,true)."</b><br />";
    }
    if($success)
        $message .= returnMessage(true,getLanguageValue("gallery_messages_from_ftp")."<br /><br />".$success);
    else
        $message .= returnMessage(true,getLanguageValue("gallery_messages_from_ftp_no")."");
}

?>