<?php if(!defined('IS_CMS')) die();

class MenuSubs extends Plugin {
    var $breadcrumb_delimiter = "";
    var $menu2 = false;
    var $sub_count = 1;
    function getContent($value) {
        global $CatPage;

        if($value == "plugin_first" and getRequestValue('action', 'get') and getRequestValue('action', 'get') == "sitemap") {
            if(!defined("ACTION_CONTENT"))
                define("ACTION_CONTENT",false);
            global $pagecontent;
            $pagecontent = "{MenuSubs|sitemap_content}";
            return;
        }

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
                return $this->getMenuPage($tmp_cat[0]);
            } else
                return $this->getMenuPage(CAT_REQUEST);
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
        $ul = '<ul class="menusubs-breadcrumb">';
        $ul .= '<li class="menusubs-breadcrumb-home"><a href="{BASE_URL}" title="Home">Home</a></li>';

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
#                if($CatPage->is_Activ($cat,$page))
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
        $ul = '<ul class="cat-menusubs">';
        $count = 1;

        foreach($CatPage->get_CatArray() as $cat) {
            if(strpos($cat,"%2F") > 1) continue;
            if($this->menu2 and $this->menu2 == $cat)
                continue;

            if($CatPage->get_Type($cat,false) == EXT_LINK) {
                $ul .= '<li class="cat-menusubs cat'.$count.'">'.$CatPage->create_AutoLinkTag($cat,false,$css).'</li>';
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
                $ul .= '<li class="cat-menusubs cat'.$count.'">'.$CatPage->create_AutoLinkTag($cat,false,$css.$cssactiv);
                if(!$only_main and ($activ or $CMS_CONF->get("usesubmenu") == 2)) {# or $CatPage->is_Activ($cat,false)
                    $this->sub_count = 1;
                    $ul .= $this->getMenuPage($cat);
                }
                $ul .= '</li>';
                $return = true;
            }
            $count++;
        }
        if($return)
            return $ul.'</ul>';
        return null;
    }

    function getMenuPage($cat,$subcat = false,$menu_2 = false) {
        global $CatPage, $CMS_CONF;
        $return = false;
        $ul = '<ul class="page-menusubs sub'.$this->sub_count.'">';
        if($subcat)
            $ul = '<ul class="subcat-menusubs sub'.$this->sub_count.'">';
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
                $ul .= '<li class="page-menusubs page'.$count.'">'.$CatPage->create_AutoLinkTag($cat,$page,"page-menusubs-link menusubs-link").'</li>';
                $return = true;
                continue;
            }
            if(strpos($page,"%2F") > 1
                    and $CatPage->get_Type($cat,$page) == EXT_HIDDEN
                    and $CatPage->get_Type($page,false) == "cat"
                    and count($CatPage->get_PageArray($page,array(EXT_PAGE,EXT_HIDDEN,EXT_LINK))) > 0) {
                $ul .= '<li class="subcat-menusubs page'.$count.'">'.$this->create_CatSubLinkTag($cat,$page,"subcat-menusubs-link menusubs-link");
                if(strstr(CAT_REQUEST,$page) or $CMS_CONF->get("usesubmenu") == 2)
                    $ul .= $this->getMenuPage($page,true);
                $ul .= '</li>'."\n";
                $return = true;
            } elseif($CatPage->get_Type($cat,$page) != EXT_HIDDEN) {
                if($CatPage->get_Type($cat,$page) == EXT_LINK)
                    continue;
                $ul .= '<li class="page-menusubs page'.$count.'">'.$CatPage->create_AutoLinkTag($cat,$page,"page-menusubs-link menusubs-link").'</li>';
                $return = true;
            }
            $count++;
        }
        if($return)
            return $ul.'</ul>';
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
                $sitemap .= '<li><h3>'.$this->create_CatSubLinkTag($cat,$page,"").'</h3>';
                $sitemap .= $this->getSitemapPage($page);
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
            "description" => $conf_txt->get("sitemap_show_menu2"),
        );
        $config['breadcrumb_delimiter'] = array(
            "type" => "text",
            "maxlength" => "10",
            "size" => "10",
            "description" => 'Trennzeichen der Brotkrümel Einträge. Default ist "»"'
        );
        return $config;
    }

    function getInfo() {
        global $ADMIN_CONF;
        if(file_exists(BASE_DIR.PLUGIN_DIR_NAME."/MenuSubs/lang/info_".$ADMIN_CONF->get("language").".txt"))
            $info_txt = file_get_contents(BASE_DIR.PLUGIN_DIR_NAME."/MenuSubs/lang/info_".$ADMIN_CONF->get("language").".txt");
        else
            $info_txt = file_get_contents(BASE_DIR.PLUGIN_DIR_NAME."/MenuSubs/lang/info_deDE.txt");
        $info = array(
            // Plugin-Name
            "<b>MenuSubs</b> Revision: 5",
            // Plugin-Version
            "2.0",
            // Kurzbeschreibung
            $info_txt,
            // Name des Autors
            "stefanbe",
            // Download-URL
            array("https://github.com/mozilo/mozilo2.0","moziloCMS 2.0"),
            array("{MenuSubs|main/detail/menusubs_2/breadcrumb}" => "MenuSubs")
            );
        return $info;
    }
}

?>