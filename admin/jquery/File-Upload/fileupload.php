<?php

function getFileUpload($curent_dir,$dir = false,$count_text = false,$newcss = "") {

    $head = "";
    if(ACTION != "template") {
        $head = '<div class="js-tools-show-hide mo-li-head-tag mo-li-head-tag-no-ul ui-state-active ui-corner-all">'
        .'<table width="100%" cellspacing="0" border="0" cellpadding="0" class="mo-tag-height-from-icon">'
            .'<tr>'
                .'<td width="99%" class="mo-nowrap">'
                    .'<span class="js-gallery-name mo-padding-left mo-bold">'.$dir.'</span>'
                .'</td>'
                .'<td class="mo-nowrap">'
                    .'<span class="mo-staus mo-font-small'.$newcss.'">( '
                        .'<span class="files-count">0</span> '
                        .$count_text.' )</span>'
                .'</td>'
                .'<td class="td_icons mo-nowrap">'
                    .'<img class="js-tools-icon-show-hide js-toggle mo-tool-icon'.$newcss.'" src="'.ADMIN_ICONS.'edit.png" alt="edit" />';
                    if(ACTION == "gallery") {
                        $head .= '<img class="js-tools-icon-show-hide js-rename-file mo-tool-icon mo-icon'.$newcss.'" src="'.ADMIN_ICONS.'work.png" alt="work" />'
                        .'<img class="js-tools-icon-show-hide js-edit-delete mo-tool-icon mo-icon'.$newcss.'" src="'.ADMIN_ICONS.'delete.png" alt="delete" hspace="0" vspace="0" />';
                    }
                $head .= '</td>'
            .'</tr>'
        .'</table>'
    .'</div>';
    }

    $css = "mo-ul";
    if(ACTION != "template")
        $css = "mo-in-ul-ul";
    $fileupload = '<ul class="js-toggle-content '.$css.' ui-corner-bottom">'
        .'<li class="ui-widget-content ui-corner-all">'
            #  hier die zusätzlichen para meter setzen
            .'<input type="hidden" name="curent_dir" value="'.$curent_dir.'" />'
#            .'<input type="hidden" name="prev_img" value="false" />'
            .'<input type="hidden" name="chancefiles" value="true" />'
            .'<input type="hidden" name="action" value="'.ACTION.'" />'
            # The fileupload-buttonbar contains buttons to add/delete files and start/cancel the upload
            .'<div class="fileupload-buttonbar mo-li-head-tag mo-li-head-tag-no-ul ui-widget-header ui-corner-top">'
#                .'<div class="mo-tag-height-from-icon mo-nowrap">'
                    .'<span class="fileinput-button">'
                        .'<img src="'.ADMIN_ICONS.'add-file.png" alt="add-file" />'
                        .'<input type="file" name="files[]" />'
                    .'</span>'
                    .'<button type="submit" class="fu-img-button start" style="background-image:url('.ADMIN_ICONS.'save.png);">&nbsp;</button>'
                    .'<button type="reset" class="fu-img-button cancel" style="background-image:url('.ADMIN_ICONS.'stop.png);">&nbsp;</button>'
                    .'<img style="width:2em;height:1px;" src="'.ADMIN_ICONS.'clear.gif" alt=" " />'
                    .'<button type="button" class="fu-img-button delete" style="background-image:url('.ADMIN_ICONS.'delete.png);">&nbsp;</button>';
                    if(ACTION == "gallery") {
                        $fileupload .= '<button type="button" class="fu-img-button resize" style="background-image:url('.ADMIN_ICONS.'img-scale.png);">&nbsp;</button>';
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
                        .'<img style="width:2em;height:1px;" src="'.ADMIN_ICONS.'clear.gif" alt=" " />'
                        .'Bildgröße <input type="text" name="new_width" value="'.$tmp_w.'" size="4" maxlength="4" class="mo-input-digit js-in-digit" /> x <input type="text" name="new_height" value="'.$tmp_h.'" size="4" maxlength="4" class="mo-input-digit js-in-digit" />'
                        .'<img style="width:2em;height:1px;" src="'.ADMIN_ICONS.'clear.gif" alt=" " />'
                        .'Vorschaugröße <input type="text" name="thumbnail_max_width" value="'.$GALLERY_CONF->get('maxthumbwidth').'" size="4" maxlength="4" class="mo-input-digit js-in-digit" /> x <input type="text" name="thumbnail_max_height" value="'.$GALLERY_CONF->get('maxthumbheight').'" size="4" maxlength="4" class="mo-input-digit js-in-digit" />'
                        .'';
                    }
                $fileupload .= '</div>'
#            .'</div>'
            .'<ul class="files"></ul>'
        .'</li>'
    .'</ul>';

    $form_start = '<form class="fileupload" action="index.php" method="post" enctype="multipart/form-data">';
    $form_end = '</form>';
    return $form_start.$head.$fileupload.$form_end;
}
?>