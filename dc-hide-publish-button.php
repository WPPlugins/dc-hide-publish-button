<?php /*

**************************************************************************

Plugin Name:  DC Hide Publish Button
Plugin URI:   https://donisusanto.net/wordpress/plugins/dc-hide-publish-button.html
Description:  Avoid accidentally publish post/page
Version:      2.0.0
Author:       Doni Susanto
Author URI:   https://donisusanto.net
License:      GPL V2+

**************************************************************************/

defined('ABSPATH') or die();

define("my_dir", dirname(__FILE__).'/');

require my_dir."class.php";


/**************************************************************************/
/* main */

add_action('plugins_loaded', 'dc_hide_publish_button_init');
function dc_hide_publish_button_init() {
	$dchpb = new dc_hide_publish_button();
}

/* end main */

?>