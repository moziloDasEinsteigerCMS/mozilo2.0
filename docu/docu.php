<?php
define("CHARSET","UTF-8");
define("DOCU_DIR_NAME","docu");
$URL_BASE = substr($_SERVER['SCRIPT_NAME'],0,strpos($_SERVER['SCRIPT_NAME'],DOCU_DIR_NAME."/docu.php"));
$URL_BASE = htmlentities($URL_BASE,ENT_COMPAT,CHARSET);
define("URL_BASE",$URL_BASE);
unset($URL_BASE);
# fals da bei winsystemen \\ drin sind in \ wandeln
$BASE_DIR = str_replace("\\\\", "\\",__FILE__);
# zum schluss noch den teil denn wir nicht brauchen abschneiden
$BASE_DIR = substr($BASE_DIR,0,-(strlen(DOCU_DIR_NAME."/docu.php")));
define("BASE_DIR",$BASE_DIR);
unset($BASE_DIR);

#!!!!!!!! den inhalt "editsite" userloging abhängig machen 
$array_menu_lang = array("start" => "Einführung",
                    "home" => "Info",
                    "catpage" => "Kategorien/Inhaltseiten",
                    "files" => "Dateien",
                    "gallery" => "Galerien",
                    "config" => "Einstelungen",
                    "admin" => "Admin",
                    "plugins" => "Plugins",
                    "template" => "Template",
                    "editsite" => array("Editor",
                                        "syntax" => "CMS Syntax",
                                        "color" => "Farbeändern",
                                        "smileys" => "Smileys",
                                        "selectbox" => "Selectboxen",
#                                        "plugins" => "Plugins",
                                        "editusersyntax" => "Edit Usersyntax",
                                        "template" => "Edit Template"
                                    )
                    );

$array_menu = array("start" => "",
                    "home" => "",
                    "catpage" => "",
                    "files" => "",
                    "gallery" => "",
                    "config" => "",
                    "admin" => "",
                    "plugins" => "",
                    "template" => "",
                    "editsite" => array("syntax","color","smileys","selectbox","editusersyntax","template")
                    );

#$array_tabs = array("home","catpage","files","gallery","config","admin","plugins","template");
# index
$menu = true;
if(isset($_GET["menu"]) and $_GET["menu"] == "false") {
    $menu = false;
}

$artikel = "start";
if(isset($_GET["artikel"])) {
    $artikel = cleanGet("artikel");
}

$subartikel = "";
if(isset($_GET["subartikel"])) {
    $subartikel = "_".cleanGet("subartikel");
}

$html = getDocuHead();


#    $array_tabs = array("home","catpage","files","gallery","config","admin","plugins","template");

$docu_artikel = false;
if(file_exists(BASE_DIR.DOCU_DIR_NAME."/".$artikel.$subartikel.".html")) {
    if(false === ($docu_artikel = file_get_contents(BASE_DIR.DOCU_DIR_NAME."/".$artikel.$subartikel.".html")))
        die("Fatal Error Can't read file: ".$artikel.$subartikel);
} elseif($artikel == "test")
    $docu_artikel = "";
$subnav = "";
if($docu_artikel === false)
    $docu_artikel = 'Der Artikel "'.$artikel.$subartikel.'" kommt noch';
elseif($artikel == "test")
    $docu_artikel = getPlacholder();
else {
    $subnav = makeMenu($artikel,$subartikel);
    $replace = makeModulPlacholder();
    foreach($replace[0] as $pos => $place) {
        if(strstr($docu_artikel,$replace[0][$pos])) {
            $modul = file_get_contents(BASE_DIR.DOCU_DIR_NAME."/module/".$replace[1][$pos]);
            $docu_artikel = str_replace($replace[0][$pos],$modul,$docu_artikel);
        }
    }
    $replace = makeIconsPlacholder();
    $docu_artikel = str_replace($replace[0],$replace[1],$docu_artikel);
    $replace = makeImgPlacholder();
    $docu_artikel = str_replace($replace[0],$replace[1],$docu_artikel);
    $replace = makeIconsSyntaxPlacholder();
    $docu_artikel = str_replace($replace[0],$replace[1],$docu_artikel);
    $replace = makeIconsExtPlacholder();
    $docu_artikel = str_replace($replace[0],$replace[1],$docu_artikel);
#    $docu_artikel = makeMenu($artikel).$docu_artikel;
}

if($menu) {
    $html .= '<table cellspacing="0" border="0" cellpadding="0">';
    $html .= '<tr>';
    $html .= '<td width="1%" class="do-td-menu">';
    $html .= makeDocuMenu($artikel,$subartikel);
    $html .= '</td>';
    $html .= '<td class="do-td-content">';
    $html .= '<div class="do-content">'.$docu_artikel.'</div>';
    $html .= '</td>';
    $html .= '<td>&nbsp;</td>';
    $html .= '</tr>';
    $html .= '</table>';

} else {
    if(strlen($subnav) >1)
    $html .= '<div class="do-content">'.$subnav.'<div style="padding:1em .2em 1em .2em;">'.$docu_artikel.'</div></div>';
    else
        $html .= '<div class="do-content">'.$docu_artikel.'</div>';
}
/*
hab 2 Platzhalter functionen gebaut

1. es gibt im docu ordener einen "icon" ordner da sind alle icons die wir benutzen drin
    daraus werden die Platzhalter gemacht z.B. work.png = {ICON_work}

2. es gibt im docu ordener einen "img" ordner da sind alle bilder drin die wir für die docu erstellen
    daraus werden auch die Platzhalter gemacht z.B. testbild.png = {IMG_testbild}
*/
$html .= "</body></html>";

echo $html;



function getDocuHead() {
    $html = '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" '."\n"
        .'  "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">'."\n"
        .'<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="de" lang="de">'."\n";
    $html .= "<head>";
    $html .= '<meta http-equiv="Content-Type" content="text/html; charset='.CHARSET.'" />';
    $html .= '<title>'."CMS Dokumentation".'</title>';
    $html .= '<link type="image/x-icon" rel="SHORTCUT ICON" href="'.URL_BASE.DOCU_DIR_NAME.'/favicon.ico" />';

    $html .= '<link type="text/css" rel="stylesheet" href="'.URL_BASE.'admin/css/mozilo/jquery-ui-1.8.21.custom.css" />';
    $html .= '<link type="text/css" rel="stylesheet" href="'.URL_BASE.DOCU_DIR_NAME.'/admin.css" />';
    $html .= '<link type="text/css" rel="stylesheet" href="'.URL_BASE.DOCU_DIR_NAME.'/editsite.css" />';
    $html .= '<link type="text/css" rel="stylesheet" href="'.URL_BASE.DOCU_DIR_NAME.'/docu.css" />';

#    $html .= '<script type="text/javascript" src="'.URL_BASE.'jquery/jquery-1.7.1.min.js"></script>';
#        $html .= '<script type="text/javascript" src="'.URL_BASE.DOCU_DIR_NAME.'/docu.js"></script>';
    $html .= '</head><body class="ui-widget" style="font-size:12px;">';
    return $html;

}

function cleanGet($key) {
    if(strpos("tmp".$_GET[$key], "\x00") > 0) die();
    $artikel = trim($_GET[$key], ".\x00..\x20");
    return preg_replace('/[^a-zA-Z0-9._-]/', "",$artikel);
}

function makeModulPlacholder() {
    $dateien = array();
    $placeholder = array();
    $filetype = array(".html");
    if(is_dir(BASE_DIR.DOCU_DIR_NAME."/module") and false !== ($currentdir = opendir(BASE_DIR.DOCU_DIR_NAME."/module"))) {
        while(false !== ($file = readdir($currentdir))) {
            $ext = strtolower(substr($file,strrpos($file,".")));
            if(in_array($ext,$filetype)) {
                $dateien[] = $file;
                $placeholder[] = '{MODUL_'.str_replace($ext,"",$file).'}';
            }
        }
        closedir($currentdir);
    }
    return array($placeholder,$dateien);
}

function makeIconsExtPlacholder() {
    $dateien = array();
    $placeholder = array();
    $filetype = array(".png",".jpg",".gif");
    if(is_dir(BASE_DIR.DOCU_DIR_NAME."/icons_ext") and false !== ($currentdir = opendir(BASE_DIR.DOCU_DIR_NAME."/icons_ext"))) {
        while(false !== ($file = readdir($currentdir))) {
            $ext = strtolower(substr($file,strrpos($file,".")));
            if(in_array($ext,$filetype)) {
                $dateien[] = '<img class="do-icon" src="'.URL_BASE.DOCU_DIR_NAME.'/icons_ext/'.$file.'" alt="" />';
                $placeholder[] = '{ICON_'.str_replace($ext,"",$file).'}';
            }
        }
        closedir($currentdir);
    }
    return array($placeholder,$dateien);
}

function makeIconsPlacholder() {
    $dateien = array();
    $placeholder = array();
    $filetype = array(".png",".jpg",".gif");
    if(is_dir(BASE_DIR.DOCU_DIR_NAME."/icons") and false !== ($currentdir = opendir(BASE_DIR.DOCU_DIR_NAME."/icons"))) {
        while(false !== ($file = readdir($currentdir))) {
            $ext = strtolower(substr($file,strrpos($file,".")));
            if(in_array($ext,$filetype)) {
                $dateien[] = '<img class="do-icon" src="'.URL_BASE.DOCU_DIR_NAME.'/icons/'.$file.'" alt="" />';
                $placeholder[] = '{ICON_'.str_replace($ext,"",$file).'}';
            }
        }
        closedir($currentdir);
    }
    return array($placeholder,$dateien);
}

function makeIconsSyntaxPlacholder() {
    $dateien = array();
    $placeholder = array();
    $filetype = array(".png",".jpg",".gif");
    if(is_dir(BASE_DIR.DOCU_DIR_NAME."/icons_syntax") and false !== ($currentdir = opendir(BASE_DIR.DOCU_DIR_NAME."/icons_syntax"))) {
        while(false !== ($file = readdir($currentdir))) {
            $ext = strtolower(substr($file,strrpos($file,".")));
            if(in_array($ext,$filetype)) {
                $dateien[] = '<img class="do-icon-syntax ed-syntax-icon ed-syntax-hover ui-state-active" src="'.URL_BASE.DOCU_DIR_NAME.'/icons_syntax/'.$file.'" alt="" />';
                $placeholder[] = '{ICON_'.str_replace($ext,"",$file).'}';
            }
        }
        closedir($currentdir);
    }
    return array($placeholder,$dateien);
}

function makeImgPlacholder() {
    $dateien = array();
    $placeholder = array();
    $filetype = array(".png",".jpg",".gif");
    if(is_dir(BASE_DIR.DOCU_DIR_NAME."/img") and false !== ($currentdir = opendir(BASE_DIR.DOCU_DIR_NAME."/img"))) {
        while(false !== ($file = readdir($currentdir))) {
            $ext = strtolower(substr($file,strrpos($file,".")));
            if(in_array($ext,$filetype)) {
                $dateien[] = '<img class="do-img" src="'.URL_BASE.DOCU_DIR_NAME.'/img/'.$file.'" alt="" />';
                $placeholder[] = '{IMG_'.str_replace($ext,"",$file).'}';
            }
        }
        closedir($currentdir);
    }
    return array($placeholder,$dateien);
}

function makeMenu($artikel,$subartikel) {
    global $array_menu;
    global $array_menu_lang;

    $menu = "";
    if(is_array($array_menu[$artikel]) and is_file(BASE_DIR.DOCU_DIR_NAME.'/'.$artikel.'.html')) {
        $submenu = '<div class="ui-tabs ui-widget d_ui-widget-content">';
        $submenu .= '<ul class="ui-tabs-nav ui-helper-reset ui-helper-clearfix ui-widget-header ui-corner-top">';
        $subnr = 0;
        $cssaktiv = " js-hover-default";
        if(is_file(BASE_DIR.DOCU_DIR_NAME.'/'.$artikel.'.html'))
            if(strlen($subartikel) < 1) $cssaktiv = " ui-tabs-selected ui-state-active js-no-click";
            $submenu .= '<li class="ui-state-default ui-corner-top'.$cssaktiv.'"><a href="'.URL_BASE.DOCU_DIR_NAME.'/docu.php?menu=false&amp;artikel='.$artikel.'" target="_self"><span class="mo-bold">'.$array_menu_lang[$artikel][0].'</span></a></li>';
        foreach($array_menu[$artikel] as $subnav) {
            $cssaktiv = " js-hover-default";
            if($subartikel == "_".$subnav) $cssaktiv = " ui-tabs-selected ui-state-active js-no-click";
            if(!is_file(BASE_DIR.DOCU_DIR_NAME.'/'.$artikel.'_'.$subnav.'.html')) continue;
            $submenu .= '<li class="ui-state-default ui-corner-top'.$cssaktiv.'"><a href="'.URL_BASE.DOCU_DIR_NAME.'/docu.php?menu=false&amp;artikel='.$artikel.'&amp;subartikel='.$subnav.'" target="_self"><span class="mo-bold">'.$array_menu_lang[$artikel][$subnav].'</span></a></li>';
            $subnr++;
        }
        $submenu .= '</ul>';
        $submenu .= '</div>';
        if($subnr > 0)
            $menu .= $submenu;
    }
    return $menu;

}

function makeDocuMenu($artikel,$subartikel) {
    global $array_menu;
    global $array_menu_lang;

    $menu = '<ul class="do-menu">';
    foreach($array_menu as $nav => $sub) {
        if(!is_file(BASE_DIR.DOCU_DIR_NAME.'/'.$nav.'.html')) continue;
        $cssaktiv = "";
        if($artikel == $nav) $cssaktiv = " do-link-aktiv";
        if(is_array($sub))
            $menu .= '<li><a class="do-menu-link'.$cssaktiv.'" href="'.URL_BASE.DOCU_DIR_NAME.'/docu.php?artikel='.$nav.'" target="_self">'.$array_menu_lang[$nav][0].'</a>';
        else
            $menu .= '<li><a class="do-menu-link'.$cssaktiv.'" href="'.URL_BASE.DOCU_DIR_NAME.'/docu.php?artikel='.$nav.'" target="_self">'.$array_menu_lang[$nav].'</a>';
        if(is_array($sub)) {
            $submenu = '<ul>';
            $subnr = 0;
            foreach($sub as $subnav) {
                if(!is_file(BASE_DIR.DOCU_DIR_NAME.'/'.$nav.'_'.$subnav.'.html')) continue;
                $cssaktiv = "";
                if($subartikel == "_".$subnav) $cssaktiv = " do-link-aktiv";
                $submenu .= '<li><a class="do-menu-link'.$cssaktiv.'" href="'.URL_BASE.DOCU_DIR_NAME.'/docu.php?artikel='.$nav.'&amp;subartikel='.$subnav.'" target="_self">'.$array_menu_lang[$nav][$subnav].'</a></li>';
                $subnr++;
            }
            $submenu .= '</ul>';
            if($subnr > 0)
                $menu .= $submenu;
        }
        $menu .= '</li>';

    }
    $cssaktiv = "";
    if($artikel == "test") $cssaktiv = " do-link-aktiv";
    $menu .= '<li><a class="do-menu-link'.$cssaktiv.'" href="'.URL_BASE.DOCU_DIR_NAME.'/docu.php?artikel=test" target="_self">Docu Platzhalter</a></li>';
    $menu .= '</ul>';
    return $menu;
}

function getPlacholder() {
    $docu_artikel = '<table class="do-test" cellspacing="0" border="0" cellpadding="0">';
    $docu_artikel .= '<tr>';
    $docu_artikel .= '<td width="25%">';
    $docu_artikel .= '<b>CMS Icons Platzhalter</b><br /><br /></td>';
    $docu_artikel .= '<td width="25%">';
    $docu_artikel .= '<b>CMS Icons Platzhalter Syntax</b><br /><br /></td>';
    $docu_artikel .= '<td>';
    $docu_artikel .= '<b>Datei ext</b></td>';
    $docu_artikel .= '<td>';
    $docu_artikel .= '<b>Module Platzhalter</b></td>';
#    $docu_artikel .= '<td>';
#    $docu_artikel .= '<b>Image Platzhalter</b></td>';
    $docu_artikel .= '</tr>';
    $docu_artikel .= '<tr>';

    $docu_artikel .= '<td>';
    $replace = makeIconsPlacholder();
    if(count($replace[0]) > 0) {
        $docu_artikel .= '<table width="100%" cellspacing="0" border="1" cellpadding="5">';
        foreach($replace[0] as $pos => $platz) {
            $docu_artikel .= '<tr><td width="10%">'.$platz.'</td><td>'.$replace[1][$pos].'</td></tr>';
        }
        $docu_artikel .= '</table>';
    } else $docu_artikel .= "&nbsp;";
    $docu_artikel .= '</td>';

    $docu_artikel .= '<td>';
    $replace = makeIconsSyntaxPlacholder();
    if(count($replace[0]) > 0) {
        $docu_artikel .= '<table width="100%" cellspacing="0" border="1" cellpadding="5">';
        foreach($replace[0] as $pos => $platz) {
            $docu_artikel .= '<tr><td width="10%">'.$platz.'</td><td>'.$replace[1][$pos].'</td></tr>';
        }
        $docu_artikel .= '</table>';
    } else $docu_artikel .= "&nbsp;";
    $docu_artikel .= '</td>';

    $docu_artikel .= '<td>';
    $replace = makeIconsExtPlacholder();
    if(count($replace[0]) > 0) {
        $docu_artikel .= '<table width="100%" cellspacing="0" border="1" cellpadding="5">';
        foreach($replace[0] as $pos => $platz) {
            $docu_artikel .= '<tr><td width="10%">'.$platz.'</td><td>'.$replace[1][$pos].'</td></tr>';
        }
        $docu_artikel .= '</table>';
    } else $docu_artikel .= "&nbsp;";
    $docu_artikel .= '</td>';

    $docu_artikel .= '<td>';
    $replace = makeModulPlacholder();
    if(count($replace[0]) > 0) {
        $docu_artikel .= '<table width="100%" cellspacing="0" border="1" cellpadding="5">';
        foreach($replace[0] as $pos => $platz) {
            $docu_artikel .= '<tr><td width="10%">'.$platz.'</td><td>'.$replace[1][$pos].'</td></tr>';
        }
        $docu_artikel .= '</table>';
    } else $docu_artikel .= "&nbsp;";
    $docu_artikel .= '</td>';

    $docu_artikel .= '</tr>';
    $docu_artikel .= '<tr>';


    $docu_artikel .= '<td colspan="4">';


    $docu_artikel .= '<br /><b>Image Platzhalter</b>';
    $replace = makeImgPlacholder();
    if(count($replace[0]) > 0) {
        $docu_artikel .= '<table width="100%" cellspacing="0" border="1" cellpadding="5">';
        foreach($replace[0] as $pos => $platz) {
            $docu_artikel .= '<tr><td width="10%">'.$platz.'</td><td>'.$replace[1][$pos].'</td></tr>';
        }
        $docu_artikel .= '</table>';
    } else $docu_artikel .= "&nbsp;";

    $docu_artikel .= '</td>';
    $docu_artikel .= '</tr>';
    $docu_artikel .= '</table>';

    return $docu_artikel;
}


?>