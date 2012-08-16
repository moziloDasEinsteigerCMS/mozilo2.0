<?php if(!defined('IS_CMS')) die();

class Properties {

    private $file;
#    public $properties;
    private $properties;
    private $isConf;

    function Properties($file = null) {
#echo $file."<br>\n";
        $this->isConf = true;
        if(substr($file,-9) == ".conf.php")
            $this->isConf = true;
        elseif(substr($file,-4) == ".txt")
            $this->isConf = false;
        else
            die("Fatal Error 1");
        clearstatcache();

        if(!is_file($file))
            die("Fatal Error File doesn't exist: ".basename($file));

        if(defined('IS_ADMIN') and IS_ADMIN and false !== ($handle = @fopen($file, "r+")))
            fclose($handle);
        elseif(defined('IS_CMS') and false !== ($handle = @fopen($file, "r")))
            fclose($handle);
        else
            die("Fatal Error Can't write or read file: ".basename($file));

        $this->file = $file;
        $this->loadProperties();

    }

    # props und txt sachen lesen
    private function loadProperties() {
        if($this->isConf === true) {
            if(false === ($conf = file_get_contents($this->file)))
                die("Fatal Error Can't read file: ".basename($this->file));
            global $page_protect_search;
            $conf = str_replace($page_protect_search,"",$conf);
            $conf = trim($conf);
            $conf = unserialize($conf);
            if(!is_array($conf))
                die("Fatal Error 2");
            $this->properties = $conf;
            unset($conf);
        } elseif($this->isConf === false and is_array(($lines = @file($this->file)))) {
            foreach ($lines as $line) {
                // comments
                if (preg_match("/^#/",$line) or preg_match("/^\s*$/",$line) or preg_match("/^<?php$/",$line)) {
                    continue;
                }
                if (preg_match("/^([^=]*)=(.*)/",$line,$matches)) {
                    $this->properties[trim($matches[1])] = trim($matches[2]);
                }
            }
        } else
            die("Fatal Error 3");
    }

    # props schreiben keine txt sachen
    private function saveProperties() {
        if(defined('IS_ADMIN') and IS_ADMIN and $this->isConf === true) {
            global $page_protect;
            $conf = $page_protect.serialize($this->properties);
            if(false === (file_put_contents($this->file,$conf,LOCK_EX))) {
                return false;
            }
            return true;
            unset($conf,$tmp);
        }
        return false;
    }

    # gibt den inhalt von prop zurück
    public function get($key) {
        if(isset($this->properties[$key])) {
            return $this->properties[$key];
        }
        return NULL;
    }

    # gibts den key?
    public function keyExists($key) {
        if(isset($this->properties[$key])) {
            return true;
        }
        return false;
    }

    # schreibt anhand eines arrays die props.
    # props die nicht im array sind werden nicht verändert
    # auch neue werden hinzugefügt
    public function setFromArray($values) {
        if(defined('IS_ADMIN') and IS_ADMIN and $this->isConf === true) {
            $tmp = $this->properties;
            foreach ($values as $key => $value) {
                $this->properties[$key] = $value;
            }
            if(true === ($this->saveProperties()))
                return true;
            $this->properties = $tmp;
        }
        return false;
    }

    # speichert einen eintrag. legt in auch neu an aber nur im admin
    # im cms wird er gesetzt aber nicht gespeichert
    public function set($key,$value) {
        if(defined('IS_ADMIN') and IS_ADMIN and $this->isConf === true) {
            $tmp = $this->properties;
            if(($key != "")) {
                $this->properties[$key] = $value;
#echo "save conf".$key." = ".$this->properties[$key]."<br>\n";
                if(true === ($this->saveProperties()))
                return true;
            }
            $this->properties = $tmp;
        #!!!!!!! virtuel setzen wird nicht gespeichert siehe z.B usesubmenu
        } elseif(defined('IS_CMS'))
            $this->properties[$key] = $value;
        return false;
    }

    # löscht ein prop
    public function delete($deletekey) {
        if(defined('IS_ADMIN') and IS_ADMIN and $this->isConf === true) {
            $tmp = $this->properties;
            if(isset($this->properties[$deletekey])) {
                unset($this->properties[$deletekey]);
                if(true === ($this->saveProperties()))
                    return true;
            }
            $this->properties = $tmp;
        }
        return false;
    }

    # gipt alle props als array(key => value) zurück
    public function toArray() {
        return $this->properties;
    }

    # gibt alle props textarea conform zurück
    # oder nur $key
    public function getToTextarea($key = false) {
        $syntax = NULL;
        if($key === false) {
            foreach($this->properties as $key => $value) {
                $syntax .= $key." = ".$value."\n";#<br />
            }
            if(strlen($syntax) >= strlen("\n"))#<br />
                $syntax = substr($syntax,0,(strlen($syntax)-strlen("\n")));#<br />
        } else
            $syntax = $this->get($key);
        $syntax = str_replace(array("<br />","&","<",">"),array("\n","&#38;","&#60;","&#62;"),$syntax);
        return $syntax;
    }

    # setzt die props anhand einer textarea. Achtung löscht vorher die $this->properties
    public function setFromTextarea($content) {
#        if(strstr($this->file,"syntax.conf.php"))
#            global $specialchars;
        $content = str_replace(array("\r\n","\r","\n"),"\n",$content);
#$tmp = $content;
        $content = explode("\n",$content);
        $syntax = array();
        $key = "d*u*m*y";
        $syntax[$key] = NULL;
        foreach($content as $value) {
#            $value = trim($value);
            preg_match("/^([^=][a-zA-Z0-9_]*) = (.*)/",$value,$array);
            if(count($array) == 3) {
                $key = trim($array[1]);
                $syntax[$key] = $array[2];
/*echo "<pre>";
print_r($array);
echo "</pre><br>\n";*/
            } else {
                $syntax[$key] .= "\n".$value;#"<br />".
/*echo $value."<br>\n";*/
            }
        }
#        if(isset($syntax["d*u*m*y"]))
            unset($syntax["d*u*m*y"]);
        $this->properties = array();
        $this->setFromArray($syntax);
        return $syntax;
/*
echo "############";
echo "<pre>";
echo $tmp;
print_r($syntax);
echo "</pre><br>\n";*/
    }
}

?>
