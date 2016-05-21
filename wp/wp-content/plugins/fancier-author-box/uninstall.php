<?php
/**
 * Handles plugin deactivation
 *
 * @link  https://wordpress.org/plugins/fancier-author-box/
 * @since 1.0
 *
 * @package    Fancier_Author_Box
 */

// If uninstall is not called from WordPress exit 
if( ! defined( 'WP_UNINSTALL_PLUGIN' ) )
	exit ();

// Delete options from options table ONLY if Fanciest Author Box version of plugin is not in plugins directory
if( ! file_exists( WP_PLUGIN_DIR . '/fanciest-author-box/ts-fab.php' ) ) {
	delete_option( 'ts_fab_display_settings' );
	delete_option( 'ts_fab_tabs_settings' );
}