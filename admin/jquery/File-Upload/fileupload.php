<?php if(!defined('IS_ADMIN') or !IS_ADMIN) die();

function getFileUpload($curent_dir,$dir = false,$count_text = false,$newcss = "") {
    $head = "";
    if(ACTION != "template") {
        $count = "0";
        $gallery_tools = "";
        if(ACTION == "gallery") {
            $count = count(getDirAsArray(GALLERIES_DIR_REL.$curent_dir,"img"));
            $gallery_tools = '<img class="js-tools-icon-show-hide js-rename-file mo-tool-icon mo-icon'.$newcss.' mo-icons-icon mo-icons-work" src="'.ICON_URL_SLICE.'" alt="work" />'
                    .'<img class="js-tools-icon-show-hide js-edit-delete mo-tool-icon mo-icon'.$newcss.' mo-icons-icon mo-icons-delete" src="'.ICON_URL_SLICE.'" alt="delete" hspace="0" vspace="0" />';
        } elseif(ACTION == "files") {
            global $CatPage;
            $count = count($CatPage->get_FileArray($curent_dir));
        }
        $head = '<div class="js-tools-show-hide mo-li-head-tag mo-li-head-tag-no-ul ui-state-active ui-corner-all mo-middle">'
            .'<span class="js-gallery-name mo-padding-left mo-bold">'.$dir.'</span>'
            .'<div style="float:right;" class="mo-nowrap">'
                .'<span class="mo-staus mo-font-small'.$newcss.'">( '
                .'<span class="files-count">'.$count.'</span> '.$count_text.' )</span>'
                .'<img class="js-tools-icon-show-hide js-toggle mo-tool-icon'.$newcss.' mo-icons-icon mo-icons-edit" src="'.ICON_URL_SLICE.'" alt="edit" />'
                .$gallery_tools
            .'</div>'
            .'<br class="mo-clear" />'
        .'</div>';
    }

    $css = "mo-ul";
    if(ACTION != "template")
        $css = "mo-in-ul-ul";
    $fileupload = '<ul class="js-toggle-content '.$css.' ui-corner-bottom" style="display:none;">'
        .'<li class="ui-widget-content ui-corner-all">'
            #  hier die zusätzlichen para meter setzen
            .'<input type="hidden" name="curent_dir" value="'.$curent_dir.'" />'
            .'<input type="hidden" name="chancefiles" value="true" />'
            .'<input type="hidden" name="action" value="'.ACTION.'" />'
            # The fileupload-buttonbar contains buttons to add/delete files and start/cancel the upload
            .'<div class="fileupload-buttonbar mo-li-head-tag mo-li-head-tag-no-ul ui-widget-header ui-corner-top">'
                .'<span class="fileinput-button">'
                    .'<img class="mo-icons-icon mo-icons-add-file" src="'.ICON_URL_SLICE.'" alt="add-file" />'
                    .'<input type="file" name="files[]" />'
                .'</span>'
                .'<button type="submit" class="fu-img-button start mo-icons-icon mo-icons-save">&nbsp;</button>'
                .'<button type="reset" class="fu-img-button cancel mo-icons-icon mo-icons-stop">&nbsp;</button>'
                .'<img style="width:2em;height:1px;" src="'.ICON_URL_SLICE.'" alt=" " />'
                .'<button type="button" class="fu-img-button delete mo-icons-icon mo-icons-delete">&nbsp;</button>';
                if(ACTION == "gallery") {
                    $fileupload .= '<button type="button" class="fu-img-button resize mo-icons-icon mo-icons-img-scale">&nbsp;</button>';
                }
                $fileupload .= '<input type="checkbox" class="toggle" />';
                if(ACTION == "gallery") {
                    global $GALLERY_CONF;
                    $tmp_w = "";
                    $tmp_h = "";
                    if($GALLERY_CONF->get('maxwidth') != "auto" and $GALLERY_CONF->get('maxwidth') > 0)
                        $tmp_w = $GALLERY_CONF->get('maxwidth');
                    if($GALLERY_CONF->get('maxheight') != "auto" and $GALLERY_CONF->get('maxheight') > 0)
                        $tmp_h = $GALLERY_CONF->get('maxheight');
                    $fileupload .= ''
                    .'<img style="width:2em;height:1px;" src="'.ICON_URL_SLICE.'" alt=" " />'
                    .getLanguageValue("gallery_image_size").' <input type="text" name="new_width" value="'.$tmp_w.'" size="4" maxlength="4" class="mo-input-digit js-in-digit" /> x <input type="text" name="new_height" value="'.$tmp_h.'" size="4" maxlength="4" class="mo-input-digit js-in-digit" />'
                    .'<img style="width:2em;height:1px;" src="'.ICON_URL_SLICE.'" alt=" " />'
                    .getLanguageValue("gallery_preview_size").' <input type="text" name="thumbnail_max_width" value="'.$GALLERY_CONF->get('maxthumbwidth').'" size="4" maxlength="4" class="mo-input-digit js-in-digit" /> x <input type="text" name="thumbnail_max_height" value="'.$GALLERY_CONF->get('maxthumbheight').'" size="4" maxlength="4" class="mo-input-digit js-in-digit" />'
                    .'';
                }
                $fileupload .= '</div>'
            .'<ul class="files"></ul>'
        .'</li>'
    .'</ul>';

    $form_start = '<form class="fileupload" action="index.php" method="post" enctype="multipart/form-data">';
    $form_end = '</form>';
    return $form_start.$head.$fileupload.$form_end;
}
?>