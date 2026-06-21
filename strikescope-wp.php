<?php
/**
 * Plugin Name: StrikeScopeWP
 * Plugin URI:  https://github.com/GatoGodMode/strikescope-wp
 * Description: Multi-step contact forms, lead CRM, Help Desk, and email automation.
 * Version:     1.0.0
 * Author:      uncooluser
 * Author URI:  https://profiles.wordpress.org/uncooluser/
 * License:     GPL-2.0-or-later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: strikescope-wp
 * Requires at least: 6.0
 * Requires PHP: 7.4
 */

if (!defined('ABSPATH')) {
	exit;
}

define('STRIKESCOPE_WP_VERSION', '1.0.0');
define('STRIKESCOPE_WP_FILE', __FILE__);
define('STRIKESCOPE_WP_DIR', plugin_dir_path(__FILE__));
define('STRIKESCOPE_WP_URL', plugin_dir_url(__FILE__));
define('STRIKESCOPE_SETTINGS_OPTION', 'strikescope_settings');
define('STRIKESCOPE_BRANDING_OPTION', 'strikescope_branding');
define('STRIKESCOPE_LEAD_CPT', 'strikescope_lead');
define('STRIKESCOPE_HELP_TICKET_CPT', 'strikescope_help_ticket');

require_once STRIKESCOPE_WP_DIR . 'includes/class-branding.php';
require_once STRIKESCOPE_WP_DIR . 'includes/class-plugin.php';

register_activation_hook(__FILE__, array('StrikeScope_Plugin', 'activate'));
register_deactivation_hook(__FILE__, array('StrikeScope_Plugin', 'deactivate'));

StrikeScope_Plugin::instance();
