<?php
/**
 * Separation Degrees One
 *
 * Core extension
 *
 * PHP Version 5
 *
 * @category  SDM
 * @package   SDM_Core
 * @author    Separation Degrees One <magento@separationdegrees.com>
 * @copyright Copyright (c) 2015 Separation Degrees One (http://www.separationdegrees.com)
 */

/**
 * SDM_Core_Helper_Wordpress class
 */
class SDM_Core_Helper_Wordpress extends Mage_Core_Helper_Data
{
    /**
     * Truncate HTML Safely
     *
     * @param  int    $maxLength
     * @param  string $html
     * @param  string $indicator
     * @return null
     */
    public function truncateHtml($maxLength, $html, $indicator = '&hellip;')
    {
        $output_length = 0; // number of counted characters stored so far in $output
        $position = 0;      // character offset within input string after last tag/entity
        $tag_stack = array(); // stack of tags we've encountered but not closed
        $output = '';
        $truncated = false;
        $unpaired_tags = array( 'doctype', '!doctype',
            'area','base','basefont','bgsound','br','col',
            'embed','frame','hr','img','input','link','meta',
            'param','sound','spacer','wbr');

        // loop through, splitting at HTML entities or tags
        while ($output_length < $maxLength
                && preg_match('{</?([a-z]+)[^>]*>|&#?[a-zA-Z0-9]+;}', $html, $match, PREG_OFFSET_CAPTURE, $position)) {
            list($tag, $tag_position) = $match[0];

            // get text leading up to the tag, and store it (up to maxLength)
            $text = mb_strcut($html, $position, $tag_position - $position);
            if ($output_length + mb_strlen($text) > $maxLength) {
                $output .= mb_strcut($text, 0, $maxLength - $output_length);
                $truncated = true;
                $output_length = $maxLength;
                break;
            }

            // store everything, it wasn't too long
            $output .= $text;
            $output_length += mb_strlen($text);

            if ($tag[0] == '&') {
                $output .= $tag;
                $output_length++; // only counted as one character
            } else {
                $tag_inner = $match[1][0];
                if ($tag[1] == '/') {
                    $output .= $tag;
                    // If input tags aren't balanced, we leave the popped tag
                    // on the stack so hopefully we're not introducing more
                    // problems.
                    if (end($tag_stack) == $tag_inner) {
                        array_pop($tag_stack);
                    }
                } elseif ($tag[mb_strlen($tag) - 2] == '/'
                    || in_array(strtolower($tag_inner), $unpaired_tags)
                ) {
                    // Self-closing or unpaired tag
                    $output .= $tag;
                } else {
                    $output .= $tag;
                    $tag_stack[] = $tag_inner; // push tag onto the stack
                }
            }

            // Continue after the tag we just found
            $position = $tag_position + mb_strlen($tag);
        }

        // Print any remaining text after the last tag, if there's room.
        if ($output_length < $maxLength && $position < mb_strlen($html)) {
            $output .= mb_strcut($html, $position, $maxLength - $output_length);
        }
        
        $truncated = mb_strlen($html)-$position > $maxLength - $output_length;

        // add terminator if it was truncated in loop or just above here
        if ($truncated) {
            $output .= $indicator;
        }

        // Close any open tags
        while (!empty($tag_stack)) {
            $output .= '</'.array_pop($tag_stack).'>';
        }

        return $output;
    }

    /**
     * Returns a truncated wordpress post. Respects <!--more--> tag and
     * returns supplied post if <!--truncated--> is already present.
     * 
     * @param  [type] $text [description]
     * @return [type]       [description]
     */
    public function truncatePost($content, $link)
    {
        $content = trim($content);

        if (count(explode("<!--truncated-->", $content)) > 1) {
            return $content;
        }

        $excerpt = explode("<!--more-->", $content);
        if (count($excerpt) > 1) {
            return $excerpt[0] . 
                '<div class="clear">&nbsp;</div><a class="continue-read button"'.
                'href="'.$link.'">Continue Reading &raquo;</a><!--truncated-->';
        } else {
            return $this->truncateHtml(
                    400,
                    $content,
                    '...<div class="clear">&nbsp;</div><a class="continue-read button" '.
                    'href="' . $link . '">Continue Reading &raquo;</a><!--truncated-->'
                );
        }
    }
}
