if (!defined('ABSPATH')) {
	return;
}

define('STRIKESCOPE_SETTINGS_OPTION', 'strikescope_settings');
define('STRIKESCOPE_LEAD_CPT', 'strikescope_lead');

function strikescope_form_defaults() {
	return array(
		'primary_email'       => get_option('admin_email'),
		'cc_emails'           => '',
		'service_routes'      => array(),
		'turnstile_site_key'  => '',
		'turnstile_secret_key'=> '',
		'rate_limit_per_hour' => 5,
		'js_url'              => '',
		'css_url'             => '',
		'js_version'          => '',
		'js_inline'           => '',
		'css_inline'          => '',
		'customer_follow_up_enabled' => 1,
		'customer_follow_up_subject' => 'Thanks for contacting us — we\'ll be in touch soon',
		'elemailer_template_id'      => 0,
		'customer_from_name'         => '',
		'customer_reply_to'          => '',
		'customer_service_templates' => strikescope_form_default_service_template_map(),
		'admin_service_templates'    => strikescope_form_default_service_template_map(),
		'blacklist_emails'           => '',
		'blacklist_zips'             => '',
		'blacklist_cities'           => '',
		'blacklist_states'           => '',
		'scheduler_enabled'          => 0,
		'scheduler_auto_status'      => 1,
		'api_keys'                   => array(),
		'webhooks'                   => array(),
		'integration_log'            => array(),
		'google_calendar_client_id'     => '',
		'google_calendar_client_secret' => '',
		'outlook_tenant_id'             => '',
		'outlook_client_id'             => '',
		'outlook_client_secret'         => '',
		'help_desk_enabled'             => 1,
		'help_desk_support_email'       => '',
		'help_desk_inbound_domain'      => 'inbound.%%SITE_URL%%',
		'help_desk_inbound_secret'      => '',
		'help_desk_from_name'           => 'Help Desk',
		'help_desk_email_templates'     => array(),
		'service_types_enabled'         => strikescope_form_default_service_types_enabled(),
	);
}

function strikescope_form_get_settings() {
	$stored = get_option(STRIKESCOPE_SETTINGS_OPTION, array());
	if (!is_array($stored)) {
		$stored = array();
	}
	return wp_parse_args($stored, strikescope_form_defaults());
}

function strikescope_form_update_settings(array $patch) {
	$settings = strikescope_form_get_settings();
	$merged   = array_merge($settings, $patch);
	update_option(STRIKESCOPE_SETTINGS_OPTION, $merged, false);
	return $merged;
}

function strikescope_form_client_ip() {
	$ip = isset($_SERVER['REMOTE_ADDR']) ? sanitize_text_field(wp_unslash($_SERVER['REMOTE_ADDR'])) : '';
	return $ip ? $ip : '0.0.0.0';
}

function strikescope_form_service_ids() {
	return array('roofing', 'kitchen', 'bathroom', 'addons', 'solar');
}

function strikescope_form_default_service_types_enabled() {
	$map = array();
	foreach (strikescope_form_service_ids() as $service_id) {
		$map[ $service_id ] = 1;
	}
	return $map;
}

function strikescope_form_enabled_service_ids() {
	$settings    = strikescope_form_get_settings();
	$enabled_map = isset($settings['service_types_enabled']) && is_array($settings['service_types_enabled'])
		? $settings['service_types_enabled']
		: strikescope_form_default_service_types_enabled();
	$ids = array();
	foreach (strikescope_form_service_ids() as $service_id) {
		if (!empty($enabled_map[ $service_id ])) {
			$ids[] = $service_id;
		}
	}
	return $ids;
}

function strikescope_form_allowed_services() {
	return strikescope_form_enabled_service_ids();
}

function strikescope_form_email_template_keys() {
	return array_merge(strikescope_form_service_ids(), array('multi'));
}

function strikescope_form_sanitize_service_types_enabled($input) {
	$input = is_array($input) ? $input : array();
	$out   = array();
	foreach (strikescope_form_service_ids() as $service_id) {
		$out[ $service_id ] = !empty($input[ $service_id ]) ? 1 : 0;
	}
	return $out;
}

function strikescope_form_allowed_property_types() {
	return array('residential', 'commercial');
}

function strikescope_form_allowed_timelines() {
	return array('asap', '1-3months', '3-6months', '6plus', 'just-exploring');
}

function strikescope_form_allowed_budgets() {
	return array('under-10k', '10k-25k', '25k-50k', '50k-100k', 'over-100k', 'not-sure', '');
}

function strikescope_form_default_service_template_map() {
	$map = array();
	foreach (strikescope_form_email_template_keys() as $service) {
		$map[ $service ] = array(
			'elemailer_id' => 0,
			'subject'      => '',
		);
	}
	return $map;
}

function strikescope_form_service_email_catalog() {
	return array(
		'roofing'  => array(
			'label'                    => 'Roofing',
			'customer_subject_default' => 'Thanks for contacting %%COMPANY_NAME%% about your roofing project',
			'admin_subject_default'    => 'New Roofing lead — %%FULL_NAME%%',
			'customer_intro'           => 'Thank you for your interest in roofing services. Our licensed roofing team will review your project and follow up to discuss repair, replacement, and storm-ready options for your property.',
		),
		'kitchen'  => array(
			'label'                    => 'Kitchen Remodeling',
			'customer_subject_default' => 'Thanks for contacting %%COMPANY_NAME%% about your kitchen remodel',
			'admin_subject_default'    => 'New Kitchen Remodeling lead — %%FULL_NAME%%',
			'customer_intro'           => 'Thank you for your interest in kitchen remodeling. Our design-build team will reach out soon to learn more about your layout, finishes, and timeline for your kitchen renovation.',
		),
		'bathroom' => array(
			'label'                    => 'Bathroom Remodeling',
			'customer_subject_default' => 'Thanks for contacting %%COMPANY_NAME%% about your bathroom remodel',
			'admin_subject_default'    => 'New Bathroom Remodeling lead — %%FULL_NAME%%',
			'customer_intro'           => 'Thank you for your interest in bathroom remodeling. A %%COMPANY_NAME%% specialist will contact you to discuss upgrades, waterproofing, fixtures, and the scope of your bathroom project.',
		),
		'addons'   => array(
			'label'                    => 'Home Additions',
			'customer_subject_default' => 'Thanks for contacting %%COMPANY_NAME%% about your home addition',
			'admin_subject_default'    => 'New Home Additions lead — %%FULL_NAME%%',
			'customer_intro'           => 'Thank you for your interest in expanding your living space. Our additions team will follow up to discuss square footage, permitting, and the best approach for your home addition.',
		),
		'solar'    => array(
			'label'                    => 'Commercial Solar',
			'customer_subject_default' => 'Thanks for contacting %%COMPANY_NAME%% about commercial solar',
			'admin_subject_default'    => 'New Commercial Solar lead — %%FULL_NAME%%',
			'customer_intro'           => 'Thank you for your interest in commercial solar. Our solar team will contact you to review your facility, energy goals, and next steps for a site-specific proposal.',
		),
		'multi'    => array(
			'label'                    => 'Multiple Services',
			'customer_subject_default' => 'Thanks for contacting %%COMPANY_NAME%% about your projects',
			'admin_subject_default'    => 'New multi-service lead — %%FULL_NAME%%',
			'customer_intro'           => 'Thank you for reaching out about multiple home improvement projects. Our team will review everything you selected and follow up with one coordinated plan for your property.',
		),
	);
}

function strikescope_form_sanitize_service_templates($input, $current) {
	$out = strikescope_form_default_service_template_map();
	if (!is_array($input)) {
		return is_array($current) ? $current : $out;
	}
	foreach (strikescope_form_email_template_keys() as $service) {
		if (!isset($input[ $service ]) || !is_array($input[ $service ])) {
			continue;
		}
		$row = $input[ $service ];
		$out[ $service ] = array(
			'elemailer_id' => isset($row['elemailer_id']) ? absint($row['elemailer_id']) : 0,
			'subject'      => isset($row['subject']) ? sanitize_text_field($row['subject']) : '',
		);
	}
	return $out;
}

function strikescope_form_primary_service(array $services) {
	$services = array_values(array_filter($services));
	return !empty($services) ? sanitize_key($services[0]) : 'roofing';
}

function strikescope_form_customer_email_service(array $services) {
	$services = array_values(array_filter($services));
	if (count($services) > 1) {
		return 'multi';
	}
	return strikescope_form_primary_service($services);
}

function strikescope_form_is_email_template_key($service) {
	return in_array(sanitize_key($service), strikescope_form_email_template_keys(), true);
}

function strikescope_form_has_header_injection($value) {
	return is_string($value) && preg_match('/[\r\n]/', $value);
}

function strikescope_form_parse_service_routes($raw) {
	if (is_array($raw)) {
		return $raw;
	}
	$routes = array();
	foreach (preg_split('/\r\n|\r|\n/', (string) $raw) as $line) {
		$line = trim($line);
		if ($line === '' || strpos($line, ':') === false) {
			continue;
		}
		list($service, $email) = array_map('trim', explode(':', $line, 2));
		if ($service !== '' && is_email($email)) {
			$routes[sanitize_key($service)] = sanitize_email($email);
		}
	}
	return $routes;
}

function strikescope_form_parse_cc_emails($raw) {
	$emails = array();
	foreach (preg_split('/[,;\s]+/', (string) $raw) as $part) {
		$part = trim($part);
		if ($part !== '' && is_email($part)) {
			$emails[] = sanitize_email($part);
		}
	}
	return array_values(array_unique($emails));
}

function strikescope_form_is_contact_page() {
	if (apply_filters('strikescope_form_should_enqueue', false)) {
		return true;
	}
	$slug = StrikeScope_Branding::contact_page_slug();
	if ($slug === '') {
		return false;
	}
	if (is_page($slug)) {
		return true;
	}
	$path = isset($_SERVER['REQUEST_URI']) ? wp_parse_url(wp_unslash($_SERVER['REQUEST_URI']), PHP_URL_PATH) : '';
	$path = trim((string) $path, '/');
	return $path === $slug;
}

function strikescope_form_write_asset_files($js_code, $css_code) {
	$upload = wp_upload_dir();
	if (!empty($upload['error'])) {
		return new WP_Error('upload_dir', $upload['error'], array('status' => 500));
	}

	$dir = trailingslashit($upload['basedir']) . 'strikescope-form';
	if (!wp_mkdir_p($dir)) {
		return new WP_Error('mkdir_failed', 'Could not create lead form asset directory.', array('status' => 500));
	}

	$index = trailingslashit($dir) . 'index.php';
	if (!file_exists($index)) {
		file_put_contents($index, "<?php\n// Silence is golden.\n");
	}

	$js_path  = trailingslashit($dir) . 'strikescope-form.js';
	$css_path = trailingslashit($dir) . 'strikescope-form.css';

	if ($js_code === '' || file_put_contents($js_path, $js_code) === false) {
		return new WP_Error('write_failed', 'Could not write lead form JavaScript asset.', array('status' => 500));
	}

	if ($css_code !== '' && file_put_contents($css_path, $css_code) === false) {
		return new WP_Error('write_failed', 'Could not write lead form CSS asset.', array('status' => 500));
	}

	$email_path = trailingslashit($dir) . 'lead-customer-follow-up.html';
	if (isset($GLOBALS['strikescope_form_email_html']) && is_string($GLOBALS['strikescope_form_email_html']) && $GLOBALS['strikescope_form_email_html'] !== '') {
		file_put_contents($email_path, $GLOBALS['strikescope_form_email_html']);
	}

	if (isset($GLOBALS['strikescope_form_email_templates']) && is_array($GLOBALS['strikescope_form_email_templates'])) {
		foreach (array('customer', 'admin') as $type) {
			if (empty($GLOBALS['strikescope_form_email_templates'][ $type ]) || !is_array($GLOBALS['strikescope_form_email_templates'][ $type ])) {
				continue;
			}
			$type_dir = trailingslashit($dir) . 'emails/' . $type;
			wp_mkdir_p($type_dir);
			foreach ($GLOBALS['strikescope_form_email_templates'][ $type ] as $service => $html) {
				$service = sanitize_key($service);
				if ($service === '' || !is_string($html) || $html === '') {
					continue;
				}
				file_put_contents(trailingslashit($type_dir) . $service . '.html', $html);
			}
		}
	}

	if (isset($GLOBALS['strikescope_help_ticket_email_templates']) && is_array($GLOBALS['strikescope_help_ticket_email_templates'])) {
		$ht_dir = trailingslashit($dir) . 'emails/help-ticket';
		wp_mkdir_p($ht_dir);
		wp_mkdir_p(trailingslashit($ht_dir) . 'status');
		foreach ($GLOBALS['strikescope_help_ticket_email_templates'] as $key => $html) {
			$key = sanitize_key(str_replace('_', '-', (string) $key));
			if ($key === '' || !is_string($html) || $html === '') {
				continue;
			}
			if (strpos($key, 'status-') === 0) {
				$slug = substr($key, 7);
				file_put_contents(trailingslashit($ht_dir) . 'status/' . $slug . '.html', $html);
			} else {
				file_put_contents(trailingslashit($ht_dir) . $key . '.html', $html);
			}
		}
	}

	return array(
		'js_url'  => trailingslashit($upload['baseurl']) . 'strikescope-form/strikescope-form.js',
		'css_url' => $css_code !== '' ? trailingslashit($upload['baseurl']) . 'strikescope-form/strikescope-form.css' : '',
		'email_url' => file_exists($email_path) ? trailingslashit($upload['baseurl']) . 'strikescope-form/lead-customer-follow-up.html' : '',
	);
}

function strikescope_form_asset_status_label(array $settings) {
	if (!empty($settings['js_url'])) {
		return 'Deployed to uploads folder';
	}
	if (!empty($settings['js_inline'])) {
		return 'Inline fallback (legacy)';
	}
	return 'Not deployed yet';
}

add_action('init', static function () {
	register_post_type(
		STRIKESCOPE_LEAD_CPT,
		array(
			'labels'              => array(
				'name'          => 'Leads',
				'singular_name' => 'Lead',
				'menu_name'     => 'Leads',
			),
			'public'              => false,
			'show_ui'             => true,
			'show_in_menu'        => 'strikescope-wp',
			'show_in_rest'        => false,
			'exclude_from_search' => true,
			'publicly_queryable'  => false,
			'has_archive'         => false,
			'capability_type'     => 'post',
			'map_meta_cap'        => true,
			'supports'            => array('title'),
			'menu_icon'           => 'dashicons-email-alt',
		)
	);
});

add_action('admin_init', static function () {
	register_setting(
		'strikescope_form',
		STRIKESCOPE_SETTINGS_OPTION,
		array(
			'type'              => 'array',
			'sanitize_callback' => 'strikescope_form_sanitize_settings',
			'default'           => strikescope_form_defaults(),
		)
	);
});

function strikescope_form_sanitize_settings($input) {
	$defaults = strikescope_form_defaults();
	$input    = is_array($input) ? $input : array();
	$current  = strikescope_form_get_settings();

	$primary = isset($input['primary_email']) ? sanitize_email($input['primary_email']) : $defaults['primary_email'];
	if (!is_email($primary)) {
		$primary = sanitize_email($defaults['primary_email']);
	}

	$out = array(
		'primary_email'        => $primary,
		'cc_emails'            => isset($input['cc_emails']) ? sanitize_textarea_field($input['cc_emails']) : $current['cc_emails'],
		'service_routes'       => isset($input['service_routes']) ? strikescope_form_parse_service_routes($input['service_routes']) : $current['service_routes'],
		'turnstile_site_key'   => isset($input['turnstile_site_key']) ? sanitize_text_field($input['turnstile_site_key']) : $current['turnstile_site_key'],
		'turnstile_secret_key' => isset($input['turnstile_secret_key']) ? sanitize_text_field($input['turnstile_secret_key']) : $current['turnstile_secret_key'],
		'rate_limit_per_hour'  => isset($input['rate_limit_per_hour']) ? max(1, min(50, absint($input['rate_limit_per_hour']))) : (int) $current['rate_limit_per_hour'],
		'js_url'               => isset($input['js_url']) ? esc_url_raw($input['js_url']) : $current['js_url'],
		'css_url'              => isset($input['css_url']) ? esc_url_raw($input['css_url']) : $current['css_url'],
		'js_version'           => isset($input['js_version']) ? sanitize_text_field($input['js_version']) : $current['js_version'],
		'js_inline'            => isset($input['js_inline']) ? (string) $input['js_inline'] : $current['js_inline'],
		'css_inline'           => isset($input['css_inline']) ? (string) $input['css_inline'] : $current['css_inline'],
		'customer_follow_up_enabled' => !empty($input['customer_follow_up_enabled']) ? 1 : 0,
		'customer_follow_up_subject' => isset($input['customer_follow_up_subject']) ? sanitize_text_field($input['customer_follow_up_subject']) : $current['customer_follow_up_subject'],
		'elemailer_template_id'      => isset($input['elemailer_template_id']) ? absint($input['elemailer_template_id']) : (int) $current['elemailer_template_id'],
		'customer_from_name'         => isset($input['customer_from_name']) ? sanitize_text_field($input['customer_from_name']) : $current['customer_from_name'],
		'customer_reply_to'          => isset($input['customer_reply_to']) ? sanitize_email($input['customer_reply_to']) : $current['customer_reply_to'],
		'customer_service_templates' => strikescope_form_sanitize_service_templates(
			isset($input['customer_service_templates']) ? $input['customer_service_templates'] : null,
			isset($current['customer_service_templates']) ? $current['customer_service_templates'] : null
		),
		'admin_service_templates'    => strikescope_form_sanitize_service_templates(
			isset($input['admin_service_templates']) ? $input['admin_service_templates'] : null,
			isset($current['admin_service_templates']) ? $current['admin_service_templates'] : null
		),
		'blacklist_emails'           => isset($input['blacklist_emails']) ? sanitize_textarea_field($input['blacklist_emails']) : ($current['blacklist_emails'] ?? ''),
		'blacklist_zips'             => isset($input['blacklist_zips']) ? sanitize_textarea_field($input['blacklist_zips']) : ($current['blacklist_zips'] ?? ''),
		'blacklist_cities'           => isset($input['blacklist_cities']) ? sanitize_textarea_field($input['blacklist_cities']) : ($current['blacklist_cities'] ?? ''),
		'blacklist_states'           => isset($input['blacklist_states']) ? sanitize_textarea_field($input['blacklist_states']) : ($current['blacklist_states'] ?? ''),
		'scheduler_enabled'          => !empty($input['scheduler_enabled']) ? 1 : 0,
		'scheduler_auto_status'      => !empty($input['scheduler_auto_status']) ? 1 : 0,
		'google_calendar_client_id'     => isset($input['google_calendar_client_id']) ? sanitize_text_field($input['google_calendar_client_id']) : ($current['google_calendar_client_id'] ?? ''),
		'google_calendar_client_secret' => isset($input['google_calendar_client_secret']) ? sanitize_text_field($input['google_calendar_client_secret']) : ($current['google_calendar_client_secret'] ?? ''),
		'outlook_tenant_id'             => isset($input['outlook_tenant_id']) ? sanitize_text_field($input['outlook_tenant_id']) : ($current['outlook_tenant_id'] ?? ''),
		'outlook_client_id'             => isset($input['outlook_client_id']) ? sanitize_text_field($input['outlook_client_id']) : ($current['outlook_client_id'] ?? ''),
		'outlook_client_secret'         => isset($input['outlook_client_secret']) ? sanitize_text_field($input['outlook_client_secret']) : ($current['outlook_client_secret'] ?? ''),
		'help_desk_enabled'             => !empty($input['help_desk_enabled']) ? 1 : 0,
		'help_desk_support_email'       => isset($input['help_desk_support_email']) ? sanitize_email($input['help_desk_support_email']) : ($current['help_desk_support_email'] ?? ''),
		'help_desk_inbound_domain'      => isset($input['help_desk_inbound_domain']) ? sanitize_text_field($input['help_desk_inbound_domain']) : ($current['help_desk_inbound_domain'] ?? 'inbound.%%SITE_URL%%'),
		'help_desk_from_name'           => isset($input['help_desk_from_name']) ? sanitize_text_field($input['help_desk_from_name']) : ($current['help_desk_from_name'] ?? 'Help Desk'),
		'help_desk_inbound_secret'      => !empty($current['help_desk_inbound_secret']) ? (string) $current['help_desk_inbound_secret'] : '',
		'service_types_enabled'         => array_key_exists('service_types_enabled', $input)
			? strikescope_form_sanitize_service_types_enabled($input['service_types_enabled'])
			: (isset($current['service_types_enabled']) && is_array($current['service_types_enabled'])
				? $current['service_types_enabled']
				: strikescope_form_default_service_types_enabled()),
	);

	if (function_exists('strikescope_help_ticket_sanitize_email_templates')) {
		$out['help_desk_email_templates'] = strikescope_help_ticket_sanitize_email_templates(
			isset($input['help_desk_email_templates']) ? $input['help_desk_email_templates'] : null,
			isset($current['help_desk_email_templates']) ? $current['help_desk_email_templates'] : null
		);
	} else {
		$out['help_desk_email_templates'] = isset($current['help_desk_email_templates']) && is_array($current['help_desk_email_templates'])
			? $current['help_desk_email_templates']
			: array();
	}

	$out['api_keys'] = isset($current['api_keys']) && is_array($current['api_keys']) ? $current['api_keys'] : array();
	if (isset($input['webhooks']) && is_array($input['webhooks'])) {
		$hooks = array();
		foreach ($input['webhooks'] as $hook) {
			if (!is_array($hook)) {
				continue;
			}
			$events = isset($hook['events']) && is_array($hook['events']) ? array_map('sanitize_text_field', $hook['events']) : array();
			$hooks[] = array(
				'label'      => sanitize_text_field($hook['label'] ?? ''),
				'url'        => esc_url_raw($hook['url'] ?? ''),
				'secret'     => sanitize_text_field($hook['secret'] ?? ''),
				'enabled'    => !empty($hook['enabled']) ? 1 : 0,
				'events'     => $events,
				'field_map'  => isset($hook['field_map']) ? wp_kses_post($hook['field_map']) : '',
			);
		}
		$out['webhooks'] = $hooks;
	} else {
		$out['webhooks'] = isset($current['webhooks']) && is_array($current['webhooks']) ? $current['webhooks'] : array();
	}
	$out['integration_log'] = isset($current['integration_log']) && is_array($current['integration_log']) ? $current['integration_log'] : array();

	if (is_string($input['service_routes'] ?? null)) {
		$out['service_routes'] = strikescope_form_parse_service_routes($input['service_routes']);
	}

	return $out;
}


function strikescope_form_error($code, $message, $status = 400) {
	return new WP_Error($code, $message, array('status' => $status));
}

function strikescope_form_verify_turnstile($token, $secret) {
	if ($secret === '') {
		return true;
	}
	if ($token === '') {
		return false;
	}
	$response = wp_remote_post(
		'https://challenges.cloudflare.com/turnstile/v0/siteverify',
		array(
			'timeout' => 12,
			'body'    => array(
				'secret'   => $secret,
				'response' => $token,
				'remoteip' => strikescope_form_client_ip(),
			),
		)
	);
	if (is_wp_error($response)) {
		return false;
	}
	$data = json_decode(wp_remote_retrieve_body($response), true);
	return is_array($data) && !empty($data['success']);
}

function strikescope_form_check_rate_limit($limit) {
	$ip_hash = hash('sha256', strikescope_form_client_ip());
	$key     = 'strikescope_lead_rl_' . $ip_hash;
	$count   = (int) get_transient($key);
	if ($count >= $limit) {
		return false;
	}
	set_transient($key, $count + 1, HOUR_IN_SECONDS);
	return true;
}

function strikescope_form_validate_payload(array $params) {
	$clean = array();

	$services = isset($params['serviceType']) && is_array($params['serviceType']) ? $params['serviceType'] : array();
	$services = array_values(array_unique(array_filter(array_map('sanitize_key', $services))));
	$services = array_values(array_intersect($services, strikescope_form_allowed_services()));
	if (!$services) {
		return strikescope_form_error('validation_error', 'Select at least one service.');
	}
	$clean['serviceType'] = $services;

	$property = isset($params['propertyType']) ? sanitize_key($params['propertyType']) : '';
	if (!in_array($property, strikescope_form_allowed_property_types(), true)) {
		return strikescope_form_error('validation_error', 'Invalid property type.');
	}
	$clean['propertyType'] = $property;

	$timeline = isset($params['timeline']) ? sanitize_key($params['timeline']) : '';
	if (!in_array($timeline, strikescope_form_allowed_timelines(), true)) {
		return strikescope_form_error('validation_error', 'Invalid project timeline.');
	}
	$clean['timeline'] = $timeline;

	$budget = isset($params['budget']) ? sanitize_key($params['budget']) : '';
	if (!in_array($budget, strikescope_form_allowed_budgets(), true)) {
		return strikescope_form_error('validation_error', 'Invalid budget selection.');
	}
	$clean['budget'] = $budget;

	$fields = array(
		'firstName'         => 80,
		'lastName'          => 80,
		'email'             => 190,
		'phone'             => 40,
		'address'           => 200,
		'city'              => 100,
		'state'             => 40,
		'zipCode'           => 20,
		'additionalDetails' => 5000,
	);
	foreach ($fields as $field => $max) {
		$value = isset($params[$field]) ? sanitize_text_field((string) $params[$field]) : '';
		if (strikescope_form_has_header_injection($value)) {
			return strikescope_form_error('validation_error', 'Invalid characters in form submission.');
		}
		if ($field !== 'additionalDetails' && $value === '') {
			return strikescope_form_error('validation_error', 'Please complete all required fields.');
		}
		if (strlen($value) > $max) {
			return strikescope_form_error('validation_error', 'One or more fields is too long.');
		}
		$clean[$field] = $value;
	}

	if (!is_email($clean['email'])) {
		return strikescope_form_error('validation_error', 'Enter a valid email address.');
	}
	if (!preg_match('/^[0-9+\-().\s]{7,40}$/', $clean['phone'])) {
		return strikescope_form_error('validation_error', 'Enter a valid phone number.');
	}
	if (!preg_match('/^[0-9\-]{4,20}$/', $clean['zipCode'])) {
		return strikescope_form_error('validation_error', 'Enter a valid ZIP code.');
	}

	$settings = strikescope_form_get_settings();
	if (strikescope_form_check_blacklist($clean, $settings)) {
		return strikescope_form_error('validation_error', 'Please review your submission and try again.', 400);
	}

	return $clean;
}

function strikescope_form_resolve_recipients(array $services, array $settings) {
	$to = array(sanitize_email($settings['primary_email']));
	foreach ($services as $service) {
		if (!empty($settings['service_routes'][$service])) {
			$to[] = sanitize_email($settings['service_routes'][$service]);
		}
	}
	$to = array_values(array_unique(array_filter($to, 'is_email')));
	$cc = strikescope_form_parse_cc_emails($settings['cc_emails']);
	return array($to, $cc);
}

function strikescope_form_service_labels(array $services) {
	$labels = array(
		'roofing'   => 'Roofing',
		'kitchen'   => 'Kitchen Remodeling',
		'bathroom'  => 'Bathroom Remodeling',
		'addons'    => 'Home Additions',
		'solar'     => 'Commercial Solar',
	);
	$out = array();
	foreach ($services as $service) {
		$out[] = isset($labels[$service]) ? $labels[$service] : $service;
	}
	return $out;
}

function strikescope_form_store_lead(array $clean, array $settings) {
	$name     = trim($clean['firstName'] . ' ' . $clean['lastName']);
	$services = strikescope_form_service_labels($clean['serviceType']);
	$post_id  = wp_insert_post(
		array(
			'post_type'   => STRIKESCOPE_LEAD_CPT,
			'post_status' => 'publish',
			'post_author' => strikescope_form_default_lead_author_id(),
			'post_title'  => $name . ' — ' . implode(', ', $services),
		),
		true
	);
	if (is_wp_error($post_id)) {
		return $post_id;
	}
	update_post_meta($post_id, '_lead_payload', wp_json_encode($clean));
	update_post_meta($post_id, '_lead_services', implode(', ', $services));
	update_post_meta($post_id, '_lead_email', $clean['email']);
	update_post_meta($post_id, '_lead_phone', $clean['phone']);
	update_post_meta($post_id, '_lead_city', $clean['city']);
	update_post_meta($post_id, '_lead_state', strtoupper($clean['state']));
	update_post_meta($post_id, '_lead_zip', $clean['zipCode']);
	update_post_meta($post_id, '_lead_ip_hash', hash('sha256', strikescope_form_client_ip()));
	update_post_meta($post_id, '_lead_submitted_at', (string) time());
	strikescope_form_init_lead_status($post_id);
	strikescope_form_schedule_geocode($post_id);
	if (function_exists('strikescope_form_dispatch_integration_event')) {
		strikescope_form_dispatch_integration_event('lead.created', (int) $post_id, array());
	}
	return $post_id;
}

function strikescope_form_send_mail(array $clean, array $settings, $lead_id = 0) {
	$cc_global = strikescope_form_parse_cc_emails($settings['cc_emails']);
	$any_sent  = false;
	$index     = 0;
	$lead_id   = absint($lead_id);

	foreach ($clean['serviceType'] as $service) {
		$service = sanitize_key($service);
		if ($service === '') {
			continue;
		}

		$to = strikescope_form_resolve_admin_recipients_for_service($service, $settings);
		if (!$to) {
			continue;
		}

		$tags    = strikescope_form_lead_merge_tags($clean, $settings, $service, $lead_id);
		$subject = strikescope_form_resolve_email_subject('admin', $service, $settings, $tags);
		$body    = strikescope_form_build_email_body('admin', $clean, $settings, $service, $lead_id);

		if ($body === '') {
			$body = strikescope_form_build_admin_plain_body($clean);
		}

		$is_html = stripos($body, '<') !== false;
		$headers = array(
			'Content-Type: ' . ($is_html ? 'text/html' : 'text/plain') . '; charset=UTF-8',
			'Reply-To: ' . sanitize_email($clean['email']),
		);
		if ($index === 0 && $cc_global) {
			$headers[] = 'Cc: ' . implode(', ', $cc_global);
		}

		add_filter(
			'wp_mail_from_name',
			static function () {
				return 'StrikeScopeWP';
			}
		);

		$sent = wp_mail($to, $subject, $body, $headers);
		remove_all_filters('wp_mail_from_name');
		if ($sent) {
			$any_sent = true;
		}
		$index++;
	}

	return $any_sent;
}

function strikescope_form_resolve_admin_recipients_for_service($service, array $settings) {
	$to = array(sanitize_email($settings['primary_email']));
	if (!empty($settings['service_routes'][ $service ])) {
		$to[] = sanitize_email($settings['service_routes'][ $service ]);
	}
	return array_values(array_unique(array_filter($to, 'is_email')));
}

function strikescope_form_build_admin_plain_body(array $clean) {
	$service_labels = strikescope_form_service_labels($clean['serviceType']);
	$lines          = array(
		'New lead from %%SITE_URL%% contact form',
		'',
		'Services: ' . implode(', ', $service_labels),
		'Property type: ' . $clean['propertyType'],
		'Timeline: ' . $clean['timeline'],
		'Budget: ' . ($clean['budget'] !== '' ? $clean['budget'] : 'Not provided'),
		'',
		'Name: ' . trim($clean['firstName'] . ' ' . $clean['lastName']),
		'Email: ' . $clean['email'],
		'Phone: ' . $clean['phone'],
		'Address: ' . $clean['address'],
		'City: ' . $clean['city'],
		'State: ' . $clean['state'],
		'ZIP: ' . $clean['zipCode'],
		'',
		'Additional details:',
		$clean['additionalDetails'] !== '' ? $clean['additionalDetails'] : '(none)',
	);
	return implode("\n", $lines);
}

function strikescope_form_merge_tag_keys() {
	return array(
		'%%FIRST_NAME%%',
		'%%LAST_NAME%%',
		'%%FULL_NAME%%',
		'%%SERVICE_LIST%%',
		'%%PRIMARY_SERVICE%%',
		'%%PRIMARY_SERVICE_LABEL%%',
		'%%SERVICE_INTRO%%',
		'%%CITY%%',
		'%%STATE%%',
		'%%PHONE%%',
		'%%PHONE_TEL%%',
		'%%SITE_URL%%',
		'%%SUBMISSION_DATE%%',
		'%%LOGO_URL%%',
		'%%LICENSES_FOOTER%%',
		'%%PROPERTY_TYPE%%',
		'%%TIMELINE%%',
		'%%BUDGET%%',
		'%%LEAD_EMAIL%%',
		'%%LEAD_PHONE%%',
		'%%LEAD_PHONE_TEL%%',
		'%%ADDRESS%%',
		'%%ZIP_CODE%%',
		'%%ADDITIONAL_DETAILS%%',
		'%%GOOGLE_MAPS_URL%%',
		'%%LEAD_ADMIN_URL%%',
		'%%PROPERTY_LOOKUP_HTML%%',
	);
}

function strikescope_form_brand_defaults() {
	$defaults = array(
		'logo_url'         => '',
		'site_url'         => home_url(),
		'licenses_footer'  => 'Licensed & Insured',
	);
	return apply_filters('strikescope_form_brand_defaults', $defaults);
}

function strikescope_form_embedded_email_template() {
	// STRIKESCOPE_LEAD_FORM_EMAIL_TEMPLATE_START
	return <<<'STRIKESCOPE_EMAIL'
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Thanks for contacting %%COMPANY_NAME%%</title>
</head>
<body style="margin:0;padding:0;background-color:%%COLOR_BACKGROUND%%;font-family:Montserrat,Arial,Helvetica,sans-serif;color:#1c1c1c;">
  <table role="presentation" cellpadding="0" cellspacing="0" border="0" width="100%" style="background-color:%%COLOR_BACKGROUND%%;">
    <tr>
      <td align="center" style="padding:32px 16px;">
        <table role="presentation" cellpadding="0" cellspacing="0" border="0" width="600" style="max-width:600px;width:100%;background-color:#ffffff;border-radius:16px;overflow:hidden;border:1px solid %%COLOR_BORDER%%;">
          <tr>
            <td align="center" style="background-color:%%COLOR_SECONDARY%%;padding:28px 24px;border-bottom:3px solid %%COLOR_PRIMARY%%;">
              <img src="%%LOGO_URL%%" alt="%%COMPANY_NAME%%" width="220" style="display:block;max-width:220px;height:auto;border:0;">
            </td>
          </tr>
          <tr>
            <td style="padding:36px 32px 12px;">
              <h1 style="margin:0 0 16px;font-size:28px;line-height:1.25;font-weight:800;color:#1c1c1c;">Thanks, %%FIRST_NAME%%!</h1>
              <p style="margin:0 0 18px;font-size:16px;line-height:1.6;color:#3a2a1a;">
                We received your project request and look forward to working with you. A %%COMPANY_NAME%% representative will contact you soon to collect more details about your project.
              </p>
              <p style="margin:0;font-size:16px;line-height:1.6;color:%%COLOR_MUTED%%;">
                We typically respond within <strong style="color:%%COLOR_PRIMARY%%;">24 hours</strong> during business days.
              </p>
            </td>
          </tr>
          <tr>
            <td style="padding:8px 32px 24px;">
              <table role="presentation" cellpadding="0" cellspacing="0" border="0" width="100%" style="background-color:%%COLOR_SECONDARY%%;border-radius:12px;border:1px solid %%COLOR_BORDER%%;">
                <tr>
                  <td style="padding:20px 22px;">
                    <p style="margin:0 0 10px;font-size:13px;font-weight:700;letter-spacing:0.04em;text-transform:uppercase;color:%%COLOR_MUTED%%;">Your request summary</p>
                    <p style="margin:0 0 8px;font-size:15px;line-height:1.5;color:#1c1c1c;"><strong>Services:</strong> %%SERVICE_LIST%%</p>
                    <p style="margin:0 0 8px;font-size:15px;line-height:1.5;color:#1c1c1c;"><strong>Location:</strong> %%CITY%%, %%STATE%%</p>
                    <p style="margin:0;font-size:15px;line-height:1.5;color:#1c1c1c;"><strong>Submitted:</strong> %%SUBMISSION_DATE%%</p>
                  </td>
                </tr>
              </table>
            </td>
          </tr>
          <tr>
            <td style="padding:0 32px 28px;">
              <table role="presentation" cellpadding="0" cellspacing="0" border="0" width="100%">
                <tr>
                  <td width="33%" align="center" style="padding:8px;font-size:13px;line-height:1.4;color:#1c1c1c;">
                    <strong style="display:block;color:%%COLOR_PRIMARY%%;margin-bottom:4px;">100% Secure</strong>
                    SSL Encrypted
                  </td>
                  <td width="33%" align="center" style="padding:8px;font-size:13px;line-height:1.4;color:#1c1c1c;">
                    <strong style="display:block;color:%%COLOR_PRIMARY%%;margin-bottom:4px;">24hr Response</strong>
                    Fast turnaround
                  </td>
                  <td width="33%" align="center" style="padding:8px;font-size:13px;line-height:1.4;color:#1c1c1c;">
                    <strong style="display:block;color:%%COLOR_PRIMARY%%;margin-bottom:4px;">Licensed &amp; Insured</strong>
                    %%FOOTER_TEXT%%
                  </td>
                </tr>
              </table>
            </td>
          </tr>
          <tr>
            <td align="center" style="padding:0 32px 32px;">
              <a href="%%SITE_URL%%%%CONTACT_PAGE_URL%%" style="display:inline-block;background-color:%%COLOR_PRIMARY%%;color:#ffffff;text-decoration:none;font-size:15px;font-weight:700;padding:14px 28px;border-radius:10px;">Visit %%COMPANY_NAME%%</a>
            </td>
          </tr>
          <tr>
            <td style="background-color:#fdf7f0;padding:24px 32px;border-top:1px solid %%COLOR_BORDER%%;">
              <p style="margin:0 0 10px;font-size:14px;line-height:1.5;color:#1c1c1c;text-align:center;">
                Questions? Call us at <a href="tel:%%PHONE_TEL%%" style="color:%%COLOR_PRIMARY%%;font-weight:700;text-decoration:none;">%%PHONE%%</a>
              </p>
              <p style="margin:0 0 8px;font-size:12px;line-height:1.5;color:%%COLOR_MUTED%%;text-align:center;">Mon–Fri 9:00 AM – 5:00 PM</p>
              <p style="margin:0 0 12px;font-size:12px;line-height:1.5;color:%%COLOR_MUTED%%;text-align:center;">%%FOOTER_TEXT%%</p>
              <p style="margin:0;font-size:11px;line-height:1.5;color:%%COLOR_MUTED%%;text-align:center;">%%LICENSES_FOOTER%%</p>
            </td>
          </tr>
        </table>
      </td>
    </tr>
  </table>
</body>
</html>
STRIKESCOPE_EMAIL;
	// STRIKESCOPE_LEAD_FORM_EMAIL_TEMPLATE_END
}

function strikescope_form_embedded_service_email_templates() {
	// STRIKESCOPE_LEAD_FORM_SERVICE_EMAILS_START
	return array(
		'customer' => array(
			"roofing" => "<!DOCTYPE html>\n<html lang=\"en\">\n<head>\n  <meta charset=\"UTF-8\">\n  <meta name=\"viewport\" content=\"width=device-width, initial-scale=1.0\">\n  <title>Thanks for contacting %%COMPANY_NAME%%</title>\n</head>\n<body style=\"margin:0;padding:0;background-color:%%COLOR_BACKGROUND%%;font-family:Montserrat,Arial,Helvetica,sans-serif;color:#1c1c1c;\">\n  <table role=\"presentation\" cellpadding=\"0\" cellspacing=\"0\" border=\"0\" width=\"100%\" style=\"background-color:%%COLOR_BACKGROUND%%;\">\n    <tr>\n      <td align=\"center\" style=\"padding:32px 16px;\">\n        <table role=\"presentation\" cellpadding=\"0\" cellspacing=\"0\" border=\"0\" width=\"600\" style=\"max-width:600px;width:100%;background-color:#ffffff;border-radius:16px;overflow:hidden;border:1px solid %%COLOR_BORDER%%;\">\n          <tr>\n            <td align=\"center\" style=\"background-color:%%COLOR_SECONDARY%%;padding:28px 24px;border-bottom:3px solid %%COLOR_PRIMARY%%;\">\n              <img src=\"%%LOGO_URL%%\" alt=\"%%COMPANY_NAME%%\" width=\"220\" style=\"display:block;max-width:220px;height:auto;border:0;\">\n            </td>\n          </tr>\n          <tr>\n            <td style=\"padding:36px 32px 12px;\">\n              <p style=\"margin:0 0 8px;font-size:13px;font-weight:700;letter-spacing:0.05em;text-transform:uppercase;color:%%COLOR_PRIMARY%%;\">%%PRIMARY_SERVICE_LABEL%%</p>\n              <h1 style=\"margin:0 0 16px;font-size:28px;line-height:1.25;font-weight:800;color:#1c1c1c;\">Thanks, %%FIRST_NAME%%!</h1>\n              <p style=\"margin:0 0 18px;font-size:16px;line-height:1.6;color:#3a2a1a;\">%%SERVICE_INTRO%%</p>\n              <p style=\"margin:0;font-size:16px;line-height:1.6;color:%%COLOR_MUTED%%;\">We typically respond within <strong style=\"color:%%COLOR_PRIMARY%%;\">24 hours</strong> during business days.</p>\n            </td>\n          </tr>\n          <tr>\n            <td style=\"padding:8px 32px 24px;\">\n              <table role=\"presentation\" cellpadding=\"0\" cellspacing=\"0\" border=\"0\" width=\"100%\" style=\"background-color:%%COLOR_SECONDARY%%;border-radius:12px;border:1px solid %%COLOR_BORDER%%;\">\n                <tr>\n                  <td style=\"padding:20px 22px;\">\n                    <p style=\"margin:0 0 10px;font-size:13px;font-weight:700;letter-spacing:0.04em;text-transform:uppercase;color:%%COLOR_MUTED%%;\">Your request summary</p>\n                    <p style=\"margin:0 0 8px;font-size:15px;line-height:1.5;color:#1c1c1c;\"><strong>Services:</strong> %%SERVICE_LIST%%</p>\n                    <p style=\"margin:0 0 8px;font-size:15px;line-height:1.5;color:#1c1c1c;\"><strong>Location:</strong> %%CITY%%, %%STATE%%</p>\n                    <p style=\"margin:0;font-size:15px;line-height:1.5;color:#1c1c1c;\"><strong>Submitted:</strong> %%SUBMISSION_DATE%%</p>\n                  </td>\n                </tr>\n              </table>\n            </td>\n          </tr>\n          <tr>\n            <td align=\"center\" style=\"padding:0 32px 32px;\">\n              <a href=\"%%SITE_URL%%%%CONTACT_PAGE_URL%%\" style=\"display:inline-block;background-color:%%COLOR_PRIMARY%%;color:#ffffff;text-decoration:none;font-size:15px;font-weight:700;padding:14px 28px;border-radius:10px;\">Visit %%COMPANY_NAME%%</a>\n            </td>\n          </tr>\n          <tr>\n            <td style=\"background-color:#fdf7f0;padding:24px 32px;border-top:1px solid %%COLOR_BORDER%%;\">\n              <p style=\"margin:0 0 10px;font-size:14px;line-height:1.5;color:#1c1c1c;text-align:center;\">Questions? Call <a href=\"tel:%%PHONE_TEL%%\" style=\"color:%%COLOR_PRIMARY%%;font-weight:700;text-decoration:none;\">%%PHONE%%</a></p>\n              <p style=\"margin:0;font-size:11px;line-height:1.5;color:%%COLOR_MUTED%%;text-align:center;\">%%LICENSES_FOOTER%%</p>\n            </td>\n          </tr>\n        </table>\n      </td>\n    </tr>\n  </table>\n</body>\n</html>",
			"kitchen" => "<!DOCTYPE html>\n<html lang=\"en\">\n<head>\n  <meta charset=\"UTF-8\">\n  <meta name=\"viewport\" content=\"width=device-width, initial-scale=1.0\">\n  <title>Thanks for contacting %%COMPANY_NAME%%</title>\n</head>\n<body style=\"margin:0;padding:0;background-color:%%COLOR_BACKGROUND%%;font-family:Montserrat,Arial,Helvetica,sans-serif;color:#1c1c1c;\">\n  <table role=\"presentation\" cellpadding=\"0\" cellspacing=\"0\" border=\"0\" width=\"100%\" style=\"background-color:%%COLOR_BACKGROUND%%;\">\n    <tr>\n      <td align=\"center\" style=\"padding:32px 16px;\">\n        <table role=\"presentation\" cellpadding=\"0\" cellspacing=\"0\" border=\"0\" width=\"600\" style=\"max-width:600px;width:100%;background-color:#ffffff;border-radius:16px;overflow:hidden;border:1px solid %%COLOR_BORDER%%;\">\n          <tr>\n            <td align=\"center\" style=\"background-color:%%COLOR_SECONDARY%%;padding:28px 24px;border-bottom:3px solid %%COLOR_PRIMARY%%;\">\n              <img src=\"%%LOGO_URL%%\" alt=\"%%COMPANY_NAME%%\" width=\"220\" style=\"display:block;max-width:220px;height:auto;border:0;\">\n            </td>\n          </tr>\n          <tr>\n            <td style=\"padding:36px 32px 12px;\">\n              <p style=\"margin:0 0 8px;font-size:13px;font-weight:700;letter-spacing:0.05em;text-transform:uppercase;color:%%COLOR_PRIMARY%%;\">%%PRIMARY_SERVICE_LABEL%%</p>\n              <h1 style=\"margin:0 0 16px;font-size:28px;line-height:1.25;font-weight:800;color:#1c1c1c;\">Thanks, %%FIRST_NAME%%!</h1>\n              <p style=\"margin:0 0 18px;font-size:16px;line-height:1.6;color:#3a2a1a;\">%%SERVICE_INTRO%%</p>\n              <p style=\"margin:0;font-size:16px;line-height:1.6;color:%%COLOR_MUTED%%;\">We typically respond within <strong style=\"color:%%COLOR_PRIMARY%%;\">24 hours</strong> during business days.</p>\n            </td>\n          </tr>\n          <tr>\n            <td style=\"padding:8px 32px 24px;\">\n              <table role=\"presentation\" cellpadding=\"0\" cellspacing=\"0\" border=\"0\" width=\"100%\" style=\"background-color:%%COLOR_SECONDARY%%;border-radius:12px;border:1px solid %%COLOR_BORDER%%;\">\n                <tr>\n                  <td style=\"padding:20px 22px;\">\n                    <p style=\"margin:0 0 10px;font-size:13px;font-weight:700;letter-spacing:0.04em;text-transform:uppercase;color:%%COLOR_MUTED%%;\">Your request summary</p>\n                    <p style=\"margin:0 0 8px;font-size:15px;line-height:1.5;color:#1c1c1c;\"><strong>Services:</strong> %%SERVICE_LIST%%</p>\n                    <p style=\"margin:0 0 8px;font-size:15px;line-height:1.5;color:#1c1c1c;\"><strong>Location:</strong> %%CITY%%, %%STATE%%</p>\n                    <p style=\"margin:0;font-size:15px;line-height:1.5;color:#1c1c1c;\"><strong>Submitted:</strong> %%SUBMISSION_DATE%%</p>\n                  </td>\n                </tr>\n              </table>\n            </td>\n          </tr>\n          <tr>\n            <td align=\"center\" style=\"padding:0 32px 32px;\">\n              <a href=\"%%SITE_URL%%%%CONTACT_PAGE_URL%%\" style=\"display:inline-block;background-color:%%COLOR_PRIMARY%%;color:#ffffff;text-decoration:none;font-size:15px;font-weight:700;padding:14px 28px;border-radius:10px;\">Visit %%COMPANY_NAME%%</a>\n            </td>\n          </tr>\n          <tr>\n            <td style=\"background-color:#fdf7f0;padding:24px 32px;border-top:1px solid %%COLOR_BORDER%%;\">\n              <p style=\"margin:0 0 10px;font-size:14px;line-height:1.5;color:#1c1c1c;text-align:center;\">Questions? Call <a href=\"tel:%%PHONE_TEL%%\" style=\"color:%%COLOR_PRIMARY%%;font-weight:700;text-decoration:none;\">%%PHONE%%</a></p>\n              <p style=\"margin:0;font-size:11px;line-height:1.5;color:%%COLOR_MUTED%%;text-align:center;\">%%LICENSES_FOOTER%%</p>\n            </td>\n          </tr>\n        </table>\n      </td>\n    </tr>\n  </table>\n</body>\n</html>",
			"bathroom" => "<!DOCTYPE html>\n<html lang=\"en\">\n<head>\n  <meta charset=\"UTF-8\">\n  <meta name=\"viewport\" content=\"width=device-width, initial-scale=1.0\">\n  <title>Thanks for contacting %%COMPANY_NAME%%</title>\n</head>\n<body style=\"margin:0;padding:0;background-color:%%COLOR_BACKGROUND%%;font-family:Montserrat,Arial,Helvetica,sans-serif;color:#1c1c1c;\">\n  <table role=\"presentation\" cellpadding=\"0\" cellspacing=\"0\" border=\"0\" width=\"100%\" style=\"background-color:%%COLOR_BACKGROUND%%;\">\n    <tr>\n      <td align=\"center\" style=\"padding:32px 16px;\">\n        <table role=\"presentation\" cellpadding=\"0\" cellspacing=\"0\" border=\"0\" width=\"600\" style=\"max-width:600px;width:100%;background-color:#ffffff;border-radius:16px;overflow:hidden;border:1px solid %%COLOR_BORDER%%;\">\n          <tr>\n            <td align=\"center\" style=\"background-color:%%COLOR_SECONDARY%%;padding:28px 24px;border-bottom:3px solid %%COLOR_PRIMARY%%;\">\n              <img src=\"%%LOGO_URL%%\" alt=\"%%COMPANY_NAME%%\" width=\"220\" style=\"display:block;max-width:220px;height:auto;border:0;\">\n            </td>\n          </tr>\n          <tr>\n            <td style=\"padding:36px 32px 12px;\">\n              <p style=\"margin:0 0 8px;font-size:13px;font-weight:700;letter-spacing:0.05em;text-transform:uppercase;color:%%COLOR_PRIMARY%%;\">%%PRIMARY_SERVICE_LABEL%%</p>\n              <h1 style=\"margin:0 0 16px;font-size:28px;line-height:1.25;font-weight:800;color:#1c1c1c;\">Thanks, %%FIRST_NAME%%!</h1>\n              <p style=\"margin:0 0 18px;font-size:16px;line-height:1.6;color:#3a2a1a;\">%%SERVICE_INTRO%%</p>\n              <p style=\"margin:0;font-size:16px;line-height:1.6;color:%%COLOR_MUTED%%;\">We typically respond within <strong style=\"color:%%COLOR_PRIMARY%%;\">24 hours</strong> during business days.</p>\n            </td>\n          </tr>\n          <tr>\n            <td style=\"padding:8px 32px 24px;\">\n              <table role=\"presentation\" cellpadding=\"0\" cellspacing=\"0\" border=\"0\" width=\"100%\" style=\"background-color:%%COLOR_SECONDARY%%;border-radius:12px;border:1px solid %%COLOR_BORDER%%;\">\n                <tr>\n                  <td style=\"padding:20px 22px;\">\n                    <p style=\"margin:0 0 10px;font-size:13px;font-weight:700;letter-spacing:0.04em;text-transform:uppercase;color:%%COLOR_MUTED%%;\">Your request summary</p>\n                    <p style=\"margin:0 0 8px;font-size:15px;line-height:1.5;color:#1c1c1c;\"><strong>Services:</strong> %%SERVICE_LIST%%</p>\n                    <p style=\"margin:0 0 8px;font-size:15px;line-height:1.5;color:#1c1c1c;\"><strong>Location:</strong> %%CITY%%, %%STATE%%</p>\n                    <p style=\"margin:0;font-size:15px;line-height:1.5;color:#1c1c1c;\"><strong>Submitted:</strong> %%SUBMISSION_DATE%%</p>\n                  </td>\n                </tr>\n              </table>\n            </td>\n          </tr>\n          <tr>\n            <td align=\"center\" style=\"padding:0 32px 32px;\">\n              <a href=\"%%SITE_URL%%%%CONTACT_PAGE_URL%%\" style=\"display:inline-block;background-color:%%COLOR_PRIMARY%%;color:#ffffff;text-decoration:none;font-size:15px;font-weight:700;padding:14px 28px;border-radius:10px;\">Visit %%COMPANY_NAME%%</a>\n            </td>\n          </tr>\n          <tr>\n            <td style=\"background-color:#fdf7f0;padding:24px 32px;border-top:1px solid %%COLOR_BORDER%%;\">\n              <p style=\"margin:0 0 10px;font-size:14px;line-height:1.5;color:#1c1c1c;text-align:center;\">Questions? Call <a href=\"tel:%%PHONE_TEL%%\" style=\"color:%%COLOR_PRIMARY%%;font-weight:700;text-decoration:none;\">%%PHONE%%</a></p>\n              <p style=\"margin:0;font-size:11px;line-height:1.5;color:%%COLOR_MUTED%%;text-align:center;\">%%LICENSES_FOOTER%%</p>\n            </td>\n          </tr>\n        </table>\n      </td>\n    </tr>\n  </table>\n</body>\n</html>",
			"addons" => "<!DOCTYPE html>\n<html lang=\"en\">\n<head>\n  <meta charset=\"UTF-8\">\n  <meta name=\"viewport\" content=\"width=device-width, initial-scale=1.0\">\n  <title>Thanks for contacting %%COMPANY_NAME%%</title>\n</head>\n<body style=\"margin:0;padding:0;background-color:%%COLOR_BACKGROUND%%;font-family:Montserrat,Arial,Helvetica,sans-serif;color:#1c1c1c;\">\n  <table role=\"presentation\" cellpadding=\"0\" cellspacing=\"0\" border=\"0\" width=\"100%\" style=\"background-color:%%COLOR_BACKGROUND%%;\">\n    <tr>\n      <td align=\"center\" style=\"padding:32px 16px;\">\n        <table role=\"presentation\" cellpadding=\"0\" cellspacing=\"0\" border=\"0\" width=\"600\" style=\"max-width:600px;width:100%;background-color:#ffffff;border-radius:16px;overflow:hidden;border:1px solid %%COLOR_BORDER%%;\">\n          <tr>\n            <td align=\"center\" style=\"background-color:%%COLOR_SECONDARY%%;padding:28px 24px;border-bottom:3px solid %%COLOR_PRIMARY%%;\">\n              <img src=\"%%LOGO_URL%%\" alt=\"%%COMPANY_NAME%%\" width=\"220\" style=\"display:block;max-width:220px;height:auto;border:0;\">\n            </td>\n          </tr>\n          <tr>\n            <td style=\"padding:36px 32px 12px;\">\n              <p style=\"margin:0 0 8px;font-size:13px;font-weight:700;letter-spacing:0.05em;text-transform:uppercase;color:%%COLOR_PRIMARY%%;\">%%PRIMARY_SERVICE_LABEL%%</p>\n              <h1 style=\"margin:0 0 16px;font-size:28px;line-height:1.25;font-weight:800;color:#1c1c1c;\">Thanks, %%FIRST_NAME%%!</h1>\n              <p style=\"margin:0 0 18px;font-size:16px;line-height:1.6;color:#3a2a1a;\">%%SERVICE_INTRO%%</p>\n              <p style=\"margin:0;font-size:16px;line-height:1.6;color:%%COLOR_MUTED%%;\">We typically respond within <strong style=\"color:%%COLOR_PRIMARY%%;\">24 hours</strong> during business days.</p>\n            </td>\n          </tr>\n          <tr>\n            <td style=\"padding:8px 32px 24px;\">\n              <table role=\"presentation\" cellpadding=\"0\" cellspacing=\"0\" border=\"0\" width=\"100%\" style=\"background-color:%%COLOR_SECONDARY%%;border-radius:12px;border:1px solid %%COLOR_BORDER%%;\">\n                <tr>\n                  <td style=\"padding:20px 22px;\">\n                    <p style=\"margin:0 0 10px;font-size:13px;font-weight:700;letter-spacing:0.04em;text-transform:uppercase;color:%%COLOR_MUTED%%;\">Your request summary</p>\n                    <p style=\"margin:0 0 8px;font-size:15px;line-height:1.5;color:#1c1c1c;\"><strong>Services:</strong> %%SERVICE_LIST%%</p>\n                    <p style=\"margin:0 0 8px;font-size:15px;line-height:1.5;color:#1c1c1c;\"><strong>Location:</strong> %%CITY%%, %%STATE%%</p>\n                    <p style=\"margin:0;font-size:15px;line-height:1.5;color:#1c1c1c;\"><strong>Submitted:</strong> %%SUBMISSION_DATE%%</p>\n                  </td>\n                </tr>\n              </table>\n            </td>\n          </tr>\n          <tr>\n            <td align=\"center\" style=\"padding:0 32px 32px;\">\n              <a href=\"%%SITE_URL%%%%CONTACT_PAGE_URL%%\" style=\"display:inline-block;background-color:%%COLOR_PRIMARY%%;color:#ffffff;text-decoration:none;font-size:15px;font-weight:700;padding:14px 28px;border-radius:10px;\">Visit %%COMPANY_NAME%%</a>\n            </td>\n          </tr>\n          <tr>\n            <td style=\"background-color:#fdf7f0;padding:24px 32px;border-top:1px solid %%COLOR_BORDER%%;\">\n              <p style=\"margin:0 0 10px;font-size:14px;line-height:1.5;color:#1c1c1c;text-align:center;\">Questions? Call <a href=\"tel:%%PHONE_TEL%%\" style=\"color:%%COLOR_PRIMARY%%;font-weight:700;text-decoration:none;\">%%PHONE%%</a></p>\n              <p style=\"margin:0;font-size:11px;line-height:1.5;color:%%COLOR_MUTED%%;text-align:center;\">%%LICENSES_FOOTER%%</p>\n            </td>\n          </tr>\n        </table>\n      </td>\n    </tr>\n  </table>\n</body>\n</html>",
			"solar" => "<!DOCTYPE html>\n<html lang=\"en\">\n<head>\n  <meta charset=\"UTF-8\">\n  <meta name=\"viewport\" content=\"width=device-width, initial-scale=1.0\">\n  <title>Thanks for contacting %%COMPANY_NAME%%</title>\n</head>\n<body style=\"margin:0;padding:0;background-color:%%COLOR_BACKGROUND%%;font-family:Montserrat,Arial,Helvetica,sans-serif;color:#1c1c1c;\">\n  <table role=\"presentation\" cellpadding=\"0\" cellspacing=\"0\" border=\"0\" width=\"100%\" style=\"background-color:%%COLOR_BACKGROUND%%;\">\n    <tr>\n      <td align=\"center\" style=\"padding:32px 16px;\">\n        <table role=\"presentation\" cellpadding=\"0\" cellspacing=\"0\" border=\"0\" width=\"600\" style=\"max-width:600px;width:100%;background-color:#ffffff;border-radius:16px;overflow:hidden;border:1px solid %%COLOR_BORDER%%;\">\n          <tr>\n            <td align=\"center\" style=\"background-color:%%COLOR_SECONDARY%%;padding:28px 24px;border-bottom:3px solid %%COLOR_PRIMARY%%;\">\n              <img src=\"%%LOGO_URL%%\" alt=\"%%COMPANY_NAME%%\" width=\"220\" style=\"display:block;max-width:220px;height:auto;border:0;\">\n            </td>\n          </tr>\n          <tr>\n            <td style=\"padding:36px 32px 12px;\">\n              <p style=\"margin:0 0 8px;font-size:13px;font-weight:700;letter-spacing:0.05em;text-transform:uppercase;color:%%COLOR_PRIMARY%%;\">%%PRIMARY_SERVICE_LABEL%%</p>\n              <h1 style=\"margin:0 0 16px;font-size:28px;line-height:1.25;font-weight:800;color:#1c1c1c;\">Thanks, %%FIRST_NAME%%!</h1>\n              <p style=\"margin:0 0 18px;font-size:16px;line-height:1.6;color:#3a2a1a;\">%%SERVICE_INTRO%%</p>\n              <p style=\"margin:0;font-size:16px;line-height:1.6;color:%%COLOR_MUTED%%;\">We typically respond within <strong style=\"color:%%COLOR_PRIMARY%%;\">24 hours</strong> during business days.</p>\n            </td>\n          </tr>\n          <tr>\n            <td style=\"padding:8px 32px 24px;\">\n              <table role=\"presentation\" cellpadding=\"0\" cellspacing=\"0\" border=\"0\" width=\"100%\" style=\"background-color:%%COLOR_SECONDARY%%;border-radius:12px;border:1px solid %%COLOR_BORDER%%;\">\n                <tr>\n                  <td style=\"padding:20px 22px;\">\n                    <p style=\"margin:0 0 10px;font-size:13px;font-weight:700;letter-spacing:0.04em;text-transform:uppercase;color:%%COLOR_MUTED%%;\">Your request summary</p>\n                    <p style=\"margin:0 0 8px;font-size:15px;line-height:1.5;color:#1c1c1c;\"><strong>Services:</strong> %%SERVICE_LIST%%</p>\n                    <p style=\"margin:0 0 8px;font-size:15px;line-height:1.5;color:#1c1c1c;\"><strong>Location:</strong> %%CITY%%, %%STATE%%</p>\n                    <p style=\"margin:0;font-size:15px;line-height:1.5;color:#1c1c1c;\"><strong>Submitted:</strong> %%SUBMISSION_DATE%%</p>\n                  </td>\n                </tr>\n              </table>\n            </td>\n          </tr>\n          <tr>\n            <td align=\"center\" style=\"padding:0 32px 32px;\">\n              <a href=\"%%SITE_URL%%%%CONTACT_PAGE_URL%%\" style=\"display:inline-block;background-color:%%COLOR_PRIMARY%%;color:#ffffff;text-decoration:none;font-size:15px;font-weight:700;padding:14px 28px;border-radius:10px;\">Visit %%COMPANY_NAME%%</a>\n            </td>\n          </tr>\n          <tr>\n            <td style=\"background-color:#fdf7f0;padding:24px 32px;border-top:1px solid %%COLOR_BORDER%%;\">\n              <p style=\"margin:0 0 10px;font-size:14px;line-height:1.5;color:#1c1c1c;text-align:center;\">Questions? Call <a href=\"tel:%%PHONE_TEL%%\" style=\"color:%%COLOR_PRIMARY%%;font-weight:700;text-decoration:none;\">%%PHONE%%</a></p>\n              <p style=\"margin:0;font-size:11px;line-height:1.5;color:%%COLOR_MUTED%%;text-align:center;\">%%LICENSES_FOOTER%%</p>\n            </td>\n          </tr>\n        </table>\n      </td>\n    </tr>\n  </table>\n</body>\n</html>",
			"multi" => "<!DOCTYPE html>\n<html lang=\"en\">\n<head>\n  <meta charset=\"UTF-8\">\n  <meta name=\"viewport\" content=\"width=device-width, initial-scale=1.0\">\n  <title>Thanks for contacting %%COMPANY_NAME%%</title>\n</head>\n<body style=\"margin:0;padding:0;background-color:%%COLOR_BACKGROUND%%;font-family:Montserrat,Arial,Helvetica,sans-serif;color:#1c1c1c;\">\n  <table role=\"presentation\" cellpadding=\"0\" cellspacing=\"0\" border=\"0\" width=\"100%\" style=\"background-color:%%COLOR_BACKGROUND%%;\">\n    <tr>\n      <td align=\"center\" style=\"padding:32px 16px;\">\n        <table role=\"presentation\" cellpadding=\"0\" cellspacing=\"0\" border=\"0\" width=\"600\" style=\"max-width:600px;width:100%;background-color:#ffffff;border-radius:16px;overflow:hidden;border:1px solid %%COLOR_BORDER%%;\">\n          <tr>\n            <td align=\"center\" style=\"background-color:%%COLOR_SECONDARY%%;padding:28px 24px;border-bottom:3px solid %%COLOR_PRIMARY%%;\">\n              <img src=\"%%LOGO_URL%%\" alt=\"%%COMPANY_NAME%%\" width=\"220\" style=\"display:block;max-width:220px;height:auto;border:0;\">\n            </td>\n          </tr>\n          <tr>\n            <td style=\"padding:36px 32px 12px;\">\n              <p style=\"margin:0 0 8px;font-size:13px;font-weight:700;letter-spacing:0.05em;text-transform:uppercase;color:%%COLOR_PRIMARY%%;\">Multiple Services</p>\n              <h1 style=\"margin:0 0 16px;font-size:28px;line-height:1.25;font-weight:800;color:#1c1c1c;\">Thanks, %%FIRST_NAME%%!</h1>\n              <p style=\"margin:0 0 18px;font-size:16px;line-height:1.6;color:#3a2a1a;\">%%SERVICE_INTRO%%</p>\n              <p style=\"margin:0;font-size:16px;line-height:1.6;color:%%COLOR_MUTED%%;\">We typically respond within <strong style=\"color:%%COLOR_PRIMARY%%;\">24 hours</strong> during business days.</p>\n            </td>\n          </tr>\n          <tr>\n            <td style=\"padding:8px 32px 24px;\">\n              <table role=\"presentation\" cellpadding=\"0\" cellspacing=\"0\" border=\"0\" width=\"100%\" style=\"background-color:%%COLOR_SECONDARY%%;border-radius:12px;border:1px solid %%COLOR_BORDER%%;\">\n                <tr>\n                  <td style=\"padding:20px 22px;\">\n                    <p style=\"margin:0 0 10px;font-size:13px;font-weight:700;letter-spacing:0.04em;text-transform:uppercase;color:%%COLOR_MUTED%%;\">Your request summary</p>\n                    <p style=\"margin:0 0 8px;font-size:15px;line-height:1.5;color:#1c1c1c;\"><strong>Services:</strong> %%SERVICE_LIST%%</p>\n                    <p style=\"margin:0 0 8px;font-size:15px;line-height:1.5;color:#1c1c1c;\"><strong>Location:</strong> %%CITY%%, %%STATE%%</p>\n                    <p style=\"margin:0;font-size:15px;line-height:1.5;color:#1c1c1c;\"><strong>Submitted:</strong> %%SUBMISSION_DATE%%</p>\n                  </td>\n                </tr>\n              </table>\n            </td>\n          </tr>\n          <tr>\n            <td align=\"center\" style=\"padding:0 32px 32px;\">\n              <a href=\"%%SITE_URL%%%%CONTACT_PAGE_URL%%\" style=\"display:inline-block;background-color:%%COLOR_PRIMARY%%;color:#ffffff;text-decoration:none;font-size:15px;font-weight:700;padding:14px 28px;border-radius:10px;\">Visit %%COMPANY_NAME%%</a>\n            </td>\n          </tr>\n          <tr>\n            <td style=\"background-color:#fdf7f0;padding:24px 32px;border-top:1px solid %%COLOR_BORDER%%;\">\n              <p style=\"margin:0 0 10px;font-size:14px;line-height:1.5;color:#1c1c1c;text-align:center;\">Questions? Call <a href=\"tel:%%PHONE_TEL%%\" style=\"color:%%COLOR_PRIMARY%%;font-weight:700;text-decoration:none;\">%%PHONE%%</a></p>\n              <p style=\"margin:0;font-size:11px;line-height:1.5;color:%%COLOR_MUTED%%;text-align:center;\">%%LICENSES_FOOTER%%</p>\n            </td>\n          </tr>\n        </table>\n      </td>\n    </tr>\n  </table>\n</body>\n</html>",
		),
		'admin' => array(
			"roofing" => "<!DOCTYPE html>\n<html lang=\"en\">\n<head>\n  <meta charset=\"UTF-8\">\n  <meta name=\"viewport\" content=\"width=device-width, initial-scale=1.0\">\n  <title>New Lead</title>\n</head>\n<body style=\"margin:0;padding:0;background-color:%%COLOR_BACKGROUND%%;font-family:Montserrat,Arial,Helvetica,sans-serif;color:#1c1c1c;\">\n  <table role=\"presentation\" cellpadding=\"0\" cellspacing=\"0\" border=\"0\" width=\"100%\" style=\"background-color:%%COLOR_BACKGROUND%%;\">\n    <tr>\n      <td align=\"center\" style=\"padding:32px 16px;\">\n        <table role=\"presentation\" cellpadding=\"0\" cellspacing=\"0\" border=\"0\" width=\"600\" style=\"max-width:600px;width:100%;background-color:#ffffff;border-radius:16px;overflow:hidden;border:1px solid %%COLOR_BORDER%%;\">\n          <tr>\n            <td align=\"center\" style=\"background-color:#1c1c1c;padding:24px;\">\n              <img src=\"%%LOGO_URL%%\" alt=\"%%COMPANY_NAME%%\" width=\"200\" style=\"display:block;max-width:200px;height:auto;border:0;\">\n            </td>\n          </tr>\n          <tr>\n            <td style=\"background-color:%%COLOR_PRIMARY%%;padding:14px 24px;\">\n              <p style=\"margin:0;font-size:13px;font-weight:700;letter-spacing:0.06em;text-transform:uppercase;color:#ffffff;\">New Lead · %%PRIMARY_SERVICE_LABEL%%</p>\n            </td>\n          </tr>\n          <tr>\n            <td style=\"padding:28px 32px 8px;\">\n              <h1 style=\"margin:0 0 8px;font-size:24px;line-height:1.25;font-weight:800;color:#1c1c1c;\">%%FULL_NAME%%</h1>\n              <p style=\"margin:0;font-size:15px;line-height:1.5;color:%%COLOR_MUTED%%;\">Submitted %%SUBMISSION_DATE%% · %%SITE_URL%% contact form</p>\n            </td>\n          </tr>\n          <tr>\n            <td style=\"padding:16px 32px 24px;\">\n              <table role=\"presentation\" cellpadding=\"0\" cellspacing=\"0\" border=\"0\" width=\"100%\" style=\"background-color:%%COLOR_SECONDARY%%;border-radius:12px;border:1px solid %%COLOR_BORDER%%;\">\n                <tr>\n                  <td style=\"padding:20px 22px;font-size:15px;line-height:1.6;color:#1c1c1c;\">\n                    <p style=\"margin:0 0 10px;\"><strong>Services:</strong> %%SERVICE_LIST%%</p>\n                    <p style=\"margin:0 0 10px;\"><strong>Property type:</strong> %%PROPERTY_TYPE%%</p>\n                    <p style=\"margin:0 0 10px;\"><strong>Timeline:</strong> %%TIMELINE%%</p>\n                    <p style=\"margin:0 0 10px;\"><strong>Budget:</strong> %%BUDGET%%</p>\n                    <p style=\"margin:0 0 10px;\"><strong>Email:</strong> <a href=\"mailto:%%LEAD_EMAIL%%\" style=\"color:%%COLOR_PRIMARY%%;\">%%LEAD_EMAIL%%</a></p>\n                    <p style=\"margin:0 0 10px;\"><strong>Phone:</strong> <a href=\"tel:%%LEAD_PHONE_TEL%%\" style=\"color:%%COLOR_PRIMARY%%;\">%%LEAD_PHONE%%</a></p>\n                    <p style=\"margin:0 0 10px;\"><strong>Address:</strong> %%ADDRESS%%, %%CITY%%, %%STATE%% %%ZIP_CODE%%</p>\n                    <p style=\"margin:0;\"><strong>Additional details:</strong><br>%%ADDITIONAL_DETAILS%%</p>\n                  </td>\n                </tr>\n              </table>\n            </td>\n          </tr>\n          <tr>\n            <td style=\"padding:0 32px 24px;\">\n              %%PROPERTY_LOOKUP_HTML%%\n            </td>\n          </tr>\n          <tr>\n            <td align=\"center\" style=\"padding:0 32px 28px;\">\n              <a href=\"mailto:%%LEAD_EMAIL%%\" style=\"display:inline-block;background-color:%%COLOR_PRIMARY%%;color:#ffffff;text-decoration:none;font-size:15px;font-weight:700;padding:14px 28px;border-radius:10px;\">Reply to lead</a>\n            </td>\n          </tr>\n          <tr>\n            <td style=\"background-color:#fdf7f0;padding:18px 32px;border-top:1px solid %%COLOR_BORDER%%;\">\n              <p style=\"margin:0;font-size:11px;line-height:1.5;color:%%COLOR_MUTED%%;text-align:center;\">%%LICENSES_FOOTER%%</p>\n            </td>\n          </tr>\n        </table>\n      </td>\n    </tr>\n  </table>\n</body>\n</html>",
			"kitchen" => "<!DOCTYPE html>\n<html lang=\"en\">\n<head>\n  <meta charset=\"UTF-8\">\n  <meta name=\"viewport\" content=\"width=device-width, initial-scale=1.0\">\n  <title>New Lead</title>\n</head>\n<body style=\"margin:0;padding:0;background-color:%%COLOR_BACKGROUND%%;font-family:Montserrat,Arial,Helvetica,sans-serif;color:#1c1c1c;\">\n  <table role=\"presentation\" cellpadding=\"0\" cellspacing=\"0\" border=\"0\" width=\"100%\" style=\"background-color:%%COLOR_BACKGROUND%%;\">\n    <tr>\n      <td align=\"center\" style=\"padding:32px 16px;\">\n        <table role=\"presentation\" cellpadding=\"0\" cellspacing=\"0\" border=\"0\" width=\"600\" style=\"max-width:600px;width:100%;background-color:#ffffff;border-radius:16px;overflow:hidden;border:1px solid %%COLOR_BORDER%%;\">\n          <tr>\n            <td align=\"center\" style=\"background-color:#1c1c1c;padding:24px;\">\n              <img src=\"%%LOGO_URL%%\" alt=\"%%COMPANY_NAME%%\" width=\"200\" style=\"display:block;max-width:200px;height:auto;border:0;\">\n            </td>\n          </tr>\n          <tr>\n            <td style=\"background-color:%%COLOR_PRIMARY%%;padding:14px 24px;\">\n              <p style=\"margin:0;font-size:13px;font-weight:700;letter-spacing:0.06em;text-transform:uppercase;color:#ffffff;\">New Lead · %%PRIMARY_SERVICE_LABEL%%</p>\n            </td>\n          </tr>\n          <tr>\n            <td style=\"padding:28px 32px 8px;\">\n              <h1 style=\"margin:0 0 8px;font-size:24px;line-height:1.25;font-weight:800;color:#1c1c1c;\">%%FULL_NAME%%</h1>\n              <p style=\"margin:0;font-size:15px;line-height:1.5;color:%%COLOR_MUTED%%;\">Submitted %%SUBMISSION_DATE%% · %%SITE_URL%% contact form</p>\n            </td>\n          </tr>\n          <tr>\n            <td style=\"padding:16px 32px 24px;\">\n              <table role=\"presentation\" cellpadding=\"0\" cellspacing=\"0\" border=\"0\" width=\"100%\" style=\"background-color:%%COLOR_SECONDARY%%;border-radius:12px;border:1px solid %%COLOR_BORDER%%;\">\n                <tr>\n                  <td style=\"padding:20px 22px;font-size:15px;line-height:1.6;color:#1c1c1c;\">\n                    <p style=\"margin:0 0 10px;\"><strong>Services:</strong> %%SERVICE_LIST%%</p>\n                    <p style=\"margin:0 0 10px;\"><strong>Property type:</strong> %%PROPERTY_TYPE%%</p>\n                    <p style=\"margin:0 0 10px;\"><strong>Timeline:</strong> %%TIMELINE%%</p>\n                    <p style=\"margin:0 0 10px;\"><strong>Budget:</strong> %%BUDGET%%</p>\n                    <p style=\"margin:0 0 10px;\"><strong>Email:</strong> <a href=\"mailto:%%LEAD_EMAIL%%\" style=\"color:%%COLOR_PRIMARY%%;\">%%LEAD_EMAIL%%</a></p>\n                    <p style=\"margin:0 0 10px;\"><strong>Phone:</strong> <a href=\"tel:%%LEAD_PHONE_TEL%%\" style=\"color:%%COLOR_PRIMARY%%;\">%%LEAD_PHONE%%</a></p>\n                    <p style=\"margin:0 0 10px;\"><strong>Address:</strong> %%ADDRESS%%, %%CITY%%, %%STATE%% %%ZIP_CODE%%</p>\n                    <p style=\"margin:0;\"><strong>Additional details:</strong><br>%%ADDITIONAL_DETAILS%%</p>\n                  </td>\n                </tr>\n              </table>\n            </td>\n          </tr>\n          <tr>\n            <td style=\"padding:0 32px 24px;\">\n              %%PROPERTY_LOOKUP_HTML%%\n            </td>\n          </tr>\n          <tr>\n            <td align=\"center\" style=\"padding:0 32px 28px;\">\n              <a href=\"mailto:%%LEAD_EMAIL%%\" style=\"display:inline-block;background-color:%%COLOR_PRIMARY%%;color:#ffffff;text-decoration:none;font-size:15px;font-weight:700;padding:14px 28px;border-radius:10px;\">Reply to lead</a>\n            </td>\n          </tr>\n          <tr>\n            <td style=\"background-color:#fdf7f0;padding:18px 32px;border-top:1px solid %%COLOR_BORDER%%;\">\n              <p style=\"margin:0;font-size:11px;line-height:1.5;color:%%COLOR_MUTED%%;text-align:center;\">%%LICENSES_FOOTER%%</p>\n            </td>\n          </tr>\n        </table>\n      </td>\n    </tr>\n  </table>\n</body>\n</html>",
			"bathroom" => "<!DOCTYPE html>\n<html lang=\"en\">\n<head>\n  <meta charset=\"UTF-8\">\n  <meta name=\"viewport\" content=\"width=device-width, initial-scale=1.0\">\n  <title>New Lead</title>\n</head>\n<body style=\"margin:0;padding:0;background-color:%%COLOR_BACKGROUND%%;font-family:Montserrat,Arial,Helvetica,sans-serif;color:#1c1c1c;\">\n  <table role=\"presentation\" cellpadding=\"0\" cellspacing=\"0\" border=\"0\" width=\"100%\" style=\"background-color:%%COLOR_BACKGROUND%%;\">\n    <tr>\n      <td align=\"center\" style=\"padding:32px 16px;\">\n        <table role=\"presentation\" cellpadding=\"0\" cellspacing=\"0\" border=\"0\" width=\"600\" style=\"max-width:600px;width:100%;background-color:#ffffff;border-radius:16px;overflow:hidden;border:1px solid %%COLOR_BORDER%%;\">\n          <tr>\n            <td align=\"center\" style=\"background-color:#1c1c1c;padding:24px;\">\n              <img src=\"%%LOGO_URL%%\" alt=\"%%COMPANY_NAME%%\" width=\"200\" style=\"display:block;max-width:200px;height:auto;border:0;\">\n            </td>\n          </tr>\n          <tr>\n            <td style=\"background-color:%%COLOR_PRIMARY%%;padding:14px 24px;\">\n              <p style=\"margin:0;font-size:13px;font-weight:700;letter-spacing:0.06em;text-transform:uppercase;color:#ffffff;\">New Lead · %%PRIMARY_SERVICE_LABEL%%</p>\n            </td>\n          </tr>\n          <tr>\n            <td style=\"padding:28px 32px 8px;\">\n              <h1 style=\"margin:0 0 8px;font-size:24px;line-height:1.25;font-weight:800;color:#1c1c1c;\">%%FULL_NAME%%</h1>\n              <p style=\"margin:0;font-size:15px;line-height:1.5;color:%%COLOR_MUTED%%;\">Submitted %%SUBMISSION_DATE%% · %%SITE_URL%% contact form</p>\n            </td>\n          </tr>\n          <tr>\n            <td style=\"padding:16px 32px 24px;\">\n              <table role=\"presentation\" cellpadding=\"0\" cellspacing=\"0\" border=\"0\" width=\"100%\" style=\"background-color:%%COLOR_SECONDARY%%;border-radius:12px;border:1px solid %%COLOR_BORDER%%;\">\n                <tr>\n                  <td style=\"padding:20px 22px;font-size:15px;line-height:1.6;color:#1c1c1c;\">\n                    <p style=\"margin:0 0 10px;\"><strong>Services:</strong> %%SERVICE_LIST%%</p>\n                    <p style=\"margin:0 0 10px;\"><strong>Property type:</strong> %%PROPERTY_TYPE%%</p>\n                    <p style=\"margin:0 0 10px;\"><strong>Timeline:</strong> %%TIMELINE%%</p>\n                    <p style=\"margin:0 0 10px;\"><strong>Budget:</strong> %%BUDGET%%</p>\n                    <p style=\"margin:0 0 10px;\"><strong>Email:</strong> <a href=\"mailto:%%LEAD_EMAIL%%\" style=\"color:%%COLOR_PRIMARY%%;\">%%LEAD_EMAIL%%</a></p>\n                    <p style=\"margin:0 0 10px;\"><strong>Phone:</strong> <a href=\"tel:%%LEAD_PHONE_TEL%%\" style=\"color:%%COLOR_PRIMARY%%;\">%%LEAD_PHONE%%</a></p>\n                    <p style=\"margin:0 0 10px;\"><strong>Address:</strong> %%ADDRESS%%, %%CITY%%, %%STATE%% %%ZIP_CODE%%</p>\n                    <p style=\"margin:0;\"><strong>Additional details:</strong><br>%%ADDITIONAL_DETAILS%%</p>\n                  </td>\n                </tr>\n              </table>\n            </td>\n          </tr>\n          <tr>\n            <td style=\"padding:0 32px 24px;\">\n              %%PROPERTY_LOOKUP_HTML%%\n            </td>\n          </tr>\n          <tr>\n            <td align=\"center\" style=\"padding:0 32px 28px;\">\n              <a href=\"mailto:%%LEAD_EMAIL%%\" style=\"display:inline-block;background-color:%%COLOR_PRIMARY%%;color:#ffffff;text-decoration:none;font-size:15px;font-weight:700;padding:14px 28px;border-radius:10px;\">Reply to lead</a>\n            </td>\n          </tr>\n          <tr>\n            <td style=\"background-color:#fdf7f0;padding:18px 32px;border-top:1px solid %%COLOR_BORDER%%;\">\n              <p style=\"margin:0;font-size:11px;line-height:1.5;color:%%COLOR_MUTED%%;text-align:center;\">%%LICENSES_FOOTER%%</p>\n            </td>\n          </tr>\n        </table>\n      </td>\n    </tr>\n  </table>\n</body>\n</html>",
			"addons" => "<!DOCTYPE html>\n<html lang=\"en\">\n<head>\n  <meta charset=\"UTF-8\">\n  <meta name=\"viewport\" content=\"width=device-width, initial-scale=1.0\">\n  <title>New Lead</title>\n</head>\n<body style=\"margin:0;padding:0;background-color:%%COLOR_BACKGROUND%%;font-family:Montserrat,Arial,Helvetica,sans-serif;color:#1c1c1c;\">\n  <table role=\"presentation\" cellpadding=\"0\" cellspacing=\"0\" border=\"0\" width=\"100%\" style=\"background-color:%%COLOR_BACKGROUND%%;\">\n    <tr>\n      <td align=\"center\" style=\"padding:32px 16px;\">\n        <table role=\"presentation\" cellpadding=\"0\" cellspacing=\"0\" border=\"0\" width=\"600\" style=\"max-width:600px;width:100%;background-color:#ffffff;border-radius:16px;overflow:hidden;border:1px solid %%COLOR_BORDER%%;\">\n          <tr>\n            <td align=\"center\" style=\"background-color:#1c1c1c;padding:24px;\">\n              <img src=\"%%LOGO_URL%%\" alt=\"%%COMPANY_NAME%%\" width=\"200\" style=\"display:block;max-width:200px;height:auto;border:0;\">\n            </td>\n          </tr>\n          <tr>\n            <td style=\"background-color:%%COLOR_PRIMARY%%;padding:14px 24px;\">\n              <p style=\"margin:0;font-size:13px;font-weight:700;letter-spacing:0.06em;text-transform:uppercase;color:#ffffff;\">New Lead · %%PRIMARY_SERVICE_LABEL%%</p>\n            </td>\n          </tr>\n          <tr>\n            <td style=\"padding:28px 32px 8px;\">\n              <h1 style=\"margin:0 0 8px;font-size:24px;line-height:1.25;font-weight:800;color:#1c1c1c;\">%%FULL_NAME%%</h1>\n              <p style=\"margin:0;font-size:15px;line-height:1.5;color:%%COLOR_MUTED%%;\">Submitted %%SUBMISSION_DATE%% · %%SITE_URL%% contact form</p>\n            </td>\n          </tr>\n          <tr>\n            <td style=\"padding:16px 32px 24px;\">\n              <table role=\"presentation\" cellpadding=\"0\" cellspacing=\"0\" border=\"0\" width=\"100%\" style=\"background-color:%%COLOR_SECONDARY%%;border-radius:12px;border:1px solid %%COLOR_BORDER%%;\">\n                <tr>\n                  <td style=\"padding:20px 22px;font-size:15px;line-height:1.6;color:#1c1c1c;\">\n                    <p style=\"margin:0 0 10px;\"><strong>Services:</strong> %%SERVICE_LIST%%</p>\n                    <p style=\"margin:0 0 10px;\"><strong>Property type:</strong> %%PROPERTY_TYPE%%</p>\n                    <p style=\"margin:0 0 10px;\"><strong>Timeline:</strong> %%TIMELINE%%</p>\n                    <p style=\"margin:0 0 10px;\"><strong>Budget:</strong> %%BUDGET%%</p>\n                    <p style=\"margin:0 0 10px;\"><strong>Email:</strong> <a href=\"mailto:%%LEAD_EMAIL%%\" style=\"color:%%COLOR_PRIMARY%%;\">%%LEAD_EMAIL%%</a></p>\n                    <p style=\"margin:0 0 10px;\"><strong>Phone:</strong> <a href=\"tel:%%LEAD_PHONE_TEL%%\" style=\"color:%%COLOR_PRIMARY%%;\">%%LEAD_PHONE%%</a></p>\n                    <p style=\"margin:0 0 10px;\"><strong>Address:</strong> %%ADDRESS%%, %%CITY%%, %%STATE%% %%ZIP_CODE%%</p>\n                    <p style=\"margin:0;\"><strong>Additional details:</strong><br>%%ADDITIONAL_DETAILS%%</p>\n                  </td>\n                </tr>\n              </table>\n            </td>\n          </tr>\n          <tr>\n            <td style=\"padding:0 32px 24px;\">\n              %%PROPERTY_LOOKUP_HTML%%\n            </td>\n          </tr>\n          <tr>\n            <td align=\"center\" style=\"padding:0 32px 28px;\">\n              <a href=\"mailto:%%LEAD_EMAIL%%\" style=\"display:inline-block;background-color:%%COLOR_PRIMARY%%;color:#ffffff;text-decoration:none;font-size:15px;font-weight:700;padding:14px 28px;border-radius:10px;\">Reply to lead</a>\n            </td>\n          </tr>\n          <tr>\n            <td style=\"background-color:#fdf7f0;padding:18px 32px;border-top:1px solid %%COLOR_BORDER%%;\">\n              <p style=\"margin:0;font-size:11px;line-height:1.5;color:%%COLOR_MUTED%%;text-align:center;\">%%LICENSES_FOOTER%%</p>\n            </td>\n          </tr>\n        </table>\n      </td>\n    </tr>\n  </table>\n</body>\n</html>",
			"solar" => "<!DOCTYPE html>\n<html lang=\"en\">\n<head>\n  <meta charset=\"UTF-8\">\n  <meta name=\"viewport\" content=\"width=device-width, initial-scale=1.0\">\n  <title>New Lead</title>\n</head>\n<body style=\"margin:0;padding:0;background-color:%%COLOR_BACKGROUND%%;font-family:Montserrat,Arial,Helvetica,sans-serif;color:#1c1c1c;\">\n  <table role=\"presentation\" cellpadding=\"0\" cellspacing=\"0\" border=\"0\" width=\"100%\" style=\"background-color:%%COLOR_BACKGROUND%%;\">\n    <tr>\n      <td align=\"center\" style=\"padding:32px 16px;\">\n        <table role=\"presentation\" cellpadding=\"0\" cellspacing=\"0\" border=\"0\" width=\"600\" style=\"max-width:600px;width:100%;background-color:#ffffff;border-radius:16px;overflow:hidden;border:1px solid %%COLOR_BORDER%%;\">\n          <tr>\n            <td align=\"center\" style=\"background-color:#1c1c1c;padding:24px;\">\n              <img src=\"%%LOGO_URL%%\" alt=\"%%COMPANY_NAME%%\" width=\"200\" style=\"display:block;max-width:200px;height:auto;border:0;\">\n            </td>\n          </tr>\n          <tr>\n            <td style=\"background-color:%%COLOR_PRIMARY%%;padding:14px 24px;\">\n              <p style=\"margin:0;font-size:13px;font-weight:700;letter-spacing:0.06em;text-transform:uppercase;color:#ffffff;\">New Lead · %%PRIMARY_SERVICE_LABEL%%</p>\n            </td>\n          </tr>\n          <tr>\n            <td style=\"padding:28px 32px 8px;\">\n              <h1 style=\"margin:0 0 8px;font-size:24px;line-height:1.25;font-weight:800;color:#1c1c1c;\">%%FULL_NAME%%</h1>\n              <p style=\"margin:0;font-size:15px;line-height:1.5;color:%%COLOR_MUTED%%;\">Submitted %%SUBMISSION_DATE%% · %%SITE_URL%% contact form</p>\n            </td>\n          </tr>\n          <tr>\n            <td style=\"padding:16px 32px 24px;\">\n              <table role=\"presentation\" cellpadding=\"0\" cellspacing=\"0\" border=\"0\" width=\"100%\" style=\"background-color:%%COLOR_SECONDARY%%;border-radius:12px;border:1px solid %%COLOR_BORDER%%;\">\n                <tr>\n                  <td style=\"padding:20px 22px;font-size:15px;line-height:1.6;color:#1c1c1c;\">\n                    <p style=\"margin:0 0 10px;\"><strong>Services:</strong> %%SERVICE_LIST%%</p>\n                    <p style=\"margin:0 0 10px;\"><strong>Property type:</strong> %%PROPERTY_TYPE%%</p>\n                    <p style=\"margin:0 0 10px;\"><strong>Timeline:</strong> %%TIMELINE%%</p>\n                    <p style=\"margin:0 0 10px;\"><strong>Budget:</strong> %%BUDGET%%</p>\n                    <p style=\"margin:0 0 10px;\"><strong>Email:</strong> <a href=\"mailto:%%LEAD_EMAIL%%\" style=\"color:%%COLOR_PRIMARY%%;\">%%LEAD_EMAIL%%</a></p>\n                    <p style=\"margin:0 0 10px;\"><strong>Phone:</strong> <a href=\"tel:%%LEAD_PHONE_TEL%%\" style=\"color:%%COLOR_PRIMARY%%;\">%%LEAD_PHONE%%</a></p>\n                    <p style=\"margin:0 0 10px;\"><strong>Address:</strong> %%ADDRESS%%, %%CITY%%, %%STATE%% %%ZIP_CODE%%</p>\n                    <p style=\"margin:0;\"><strong>Additional details:</strong><br>%%ADDITIONAL_DETAILS%%</p>\n                  </td>\n                </tr>\n              </table>\n            </td>\n          </tr>\n          <tr>\n            <td style=\"padding:0 32px 24px;\">\n              %%PROPERTY_LOOKUP_HTML%%\n            </td>\n          </tr>\n          <tr>\n            <td align=\"center\" style=\"padding:0 32px 28px;\">\n              <a href=\"mailto:%%LEAD_EMAIL%%\" style=\"display:inline-block;background-color:%%COLOR_PRIMARY%%;color:#ffffff;text-decoration:none;font-size:15px;font-weight:700;padding:14px 28px;border-radius:10px;\">Reply to lead</a>\n            </td>\n          </tr>\n          <tr>\n            <td style=\"background-color:#fdf7f0;padding:18px 32px;border-top:1px solid %%COLOR_BORDER%%;\">\n              <p style=\"margin:0;font-size:11px;line-height:1.5;color:%%COLOR_MUTED%%;text-align:center;\">%%LICENSES_FOOTER%%</p>\n            </td>\n          </tr>\n        </table>\n      </td>\n    </tr>\n  </table>\n</body>\n</html>",
			"multi" => "<!DOCTYPE html>\n<html lang=\"en\">\n<head>\n  <meta charset=\"UTF-8\">\n  <meta name=\"viewport\" content=\"width=device-width, initial-scale=1.0\">\n  <title>New Lead</title>\n</head>\n<body style=\"margin:0;padding:0;background-color:%%COLOR_BACKGROUND%%;font-family:Montserrat,Arial,Helvetica,sans-serif;color:#1c1c1c;\">\n  <table role=\"presentation\" cellpadding=\"0\" cellspacing=\"0\" border=\"0\" width=\"100%\" style=\"background-color:%%COLOR_BACKGROUND%%;\">\n    <tr>\n      <td align=\"center\" style=\"padding:32px 16px;\">\n        <table role=\"presentation\" cellpadding=\"0\" cellspacing=\"0\" border=\"0\" width=\"600\" style=\"max-width:600px;width:100%;background-color:#ffffff;border-radius:16px;overflow:hidden;border:1px solid %%COLOR_BORDER%%;\">\n          <tr>\n            <td align=\"center\" style=\"background-color:#1c1c1c;padding:24px;\">\n              <img src=\"%%LOGO_URL%%\" alt=\"%%COMPANY_NAME%%\" width=\"200\" style=\"display:block;max-width:200px;height:auto;border:0;\">\n            </td>\n          </tr>\n          <tr>\n            <td style=\"background-color:%%COLOR_PRIMARY%%;padding:14px 24px;\">\n              <p style=\"margin:0;font-size:13px;font-weight:700;letter-spacing:0.06em;text-transform:uppercase;color:#ffffff;\">New Lead · Multiple Services</p>\n            </td>\n          </tr>\n          <tr>\n            <td style=\"padding:28px 32px 8px;\">\n              <h1 style=\"margin:0 0 8px;font-size:24px;line-height:1.25;font-weight:800;color:#1c1c1c;\">%%FULL_NAME%%</h1>\n              <p style=\"margin:0;font-size:15px;line-height:1.5;color:%%COLOR_MUTED%%;\">Submitted %%SUBMISSION_DATE%% · %%SITE_URL%% contact form</p>\n            </td>\n          </tr>\n          <tr>\n            <td style=\"padding:16px 32px 24px;\">\n              <table role=\"presentation\" cellpadding=\"0\" cellspacing=\"0\" border=\"0\" width=\"100%\" style=\"background-color:%%COLOR_SECONDARY%%;border-radius:12px;border:1px solid %%COLOR_BORDER%%;\">\n                <tr>\n                  <td style=\"padding:20px 22px;font-size:15px;line-height:1.6;color:#1c1c1c;\">\n                    <p style=\"margin:0 0 10px;\"><strong>Services:</strong> %%SERVICE_LIST%%</p>\n                    <p style=\"margin:0 0 10px;\"><strong>Property type:</strong> %%PROPERTY_TYPE%%</p>\n                    <p style=\"margin:0 0 10px;\"><strong>Timeline:</strong> %%TIMELINE%%</p>\n                    <p style=\"margin:0 0 10px;\"><strong>Budget:</strong> %%BUDGET%%</p>\n                    <p style=\"margin:0 0 10px;\"><strong>Email:</strong> <a href=\"mailto:%%LEAD_EMAIL%%\" style=\"color:%%COLOR_PRIMARY%%;\">%%LEAD_EMAIL%%</a></p>\n                    <p style=\"margin:0 0 10px;\"><strong>Phone:</strong> <a href=\"tel:%%LEAD_PHONE_TEL%%\" style=\"color:%%COLOR_PRIMARY%%;\">%%LEAD_PHONE%%</a></p>\n                    <p style=\"margin:0 0 10px;\"><strong>Address:</strong> %%ADDRESS%%, %%CITY%%, %%STATE%% %%ZIP_CODE%%</p>\n                    <p style=\"margin:0;\"><strong>Additional details:</strong><br>%%ADDITIONAL_DETAILS%%</p>\n                  </td>\n                </tr>\n              </table>\n            </td>\n          </tr>\n          <tr>\n            <td style=\"padding:0 32px 24px;\">\n              %%PROPERTY_LOOKUP_HTML%%\n            </td>\n          </tr>\n          <tr>\n            <td align=\"center\" style=\"padding:0 32px 28px;\">\n              <a href=\"mailto:%%LEAD_EMAIL%%\" style=\"display:inline-block;background-color:%%COLOR_PRIMARY%%;color:#ffffff;text-decoration:none;font-size:15px;font-weight:700;padding:14px 28px;border-radius:10px;\">Reply to lead</a>\n            </td>\n          </tr>\n          <tr>\n            <td style=\"background-color:#fdf7f0;padding:18px 32px;border-top:1px solid %%COLOR_BORDER%%;\">\n              <p style=\"margin:0;font-size:11px;line-height:1.5;color:%%COLOR_MUTED%%;text-align:center;\">%%LICENSES_FOOTER%%</p>\n            </td>\n          </tr>\n        </table>\n      </td>\n    </tr>\n  </table>\n</body>\n</html>",
		),
	);
	// STRIKESCOPE_LEAD_FORM_SERVICE_EMAILS_END
}

function strikescope_form_get_service_fallback_email_raw($type, $service) {
	$type    = $type === 'admin' ? 'admin' : 'customer';
	$service = sanitize_key($service);
	$upload  = wp_upload_dir();
	if (empty($upload['error']) && $service !== '') {
		$path = trailingslashit($upload['basedir']) . 'strikescope-form/emails/' . $type . '/' . $service . '.html';
		if (file_exists($path)) {
			$raw = file_get_contents($path);
			if (is_string($raw) && trim($raw) !== '') {
				return $raw;
			}
		}
	}

	$embedded = strikescope_form_embedded_service_email_templates();
	if (isset($embedded[ $type ][ $service ]) && is_string($embedded[ $type ][ $service ]) && $embedded[ $type ][ $service ] !== '') {
		return $embedded[ $type ][ $service ];
	}

	if ($type === 'customer') {
		return strikescope_form_get_fallback_email_template_raw();
	}

	return '';
}

function strikescope_form_get_fallback_email_template_raw() {
	$upload = wp_upload_dir();
	if (empty($upload['error'])) {
		$path = trailingslashit($upload['basedir']) . 'strikescope-form/lead-customer-follow-up.html';
		if (file_exists($path)) {
			$raw = file_get_contents($path);
			if (is_string($raw) && trim($raw) !== '') {
				return $raw;
			}
		}
	}
	return strikescope_form_embedded_email_template();
}

function strikescope_form_service_template_row(array $settings, $type, $service) {
	$key  = $type === 'admin' ? 'admin_service_templates' : 'customer_service_templates';
	$rows = isset($settings[ $key ]) && is_array($settings[ $key ]) ? $settings[ $key ] : strikescope_form_default_service_template_map();
	if (!isset($rows[ $service ]) || !is_array($rows[ $service ])) {
		return array('elemailer_id' => 0, 'subject' => '');
	}
	return wp_parse_args($rows[ $service ], array('elemailer_id' => 0, 'subject' => ''));
}

function strikescope_form_resolve_email_subject($type, $service, array $settings, array $tags) {
	$row     = strikescope_form_service_template_row($settings, $type, $service);
	$catalog = strikescope_form_service_email_catalog();
	$subject = isset($row['subject']) ? trim((string) $row['subject']) : '';

	if ($subject === '') {
		if ($type === 'admin') {
			$subject = isset($catalog[ $service ]['admin_subject_default'])
				? $catalog[ $service ]['admin_subject_default']
				: 'New lead — %%FULL_NAME%%';
		} else {
			$subject = isset($catalog[ $service ]['customer_subject_default'])
				? $catalog[ $service ]['customer_subject_default']
				: strikescope_form_defaults()['customer_follow_up_subject'];
		}
	}

	if ($type === 'customer' && $subject === '' && !empty($settings['customer_follow_up_subject'])) {
		$subject = (string) $settings['customer_follow_up_subject'];
	}

	return strikescope_form_apply_merge_tags($subject, $tags);
}

function strikescope_form_lead_merge_tags(array $clean, array $settings, $service = '', $lead_id = 0) {
	$brand     = strikescope_form_brand_defaults();
	$catalog   = strikescope_form_service_email_catalog();
	$services  = strikescope_form_service_labels($clean['serviceType']);
	$phone     = StrikeScope_Branding::phone_display();
	$phone_tel = preg_replace('/[^\d+]/', '', $phone);
	$site_url  = home_url('/');
	$site_host = wp_parse_url($site_url, PHP_URL_SCHEME) . '://' . wp_parse_url($site_url, PHP_URL_HOST);
	$service   = $service !== '' ? sanitize_key($service) : strikescope_form_primary_service($clean['serviceType']);
	$label     = isset($catalog[ $service ]['label']) ? $catalog[ $service ]['label'] : ucfirst($service);
	$intro     = isset($catalog[ $service ]['customer_intro']) ? $catalog[ $service ]['customer_intro'] : '';
	$details   = $clean['additionalDetails'] !== '' ? $clean['additionalDetails'] : '(none)';

	$tags = array(
		'%%FIRST_NAME%%'           => esc_html($clean['firstName']),
		'%%LAST_NAME%%'            => esc_html($clean['lastName']),
		'%%FULL_NAME%%'            => esc_html(trim($clean['firstName'] . ' ' . $clean['lastName'])),
		'%%SERVICE_LIST%%'         => esc_html(implode(', ', $services)),
		'%%PRIMARY_SERVICE%%'      => esc_html($service),
		'%%PRIMARY_SERVICE_LABEL%%'=> esc_html($label),
		'%%SERVICE_INTRO%%'        => esc_html($intro),
		'%%CITY%%'                 => esc_html($clean['city']),
		'%%STATE%%'                 => esc_html($clean['state']),
		'%%PHONE%%'                => esc_html($phone),
		'%%PHONE_TEL%%'            => esc_attr($phone_tel),
		'%%SITE_URL%%'             => esc_url($site_host),
		'%%SUBMISSION_DATE%%'      => esc_html(wp_date(get_option('date_format') . ' ' . get_option('time_format'))),
		'%%LOGO_URL%%'             => esc_url($brand['logo_url']),
		'%%LICENSES_FOOTER%%'      => esc_html($brand['licenses_footer']),
		'%%PROPERTY_TYPE%%'        => esc_html($clean['propertyType']),
		'%%TIMELINE%%'             => esc_html($clean['timeline']),
		'%%BUDGET%%'               => esc_html($clean['budget'] !== '' ? $clean['budget'] : 'Not provided'),
		'%%LEAD_EMAIL%%'           => esc_html($clean['email']),
		'%%LEAD_PHONE%%'           => esc_html($clean['phone']),
		'%%LEAD_PHONE_TEL%%'       => esc_attr(preg_replace('/[^\d+]/', '', $clean['phone'])),
		'%%ADDRESS%%'              => esc_html($clean['address']),
		'%%ZIP_CODE%%'             => esc_html($clean['zipCode']),
		'%%ADDITIONAL_DETAILS%%'   => esc_html($details),
	);

	return array_merge($tags, strikescope_form_location_merge_tags($clean, $lead_id));
}

function strikescope_form_customer_merge_tags(array $clean, array $settings) {
	$service = strikescope_form_customer_email_service($clean['serviceType']);
	return strikescope_form_lead_merge_tags($clean, $settings, $service);
}

function strikescope_form_sample_customer_merge_tags(array $settings) {
	return strikescope_form_lead_merge_tags(
		array(
			'firstName'   => 'Alex',
			'lastName'    => 'Sample',
			'email'       => 'alex@example.com',
			'phone'       => '+1 555-010-0100',
			'address'     => '123 Sample Street',
			'city'        => 'Springfield',
			'state'       => 'IL',
			'zipCode'     => '62704',
			'serviceType' => array('roofing', 'kitchen'),
			'propertyType'=> 'residential',
			'timeline'    => '1-3months',
			'budget'      => '25k-50k',
			'additionalDetails' => 'Sample test submission from StrikeScopeWP settings.',
		),
		$settings,
		'roofing'
	);
}

function strikescope_form_apply_merge_tags($template, array $tags) {
	$out = (string) $template;
	foreach ($tags as $token => $value) {
		$out = str_replace($token, $value, $out);
	}
	return apply_filters('strikescope_form_apply_merge_tags', $out, $tags);
}

function strikescope_form_is_eleemailer_active() {
	return shortcode_exists('elemailer-form')
		|| shortcode_exists('elemailer')
		|| shortcode_exists('elemailer_form')
		|| function_exists('elemailer_render_template');
}

function strikescope_form_render_eleemailer_template($template_id, array $tags) {
	$template_id = absint($template_id);
	if ($template_id <= 0 || !strikescope_form_is_eleemailer_active()) {
		return '';
	}

	$emap_parts = array();
	foreach ($tags as $placeholder => $value) {
		$safe_value = str_replace(array('"', ']'), '', wp_strip_all_tags((string) $value));
		$safe_key   = str_replace(array('"', ']'), '', (string) $placeholder);
		if ($safe_key === '') {
			continue;
		}
		// Elemailer :emap: — left id must match Shortcode widget token in the template.
		$emap_parts[] = $safe_key . ':emap:' . $safe_value;
	}
	$emap_chain = implode(' /esplit/ ', $emap_parts);

	$shortcodes = array(
		'[elemailer:id="' . $template_id . '"]',
	);
	if ($emap_chain !== '') {
		$shortcodes[] = '[elemailer:id="' . $template_id . '" ' . $emap_chain . ']';
		$shortcodes[] = '[ele_override_start][elemailer:id="' . $template_id . '" ' . $emap_chain . '][ele_override_end]';
	}

	foreach ($shortcodes as $shortcode) {
		$html = do_shortcode($shortcode);
		if (is_string($html) && strlen($html) > 100 && stripos($html, '<') !== false && $html !== $shortcode) {
			return $html;
		}
	}

	return '';
}

function strikescope_form_build_email_body($type, array $clean, array $settings, $service, $lead_id = 0) {
	$type    = $type === 'admin' ? 'admin' : 'customer';
	$service = sanitize_key($service);
	$tags    = strikescope_form_lead_merge_tags($clean, $settings, $service, $lead_id);
	$row     = strikescope_form_service_template_row($settings, $type, $service);
	$template_id = isset($row['elemailer_id']) ? absint($row['elemailer_id']) : 0;

	if ($template_id <= 0 && $type === 'customer' && !empty($settings['elemailer_template_id'])) {
		$template_id = absint($settings['elemailer_template_id']);
	}

	$elemailer_html = strikescope_form_render_eleemailer_template($template_id, $tags);
	if ($elemailer_html !== '') {
		return $elemailer_html;
	}

	$fallback = strikescope_form_get_service_fallback_email_raw($type, $service);
	if ($fallback === '') {
		return '';
	}

	return strikescope_form_apply_merge_tags($fallback, $tags);
}

function strikescope_form_build_customer_email_body(array $clean, array $settings, $lead_id = 0) {
	$service = strikescope_form_customer_email_service($clean['serviceType']);
	return strikescope_form_build_email_body('customer', $clean, $settings, $service, $lead_id);
}

function strikescope_form_log_customer_email_result($lead_id, $sent, $error = '') {
	if ($lead_id <= 0) {
		return;
	}
	update_post_meta($lead_id, '_lead_customer_email_sent', $sent ? '1' : '0');
	if ($sent) {
		update_post_meta($lead_id, '_lead_customer_email_sent_at', (string) time());
		delete_post_meta($lead_id, '_lead_customer_email_error');
	} elseif ($error !== '') {
		update_post_meta($lead_id, '_lead_customer_email_error', sanitize_text_field($error));
	}
}

function strikescope_form_send_customer_follow_up(array $clean, array $settings, $lead_id = 0) {
	if (empty($settings['customer_follow_up_enabled'])) {
		strikescope_form_log_customer_email_result($lead_id, false, 'disabled');
		return array('sent' => false, 'skipped' => true, 'reason' => 'disabled');
	}

	if (!is_email($clean['email'])) {
		strikescope_form_log_customer_email_result($lead_id, false, 'invalid_email');
		return array('sent' => false, 'skipped' => true, 'reason' => 'invalid_email');
	}

	$service = strikescope_form_customer_email_service($clean['serviceType']);
	$tags    = strikescope_form_lead_merge_tags($clean, $settings, $service, $lead_id);
	$subject = strikescope_form_resolve_email_subject('customer', $service, $settings, $tags);

	$body = strikescope_form_build_customer_email_body($clean, $settings, $lead_id);
	if ($body === '') {
		strikescope_form_log_customer_email_result($lead_id, false, 'empty_body');
		return array('sent' => false, 'skipped' => true, 'reason' => 'empty_body');
	}

	$reply_to = !empty($settings['customer_reply_to']) && is_email($settings['customer_reply_to'])
		? sanitize_email($settings['customer_reply_to'])
		: sanitize_email($settings['primary_email']);
	$from_name = !empty($settings['customer_from_name'])
		? sanitize_text_field($settings['customer_from_name'])
		: '%%COMPANY_NAME%%';

	$headers = array(
		'Content-Type: text/html; charset=UTF-8',
		'Reply-To: ' . $reply_to,
	);

	add_filter(
		'wp_mail_from_name',
		static function () use ($from_name) {
			return $from_name;
		}
	);

	$sent = wp_mail(
		sanitize_email($clean['email']),
		$subject,
		$body,
		$headers
	);

	remove_all_filters('wp_mail_from_name');

	if (!$sent) {
		strikescope_form_log_customer_email_result($lead_id, false, 'wp_mail_failed');
		return array('sent' => false, 'skipped' => false, 'reason' => 'wp_mail_failed');
	}

	strikescope_form_log_customer_email_result($lead_id, true);
	return array('sent' => true, 'skipped' => false);
}

function strikescope_form_send_customer_test_email($to_email, array $settings, $type = 'customer', $service = 'roofing') {
	if (!is_email($to_email)) {
		return new WP_Error('invalid_email', 'Enter a valid test email address.', array('status' => 400));
	}

	$type    = $type === 'admin' ? 'admin' : 'customer';
	$service = sanitize_key($service);
	if (!strikescope_form_is_email_template_key($service)) {
		$service = 'roofing';
	}

	$sample_services = $service === 'multi' ? array('roofing', 'kitchen') : array($service);
	$sample = array(
		'firstName'   => 'Alex',
		'lastName'    => 'Sample',
		'email'       => sanitize_email($to_email),
		'phone'       => '+1 555-010-0100',
		'address'     => '123 Sample Street',
		'city'        => 'Springfield',
		'state'       => 'IL',
		'zipCode'     => '62704',
		'serviceType' => $sample_services,
		'propertyType'=> 'residential',
		'timeline'    => '1-3months',
		'budget'      => '25k-50k',
		'additionalDetails' => 'Sample test submission from StrikeScopeWP settings.',
	);

	if ($type === 'admin') {
		$to = strikescope_form_resolve_admin_recipients_for_service($service, $settings);
		if (!$to) {
			$to = array(sanitize_email($to_email));
		} else {
			$to = array(sanitize_email($to_email));
		}
		$tags    = strikescope_form_lead_merge_tags($sample, $settings, $service);
		$subject = strikescope_form_resolve_email_subject('admin', $service, $settings, $tags);
		$body    = strikescope_form_build_email_body('admin', $sample, $settings, $service);
		if ($body === '') {
			$body = strikescope_form_build_admin_plain_body($sample);
		}
		$is_html = stripos($body, '<') !== false;
		$headers = array(
			'Content-Type: ' . ($is_html ? 'text/html' : 'text/plain') . '; charset=UTF-8',
			'Reply-To: ' . sanitize_email($sample['email']),
		);
		add_filter('wp_mail_from_name', static function () { return 'StrikeScopeWP'; });
		$sent = wp_mail($to, $subject, $body, $headers);
		remove_all_filters('wp_mail_from_name');
	} else {
		$prev = $settings['customer_follow_up_enabled'];
		$settings['customer_follow_up_enabled'] = 1;
		$result = strikescope_form_send_customer_follow_up($sample, $settings, 0);
		$settings['customer_follow_up_enabled'] = $prev;
		$sent = !empty($result['sent']);
	}

	if ($sent) {
		$row = strikescope_form_service_template_row($settings, $type, $service);
		$source = (absint($row['elemailer_id']) > 0 && strikescope_form_is_eleemailer_active()) ? 'elemailer' : 'fallback_html';
		if ($type === 'customer' && $source === 'fallback_html' && absint($settings['elemailer_template_id']) > 0 && strikescope_form_is_eleemailer_active()) {
			$source = 'elemailer';
		}
		return array(
			'success'         => true,
			'message'         => ucfirst($type) . ' test email sent to ' . sanitize_email($to_email) . ' (' . $service . ').',
			'template_source' => $source,
			'service'         => $service,
			'type'            => $type,
		);
	}

	return new WP_Error(
		'send_failed',
		'Unable to send test email. Check SMTP/mail settings on the server.',
		array('status' => 500)
	);
}

add_action('rest_api_init', static function () {
	register_rest_route(
		'strikescope/v1',
		'/lead-form/submit',
		array(
			'methods'             => 'POST',
			'permission_callback' => '__return_true',
			'callback'            => static function ($request) {
				$params   = $request->get_json_params();
				$params   = is_array($params) ? $params : array();
				$settings = strikescope_form_get_settings();
				$nonce    = isset($params['_wpnonce']) ? sanitize_text_field((string) $params['_wpnonce']) : '';

				if (!wp_verify_nonce($nonce, 'wp_rest')) {
					return strikescope_form_error('invalid_nonce', 'Security check failed. Refresh the page and try again.', 403);
				}

				$honeypot = isset($params['website']) ? sanitize_text_field((string) $params['website']) : '';
				if ($honeypot !== '') {
					return strikescope_form_error('validation_error', 'Unable to submit your request.', 400);
				}

				$started = isset($params['formStartedAt']) ? absint($params['formStartedAt']) : 0;
				$elapsed = (int) round(microtime(true) * 1000) - $started;
				if ($started <= 0 || $elapsed < 3000) {
					return strikescope_form_error('validation_error', 'Please review your submission and try again.', 400);
				}

				if (!strikescope_form_check_rate_limit((int) $settings['rate_limit_per_hour'])) {
					return strikescope_form_error('rate_limited', 'Too many submissions. Please try again later.', 429);
				}

				$token = isset($params['cfTurnstileResponse']) ? sanitize_text_field((string) $params['cfTurnstileResponse']) : '';
				if (!strikescope_form_verify_turnstile($token, (string) $settings['turnstile_secret_key'])) {
					return strikescope_form_error('captcha_failed', 'Security verification failed. Please try again.', 403);
				}

				$validated = strikescope_form_validate_payload($params);
				if (is_wp_error($validated)) {
					return $validated;
				}

				$stored = strikescope_form_store_lead($validated, $settings);
				if (is_wp_error($stored)) {
					return strikescope_form_error('storage_error', 'Unable to save your request right now.', 500);
				}

				strikescope_form_send_mail($validated, $settings, (int) $stored);
				strikescope_form_send_customer_follow_up($validated, $settings, (int) $stored);

				return array(
					'success' => true,
					'message' => 'Thank you. Your request has been received.',
				);
			},
		)
	);

	register_rest_route(
		'strikescope/v1',
		'/lead-form/assets',
		array(
			'methods'             => 'POST',
			'permission_callback' => static function () {
				return current_user_can('manage_options');
			},
			'callback'            => static function ($request) {
				$params = $request->get_json_params();
				$params = is_array($params) ? $params : array();
				$patch  = array();

				if (!empty($params['js_code'])) {
					$GLOBALS['strikescope_form_email_html'] = isset($params['email_html']) ? (string) $params['email_html'] : '';
					$GLOBALS['strikescope_form_email_templates'] = isset($params['email_templates']) && is_array($params['email_templates'])
						? $params['email_templates']
						: array();
					$GLOBALS['strikescope_help_ticket_email_templates'] = isset($params['help_ticket_templates']) && is_array($params['help_ticket_templates'])
						? $params['help_ticket_templates']
						: array();
					$written = strikescope_form_write_asset_files(
						(string) $params['js_code'],
						isset($params['css_code']) ? (string) $params['css_code'] : ''
					);
					if (is_wp_error($written)) {
						return $written;
					}
					$patch['js_url']     = $written['js_url'];
					$patch['css_url']    = $written['css_url'];
					$patch['js_inline']  = '';
					$patch['css_inline'] = '';
				} else {
					if (!empty($params['js_url'])) {
						$patch['js_url'] = esc_url_raw($params['js_url']);
					}
					if (!empty($params['css_url'])) {
						$patch['css_url'] = esc_url_raw($params['css_url']);
					}
				}

				if (!empty($params['js_version'])) {
					$patch['js_version'] = sanitize_text_field($params['js_version']);
				}
				$settings = strikescope_form_update_settings($patch);
				return array(
					'updated' => true,
					'js_url'  => $settings['js_url'],
					'css_url' => $settings['css_url'],
					'js_version' => $settings['js_version'],
				);
			},
		)
	);

	register_rest_route(
		'strikescope/v1',
		'/lead-form/email/test',
		array(
			'methods'             => 'POST',
			'permission_callback' => static function () {
				return current_user_can('manage_options');
			},
			'callback'            => static function ($request) {
				$params   = $request->get_json_params();
				$params   = is_array($params) ? $params : array();
				$settings = strikescope_form_get_settings();
				$to       = isset($params['email']) ? sanitize_email((string) $params['email']) : '';
				$type     = isset($params['type']) && $params['type'] === 'admin' ? 'admin' : 'customer';
				$service  = isset($params['service']) ? sanitize_key((string) $params['service']) : 'roofing';
				if ($to === '' || !is_email($to)) {
					$to = wp_get_current_user()->user_email;
				}
				$result = strikescope_form_send_customer_test_email($to, $settings, $type, $service);
				if (is_wp_error($result)) {
					return $result;
				}
				return $result;
			},
		)
	);
});

add_action('wp_enqueue_scripts', static function () {
	if (!strikescope_form_is_contact_page()) {
		return;
	}
	$settings = strikescope_form_get_settings();
	$assets   = apply_filters(
		'strikescope_form_asset_urls',
		array(
			'js_url'      => $settings['js_url'],
			'css_url'     => $settings['css_url'],
			'js_version'  => $settings['js_version'] !== '' ? $settings['js_version'] : STRIKESCOPE_WP_VERSION,
			'js_inline'   => $settings['js_inline'],
			'css_inline'  => $settings['css_inline'],
		)
	);
	if (empty($assets['js_url']) && empty($assets['js_inline'])) {
		return;
	}

	$version = $assets['js_version'] !== '' ? $assets['js_version'] : '1.0.0';
	if (!empty($assets['js_url'])) {
		wp_enqueue_script(
			'strikescope-form',
			$assets['js_url'],
			array(),
			$version,
			true
		);
	} else {
		wp_register_script('strikescope-form', '', array(), $version, true);
		wp_enqueue_script('strikescope-form');
		wp_add_inline_script('strikescope-form', (string) $assets['js_inline']);
	}
	if (!empty($assets['css_url'])) {
		wp_enqueue_style(
			'strikescope-form',
			$assets['css_url'],
			array(),
			$version
		);
	} elseif (!empty($assets['css_inline'])) {
		wp_register_style('strikescope-form-inline', false, array(), $version);
		wp_enqueue_style('strikescope-form-inline');
		wp_add_inline_style('strikescope-form-inline', (string) $assets['css_inline']);
	}

	$config = apply_filters(
		'strikescope_form_localize_config',
		array(
			'restUrl'           => esc_url_raw(rest_url('strikescope/v1/lead-form/submit')),
			'helpTicketRestUrl' => esc_url_raw(rest_url('strikescope/v1/help-ticket/submit')),
			'nonce'             => wp_create_nonce('wp_rest'),
			'turnstileSiteKey'  => sanitize_text_field($settings['turnstile_site_key']),
			'formStartedAt'     => (int) round(microtime(true) * 1000),
			'enabledServices'   => strikescope_form_enabled_service_ids(),
			'helpDeskEnabled'   => !empty($settings['help_desk_enabled']),
		)
	);
	wp_localize_script('strikescope-form', 'strikeScopeForm', $config);
});

add_action('wp_head', static function () {
	if (!strikescope_form_is_contact_page()) {
		return;
	}
	echo '<style>
	.strikescope-hide-legacy-cf7 .wpcf7,.strikescope-hide-legacy-cf7 .wpcf7-response-output{display:none!important}
	.elementor-column.strikescope-form-column,.elementor-element.strikescope-form-host{width:100%!important;max-width:100%!important}
	.elementor-element.strikescope-form-host .elementor-widget-container,#strikescope-form-root{width:100%!important;max-width:100%!important}
	#strikescope-form-root{margin:0 auto}
	#site-header .site-logo img,header .site-logo img,.menu-item-type-split-logo .site-logo img{max-width:none!important}
	</style>';
}, 20);

add_action('wp_footer', static function () {
	if (!strikescope_form_is_contact_page()) {
		return;
	}
	?>
	<script>
	(function () {
		var rootId = 'strikescope-form-root';
		if (document.getElementById(rootId)) {
			window.dispatchEvent(new Event('strikescope-form-ready'));
			return;
		}

		var target = document.querySelector('.wpcf7, form.wpcf7-form');
		var wrap = null;

		if (target) {
			var widget = target.closest('.elementor-element');
			if (widget) {
				widget.classList.add('strikescope-form-host');
			}
			wrap = target.closest('.elementor-column') || target.closest('.elementor-widget-wrap') || target.closest('.elementor-element') || target.parentElement;
		}

		if (!wrap) {
			var widgets = document.querySelectorAll('.elementor-widget-shortcode, .elementor-widget-html');
			for (var i = 0; i < widgets.length; i++) {
				if (widgets[i].querySelector('.wpcf7') || (widgets[i].textContent || '').indexOf('[contact-form-7') !== -1) {
					wrap = widgets[i].querySelector('.elementor-widget-container') || widgets[i];
					break;
				}
			}
		}

		if (!wrap) {
			var headings = document.querySelectorAll('h1, h2, h3, h4, h5, h6');
			for (var j = 0; j < headings.length; j++) {
				if ((headings[j].textContent || '').trim().toUpperCase().indexOf('CONTACT US') !== -1) {
					wrap = headings[j].closest('.elementor-column') || headings[j].closest('.elementor-widget-wrap') || headings[j].parentElement;
					break;
				}
			}
		}

		if (!wrap) {
			return;
		}

		var column = wrap.closest ? wrap.closest('.elementor-column') : null;
		if (column) {
			column.classList.add('strikescope-form-column');
		}

		wrap.classList.add('strikescope-hide-legacy-cf7');
		wrap.innerHTML = '<div id="' + rootId + '" class="strikescope-form-root"></div>';
		window.dispatchEvent(new Event('strikescope-form-ready'));
	})();
	</script>
	<?php
}, 1);