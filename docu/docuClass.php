<?php

define("BASE_DIR_DOCU",BASE_DIR.DOCU_DIR_NAME."/");
define("BASE_URL_DOCU",URL_BASE.DOCU_DIR_NAME."/");

class moziloDocuClass {

    public $dialog;
    public $artikel = false;
    public $subartikel = false;
    public $docu_artikel;
    public $curent_lang;
    public $docu_writer = false;
#    public $docu_writer = "docu_help";
    public $docu_lang = array();
    public $docu_error = false;
    public $isplugin = false;
    public $menu = array(
#                    "install" => false,
                    "start" => false,
                    "home" => false,
                    "catpage" => false,
                    "files" => false,
                    "gallery" => false,
                    "config" => false,
                    "admin" => false,
                    "plugins" => false,
                    "template" => false,
                    "editsite" => array(
                                        "syntax" => false,
                                        "rule" => false,
                                        "editusersyntax" => false
                                    )
                    );

    function __construct($only_docu = false) {
        if($this->docu_writer)
            $this->menu[$this->docu_writer] = false;

        $this->curent_lang = 'deDE';
        if(isset($_GET["lang"])) {
            $this->curent_lang = $this->cleanGet("lang");
        }

        $this->dialog = false;
        if(isset($_GET["menu"]) and $_GET["menu"] == "false") {
            $this->dialog = "&amp;menu=false";
        }

        $this->artikel = "start";
        if(isset($_GET["artikel"])) {
            $this->artikel = $this->cleanGet("artikel");
        }

        $this->subartikel = "";
        if(isset($_GET["subartikel"])) {
            $this->subartikel = $this->cleanGet("subartikel");
        }

        $this->makeLanguageAsArray($only_docu);

    }

    function makeDocuArtikel() {

        $this->docu_artikel = "";
        if($this->docu_error !== false)
            return $this->docu_error;

        if(!isset($this->menu[$this->artikel]))
            $this->artikel = "start";

        if(!isset($this->menu[$this->artikel][$this->subartikel]))
            $this->subartikel = "";

        $subartikel = $this->subartikel;
        if($subartikel)
            $subartikel = "_".$this->subartikel;
        if(is_readable(BASE_DIR_DOCU.$this->curent_lang."/".$this->artikel.$subartikel.".html"))
            $this->docu_artikel = @file_get_contents(BASE_DIR_DOCU.$this->curent_lang."/".$this->artikel.$subartikel.".html");
        else
            $this->docu_artikel = "Fatal Error Can't read file: ".$this->curent_lang."/".$this->artikel.$subartikel.".html";

        if($this->isplugin or !$this->dialog)
            $this->docu_artikel = preg_replace("/\<!--dialog_start-->(.*)\<!--dialog_end-->/Umsi","",$this->docu_artikel);
        if($this->dialog)
            $this->docu_artikel = preg_replace("/\<!--no_dialog_start-->(.*)\<!--no_dialog_end-->/Umsi","",$this->docu_artikel);

        $this->makeDocuHelp();

        $this->makeModulPlacholder();
        $this->makeImgPlacholder();
        $this->makeIconsExtPlacholder();
        $this->makeMoImgPlacholder();
        $this->makeEdImgPlacholder();
        $this->makeLanguagePlacholder();

        $replace = array();
        $replace[0] = array("{ICONS_CLEAR}","{BASE_URL_DOCU}");
        $replace[1] = array(BASE_URL_DOCU.'admin/gfx/clear.gif',BASE_URL_DOCU);
        $this->docu_artikel = str_replace($replace[0],$replace[1],$this->docu_artikel);

        $this->makeDocuLinks();

#!!!!!! Braucht das die normale Docu?
#        if($this->docu_writer)
            $this->docu_artikel .= '<script type="text/javascript" src="'.BASE_URL_DOCU.'jquery/toggle_jquery.js"></script>'
                .'<script type="text/javascript" src="'.BASE_URL_DOCU.'jquery/docu.js"></script>';

        return $this->docu_error;
    }

    function getDocuHead() {
        $html = '<link type="image/x-icon" rel="SHORTCUT ICON" href="'.BASE_URL_DOCU.'admin/favicon.ico" />';

        $html .= '<link type="text/css" rel="stylesheet" href="'.BASE_URL_DOCU.'admin/css/mozilo/jquery-ui-1.9.2.custom.css" />';
        $html .= '<link type="text/css" rel="stylesheet" href="'.BASE_URL_DOCU.'admin/jquery/File-Upload/bootstrap.cms.css" />';

#!!!!!!!! test
        $html .= '<link type="text/css" rel="stylesheet" href="'.BASE_URL_DOCU.'admin/admin.css" />';
        $html .= '<link type="text/css" rel="stylesheet" href="'.BASE_URL_DOCU.'admin/jquery/File-Upload/jquery.fileupload-ui.css" />';
        $html .= '<link type="text/css" rel="stylesheet" href="'.BASE_URL_DOCU.'admin/editsite.css" />';
        $html .= '<link type="text/css" rel="stylesheet" href="'.BASE_URL_DOCU.'admin/jquery/coloredit/coloredit.css" />';
        $html .= '<link type="text/css" rel="stylesheet" href="'.BASE_URL_DOCU.'css/from_ace.css" />';
        $html .= '<link type="text/css" rel="stylesheet" href="'.BASE_URL_DOCU.'admin/jquery/ui-multiselect-widget/jquery.multiselect.css" />';
        $html .= '<link type="text/css" rel="stylesheet" href="'.BASE_URL_DOCU.'admin/jquery/ui-multiselect-widget/jquery.multiselect.filter.css" />';


        $html .= '<link type="text/css" rel="stylesheet" href="'.BASE_URL_DOCU.'css/docu.css" />';
        $html .= '<link type="text/css" rel="stylesheet" href="'.BASE_URL_DOCU.'css/change_admin.css" />';
        $html .= '<link type="text/css" rel="stylesheet" href="'.BASE_URL_DOCU.'css/arrow/arrow.css" />';
        $html .= '<link type="text/css" rel="stylesheet" href="'.BASE_URL_DOCU.'css/mo_icons/mo_icons_tabs.css" />';

        if($this->isplugin) {
            global $syntax;
            $syntax->insert_jquery_in_head('jquery');
        } else
            $html .= '<script type="text/javascript" src="'.BASE_URL_DOCU.'jquery/jquery-1.7.2.min.js"></script>';
#        $css = ' do-body-nodialog';
        $titel_artikel = "";
        $titel_subartikel = "";
        $var_dialog = "false";
        if($this->dialog) {
            $var_dialog = "true";

            $titel_artikel = " → ".$this->getDocuLanguage("domenu_".$this->artikel);
            if(strlen($this->subartikel) > 1)
                $titel_subartikel = " → ".$this->getDocuLanguage("domenu_".$this->artikel."_".$this->subartikel);
        }
        if($this->isplugin)
            $var_dialog = "false";
        $html .= '<script type="text/javascript">'
            .'var dialog = '.$var_dialog.';'
            .'var titel_artikel = "'.$titel_artikel.'";'
            .'var titel_subartikel = "'.$titel_subartikel.'";'
            .'var in_text = "'.$this->getDocuLanguage("do_toggle_in_text").'";'
            .'var out_text = "'.$this->getDocuLanguage("do_toggle_out_text").'";'
        .'</script>';
        return $html;
    }

    function cleanGet($key) {
        if(strpos("tmp".$_GET[$key], "\x00") > 0) die();
        $tmp = trim($_GET[$key], ".\x00..\x20");
        return preg_replace('/[^a-zA-Z0-9._-]/', "",$tmp);
    }

    function replacePlacholder($dir,$art,$filetype,$css = false) {
        preg_match_all('/\{'.$art.'([\w\-]+)\}/',$this->docu_artikel,$match);
        $url = str_replace(BASE_DIR_DOCU,BASE_URL_DOCU,$dir);
        foreach($match[0] as $pos => $search) {
            $find = false;
            $modul = "&#123;".$art.$match[1][$pos]."&#125;";
            foreach($filetype as $ext) {
                if(is_file($dir.$match[1][$pos].$ext)) {
                    if(!$css)
                        $modul = file_get_contents($dir.$match[1][$pos].$ext);
                    else
                        $modul = '<img class="do-'.$css.'" src="'.$url.$match[1][$pos].$ext.'" alt="" />';
                    break;
                } elseif($css)
                    $modul = "{".$art.$match[1][$pos]."}";
            }
            $this->docu_artikel = str_replace($search,$modul,$this->docu_artikel);
        }
    }

    function makeModulPlacholder() {
        $this->replacePlacholder(BASE_DIR_DOCU."module/","MODUL_",array(".html"));
        if(preg_match('/\{MODUL_([\w\-]+)\}/',$this->docu_artikel))
            $this->makeModulPlacholder();
        return;
    }

    function makeImgPlacholder() {
        $this->replacePlacholder(BASE_DIR_DOCU."img/","IMG_",array(".png",".jpg",".gif"),"img");
    }

    function makeMoImgPlacholder($get_array = false) {
        $dateien = array();
        foreach(array("add-file","blank","copy","delete","delete-full","edit","error","docu","help","img-scale","info","information","logout","move","page-edit","pw","save","stop","warning","website","website-small","work") as $icon) {
            $dateien['{MO_ICON_'.$icon.'}'] = '<img class="mo-icons-icon mo-icons-'.$icon.'" src="'.BASE_URL_DOCU.'admin/gfx/clear.gif" alt="" />';
        }
        foreach(array("ajax-loader.gif","menu.png","login.png") as $file) {
            $ext = strtolower(substr($file,strrpos($file,".")));
            $dateien['{MO_ICON_'.str_replace($ext,"",$file).'}'] = '<img class="mo-icons-file" src="'.BASE_URL_DOCU.'admin/gfx/'.$file.'" alt="" />';
        }

        $dateien['{MO_ICON_'.'dialog_close_button'.'}'] = '<img class="mo-icons-file" src="'.BASE_URL_DOCU.'css/mo_icons/dialog_close_button.png" alt="" />';

        $dateien['{MO_ICON_'.'editor_cursor'.'}'] = '<img class="mo-icons-editor-cursor" src="'.BASE_URL_DOCU.'css/mo_icons/editor_cursor.gif" alt="" />';

        $dateien['{MO_ICON_'.'editor_cursor_noblink'.'}'] = '<img class="mo-icons-editor-cursor" src="'.BASE_URL_DOCU.'css/mo_icons/editor_cursor_noblink.png" alt="" />';

        if($get_array) {
            ksort($dateien);
            return $dateien;
        }
        $this->docu_artikel = str_replace(array_keys($dateien),array_values($dateien),$this->docu_artikel);
    }

    function makeEdImgPlacholder($get_array = false) {
        $dateien = array();
        foreach(array("link","mail","seite","kategorie","datei","bild","bildlinks","bildrechts","absatz","liste","numliste","tabelle","linie","html","include","ueber1","ueber2","ueber3","links","zentriert","block","rechts","fett","kursiv","unter","durch","fontsize","farbe","farbeedit") as $icon) {
            $titel = $icon;
            if($icon == "linie")
                $titel = "----";
            $dateien['{ED_ICON_'.$icon.'}'] = '<img class="ed-syntax-icon ed-icon-border ed-'.$icon.'" src="'.BASE_URL_DOCU.'admin/gfx/clear.gif" alt="" title="&#091;'.$titel.'&#124;...&#093;" />';
        }
        foreach(array("undo","redo","expand","find","replace","number","noprint") as $icon) {
            $dateien['{ED_ICON_'.$icon.'}'] = '<img style="border-color:transparent;" class="ed-ace-icon ed-syntax-icon ed-icon-border ed-'.$icon.'" src="'.BASE_URL_DOCU.'admin/gfx/clear.gif" alt="" />';
        }
        $dateien['{ED_ICON_number_active}'] = '<img style="border-color:transparent;" class="ed-ace-icon ed-syntax-icon ed-icon-border ed-number ed-ace-icon-active" src="'.BASE_URL_DOCU.'admin/gfx/clear.gif" alt="" />';
        $dateien['{ED_ICON_noprint_active}'] = '<img style="border-color:transparent;" class="ed-ace-icon ed-syntax-icon ed-icon-border ed-noprint ed-ace-icon-active" src="'.BASE_URL_DOCU.'admin/gfx/clear.gif" alt="" />';

        if($get_array)
            return $dateien;
        $this->docu_artikel = str_replace(array_keys($dateien),array_values($dateien),$this->docu_artikel);
    }

    function makeIconsExtPlacholder($get_array = false) {
        $dateien = array();
        foreach(array("doc","img","iso","mov","none","pdf","txt","wav","zip") as $icon) {
            $dateien['{EX_ICON_'.$icon.'}'] = '<img class="fu-ext-imgs fu-ext-'.$icon.'" src="'.BASE_URL_DOCU.'admin/gfx/clear.gif" alt="" />';
        }
        if($get_array)
            return $dateien;
        $this->docu_artikel = str_replace(array_keys($dateien),array_values($dateien),$this->docu_artikel);
    }

    function makeDocuLinks() {
        if($this->isplugin)
            global $CatPage;

        preg_match_all('/\{INTERNAL_LINK\|([a-zA-Z]+)(\:[a-zA-Z]+)*(\#[a-zA-Z0-9\-_]+)*\}/U',$this->docu_artikel,$match);

        foreach($match[0] as $pos => $search) {
            $link_add = "";
            $cat = $this->isplugin;
            $page = false;
            if(!isset($this->menu[$match[1][$pos]]))
                continue;
            $link_text = $this->getDocuLanguage("domenu_".$match[1][$pos]);
            $link = "index.php?artikel=".$match[1][$pos];
            if($this->isplugin)
                $page = $CatPage->get_AsKeyName($this->getDocuLanguage("domenu_".$match[1][$pos]),true);
            if($match[2][$pos] and $match[2][$pos][0] == ":") {
                $link_text .= " → ".$this->getDocuLanguage("domenu_".$match[1][$pos]."_".substr($match[2][$pos],1));
                $link .= '&amp;subartikel='.substr($match[2][$pos],1);
                if($this->isplugin) {
                    $cat .= "%2F".$page;
                    $page = $CatPage->get_AsKeyName($this->getDocuLanguage("domenu_".$match[1][$pos]."_".substr($match[2][$pos],1)),true);
                }
            }
            $link_add = '&amp;lang='.$this->curent_lang.$this->dialog;
            if(isset($match[3][$pos]) and $match[3][$pos])
                $link_add .= $match[3][$pos];
            if($this->isplugin) {
                if(is_array($this->menu[$match[1][$pos]]) and !$match[2][$pos])
                    $cat .= "%2F".$page;
                $this->docu_artikel = str_replace($search,'<a href="'.$CatPage->get_Href($cat,$page,$link_add).'">'.$link_text.' &gt;</a>',$this->docu_artikel);
            } else
                $this->docu_artikel = str_replace($search,'<a href="'.$link.$link_add.'">'.$link_text.' &gt;</a>',$this->docu_artikel);

        }
    }

    function makeLanguagePlacholder($get_array = false) {
        if($get_array)
            return $this->docu_lang;

        $this->docu_artikel = str_replace(array_keys($this->docu_lang),array_values($this->docu_lang),$this->docu_artikel);
        $this->docu_artikel = str_replace("{TemplateName}","",$this->docu_artikel);
    }

    function getDocuLanguage($value) {
        $value = '{'.strtoupper($value).'}';
        if(isset($this->docu_lang[$value]))
            return $this->docu_lang[$value];
        return false;
    }

    function makeLanguageAsArray($only_docu = false) {
        if(!$only_docu) {
            $admin_lang_file = BASE_DIR_DOCU."admin/sprachen/language_".$this->curent_lang.".txt";
            $this->docu_lang = array();
            $this->docu_lang["{DOCU_LANG}"] = $this->curent_lang;
            if(is_readable($admin_lang_file) and is_array(($lines = @file($admin_lang_file))))
                $this->helpLanguageAsArray($lines);
        }
        $admin_lang_file = BASE_DIR_DOCU."language/docu_".$this->curent_lang.".txt";
        if(is_readable($admin_lang_file) and is_array(($lines = @file($admin_lang_file))))
            $this->helpLanguageAsArray($lines);
    }

    function helpLanguageAsArray($lines) {
        foreach($lines as $line) {
            // comments
            if(preg_match("/^#/",$line) or preg_match("/^\s*$/",$line) or preg_match("/^<?php$/",$line) or preg_match("/^install_/",$line))
                continue;
            if(preg_match("/^([^=]*)=(.*)/",$line,$matches))
                $this->docu_lang["{".strtoupper(trim($matches[1]))."}"] = trim($matches[2]);
        }
    }

    function makeSubMenu() {
# makeSubMenu wird nicht vom Plugin Aufgerufen $this->isplugin kann raus?
#        if($this->isplugin or ($this->dialog and !is_array($this->menu[$this->artikel])))
        if($this->dialog and !is_array($this->menu[$this->artikel]))
            return "";

        $submenu = '<ul class="mo-menu-tabs ui-tabs-nav ui-helper-reset ui-helper-clearfix ui-widget-header ui-corner-top">';

        $cssaktiv = " mo-ui-state-hover";
        if(strlen($this->subartikel) < 1)
            $cssaktiv = " ui-tabs-selected ui-state-active";
        $submenu .= '<li class="ui-state-default ui-corner-top'.$cssaktiv.'">'.$this->makeDocuLink('<b>'.$this->getDocuLanguage("domenu_".$this->artikel).'</b>',$this->artikel).'</li>';

        if(isset($this->menu[$this->artikel]) and is_array($this->menu[$this->artikel])) {
            foreach($this->menu[$this->artikel] as $subnav => $tmp) {
                $cssaktiv = " mo-ui-state-hover";
                if($this->subartikel == $subnav)
                    $cssaktiv = " ui-tabs-selected ui-state-active";
                $submenu .= '<li class="ui-state-default ui-corner-top'.$cssaktiv.'">'.$this->makeDocuLink('<b>'.$this->getDocuLanguage("domenu_".$this->artikel."_".$subnav).'</b>',$this->artikel,$subnav).'</li>';
            }
        }
        $submenu .= '</ul>';
        return $submenu;
    }

    function makeDocuMenu() {
        $docmenu = '<ul class="do-menu ui-helper-reset ui-helper-clearfix">';
        foreach($this->menu as $nav => $tmp) {
            if($this->docu_writer and $nav == $this->docu_writer)
                continue;
            $cssaktiv = " mo-ui-state-hover";
            if($this->artikel == $nav)
                $cssaktiv = " ui-state-active";
            $docmenu .= '<li class="ui-state-default ui-corner-right'.$cssaktiv.'">'.$this->makeDocuLink('<b>'.$this->getDocuLanguage("domenu_".$nav).'</b>',$nav).'</li>';
        }

        $docmenu .= '</ul>';
        return $docmenu;
    }

    function makeDocuLink($text,$artikel,$subartikel = false,$language = false,$is_help_icon = false) {
        $id = "";
        $sub = "";
        $url_lang = '&amp;lang='.$this->curent_lang;
        if($language) {
            $url_lang = '&amp;lang='.$language;
        }
        if($subartikel) {
            $sub = '&amp;subartikel='.$subartikel;
        }
        if($is_help_icon)
            $id = ' id="getplaceholder"';
        return '<a href="'.BASE_URL_DOCU.DOCU_PHP.'?artikel='.$artikel.$sub.$this->dialog.$url_lang.'"'.$id.'>'.$text.'</a>';
    }

    function getLanguages() {
        $dateien = array();
        if(is_dir(BASE_DIR_DOCU) and false !== ($dir = opendir(BASE_DIR_DOCU))) {
            while(false !== ($file = readdir($dir))) {
                if(strlen($file) == 4
                        and is_dir(BASE_DIR_DOCU.$file)
                        and is_file(BASE_DIR_DOCU."language/docu_".$file.".txt")
                        and is_file(BASE_DIR_DOCU.$file."/home.html")
                        and is_file(BASE_DIR_DOCU."css/do_flags/".$file.".png")
                    ) {
                    $dateien[] = $file;
                }
            }
            closedir($dir);
        }
        sort($dateien);
        return $dateien;
    }


############################## Docu Hilfe
    function makePlacholderArray($dir,$art,$filetype,$nodir = false) {
        $dateien = array();
        if(is_dir($dir) and false !== ($currentdir = opendir($dir))) {
            $adddir = basename($dir)."/";
            if($nodir)
                $adddir = "";
            while(false !== ($file = readdir($currentdir))) {
                $ext = strtolower(substr($file,strrpos($file,".")));
                if(in_array($ext,$filetype)) {
                    $place = '{'.$art.str_replace($ext,"",$file).'}';
                    $dateien[$place] = $adddir.$file;
                }
            }
            closedir($currentdir);
        }
        ksort($dateien);
        return $dateien;
    }

    function makeDocuHelp() {
        if(!$this->docu_writer or ($this->docu_writer and $this->artikel != $this->docu_writer))
            return;

        $replace = $this->makeMoImgPlacholder(true);
        $tmp = "";
        foreach($replace as $platz => $img) {
            $tmp .= '<tr><td>'.$this->htmlPlacholder($platz).'</td><td width="1%">'.$img.'</td></tr>';
        }
        $this->docu_artikel = str_replace('{function_makeMoImgPlacholder}',$tmp,$this->docu_artikel);

        $replace = $this->makeEdImgPlacholder(true);
        $tmp = "";
        foreach($replace as $platz => $img) {
            $tmp .= '<tr><td>'.$this->htmlPlacholder($platz).'</td><td width="1%">'.$img.'</td></tr>';
        }
        $this->docu_artikel = str_replace('{function_makeEdImgPlacholder}',$tmp,$this->docu_artikel);

        $replace = $this->makeIconsExtPlacholder(true);
        $tmp = "";
        foreach($replace as $platz => $img) {
            $tmp .= '<tr><td>'.$this->htmlPlacholder($platz).'</td><td width="1%">'.$img.'</td></tr>';
        }
        $this->docu_artikel = str_replace('{function_makeIconsExtPlacholder}',$tmp,$this->docu_artikel);

        $replace = $this->makePlacholderArray(BASE_DIR_DOCU."module/","MODUL_",array(".html"),true);
        $tmp = "";
        $pos = 1;
        foreach($replace as $platz => $modul) {
            $tmp .= '<tr>'
                        .'<td class="do-docu-table-td">'.$this->htmlPlacholder($platz).'</td>'
                        .'<td class="do-docu-table-td">'.$modul.'</td>'
                        .'<td class="do-docu-table-td">'
                            .'<b id="to-b-o'.$pos.'" class="to-docu-button">[ + ]</b>'
                            .'<b id="to-b-c'.$pos.'" class="to-docu-button" style="display:none;">[ - ]</b>'
                        .'</td>'
                    .'</tr>'
                    .'<tr>'
                    .'<td colspan="3" class="do-docu-table-td-content">'
                        .'<div class="do-placholder-modul-content">'
                            .'<div id="to-docu-content'.$pos.'" class="to-docu-content">'
                                .$platz
                            .'</div>'
                        .'</div>'
                    .'</td>'
                .'</tr>';
            $pos++;
        }
        $this->docu_artikel = str_replace('{function_makeModulPlacholder}',$tmp,$this->docu_artikel);

        $replace = $this->makePlacholderArray(BASE_DIR_DOCU."img","IMG_",array(".png",".jpg",".gif"));
        $tmp = "";
        foreach($replace as $platz => $modul) {
            $tmp .= '<tr><td>'.$this->htmlPlacholder($platz).'</td><td>'.$platz.'</td></tr>';
        }
        $this->docu_artikel = str_replace('{function_makeImgPlacholder}',$tmp,$this->docu_artikel);

        $replace = $this->makeLanguagePlacholder(true);
        $tmp = "";
        foreach($replace as $platz => $modul) {
            $tmp .= '<tr><td>'.$this->htmlPlacholder($platz).'</td><td>'.$modul.'</td></tr>';
        }
        $this->docu_artikel = str_replace('{function_makeLanguagePlacholder}',$tmp,$this->docu_artikel);

        $replace = $this->testNotUsed();
        $this->docu_artikel = str_replace('{function_testNotUsed}',$replace,$this->docu_artikel);

    }

    function htmlPlacholder($platz) {
        return str_replace(array("{","}"),array("&#123;","&#125;"),$platz);
    }

    function testNotUsed() {
        $filetype = ".html";
        $replace1 = $this->makePlacholderArray(BASE_DIR_DOCU."module/","MODUL_",array(".html"));
        $replace2 = $this->makePlacholderArray(BASE_DIR_DOCU."img/","IMG_",array(".png",".jpg",".gif"));
        $test_array = array();
        $test_array = array_merge($replace1, $replace2);

        if(is_dir(BASE_DIR_DOCU.$this->curent_lang."") and false !== ($currentdir = opendir(BASE_DIR_DOCU.$this->curent_lang.""))) {
            while(false !== ($file = readdir($currentdir))) {
                $ext = strtolower(substr($file,strrpos($file,".")));
                if($ext == $filetype) {
                    $test_file = file_get_contents(BASE_DIR_DOCU.$this->curent_lang."/".$file);
                    foreach($test_array as $platz => $tmp) {
                        if(false !== strpos($test_file,$platz))
                            unset($test_array[$platz]);
                    }
                }
            }
            closedir($currentdir);
        }
        if(is_dir(BASE_DIR_DOCU."module") and false !== ($currentdir = opendir(BASE_DIR_DOCU."module"))) {
            while(false !== ($file = readdir($currentdir))) {
                $ext = strtolower(substr($file,strrpos($file,".")));
                if($ext == $filetype) {
                    $test_file = file_get_contents(BASE_DIR_DOCU."module/".$file);
                    foreach($test_array as $platz => $tmp) {
                        if(false !== strpos($test_file,$platz))
                            unset($test_array[$platz]);
                    }
                }
            }
            closedir($currentdir);
        }
        $html = '<table class="do-table" width="100%">';
        foreach($test_array as $platz => $modul) {
            $html .= '<tr><td>'.$this->htmlPlacholder($platz).'</td><td>'.$modul.'</td></tr>';
        }
        $html .= '</table>';
        return $html;
    }
}
?>