/**
 * API keys, outbound webhooks, REST lead endpoints.
 */

function strikescope_form_integration_defaults() {
	return array(
		'api_keys'              => array(),
		'webhooks'              => array(),
		'integration_log'       => array(),
		'google_calendar_client_id'     => '',
		'google_calendar_client_secret' => '',
		'outlook_tenant_id'             => '',
		'outlook_client_id'             => '',
		'outlook_client_secret'         => '',
	);
}

function strikescope_form_allowed_api_scopes() {
	return array('leads:read', 'leads:write', 'leads:export');
}

function strikescope_form_generate_api_key() {
	return 'spk_' . bin2hex(random_bytes(24));
}

function strikescope_form_hash_api_key($key) {
	return hash_hmac('sha256', $key, wp_salt('auth'));
}

function strikescope_form_add_api_key($label, array $scopes) {
	$settings = strikescope_form_get_settings();
	$keys     = isset($settings['api_keys']) && is_array($settings['api_keys']) ? $settings['api_keys'] : array();
	$plain    = strikescope_form_generate_api_key();
	$id       = 'key_' . wp_generate_password(8, false, false);
	$keys[]   = array(
		'id'         => $id,
		'label'      => sanitize_text_field($label),
		'prefix'     => substr($plain, 0, 12),
		'hash'       => strikescope_form_hash_api_key($plain),
		'scopes'     => array_values(array_intersect($scopes, strikescope_form_allowed_api_scopes())),
		'created_at' => gmdate('c'),
		'last_used'  => '',
	);
	strikescope_form_update_settings(array('api_keys' => $keys));
	return array('id' => $id, 'key' => $plain);
}

function strikescope_form_revoke_api_key($id) {
	$settings = strikescope_form_get_settings();
	$keys     = isset($settings['api_keys']) && is_array($settings['api_keys']) ? $settings['api_keys'] : array();
	$keys     = array_values(array_filter($keys, static function ($row) use ($id) {
		return ($row['id'] ?? '') !== $id;
	}));
	strikescope_form_update_settings(array('api_keys' => $keys));
}

function strikescope_form_extract_api_key_from_request($request) {
	$header = $request->get_header('x-strikescope-api-key');
	if ($header) {
		return trim((string) $header);
	}
	$auth = $request->get_header('authorization');
	if ($auth && stripos($auth, 'Bearer ') === 0) {
		return trim(substr($auth, 7));
	}
	return '';
}

function strikescope_form_authenticate_api_request($request, $scope) {
	if (strikescope_form_user_can_manage_leads()) {
		return true;
	}
	$key = strikescope_form_extract_api_key_from_request($request);
	if ($key === '') {
		return false;
	}
	$settings = strikescope_form_get_settings();
	$keys     = isset($settings['api_keys']) && is_array($settings['api_keys']) ? $settings['api_keys'] : array();
	$hash     = strikescope_form_hash_api_key($key);
	foreach ($keys as $index => $row) {
		if (!hash_equals($row['hash'] ?? '', $hash)) {
			continue;
		}
		$scopes = isset($row['scopes']) && is_array($row['scopes']) ? $row['scopes'] : array();
		if (!in_array($scope, $scopes, true)) {
			return false;
		}
		$keys[ $index ]['last_used'] = gmdate('c');
		strikescope_form_update_settings(array('api_keys' => $keys));
		return true;
	}
	return false;
}

function strikescope_form_render_field_map($template, $post_id, $extra = array()) {
	$tags = array_merge(strikescope_form_lead_merge_context($post_id), $extra);
	$out  = (string) $template;
	foreach ($tags as $token => $value) {
		$out = str_replace($token, (string) $value, $out);
	}
	return $out;
}

function strikescope_form_log_integration_failure($message) {
	$settings = strikescope_form_get_settings();
	$log      = isset($settings['integration_log']) && is_array($settings['integration_log']) ? $settings['integration_log'] : array();
	array_unshift($log, array('at' => gmdate('c'), 'message' => sanitize_text_field($message)));
	$log = array_slice($log, 0, 20);
	strikescope_form_update_settings(array('integration_log' => $log));
}

function strikescope_form_dispatch_integration_event($event, $post_id, $extra = array()) {
	$post_id = absint($post_id);
	if ($post_id <= 0) {
		return;
	}
	$settings = strikescope_form_get_settings();
	$hooks    = isset($settings['webhooks']) && is_array($settings['webhooks']) ? $settings['webhooks'] : array();
	$lead     = strikescope_form_lead_to_array($post_id);
	$payload  = array(
		'event'       => sanitize_text_field($event),
		'occurred_at' => gmdate('c'),
		'lead'        => $lead,
		'extra'       => $extra,
	);
	$body = wp_json_encode($payload, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
	foreach ($hooks as $hook) {
		if (empty($hook['enabled']) || empty($hook['url'])) {
			continue;
		}
		$events = isset($hook['events']) && is_array($hook['events']) ? $hook['events'] : array();
		if ($events && !in_array($event, $events, true)) {
			continue;
		}
		$url = esc_url_raw($hook['url']);
		if (!$url) {
			continue;
		}
		$send_body = $body;
		if (!empty($hook['field_map'])) {
			$mapped = strikescope_form_render_field_map($hook['field_map'], $post_id, $extra);
			$decoded = json_decode($mapped, true);
			$send_body = is_array($decoded) ? wp_json_encode($decoded) : $mapped;
		}
		$secret = isset($hook['secret']) ? (string) $hook['secret'] : '';
		$sig    = $secret !== '' ? hash_hmac('sha256', $send_body, $secret) : '';
		$headers = array(
			'Content-Type' => 'application/json; charset=utf-8',
		);
		if ($sig !== '') {
			$headers['X-StrikeScope-Signature'] = 'sha256=' . $sig;
		}
		if (!empty($hook['headers']) && is_array($hook['headers'])) {
			foreach ($hook['headers'] as $hk => $hv) {
				$headers[ sanitize_text_field($hk) ] = sanitize_text_field($hv);
			}
		}
		$response = wp_remote_post(
			$url,
			array(
				'timeout'  => 8,
				'blocking' => false,
				'headers'  => $headers,
				'body'     => $send_body,
			)
		);
		if (is_wp_error($response)) {
			strikescope_form_log_integration_failure($event . ': ' . $response->get_error_message());
		}
	}
	$log = get_post_meta($post_id, '_lead_integration_log', true);
	if (!is_array($log)) {
		$log = array();
	}
	array_unshift($log, array('event' => $event, 'at' => gmdate('c')));
	update_post_meta($post_id, '_lead_integration_log', array_slice($log, 0, 5));
}

function strikescope_form_patch_lead($post_id, array $data) {
	$post_id = absint($post_id);
	if ($post_id <= 0 || get_post_type($post_id) !== STRIKESCOPE_LEAD_CPT) {
		return new WP_Error('not_found', 'Lead not found', array('status' => 404));
	}
	if (isset($data['status'])) {
		strikescope_form_set_lead_status($post_id, sanitize_key($data['status']));
	}
	if (array_key_exists('sales_agent', $data) || array_key_exists('sales_agent_id', $data)) {
		$agent = isset($data['sales_agent_id']) ? absint($data['sales_agent_id']) : absint($data['sales_agent']);
		strikescope_form_set_sales_agent($post_id, $agent);
	}
	if (array_key_exists('sold_price', $data)) {
		if (strikescope_form_get_lead_status($post_id) === 'sold') {
			strikescope_form_set_sold_price($post_id, $data['sold_price']);
		}
	}
	return strikescope_form_lead_to_array($post_id);
}

add_action('wp_ajax_strikescope_lead_generate_api_key', static function () {
	if (!current_user_can('manage_options')) {
		wp_send_json_error(array('message' => 'Forbidden'), 403);
	}
	check_ajax_referer('strikescope_lead_admin', 'nonce');
	$label  = isset($_POST['label']) ? sanitize_text_field(wp_unslash($_POST['label'])) : 'API key';
	$scopes = isset($_POST['scopes']) ? array_map('sanitize_text_field', (array) wp_unslash($_POST['scopes'])) : array('leads:read');
	$result = strikescope_form_add_api_key($label, $scopes);
	wp_send_json_success($result);
});

add_action('wp_ajax_strikescope_lead_revoke_api_key', static function () {
	if (!current_user_can('manage_options')) {
		wp_send_json_error(array('message' => 'Forbidden'), 403);
	}
	check_ajax_referer('strikescope_lead_admin', 'nonce');
	$id = isset($_POST['key_id']) ? sanitize_text_field(wp_unslash($_POST['key_id'])) : '';
	if ($id === '') {
		wp_send_json_error(array('message' => 'Missing key id'), 400);
	}
	strikescope_form_revoke_api_key($id);
	wp_send_json_success(array('revoked' => true));
});

add_action('wp_ajax_strikescope_lead_test_webhook', static function () {
	if (!current_user_can('manage_options')) {
		wp_send_json_error(array('message' => 'Forbidden'), 403);
	}
	check_ajax_referer('strikescope_lead_admin', 'nonce');
	$url = isset($_POST['url']) ? esc_url_raw(wp_unslash($_POST['url'])) : '';
	if (!$url) {
		wp_send_json_error(array('message' => 'URL required'), 400);
	}
	$body = wp_json_encode(array('event' => 'webhook.test', 'occurred_at' => gmdate('c'), 'lead' => array('lead_id' => 0)));
	$response = wp_remote_post($url, array('timeout' => 10, 'headers' => array('Content-Type' => 'application/json'), 'body' => $body));
	if (is_wp_error($response)) {
		wp_send_json_error(array('message' => $response->get_error_message()), 500);
	}
	wp_send_json_success(array('code' => wp_remote_retrieve_response_code($response)));
});

add_action('wp_ajax_strikescope_lead_set_sold_price', static function () {
	if (!strikescope_form_user_can_manage_leads()) {
		wp_send_json_error(array('message' => 'Forbidden'), 403);
	}
	check_ajax_referer('strikescope_lead_admin', 'nonce');
	$post_id = isset($_POST['lead_id']) ? absint($_POST['lead_id']) : 0;
	$price   = isset($_POST['sold_price']) ? wp_unslash($_POST['sold_price']) : '';
	if ($post_id <= 0) {
		wp_send_json_error(array('message' => 'Invalid lead'), 400);
	}
	if (strikescope_form_get_lead_status($post_id) !== 'sold') {
		wp_send_json_error(array('message' => 'Sold price only allowed when status is Sold'), 400);
	}
	strikescope_form_set_sold_price($post_id, $price);
	wp_send_json_success(array('sold_price' => strikescope_form_get_sold_price($post_id)));
});

add_action('wp_ajax_strikescope_lead_copy_contact', static function () {
	if (!strikescope_form_user_can_manage_leads()) {
		wp_send_json_error(array('message' => 'Forbidden'), 403);
	}
	check_ajax_referer('strikescope_lead_admin', 'nonce');
	$post_id = isset($_POST['lead_id']) ? absint($_POST['lead_id']) : 0;
	$format  = isset($_POST['format']) ? sanitize_key(wp_unslash($_POST['format'])) : 'tsv';
	if ($post_id <= 0) {
		wp_send_json_error(array('message' => 'Invalid lead'), 400);
	}
	wp_send_json_success(array('text' => strikescope_form_contact_copy_text($post_id, $format === 'multiline' ? 'multiline' : 'tsv')));
});

function strikescope_form_render_api_webhooks_settings($settings, $show_heading = true) {
	if (!current_user_can('manage_options')) {
		return;
	}
	$keys = isset($settings['api_keys']) && is_array($settings['api_keys']) ? $settings['api_keys'] : array();
	$hooks = isset($settings['webhooks']) && is_array($settings['webhooks']) ? $settings['webhooks'] : array();
	if (!$hooks) {
		$hooks = array(array('label' => '', 'url' => '', 'secret' => '', 'enabled' => 0, 'events' => array('lead.created'), 'field_map' => ''));
	}
	$events = array('lead.created', 'lead.status_changed', 'lead.assigned', 'lead.appointment_scheduled', 'lead.appointment_rescheduled', 'lead.appointment_cancelled');
	?>
	<?php if ($show_heading) : ?>
		<h2 class="title">API / Webhooks</h2>
	<?php endif; ?>
	<h3>REST API keys</h3>
	<p class="description">Use <code>Authorization: Bearer {key}</code> or <code>X-StrikeScope-Api-Key</code>. Scopes: leads:read, leads:write, leads:export.</p>
	<table class="widefat striped" style="max-width:900px;margin-bottom:12px;">
		<thead><tr><th>Label</th><th>Prefix</th><th>Scopes</th><th>Created</th><th></th></tr></thead>
		<tbody>
		<?php foreach ($keys as $row) : ?>
			<tr>
				<td><?php echo esc_html($row['label'] ?? ''); ?></td>
				<td><code><?php echo esc_html($row['prefix'] ?? ''); ?>…</code></td>
				<td><?php echo esc_html(implode(', ', (array) ($row['scopes'] ?? array()))); ?></td>
				<td><?php echo esc_html($row['created_at'] ?? ''); ?></td>
				<td><button type="button" class="button strikescope-revoke-api-key" data-key-id="<?php echo esc_attr($row['id'] ?? ''); ?>">Revoke</button></td>
			</tr>
		<?php endforeach; ?>
		</tbody>
	</table>
	<p>
		<input type="text" id="strikescope-new-api-key-label" class="regular-text" placeholder="Key label">
		<button type="button" class="button" id="strikescope-generate-api-key">Generate API key</button>
		<span id="strikescope-new-api-key-result" class="description"></span>
	</p>

	<h3>Outbound webhooks</h3>
	<?php foreach ($hooks as $i => $hook) : ?>
		<table class="form-table" role="presentation">
			<tr><th>Label</th><td><input type="text" name="<?php echo esc_attr(STRIKESCOPE_SETTINGS_OPTION); ?>[webhooks][<?php echo (int) $i; ?>][label]" value="<?php echo esc_attr($hook['label'] ?? ''); ?>" class="regular-text"></td></tr>
			<tr><th>URL</th><td><input type="url" name="<?php echo esc_attr(STRIKESCOPE_SETTINGS_OPTION); ?>[webhooks][<?php echo (int) $i; ?>][url]" value="<?php echo esc_attr($hook['url'] ?? ''); ?>" class="large-text"></td></tr>
			<tr><th>Secret (HMAC)</th><td><input type="text" name="<?php echo esc_attr(STRIKESCOPE_SETTINGS_OPTION); ?>[webhooks][<?php echo (int) $i; ?>][secret]" value="<?php echo esc_attr($hook['secret'] ?? ''); ?>" class="regular-text"></td></tr>
			<tr><th>Enabled</th><td><label><input type="checkbox" name="<?php echo esc_attr(STRIKESCOPE_SETTINGS_OPTION); ?>[webhooks][<?php echo (int) $i; ?>][enabled]" value="1" <?php checked(!empty($hook['enabled'])); ?>> Active</label></td></tr>
			<tr><th>Events</th><td>
				<?php foreach ($events as $ev) : ?>
					<label style="display:inline-block;margin-right:12px;"><input type="checkbox" name="<?php echo esc_attr(STRIKESCOPE_SETTINGS_OPTION); ?>[webhooks][<?php echo (int) $i; ?>][events][]" value="<?php echo esc_attr($ev); ?>" <?php checked(in_array($ev, (array) ($hook['events'] ?? array()), true)); ?>> <?php echo esc_html($ev); ?></label>
				<?php endforeach; ?>
			</td></tr>
			<tr><th>Field map JSON</th><td><textarea name="<?php echo esc_attr(STRIKESCOPE_SETTINGS_OPTION); ?>[webhooks][<?php echo (int) $i; ?>][field_map]" class="large-text code" rows="4" placeholder='{"Email":"{{email}}","DealAmount":"{{soldPrice}}"}'><?php echo esc_textarea($hook['field_map'] ?? ''); ?></textarea></td></tr>
		</table>
	<?php endforeach; ?>
	<p><button type="button" class="button" id="strikescope-test-webhook">Send test webhook to first URL</button></p>
	<?php
}

function strikescope_form_render_calendar_integrations_settings($settings, $show_heading = true) {
	if (!current_user_can('manage_options')) {
		return;
	}
	?>
	<?php if ($show_heading) : ?>
		<h2 class="title">Calendar integrations</h2>
	<?php endif; ?>
	<h3>Google Calendar OAuth</h3>
	<table class="form-table" role="presentation">
		<tr><th>Google Client ID</th><td><input type="text" name="<?php echo esc_attr(STRIKESCOPE_SETTINGS_OPTION); ?>[google_calendar_client_id]" value="<?php echo esc_attr($settings['google_calendar_client_id'] ?? ''); ?>" class="regular-text"></td></tr>
		<tr><th>Google Client Secret</th><td><input type="password" name="<?php echo esc_attr(STRIKESCOPE_SETTINGS_OPTION); ?>[google_calendar_client_secret]" value="<?php echo esc_attr($settings['google_calendar_client_secret'] ?? ''); ?>" class="regular-text" autocomplete="new-password"></td></tr>
	</table>
	<h3>Outlook Calendar OAuth</h3>
	<table class="form-table" role="presentation">
		<tr><th>Outlook Tenant ID</th><td><input type="text" name="<?php echo esc_attr(STRIKESCOPE_SETTINGS_OPTION); ?>[outlook_tenant_id]" value="<?php echo esc_attr($settings['outlook_tenant_id'] ?? ''); ?>" class="regular-text"></td></tr>
		<tr><th>Outlook Client ID</th><td><input type="text" name="<?php echo esc_attr(STRIKESCOPE_SETTINGS_OPTION); ?>[outlook_client_id]" value="<?php echo esc_attr($settings['outlook_client_id'] ?? ''); ?>" class="regular-text"></td></tr>
		<tr><th>Outlook Client Secret</th><td><input type="password" name="<?php echo esc_attr(STRIKESCOPE_SETTINGS_OPTION); ?>[outlook_client_secret]" value="<?php echo esc_attr($settings['outlook_client_secret'] ?? ''); ?>" class="regular-text" autocomplete="new-password"></td></tr>
	</table>
	<p class="description">Redirect URI: <code><?php echo esc_html(rest_url('strikescope/v1/lead-form/calendar/google/callback')); ?></code></p>
	<?php
}

function strikescope_form_render_integrations_settings($settings, $show_heading = true) {
	strikescope_form_render_api_webhooks_settings($settings, $show_heading);
	strikescope_form_render_calendar_integrations_settings($settings, false);
}

add_action('rest_api_init', static function () {
	register_rest_route(
		'strikescope/v1',
		'/lead-form/leads',
		array(
			array(
				'methods'             => 'GET',
				'permission_callback' => static function ($request) {
					return strikescope_form_authenticate_api_request($request, 'leads:read') || strikescope_form_user_can_manage_leads();
				},
				'callback'            => static function ($request) {
					$status = sanitize_key((string) $request->get_param('status'));
					$agent  = absint($request->get_param('sales_agent'));
					$statuses = $status !== '' ? array($status) : array();
					$ids = strikescope_form_query_lead_ids(array('statuses' => $statuses, 'agent_id' => $agent));
					$rows = array();
					foreach ($ids as $id) {
						$rows[] = strikescope_form_lead_to_array($id);
					}
					return array('leads' => $rows, 'count' => count($rows));
				},
			),
		)
	);

	register_rest_route(
		'strikescope/v1',
		'/lead-form/leads/export',
		array(
			'methods'             => 'GET',
			'permission_callback' => static function ($request) {
				return strikescope_form_authenticate_api_request($request, 'leads:export') || strikescope_form_user_can_manage_leads();
			},
			'callback'            => static function ($request) {
				$preset = sanitize_key((string) $request->get_param('preset'));
				$status = sanitize_key((string) $request->get_param('status'));
				$ids = strikescope_form_query_lead_ids(array('statuses' => strikescope_form_resolve_export_statuses($preset, $status)));
				strikescope_form_send_csv_export($ids, strikescope_form_export_filename($preset ?: 'export'));
				return null;
			},
		)
	);

	register_rest_route(
		'strikescope/v1',
		'/lead-form/leads/(?P<id>\d+)',
		array(
			array(
				'methods'             => 'GET',
				'permission_callback' => static function ($request) {
					return strikescope_form_authenticate_api_request($request, 'leads:read') || strikescope_form_user_can_manage_leads();
				},
				'callback'            => static function ($request) {
					$id = absint($request['id']);
					$row = strikescope_form_lead_to_array($id);
					if (!$row) {
						return new WP_Error('not_found', 'Lead not found', array('status' => 404));
					}
					return $row;
				},
			),
			array(
				'methods'             => 'PATCH',
				'permission_callback' => static function ($request) {
					return strikescope_form_authenticate_api_request($request, 'leads:write') || strikescope_form_user_can_manage_leads();
				},
				'callback'            => static function ($request) {
					$id   = absint($request['id']);
					$data = $request->get_json_params();
					$data = is_array($data) ? $data : array();
					$result = strikescope_form_patch_lead($id, $data);
					if (is_wp_error($result)) {
						return $result;
					}
					return $result;
				},
			),
		)
	);
});