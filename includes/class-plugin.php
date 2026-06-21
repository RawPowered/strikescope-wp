<?php
/**
 * StrikeScopeWP bootstrap loader.
 */

if (!defined('ABSPATH')) {
	exit;
}

final class StrikeScope_Plugin {
	private static ?StrikeScope_Plugin $instance = null;

	public static function instance(): StrikeScope_Plugin {
		if (self::$instance === null) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	private function __construct() {
		$this->load_modules();
		add_action('init', array($this, 'register_shortcode'));
		add_filter('strikescope_form_should_enqueue', array($this, 'should_enqueue_assets'));
		add_filter('strikescope_form_asset_urls', array($this, 'plugin_asset_urls'));
		add_action('admin_menu', array($this, 'register_branding_menu'), 20);
		require_once STRIKESCOPE_WP_DIR . 'includes/class-privacy.php';
		StrikeScope_Branding::init();
		StrikeScope_Privacy::init();
	}

	private function load_modules(): void {
		$files = array(
			'blacklist.php',
			'status.php',
			'lead-data.php',
			'sales-agent.php',
			'export.php',
			'location.php',
			'scheduler.php',
			'help-ticket-core.php',
			'help-ticket-email.php',
			'integrations.php',
			'admin-leads.php',
			'help-ticket-admin.php',
			'admin-settings.php',
			'lead-form-core.php',
		);
		foreach ($files as $file) {
			$path = STRIKESCOPE_WP_DIR . 'includes/' . $file;
			if (is_readable($path)) {
				require_once $path;
			}
		}
	}

	public static function activate(): void {
		if (function_exists('strikescope_form_defaults') && !get_option(STRIKESCOPE_SETTINGS_OPTION)) {
			update_option(STRIKESCOPE_SETTINGS_OPTION, strikescope_form_defaults(), false);
		}
		if (!get_option(STRIKESCOPE_BRANDING_OPTION)) {
			update_option(STRIKESCOPE_BRANDING_OPTION, StrikeScope_Branding::default_branding(), false);
		}
		StrikeScope_Branding::maybe_migrate_legacy_contact_settings();
		if (function_exists('strikescope_form_register_sales_agent_role')) {
			strikescope_form_register_sales_agent_role();
		}
		if (!wp_next_scheduled('strikescope_lead_aging_cron')) {
			wp_schedule_event(time() + HOUR_IN_SECONDS, 'daily', 'strikescope_lead_aging_cron');
		}
		if (!wp_next_scheduled('strikescope_appointment_reminders_cron')) {
			wp_schedule_event(time() + 900, 'strikescope_15min', 'strikescope_appointment_reminders_cron');
		}
		flush_rewrite_rules();
	}

	public static function deactivate(): void {
		wp_clear_scheduled_hook('strikescope_lead_aging_cron');
		wp_clear_scheduled_hook('strikescope_appointment_reminders_cron');
		flush_rewrite_rules();
	}

	public function register_shortcode(): void {
		add_shortcode(
			'strikescope_form',
			static function ($atts) {
				$atts = shortcode_atts(array('id' => 'strikescope-form-root'), $atts, 'strikescope_form');
				$id   = sanitize_html_class((string) $atts['id']);
				if ($id === '') {
					$id = 'strikescope-form-root';
				}
				return '<div id="' . esc_attr($id) . '" class="strikescope-form-shortcode"></div>';
			}
		);
	}

	public function should_enqueue_assets($should): bool {
		if ($should) {
			return true;
		}
		if (is_singular() && has_shortcode((string) get_post()->post_content, 'strikescope_form')) {
			return true;
		}
		return (bool) $should;
	}

	public function plugin_asset_urls(array $urls): array {
		$js = STRIKESCOPE_WP_URL . 'assets/js/strikescope-form.js';
		$css = STRIKESCOPE_WP_URL . 'assets/css/strikescope-form.css';
		if (file_exists(STRIKESCOPE_WP_DIR . 'assets/js/strikescope-form.js')) {
			$urls['js_url'] = $js;
		}
		if (file_exists(STRIKESCOPE_WP_DIR . 'assets/css/strikescope-form.css')) {
			$urls['css_url'] = $css;
		}
		$urls['js_version'] = STRIKESCOPE_WP_VERSION;
		return $urls;
	}

	public function register_branding_menu(): void {
		add_submenu_page(
			'strikescope-wp',
			__('Branding', 'strikescope-wp'),
			__('Branding', 'strikescope-wp'),
			'manage_options',
			'strikescope-branding',
			array('StrikeScope_Branding', 'render_page')
		);
	}
}
