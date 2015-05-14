<?php if(!defined('IS_CMS')) die();

/**
LICENSE New BSD License
Regular expressions used : http://code.google.com/p/minify/
*/

class cssMinifier {
    private $buffer = '';

    public function echoCSS($css) {
        if(is_array($css)) {
            echo '<style type="text/css">'."\n";
            foreach($css as $file) {
                if(file_exists(BASE_DIR.$file)) {
                    if(PACK_CSS) {
                        $this->buffer = file_get_contents(BASE_DIR.$file);
                        $this->replaceImgUrl(BASE_DIR.$file);
                        $this->process();
                    } else
                        echo '@import "'.URL_BASE.$file.'";'."\n";
                }
            }
            echo '</style>'."\n";
        } elseif(file_exists(BASE_DIR.$css)) {
                echo '<style type="text/css">'."\n";
                if(PACK_CSS) {
                    $this->buffer = file_get_contents(BASE_DIR.$css);
                    $this->replaceImgUrl(BASE_DIR.$css);
                    $this->process();
                } else
                    echo '@import "'.URL_BASE.$css.'";'."\n";
                echo '</style>'."\n";
        }
        $this->buffer = '';
    }

    private function replaceImgUrl($file) {
        preg_match_all('/url\\(\\s*([^\\)\\s]+)\\s*\\)/',$this->buffer,$match1);
        preg_match_all('/@import\\s+([\'"])(.*?)[\'"]/',$this->buffer,$match2);

        $url = dirname(str_replace(BASE_DIR,URL_BASE,$file));
        $match = array_unique(array_merge($match1[1],$match2[1]));
        foreach($match as $img) {
            $this->buffer = str_replace($img,$url."/".$img,$this->buffer);
        }
    }

    private function process() {
        // Remove comments
        $this->buffer = preg_replace('!/\*[^*]*\*+([^/][^*]*\*+)*/!', '', $this->buffer);
        //Trim
        $this->buffer = trim($this->buffer);
        $this->buffer = str_replace("\r\n", "\n", $this->buffer);
        // preserve empty comment after '>'
        // http://www.webdevout.net/css-hacks#in_css-selectors
        $this->buffer = preg_replace('@>/\\*\\s*\\*/@', '>/*keep*/', $this->buffer);
        // preserve empty comment between property and value
        // http://css-discuss.incutio.com/?page=BoxModelHack
        $this->buffer = preg_replace('@/\\*\\s*\\*/\\s*:@', '/*keep*/:', $this->buffer);
        $this->buffer = preg_replace('@:\\s*/\\*\\s*\\*/@', ':/*keep*/', $this->buffer);

        // remove ws around { } and last semicolon in declaration block
        $this->buffer = preg_replace('/\\s*{\\s*/', '{', $this->buffer);
        $this->buffer = preg_replace('/;?\\s*}\\s*/', '}', $this->buffer);
        // remove ws surrounding semicolons
        $this->buffer = preg_replace('/\\s*;\\s*/', ';', $this->buffer);
        // remove ws around urls
        $this->buffer = preg_replace('/
                url\\(      # url(
                \\s*
                ([^\\)]+?)  # 1 = the URL (really just a bunch of non right parenthesis)
                \\s*
                \\)         # )
            /x', 'url($1)', $this->buffer);
        // remove ws between rules and colons
        $this->buffer = preg_replace('/
                \\s*
                ([{;])              # 1 = beginning of block or rule separator 
                \\s*
                ([\\*_]?[\\w\\-]+)  # 2 = property (and maybe IE filter)
                \\s*
                :
                \\s*
                (\\b|[#\'"-])        # 3 = first character of a value
            /x', '$1$2:$3', $this->buffer);
        // minimize hex colors
        $this->buffer = preg_replace('/([^=])#([a-f\\d])\\2([a-f\\d])\\3([a-f\\d])\\4([\\s;\\}])/i'
            , '$1#$2$3$4$5', $this->buffer);
        $this->buffer = preg_replace('/@import\\s+url/', '@import url', $this->buffer);
        // replace any ws involving newlines with a single newline
        $this->buffer = preg_replace('/[ \\t]*\\n+\\s*/', "\n", $this->buffer);
        // separate common descendent selectors w/ newlines (to limit line lengths)
        $this->buffer = preg_replace('/([\\w#\\.\\*]+)\\s+([\\w#\\.\\*]+){/', "$1\n$2{", $this->buffer);
        // Use newline after 1st numeric value (to limit line lengths).
        $this->buffer = preg_replace('/
            ((?:padding|margin|border|outline):\\d+(?:px|em)?) # 1 = prop : 1st numeric value
            \\s+
            /x'
            ,"$1\n", $this->buffer);
        // prevent triggering IE6 bug: http://www.crankygeek.com/ie6pebug/
        $this->buffer = preg_replace('/:first-l(etter|ine)\\{/', ':first-l$1 {', $this->buffer);
        echo $this->buffer;
    }
}
?>