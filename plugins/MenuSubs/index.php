<?php if(!defined('IS_CMS')) die();

class MenuSubs extends Plugin {

    function getContent($value) {
        global $CatPage;

        if($value == "plugin_first" and getRequestValue('action', 'get') and getRequestValue('action', 'get') == "sitemap") {
            define("ACTION_CONTENT",false);
            global $pagecontent;
            $pagecontent = "{MenuSubs|sitemap_content}";
            return;
        }
        if($value === false)
            return $this->getMenuCat();
        if($value === "main")
            return $this->getMenuCat(true);
        if($value === "detail") {
            if(strpos(CAT_REQUEST,"%2F") > 1) {
                $tmp_cat = explode("%2F",CAT_REQUEST);
                return $this->getMenuPage($tmp_cat[0]);
            } else
                return $this->getMenuPage(CAT_REQUEST);
        }
        if($value === "menusubs_2" and $CatPage->exists_CatPage($this->settings->get("menusubs_2"),false))
            return $this->getMenuPage($this->settings->get("menusubs_2"),false,true);
        if($value === "sitemap_content")
            return $this->getSidemapCat();
        if($value === "breadcrumb")
            return $this->getBreadcrumb();
        return NULL;
    } // function getContent

    function getBreadcrumb() {
        global $CatPage, $CMS_CONF;
        $css = "";
        $ul = '<ul class="menusubs-breadcrumb">';
        $ul .= '<li><a href="{BASE_URL}" class="" title="Home">Home</a></li>';

        foreach($CatPage->get_CatArray() as $cat) {
            if($CatPage->get_Type($cat,false) == "cat" and $CatPage->is_Activ($cat,false)) {
                if(strpos($cat,"%2F") > 1) {
                    $cats = explode("%2F",$cat);
                    $linkcat = "";
                    foreach($cats as $ca) {
                        $linkcat .= "%2F".$ca;
                        $ul .= '<li> »&nbsp;&nbsp;'.$this->create_BreadcrumbLinkTag($linkcat,$ca,"").'</li>';
                    }
                } else {
                        $ul .= '<li> »&nbsp;&nbsp;'.$CatPage->create_AutoLinkTag($cat,false,"").'</li>';
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
#echo CAT_REQUEST." = ".$cat." -> ".$page." 1<br />\n";
            if(strpos($page,"%2F") > 1
                    and $CatPage->get_Type($cat,$page) == EXT_HIDDEN
                    and $CatPage->get_Type($page,false) == "cat") {
                if($CatPage->is_Activ($cat,$page))
                    $ul .= '<li> »&nbsp;&nbsp;'.$this->create_CatSubLinkTag($cat,$page,"").'</li>';
                if(strstr(CAT_REQUEST,$page))
#                if($CatPage->is_Activ($cat,$page))
                    $ul .= $this->getBreadcrumbPage($page,true);
            } elseif($CatPage->get_Type($cat,$page) != EXT_HIDDEN and $CatPage->is_Activ($cat,$page)) {
#echo $cat." -> ".$page." 2<br />\n";
                if($CMS_CONF->get("hidecatnamedpages") == "true" and $cat == $page)
                    continue;
                $ul .= '<li> »&nbsp;&nbsp;'.$CatPage->create_AutoLinkTag($cat,$page,"").'</li>';
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
        $ul = '<ul class="cat-menusubs">';
        foreach($CatPage->get_CatArray() as $cat) {
            if(strpos($cat,"%2F") > 1) continue;
            if($this->settings->get("menusubs_2") == $cat)
                continue;
            if($CatPage->get_Type($cat,false) == EXT_LINK) {
                $ul .= '<li class="cat-menusubs">'.$CatPage->create_AutoLinkTag($cat,false,$css).'</li>';
                $return = true;
            } elseif($CatPage->get_Type($cat,false) == "cat") {
                $cssactiv = "";
                $activ = false;
                if(!$CatPage->is_Activ($cat,false) and strstr(CAT_REQUEST,$cat."%2F")) {
                    $activ = true;
                    $cssactiv = "active";
                } elseif($CatPage->is_Activ($cat,false)) {
#                    $cssactiv = "active";
                    $activ = true;
                }
                $ul .= '<li class="cat-menusubs">'.$CatPage->create_AutoLinkTag($cat,false,$css.$cssactiv);
                if(!$only_main and ($activ or $CMS_CONF->get("usesubmenu") == 0)) {# or $CatPage->is_Activ($cat,false)
                    $ul .= $this->getMenuPage($cat);
                }
                $ul .= '</li>';
                $return = true;
            }
        }
        if($return)
            return $ul.'</ul>';
        return null;
    }

    function getMenuPage($cat,$subcat = false,$menu_2 = false) {
        global $CatPage, $CMS_CONF;
        $return = false;
        $ul = '<ul class="page-menusubs">';
        if($subcat)
            $ul = '<ul class="subcat-menusubs">';
        if($menu_2)
            $ul = '<ul class="cat-menusubs" id="menusubs2">';
        foreach($CatPage->get_PageArray($cat,array(EXT_PAGE,EXT_HIDDEN,EXT_LINK)) as $page) {
            if(strpos($cat,"%2F") > 1
                    and $CMS_CONF->get("hidecatnamedpages") == "true"
                    and substr($cat,(strrpos($cat,"%2F") + 3)) == $page)
                continue;
            if($CatPage->get_Type($cat,$page) == EXT_LINK) {
                $ul .= '<li class="page-menusubs">'.$CatPage->create_AutoLinkTag($cat,$page,"page-menusubs-link menusubs-link").'</li>';
                $return = true;
                continue;
            }
            if(strpos($page,"%2F") > 1
                    and $CatPage->get_Type($cat,$page) == EXT_HIDDEN
                    and $CatPage->get_Type($page,false) == "cat") {
                $ul .= '<li class="subcat-menusubs">'.$this->create_CatSubLinkTag($cat,$page,"subcat-menusubs-link menusubs-link");
                if(strstr(CAT_REQUEST,$page) or $CMS_CONF->get("usesubmenu") == 0)
                    $ul .= $this->getMenuPage($page,true);
                $ul .= '</li>'."\n";
                $return = true;
            } elseif($CatPage->get_Type($cat,$page) != EXT_HIDDEN) {
                if($CatPage->get_Type($cat,$page) == EXT_LINK)
                    continue;
                $ul .= '<li class="page-menusubs">'.$CatPage->create_AutoLinkTag($cat,$page,"page-menusubs-link menusubs-link").'</li>';
                $return = true;
            }
        }
        if($return)
            return $ul.'</ul>';
        return null;
    }

    function create_CatSubLinkTag($cat,$page,$css) {
        global $specialchars, $CatPage, $language;
        $cssactiv = "";
#echo CAT_REQUEST."<br />\n";
        if(strstr(CAT_REQUEST,$page)) {
#        if(strstr(CAT_REQUEST,$cat."%2F")) {
            $cssactiv = "active";
#echo CAT_REQUEST." = ".$page."<br />\n";
        }
        return $CatPage->create_LinkTag(
                $CatPage->get_Href($page,false),
                $specialchars->rebuildSpecialChars(substr($page,(strrpos($page,"%2F") + 3)),true,true),
                $css.$cssactiv,
                $language->getLanguageHtml("tooltip_link_category_1", $CatPage->get_HrefText($page,false)));
    }

    function getSidemapCat() {
        global $CatPage, $CMS_CONF, $language;
        $return = false;
        $menu_2 = "";
        $include_pages = array(EXT_PAGE);
        if($CMS_CONF->get("showhiddenpagesinsitemap") == "true") {
            $include_pages = array(EXT_PAGE,EXT_HIDDEN);
        }
        $sitemap = '<h1 id="menusubs-sidemap-title">'.$language->getLanguageValue("message_sitemap_0")."</h1>"
                    .'<div class="sitemap" id="menusubs-sidemap">';
        foreach($CatPage->get_CatArray(false, false, $include_pages) as $cat) {
            if(strpos($cat,"%2F") > 1) continue;
            if($this->settings->get("menusubs_2") == $cat) {
                if($this->settings->get("sidemap_show_menu2") == "true") {
                    $menu_2 = '<h2>'.$CatPage->get_HrefText($cat,false).'</h2>';
                    $menu_2 .= $this->getSidemapPage($cat,true);
                    $return = true;
                }
                continue;
            }
            if($CatPage->get_Type($cat,false) == "cat") {
                $sitemap .= '<h2>'.$CatPage->create_AutoLinkTag($cat,false,"").'</h2>';
                $sitemap .= $this->getSidemapPage($cat);
                $return = true;
            }
        }
        if($return)
            return $sitemap.$menu_2.'</div>';
        return null;
    }

    function getSidemapPage($cat,$menu2 = false) {
        global $CatPage, $CMS_CONF;
        $return = false;
        $sitemap = '<ul>';
        if($menu2)
            $sitemap = '<ul id="menusubs-sidemap-menu2">';
        foreach($CatPage->get_PageArray($cat,array(EXT_PAGE,EXT_HIDDEN)) as $page) {
            if(strpos($cat,"%2F") > 1
                    and $CMS_CONF->get("hidecatnamedpages") == "true"
                    and substr($cat,(strrpos($cat,"%2F") + 3)) == $page)
                continue;
            if(strpos($page,"%2F") > 1
                    and $CatPage->get_Type($cat,$page) == EXT_HIDDEN
                    and $CatPage->get_Type($page,false) == "cat") {
                $return = true;
                $sitemap .= '<li><h3>'.$this->create_CatSubLinkTag($cat,$page,"").'</h3>';
                $sitemap .= $this->getSidemapPage($page);
                $sitemap .= '</li>'."\n";
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
        if(file_exists(BASE_DIR.PLUGIN_DIR_NAME."/MenuSubs/lang/conf_".$ADMIN_CONF->get("language").".txt"))
            $conf_txt = new Properties(BASE_DIR.PLUGIN_DIR_NAME."/MenuSubs/lang/conf_".$ADMIN_CONF->get("language").".txt");
        else
            $conf_txt = new Properties(BASE_DIR.PLUGIN_DIR_NAME."/MenuSubs/lang/conf_deDE.txt");

        // Das muß auf jeden Fall geschehen!
        $config = array();
        $descriptions = array("no_menusubs_2" => $conf_txt->get("no_menusubs_2"));
        $cat_array = $CatPage->get_CatArray(false,false,array(EXT_PAGE,EXT_HIDDEN));
        foreach($cat_array as $cat) {
            if(strpos($cat,"%2F") !== false) continue;
            $descriptions[$cat] = $CatPage->get_HrefText($cat, false);
        }
        $config['menusubs_2'] = array(
            "type" => "select",
            "description" => $conf_txt->get("menusubs_2"),
            "descriptions" => $descriptions,
            "multiple" => "false"
            ); 
        $config['sidemap_show_menu2'] = array(
            "type" => "checkbox",
            "description" => $conf_txt->get("sidemap_show_menu2"),
        );
        return $config;
    } // function getConfig    

    function getInfo() {
        global $ADMIN_CONF;
        if(file_exists(BASE_DIR.PLUGIN_DIR_NAME."/MenuSubs/lang/info_".$ADMIN_CONF->get("language").".txt"))
            $info_txt = file_get_contents(BASE_DIR.PLUGIN_DIR_NAME."/MenuSubs/lang/info_".$ADMIN_CONF->get("language").".txt");
        else
            $info_txt = file_get_contents(BASE_DIR.PLUGIN_DIR_NAME."/MenuSubs/lang/info_deDE.txt");
        $info = array(
            // Plugin-Name
            "<b>MenuSubs</b> Revision: 1",
            // Plugin-Version
            "2.0",
            // Kurzbeschreibung
            $info_txt,
            // Name des Autors
            "stefanbe",
            // Download-URL
            "",
            array("{MenuSubs}" => "MenuSubs")
            );
        return $info;
    } // function getInfo
}

?>