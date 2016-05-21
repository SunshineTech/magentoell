<?php
/*
Plugin Name: Attachment Page Redirect X
Description: Redirect attachment pages to their parent page (301), when possible, otherwise to the blog home page (302).
Author: Ben Yates
Version: 1.0.0
Author URI: http://bayates.host-ed.me/wordpress/
*/

if (!function_exists('aprx_template_redirect')) {
	function aprx_template_redirect() {
		global $post, $wpdb;
		if (is_attachment() and isset($post->post_parent) and is_numeric($post->post_parent)) {
			if ($post->post_parent != 0) {
				wp_redirect(get_permalink($post->post_parent), 301);
				exit;
			} else {
				$publish = true;
				$path = '';
				$sql = "SELECT post_name, post_parent FROM " . $wpdb->posts
					. " WHERE post_name = '" . $post->post_name . "'
					AND post_status = 'publish' AND (post_type = 'page' OR post_type = 'post')
					ORDER BY ID ASC LIMIT 1";
				$row = $wpdb->get_row($sql);
				if ($row) {
					$path = $post->post_name . '/';
					while ($row->post_parent) {
						$sql = "SELECT post_status, post_name, post_parent FROM " . $wpdb->posts
							. " WHERE ID = " . $row->post_parent;
						$row = $wpdb->get_row($sql);
						if ($row->post_status != 'publish') {
							$publish = false;
							break;
						}
						$path = $row->post_name . "/$path";
					}
				}
				if ($path and $publish) {
					wp_redirect(home_url("/$path"), 301);
					exit;
				} else {
					wp_redirect(home_url(), 302);
					exit;
		    }
			}
	  }
	}
	add_action('template_redirect', 'aprx_template_redirect', 1);
}

?>
