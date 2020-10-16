<?php
/**
 * Plugin Name
 *
 * @package           Realm Digital Wishes
 * @author            Realm
 * @copyright         Realm
 * @license           GPL-2.0-or-later
 *
 * @wordpress-plugin
 * Plugin Name:       Realm Digital Wishes
 * Plugin URI:        #
 * Description:       Realm Digital Wishes.
 * Version:           1.3
 * Requires at least: 5.0
 * Requires PHP:      5.0
 * Author:            Realm
 * Author URI:        #
 * Text Domain:       realm
 * License:           GPL v2 or later
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt

*/

// Make sure we don't expose any info if called directly
if ( !function_exists( 'add_action' ) ) {
	echo __('Hi there!  I\'m just a plugin, not much I can do when called directly.', 'realm');
	exit;
}

/* Plugin Constants */
if (!defined('REALM_WHISHES_URL')) {
    define('REALM_WHISHES_URL', plugin_dir_url(__FILE__));
}

if (!defined('REALM_WHISHES_PLUGIN_PATH')) {
    define('REALM_WHISHES_PLUGIN_PATH', plugin_dir_path(__FILE__));
}

/* Api Constant */
define( 'REALM_EMPLOYEES_API', "https://interview-assessment-1.realmdigital.co.za/employees");
define( 'REALM_DO_NOT_SEND_BIRTHDAY_EMAIL_API', "https://interview-assessment-1.realmdigital.co.za/do-not-send-birthday-wishes");

require_once (REALM_WHISHES_PLUGIN_PATH . '/includes/settings.php');

register_activation_hook( __FILE__, array('Realm_Wishes_Settings','realm_activation_hook') );

register_activation_hook( __FILE__, array('Realm_Wishes_Settings','realm_deactivation_hook') );


/**
 * MAIN CLASS
 */
class Realm_Whishes 
{
	function __construct()
	{
		Realm_Wishes_Settings::settings_init();
	}

}

$post_view = new Realm_Whishes();