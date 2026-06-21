/**
 * Sales Agent role, assignment, visibility filters.
 */

function strikescope_form_register_sales_agent_role() {
	if (get_role('strikescope_sales_agent')) {
		return;
	}
	add_role(
		'strikescope_sales_agent',
		'Sales Agent',
		array(
			'read'                   => true,
			'strikescope_view_leads'    => true,
			'strikescope_edit_leads'    => true,
		)
	);
	$admin = get_role('administrator');
	if ($admin) {
		$admin->add_cap('strikescope_view_leads');
		$admin->add_cap('strikescope_edit_leads');
	}
}

add_action('init', 'strikescope_form_register_sales_agent_role', 5);

function strikescope_form_assignable_agents() {
	$users = get_users(
		array(
			'role__in' => array('strikescope_sales_agent', 'administrator'),
			'orderby'  => 'display_name',
			'order'    => 'ASC',
		)
	);
	return is_array($users) ? $users : array();
}

function strikescope_form_agent_select_options($selected = 0) {
	$html = '<option value="0">Unassigned</option>';
	foreach (strikescope_form_assignable_agents() as $user) {
		$html .= '<option value="' . esc_attr((string) $user->ID) . '"' . selected((int) $selected, (int) $user->ID, false) . '>' . esc_html($user->display_name) . '</option>';
	}
	return $html;
}

function strikescope_form_sales_agent_select_html($post_id, $css_class = 'strikescope-lead-agent-select') {
	$post_id  = absint($post_id);
	$selected = strikescope_form_get_sales_agent_id($post_id);
	$html     = '<select class="' . esc_attr($css_class) . '" data-lead-id="' . esc_attr((string) $post_id) . '">';
	$html    .= strikescope_form_agent_select_options($selected);
	$html    .= '</select>';
	return $html;
}

function strikescope_form_set_sales_agent($post_id, $user_id) {
	$post_id = absint($post_id);
	if ($post_id <= 0) {
		return false;
	}
	$user_id = absint($user_id);
	$old     = strikescope_form_get_sales_agent_id($post_id);
	if ($user_id > 0) {
		$user = get_userdata($user_id);
		if (!$user || (!in_array('strikescope_sales_agent', (array) $user->roles, true) && !user_can($user, 'manage_options'))) {
			return false;
		}
		update_post_meta($post_id, '_lead_sales_agent', (string) $user_id);
	} else {
		update_post_meta($post_id, '_lead_sales_agent', '0');
	}
	update_post_meta($post_id, '_lead_sales_agent_updated_at', (string) time());
	if ($old !== $user_id && function_exists('strikescope_form_dispatch_integration_event')) {
		strikescope_form_dispatch_integration_event(
			'lead.assigned',
			$post_id,
			array('old_agent_id' => $old, 'new_agent_id' => $user_id)
		);
	}
	return true;
}

function strikescope_form_user_can_view_lead($post_id, $user_id = 0) {
	$user_id = $user_id ? (int) $user_id : get_current_user_id();
	if ($user_id <= 0) {
		return false;
	}
	if (user_can($user_id, 'manage_options')) {
		return true;
	}
	if (!strikescope_form_user_can_manage_leads($user_id)) {
		return false;
	}
	if (!user_can($user_id, 'strikescope_view_leads') && !user_can($user_id, 'strikescope_edit_leads')) {
		return true;
	}
	$agent_id = strikescope_form_get_sales_agent_id($post_id);
	return $agent_id === 0 || $agent_id === (int) $user_id;
}

add_filter('posts_where', static function ($where, $query) {
	if (!is_admin() || !$query->is_main_query()) {
		return $where;
	}
	$post_type = $query->get('post_type');
	if ($post_type !== STRIKESCOPE_LEAD_CPT) {
		return $where;
	}
	if (user_can(get_current_user_id(), 'manage_options')) {
		return $where;
	}
	if (!user_can(get_current_user_id(), 'strikescope_view_leads') && !user_can(get_current_user_id(), 'strikescope_edit_leads')) {
		return $where;
	}
	global $wpdb;
	$user_id = get_current_user_id();
	$where  .= $wpdb->prepare(
		" AND (
			NOT EXISTS (
				SELECT 1 FROM {$wpdb->postmeta} pm_agent
				WHERE pm_agent.post_id = {$wpdb->posts}.ID
				AND pm_agent.meta_key = '_lead_sales_agent'
			)
			OR {$wpdb->posts}.ID IN (
				SELECT post_id FROM {$wpdb->postmeta}
				WHERE meta_key = '_lead_sales_agent'
				AND (meta_value = '0' OR meta_value = '' OR meta_value = %d)
			)
		)",
		$user_id
	);
	return $where;
}, 10, 2);

add_filter('bulk_actions-edit-' . STRIKESCOPE_LEAD_CPT, static function ($actions) {
	foreach (strikescope_form_assignable_agents() as $user) {
		$actions[ 'strikescope_agent_' . $user->ID ] = 'Assign agent: ' . $user->display_name;
	}
	return $actions;
});

add_filter('handle_bulk_actions-edit-' . STRIKESCOPE_LEAD_CPT, static function ($redirect, $action, $post_ids) {
	if (strpos($action, 'strikescope_agent_') !== 0) {
		return $redirect;
	}
	$user_id = absint(substr($action, strlen('strikescope_agent_')));
	if ($user_id <= 0) {
		return $redirect;
	}
	$count = 0;
	foreach ((array) $post_ids as $post_id) {
		if (get_post_type($post_id) === STRIKESCOPE_LEAD_CPT && strikescope_form_set_sales_agent($post_id, $user_id)) {
			$count++;
		}
	}
	return add_query_arg('strikescope_agent_updated', $count, $redirect);
}, 10, 3);

add_action('restrict_manage_posts', static function ($post_type) {
	if ($post_type !== STRIKESCOPE_LEAD_CPT) {
		return;
	}
	$current = isset($_GET['lead_agent']) ? absint($_GET['lead_agent']) : 0;
	echo '<select name="lead_agent"><option value="">All agents</option><option value="0"' . selected($current, 0, false) . '>Unassigned</option>';
	foreach (strikescope_form_assignable_agents() as $user) {
		printf(
			'<option value="%d"%s>%s</option>',
			(int) $user->ID,
			selected($current, (int) $user->ID, false),
			esc_html($user->display_name)
		);
	}
	echo '</select>';
}, 11);

add_action('pre_get_posts', static function ($query) {
	if (!is_admin() || !$query->is_main_query()) {
		return;
	}
	if ($query->get('post_type') !== STRIKESCOPE_LEAD_CPT) {
		return;
	}
	if (!isset($_GET['lead_agent']) || $_GET['lead_agent'] === '') {
		return;
	}
	$agent_id = absint(wp_unslash($_GET['lead_agent']));
	$query->set(
		'meta_query',
		array(
			array(
				'key'     => '_lead_sales_agent',
				'value'   => (string) $agent_id,
				'compare' => '=',
			),
		)
	);
}, 11);

add_action('wp_ajax_strikescope_lead_set_sales_agent', static function () {
	if (!strikescope_form_user_can_manage_leads()) {
		wp_send_json_error(array('message' => 'Forbidden'), 403);
	}
	check_ajax_referer('strikescope_lead_admin', 'nonce');
	$post_id = isset($_POST['lead_id']) ? absint($_POST['lead_id']) : 0;
	$agent   = isset($_POST['agent_id']) ? absint($_POST['agent_id']) : 0;
	if ($post_id <= 0 || get_post_type($post_id) !== STRIKESCOPE_LEAD_CPT) {
		wp_send_json_error(array('message' => 'Invalid lead'), 400);
	}
	if (!strikescope_form_set_sales_agent($post_id, $agent)) {
		wp_send_json_error(array('message' => 'Unable to assign agent'), 400);
	}
	$user = $agent > 0 ? get_userdata($agent) : null;
	wp_send_json_success(
		array(
			'agent_id'   => $agent,
			'agent_name' => $user ? $user->display_name : 'Unassigned',
		)
	);
});