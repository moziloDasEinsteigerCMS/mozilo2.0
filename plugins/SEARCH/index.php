<?php if(!defined('IS_CMS')) die();

class SEARCH extends Plugin {

# möglichkeiten für  in plugin_first:
#   define("CAT_REQUEST",NULL); und define("PAGE_REQUEST",NULL); = es gibt kein activen menu punkt
#   define("ACTION_REQUEST","search"); = default websittitel und detailmenu (mit den suchergebnissen)
#           wenn die CAT_REQUEST und PAGE_REQUEST noch nicht definiert worden sind werden die durch
#           ACTION_REQUEST=search gesetzt
#   eigener {WEBSITE_TITLE} die einfach erseten
#   eigenes {DETAILMENU} die einfach erseten
    var $tmpl_fild = array();
    var $tmpl_result = array();
    var $tmpl_complet = array();
    var $tmpl = "{SEARCH_TITLE}{SEARCH_FILD}{RESULT_TITLE}{RESULT}";
    function getContent($value) {

        if($this->settings->get("template"))
            $this->tmpl = $this->settings->get("template");
        $this->tmpl = str_replace(array(","," "),"",$this->tmpl);

        $tmpl_replace = array();
        $tmpl_replace['result'] = "";
        $tmpl_replace['search_title'] = $this->settings->get("serachtitle");
        $tmpl_replace['serach_fild'] = "";
        $tmpl_replace['result_title'] = "";
#        $tmpl_replace['serach_help_link'] = $this->settings->get("serachhelp");
        $tmpl_replace['search_help_text'] = $this->settings->get("serachhelptext");

        global $language;
        global $specialchars;
        $dummy_cat = $language->getLanguageValue("message_search_0");
        if($this->settings->get("dummycat")) {
            $dummy_cat = $this->settings->get("dummycat");
        }
        $dummy_cat = $specialchars->replaceSpecialChars($dummy_cat,false);

        # wir benutzen einen eigenen Websitetitel
        if($value == "plugin_first" and $this->settings->get("websitetitel")) {
            global $template;
            $websitetitel = $specialchars->rebuildSpecialChars($this->settings->get("websitetitel"),false,true);
            if(strstr($websitetitel,"{SEARCH_WORDS}")) {
                global $SEARCH_REQUEST;
                $searchwords = $specialchars->rebuildSpecialChars($SEARCH_REQUEST, false, false);
                $websitetitel = str_replace("{SEARCH_WORDS}",$searchwords,$websitetitel);
            }
            if(strstr($websitetitel,"{CATEGORY|")) {
                global $syntax;
                preg_match_all("/\{(CATEGORY|PAGE)\|(.*)\}/U",$websitetitel,$matches);
                $cat = false;
                $page = false;
                foreach($matches[1] as $pos => $cat_page) {
                    $cat_page = trim($cat_page);
                    if($cat_page == "CATEGORY")
                        $cat = $matches[2][$pos];
                    if($cat_page == "PAGE")
                        $page = $matches[2][$pos];
                }
                $websitetitel = $syntax->getWebsiteTitle($cat,$page);
            }
            $template = str_replace("{WEBSITE_TITLE}",$websitetitel,$template);
        }
        # Die Suche oder Ausgabe ist in einer Virtuellen Inhaltseite
        if($value == "plugin_first" and $this->settings->get("searchart") != "page") {
            global $CatPage;
            $CatPage->make_DummyCat($dummy_cat);
            # für die Detailmenu ausgabe denn link setzen
            $CatPage->ActionLinksearch = $CatPage->get_Href($dummy_cat,false);
            # für Detailmenu und websitetitel denn ausgabe Text überschreiben
            if($this->settings->get("detailmenutext")) {
                $detailmenutext = str_replace("{SEARCH_WORDS}","{PARAM1}",$this->settings->get("detailmenutext"));
                $language->LANG_CONF->set("message_searchresult_1",$detailmenutext);
            }
#echo $_SERVER['REQUEST_URI']."<br />\n";
#echo $CatPage->get_Href($dummy_cat,false)."<br />\n";
            # wir suchen was oder haben auf einen such link geklickt
            if(strstr($_SERVER['REQUEST_URI'],$CatPage->get_Href($dummy_cat,false))) {
                global $pagecontent;
                define("ACTION_CONTENT",false);
                define("ACTION_REQUEST","search");
                $pagecontent = "";
                $this->setTmpl();
                if($this->settings->get("searchart") == "searchlink")
                    $pagecontent = "{SEARCH|".$this->tmpl_complet."}";
                if($this->settings->get("searchart") == "search")
                    $pagecontent = "{SEARCH|".$this->tmpl_result."}";
            }
            return;
        }

        # $value = false weil der Platzhalter {SEARCH} ist (ohne Parameter)
        if($value === false) {
            global $CatPage;
            global $SEARCH_REQUEST;
            # für die Vituelle Inhaltseite {SEARCH} durch den Link ersetzen
            if($this->settings->get("searchart") == "searchlink") {
                $serach_query = false;
                if(strlen($SEARCH_REQUEST) > 0)
                    $serach_query = "search=".$SEARCH_REQUEST;
                $linktext = $CatPage->get_HrefText($dummy_cat,false);
                if($this->settings->get("dummycattext"))
                    $linktext = $this->settings->get("dummycattext");
                return $CatPage->create_LinkTag($CatPage->get_Href($dummy_cat,false,$serach_query)
                                            ,$linktext
                                            ,"searchlink"
                                            ,$CatPage->get_HrefText($dummy_cat,false));
            }
            # für die Vituelle Inhaltseite {SEARCH} durch {SEARCH|fild} = Suchfeld
            if($this->settings->get("searchart") == "search") {
                $this->setTmpl();
                return "{SEARCH|".$this->tmpl_fild."}";
            }
            # für die Inhaltseite {SEARCH} durch {SEARCH|fild} = Suchfeld und {SEARCH|result} ersetzen
            if($this->settings->get("searchart") == "page") {
                $this->setTmpl();
                return "{SEARCH|".$this->tmpl_complet."}";
            }
            # es wurde noch keine searchart definiert
            return NULL;
        }

        # damit die SearchClass nur eingebunden wird wenn sie brauchen
        if($value !== false) {
            require_once(BASE_DIR_CMS."SearchClass.php");
            $search = new SearchClass("SEARCH");
            $search->hidecatnamedpages = $this->settings->get("hidecatnamedpages");
            $search->showhiddenpagesinsearch = $this->settings->get("showhiddenpagesinsearch");
        }
        # Ausgabe des Suchfeldes
        if(strstr($value,"SEARCH_TITLE") or strstr($value,"SEARCH_FILD")) {
            if($this->settings->get("searchart") == "page")
                $tmpl_replace['serach_fild'] = $search->getUserSearchForm(CAT_REQUEST,PAGE_REQUEST,"search","default");
            else
                $tmpl_replace['serach_fild'] =  $search->getUserSearchForm($dummy_cat,false,"search","default");
        }

        # Ausgabe der Such ergebnisse
        if(strstr($value,"RESULT") or strstr($value,"RESULT_TITLE")) {
            $add_search_query = false;
            # wir haben searchfeld im template
            if($this->settings->get("searchart") == "search") {
                $add_search_query = true;
                $tmpl_replace['search_title'] = "";
            }
            # wir haben nur einen such link ohne cat
            if($this->settings->get("searchart") == "searchlink") {
                $add_search_query = true;
            }
            if(getRequestValue('search') !== false) {
                if(getRequestValue('search') != "") {
                    $findpages = $search->getFindPageArray();
                    if(count($findpages) < 1) {
                        $tmpl_replace['result'] = $this->settings->get("resultnofind");
                        $tmpl_replace['result_title'] = $this->settings->get("resulttitlenofind");
                    } else {
                        $tmpl_replace['result'] = $search->getFindPagesMenuList($findpages,$add_search_query);
                        $tmpl_replace['result_title'] = $this->settings->get("resulttitlefind");
                    }
                } else {
                    $tmpl_replace['result'] = $this->settings->get("resultnowords");
                    $tmpl_replace['result_title'] = $this->settings->get("resulttitlenowords");
                }
            }
        }
        return $this->getSearchContent($tmpl_replace);
    } // function getContent

    function getSearchContent($tmpl_replace) {
        # ,"{HELP_LINK}"
        $tmpl_search = array("{RESULT}","{SEARCH_TITLE}","{SEARCH_FILD}","{RESULT_TITLE}","{HELP_TEXT}");
        global $specialchars;
        global $SEARCH_REQUEST;
        $searchwords = $specialchars->rebuildSpecialChars($SEARCH_REQUEST, false, false);
        $tmpl_replace = array_values($tmpl_replace);
        $tmpl = str_replace($tmpl_search,$tmpl_replace,$this->tmpl);
        $tmpl = str_replace("{SEARCH_WORDS}",$searchwords,$tmpl);
        return $tmpl;
    }

    function setTmpl() {
        $tmp_array = explode("}{",substr($this->tmpl,1,-1));
        foreach($tmp_array as $place) {
            $this->tmpl_complet[] = $place;
            if(strstr($place,"SEARCH"))
                $this->tmpl_fild[] = $place;
            if(strstr($place,"RESULT"))
                $this->tmpl_result[] = $place;
        }
        $this->tmpl_fild = implode(",",$this->tmpl_fild);
        $this->tmpl_result = implode(",",$this->tmpl_result);
        $this->tmpl_complet = implode(",",$this->tmpl_complet);
    }

    function getConfig() {
        global $ADMIN_CONF;
        $language = $ADMIN_CONF->get("language");
        # das ist ein Plugin was in der index.php als erstes aufgerufen werden muss
        # deshalb erzwingen wir den conf parameter
        if(IS_ADMIN and $this->settings->get("plugin_first") !== "true") {
            $this->settings->set("plugin_first","true");
        }

        $padding_left = "1em";
        $config['deDE']['searchart'] = array(
            "type" => "radio",
            "description" => "<b>Art der Ausgabe:</b>",
            "descriptions" => array(
                "search" => "Ich möchte ein Suchfeld und die Ausgabe ist in einer Virtuelen Inhaltseite",
                "searchlink" => "Ich möchte ein Suchlink und die Ausgabe ist in einer Virtuelen Inhaltseite",
                "page" => "Ich habe eine normale Inhaltseite mit dem Pluginplatzhalter"
                ),
            "template" => '{searchart_description}<table cellspacing="0" border="0" cellpadding="0" style="margin-left:1.5em;"><tr><td>{searchart_descriptions_search}</td><td style="padding-left:'.$padding_left.';">{searchart_radio_search}</td></tr><tr><td>{searchart_descriptions_searchlink}</td><td style="padding-left:'.$padding_left.';">{searchart_radio_searchlink}</td></tr><tr><td>{searchart_descriptions_page}</td><td style="padding-left:'.$padding_left.';">{searchart_radio_page}</td></tr></table>',
        );

        $config['deDE']['showhiddenpagesinsearch'] = array(
            "type" => "checkbox",
            "description" => "Versteckte Inhaltseiten in der Ergebnisliste zeigen"
        );
        $config['deDE']['hidecatnamedpages'] = array(
            "type" => "checkbox",
            "description" => "Möchtest du die Kategorie in der Ergebnisliste als Link haben wenn eine gefundene Inhaltseite so heist wie die Kategorie"
        );
#$search->hidecatnamedpages = $this->settings->->get("hidecatnamedpages");
#$search->showhiddenpagesinsearch = $this->settings->->get("showhiddenpagesinsearch");
        # virtuelle Kategorie
        $config['deDE']['dummycat']  = array(
            "type" => "text",
            "description" => 'Virtuelle Kategorie und Link Text. Info wird auch für den Websitetitle benutzt',
            "maxlength" => "255",
        );
        $config['deDE']['dummycattext']  = array(
            "type" => "text",
            "description" => 'Optinaler Link Text für die Virtuelle Kategorie',
            "maxlength" => "255",
        );
        $padding_bottom = ".5em";
        $config['deDE']['detailmenutext']  = array(
            "type" => "text",
            "description" => "Text der anstelle des Detailmenu erscheinen soll. Info wird auch für den Websitetitle benutzt. Erlaubte Platzhalter <b>{SEARCH_WORDS}</b>",
            "maxlength" => "300",
            "template" => '<div style="padding-bottom:'.$padding_bottom.';">{detailmenutext_description}</div>{detailmenutext_text}',
        );
        $config['deDE']['websitetitel']  = array(
            "type" => "text",
            "description" => 'Eigenen Websitetitle benutzt.<br />Möglichkeiten:<ol><li>Text der als Websitetitle benutzt wird.</li><li><b>{CATEGORY|</b> Dieser Text wird benutzt für "{CATEGORY}"<b>}</b> und Optional <b>{PAGE|</b> Dieser Text wird benutzt für "{PAGE}"<b>}</b><br />siehe Admin->Einstelungen Titelbarformat.</li></ol>Erlaubte Platzhalter "<b>{SEARCH_WORDS}</b>"',
            "maxlength" => "300",
            "template" => '<div style="padding-bottom:'.$padding_bottom.';">{websitetitel_description}</div>{websitetitel_text}',
        );
        $td_width = "35%";
        $config['deDE']['serachtitle']  = array(
            "type" => "textarea",
            "description" => "Titel für das Suchfeld",
            "rows" => "3",
            "template" => '<table width="100%" cellspacing="0" border="0" cellpadding="0"><tr><td width="'.$td_width.'" valign="top">{serachtitle_description}</td><td>{serachtitle_textarea}</td></tr></table>'
        );

        $config['deDE']['resulttitlefind']  = array(
            "type" => "textarea",
            "description" => "Titel Suchergebniss Gefunden",
            "rows" => "3",
            "template" => '<table width="100%" cellspacing="0" border="0" cellpadding="0"><tr><td width="'.$td_width.'" valign="top">{resulttitlefind_description}</td><td>{resulttitlefind_textarea}</td></tr></table>'
        );
        $config['deDE']['resulttitlenofind']  = array(
            "type" => "textarea",
            "description" => "Titel Suchergebniss nichts Gefunden",
            "rows" => "3",
            "template" => '<table width="100%" cellspacing="0" border="0" cellpadding="0"><tr><td width="'.$td_width.'" valign="top">{resulttitlenofind_description}</td><td>{resulttitlenofind_textarea}</td></tr></table>'
        );
        $config['deDE']['resulttitlenowords']  = array(
            "type" => "textarea",
            "description" => "Titel Suchergebniss keine Suchbegriffe",
            "rows" => "3",
            "template" => '<table width="100%" cellspacing="0" border="0" cellpadding="0"><tr><td width="'.$td_width.'" valign="top">{resulttitlenowords_description}</td><td>{resulttitlenowords_textarea}</td></tr></table>'
        );
        $config['deDE']['resultnofind']  = array(
            "type" => "textarea",
            "description" => "Suchergebnis Text nichts gefunden",
            "rows" => "3",
            "template" => '<table width="100%" cellspacing="0" border="0" cellpadding="0"><tr><td width="'.$td_width.'" valign="top">{resultnofind_description}</td><td>{resultnofind_textarea}</td></tr></table>'
        );
        $config['deDE']['resultnowords']  = array(
            "type" => "textarea",
            "description" => "Suchergebnis Text keine Suchbegriffe",
            "rows" => "3",
            "template" => '<table width="100%" cellspacing="0" border="0" cellpadding="0"><tr><td width="'.$td_width.'" valign="top">{resultnowords_description}</td><td>{resultnowords_textarea}</td></tr></table>'
        );
/*        $config['deDE']['serachhelp']  = array(
            "type" => "text",
            "description" => "Link Text für Suchhilfe",
            "maxlength" => "500",
        );*/
        $config['deDE']['serachhelptext'] = array(
            "type" => "textarea",
#            "cols" => "90",
            "rows" => "7",
            "description" => "Der Hilfetext füt den Platzhalter <b>{HELP_TEXT}</b>",
            "template" => '<div style="padding-bottom:'.$padding_bottom.';">{serachhelptext_description}</div>{serachhelptext_textarea}',
        );
        $config['deDE']['template']  = array(
            "type" => "text",
            "description" => 'Template Anpassung.<br />
            Das sind die möglichen Platzhalter: <b>Achtung:</b> Nur diese sind erlaubt sonst nichts<br />
            &nbsp;&nbsp;&nbsp;&nbsp;<b>{SEARCH_TITLE}</b> = Titel über dem Suchfeld.<br />
            &nbsp;&nbsp;&nbsp;&nbsp;<b>{SEARCH_FILD}</b> = Das Suchfeld.<br />
            &nbsp;&nbsp;&nbsp;&nbsp;<b>{RESULT_TITLE}</b> = Titel über dem Suchergebnis.<br />
            &nbsp;&nbsp;&nbsp;&nbsp;<b>{RESULT}</b> = Das Suchergebnis oder der Text aus Suchergebnis nichts gefunden/keine Suchbegriffe.<br />
            Default ist: "<b>{SEARCH_TITLE} {SEARCH_FILD} {RESULT_TITLE} {RESULT}</b>"<br />',
            "maxlength" => "500",
            "template" => '<div style="padding-bottom:'.$padding_bottom.';">{template_description}</div>{template_text}',
        );

        if(isset($config[$language])) {
            return $config[$language];
        } else {
            return $config['deDE'];
        }
    } // function getConfig    

    function getInfo() {
        global $ADMIN_CONF;
        $language = $ADMIN_CONF->get("language");

        $info['deDE'] = array(
            // Plugin-Name
            "<b>SEARCH</b> in Inhaltseiten Suchen Revision: 1",
            // Plugin-Version
            "2.0",
            // Kurzbeschreibung
            'Das einzige was du Machen must ist den Pluginplatzhalter irgendwo einzufügen und die <b>Art der Ausgabe:</b> einzustellen<br />
            <br />
            Diese Platzhalter kanst du in der Konfiguration fast überall benutzen<br />
            <b>{SEARCH_WORDS}</b> = Die Suchbegriffe<br />
            <b>{HELP_TEXT}</b> = Der Hilfe Text<br />
            <br />
            Auserdem sind Syntaxelemente und HTML Tags erlaubt da wo es sin macht

',
            // Name des Autors
            "stefanbe",
            // Download-URL
            "",
            array("{SEARCH}" => "Suchen Achtung Plugin Konfiguration Beachten")
            );

        if(isset($info[$language])) {
            return $info[$language];
        } else {
            return $info['deDE'];
        }
    } // function getInfo



} // class DEMOPLUGIN

?>