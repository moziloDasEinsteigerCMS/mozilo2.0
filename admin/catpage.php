<?php if(!defined('IS_ADMIN') or !IS_ADMIN) die();
function catpage() {
    global $CatPage;

    if(getRequestValue('editpage','post',false)) {
        list($cat,$page) = $CatPage->split_CatPage_fromSyntax(getRequestValue('editpage','post',false));
        if($CatPage->exists_CatPage($cat, $page)) {
            if(false === getRequestValue('content','post',false)) {
                echo get_page($cat, $page);
            } elseif(getRequestValue('content','post',false)) {
                echo save_page($cat, $page);
            }
        } else {
            ajax_return("error",true,returnMessage(false,getLanguageValue("page_error_no_page")),true,"js-dialog-reload");
        }
        exit();
    } elseif(false !== ($changeart = getRequestValue('changeart','post'))
            and false !== ($cat_page_change = getRequestValue('cat_page_change','post'))) {
        if(false !== ($sort_array = getRequestValue('sort_array','post'))
                and !is_array($sort_array)) {
            ajax_return("error",true,returnMessage(false,getLanguageValue("error_post_parameter")),true,true);
        }
        if(function_exists($changeart)) {
            if(!is_array($cat_page_change))
                ajax_return("error",true,returnMessage(false,getLanguageValue("error_post_parameter")),true,true);
            else {
                $name = make_NewOrgCatPageFromRequest();
                
                // echo "Type = " . $name['type'] . "<br />";
                // echo "New = " . $name['new'] . "<br />";
                // echo "Org = " . $name['org'] . "<br />";
                
                $counter = 0;
                $xss_param_left = '%3C';    // <
                $xss_param_right = '%3E';   // > 
                
                $pos = strpos($name['new'], $xss_param_left);
                if ($pos !== false) {
                  $counter++;
                }
                $pos = strpos($name['new'], $xss_param_right);
                if ($pos !== false) {
                  $counter++;
                }                
                
                if ($counter > 0) {
                  $name = array();
                  $cleanup = getRequestValue('cat_page_change','post',true);
                  ajax_return("error",true,returnMessage(false,getLanguageValue("error_datei_file_name")),true,true);
                  exit();
                }              
                                              
                if(is_array($name)) {
                    $function = $changeart;
                    echo $function($name);
                } else
                    ajax_return("error",true,returnMessage(false,getLanguageValue("error_post_parameter")),true,true);
            }
        } else {
            ajax_return("error",true,returnMessage(false,getLanguageValue("function_exists_error")),true,true);
        }
        exit();
    } elseif(false !== ($sort_array = getRequestValue('sort_array','post'))) {
        if(is_array($sort_array)) {
            echo write_sort_list();
        } else
            ajax_return("error",true,returnMessage(false,getLanguageValue("error_post_parameter")),true,true);
        exit();
    }

    $page_lang = array("category" => "","page" => "","pages" => "","url" => "","target" => "","page_saveasnormal" => "","page_saveashidden" => "","page_saveasdraft" => "");
    # Variable erzeugen z.B. pages = $text_pages
    foreach($page_lang as $language => $tmp) {
        $page_lang[$language] = getLanguageValue($language);
    }
    $page_lang[EXT_PAGE] = $page_lang["page_saveasnormal"];
    $page_lang[EXT_HIDDEN] = $page_lang["page_saveashidden"];
    $page_lang[EXT_DRAFT] = $page_lang["page_saveasdraft"];

    return array(ul_cats($page_lang).pageedit_dialog(),new_cat_page($page_lang));
}

function new_cat_page($page_lang) {
    $cat_files = '<input type="hidden" value="false" class="js-cat-files" />';
    $new_catpage = '<ul class="js-new-ul">'
                        .'<li class="js-li-cat mo-li ui-widget-content ui-corner-all">';
                            $status = '<span class="js-status">0</span> '.$page_lang["pages"];
                            $new_catpage .= li_table($page_lang["category"],"[".$page_lang["category"]."]",$status,"cat",$cat_files)
                        .'</li>'
                        .'<li class="js-li-page mo-in-ul-li new-page ui-widget ui-state-default ui-corner-all">';
                            $status = '<span class="js-status">'.$page_lang[EXT_HIDDEN].'</span>';
                            $new_catpage .= li_table($page_lang["page"],"[".$page_lang["category"]."][".$page_lang["page"].EXT_HIDDEN."]",$status,EXT_HIDDEN,"")
                        .'</li>'
                        .'<li class="js-li-cat mo-li js-link ui-widget-content ui-corner-all">';
                            $status = $page_lang["url"].' '.$page_lang["target"].' <span class="js-status">blank</span>';
                            $new_catpage .= li_table($page_lang["url"]." ".$page_lang["category"] ,"[".$page_lang["url"]."%20".$page_lang["category"]."-_blank-".EXT_LINK."]",$status,"cat","",true)
                        .'</li>'
                        .'<li class="js-li-page mo-in-ul-li new-page js-link ui-widget ui-state-default ui-corner-all">';
                            $status = $page_lang["url"].' '.$page_lang["target"].' <span class="js-status">blank</span>';
                            $new_catpage .= li_table($page_lang["url"]." ".$page_lang["page"],"[".$page_lang["category"]."][".$page_lang["url"]."%20".$page_lang["page"]."-_blank-".EXT_LINK."]",$status,EXT_LINK,"",true)
                        .'</li>'
                    .'</ul>';
    return $new_catpage;
}

function ul_cats($page_lang) {
    global $CatPage;
    $ul_cats = '<ul class="js-ul-cats mo-ul">';
    foreach($CatPage->get_CatArray(true) as $cat) {
        $page_array = $CatPage->get_PageArray( $cat, array(EXT_PAGE,EXT_HIDDEN,EXT_DRAFT,EXT_LINK), true );
        $link_class = '';
        if($CatPage->get_Type($cat,false) == EXT_LINK) {
            $link_class = ' js-link';
        }
        $cat_files = '<input type="hidden" value="false" class="js-cat-files" />';
        if(count($CatPage->get_FileArray($cat)) > 0) {
            $cat_files = '<input type="hidden" value="'.implode("-#-",$CatPage->get_FileArray($cat)).'" class="js-cat-files" />';
        }

        $ul_cats .= '<li class="js-li-cat'.$link_class.' mo-li ui-widget-content ui-corner-all">';
        $in_cat = "[".$CatPage->get_FileSystemName($cat,false)."]";
        $status = '<span class="js-status">'.count($page_array).'</span> '.$page_lang["pages"];
        $type = $CatPage->get_Type($cat,false);
        if($type == EXT_LINK) {
            $cat_files = '';
            $status = $page_lang["url"].' '.$page_lang["target"].' <span class="js-status">'.substr($CatPage->get_HrefTarget($cat,false),1).'</span>';
            $ul_cats .= li_table($CatPage->get_HrefText($cat,false),$in_cat,$status,"cat",$cat_files,$CatPage->get_Href($cat,false));
        } else
            $ul_cats .= li_table($CatPage->get_HrefText($cat,false),$in_cat,$status,"cat",$cat_files);
        if($type == EXT_LINK) {
            $ul_cats .= '</li>';
            continue;
        }
        $ul_cats .= '<ul class="js-ul-pages mo-in-ul-ul">';
        foreach($page_array as $page) {
            $link_class = '';
            if($CatPage->get_Type($cat,$page) == EXT_LINK) {
                $link_class = ' js-link';
            }
            $ul_cats .= '<li class="js-li-page'.$link_class.' mo-in-ul-li ui-widget ui-state-default ui-corner-all">';
            $in_page = $in_cat."[".$CatPage->get_FileSystemName($cat,$page)."]";
            $type = $CatPage->get_Type($cat,$page);
$href = false;
            if($type == EXT_LINK) {
                $status = $page_lang["url"].' '.$page_lang["target"].' <span class="js-status">'.substr($CatPage->get_HrefTarget($cat,$page),1).'</span>';
$href = $CatPage->get_Href($cat,$page);
            }
            else
                $status = '<span class="js-status">'.$page_lang[$type].'</span>';
            $ul_cats .= li_table($CatPage->get_HrefText($cat,$page),$in_page,$status,$type,"",$href);
            $ul_cats .= '</li>';

        }
        $ul_cats .= '</ul>';
        $ul_cats .= '</li>';
    }
    $ul_cats .= '</ul>';
    return $ul_cats;
}

function li_table($name,$in_cat_page,$status,$type,$cat_files,$cat_page_link = false) {
    $class = "page";
    $move_page = " js-move-me-page";
    $head_div_open = '';
    $head_div_close = '';
    if($type == "cat") {
        $head_div_open = '<div class="mo-li-head-tag ui-state-active ui-corner-all">';
        $head_div_close = '</div>';
        $class = "cat";
        $move_page = "";
    }
    if($cat_page_link !== false and $type == "cat")
        $head_div_open = '<div class="mo-li-head-tag mo-li-head-tag-no-ul ui-state-active ui-corner-all">';

    $table = $head_div_open.'<table class="js-tools-show-hide mo-tag-height-from-icon" width="100%" cellspacing="0" border="0" cellpadding="0">'
        .'<tbody>'
            .'<tr>';
                if($type == "cat") {
                    $table .= '<td class="js-move-cat">'
                        .'<img src="'.ICON_URL_SLICE.'" alt="move" hspace="0" vspace="0" align="left" border="0" class="js-move-me-cat mo-tool-icon mo-icons-icon mo-icons-move" />'
                    .'</td>';
                }
                $table .= '<td class="mo-nowrap'.$move_page.'" width="99%">';
                if($cat_page_link !== false) {
                    $target = "_blank";
                    if(strlen($cat_page_link) <= 1) {
                        $cat_page_link = "#";
                        $target = "_self";
                    }
                    $table .= '<a href="'.$cat_page_link.'" class="js-link-href" target="'.$target.'"><img class="mo-icon-link mo-icons-icon mo-icons-website-small" src="'.ICON_URL_SLICE.'" alt=" " hspace="0" vspace="0" border="0" /></a>';
                }
                    $table .= '<span class="js-'.$class.'-name mo-padding-left js-normal-in-name js-rename-mode-hide">'.$name.'</span>'
                    .'<input type="hidden" name="sort_array'.$in_cat_page.'" value="'.$in_cat_page.'" class="js-in-'.$class.' js-in-cat-page" />'
                    .$cat_files
                    .'<div class="js-edit-in-name mo-padding-left js-rename-mode-show">'
                    .'</div>'
                .'</td>';
                $table .= '<td class="mo-nowrap">'
                    .'<div class="js-rename-mode-hide mo-staus"><span class="mo-font-small">( '
                        .$status
                    .' )</span></div>'
                    .'<div class="js-edit-box js-rename-mode-show mo-staus">'
                    .'</div>'
                .'</td>'
                .'<td class="mo-nowrap">'
                    .'<div class="js-tools mo-tag-height-from-icon">';#
                        if($type != "cat" and $type != EXT_LINK) {
                            $table .= '<img class="js-tools-icon-show-hide js-copy-me-page mo-tool-icon mo-icons-icon mo-icons-copy" src="'.ICON_URL_SLICE.'" alt="new_page" hspace="0" vspace="0" />';
                            $table .= '<img class="js-tools-icon-show-hide js-edit-page mo-tool-icon mo-icons-icon mo-icons-page-edit" src="'.ICON_URL_SLICE.'" alt="page-edit" hspace="0" vspace="0" />';
                        } elseif($type == EXT_LINK) {
                            $table .= '<img class="js-tools-icon-show-hide mo-tool-icon mo-icon-blank" src="'.ICON_URL_SLICE.'" alt=" " hspace="0" vspace="0" />';
                            $table .= '<img class="js-tools-icon-show-hide mo-tool-icon mo-icon-blank" src="'.ICON_URL_SLICE.'" alt=" " hspace="0" vspace="0" />';
                        }
                        $table .= '<img class="js-tools-icon-show-hide js-edit-rename mo-tool-icon mo-icons-icon mo-icons-work" src="'.ICON_URL_SLICE.'" alt="work" hspace="0" vspace="0" />'
                        .'<img class="js-tools-icon-show-hide js-edit-delete mo-tool-icon mo-icons-icon mo-icons-delete" src="'.ICON_URL_SLICE.'" alt="delete" hspace="0" vspace="0" />'
                    .'</div>'
                .'</td>'
            .'</tr>'
        .'</tbody>'
    .'</table>'.$head_div_close;
    return $table;
}

# hier bleiben die rechte erhalten
function cat_page_move($name) {
    if(true !== ($error = moveFileDir(CONTENT_DIR_REL.$name["org"],CONTENT_DIR_REL.$name["new"])))
        return ajax_return("error",false,$error,true,"js-dialog-reload");
    return write_sort_list($name);
}

# hier werden die rechte neu gesetzt
function cat_page_copy($name) {
    if(true !== ($error = copyFile(CONTENT_DIR_REL.$name["org"],CONTENT_DIR_REL.$name["new"])))
        return ajax_return("error",false,$error,true,"js-dialog-reload");
    return write_sort_list();
}

function cat_page_del($name) {
    if($name["type"] == "page") {
        if(true !== ($error = deleteFile(CONTENT_DIR_REL.$name["org"])))
            return ajax_return("error",false,$error,true,"js-dialog-reload");
    }
    if($name["type"] == "cat") {
        if(true !== ($error = deleteDir(CONTENT_DIR_REL.$name["org"])))
            return ajax_return("error",false,$error,true,"js-dialog-reload");
    }
    return write_sort_list();
}

function cat_page_new($name) {
    if($name["type"] == "page") {
        $page_inhalt = "";
        if(substr($name["new"],-(EXT_LENGTH)) != EXT_LINK) {
            $page_inhalt = "[ueber1|Das ist eine Inhaltsseite]";
        }
        if(true !== ($error = saveContentToPage($page_inhalt,CONTENT_DIR_REL.$name["new"],true)))
            return ajax_return("error",false,$error,true,"js-dialog-reload");
    } elseif($name["type"] == "cat") {
        if(true !== ($error = mkdirMulti(array(CONTENT_DIR_REL."/".$name["new"],CONTENT_DIR_REL."/".$name["new"]."/".CONTENT_FILES_DIR_NAME))))
            return ajax_return("error",false,$error,true,"js-dialog-reload");
    }
    return write_sort_list();
}

# schreibt die sortliste neu Achtung es muss success oder error zurück kommen
function write_sort_list($movecat = false) {
    if(false === getRequestValue('sort_array','post'))
        return ajax_return("success",false);
    global $cat_page_sort_array;
    $post = getRequestValue('sort_array','post');
    # da im frontend die cat erst umbenant wird wenn vom server ein succsses zurück kamm
    # müssen wir hier das sort_array aktualiesieren
    if(is_array($movecat)) {
        if($movecat['type'] == "cat" and isset($post[$movecat['new']]) and isset($post[$movecat['org']])) {
            $post[$movecat['new']] = $post[$movecat['org']];
            unset($post[$movecat['org']]);
        }
    }
    $cat_page_sort_array = array();
    foreach($post as $cat => $tmp) {
        if(substr($cat,-(EXT_LENGTH)) == EXT_LINK) {
            $cat_page_sort_array[$cat] = "null";
            continue;
        } else {
            $cat_page_sort_array[$cat] = array();
        }
        if(is_array($post[$cat])) {
            foreach($post[$cat] as $page => $tmp) {
                $cat_page_sort_array[$cat][$page] = "null";
            }
        }
    }
    $sort_array = var_export($cat_page_sort_array,true);
    global $page_protect;
    if(true != (mo_file_put_contents(SORT_CAT_PAGE,"<?php if(!defined('IS_CMS')) die();\n\$cat_page_sort_array = ".$sort_array.";\n?>")))
        return ajax_return("error",false,returnMessage(false,getLanguageValue("error_write_sort_list")),true,"js-dialog-reload");

    global $CatPage;
    $CatPage = new CatPageClass();
    require_once(BASE_DIR_ADMIN.'editsite.php');
    if(true != ($error = write_xmlsitmap()))
        return $error;
    # wir schiken die neu selectbox zurück
    return ajax_return("success",false).'<span id="replace-item">'.returnCatPagesSelectbox().'</span>';
}

function get_page($cat, $page) {
    global $CatPage;
    $cat = $CatPage->get_FileSystemName($cat,false);
    $page = $CatPage->get_FileSystemName($cat,$page);
    if($CatPage->get_Type($cat,$page) != EXT_LINK) {
        if(false !== ($pagecontent = get_contents_ace_edit(CONTENT_DIR_REL.$cat.'/'.$page)))
            return ajax_return("success",false).'<textarea id="page-content">'.$pagecontent.'</textarea>';
    }
    return ajax_return("error",false,returnMessage(false,getLanguageValue("editor_content_error_open")),true,true);
}

function save_page($cat, $page) {
    global $CatPage;
    $cat = $CatPage->get_FileSystemName($cat,false);
    $page = $CatPage->get_FileSystemName($cat,$page);
    if(true !== ($error = saveContentToPage(getRequestValue('content','post',false),CONTENT_DIR_REL.$cat."/".$page)))
        return ajax_return("error",false,$error,true,true);
    return ajax_return("success",false);
}

function make_NewOrgCatPageFromRequest() {
    $post = getRequestValue('cat_page_change','post',false);
    $new_cat = key($post);
    $new_page = false;
    if(is_array($post[$new_cat])) {
        $new_page = key($post[$new_cat]);
        $tmp = substr($post[$new_cat][$new_page],1,-1);
        if(strpos($tmp,"][") > 1)
            list($org_cat,$org_page) = explode("][",$tmp);
        else
            return false;
    } else {
        $org_page = false;
        $org_cat = substr($post[$new_cat],1,-1);
    }
    global $CatPage;
    $name = array();
    $name["type"] = "cat";
    $name["new"] = $CatPage->get_UrlCoded($new_cat);
    $name["org"] = $CatPage->get_UrlCoded($org_cat);
    if($new_page and $org_page) {
        $name["type"] = "page";
        $name["new"] .= "/".$CatPage->get_UrlCoded($new_page);
        $name["org"] .= "/".$CatPage->get_UrlCoded($org_page);
    }
        
    return $name;
}

?>