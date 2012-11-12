<?php if(!defined('IS_ADMIN') or !IS_ADMIN) die();
global $activ_plugins,$deactiv_plugins;

#list($activ_plugins,$deactiv_plugins) = meditor_findPlugins();
$var_PluginsActiv = 'var moziloPluginsActiv = "0E0M0P0T0Y0";';
if(isset($activ_plugins) and count($activ_plugins) > 0) {
    rsort($activ_plugins);
    $var_PluginsActiv = 'var moziloPluginsActiv = "'.implode('|',$activ_plugins).'";';
}
$var_PluginsDeactiv = 'var moziloPluginsDeactiv = "0E0M0P0T0Y0";';
if(isset($deactiv_plugins) and count($deactiv_plugins) > 0) {
    rsort($deactiv_plugins);
    $var_PluginsDeactiv = 'var moziloPluginsDeactiv = "'.implode('|',$deactiv_plugins).'";';
}
$moziloPlace = makePlatzhalter(true);
foreach($moziloPlace as $key => $value) {
    $value = substr($value,1,-1);
    if(in_array($value,$activ_plugins)) {
        unset($moziloPlace[$key]);
        continue;
    }
    $moziloPlace[$key] = $value;
}
rsort($moziloPlace);
$var_Place = 'var moziloPlace = "'.implode('|',$moziloPlace).'|VALUE|DESCRIPTION";';

$var_UserSyntax = 'var moziloUserSyntax = "0E0M0P0T0Y0";';
global $USER_SYNTAX;
$moziloUserSyntax  = $USER_SYNTAX->toArray();
if(count($moziloUserSyntax) > 0) {
    $moziloUserSyntax = array_keys($moziloUserSyntax);
    rsort($moziloUserSyntax);
    $var_UserSyntax = 'var moziloUserSyntax = "'.implode('|',$moziloUserSyntax).'";';
}

$smileys = new Properties(BASE_DIR_CMS."smileys/smileys.txt");
$moziloSmileys = $smileys->toArray();
$var_Smileys = 'var moziloSmileys = "0E0M0P0T0Y0";';
if(count($moziloSmileys) > 0) {
    $moziloSmileys = array_keys($moziloSmileys);
    rsort($moziloSmileys);
    $var_Smileys = 'var moziloSmileys = "'.implode('|',$moziloSmileys).'";';
}

$moziloSyntax = 'var moziloSyntax = "link|mail|kategorie|seite|absatz|datei|galerie|bildlinks|bildrechts|bild|----|links|zentriert|block|rechts|fett|kursiv|fettkursiv|unter|durch|ueber1|ueber2|ueber3|liste|numliste|liste1|liste2|liste3|html|tabelle|include|farbe|fontsize";';


$editor_area_html = '<link type="text/css" rel="stylesheet" href="editsite.css" />'.
'<link type="text/css" rel="stylesheet" href="jquery/farbtastic/farbtastic.css" />'.
'<script type="text/javascript" src="jquery/farbtastic/farbtastic.js"></script>';
# -uncompressed
$editor_area_html .= '<script src="ace_editor/ace.js" type="text/javascript" charset="utf-8"></script>'
.'<script language="Javascript" type="text/javascript">/*<![CDATA[*/
var meditorID = "pagecontent";
var ace_editor = "";
'.$var_PluginsActiv.'
'.$var_PluginsDeactiv.'
'.$var_Place.'
'.$var_UserSyntax.'
'.$var_Smileys.'
'.$moziloSyntax.'
/*]]>*/</script>';

?>