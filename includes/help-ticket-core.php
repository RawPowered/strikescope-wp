/**
 * Help Desk: CPT, ticket storage, submit REST.
 */

if (!defined('STRIKESCOPE_HELP_TICKET_CPT')) {
	define('STRIKESCOPE_HELP_TICKET_CPT', 'strikescope_help_ticket');
}

function strikescope_help_ticket_status_catalog() {
	// STRIKESCOPE_HELP_TICKET_STATUS_CATALOG_START
	return array(
		"open" => array('label' => "Open", 'color' => "#2271b1", 'open' => true, 'notify_customer' => false),
		"waiting_customer" => array('label' => "Waiting on customer", 'color' => "#dba617", 'open' => true, 'notify_customer' => true),
		"waiting_staff" => array('label' => "Waiting on staff", 'color' => "#646970", 'open' => true, 'notify_customer' => false),
		"in_progress" => array('label' => "In progress", 'color' => "#8c45ff", 'open' => true, 'notify_customer' => true),
		"resolved" => array('label' => "Resolved", 'color' => "#00a32a", 'open' => false, 'notify_customer' => true),
		"closed" => array('label' => "Closed", 'color' => "#1c1c1c", 'open' => false, 'notify_customer' => true),
	);
	// STRIKESCOPE_HELP_TICKET_STATUS_CATALOG_END
}

function strikescope_help_ticket_allowed_statuses() {
	return array_keys(strikescope_help_ticket_status_catalog());
}

function strikescope_help_ticket_normalize_status($status) {
	$status = sanitize_key((string) $status);
	return in_array($status, strikescope_help_ticket_allowed_statuses(), true) ? $status : 'open';
}

function strikescope_help_ticket_get_status($ticket_id) {
	$status = get_post_meta($ticket_id, '_ticket_status', true);
	if ($status === '' || $status === false) {
		return 'open';
	}
	return strikescope_help_ticket_normalize_status($status);
}

function strikescope_help_ticket_set_status($ticket_id, $status, $send_email = false, $status_note = '') {
	$ticket_id = absint($ticket_id);
	if ($ticket_id <= 0) {
		return false;
	}
	$old    = strikescope_help_ticket_get_status($ticket_id);
	$status = strikescope_help_ticket_normalize_status($status);
	update_post_meta($ticket_id, '_ticket_status', $status);
	update_post_meta($ticket_id, '_ticket_status_updated_at', (string) time());
	if ($old !== $status && $send_email && function_exists('strikescope_help_ticket_send_status_email')) {
		strikescope_help_ticket_send_status_email($ticket_id, $status, $status_note);
	}
	return true;
}

function strikescope_help_ticket_status_badge_html($status) {
	$catalog = strikescope_help_ticket_status_catalog();
	$status  = strikescope_help_ticket_normalize_status($status);
	$row     = isset($catalog[ $status ]) ? $catalog[ $status ] : $catalog['open'];
	return '<span class="strikescope-ticket-status-badge" style="display:inline-block;padding:2px 8px;border-radius:4px;font-size:12px;font-weight:600;color:#fff;background:' . esc_attr($row['color']) . ';">' . esc_html($row['label']) . '</span>';
}

function strikescope_help_ticket_status_select_html($ticket_id, $css_class = 'strikescope-ticket-status-select') {
	$ticket_id = absint($ticket_id);
	$current   = strikescope_help_ticket_get_status($ticket_id);
	$html      = '<select class="' . esc_attr($css_class) . '" data-ticket-id="' . esc_attr((string) $ticket_id) . '">';
	foreach (strikescope_help_ticket_status_catalog() as $id => $row) {
		$html .= '<option value="' . esc_attr($id) . '"' . selected($current, $id, false) . '>' . esc_html($row['label']) . '</option>';
	}
	$html .= '</select>';
	return $html;
}

function strikescope_help_ticket_sanitize_email_templates($input, $current) {
	$out = strikescope_help_ticket_default_email_template_map();
	if (!is_array($input)) {
		return is_array($current) ? $current : $out;
	}
	foreach (strikescope_help_ticket_email_template_keys() as $key) {
		if (!isset($input[ $key ]) || !is_array($input[ $key ])) {
			continue;
		}
		$row = $input[ $key ];
		$out[ $key ] = array(
			'enabled'      => !empty($row['enabled']) ? 1 : 0,
			'elemailer_id' => isset($row['elemailer_id']) ? absint($row['elemailer_id']) : 0,
			'subject'      => isset($row['subject']) ? sanitize_text_field($row['subject']) : '',
		);
	}
	return $out;
}

function strikescope_help_ticket_user_can_manage($user_id = 0) {
	if (function_exists('strikescope_form_user_can_manage_leads')) {
		return strikescope_form_user_can_manage_leads($user_id);
	}
	return current_user_can('edit_posts');
}

function strikescope_help_ticket_default_email_template_map() {
	$keys = strikescope_help_ticket_email_template_keys();
	$map  = array();
	foreach ($keys as $key) {
		$map[ $key ] = array(
			'enabled'      => 1,
			'elemailer_id' => 0,
			'subject'      => '',
		);
	}
	return $map;
}

function strikescope_help_ticket_email_template_keys() {
	return array(
		'new-ticket-customer',
		'new-ticket-staff',
		'staff-reply',
		'status-open',
		'status-waiting_customer',
		'status-waiting_staff',
		'status-in_progress',
		'status-resolved',
		'status-closed',
	);
}

function strikescope_help_ticket_email_subject_defaults() {
	return array(
		'new-ticket-customer'      => 'Your %%COMPANY_NAME%% help ticket %%TICKET_NUMBER%%',
		'new-ticket-staff'         => 'New help ticket %%TICKET_NUMBER%% — %%CUSTOMER_NAME%%',
		'staff-reply'              => 'Re: Ticket %%TICKET_NUMBER%% — Help Desk',
		'status-open'              => 'Ticket %%TICKET_NUMBER%% update',
		'status-waiting_customer'  => 'Action needed on ticket %%TICKET_NUMBER%%',
		'status-waiting_staff'     => 'Ticket %%TICKET_NUMBER%% update',
		'status-in_progress'       => 'Ticket %%TICKET_NUMBER%% is in progress',
		'status-resolved'          => 'Ticket %%TICKET_NUMBER%% resolved',
		'status-closed'            => 'Ticket %%TICKET_NUMBER%% closed',
	);
}

function strikescope_help_ticket_get_settings() {
	$settings = strikescope_form_get_settings();
	return array(
		'enabled'          => !empty($settings['help_desk_enabled']),
		'support_email'    => !empty($settings['help_desk_support_email']) ? sanitize_email($settings['help_desk_support_email']) : sanitize_email($settings['primary_email']),
		'inbound_domain'   => !empty($settings['help_desk_inbound_domain']) ? sanitize_text_field($settings['help_desk_inbound_domain']) : 'inbound.%%SITE_URL%%',
		'inbound_secret'   => isset($settings['help_desk_inbound_secret']) ? (string) $settings['help_desk_inbound_secret'] : '',
		'from_name'        => !empty($settings['help_desk_from_name']) ? sanitize_text_field($settings['help_desk_from_name']) : 'Help Desk',
		'email_templates'  => isset($settings['help_desk_email_templates']) && is_array($settings['help_desk_email_templates'])
			? $settings['help_desk_email_templates']
			: strikescope_help_ticket_default_email_template_map(),
	);
}

function strikescope_help_ticket_ensure_inbound_secret() {
	$settings = strikescope_form_get_settings();
	if (!empty($settings['help_desk_inbound_secret'])) {
		return $settings['help_desk_inbound_secret'];
	}
	$secret = bin2hex(random_bytes(24));
	strikescope_form_update_settings(array('help_desk_inbound_secret' => $secret));
	return $secret;
}

function strikescope_help_ticket_generate_number() {
	$year = gmdate('Y');
	$q    = new WP_Query(
		array(
			'post_type'      => STRIKESCOPE_HELP_TICKET_CPT,
			'post_status'    => 'publish',
			'posts_per_page' => 1,
			'fields'         => 'ids',
			'orderby'        => 'ID',
			'order'          => 'DESC',
		)
	);
	$seq = 1;
	if (!empty($q->posts[0])) {
		$last = get_post_meta((int) $q->posts[0], '_ticket_number', true);
		if (is_string($last) && preg_match('/HT-' . preg_quote($year, '/') . '-(\d+)/', $last, $m)) {
			$seq = (int) $m[1] + 1;
		}
	}
	return 'HT-' . $year . '-' . str_pad((string) $seq, 4, '0', STR_PAD_LEFT);
}

function strikescope_help_ticket_generate_reply_token() {
	return bin2hex(random_bytes(16));
}

function strikescope_help_ticket_new_message_id($ticket_id, $msg_id) {
	$host = wp_parse_url(home_url('/'), PHP_URL_HOST);
	$host = $host ? $host : '%%SITE_URL%%';
	return '<ticket-' . absint($ticket_id) . '-msg-' . sanitize_key($msg_id) . '@' . $host . '>';
}

function strikescope_help_ticket_get_messages($ticket_id) {
	$raw = get_post_meta($ticket_id, '_ticket_messages', true);
	if (!is_string($raw) || $raw === '') {
		return array();
	}
	$decoded = json_decode($raw, true);
	return is_array($decoded) ? $decoded : array();
}

function strikescope_help_ticket_save_messages($ticket_id, array $messages) {
	update_post_meta($ticket_id, '_ticket_messages', wp_json_encode(array_values($messages)));
	update_post_meta($ticket_id, '_ticket_last_activity_at', (string) time());
}

function strikescope_help_ticket_append_message($ticket_id, array $message) {
	$messages = strikescope_help_ticket_get_messages($ticket_id);
	if (empty($message['id'])) {
		$message['id'] = 'msg_' . wp_generate_password(12, false, false);
	}
	if (empty($message['created_at'])) {
		$message['created_at'] = time();
	}
	$messages[] = $message;
	strikescope_help_ticket_save_messages($ticket_id, $messages);
	return $message;
}

function strikescope_help_ticket_payload_array($ticket_id) {
	$raw = get_post_meta($ticket_id, '_ticket_payload', true);
	if (!is_string($raw) || $raw === '') {
		return array();
	}
	$decoded = json_decode($raw, true);
	return is_array($decoded) ? $decoded : array();
}

function strikescope_help_ticket_format_address(array $clean) {
	$parts = array_filter(
		array(
			$clean['address'] ?? '',
			$clean['city'] ?? '',
			$clean['state'] ?? '',
			$clean['zipCode'] ?? '',
		)
	);
	return implode(', ', $parts);
}

function strikescope_help_ticket_admin_url($ticket_id) {
	return admin_url('admin.php?page=strikescope-help-ticket-detail&ticket_id=' . absint($ticket_id));
}

function strikescope_help_ticket_reply_address($ticket_id, $token = '') {
	$ticket_id = absint($ticket_id);
	$token     = $token !== '' ? $token : (string) get_post_meta($ticket_id, '_ticket_reply_token', true);
	$ht        = strikescope_help_ticket_get_settings();
	$domain    = $ht['inbound_domain'] !== '' ? $ht['inbound_domain'] : 'inbound.%%SITE_URL%%';
	return 'ticket+' . $ticket_id . '+' . $token . '@' . $domain;
}

function strikescope_help_ticket_validate_payload(array $params) {
	$clean = array();
	$clean['firstName']         = isset($params['firstName']) ? sanitize_text_field(substr((string) $params['firstName'], 0, 80)) : '';
	$clean['lastName']          = isset($params['lastName']) ? sanitize_text_field(substr((string) $params['lastName'], 0, 80)) : '';
	$clean['email']             = isset($params['email']) ? sanitize_email((string) $params['email']) : '';
	$clean['phone']             = isset($params['phone']) ? sanitize_text_field(substr((string) $params['phone'], 0, 40)) : '';
	$clean['address']           = isset($params['address']) ? sanitize_text_field(substr((string) $params['address'], 0, 200)) : '';
	$clean['city']              = isset($params['city']) ? sanitize_text_field(substr((string) $params['city'], 0, 80)) : '';
	$clean['state']             = isset($params['state']) ? strtoupper(sanitize_text_field(substr((string) $params['state'], 0, 2))) : '';
	$clean['zipCode']           = isset($params['zipCode']) ? sanitize_text_field(substr((string) $params['zipCode'], 0, 20)) : '';
	$clean['issueDescription']  = isset($params['issueDescription']) ? sanitize_textarea_field(substr((string) $params['issueDescription'], 0, 5000)) : '';

	if ($clean['firstName'] === '' || $clean['lastName'] === '') {
		return new WP_Error('validation_error', 'Please enter your name.', array('status' => 400));
	}
	if (!is_email($clean['email'])) {
		return new WP_Error('validation_error', 'Please enter a valid email address.', array('status' => 400));
	}
	if ($clean['phone'] === '' || !preg_match('/^[0-9+\-().\s]{7,40}$/', $clean['phone'])) {
		return new WP_Error('validation_error', 'Please enter a valid phone number.', array('status' => 400));
	}
	if ($clean['address'] === '' || $clean['city'] === '' || $clean['state'] === '' || $clean['zipCode'] === '') {
		return new WP_Error('validation_error', 'Please enter your service address.', array('status' => 400));
	}
	if (!preg_match('/^[0-9\-]{4,20}$/', $clean['zipCode'])) {
		return new WP_Error('validation_error', 'Please enter a valid ZIP code.', array('status' => 400));
	}
	if ($clean['issueDescription'] === '') {
		return new WP_Error('validation_error', 'Please describe your issue.', array('status' => 400));
	}

	$settings = strikescope_form_get_settings();
	$bl_clean = array(
		'email'   => $clean['email'],
		'city'    => $clean['city'],
		'state'   => $clean['state'],
		'zipCode' => $clean['zipCode'],
	);
	if (function_exists('strikescope_form_check_blacklist') && strikescope_form_check_blacklist($bl_clean, $settings)) {
		return new WP_Error('validation_error', 'Unable to submit your request.', array('status' => 400));
	}

	return $clean;
}

function strikescope_help_ticket_store(array $clean) {
	$name   = trim($clean['firstName'] . ' ' . $clean['lastName']);
	$number = strikescope_help_ticket_generate_number();
	$token  = strikescope_help_ticket_generate_reply_token();

	$post_id = wp_insert_post(
		array(
			'post_type'   => STRIKESCOPE_HELP_TICKET_CPT,
			'post_status' => 'publish',
			'post_title'  => $number . ' — ' . $name,
			'post_author' => function_exists('strikescope_form_default_lead_author_id')
				? strikescope_form_default_lead_author_id()
				: 1,
		),
		true
	);
	if (is_wp_error($post_id)) {
		return $post_id;
	}

	update_post_meta($post_id, '_ticket_number', $number);
	update_post_meta($post_id, '_ticket_reply_token', $token);
	update_post_meta($post_id, '_ticket_payload', wp_json_encode($clean));
	update_post_meta($post_id, '_ticket_email', sanitize_email($clean['email']));
	update_post_meta($post_id, '_ticket_phone', sanitize_text_field($clean['phone']));
	update_post_meta($post_id, '_ticket_customer_name', sanitize_text_field($name));
	update_post_meta($post_id, '_ticket_address', sanitize_text_field($clean['address']));
	update_post_meta($post_id, '_ticket_city', sanitize_text_field($clean['city']));
	update_post_meta($post_id, '_ticket_state', sanitize_text_field($clean['state']));
	update_post_meta($post_id, '_ticket_zip', sanitize_text_field($clean['zipCode']));
	update_post_meta($post_id, '_ticket_submitted_at', (string) time());
	update_post_meta($post_id, '_ticket_assigned_user', 0);
	strikescope_help_ticket_set_status($post_id, 'open');

	$msg_id = 'msg_' . wp_generate_password(12, false, false);
	$subject = 'Help ticket ' . $number;
	strikescope_help_ticket_append_message(
		$post_id,
		array(
			'id'               => $msg_id,
			'direction'        => 'inbound',
			'author_type'      => 'customer',
			'author_name'      => $name,
			'author_user_id'   => 0,
			'subject'          => $subject,
			'body_html'        => wpautop(esc_html($clean['issueDescription'])),
			'body_text'        => $clean['issueDescription'],
			'email_message_id' => strikescope_help_ticket_new_message_id($post_id, $msg_id),
			'in_reply_to'      => '',
			'created_at'       => time(),
		)
	);

	return (int) $post_id;
}

add_action('init', static function () {
	register_post_type(
		STRIKESCOPE_HELP_TICKET_CPT,
		array(
			'labels'              => array(
				'name'          => 'Help Desk',
				'singular_name' => 'Help Ticket',
				'menu_name'     => 'Help Desk',
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
			'menu_icon'           => 'dashicons-sos',
		)
	);
});

add_filter('map_meta_cap', static function ($caps, $cap, $user_id, $args) {
	$meta_caps = array('read_post', 'edit_post', 'delete_post', 'publish_posts');
	if (!in_array($cap, $meta_caps, true)) {
		return $caps;
	}
	$post_id = isset($args[0]) ? (int) $args[0] : 0;
	if ($post_id <= 0) {
		return $caps;
	}
	$post = get_post($post_id);
	if (!$post || $post->post_type !== STRIKESCOPE_HELP_TICKET_CPT) {
		return $caps;
	}
	if (strikescope_help_ticket_user_can_manage($user_id)) {
		return array('edit_posts');
	}
	return $caps;
}, 11, 4);

add_action('rest_api_init', static function () {
	register_rest_route(
		'strikescope/v1',
		'/help-ticket/submit',
		array(
			'methods'             => 'POST',
			'permission_callback' => '__return_true',
			'callback'            => static function ($request) {
				$ht = strikescope_help_ticket_get_settings();
				if (!$ht['enabled']) {
					return new WP_Error('disabled', 'Help desk submissions are not available.', array('status' => 503));
				}

				$params   = $request->get_json_params();
				$params   = is_array($params) ? $params : array();
				$settings = strikescope_form_get_settings();
				$nonce    = isset($params['_wpnonce']) ? sanitize_text_field((string) $params['_wpnonce']) : '';

				if (!wp_verify_nonce($nonce, 'wp_rest')) {
					return new WP_Error('invalid_nonce', 'Security check failed. Refresh the page and try again.', array('status' => 403));
				}

				$honeypot = isset($params['website']) ? sanitize_text_field((string) $params['website']) : '';
				if ($honeypot !== '') {
					return new WP_Error('validation_error', 'Unable to submit your request.', array('status' => 400));
				}

				$started = isset($params['formStartedAt']) ? absint($params['formStartedAt']) : 0;
				$elapsed = (int) round(microtime(true) * 1000) - $started;
				if ($started <= 0 || $elapsed < 3000) {
					return new WP_Error('validation_error', 'Please review your submission and try again.', array('status' => 400));
				}

				if (!strikescope_form_check_rate_limit((int) $settings['rate_limit_per_hour'])) {
					return new WP_Error('rate_limited', 'Too many submissions. Please try again later.', array('status' => 429));
				}

				$token = isset($params['cfTurnstileResponse']) ? sanitize_text_field((string) $params['cfTurnstileResponse']) : '';
				if (!strikescope_form_verify_turnstile($token, (string) $settings['turnstile_secret_key'])) {
					return new WP_Error('captcha_failed', 'Security verification failed. Please try again.', array('status' => 403));
				}

				$validated = strikescope_help_ticket_validate_payload($params);
				if (is_wp_error($validated)) {
					return $validated;
				}

				$stored = strikescope_help_ticket_store($validated);
				if (is_wp_error($stored)) {
					return new WP_Error('storage_error', 'Unable to save your request right now.', array('status' => 500));
				}

				if (function_exists('strikescope_help_ticket_send_new_ticket_emails')) {
					strikescope_help_ticket_send_new_ticket_emails($stored);
				}

				$number = get_post_meta($stored, '_ticket_number', true);
				return array(
					'success'      => true,
					'ticketId'     => $stored,
					'ticketNumber' => $number,
					'message'      => 'Thank you. Your help ticket ' . $number . ' has been received. Check your email to continue the conversation.',
				);
			},
		)
	);
});