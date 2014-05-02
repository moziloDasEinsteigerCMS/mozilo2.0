<?php

define("CHARSET","UTF-8");
define("DOCU_DIR_NAME","docu");
define("DOCU_PHP","index.php");
$URL_BASE = substr($_SERVER['SCRIPT_NAME'],0,strpos($_SERVER['SCRIPT_NAME'],DOCU_DIR_NAME."/".DOCU_PHP));
$URL_BASE = htmlentities($URL_BASE,ENT_COMPAT,CHARSET);
define("URL_BASE",$URL_BASE);
unset($URL_BASE);
# fals da bei winsystemen \\ drin sind in \ wandeln
$BASE_DIR = str_replace("\\\\", "\\",__FILE__);
# zum schluss noch den teil denn wir nicht brauchen abschneiden
$BASE_DIR = substr($BASE_DIR,0,-(strlen(DOCU_DIR_NAME."/".DOCU_PHP)));
define("BASE_DIR",$BASE_DIR);
unset($BASE_DIR);


if(is_readable(BASE_DIR.DOCU_DIR_NAME."/docuClass.php")) {
    include_once(BASE_DIR.DOCU_DIR_NAME."/docuClass.php");
    $DocuClass = new moziloDocuClass();
} else
    exit("Fatal Error Can't read file: docuClass.php");




#$DocuClass->docu_artikel = $docu_error;
if(false !== ($html = $DocuClass->makeDocuArtikel()))
    exit($html);

$html = '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" '."\n"
            .'  "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">'."\n"
            .'<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="de" lang="de">'."\n";
$html .= "<head>";
$html .= '<meta http-equiv="Content-Type" content="text/html; charset='.CHARSET.'" />';
$html .= '<title>'.$DocuClass->getDocuLanguage('do_title').'</title>';

$html .= $DocuClass->getDocuHead();

$css = ' do-body-nodialog';
if($DocuClass->dialog)
    $css = ' do-body-dialog';

$html .= '</head><body class="ui-widget'.$css.'" style="font-size:12px;">';

$html .= '<div id="do-box">';

if(!$DocuClass->dialog) {
    $html .= '<table id="do-main" cellspacing="0" border="0" cellpadding="0">'
        .'<tr class="do-noprint"><td width="50%">&nbsp;</td>'
        .'<td colspan="2">'
        .'<div class="mo-margin-bottom ui-widget ui-state-default ui-corner-all mo-li-head-tag-no-ul mo-li-head-tag mo-td-middle">'
        .'<b class="mo-padding-left" style="float:left;line-height:23px;">'.$DocuClass->getDocuLanguage("do_title").'</b>';

    if($DocuClass->docu_writer)
        $html .= $DocuClass->makeDocuLink('<img class="mo-icons-icon mo-icons-help" src="'.BASE_URL_DOCU.'admin/gfx/clear.gif" />',$DocuClass->docu_writer,false,false,true);

    $langs = $DocuClass->getLanguages();
    if(count($langs) > 1) {
        $html .= '<div class="do-lang"><ul class="ui-helper-reset ui-helper-clearfix">';
        foreach($langs as $img_lang) {
            $cssaktiv = " mo-ui-state-hover";
            if($img_lang == $DocuClass->curent_lang)
                $cssaktiv = ' ui-tabs-selected ui-state-active';
        $html .= '<li class="ui-state-default ui-corner-all'.$cssaktiv.'">'.$DocuClass->makeDocuLink('<img src="'.BASE_URL_DOCU.'css/do_flags/'.$img_lang.'.png" />',$DocuClass->artikel,$DocuClass->subartikel,$img_lang).'</li>';
        }
        $html .= '</ul></div>';
    }

    $html .= '<br class="mo-clear" /></div>'
        .'</td><td width="50%">&nbsp;</td></tr>'
        .'<tr><td class="do-noprint">&nbsp;</td>'
        .'<td class="do-td-top">'
        .'<div class="ui-tabs mo-ui-tabs">'.$DocuClass->makeSubMenu().'</div>'
        .'<div id="do-content" class="ui-widget-content ui-corner-bottom mo-no-border-top">'.$DocuClass->docu_artikel.'</div>'
        .'</td>'
        .'<td class="do-noprint do-td-top">'
        .$DocuClass->makeDocuMenu()
        .'</td>'
        .'<td class="do-noprint">&nbsp;</td></tr></table>';
} else {
    $html .= '<div id="do-content" class="ui-tabs mo-ui-tabs">'.$DocuClass->makeSubMenu().$DocuClass->docu_artikel.'</div>';
}

$html .= "</div></body></html>";

header('content-type: text/html; charset='.CHARSET.'');

echo $html;

?>