<?php if(!defined('IS_CMS')) die();

class MenuSubsMobile extends Plugin {
    var $breadcrumb_delimiter = "";
    var $id = 0;
    var $menu2 = false;
var $sub_count = 1;
    function getContent($value) {
        global $CatPage, $specialchars;

        if($value == "plugin_first") {
            if(getRequestValue('action', 'get') and getRequestValue('action', 'get') == "sitemap") {
                if(!defined("ACTION_CONTENT"))
                    define("ACTION_CONTENT",false);
                global $pagecontent;
                $pagecontent = "{MenuSubsMobile|sitemap_content}";
            }
            return;
        }

        global $syntax;
        $syntax->insert_in_head('<script type="text/javascript" src="'.$this->PLUGIN_SELF_URL.'menusubsmobile.js"></script>');
        if($this->settings->get("menusubs_2") != "no_menusubs_2"
                and $CatPage->exists_CatPage(replaceFileMarker($this->settings->get("menusubs_2"),false),false)) {
            global $specialchars;
            $this->menu2 = $specialchars->replaceSpecialChars(replaceFileMarker($this->settings->get("menusubs_2"),false),false);
        }

        if($value === false)
            return $this->getMenuCat();
        if($value === "main")
            return $this->getMenuCat(true);
        if($value === "detail") {
            if(strpos(CAT_REQUEST,"%2F") > 1) {
                $tmp_cat = explode("%2F",CAT_REQUEST);
                return $this->getMenuPage($tmp_cat[0],false,false,true);
            } else
                return $this->getMenuPage(CAT_REQUEST,false,false,true);
        }

        if($this->menu2 and $value === "menusubs_2")
            return $this->getMenuPage($this->menu2,false,true);
        if($value === "sitemap_content")
            return $this->getSitemapCat();
        if($value === "breadcrumb") {
            $this->breadcrumb_delimiter = "»";
            if($this->settings->get("breadcrumb_delimiter"))
                $this->breadcrumb_delimiter = $this->settings->get("breadcrumb_delimiter");
            return $this->getBreadcrumb();
        }
        return NULL;
    }

    function getBreadcrumb() {
        global $CatPage, $CMS_CONF;
        $css = "";
        $ul = '<ul class="menusubs-breadcrumb">'
            .'<li class="menusubs-breadcrumb-home"><a href="{BASE_URL}" title="Home">Home</a></li>';

        foreach($CatPage->get_CatArray() as $cat) {
            if($CatPage->get_Type($cat,false) == "cat" and $CatPage->is_Activ($cat,false)) {
                if(strpos($cat,"%2F") > 1) {
                    $cats = explode("%2F",$cat);
                    $linkcat = "";
                    foreach($cats as $ca) {
                        $linkcat .= "%2F".$ca;
                        $ul .= '<li>'.$this->breadcrumb_delimiter.$this->create_BreadcrumbLinkTag($linkcat,$ca,"").'</li>';
                    }
                } else {
                        $ul .= '<li>'.$this->breadcrumb_delimiter.$CatPage->create_AutoLinkTag($cat,false,"").'</li>';
                }
                $ul .= $this->getBreadcrumbPage($cat);
            }
        }
        return $ul.'</ul>';
    }

    function getBreadcrumbPage($cat,$subcat = false,$menu_2 = false) {
        global $CatPage, $CMS_CONF;
        $ul = '';
        foreach($CatPage->get_PageArray($cat,array(EXT_PAGE,EXT_HIDDEN)) as $page) {
            if(strpos($cat,"%2F") > 1
                    and $CMS_CONF->get("hidecatnamedpages") == "true"
                    and substr($cat,(strrpos($cat,"%2F") + 3)) == $page)
                continue;
            if(strpos($page,"%2F") > 1
                    and $CatPage->get_Type($cat,$page) == EXT_HIDDEN
                    and $CatPage->get_Type($page,false) == "cat") {
                if($CatPage->is_Activ($cat,$page))
                    $ul .= '<li>'.$this->breadcrumb_delimiter.$this->create_CatSubLinkTag($cat,$page,"").'</li>';
                if(strstr(CAT_REQUEST,$page))
                    $ul .= $this->getBreadcrumbPage($page,true);
            } elseif($CatPage->get_Type($cat,$page) != EXT_HIDDEN and $CatPage->is_Activ($cat,$page)) {
                if($CMS_CONF->get("hidecatnamedpages") == "true" and $cat == $page)
                    continue;
                $ul .= '<li>'.$this->breadcrumb_delimiter.$CatPage->create_AutoLinkTag($cat,$page,"").'</li>';
            }
        }
        return $ul.'';
    }

    function create_BreadcrumbLinkTag($cat,$page,$css) {
        global $specialchars, $CatPage, $language;
        $cssactiv = "";
        $cat = substr($cat,3);
        return $CatPage->create_LinkTag(
                $CatPage->get_Href($cat,false),
                $specialchars->rebuildSpecialChars($page,true,true),
                $css.$cssactiv,
                $language->getLanguageHtml("tooltip_link_category_1", $page));
    }

    function getMenuCat($only_main = false) {
        global $CatPage, $CMS_CONF;
        $return = false;
        $css = "cat-menusubs-link menusubs-link";
        $noinput = '';
        if($CMS_CONF->get("usesubmenu") == 2)
            $noinput = ' menusubs-noinput';

$count = 1;
        $js = '';
        $js_onclick = '';
        $hidden = $this->settings->get("hidden");
        if(strlen($hidden) > 4 and  strpos($hidden,"=") > 0 and strpos($hidden,":") > 3) {
            $hidden = explode(",",$hidden);
            $js = '<script type="text/javascript">'
                .'var ms_hidden = new Array();';
            foreach($hidden as $pos => $object) {
                $object = explode("=",$object);
                if(isset($object[0]) and isset($object[1]) and strpos($object[1],":") > 2) {
                    $js .= 'ms_hidden['.$pos.'] = new Object();';
                    $js .= 'ms_hidden['.$pos.']["id"] = "'.trim($object[0]).'";';
                    $object = explode(":",$object[1]);
                    $js .= 'ms_hidden['.$pos.']["para"] = "'.trim($object[0]).'";'
                        .'ms_hidden['.$pos.']["val"] = "'.trim($object[1]).'";';
                }
            }
            $js .= '</script>';
            $js_onclick = ' onchange="menuSubsToggleContent();"';
        }
        $ul = '<div id="menusubs-cats" class="menusubs-box'.$noinput.'">'
                .$js
                .'<div class="menusubs-box-margintop">'
                    .'<div class="menusubs-box-fontsize">'
                        .'<label for="menusubs-label-id'.$this->id.'" class="menusubs-show-hide"><span>&equiv;</span></label>'
                        .'<input id="menusubs-label-id'.$this->id.'" class="menusubs-show-hide" type="checkbox"'.$js_onclick.' checked="checked" />'
                        .'<ul class="cat-menusubs">';
        foreach($CatPage->get_CatArray() as $cat) {
            if(strpos($cat,"%2F") > 1) continue;
            if($this->menu2 and $this->menu2 == $cat)
                continue;
            if($CatPage->get_Type($cat,false) == EXT_LINK) {
                $ul .= '<li class="cat-menusubs cat'.$count.'"><div>'.$CatPage->create_AutoLinkTag($cat,false,$css).'</div></li>';
                $return = true;
            } elseif($CatPage->get_Type($cat,false) == "cat") {
                $cssactiv = "";
                $activ = false;
                if(!$CatPage->is_Activ($cat,false) and strstr(CAT_REQUEST,$cat."%2F")) {
                    $activ = true;
                    $cssactiv = "active";
                } elseif($CatPage->is_Activ($cat,false)) {
                    $activ = true;
                }
                $ul .= '<li class="cat-menusubs cat'.$count.'"><div>'.$CatPage->create_AutoLinkTag($cat,false,$css.$cssactiv).'</div>';
                $cc = ' checked="checked"';
$this->sub_count = 1;
                if(strlen(($tmp = $this->getMenuPage($cat))) > 1) {
                    if(!$only_main and ($activ or $CMS_CONF->get("usesubmenu") == 2)) {
                        $tmp = substr_replace($tmp, 'menusubs-show', 11, 15);
                        $cc = '';
                    }
                    $this->id++;
                    $ul .= '<label for="menusubs-label-id'.$this->id.'" class="menusubs-show-hide" onclick><span>&equiv;</span></label>'
                            .'<input id="menusubs-label-id'.$this->id.'" class="menusubs-show-hide" type="checkbox"'.$cc.' />';
                    $ul .= $tmp;
                }
                $ul .= '</li>';
                $return = true;
            }
$count++;
        }
        if($return)
            return $ul.'</ul></div></div></div>';
        return null;
    }

    function getMenuPage($cat,$subcat = false,$menu_2 = false,$only_detail = false) {
        global $CatPage, $CMS_CONF;
        $return = false;
        $ul = '<ul class="menusubs-hidden page-menusubs sub'.$this->sub_count.'">';
        if($subcat)
            $ul = '<ul class="menusubs-hidden subcat-menusubs sub'.$this->sub_count.'">';
        if($only_detail)
            $ul = '<ul class="menusubs-box-detail page-menusubs sub'.$this->sub_count.'">';
$this->sub_count++;
        if($menu_2)
            $ul = '<ul class="cat-menusubs" id="menusubs2">';
$count = 1;
        foreach($CatPage->get_PageArray($cat,array(EXT_PAGE,EXT_HIDDEN,EXT_LINK)) as $page) {
            if(strpos($cat,"%2F") > 1
                    and $CMS_CONF->get("hidecatnamedpages") == "true"
                    and substr($cat,(strrpos($cat,"%2F") + 3)) == $page)
                continue;
            if($CatPage->get_Type($cat,$page) == EXT_LINK) {
                $ul .= '<li class="page-menusubs page'.$count.'"><div>'.$CatPage->create_AutoLinkTag($cat,$page,"page-menusubs-link menusubs-link").'</div></li>';
                $return = true;
                continue;
            }
            if(strpos($page,"%2F") > 1
                    and $CatPage->get_Type($cat,$page) == EXT_HIDDEN
                    and $CatPage->get_Type($page,false) == "cat"
                    and count($CatPage->get_PageArray($page,array(EXT_PAGE,EXT_HIDDEN,EXT_LINK))) > 0) {
                $ul .= '<li class="subcat-menusubs page'.$count.'"><div>'.$this->create_CatSubLinkTag($cat,$page,"subcat-menusubs-link menusubs-link").'</div>';
                $cc = ' checked="checked"';
                $tmp = $this->getMenuPage($page,true);
                if(strstr(CAT_REQUEST,$page) or $CMS_CONF->get("usesubmenu") == 2) {
                    $tmp = substr_replace($tmp, 'menusubs-show', 11, 15);
                    $cc = '';
                }
                if($CMS_CONF->get("usesubmenu") < 2) {
                    $this->id++;
                    $ul .= '<label for="menusubs-label-id'.$this->id.'" class="menusubs-show-hide" onclick><span>&equiv;</span></label>'
                        .'<input id="menusubs-label-id'.$this->id.'" class="menusubs-show-hide" type="checkbox"'.$cc.' />';
                }
                $ul .= $tmp.'</li>';
                $return = true;
            } elseif($CatPage->get_Type($cat,$page) != EXT_HIDDEN) {
                if($CatPage->get_Type($cat,$page) == EXT_LINK)
                    continue;
                $ul .= '<li class="page-menusubs page'.$count.'"><div>'.$CatPage->create_AutoLinkTag($cat,$page,"page-menusubs-link menusubs-link").'</div></li>';
                $return = true;
            }
#$this->sub_count++;
$count++;
        }
        if($return) {
            return $ul.'</ul>';
        }
        return null;
    }

    function create_CatSubLinkTag($cat,$page,$css) {
        global $specialchars, $CatPage, $language;
        $cssactiv = "";
        if(strstr(CAT_REQUEST,$page))
            $cssactiv = "active";
        $text = $CatPage->get_HrefText($page,false);
        $text = substr($text,(strrpos($text,"/")));
        if($text[0] == "/")
            $text = substr($text,1);
        return $CatPage->create_LinkTag(
                $CatPage->get_Href($page,false),
                $text,
                $css.$cssactiv,
                $language->getLanguageHtml("tooltip_link_category_1", $CatPage->get_HrefText($page,false)));
    }

    function getSitemapCat() {
        global $CatPage, $CMS_CONF, $language;
        $return = false;
        $menu_2 = "";
        $include_pages = array(EXT_PAGE);
        if($CMS_CONF->get("showhiddenpagesinsitemap") == "true") {
            $include_pages = array(EXT_PAGE,EXT_HIDDEN);
        }
        $sitemap = '<h1 id="menusubs-sitemap-title">'.$language->getLanguageValue("message_sitemap_0")."</h1>"
                    .'<div class="sitemap" id="menusubs-sitemap">';
        foreach($CatPage->get_CatArray(false, false, $include_pages) as $cat) {
            if(strpos($cat,"%2F") > 1) continue;
            if($this->settings->get("menusubs_2") == $cat) {
                if($this->settings->get("sitemap_show_menu2") == "true") {
                    $menu_2 = '<h2>'.$CatPage->get_HrefText($cat,false).'</h2>';
                    $menu_2 .= $this->getSitemapPage($cat,true);
                    $return = true;
                }
                continue;
            }
            if($CatPage->get_Type($cat,false) == "cat") {
                $sitemap .= '<h2>'.$CatPage->create_AutoLinkTag($cat,false,"").'</h2>';
                $sitemap .= $this->getSitemapPage($cat);
                $return = true;
            }
        }
        if($return)
            return $sitemap.$menu_2.'</div>';
        return null;
    }

    function getSitemapPage($cat,$menu2 = false) {
        global $CatPage, $CMS_CONF;
        $return = false;
        $sitemap = '<ul>';
        if($menu2)
            $sitemap = '<ul id="menusubs-sitemap-menu2">';
        foreach($CatPage->get_PageArray($cat,array(EXT_PAGE,EXT_HIDDEN)) as $page) {
            if(strpos($cat,"%2F") > 1
                    and $CMS_CONF->get("hidecatnamedpages") == "true"
                    and substr($cat,(strrpos($cat,"%2F") + 3)) == $page)
                continue;
            if(strpos($page,"%2F") > 1
                    and $CatPage->get_Type($cat,$page) == EXT_HIDDEN
                    and $CatPage->get_Type($page,false) == "cat") {
                $return = true;
                $sitemap .= '<li><h3>'.$this->create_CatSubLinkTag($cat,$page,"").'</h3>'
                    .$this->getSitemapPage($page)
                    .'</li>'."\n";
            } elseif($CatPage->get_Type($cat,$page) != EXT_HIDDEN) {
                $sitemap .= '<li>'.$CatPage->create_AutoLinkTag($cat,$page,"").'</li>';
                $return = true;
            }
        }
        if($return)
            return $sitemap.'</ul>';
        return null;
    }

    function getConfig() {
        global $ADMIN_CONF;
        global $CatPage;
        if(IS_ADMIN and $this->settings->get("plugin_first") !== "true") {
            $this->settings->set("plugin_first","true");
        }
        if(file_exists($this->PLUGIN_SELF_DIR."lang/conf_".$ADMIN_CONF->get("language").".txt"))
            $conf_txt = new Properties($this->PLUGIN_SELF_DIR."lang/conf_".$ADMIN_CONF->get("language").".txt");
        else
            $conf_txt = new Properties($this->PLUGIN_SELF_DIR."lang/conf_deDE.txt");

        // Das muß auf jeden Fall geschehen!
        $config = array();
        $descriptions = array("no_menusubs_2" => $conf_txt->get("no_menusubs_2"));
        $cat_array = $CatPage->get_CatArray(false,false,array(EXT_PAGE,EXT_HIDDEN));
        foreach($cat_array as $cat) {
            if(strpos($cat,"%2F") !== false) continue;
            $descriptions[FILE_START.$cat.FILE_END] = $CatPage->get_HrefText($cat, false);
        }
        $config['menusubs_2'] = array(
            "type" => "select",
            "description" => $conf_txt->get("menusubs_2"),
            "descriptions" => $descriptions,
            "multiple" => "false"
            ); 
        $config['sitemap_show_menu2'] = array(
            "type" => "checkbox",
            "description" => $conf_txt->get("sitemap_show_menu2")
        );
        $config['breadcrumb_delimiter'] = array(
            "type" => "text",
            "maxlength" => "10",
            "size" => "10",
            "description" => $conf_txt->get("breadcrumb_delimiter")
        );
        $config['hidden'] = array(
            "type" => "text",
            "description" => $conf_txt->get("hidden"),
            'template' => '{hidden_description}<br />{hidden_text}'
        );
        return $config;
    }

    function getInfo() {
        global $ADMIN_CONF;
        if(file_exists($this->PLUGIN_SELF_DIR."lang/info_".$ADMIN_CONF->get("language").".txt"))
            $info_txt = file_get_contents($this->PLUGIN_SELF_DIR."lang/info_".$ADMIN_CONF->get("language").".txt");
        else
            $info_txt = file_get_contents($this->PLUGIN_SELF_DIR."lang/info_deDE.txt");
        $info = array(
            // Plugin-Name
            "<b>MenuSubsMobile</b> Revision: 5",
            // Plugin-Version
            "2.0",
            // Kurzbeschreibung
            $info_txt,
            // Name des Autors
            "stefanbe",
            // Download-URL
            '',
            array("{MenuSubsMobile|main/detail/menusubs_2/breadcrumb}" => "MenuSubsMobile")
        );
        return $info;
    }
}

?>