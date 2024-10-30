<?php
/*
Plugin Name: CSV Posts Importer
Description: Plugin imports posts from CSV file, allowing to connect CSV field with post field when creating a post.
Version: 1.0.0
Author: BWT group
Author URI: http://www.groupbwt.com/
License: GPLv2 or later
*/

/*
Copyright 2017 BWT group (email : management@groupbwt.com)

This program is free software; you can redistribute it and/or
modify it under the terms of the GNU General Public License
as published by the Free Software Foundation; either version 2
of the License, or (at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
*/

require_once( plugin_dir_path( __FILE__ ) . 'class-bwt-csv-importer.php' );

define( 'CIP_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'CIP_PLUGIN_URL', plugins_url( '', __FILE__ ) );

register_activation_hook( __FILE__, [ 'Bwt_Csv_Importer', 'on_activation' ] );
register_deactivation_hook ( __FILE__, [ 'Bwt_Csv_Importer', 'on_deactivate' ] );

add_action( 'plugins_loaded', array( 'Bwt_Csv_Importer', 'init' ) );
