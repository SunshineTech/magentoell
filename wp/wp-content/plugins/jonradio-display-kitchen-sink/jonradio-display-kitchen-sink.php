<?php
/*
Plugin Name: jonradio Display Kitchen Sink
Plugin URI: http://zatzlabs.com/plugins/
Description: All users will have the Kitchen Sink displayed in Visual mode for both the Page and Post Editors.
Version: 3.1
Author: David Gewirtz
Author URI: http://zatzlabs.com/plugins/
License: GPLv2
*/

/*  Copyright 2014  jonradio  (email : info@zatz.com)

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation; either version 2 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

/*	Exit if .php file accessed directly
*/
if ( !defined( 'ABSPATH' ) ) {
	exit;
}

if ( is_admin() ) {
	DEFINE( 'JR_DKS__FILE__', __FILE__ );

	require_once( plugin_dir_path( JR_DKS__FILE__ ) . 'includes/admin.php' );
}

?>