<?php if(!defined('IS_ADMIN') or !IS_ADMIN) die();
$TMP_DIR = "tmp/";
if(!is_dir(BASE_DIR.$TMP_DIR)) {
    @mkdir(BASE_DIR.$TMP_DIR);
    @chmod(BASE_DIR.$TMP_DIR,0700);
} else
    @chmod(BASE_DIR.$TMP_DIR,0700);

@session_name(SESSION_MO);
@session_save_path(BASE_DIR.$TMP_DIR);

# hat session_save_path functioniert?
if(strstr(@session_save_path(),BASE_DIR.$TMP_DIR)) {
    define("MULTI_USER", true);
    define("MULTI_USER_FILE","");
# können wir denn session_save_path benutzen
} elseif(strlen(@session_save_path()) > 2 and @is_writable(@session_save_path())) {
    define("MULTI_USER", true);
    define("MULTI_USER_FILE",md5(BASE_DIR));
}
# hat session_save_path functioniert? ansonsten server eigene session verwenden
if(defined("MULTI_USER") and MULTI_USER) {
    $lifetime = 1440;
    if(@ini_get("session.gc_maxlifetime") > 2); # wir ziehen in der admin_template.php 10 secunden ab
        $lifetime = @ini_get("session.gc_maxlifetime");

    define("MULTI_USER_TIME", $lifetime);
    new SessionSaveHandler();
} else
    define("MULTI_USER", false);
unset($TMP_DIR);

class SessionSaveHandler {
    protected $savePath;
    protected $sessionName;

    public function __construct() {
        session_set_save_handler(
            array($this, "open"),
            array($this, "close"),
            array($this, "read"),
            array($this, "write"),
            array($this, "destroy"),
            array($this, "gc")
        );
    }

    public function open($savePath, $sessionName) {
        $this->savePath = $savePath.((substr($savePath,-1) != "/") ? "/" : "");
        $this->sessionName = $sessionName;

        if(!is_file($this->savePath.MULTI_USER_FILE."users.conf.php")) {
            @file_put_contents($this->savePath.MULTI_USER_FILE."users.conf.php","<?php die(); ?>\n".serialize(array()),LOCK_EX);
        }
        @chmod($this->savePath.MULTI_USER_FILE."users.conf.php",0600);

        if(!is_file($this->savePath.MULTI_USER_FILE."session.conf.php")) {
            @file_put_contents($this->savePath.MULTI_USER_FILE."session.conf.php","<?php die(); ?>\n".serialize(array()),LOCK_EX);
        }
        @chmod($this->savePath.MULTI_USER_FILE."session.conf.php",0600);
        return true;
    }

    public function close() {
        return true;
    }

    public function read($id) {
        $id = md5($id);
        $conf = $this->getSessionArray();
        if(!array_key_exists($id, $conf))
            return "";
        return $conf[$id][0];
    }

    public function write($id, $data) {
        $id = md5($id);
        $conf = $this->getSessionArray();
        $conf[$id][0] = $data;
        $conf[$id][1] = time();
        return $this->saveSessionArray($conf);
    }

    public function destroy($id) {
        if(defined('LOGIN') and LOGIN === true and defined('LOGOUT_OTHER_USERS') and LOGOUT_OTHER_USERS === true)
            return $this->saveSessionArray();
        $id = md5($id);
        $conf = $this->getSessionArray();
        if(!array_key_exists($id, $conf))
            return false;
        unset($conf[$id]);
        return $this->saveSessionArray($conf);
    }

    public function gc($maxlifetime) {
        $conf = $this->getSessionArray();
        foreach($conf as $id => $data) {
            if($data[1] + $maxlifetime < time())
                unset($conf[$id]);
        }
        $this->saveSessionArray($conf);
        return true;
    }

    protected function getSessionArray() {
        if(false !== ($conf = @file_get_contents($this->savePath.MULTI_USER_FILE."session.conf.php"))) {
            $conf = str_replace("<?php die(); ?>","",$conf);
            $conf = trim($conf);
            $conf = unserialize($conf);
            if(is_array($conf))
                return $conf;
        }
        return array();
    }

    protected function saveSessionArray($conf = array()) {
        if(defined('MULTI_USER') and MULTI_USER) {
            $new_array = array();
            if(false !== ($confusers = @file_get_contents($this->savePath.MULTI_USER_FILE."users.conf.php"))) {
                $confusers = str_replace("<?php die(); ?>","",$confusers);
                $confusers = trim($confusers);
                $confusers = unserialize($confusers);
                if(is_array($confusers)) {
                    foreach($conf as $id => $temp) {
                        if(!array_key_exists($id, $confusers))
                            $new_array[$id] = "freetab";
                        else
                            $new_array[$id] = $confusers[$id];
                    }
                }
            }
            @file_put_contents($this->savePath.MULTI_USER_FILE."users.conf.php","<?php die(); ?>\n".serialize($new_array),LOCK_EX);
        }
        $conf = "<?php die(); ?>".serialize($conf);
        if(false === (@file_put_contents($this->savePath.MULTI_USER_FILE."session.conf.php",$conf,LOCK_EX)))
            return false;
        return true;
    }
}
?>