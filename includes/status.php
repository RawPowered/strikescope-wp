/**
 * Lead status workflow, aging cron, business-day helper.
 */

function strikescope_form_status_catalog() {
	// STRIKESCOPE_LEAD_FORM_STATUS_CATALOG_START
	return array(
		"new" => array('label' => "New", 'color' => "#2271b1", 'open' => true),
		"aging" => array('label' => "Aging", 'color' => "#dba617", 'open' => true),
		"contacted" => array('label' => "Contacted", 'color' => "#00a32a", 'open' => true),
		"scheduled" => array('label' => "Scheduled", 'color' => "#8c45ff", 'open' => true),
		"sold" => array('label' => "Sold", 'color' => "#008a20", 'open' => false),
		"lost" => array('label' => "Lost", 'color' => "#b32d2e", 'open' => false),
		"dnq" => array('label' => "DNQ", 'color' => "#646970", 'open' => false),
		"dnc" => array('label' => "DNC", 'color' => "#646970", 'open' => false),
		"spam" => array('label' => "Spam", 'color' => "#b32d2e", 'open' => false),
		"blacklisted" => array('label' => "Blacklisted", 'color' => "#1c1c1c", 'open' => false),
	);
	// STRIKESCOPE_LEAD_FORM_STATUS_CATALOG_END
}

function strikescope_form_allowed_statuses() {
	return array_keys(strikescope_form_status_catalog());
}

function strikescope_form_open_statuses() {
	$open = array();
	foreach (strikescope_form_status_catalog() as $id => $row) {
		if (!empty($row['open'])) {
			$open[] = $id;
		}
	}
	return $open;
}

function strikescope_form_normalize_status($status) {
	$status = sanitize_key((string) $status);
	return in_array($status, strikescope_form_allowed_statuses(), true) ? $status : 'new';
}

function strikescope_form_get_lead_status($post_id) {
	$status = get_post_meta($post_id, '_lead_status', true);
	if ($status === '' || $status === false) {
		return 'new';
	}
	return strikescope_form_normalize_status($status);
}

function strikescope_form_set_lead_status($post_id, $status) {
	$post_id = absint($post_id);
	if ($post_id <= 0) {
		return false;
	}
	$old     = strikescope_form_get_lead_status($post_id);
	$status  = strikescope_form_normalize_status($status);
	update_post_meta($post_id, '_lead_status', $status);
	update_post_meta($post_id, '_lead_status_updated_at', (string) time());
	if ($old !== $status && function_exists('strikescope_form_dispatch_integration_event')) {
		strikescope_form_dispatch_integration_event(
			'lead.status_changed',
			$post_id,
			array('old_status' => $old, 'new_status' => $status)
		);
	}
	return true;
}

function strikescope_form_init_lead_status($post_id) {
	strikescope_form_set_lead_status($post_id, 'new');
}

function strikescope_form_status_badge_html($status) {
	$catalog = strikescope_form_status_catalog();
	$status  = strikescope_form_normalize_status($status);
	$row     = isset($catalog[ $status ]) ? $catalog[ $status ] : $catalog['new'];
	$color   = esc_attr($row['color']);
	$label   = esc_html($row['label']);
	return '<span class="strikescope-lead-status-badge" style="display:inline-block;padding:2px 8px;border-radius:4px;font-size:12px;font-weight:600;color:#fff;background:' . $color . ';">' . $label . '</span>';
}

function strikescope_form_status_select_html($post_id, $css_class = 'strikescope-lead-status-select') {
	$post_id = absint($post_id);
	$current = strikescope_form_get_lead_status($post_id);
	$html    = '<select class="' . esc_attr($css_class) . '" data-lead-id="' . esc_attr((string) $post_id) . '">';
	foreach (strikescope_form_status_catalog() as $id => $row) {
		$html .= '<option value="' . esc_attr($id) . '"' . selected($current, $id, false) . '>' . esc_html($row['label']) . '</option>';
	}
	$html .= '</select>';
	return $html;
}

function strikescope_form_count_business_days_between($start_ts, $end_ts) {
	$tz    = wp_timezone();
	$start = (new DateTime('@' . (int) $start_ts))->setTimezone($tz);
	$end   = (new DateTime('@' . (int) $end_ts))->setTimezone($tz);
	$start->setTime(0, 0, 0);
	$end->setTime(0, 0, 0);
	$days = 0;
	while ($start < $end) {
		$dow = (int) $start->format('N');
		if ($dow >= 1 && $dow <= 5) {
			$days++;
		}
		$start->modify('+1 day');
	}
	return $days;
}

function strikescope_form_should_age_lead($post_id, $business_days = 3) {
	$status = strikescope_form_get_lead_status($post_id);
	if ($status !== 'new') {
		return false;
	}
	$post = get_post($post_id);
	if (!$post) {
		return false;
	}
	$created = strtotime($post->post_date_gmt . ' UTC');
	if (!$created) {
		return false;
	}
	return strikescope_form_count_business_days_between($created, time()) >= (int) $business_days;
}

function strikescope_form_run_aging_cron() {
	$q = new WP_Query(
		array(
			'post_type'      => STRIKESCOPE_LEAD_CPT,
			'post_status'    => strikescope_form_lead_post_statuses(),
			'posts_per_page' => 100,
			'fields'         => 'ids',
			'meta_query'     => array(
				'relation' => 'OR',
				array(
					'key'     => '_lead_status',
					'compare' => 'NOT EXISTS',
				),
				array(
					'key'     => '_lead_status',
					'value'   => 'new',
					'compare' => '=',
				),
			),
		)
	);
	foreach ($q->posts as $post_id) {
		if (strikescope_form_should_age_lead($post_id, 3)) {
			strikescope_form_set_lead_status($post_id, 'aging');
		}
	}
}

add_action('init', static function () {
	if (!wp_next_scheduled('strikescope_lead_aging_cron')) {
		wp_schedule_event(time() + HOUR_IN_SECONDS, 'daily', 'strikescope_lead_aging_cron');
	}
});

add_action('strikescope_lead_aging_cron', 'strikescope_form_run_aging_cron');

add_action('wp_ajax_strikescope_lead_set_status', static function () {
	if (!strikescope_form_user_can_manage_leads()) {
		wp_send_json_error(array('message' => 'Forbidden'), 403);
	}
	check_ajax_referer('strikescope_lead_admin', 'nonce');
	$post_id = isset($_POST['lead_id']) ? absint($_POST['lead_id']) : 0;
	$status  = isset($_POST['status']) ? sanitize_key((string) wp_unslash($_POST['status'])) : '';
	if ($post_id <= 0 || get_post_type($post_id) !== STRIKESCOPE_LEAD_CPT) {
		wp_send_json_error(array('message' => 'Invalid lead'), 400);
	}
	strikescope_form_set_lead_status($post_id, $status);
	$response = array(
		'status'      => strikescope_form_get_lead_status($post_id),
		'badge_html'  => strikescope_form_status_badge_html(strikescope_form_get_lead_status($post_id)),
	);
	if ($status === 'sold') {
		$response['show_sold_price'] = true;
	} else {
		$response['show_sold_price'] = false;
	}
	wp_send_json_success($response);
});

add_action('wp_ajax_strikescope_lead_blacklist_email', static function () {
	if (!current_user_can('manage_options')) {
		wp_send_json_error(array('message' => 'Forbidden'), 403);
	}
	check_ajax_referer('strikescope_lead_admin', 'nonce');
	$post_id = isset($_POST['lead_id']) ? absint($_POST['lead_id']) : 0;
	if ($post_id <= 0) {
		wp_send_json_error(array('message' => 'Invalid lead'), 400);
	}
	$email = get_post_meta($post_id, '_lead_email', true);
	if (!is_email($email)) {
		wp_send_json_error(array('message' => 'No email on lead'), 400);
	}
	strikescope_form_add_email_to_blacklist($email);
	wp_send_json_success(array('message' => 'Email added to blacklist.'));
});

add_action('wp_ajax_strikescope_lead_retry_geocode', static function () {
	if (!strikescope_form_user_can_manage_leads()) {
		wp_send_json_error(array('message' => 'Forbidden'), 403);
	}
	check_ajax_referer('strikescope_lead_admin', 'nonce');
	$post_id = isset($_POST['lead_id']) ? absint($_POST['lead_id']) : 0;
	if ($post_id <= 0) {
		wp_send_json_error(array('message' => 'Invalid lead'), 400);
	}
	update_post_meta($post_id, '_lead_geocode_status', 'pending');
	wp_schedule_single_event(time() + 2, 'strikescope_geocode_lead', array($post_id));
	wp_send_json_success(array('message' => 'Geocode queued.'));
});

add_filter('bulk_actions-edit-' . STRIKESCOPE_LEAD_CPT, static function ($actions) {
	foreach (strikescope_form_status_catalog() as $id => $row) {
		$actions[ 'strikescope_status_' . $id ] = 'Set status: ' . $row['label'];
	}
	return $actions;
});

add_filter('handle_bulk_actions-edit-' . STRIKESCOPE_LEAD_CPT, static function ($redirect, $action, $post_ids) {
	if (strpos($action, 'strikescope_status_') !== 0) {
		return $redirect;
	}
	$status = substr($action, strlen('strikescope_status_'));
	if (!in_array($status, strikescope_form_allowed_statuses(), true)) {
		return $redirect;
	}
	$count = 0;
	foreach ((array) $post_ids as $post_id) {
		if (get_post_type($post_id) === STRIKESCOPE_LEAD_CPT) {
			strikescope_form_set_lead_status($post_id, $status);
			$count++;
		}
	}
	return add_query_arg('strikescope_status_updated', $count, $redirect);
}, 10, 3);

add_action('restrict_manage_posts', static function ($post_type) {
	if ($post_type !== STRIKESCOPE_LEAD_CPT) {
		return;
	}
	$current = isset($_GET['lead_status']) ? sanitize_key((string) wp_unslash($_GET['lead_status'])) : '';
	echo '<select name="lead_status"><option value="">All statuses</option>';
	foreach (strikescope_form_status_catalog() as $id => $row) {
		printf(
			'<option value="%s"%s>%s</option>',
			esc_attr($id),
			selected($current, $id, false),
			esc_html($row['label'])
		);
	}
	echo '</select>';
});

add_action('pre_get_posts', static function ($query) {
	if (!is_admin() || !$query->is_main_query()) {
		return;
	}
	if ($query->get('post_type') !== STRIKESCOPE_LEAD_CPT) {
		return;
	}
	$status = isset($_GET['lead_status']) ? sanitize_key((string) wp_unslash($_GET['lead_status'])) : '';
	if ($status === '' || !in_array($status, strikescope_form_allowed_statuses(), true)) {
		return;
	}
	if ($status === 'new') {
		$query->set(
			'meta_query',
			array(
				'relation' => 'OR',
				array(
					'key'     => '_lead_status',
					'compare' => 'NOT EXISTS',
				),
				array(
					'key'     => '_lead_status',
					'value'   => 'new',
					'compare' => '=',
				),
			)
		);
		return;
	}
	$query->set(
		'meta_query',
		array(
			array(
				'key'     => '_lead_status',
				'value'   => $status,
				'compare' => '=',
			),
		)
	);
});