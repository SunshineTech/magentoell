<?php

/**
 *  Plugin Name: EZ InLinkz linkup
 *  Plugin URI: http://www.inlinkz.com
 *  Author: Aris Korbetis, InLinkz.com
 *  Description: Easily adds the InLinkz linkup code to your post instead of relying of embedded SCRIPT tags. To get the code, login normally to www.inlinkz.com and go to "get script" of the relevant collection
 *  Version: 0.7
 * */
/**
  ------------------------------------------------------------------------
  Copyright 2014 Aris Korbetis, InLinkz.com

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License as published by
  the Free Software Foundation; either version 3 of the License, or
  (at your option) any later version.

  This program is distributed in the hope that it will be useful,
  but WITHOUT ANY WARRANTY; without even the implied warranty of
  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
  GNU General Public License for more details.

  You should have received a copy of the GNU General Public License
  along with this program; if not, write to the Free Software
  Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA 02111-1307 USA

 */
define("VERSION", "007");

function replaceInLinkzCode_func($atts) {
    if (!trim($atts['id'])) {
        return "no collection id set";
    }
    
    if (strlen(trim($atts['mode']))>0) {
        $mode = trim($atts['mode']);
    } else {
        $mode = 0;
    }

    $paging = 60;
    if (trim($atts['pagesize'])) {
        $paging = trim($atts['pagesize']);
    }
    
    $noentry="";
    if (trim($atts['noentry'])) {
        $noentry = "data-mode=\"noentry\"";
    }

    if ($mode == 0) {
        return "<!-- start InLinkz script. added by InLinkz_scripter plugin -->
              <script type=\"text/javascript\" src=\"http://www.inlinkz.com/cs.php?id=" . $atts['id'] . "\"></script>
          <!-- end InLinkz script -->";
    } else if ($mode == 1) {
        return "<!-- start InLinkz script. added by InLinkz_scripter plugin -->
                    <div class=\"InLinkzContainer\" $noentry id=\"" . $atts['id'] . "\" pageSize=" . $paging . " ><img src=http://cdn2.inlinkz.com/load.gif border=0>&nbsp;Loading InLinkz ...</div>
                    <script type=\"text/javascript\" src=\"http://static.inlinkz.com/cs2.js?v=" . VERSION . "\"></script>
                <!-- inlinkz script end -->";
    }
}

add_shortcode('inlinkz_linkup', 'replaceInLinkzCode_func');