<?php if(!defined('IS_CMS')) die();
// {{{ license

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4 foldmethod=marker: */
//
// +----------------------------------------------------------------------+
// | This library is free software; you can redistribute it and/or modify |
// | it under the terms of the GNU Lesser General Public License as       |
// | published by the Free Software Foundation; either version 2.1 of the |
// | License, or (at your option) any later version.                      |
// |                                                                      |
// | This library is distributed in the hope that it will be useful, but  |
// | WITHOUT ANY WARRANTY; without even the implied warranty of           |
// | MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU    |
// | Lesser General Public License for more details.                      |
// |                                                                      |
// | You should have received a copy of the GNU Lesser General Public     |
// | License along with this library; if not, write to the Free Software  |
// | Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307 |
// | USA.                                                                 |
// +----------------------------------------------------------------------+
//

// }}}

/**
 * Encode/decode Internationalized Domain Names.
 *
 * The class allows to convert internationalized domain names
 * (see RFC 3490 for details) as they can be used with various registries worldwide
 * to be translated between their original (localized) form and their encoded form
 * as it will be used in the DNS (Domain Name System).
 *
 * The class provides two public methods, encode() and decode(), which do exactly
 * what you would expect them to do. You are allowed to use complete domain names,
 * simple strings and complete email addresses as well. That means, that you might
 * use any of the following notations:
 *
 * - www.nörgler.com
 * - xn--nrgler-wxa
 * - xn--brse-5qa.xn--knrz-1ra.info
 *
 * Unicode input might be given as either UTF-8 string, UCS-4 string or UCS-4 array.
 * Unicode output is available in the same formats.
 * You can select your preferred format via {@link set_paramter()}.
 *
 * ACE input and output is always expected to be ASCII.
 *
 * @author  Matthias Sommerfeld <mso@phlylabs.de>
 * @copyright 2004-2011 phlyLabs Berlin, http://phlylabs.de
 * @version 0.8.0 2011-03-11
 */
class idna_convert
{
    // NP See below

    // Internal settings, do not mess with them
    protected $_punycode_prefix = 'xn--';
    protected $_invalid_ucs = 0x80000000;
    protected $_max_ucs = 0x10FFFF;
    protected $_base = 36;
    protected $_tmin = 1;
    protected $_tmax = 26;
    protected $_skew = 38;
    protected $_damp = 700;
    protected $_initial_bias = 72;
    protected $_initial_n = 0x80;
    protected $_sbase = 0xAC00;
    protected $_lbase = 0x1100;
    protected $_vbase = 0x1161;
    protected $_tbase = 0x11A7;
    protected $_lcount = 19;
    protected $_vcount = 21;
    protected $_tcount = 28;
    protected $_ncount = 588;   // _vcount * _tcount
    protected $_scount = 11172; // _lcount * _tcount * _vcount
    protected $_error = false;

    protected static $_mb_string_overload = null;

    // See {@link set_paramter()} for details of how to change the following
    // settings from within your script / application
    protected $_api_encoding = 'utf8';   // Default input charset is UTF-8
    protected $_allow_overlong = false;  // Overlong UTF-8 encodings are forbidden
    protected $_strict_mode = false;     // Behave strict or not
    protected $_idn_version = 2008;      // Can be either 2003 (old, default) or 2008

    /**
     * the constructor
     *
     * @param array $options
     * @return boolean
     * @since 0.5.2
     */
    public function __construct($options = false)
    {
        $this->slast = $this->_sbase + $this->_lcount * $this->_vcount * $this->_tcount;
        // populate mbstring overloading cache if not set
        if (self::$_mb_string_overload === null) {
            self::$_mb_string_overload = (extension_loaded('mbstring')
                && (ini_get('mbstring.func_overload') & 0x02) === 0x02);
        }
    }

    public function encode($decoded)
    {
        $decoded = $this->_utf8_to_ucs4($decoded);
        // Forcing conversion of input to UCS4 array
        // If one time encoding is given, use this, else the objects property

        // No input, no output, what else did you expect?
        if (empty($decoded)) return '';

        // Anchors for iteration
        $last_begin = 0;
        // Output string
        $output = '';
        foreach ($decoded as $k => $v) {
            // Make sure to use just the plain dot
            switch($v) {
            case 0x3002:
            case 0xFF0E:
            case 0xFF61:
                $decoded[$k] = 0x2E;
                // Right, no break here, the above are converted to dots anyway
            // Stumbling across an anchoring character
            case 0x2E:
            case 0x2F:
            case 0x3A:
            case 0x3F:
            case 0x40:
                // Neither email addresses nor URLs allowed in strict mode
                if ($this->_strict_mode) {
                   $this->_error('Neither email addresses nor URLs are allowed in strict mode.');
                   return false;
                } else {
                    // Skip first char
                    if ($k) {
                        $encoded = '';
                        $encoded = $this->_encode(array_slice($decoded, $last_begin, (($k)-$last_begin)));
                        if ($encoded) {
                            $output .= $encoded;
                        } else {
                            $output .= $this->_ucs4_to_utf8(array_slice($decoded, $last_begin, (($k)-$last_begin)));
                        }
                        $output .= chr($decoded[$k]);
                    }
                    $last_begin = $k + 1;
                }
            }
        }
        // Catch the rest of the string
        if ($last_begin) {
            $inp_len = sizeof($decoded);
            $encoded = '';
            $encoded = $this->_encode(array_slice($decoded, $last_begin, (($inp_len)-$last_begin)));
            if ($encoded) {
                $output .= $encoded;
            } else {
                $output .= $this->_ucs4_to_utf8(array_slice($decoded, $last_begin, (($inp_len)-$last_begin)));
            }
            return $output;
        } else {
            if ($output = $this->_encode($decoded)) {
                return $output;
            } else {
                return $this->_ucs4_to_utf8($decoded);
            }
        }
    }

    /**
     * Removes a weakness of encode(), which cannot properly handle URIs but instead encodes their
     * path or query components, too.
     * @param string  $uri  Expects the URI as a UTF-8 (or ASCII) string
     * @return  string  The URI encoded to Punycode, everything but the host component is left alone
     * @since 0.6.4
     */
    public function encode_uri($uri)
    {
        $parsed = parse_url($uri);
        if (!isset($parsed['host'])) {
            $this->_error('The given string does not look like a URI');
            return false;
        }
        $arr = explode('.', $parsed['host']);
        foreach ($arr as $k => $v) {
            $conv = $this->encode($v, 'utf8');
            if ($conv) $arr[$k] = $conv;
        }
        $parsed['host'] = join('.', $arr);
        $return =
                (empty($parsed['scheme']) ? '' : $parsed['scheme'].(strtolower($parsed['scheme']) == 'mailto' ? ':' : '://'))
                .(empty($parsed['user']) ? '' : $parsed['user'].(empty($parsed['pass']) ? '' : ':'.$parsed['pass']).'@')
                .$parsed['host']
                .(empty($parsed['port']) ? '' : ':'.$parsed['port'])
                .(empty($parsed['path']) ? '' : $parsed['path'])
                .(empty($parsed['query']) ? '' : '?'.$parsed['query'])
                .(empty($parsed['fragment']) ? '' : '#'.$parsed['fragment']);
        return $return;
    }

    /**
     * The actual encoding algorithm
     * @param  string
     * @return mixed
     */
    protected function _encode($decoded)
    {
/*echo "<pre>";
print_r($decoded);
echo "</pre><br>\n";*/
#echo $this->_ucs4_to_utf8($decoded)."<br>\n";
        // We cannot encode a domain name containing the Punycode prefix
        $extract = self::byteLength($this->_punycode_prefix);
        $check_pref = $this->_utf8_to_ucs4($this->_punycode_prefix);
        $check_deco = array_slice($decoded, 0, $extract);

        if ($check_pref == $check_deco) {
            $this->_error('This is already a punycode string');
            return false;
        }
        // We will not try to encode strings consisting of basic code points only
        $encodable = false;
        foreach ($decoded as $k => $v) {
            if ($v > 0x7a) {
                $encodable = true;
                break;
            }
        }
        if (!$encodable) {
            $this->_error('The given string does not contain encodable chars');
            return false;
        }
        // Do NAMEPREP
        $decoded = $this->_nameprep($decoded);
        if (!$decoded || !is_array($decoded)) return false; // NAMEPREP failed
        $deco_len  = count($decoded);
        if (!$deco_len) return false; // Empty array
        $codecount = 0; // How many chars have been consumed
        $encoded = '';
        // Copy all basic code points to output
        for ($i = 0; $i < $deco_len; ++$i) {
            $test = $decoded[$i];
            // Will match [-0-9a-zA-Z]
            if ((0x2F < $test && $test < 0x40) || (0x40 < $test && $test < 0x5B)
                    || (0x60 < $test && $test <= 0x7B) || (0x2D == $test)) {
                $encoded .= chr($decoded[$i]);
                $codecount++;
            }
        }
        if ($codecount == $deco_len) return $encoded; // All codepoints were basic ones

        // Start with the prefix; copy it to output
        $encoded = $this->_punycode_prefix.$encoded;
        // If we have basic code points in output, add an hyphen to the end
        if ($codecount) $encoded .= '-';
        // Now find and encode all non-basic code points
        $is_first = true;
        $cur_code = $this->_initial_n;
        $bias = $this->_initial_bias;
        $delta = 0;
        while ($codecount < $deco_len) {
            // Find the smallest code point >= the current code point and
            // remember the last ouccrence of it in the input
            for ($i = 0, $next_code = $this->_max_ucs; $i < $deco_len; $i++) {
                if ($decoded[$i] >= $cur_code && $decoded[$i] <= $next_code) {
                    $next_code = $decoded[$i];
                }
            }
            $delta += ($next_code - $cur_code) * ($codecount + 1);
            $cur_code = $next_code;

            // Scan input again and encode all characters whose code point is $cur_code
            for ($i = 0; $i < $deco_len; $i++) {
                if ($decoded[$i] < $cur_code) {
                    $delta++;
                } elseif ($decoded[$i] == $cur_code) {
                    for ($q = $delta, $k = $this->_base; 1; $k += $this->_base) {
                        $t = ($k <= $bias) ? $this->_tmin :
                                (($k >= $bias + $this->_tmax) ? $this->_tmax : $k - $bias);
                        if ($q < $t) break;
                        $encoded .= $this->_encode_digit(intval($t + (($q - $t) % ($this->_base - $t)))); //v0.4.5 Changed from ceil() to intval()
                        $q = (int) (($q - $t) / ($this->_base - $t));
                    }
                    $encoded .= $this->_encode_digit($q);
                    $bias = $this->_adapt($delta, $codecount+1, $is_first);
                    $codecount++;
                    $delta = 0;
                    $is_first = false;
                }
            }
            $delta++;
            $cur_code++;
        }
        return $encoded;
    }

    /**
     * This converts an UTF-8 encoded string to its UCS-4 representation
     * By talking about UCS-4 "strings" we mean arrays of 32bit integers representing
     * each of the "chars". This is due to PHP not being able to handle strings with
     * bit depth different from 8. This apllies to the reverse method _ucs4_to_utf8(), too.
     * The following UTF-8 encodings are supported:
     * bytes bits  representation
     * 1        7  0xxxxxxx
     * 2       11  110xxxxx 10xxxxxx
     * 3       16  1110xxxx 10xxxxxx 10xxxxxx
     * 4       21  11110xxx 10xxxxxx 10xxxxxx 10xxxxxx
     * 5       26  111110xx 10xxxxxx 10xxxxxx 10xxxxxx 10xxxxxx
     * 6       31  1111110x 10xxxxxx 10xxxxxx 10xxxxxx 10xxxxxx 10xxxxxx
     * Each x represents a bit that can be used to store character data.
     * The five and six byte sequences are part of Annex D of ISO/IEC 10646-1:2000
     * @param string $input
     * @return string
     */
    protected function _utf8_to_ucs4($input)
    {
        $output = array();
        $out_len = 0;
        $inp_len = self::byteLength($input);
        $mode = 'next';
        $test = 'none';
        for ($k = 0; $k < $inp_len; ++$k) {
            $v = ord($input{$k}); // Extract byte from input string
            if ($v < 128) { // We found an ASCII char - put into stirng as is
                $output[$out_len] = $v;
                ++$out_len;
                if ('add' == $mode) {
                    $this->_error('Conversion from UTF-8 to UCS-4 failed: malformed input at byte '.$k);
                    return false;
                }
                continue;
            }
            if ('next' == $mode) { // Try to find the next start byte; determine the width of the Unicode char
                $start_byte = $v;
                $mode = 'add';
                $test = 'range';
                if ($v >> 5 == 6) { // &110xxxxx 10xxxxx
                    $next_byte = 0; // Tells, how many times subsequent bitmasks must rotate 6bits to the left
                    $v = ($v - 192) << 6;
                } elseif ($v >> 4 == 14) { // &1110xxxx 10xxxxxx 10xxxxxx
                    $next_byte = 1;
                    $v = ($v - 224) << 12;
                } elseif ($v >> 3 == 30) { // &11110xxx 10xxxxxx 10xxxxxx 10xxxxxx
                    $next_byte = 2;
                    $v = ($v - 240) << 18;
                } elseif ($v >> 2 == 62) { // &111110xx 10xxxxxx 10xxxxxx 10xxxxxx 10xxxxxx
                    $next_byte = 3;
                    $v = ($v - 248) << 24;
                } elseif ($v >> 1 == 126) { // &1111110x 10xxxxxx 10xxxxxx 10xxxxxx 10xxxxxx 10xxxxxx
                    $next_byte = 4;
                    $v = ($v - 252) << 30;
                } else {
                    $this->_error('This might be UTF-8, but I don\'t understand it at byte '.$k);
                    return false;
                }
                if ('add' == $mode) {
                    $output[$out_len] = (int) $v;
                    ++$out_len;
                    continue;
                }
            }
            if ('add' == $mode) {
                if (!$this->_allow_overlong && $test == 'range') {
                    $test = 'none';
                    if (($v < 0xA0 && $start_byte == 0xE0) || ($v < 0x90 && $start_byte == 0xF0) || ($v > 0x8F && $start_byte == 0xF4)) {
                        $this->_error('Bogus UTF-8 character detected (out of legal range) at byte '.$k);
                        return false;
                    }
                }
                if ($v >> 6 == 2) { // Bit mask must be 10xxxxxx
                    $v = ($v - 128) << ($next_byte * 6);
                    $output[($out_len - 1)] += $v;
                    --$next_byte;
                } else {
                    $this->_error('Conversion from UTF-8 to UCS-4 failed: malformed input at byte '.$k);
                    return false;
                }
                if ($next_byte < 0) {
                    $mode = 'next';
                }
            }
        } // for
        return $output;
    }

    /**
     * Convert UCS-4 string into UTF-8 string
     * See _utf8_to_ucs4() for details
     * @param string  $input
     * @return string
     */
    protected function _ucs4_to_utf8($input)
    {
        $output = '';
        foreach ($input as $k => $v) {
            if ($v < 128) { // 7bit are transferred literally
                $output .= chr($v);
            } elseif ($v < (1 << 11)) { // 2 bytes
                $output .= chr(192+($v >> 6)).chr(128+($v & 63));
            } elseif ($v < (1 << 16)) { // 3 bytes
                $output .= chr(224+($v >> 12)).chr(128+(($v >> 6) & 63)).chr(128+($v & 63));
            } elseif ($v < (1 << 21)) { // 4 bytes
                $output .= chr(240+($v >> 18)).chr(128+(($v >> 12) & 63)).chr(128+(($v >> 6) & 63)).chr(128+($v & 63));
            } elseif (self::$safe_mode) {
                $output .= self::$safe_char;
            } else {
                $this->_error('Conversion from UCS-4 to UTF-8 failed: malformed input at byte '.$k);
                return false;
            }
        }
        return $output;
    }

    /**
     * Gets the length of a string in bytes even if mbstring function
     * overloading is turned on
     *
     * @param string $string the string for which to get the length.
     * @return integer the length of the string in bytes.
     */
    protected static function byteLength($string)
    {
        if (self::$_mb_string_overload) {
            return mb_strlen($string, '8bit');
        }
        return strlen((binary) $string);
    }

    /**
     * Internal error handling method
     * @param  string $error
     */
    protected function _error($error = '')
    {
        $this->_error = $error;
    }

    /**
     * Do Nameprep according to RFC3491 and RFC3454
     * @param    array    Unicode Characters
     * @return   string   Unicode Characters, Nameprep'd
     */
    protected function _nameprep($input)
    {
        $output = array();
        $error = false;
        //
        // Mapping
        // Walking through the input array, performing the required steps on each of
        // the input chars and putting the result into the output array
        // While mapping required chars we apply the cannonical ordering
        foreach ($input as $v) {
            // Map to nothing == skip that code point
            if (in_array($v, self::$NP['map_nothing'])) continue;
            // Try to find prohibited input
            if (in_array($v, self::$NP['prohibit']) || in_array($v, self::$NP['general_prohibited'])) {
                $this->_error('NAMEPREP: Prohibited input U+'.sprintf('%08X', $v));
                return false;
            }
            foreach (self::$NP['prohibit_ranges'] as $range) {
                if ($range[0] <= $v && $v <= $range[1]) {
                    $this->_error('NAMEPREP: Prohibited input U+'.sprintf('%08X', $v));
                    return false;
                }
            }

            if (0xAC00 <= $v && $v <= 0xD7AF) {
                // Hangul syllable decomposition
                foreach ($this->_hangul_decompose($v) as $out) {
                    $output[] = (int) $out;
                }
            } else {
                $output[] = (int) $v;
            }
        }
        // Before applying any Combining, try to rearrange any Hangul syllables
        $output = $this->_hangul_compose($output);
        //
        // Combine code points
        //
        $last_class = 0;
        $last_starter = 0;
        $out_len = count($output);
        for ($i = 0; $i < $out_len; ++$i) {
            $class = $this->_get_combining_class($output[$i]);
            if ((!$last_class || $last_class > $class) && $class) {
                // Try to match
                $seq_len = $i - $last_starter;
                $out = $this->_combine(array_slice($output, $last_starter, $seq_len));
                // On match: Replace the last starter with the composed character and remove
                // the now redundant non-starter(s)
                if ($out) {
                    $output[$last_starter] = $out;
                    if (count($out) != $seq_len) {
                        for ($j = $i+1; $j < $out_len; ++$j) $output[$j-1] = $output[$j];
                        unset($output[$out_len]);
                    }
                    // Rewind the for loop by one, since there can be more possible compositions
                    $i--;
                    $out_len--;
                    $last_class = ($i == $last_starter) ? 0 : $this->_get_combining_class($output[$i-1]);
                    continue;
                }
            }
            // The current class is 0
            if (!$class) $last_starter = $i;
            $last_class = $class;
        }
        return $output;
    }

    /**
     * Ccomposes a Hangul syllable
     * (see http://www.unicode.org/unicode/reports/tr15/#Hangul
     * @param    array    Decomposed UCS4 sequence
     * @return   array    UCS4 sequence with syllables composed
     */
    protected function _hangul_compose($input)
    {
        $inp_len = count($input);
        if (!$inp_len) return array();
        $result = array();
        $last = (int) $input[0];
        $result[] = $last; // copy first char from input to output

        for ($i = 1; $i < $inp_len; ++$i) {
            $char = (int) $input[$i];
            $sindex = $last - $this->_sbase;
            $lindex = $last - $this->_lbase;
            $vindex = $char - $this->_vbase;
            $tindex = $char - $this->_tbase;
            // Find out, whether two current characters are LV and T
            if (0 <= $sindex && $sindex < $this->_scount && ($sindex % $this->_tcount == 0)
                    && 0 <= $tindex && $tindex <= $this->_tcount) {
                // create syllable of form LVT
                $last += $tindex;
                $result[(count($result) - 1)] = $last; // reset last
                continue; // discard char
            }
            // Find out, whether two current characters form L and V
            if (0 <= $lindex && $lindex < $this->_lcount && 0 <= $vindex && $vindex < $this->_vcount) {
                // create syllable of form LV
                $last = (int) $this->_sbase + ($lindex * $this->_vcount + $vindex) * $this->_tcount;
                $result[(count($result) - 1)] = $last; // reset last
                continue; // discard char
            }
            // if neither case was true, just add the character
            $last = $char;
            $result[] = $char;
        }
        return $result;
    }

    /**
     * Returns the combining class of a certain wide char
     * @param    integer    Wide char to check (32bit integer)
     * @return   integer    Combining class if found, else 0
     */
    protected function _get_combining_class($char)
    {
        return isset(self::$NP['norm_combcls'][$char]) ? self::$NP['norm_combcls'][$char] : 0;
    }

    /**
     * Encoding a certain digit
     * @param    int $d
     * @return string
     */
    protected function _encode_digit($d)
    {
        return chr($d + 22 + 75 * ($d < 26));
    }

    /**
     * Adapt the bias according to the current code point and position
     * @param int $delta
     * @param int $npoints
     * @param int $is_first
     * @return int
     */
    protected function _adapt($delta, $npoints, $is_first)
    {
        $delta = intval($is_first ? ($delta / $this->_damp) : ($delta / 2));
        $delta += intval($delta / $npoints);
        for ($k = 0; $delta > (($this->_base - $this->_tmin) * $this->_tmax) / 2; $k += $this->_base) {
            $delta = intval($delta / ($this->_base - $this->_tmin));
        }
        return intval($k + ($this->_base - $this->_tmin + 1) * $delta / ($delta + $this->_skew));
    }

    /**
     * Holds all relevant mapping tables
     * See RFC3454 for details
     *
     * @private array
     * @since 0.5.2
     */
    protected static $NP = array
            ('map_nothing' => array(0xAD, 0x34F, 0x1806, 0x180B, 0x180C, 0x180D, 0x200B, 0x200C
                    ,0x200D, 0x2060, 0xFE00, 0xFE01, 0xFE02, 0xFE03, 0xFE04, 0xFE05, 0xFE06, 0xFE07
                    ,0xFE08, 0xFE09, 0xFE0A, 0xFE0B, 0xFE0C, 0xFE0D, 0xFE0E, 0xFE0F, 0xFEFF
                    )
            ,'general_prohibited' => array(0, 1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14, 15, 16, 17, 18, 19
                    ,20, 21, 22, 23, 24, 25, 26, 27, 28, 29, 30, 31, 32 ,33, 34, 35, 36, 37, 38, 39, 40, 41, 42
                    ,43, 44, 47, 59, 60, 61, 62, 63, 64, 91, 92, 93, 94, 95, 96, 123, 124, 125, 126, 127, 0x3002
                    )
            ,'prohibit' => array(0xA0, 0x340, 0x341, 0x6DD, 0x70F, 0x1680, 0x180E, 0x2000, 0x2001, 0x2002, 0x2003
                    ,0x2004, 0x2005, 0x2006, 0x2007, 0x2008, 0x2009, 0x200A, 0x200B, 0x200C, 0x200D, 0x200E, 0x200F
                    ,0x2028, 0x2029, 0x202A, 0x202B, 0x202C, 0x202D, 0x202E, 0x202F, 0x205F, 0x206A, 0x206B, 0x206C
                    ,0x206D, 0x206E, 0x206F, 0x3000, 0xFEFF, 0xFFF9, 0xFFFA, 0xFFFB, 0xFFFC, 0xFFFD, 0xFFFE, 0xFFFF
                    ,0x1FFFE, 0x1FFFF, 0x2FFFE, 0x2FFFF, 0x3FFFE, 0x3FFFF, 0x4FFFE, 0x4FFFF, 0x5FFFE, 0x5FFFF, 0x6FFFE
                    ,0x6FFFF, 0x7FFFE, 0x7FFFF, 0x8FFFE, 0x8FFFF, 0x9FFFE, 0x9FFFF, 0xAFFFE, 0xAFFFF, 0xBFFFE, 0xBFFFF
                    ,0xCFFFE, 0xCFFFF, 0xDFFFE, 0xDFFFF, 0xE0001, 0xEFFFE, 0xEFFFF, 0xFFFFE, 0xFFFFF, 0x10FFFE, 0x10FFFF
                    )
            ,'prohibit_ranges' => array(array(0x80, 0x9F), array(0x2060, 0x206F), array(0x1D173, 0x1D17A)
                    ,array(0xE000, 0xF8FF) ,array(0xF0000, 0xFFFFD), array(0x100000, 0x10FFFD)
                    ,array(0xFDD0, 0xFDEF), array(0xD800, 0xDFFF), array(0x2FF0, 0x2FFB), array(0xE0020, 0xE007F)
                    )
            ,'norm_combcls' => array(0x334   => 1,   0x335   => 1,   0x336   => 1,   0x337   => 1
                    ,0x338   => 1,   0x93C   => 7,   0x9BC   => 7,   0xA3C   => 7,   0xABC   => 7
                    ,0xB3C   => 7,   0xCBC   => 7,   0x1037  => 7,   0x3099  => 8,   0x309A  => 8
                    ,0x94D   => 9,   0x9CD   => 9,   0xA4D   => 9,   0xACD   => 9,   0xB4D   => 9
                    ,0xBCD   => 9,   0xC4D   => 9,   0xCCD   => 9,   0xD4D   => 9,   0xDCA   => 9
                    ,0xE3A   => 9,   0xF84   => 9,   0x1039  => 9,   0x1714  => 9,   0x1734  => 9
                    ,0x17D2  => 9,   0x5B0   => 10,  0x5B1   => 11,  0x5B2   => 12,  0x5B3   => 13
                    ,0x5B4   => 14,  0x5B5   => 15,  0x5B6   => 16,  0x5B7   => 17,  0x5B8   => 18
                    ,0x5B9   => 19,  0x5BB   => 20,  0x5Bc   => 21,  0x5BD   => 22,  0x5BF   => 23
                    ,0x5C1   => 24,  0x5C2   => 25,  0xFB1E  => 26,  0x64B   => 27,  0x64C   => 28
                    ,0x64D   => 29,  0x64E   => 30,  0x64F   => 31,  0x650   => 32,  0x651   => 33
                    ,0x652   => 34,  0x670   => 35,  0x711   => 36,  0xC55   => 84,  0xC56   => 91
                    ,0xE38   => 103, 0xE39   => 103, 0xE48   => 107, 0xE49   => 107, 0xE4A   => 107
                    ,0xE4B   => 107, 0xEB8   => 118, 0xEB9   => 118, 0xEC8   => 122, 0xEC9   => 122
                    ,0xECA   => 122, 0xECB   => 122, 0xF71   => 129, 0xF72   => 130, 0xF7A   => 130
                    ,0xF7B   => 130, 0xF7C   => 130, 0xF7D   => 130, 0xF80   => 130, 0xF74   => 132
                    ,0x321   => 202, 0x322   => 202, 0x327   => 202, 0x328   => 202, 0x31B   => 216
                    ,0xF39   => 216, 0x1D165 => 216, 0x1D166 => 216, 0x1D16E => 216, 0x1D16F => 216
                    ,0x1D170 => 216, 0x1D171 => 216, 0x1D172 => 216, 0x302A  => 218, 0x316   => 220
                    ,0x317   => 220, 0x318   => 220, 0x319   => 220, 0x31C   => 220, 0x31D   => 220
                    ,0x31E   => 220, 0x31F   => 220, 0x320   => 220, 0x323   => 220, 0x324   => 220
                    ,0x325   => 220, 0x326   => 220, 0x329   => 220, 0x32A   => 220, 0x32B   => 220
                    ,0x32C   => 220, 0x32D   => 220, 0x32E   => 220, 0x32F   => 220, 0x330   => 220
                    ,0x331   => 220, 0x332   => 220, 0x333   => 220, 0x339   => 220, 0x33A   => 220
                    ,0x33B   => 220, 0x33C   => 220, 0x347   => 220, 0x348   => 220, 0x349   => 220
                    ,0x34D   => 220, 0x34E   => 220, 0x353   => 220, 0x354   => 220, 0x355   => 220
                    ,0x356   => 220, 0x591   => 220, 0x596   => 220, 0x59B   => 220, 0x5A3   => 220
                    ,0x5A4   => 220, 0x5A5   => 220, 0x5A6   => 220, 0x5A7   => 220, 0x5AA   => 220
                    ,0x655   => 220, 0x656   => 220, 0x6E3   => 220, 0x6EA   => 220, 0x6ED   => 220
                    ,0x731   => 220, 0x734   => 220, 0x737   => 220, 0x738   => 220, 0x739   => 220
                    ,0x73B   => 220, 0x73C   => 220, 0x73E   => 220, 0x742   => 220, 0x744   => 220
                    ,0x746   => 220, 0x748   => 220, 0x952   => 220, 0xF18   => 220, 0xF19   => 220
                    ,0xF35   => 220, 0xF37   => 220, 0xFC6   => 220, 0x193B  => 220, 0x20E8  => 220
                    ,0x1D17B => 220, 0x1D17C => 220, 0x1D17D => 220, 0x1D17E => 220, 0x1D17F => 220
                    ,0x1D180 => 220, 0x1D181 => 220, 0x1D182 => 220, 0x1D18A => 220, 0x1D18B => 220
                    ,0x59A   => 222, 0x5AD   => 222, 0x1929  => 222, 0x302D  => 222, 0x302E  => 224
                    ,0x302F  => 224, 0x1D16D => 226, 0x5AE   => 228, 0x18A9  => 228, 0x302B  => 228
                    ,0x300   => 230, 0x301   => 230, 0x302   => 230, 0x303   => 230, 0x304   => 230
                    ,0x305   => 230, 0x306   => 230, 0x307   => 230, 0x308   => 230, 0x309   => 230
                    ,0x30A   => 230, 0x30B   => 230, 0x30C   => 230, 0x30D   => 230, 0x30E   => 230
                    ,0x30F   => 230, 0x310   => 230, 0x311   => 230, 0x312   => 230, 0x313   => 230
                    ,0x314   => 230, 0x33D   => 230, 0x33E   => 230, 0x33F   => 230, 0x340   => 230
                    ,0x341   => 230, 0x342   => 230, 0x343   => 230, 0x344   => 230, 0x346   => 230
                    ,0x34A   => 230, 0x34B   => 230, 0x34C   => 230, 0x350   => 230, 0x351   => 230
                    ,0x352   => 230, 0x357   => 230, 0x363   => 230, 0x364   => 230, 0x365   => 230
                    ,0x366   => 230, 0x367   => 230, 0x368   => 230, 0x369   => 230, 0x36A   => 230
                    ,0x36B   => 230, 0x36C   => 230, 0x36D   => 230, 0x36E   => 230, 0x36F   => 230
                    ,0x483   => 230, 0x484   => 230, 0x485   => 230, 0x486   => 230, 0x592   => 230
                    ,0x593   => 230, 0x594   => 230, 0x595   => 230, 0x597   => 230, 0x598   => 230
                    ,0x599   => 230, 0x59C   => 230, 0x59D   => 230, 0x59E   => 230, 0x59F   => 230
                    ,0x5A0   => 230, 0x5A1   => 230, 0x5A8   => 230, 0x5A9   => 230, 0x5AB   => 230
                    ,0x5AC   => 230, 0x5AF   => 230, 0x5C4   => 230, 0x610   => 230, 0x611   => 230
                    ,0x612   => 230, 0x613   => 230, 0x614   => 230, 0x615   => 230, 0x653   => 230
                    ,0x654   => 230, 0x657   => 230, 0x658   => 230, 0x6D6   => 230, 0x6D7   => 230
                    ,0x6D8   => 230, 0x6D9   => 230, 0x6DA   => 230, 0x6DB   => 230, 0x6DC   => 230
                    ,0x6DF   => 230, 0x6E0   => 230, 0x6E1   => 230, 0x6E2   => 230, 0x6E4   => 230
                    ,0x6E7   => 230, 0x6E8   => 230, 0x6EB   => 230, 0x6EC   => 230, 0x730   => 230
                    ,0x732   => 230, 0x733   => 230, 0x735   => 230, 0x736   => 230, 0x73A   => 230
                    ,0x73D   => 230, 0x73F   => 230, 0x740   => 230, 0x741   => 230, 0x743   => 230
                    ,0x745   => 230, 0x747   => 230, 0x749   => 230, 0x74A   => 230, 0x951   => 230
                    ,0x953   => 230, 0x954   => 230, 0xF82   => 230, 0xF83   => 230, 0xF86   => 230
                    ,0xF87   => 230, 0x170D  => 230, 0x193A  => 230, 0x20D0  => 230, 0x20D1  => 230
                    ,0x20D4  => 230, 0x20D5  => 230, 0x20D6  => 230, 0x20D7  => 230, 0x20DB  => 230
                    ,0x20DC  => 230, 0x20E1  => 230, 0x20E7  => 230, 0x20E9  => 230, 0xFE20  => 230
                    ,0xFE21  => 230, 0xFE22  => 230, 0xFE23  => 230, 0x1D185 => 230, 0x1D186 => 230
                    ,0x1D187 => 230, 0x1D189 => 230, 0x1D188 => 230, 0x1D1AA => 230, 0x1D1AB => 230
                    ,0x1D1AC => 230, 0x1D1AD => 230, 0x315   => 232, 0x31A   => 232, 0x302C  => 232
                    ,0x35F   => 233, 0x362   => 233, 0x35D   => 234, 0x35E   => 234, 0x360   => 234
                    ,0x361   => 234, 0x345   => 240
                    )
            );
}
?>