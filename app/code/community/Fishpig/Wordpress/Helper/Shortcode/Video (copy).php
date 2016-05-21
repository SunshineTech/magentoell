<?php
/**
 * @category    Fishpig
 * @package     Fishpig_Wordpress
 * @author      Master Software Solutions
 */

class Fishpig_Wordpress_Helper_Shortcode_Video extends Mage_Core_Helper_Abstract
{
    /**
     * Allowed short code
     *
     * @var array
     */
    protected $_shortcode_tags = array(
        'embed'    => 'wp_embeded_shortcode',
        //'wp_caption' => 'img_caption_shortcode',
        // 'caption'  => 'img_caption_shortcode',
        //'gallery'  => 'gallery_shortcode',
        'playlist' => 'wp_playlist_shortcode',
        //'audio'    => 'wp_audio_shortcode',
        'video'    => 'wp_video_shortcode',
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
        $pattern = $this->get_shortcode_regex();
        $content = preg_replace_callback("/$pattern/s", array($this, 'do_shortcode_tag'), $content);
    }

    /**
     * Retrieve the shortcode regular expression for searching.
     *
     * @return string The shortcode search regular expression
     */
    public function get_shortcode_regex()
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

    public function do_shortcode_tag($m)
    {
        //echo "<pre>";print_r($m);
        if ($m[1] == '[' && $m[6] == ']') {
            return substr($m[0], 1, -1);
        }
        $tag  = $m[2];
        $attr = $this->shortcode_parse_atts($m[3]);
        if (isset($m[5])) {
            // enclosing tag - extra parameter
            return $m[1] . call_user_func(array($this, $this->_shortcode_tags[$tag]), array($attr, $m[5], $tag)) . $m[6];
        } else {
            // self-closing tag
            return $m[1] . call_user_func(array($this, $this->_shortcode_tags[$tag]), array($attr, null, $tag)) . $m[6];
        }

    }

    public function shortcode_parse_atts($text)
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
    public function wp_video_shortcode($a)
    {
        $html   = "";
        $height = "352";
        $width  = "625";
        $src    = "";
        if (!empty($a) && isset($a[0])) {
            $src = isset($a[0]['mp4']) ? $a[0]['mp4'] : $src;
        }
        $html = '<video height="' . $height . '" width="' . $width . '" controls="controls" preload="metadata" class="wp-video-shortcode"><source src=' . $src . ' type="video/mp4"></source></video>';

        return $html;
    }

    /**
     * Retrieve the HTML pattern for the embeded video
     *
     * @return string
     */
    public function wp_embeded_shortcode($a)
    {
        $html       = "";
        $height     = "352";
        $width      = "625";
        $yt_pattern = '#^https?://(?:www\.)?(?:youtube\.com/watch|youtu\.be/)#';
        //$vimeo_pattern = '#^https?://(.+\.)?vimeo\.com/.*#';
        if (isset($a[1])) {
            if (preg_match($yt_pattern, $a[1])) {
                $html = ' <iframe width="' . $width . '" height= "' . $height . '" src=' . $a[1] . '></iframe>';
            }
        }
        return $html;
    }

    public function wp_playlist_shortcode($a)
    {
        $html = "";
        if (isset($a[0]['ids']) && $a[0]['ids']) {
            $ids  = '(' . $a[0]['ids'] . ')';
            $post = Mage::getModel('wordpress/post')->getPostMeta($ids, '_wp_attached_file');
            if (!empty($post)) {
                foreach ($post as $k => $v) {
                    $src  = $v['meta_value'];
                    $html = $html . '<video height="352" width="625" controls="controls" preload="metadata" class="wp-video-shortcode"><source src="http://localhost/sizzix/ellison/wp/wp-content/uploads/' . $src . '" type="video/mp4"></source></video>';
                }
            }
        }
        return $html;
    }

}
