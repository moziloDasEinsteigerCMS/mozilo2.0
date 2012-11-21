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
        if($value === "menusubs_2" and $CatPage->exists_CatPage($this->settings->get("menusubs_2"),false))
            return $this->getMenuPage($this->settings->get("menusubs_2"),false,true);
        if($value === "sitemap_content")
            return $this->getSidemapCat();
        return NULL;
    } // function getContent

    function getMenuCat() {
        global $CatPage, $CMS_CONF;
        $css = "cat-menusubs-link menusubs-link";
        $ul = '<ul class="cat-menusubs">';
        foreach($CatPage->get_CatArray() as $cat) {
            if(strpos($cat,"%2F") > 1) continue;
            if($this->settings->get("menusubs_2") == $cat)
                continue;
            if($CatPage->get_Type($cat,false) == EXT_LINK) {
                $ul .= '<li class="cat-menusubs">'.$CatPage->create_AutoLinkTag($cat,false,$css).'</li>';
            } elseif($CatPage->get_Type($cat,false) == "cat") {
                $cssactiv = "";
                $activ = false;
                if(!$CatPage->is_Activ($cat,false) and strstr(CAT_REQUEST,$cat."%2F")) {
                    $activ = true;
                    $cssactiv = "active";
                }
                $ul .= '<li class="cat-menusubs">'.$CatPage->create_AutoLinkTag($cat,false,$css.$cssactiv);
                if($activ or $CMS_CONF->get("usesubmenu") == 0 or $CatPage->is_Activ($cat,false))
                    $ul .= $this->getMenuPage($cat);
                $ul .= '</li>';
            }
        }
        return $ul.'</ul>';
    }

    function getMenuPage($cat,$subcat = false,$menu_2 = false) {
        global $CatPage, $CMS_CONF;
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
            if(strpos($page,"%2F") > 1
                    and $CatPage->get_Type($cat,$page) == EXT_HIDDEN
                    and $CatPage->get_Type($page,false) == "cat") {
                $ul .= '<li class="subcat-menusubs">'.$this->create_CatSubLinkTag($cat,$page,"subcat-menusubs-link menusubs-link");
                if(strstr(CAT_REQUEST,$cat."%2F") or $CMS_CONF->get("usesubmenu") == 0)
                    $ul .= $this->getMenuPage($page,true);
                $ul .= '</li>'."\n";
            } elseif($CatPage->get_Type($cat,$page) != EXT_HIDDEN) {
                if($CatPage->get_Type($cat,$page) == EXT_LINK)
                    continue;
                $ul .= '<li class="page-menusubs">'.$CatPage->create_AutoLinkTag($cat,$page,"page-menusubs-link menusubs-link").'</li>';
            }
        }
        return $ul.'</ul>';
    }

    function create_CatSubLinkTag($cat,$page,$css) {
        global $specialchars, $CatPage, $language;
        $cssactiv = "";
        if(strstr(CAT_REQUEST,$cat."%2F"))
            $cssactiv = "active";
        return $CatPage->create_LinkTag(
                $CatPage->get_Href($page,false),
                $specialchars->rebuildSpecialChars(substr($page,(strrpos($page,"%2F") + 3)),true,true),
                $css.$cssactiv,
                $language->getLanguageHtml("tooltip_link_category_1", $CatPage->get_HrefText($page,false)));
    }

    function getSidemapCat() {
        global $CatPage, $CMS_CONF, $language;
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
                }
                continue;
            }
            if($CatPage->get_Type($cat,false) == "cat") {
                $sitemap .= '<h2>'.$CatPage->create_AutoLinkTag($cat,false,"").'</h2>';
                $sitemap .= $this->getSidemapPage($cat);
            }
        }
        return $sitemap.$menu_2.'</div>';
    }

    function getSidemapPage($cat,$menu2 = false) {
        global $CatPage, $CMS_CONF;
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
                $sitemap .= '<li><h3>'.$this->create_CatSubLinkTag($cat,$page,"").'</h3>';
                $sitemap .= $this->getSidemapPage($page);
                $sitemap .= '</li>'."\n";
            } elseif($CatPage->get_Type($cat,$page) != EXT_HIDDEN) {
                $sitemap .= '<li>'.$CatPage->create_AutoLinkTag($cat,$page,"").'</li>';
            }
        }
        return $sitemap.'</ul>';
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
            array()
            );
        return $info;
    } // function getInfo
}

?>