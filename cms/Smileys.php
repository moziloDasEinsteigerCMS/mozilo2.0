<?php if(!defined('IS_CMS')) die();

class Smileys {

    var $smileysarray;
    var $search = array("&",";","&amp&#059;","/","\\",":","!","'",'"','[',']','{','}','|');
    var $replace = array('&amp;','&#059;','&amp;','&#047;','&#092;','&#058;','&#033;','&apos;','&quot;','&#091;','&#093;','&#123;','&#125;','&#124;');

    function __construct($path) {
        $smileys = new Properties("$path/smileys.txt");
        $this->smileysarray = $smileys->toArray();
    }

    function replaceEmoticons($content) {
        foreach ($this->smileysarray as $icon => $emoticon) {
            $icon = trim($icon);
            $emoticon = str_replace($this->search,$this->replace,$emoticon);
            $emoticon = '<img src="'.URL_BASE.CMS_DIR_NAME.'/smileys/'.$icon.'.gif" class="noborder" alt="'.$emoticon.'" />';
            $content = str_replace(":".$icon.":",$emoticon,$content);
        }
        return $content;
    }

    function getSmileysArray() {
        return $this->smileysarray;
    }
}

?>