<?php if(!defined('IS_ADMIN') or !IS_ADMIN) die();
/*
 * jQuery File Upload Plugin PHP Class 5.9.2
 * https://github.com/blueimp/jQuery-File-Upload
 *
 * Copyright 2010, Sebastian Tschan
 * https://blueimp.net
 *
 * Licensed under the MIT license:
 * http://www.opensource.org/licenses/MIT
 */

class UploadHandler
{
    protected $options;
    protected $subtitle;
    protected $alowed_img_array;
    
    function __construct($options=null) {
        global $ALOWED_IMG_ARRAY;
        $this->alowed_img_array = $ALOWED_IMG_ARRAY;

#file_put_contents(BASE_DIR."out_UploadHandler.txt","request=".$_REQUEST['curent_dir']."\n",FILE_APPEND);

        $curent_dir = getRequestValue('curent_dir',false,false);
        global $specialchars;
        $curent_dir_url = $specialchars->replaceSpecialChars($curent_dir,true);
        $dir = BASE_DIR.CONTENT_DIR_NAME.'/'.$curent_dir.'/'.CONTENT_FILES_DIR_NAME.'/';
        $url_dir = URL_BASE.CONTENT_DIR_NAME.'/'.$curent_dir_url.'/'.CONTENT_FILES_DIR_NAME.'/';

        if(ACTION == "gallery") {
#            list($thumbnail_max_width,$thumbnail_max_height) = $this->get_width_height('thumbnail_max_width','thumbnail_max_height');
            $dir = BASE_DIR.GALLERIES_DIR_NAME.'/'.$curent_dir.'/';
            $url_dir = URL_BASE.GALLERIES_DIR_NAME.'/'.$curent_dir_url.'/';
            if(is_file(GALLERIES_DIR_REL.$curent_dir."/texte.conf.php"))
                $this->subtitle = new Properties(GALLERIES_DIR_REL.$curent_dir."/texte.conf.php");
#            $this->subtitle = new Properties(GALLERIES_DIR_REL.$curent_dir."/texte.txt");
        }
        if(ACTION == "template") {
            $dir = BASE_DIR.LAYOUT_DIR_NAME.'/'.$curent_dir.'/';
            $url_dir = URL_BASE.LAYOUT_DIR_NAME.'/'.$curent_dir_url.'/';
        }

 #       $prev_img = false;
#        if(isset($_REQUEST['prev_img']) and $_REQUEST['prev_img'] == "true")
#            $prev_img = true;

        $this->options = array(
#            'prev_img' => $prev_img,
            'curent_dir' => $curent_dir,
            'script_url' => $this->getFullUrl().'/',
            'upload_dir' => $dir,
            'upload_url' => $url_dir,
            'param_name' => 'files',
            // Set the following option to 'POST', if your server does not support
            // DELETE requests. This is a parameter sent to the client:
            'delete_type' => 'DELETE',
            // The php.ini settings upload_max_filesize and post_max_size
            // take precedence over the following max_file_size setting:
            'max_file_size' => null,
            'min_file_size' => 1,
            'accept_file_types' => '/.+$/i',
            'max_number_of_files' => null,
            // Set the following option to false to enable resumable uploads:
            'discard_aborted_uploads' => false,
            // Set to true to rotate images based on EXIF meta data, if available:
            'orient_image' => false,
            'image_versions' => array(
                // Uncomment the following version to restrict the size of
                // uploaded images. You can also add additional versions with
                // their own upload directories:
                /*
                'large' => array(
                    'upload_dir' => dirname($_SERVER['SCRIPT_FILENAME']).'/files/',
                    'upload_url' => $this->getFullUrl().'/files/',
                    'max_width' => 1920,
                    'max_height' => 1200,
                    'jpeg_quality' => 95
                ),
                */
/*
                'thumbnail' => array(
                    'upload_dir' => dirname($_SERVER['SCRIPT_FILENAME']).'/thumbnails/',
                    'upload_url' => $this->getFullUrl().'/thumbnails/',
                    'max_width' => 80,
                    'max_height' => 80
                )
*/
            )
        );

        if(ACTION == "gallery") {

            if(ctype_digit(getRequestValue('thumbnail_max_width',false,false)))
                $thumbnail_max_width = getRequestValue('thumbnail_max_width');
            else
                $thumbnail_max_width = false;
            if(ctype_digit(getRequestValue('thumbnail_max_height',false,false)))
                $thumbnail_max_height = getRequestValue('thumbnail_max_height');
            else
                $thumbnail_max_height = false;
            if(!$thumbnail_max_width and !$thumbnail_max_height) {
                $thumbnail_max_width = 80;
                $thumbnail_max_height = 80;
            }
            $this->options['image_versions']['thumbnail'] = array(
                    'upload_dir' => $dir.'vorschau/',
                    'upload_url' => $url_dir.'vorschau/',
                    'max_width' => $thumbnail_max_width,
                    'max_height' => $thumbnail_max_height
            );
            if(getRequestValue('new_width',false,false) or getRequestValue('new_height',false,false)) {
                $new_width = false;
                $new_height = false;
                if(false !== ($tmp1 = getRequestValue('new_width')) and ctype_digit($tmp1))
                    $new_width = $tmp1;
                if(false !== ($tmp2 = getRequestValue('new_height')) and ctype_digit($tmp2))
                    $new_height = $tmp2;
                if($new_width or $new_height)
                    $this->options['image_versions']['large'] = array(
                        'upload_dir' => $dir,
                        'upload_url' => $url_dir,
                        'max_width' => $new_width,
                        'max_height' => $new_height,
                        'jpeg_quality' => 95
                    );
            }
        }
        if ($options) {
            $this->options = array_replace_recursive($this->options, $options);
        }
    }

    protected function getFullUrl() {
      	return
    		(isset($_SERVER['HTTPS']) ? 'https://' : 'http://').
    		(isset($_SERVER['REMOTE_USER']) ? $_SERVER['REMOTE_USER'].'@' : '').
    		(isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : ($_SERVER['SERVER_NAME'].
    		(isset($_SERVER['HTTPS']) && $_SERVER['SERVER_PORT'] === 443 ||
    		$_SERVER['SERVER_PORT'] === 80 ? '' : ':'.$_SERVER['SERVER_PORT']))).
    		substr($_SERVER['SCRIPT_NAME'],0, strrpos($_SERVER['SCRIPT_NAME'], '/'));
    }
    
    protected function set_file_delete_url($file) {
        global $specialchars;
        $file->delete_url = $this->options['script_url']
            .'index.php?file='.$specialchars->replaceSpecialChars(($file->name),true)
            .'&curent_dir='.$specialchars->replaceSpecialChars($this->options['curent_dir'],true)
            .'&chancefiles='.getRequestValue('chancefiles')
            .'&action='.ACTION;
        $file->delete_type = $this->options['delete_type'];
        if ($file->delete_type !== 'DELETE') {
            $file->delete_url .= '&_method=DELETE';
        }
    }


    protected function is_image($file_path) {
        if(in_array(strtolower(strrchr($file_path, '.')),$this->alowed_img_array))
            return getimagesize($file_path);
        else
            return false;
    }

    protected function get_file_object($file_name) {
        global $specialchars;
        $file_path = $this->options['upload_dir'].$file_name;
        if (is_file($file_path) && $file_name[0] !== '.') {
            $file = new stdClass();
            $file->name = $file_name;
            $file->size = filesize($file_path);
            $file->url = $this->options['upload_url'].$specialchars->replaceSpecialChars($file->name,false);
            if(ACTION == "gallery") {
                $file->pixel_w = "";
                $file->pixel_h = "";
                if(false !== ($getimagesize = $this->is_image($file_path))) {
                    $file->pixel_w = $getimagesize[0];
                    $file->pixel_h = $getimagesize[1];
                }
                $file->subtitle = mo_rawurlencode($this->subtitle->get($file->name));
            }
            foreach($this->options['image_versions'] as $version => $options) {
                if (is_file($options['upload_dir'].$file_name)) {
                    $file->{$version.'_url'} = $options['upload_url']
                        .$specialchars->replaceSpecialChars($file->name,false);
                }
            }
            $this->set_file_delete_url($file);
            return $file;
        }
        return null;
    }
    
    protected function get_file_objects() {
        if(ACTION == "gallery" or ACTION == "template") {
#!!!!!!!!!!! das global machen
            $file_array = getDirAsArray($this->options['upload_dir'],$this->alowed_img_array);
        } else {
            global $CatPage;
            $file_array = $CatPage->get_FileArray($this->options['curent_dir']);
        }

        return array_values(array_filter(array_map(
            array($this, 'get_file_object'),
            $file_array
        )));
    }

    protected function create_scaled_image($file_name, $options) {
        $file_path = $this->options['upload_dir'].$file_name;
        $new_file_path = $options['upload_dir'].$file_name;
        list($img_width, $img_height) = @getimagesize($file_path);
        if (!$img_width || !$img_height) {
            return false;
        }
        # wenn new_width oder new_height false ist mit bildmassen fühlen
        if($options['max_height'] === false and $options['max_width'] !== false) {
            $options['max_height'] = ($img_height / $img_width) * $options['max_width'];
        }
        if($options['max_width'] === false and $options['max_height'] !== false) {
            $options['max_width'] = ($img_width / $img_height) * $options['max_height'];
        }

        $scale = min(
            $options['max_width'] / $img_width,
            $options['max_height'] / $img_height
        );
        if ($scale >= 1) {
            if ($file_path !== $new_file_path) {
                return copy($file_path, $new_file_path);
            }
            return true;
        }
        $new_width = $img_width * $scale;
        $new_height = $img_height * $scale;
        $new_img = @imagecreatetruecolor($new_width, $new_height);
        switch (strtolower(substr(strrchr($file_name, '.'), 1))) {
            case 'jpg':
            case 'jpeg':
                $src_img = @imagecreatefromjpeg($file_path);
                $write_image = 'imagejpeg';
                $image_quality = isset($options['jpeg_quality']) ?
                    $options['jpeg_quality'] : 75;
                break;
            case 'gif':
                @imagecolortransparent($new_img, @imagecolorallocate($new_img, 0, 0, 0));
                $src_img = @imagecreatefromgif($file_path);
                $write_image = 'imagegif';
                $image_quality = null;
                break;
            case 'png':
                @imagecolortransparent($new_img, @imagecolorallocate($new_img, 0, 0, 0));
                @imagealphablending($new_img, false);
                @imagesavealpha($new_img, true);
                $src_img = @imagecreatefrompng($file_path);
                $write_image = 'imagepng';
                $image_quality = isset($options['png_quality']) ?
                    $options['png_quality'] : 9;
                break;
            default:
                $src_img = null;
        }
        $success = $src_img && @imagecopyresampled(
            $new_img,
            $src_img,
            0, 0, 0, 0,
            $new_width,
            $new_height,
            $img_width,
            $img_height
        ) && $write_image($new_img, $new_file_path, $image_quality);
        // Free up memory (imagedestroy does not delete files):
        @imagedestroy($src_img);
        @imagedestroy($new_img);
        return $success;
    }
    
    protected function has_error($uploaded_file, $file, $error) {
        if ($error) {
            return $error;
        }
#        if (!preg_match($this->options['accept_file_types'], $file->name)) {
#            return 'acceptFileTypes';
#        }
        $acceptfiletypes = $this->alowed_img_array;
        if(ACTION == "files") {
            global $ADMIN_CONF;
            if(strlen($ADMIN_CONF->get("noupload")) > 0) {
                $acceptfiletypes = ".".str_replace("%2C","%2C.",$ADMIN_CONF->get("noupload"));
                $acceptfiletypes = explode("%2C",$acceptfiletypes);
            } else
                $acceptfiletypes = array();
            if(in_array(strtolower(substr($file->name,(strrpos($file->name,".")))),$acceptfiletypes))
                return 'acceptFileTypes';
        } else {
            if(!in_array(strtolower(substr($file->name,(strrpos($file->name,".")))),$acceptfiletypes))
                return 'acceptFileTypes';
        }
        if ($uploaded_file && is_uploaded_file($uploaded_file)) {
            $file_size = filesize($uploaded_file);
        } else {
            $file_size = $_SERVER['CONTENT_LENGTH'];
        }
        if ($this->options['max_file_size'] && (
                $file_size > $this->options['max_file_size'] ||
                $file->size > $this->options['max_file_size'])
            ) {
            return 'maxFileSize';
        }
        if ($this->options['min_file_size'] &&
            $file_size < $this->options['min_file_size']) {
            return 'minFileSize';
        }
        if (is_int($this->options['max_number_of_files']) && (
                count($this->get_file_objects()) >= $this->options['max_number_of_files'])
            ) {
            return 'maxNumberOfFiles';
        }
        return $error;
    }

    protected function upcount_name_callback($matches) {
        $index = isset($matches[1]) ? intval($matches[1]) + 1 : 1;
        $ext = isset($matches[2]) ? $matches[2] : '';
        return ' ('.$index.')'.$ext;
    }

    protected function upcount_name($name) {
        return preg_replace_callback(
            '/(?:(?: \(([\d]+)\))?(\.[^.]+))?$/',
            array($this, 'upcount_name_callback'),
            $name,
            1
        );
    }
    
    protected function trim_file_name($name, $type) {
        // Remove path information and dots around the filename, to prevent uploading
        // into different directories or replacing hidden system files.
        // Also remove control characters and spaces (\x00..\x20) around the filename:
        $file_name = basename($name);
        // Add missing file extension for known image types:
        if (strpos($file_name, '.') === false &&
            preg_match('/^image\/(gif|jpe?g|png)/', $type, $matches)) {
            $file_name .= '.'.$matches[1];
        }
        $file_name = cleanUploadFile($file_name);
        if ($this->options['discard_aborted_uploads']) {
            while(is_file($this->options['upload_dir'].$file_name)) {
                $file_name = $this->upcount_name($file_name);
            }
        }
        return $file_name;
    }

    protected function orient_image($file_path) {
      	$exif = @exif_read_data($file_path);
        if ($exif === false) {
            return false;
        }
      	$orientation = intval(@$exif['Orientation']);
      	if (!in_array($orientation, array(3, 6, 8))) { 
      	    return false;
      	}
      	$image = @imagecreatefromjpeg($file_path);
      	switch ($orientation) {
        	  case 3:
          	    $image = @imagerotate($image, 180, 0);
          	    break;
        	  case 6:
          	    $image = @imagerotate($image, 270, 0);
          	    break;
        	  case 8:
          	    $image = @imagerotate($image, 90, 0);
          	    break;
          	default:
          	    return false;
      	}
      	$success = imagejpeg($image, $file_path);
      	// Free up memory (imagedestroy does not delete files):
      	@imagedestroy($image);
      	return $success;
    }
    
    protected function handle_file_upload($uploaded_file, $name, $size, $type, $error) {

if(!is_dir($this->options['upload_dir'])
    and (ACTION == "gallery" or ACTION == "files")
    and (strpos($this->options['upload_dir'],"/".CONTENT_FILES_DIR_NAME) > 0 or strpos($this->options['upload_dir'],"/".PREVIEW_DIR_NAME) > 0)
    and true !== ($tmp = mkdirMulti($this->options['upload_dir'])))
    $error = $tmp;

        $file = new stdClass();
        $file->name = $this->trim_file_name($name, $type);
        $file->size = intval($size);
        $file->type = $type;
        $error = $this->has_error($uploaded_file, $file, $error);

        if (!$error && $file->name) {
            $file_path = $this->options['upload_dir'].".".$file->name;
            $append_file = !$this->options['discard_aborted_uploads'] &&
                is_file($file_path) && $file->size > filesize($file_path);
            clearstatcache();
            if ($uploaded_file && is_uploaded_file($uploaded_file)) {
                // multipart/formdata uploads (POST method uploads)
                if ($append_file) {
                    file_put_contents(
                        $file_path,
                        fopen($uploaded_file, 'r'),
                        FILE_APPEND
                    );
                } else {
                    move_uploaded_file($uploaded_file, $file_path);
                }
            } else {
                // Non-multipart uploads (PUT method support)
                file_put_contents(
                    $file_path,
                    fopen('php://input', 'r'),
                    $append_file ? FILE_APPEND : 0
                );
            }
            $file_size = filesize($file_path);
            if ($file_size === $file->size) {
                if(is_file($this->options['upload_dir'].$file->name))
                    unlink($this->options['upload_dir'].$file->name);
                rename($file_path,$this->options['upload_dir'].$file->name);
                $file_path = $this->options['upload_dir'].$file->name;
                global $specialchars;
            	if ($this->options['orient_image']) {
            		$this->orient_image($file_path);
            	}
                $file->url = $this->options['upload_url'].$specialchars->replaceSpecialChars($file->name,false);
                foreach($this->options['image_versions'] as $version => $options) {
                    if ($this->create_scaled_image($file->name, $options)) {
                        if ($this->options['upload_dir'] !== $options['upload_dir']) {
                            $file->{$version.'_url'} = $options['upload_url']
                                .$specialchars->replaceSpecialChars($file->name,false);
                        } else {
                            clearstatcache();
                            $file_size = filesize($file_path);
                        }
                    }
                }
                if(ACTION == "gallery") {
                    $file->pixel_w = "";
                    $file->pixel_h = "";
                    if(false !== ($getimagesize = $this->is_image($file_path))) {
                        $file->pixel_w = $getimagesize[0];
                        $file->pixel_h = $getimagesize[1];
                    }
#                    $this->subtitle->set($file->name,"");
                    $file->subtitle = "";
                }
                setChmod($file_path);
            } else if ($this->options['discard_aborted_uploads']) {
                unlink($file_path);
                $file->error = 'abort';
            }
            $file->size = $file_size;
            $this->set_file_delete_url($file);
        } else {
            $file->error = $error;
        }
        return $file;
    }
    

    protected function get_json_data($data_array=array()) {
        $json_data = "{";
        $search = array('"',"\n","\r","\t");
        $replace = array("'","<br />","","    ");
        foreach($data_array as $key => $value) {
            if(!is_int($value)) { # and !is_array($value)
                $value = addslashes(str_replace($search,$replace,trim($value)));
                $value = '"'.str_replace("\\'","'",$value).'"';
            }
            $json_data .= '"'.$key.'":'.$value.',';
        }
        if(strlen($json_data) > 1)
            $json_data = substr($json_data,0,-1);
        return $json_data."}";
    }

    protected function my_json_encode($data_array) {
        if(is_array($data_array) and isset($data_array[key($data_array)]) and (is_array($data_array[key($data_array)]) or is_object($data_array[key($data_array)]))) {
            $json_data = "[";
            $json_get = "";
            foreach($data_array as $key => $value) {
                if(is_object($value))
                    $value = (array)$value;
                $json_get .= $this->get_json_data($value).',';
            }
            if(strlen($json_get) > 1)
                $json_data .= substr($json_get,0,-1);
            $json_data .= "]";
        } elseif(is_array($data_array) or is_object($data_array)) {
            if(is_object($data_array))
                $data_array = (array)$data_array;
            $json_data = $this->get_json_data($data_array);
        } elseif(is_bool($data_array)) {
            $json_data = "false";
            if($data_array)
                $json_data = "true";
        } else
            $json_data = $data_array;
        return $json_data;
    }

    public function get() {
        $file_name = getRequestValue('file',false,false) ?
            basename(getRequestValue('file',false,false)) : null;
        if ($file_name) {
            $info = $this->get_file_object($file_name);
        } else {
            $info = $this->get_file_objects();
        }
        header('content-type: text/html');
        echo '<div id="json-data">'.$this->my_json_encode($info).'</div>';
    }
    
    public function post() {
        if (getRequestValue('_method') === 'DELETE') {
            return $this->delete();
        }
        $upload = isset($_FILES[$this->options['param_name']]) ?
            $_FILES[$this->options['param_name']] : null;
        $info = array();
        if ($upload && is_array($upload['tmp_name'])) {
            // param_name is an array identifier like "files[]",
            // $_FILES is a multi-dimensional array:
            foreach ($upload['tmp_name'] as $index => $value) {
                $info[] = $this->handle_file_upload(
                    $upload['tmp_name'][$index],
                    isset($_SERVER['HTTP_X_FILE_NAME']) ?
                        $_SERVER['HTTP_X_FILE_NAME'] : $upload['name'][$index],
                    isset($_SERVER['HTTP_X_FILE_SIZE']) ?
                        $_SERVER['HTTP_X_FILE_SIZE'] : $upload['size'][$index],
                    isset($_SERVER['HTTP_X_FILE_TYPE']) ?
                        $_SERVER['HTTP_X_FILE_TYPE'] : $upload['type'][$index],
                    $upload['error'][$index]
                );
            }
        } elseif ($upload || isset($_SERVER['HTTP_X_FILE_NAME'])) {
            // param_name is a single object identifier like "file",
            // $_FILES is a one-dimensional array:
            $info[] = $this->handle_file_upload(
                isset($upload['tmp_name']) ? $upload['tmp_name'] : null,
                isset($_SERVER['HTTP_X_FILE_NAME']) ?
                    $_SERVER['HTTP_X_FILE_NAME'] : (isset($upload['name']) ?
                        $upload['name'] : null),
                isset($_SERVER['HTTP_X_FILE_SIZE']) ?
                    $_SERVER['HTTP_X_FILE_SIZE'] : (isset($upload['size']) ?
                        $upload['size'] : null),
                isset($_SERVER['HTTP_X_FILE_TYPE']) ?
                    $_SERVER['HTTP_X_FILE_TYPE'] : (isset($upload['type']) ?
                        $upload['type'] : null),
                isset($upload['error']) ? $upload['error'] : null
            );
        }
        header('Vary: Accept');
        $json = $this->my_json_encode($info);
        $redirect = getRequestValue('redirect',false,false) ?
            getRequestValue('redirect') : null;
        if ($redirect) {
            header('Location: '.sprintf($redirect, mo_rawurlencode($json)));
            return;
        }
        if (isset($_SERVER['HTTP_ACCEPT']) &&
            (strpos($_SERVER['HTTP_ACCEPT'], 'application/json') !== false)) {
            header('Content-type: application/json');
        } else {
            header('Content-type: text/plain');
        }
        header('content-type: text/html');
        echo '<div id="json-data">'.$json.'</div>';
    }
    
    public function delete() {
        $file_name = getRequestValue('file',false,false) ?
            basename(getRequestValue('file',false,false)) : null;
        $file_path = $this->options['upload_dir'].$file_name;
        $success = is_file($file_path) && $file_name[0] !== '.' && unlink($file_path);
        if ($success) {
            if(ACTION == "gallery") {
                $this->subtitle->delete($file_name);
            }
            foreach($this->options['image_versions'] as $version => $options) {
                $file = $options['upload_dir'].$file_name;
                if (is_file($file)) {
                    unlink($file);
                }
            }
        }
    }
    
    public function resize_img() {
        $file_name = $this->trim_file_name(getRequestValue('file',false,false), null);
        foreach($this->options['image_versions'] as $version => $options) {
            $resize = $this->create_scaled_image($file_name, $options);
        }
        $success = array("error" => "Resize fehlgeschlagen");
        if($resize)
            $success = $this->get_file_object($file_name);
        header('content-type: text/html');
        echo '<div id="json-data">'.$this->my_json_encode($success).'</div>';
    }

}

$upload_handler = new UploadHandler();

header('Pragma: no-cache');
header('Cache-Control: no-store, no-cache, must-revalidate');
header('Content-Disposition: inline; filename="files.json"');
header('X-Content-Type-Options: nosniff');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: OPTIONS, HEAD, GET, POST, PUT, DELETE');
header('Access-Control-Allow-Headers: X-File-Name, X-File-Type, X-File-Size');

switch ($_SERVER['REQUEST_METHOD']) {
    case 'OPTIONS':
        break;
    case 'HEAD':
    case 'GET':
        $upload_handler->get();
        break;
    case 'POST':
        if (getRequestValue('resize',false,false) === 'true') {
            $upload_handler->resize_img();
        } elseif (getRequestValue('_method',false,false) === 'DELETE') {
            $upload_handler->delete();
        } else {
            $upload_handler->post();
        }
        break;
    case 'DELETE':
        $upload_handler->delete();
        break;
    default:
        header('HTTP/1.1 405 Method Not Allowed');

}

?>