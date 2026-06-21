/**
 * CRM CSV export — admin download + REST.
 */

function strikescope_form_export_filename($preset = 'all') {
	$preset = sanitize_key($preset);
	if ($preset === '') {
		$preset = 'all';
	}
	return 'strikescope-leads-' . $preset . '-' . gmdate('Y-m-d-His') . '.csv';
}

function strikescope_form_resolve_export_statuses($preset = '', $status = '') {
	$presets = strikescope_form_export_presets_config();
	if ($preset !== '' && isset($presets[ $preset ]['statuses'])) {
		return (array) $presets[ $preset ]['statuses'];
	}
	if ($status !== '') {
		$status = sanitize_key($status);
		if (in_array($status, strikescope_form_allowed_statuses(), true)) {
			return array($status);
		}
	}
	if (isset($_GET['lead_status']) && $_GET['lead_status'] !== '') {
		$filter = sanitize_key((string) wp_unslash($_GET['lead_status']));
		if (in_array($filter, strikescope_form_allowed_statuses(), true)) {
			return array($filter);
		}
	}
	return array();
}

function strikescope_form_send_csv_export($post_ids, $filename = 'strikescope-leads.csv') {
	$columns = strikescope_form_export_columns_config();
	if (!$columns) {
		$columns = array(
			array('key' => 'lead_id', 'header' => 'Lead ID'),
			array('key' => 'email', 'header' => 'Email'),
		);
	}
	nocache_headers();
	header('Content-Type: text/csv; charset=utf-8');
	header('Content-Disposition: attachment; filename="' . sanitize_file_name($filename) . '"');
	strikescope_form_stream_csv_rows($post_ids, $columns);
	exit;
}

add_action('admin_post_strikescope_export_leads', static function () {
	if (!strikescope_form_user_can_manage_leads()) {
		wp_die(esc_html__('Forbidden', 'strikescope'));
	}
	check_admin_referer('strikescope_export_leads');
	$preset = isset($_GET['preset']) ? sanitize_key((string) wp_unslash($_GET['preset'])) : 'all';
	$status = isset($_GET['status']) ? sanitize_key((string) wp_unslash($_GET['status'])) : '';
	$agent  = isset($_GET['agent']) ? absint($_GET['agent']) : 0;
	$ids    = strikescope_form_query_lead_ids(
		array(
			'statuses' => strikescope_form_resolve_export_statuses($preset, $status),
			'agent_id' => $agent,
		)
	);
	strikescope_form_send_csv_export($ids, strikescope_form_export_filename($preset));
});

function strikescope_form_export_toolbar_html() {
	$base = wp_nonce_url(admin_url('admin-post.php?action=strikescope_export_leads'), 'strikescope_export_leads');
	$presets = strikescope_form_export_presets_config();
	$links   = array();
	foreach ($presets as $key => $row) {
		$links[] = '<a class="button" href="' . esc_url(add_query_arg('preset', $key, $base)) . '">Export ' . esc_html($row['label'] ?? $key) . '</a>';
	}
	$links[] = '<a class="button" href="' . esc_url($base) . '">Export filtered</a>';
	return '<div class="strikescope-lead-export-bar" style="margin:12px 0;display:flex;flex-wrap:wrap;gap:8px;">' . implode('', $links) . '</div>';
}

add_action('manage_posts_extra_tablenav', static function ($which) {
	if ($which !== 'top') {
		return;
	}
	$screen = function_exists('get_current_screen') ? get_current_screen() : null;
	if (!$screen || $screen->post_type !== STRIKESCOPE_LEAD_CPT) {
		return;
	}
	echo strikescope_form_export_toolbar_html();
});