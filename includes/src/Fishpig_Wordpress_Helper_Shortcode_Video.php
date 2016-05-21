<?php
/**
 * @category    Fishpig
 * @package     Fishpig_Wordpress
 * @author      Extended By Master Software Solutions
 */

class Fishpig_Wordpress_Helper_Shortcode_Video extends Mage_Core_Helper_Abstract
{
    /**
     * Allowed short code
     *
     * @var array
     */
    protected $_shortcode_tags = array(
        'playlist' => 'playlistShortcode',
        'video'    => 'videoShortcode',
    );

    /**
     * Apply the Video short code
     *
     * @param string &$content
     * @param Fishpig_Wordpress_Model_Post_Abstract $object`
     * @return void
     */
    public function apply(&$content, Fishpig_Wordpress_Model_Post_Abstract $object)
    {
        $pattern = $this->getShortcodeRegex();
        $content = preg_replace_callback("/$pattern/s", array($this, 'shortcodeTag'), $content);
    }

    /**
     * Retrieve the shortcode regular expression for searching.
     *
     * @return string The shortcode search regular expression
     */
    public function getShortcodeRegex()
    {
        $tagnames  = array_keys($this->_shortcode_tags);
        $tagregexp = join('|', array_map('preg_quote', $tagnames));

        return
        '\\[' // Opening bracket
         . '(\\[?)' // 1: Optional second opening bracket for escaping shortcodes: [[tag]]
         . "($tagregexp)"// 2: Shortcode name
         . '(?![\\w-])' // Not followed by word character or hyphen
         . '(' // 3: Unroll the loop: Inside the opening shortcode tag
         . '[^\\]\\/]*' // Not a closing bracket or forward slash
         . '(?:'
        . '\\/(?!\\])' // A forward slash not followed by a closing bracket
         . '[^\\]\\/]*' // Not a closing bracket or forward slash
         . ')*?'
        . ')'
        . '(?:'
        . '(\\/)' // 4: Self closing tag ...
         . '\\]' // ... and closing bracket
         . '|'
        . '\\]' // Closing bracket
         . '(?:'
        . '(' // 5: Unroll the loop: Optionally, anything between the opening and closing shortcode tags
         . '[^\\[]*+' // Not an opening bracket
         . '(?:'
        . '\\[(?!\\/\\2\\])' // An opening bracket not followed by the closing shortcode tag
         . '[^\\[]*+' // Not an opening bracket
         . ')*+'
        . ')'
        . '\\[\\/\\2\\]' // Closing shortcode tag
         . ')?'
            . ')'
            . '(\\]?)'; // 6: Optional second closing brocket for escaping shortcodes: [[tag]]
    }

    /**
     * Regular Expression callable for shortcodeTag() for calling shortcode.
     *
     * @param array $m Regular expression match array
     * @return return False on failure.
     */
    public function shortcodeTag($m)
    {

        // allow [[syntax]] for escaping a tag
        if ($m[1] == '[' && $m[6] == ']') {
            return substr($m[0], 1, -1);
        }
        $tag  = $m[2];
        $attr = $this->shortcodeParseAtts($m[3]);
        if (isset($m[5])) {
            return $m[1] . call_user_func(array($this, $this->_shortcode_tags[$tag]), array($attr, $m[5], $tag)) . $m[6];
        } else {
            return $m[1] . call_user_func(array($this, $this->_shortcode_tags[$tag]), array($attr, null, $tag)) . $m[6];
        }

    }

    /**
     * Retrieve all attributes from the shortcodes tag.
     *
     * @param string $text
     * @return array List of attributes and their value.
     */
    public function shortcodeParseAtts($text)
    {
        $atts    = array();
        $pattern = '/(\w+)\s*=\s*"([^"]*)"(?:\s|$)|(\w+)\s*=\s*\'([^\']*)\'(?:\s|$)|(\w+)\s*=\s*([^\s\'"]+)(?:\s|$)|"([^"]*)"(?:\s|$)|(\S+)(?:\s|$)/';
        $text    = preg_replace("/[\x{00a0}\x{200b}]+/u", " ", $text);
        if (preg_match_all($pattern, $text, $match, PREG_SET_ORDER)) {
            foreach ($match as $m) {
                if (!empty($m[1])) {
                    $atts[strtolower($m[1])] = stripcslashes($m[2]);
                } elseif (!empty($m[3])) {
                    $atts[strtolower($m[3])] = stripcslashes($m[4]);
                } elseif (!empty($m[5])) {
                    $atts[strtolower($m[5])] = stripcslashes($m[6]);
                } elseif (isset($m[7]) && strlen($m[7])) {
                    $atts[] = stripcslashes($m[7]);
                } elseif (isset($m[8])) {
                    $atts[] = stripcslashes($m[8]);
                }

            }
        } else {
            $atts = ltrim($text);
        }
        return $atts;
    }

    /**
     * Retrieve the HTML pattern for the Video
     *
     * @return string
     */
    public function videoShortcode($a)
    {
        $html   = "";
        $height = "352";
        $width  = "625";
        $src    = "";
        $type   = "";
        if (!empty($a) && isset($a[0])) {
            if (isset($a[0]['mp4'])) {
                $src  = $a[0]['mp4'];
                $type = "video/mp4";
            }
            if (isset($a[0]['webm'])) {
                $src  = $a[0]['webm'];
                $type = "video/webm";
            }
            if (isset($a[0]['ogv'])) {
                $src  = $a[0]['ogv'];
                $type = "video/ogg";
            }
            $height = isset($a[0]['height']) && $a[0]['height'] && $a[0]['height'] < $height ? $a[0]['height'] : $height;
            $width  = isset($a[0]['width']) && $a[0]['width'] && $a[0]['width'] < $width ? $a[0]['width'] : $width;
        }
        $html = '<video style="width: 100%; height: 100%;" height="' . $height . '" width="' . $width . '" controls="controls" preload="metadata" class="wp-video-shortcode"><source src="' . $src . '" type="' . $type . '"></source></video>';
        return $html;
    }

    /**
     * Retrieve the HTML pattern for playlist videos
     *
     * @return string
     */
    public function playlistShortcode($a)
    {
        $html = "";
        $url  = Mage::helper('wordpress')->getWpOption('home');
        if (isset($a[0]['ids']) && $a[0]['ids']) {
            $ids  = '(' . $a[0]['ids'] . ')';
            $post = Mage::getModel('wordpress/post')->getPostMeta($ids, '_wp_attached_file');
            if (!empty($post)) {
                foreach ($post as $k => $v) {
                    $url  = str_replace("index.php/", "", $url);
                    $url  = rtrim($url, "/");
                    $src  = $url . '/wp-content/uploads/' . $v['meta_value'];
                    $html = $html . '<video style="width: 100%; height: 100%;" height="352" width="625" controls="controls" preload="metadata" class="wp-video-shortcode"><source src="' . $src . '" type="video/mp4"></source></video>';
                }
            }
        }
        return $html;
    }

}
