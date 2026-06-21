/**
 * Lead admin UI: list columns, detail page, map dashboard, REST.
 */

function strikescope_form_leads_admin_cap() {
	return apply_filters('strikescope_form_leads_admin_cap', 'edit_posts');
}

function strikescope_form_user_can_manage_leads($user_id = 0) {
	$user_id = $user_id ? (int) $user_id : get_current_user_id();
	if ($user_id <= 0) {
		return false;
	}
	if (user_can($user_id, 'manage_options')) {
		return true;
	}
	$cap = strikescope_form_leads_admin_cap();
	if ($cap && user_can($user_id, $cap)) {
		return true;
	}
	return user_can($user_id, 'edit_posts') || user_can($user_id, 'edit_pages');
}

function strikescope_form_lead_post_statuses() {
	return array('publish', 'private');
}

function strikescope_form_default_lead_author_id() {
	$admin = get_users(array('role' => 'administrator', 'number' => 1, 'fields' => 'ID'));
	if (!empty($admin[0])) {
		return (int) $admin[0];
	}
	return 1;
}

add_filter('map_meta_cap', static function ($caps, $cap, $user_id, $args) {
	$lead_meta_caps = array('read_post', 'edit_post', 'delete_post', 'publish_posts');
	if (!in_array($cap, $lead_meta_caps, true)) {
		return $caps;
	}
	$post_id = isset($args[0]) ? (int) $args[0] : 0;
	if ($post_id <= 0) {
		return $caps;
	}
	$post = get_post($post_id);
	if (!$post || $post->post_type !== STRIKESCOPE_LEAD_CPT) {
		return $caps;
	}
	if (strikescope_form_user_can_manage_leads($user_id)) {
		return array('edit_posts');
	}
	return $caps;
}, 10, 4);

add_action('admin_init', static function () {
	if (get_option('strikescope_form_published_migrated')) {
		return;
	}
	global $wpdb;
	$wpdb->query(
		$wpdb->prepare(
			"UPDATE {$wpdb->posts} SET post_status = 'publish' WHERE post_type = %s AND post_status = 'private'",
			STRIKESCOPE_LEAD_CPT
		)
	);
	update_option('strikescope_form_published_migrated', 1, false);
});

function strikescope_form_admin_screen_ids() {
	return array(
		'edit-' . STRIKESCOPE_LEAD_CPT,
		'admin_page_strikescope-lead-detail',
		'strikescope_lead_page_strikescope-lead-map',
	);
}

function strikescope_form_is_lead_admin_screen($hook) {
	return in_array($hook, strikescope_form_admin_screen_ids(), true);
}

add_filter('manage_' . STRIKESCOPE_LEAD_CPT . '_posts_columns', static function ($columns) {
	return array(
		'cb'             => $columns['cb'],
		'title'            => 'Lead',
		'lead_status'      => 'Status',
		'sales_agent'      => 'Sales agent',
		'services'         => 'Services',
		'email'            => 'Email',
		'phone'            => 'Phone',
		'city'             => 'City',
		'lead_state'       => 'State',
		'lead_zip'         => 'ZIP',
		'customer_email'   => 'Customer email',
		'date'             => 'Date',
	);
});

add_action('manage_' . STRIKESCOPE_LEAD_CPT . '_posts_custom_column', static function ($column, $post_id) {
	switch ($column) {
		case 'lead_status':
			echo strikescope_form_status_badge_html(strikescope_form_get_lead_status($post_id));
			echo '<div style="margin-top:6px;">' . strikescope_form_status_select_html($post_id) . '</div>';
			break;
		case 'sales_agent':
			$agent = strikescope_form_get_sales_agent_user($post_id);
			echo esc_html($agent ? $agent->display_name : 'Unassigned');
			echo '<div style="margin-top:6px;">' . strikescope_form_sales_agent_select_html($post_id) . '</div>';
			break;
		case 'services':
			echo esc_html(get_post_meta($post_id, '_lead_services', true));
			break;
		case 'email':
			echo esc_html(get_post_meta($post_id, '_lead_email', true));
			break;
		case 'phone':
			echo esc_html(get_post_meta($post_id, '_lead_phone', true));
			break;
		case 'city':
			echo esc_html(get_post_meta($post_id, '_lead_city', true));
			break;
		case 'lead_state':
			echo esc_html(get_post_meta($post_id, '_lead_state', true));
			break;
		case 'lead_zip':
			echo esc_html(get_post_meta($post_id, '_lead_zip', true));
			break;
		case 'customer_email':
			$sent  = get_post_meta($post_id, '_lead_customer_email_sent', true);
			$error = get_post_meta($post_id, '_lead_customer_email_error', true);
			if ($sent === '1' || $sent === 1 || $sent === true) {
				echo '<span style="color:#008a20;font-weight:600;">Sent</span>';
			} elseif ($error !== '') {
				echo '<span style="color:#b32d2e;font-weight:600;">Failed</span>';
			} else {
				echo '<span style="color:#646970;">Skipped</span>';
			}
			break;
	}
}, 10, 2);

add_filter('post_row_actions', static function ($actions, $post) {
	if ($post->post_type !== STRIKESCOPE_LEAD_CPT) {
		return $actions;
	}
	$detail = strikescope_form_lead_detail_url($post->ID);
	$actions['strikescope_view_lead'] = '<a href="' . esc_url($detail) . '">View record</a>';
	$actions['strikescope_copy_lead'] = '<a href="#" class="strikescope-copy-contact-btn" data-lead-id="' . esc_attr((string) $post->ID) . '" data-format="tsv">Copy contact</a>';
	unset($actions['edit'], $actions['inline hide-if-no-js']);
	return $actions;
}, 10, 2);

add_filter('get_edit_post_link', static function ($link, $post_id, $context) {
	if (get_post_type($post_id) === STRIKESCOPE_LEAD_CPT) {
		return strikescope_form_lead_detail_url($post_id);
	}
	return $link;
}, 10, 3);

add_action('load-post.php', static function () {
	if (!isset($_GET['post'])) {
		return;
	}
	$post_id = absint($_GET['post']);
	if ($post_id <= 0 || get_post_type($post_id) !== STRIKESCOPE_LEAD_CPT) {
		return;
	}
	$cap = strikescope_form_leads_admin_cap();
	if (!strikescope_form_user_can_manage_leads()) {
		wp_die(esc_html__('You do not have permission to view leads.', 'strikescope'));
	}
	wp_safe_redirect(strikescope_form_lead_detail_url($post_id));
	exit;
});

add_action('load-post-new.php', static function () {
	if (isset($_GET['post_type']) && sanitize_key((string) wp_unslash($_GET['post_type'])) === STRIKESCOPE_LEAD_CPT) {
		wp_safe_redirect(admin_url('edit.php?post_type=' . STRIKESCOPE_LEAD_CPT));
		exit;
	}
});

function strikescope_form_lead_detail_url($lead_id) {
	$lead_id = absint($lead_id);
	if ($lead_id <= 0) {
		return '';
	}
	return admin_url('admin.php?page=strikescope-lead-detail&lead_id=' . $lead_id);
}

function strikescope_form_display_label($field, $value) {
	$maps = array(
		'propertyType' => array(
			'residential' => 'Residential',
			'commercial'  => 'Commercial',
		),
		'timeline' => array(
			'asap'           => 'ASAP',
			'1-3months'      => '1–3 months',
			'3-6months'      => '3–6 months',
			'6plus'          => '6+ months',
			'just-exploring' => 'Just exploring',
		),
		'budget' => array(
			'under-10k'  => 'Under $10k',
			'10k-25k'    => '$10k–$25k',
			'25k-50k'    => '$25k–$50k',
			'50k-100k'   => '$50k–$100k',
			'over-100k'  => 'Over $100k',
			'not-sure'   => 'Not sure',
			''           => 'Not provided',
		),
	);
	if (isset($maps[ $field ][ $value ])) {
		return $maps[ $field ][ $value ];
	}
	return (string) $value;
}

function strikescope_form_customer_email_status_html($lead_id) {
	$sent  = get_post_meta($lead_id, '_lead_customer_email_sent', true);
	$error = get_post_meta($lead_id, '_lead_customer_email_error', true);
	if ($sent === '1' || $sent === 1 || $sent === true) {
		return '<span style="color:#008a20;font-weight:600;">Sent</span>';
	}
	if ($error !== '') {
		return '<span style="color:#b32d2e;font-weight:600;">Failed</span> <span class="description">(' . esc_html($error) . ')</span>';
	}
	return '<span style="color:#646970;">Skipped</span>';
}

function strikescope_form_format_submission_time($lead_id) {
	$ts = get_post_meta($lead_id, '_lead_submitted_at', true);
	if ($ts !== '' && is_numeric($ts)) {
		return wp_date(get_option('date_format') . ' ' . get_option('time_format'), (int) $ts);
	}
	$post = get_post($lead_id);
	if (!$post) {
		return '';
	}
	return wp_date(get_option('date_format') . ' ' . get_option('time_format'), strtotime($post->post_date));
}

function strikescope_form_render_lead_record($lead_id) {
	$lead_id = absint($lead_id);
	$post    = get_post($lead_id);
	if (!$post || $post->post_type !== STRIKESCOPE_LEAD_CPT) {
		return false;
	}

	$clean   = strikescope_form_lead_payload_array($lead_id);
	$status  = strikescope_form_get_lead_status($lead_id);
	$coords  = strikescope_form_get_lead_coords($lead_id);
	$geo_st  = get_post_meta($lead_id, '_lead_geocode_status', true);
	$maps    = strikescope_form_google_maps_url($clean);
	$updated = get_post_meta($lead_id, '_lead_status_updated_at', true);
	$address = strikescope_form_format_address_line($clean);
	$submitted = strikescope_form_format_submission_time($lead_id);
	$sold_price = strikescope_form_get_sold_price($lead_id);
	$agent = strikescope_form_get_sales_agent_user($lead_id);
	?>
	<div class="strikescope-lead-record">
		<div class="strikescope-lead-record-header" style="display:flex;flex-wrap:wrap;align-items:center;gap:12px;margin-bottom:20px;">
			<?php echo strikescope_form_status_badge_html($status); ?>
			<span class="description">Lead #<?php echo esc_html((string) $lead_id); ?></span>
			<?php if ($submitted) : ?>
				<span class="description">Submitted <?php echo esc_html($submitted); ?></span>
			<?php endif; ?>
		</div>

		<div class="strikescope-lead-detail-grid" style="display:grid;grid-template-columns:1fr 1fr;gap:24px;max-width:1200px;">
			<div class="postbox" style="padding:16px;">
				<h2 style="margin-top:0;">Submission details</h2>
				<table class="widefat striped" style="border:0;box-shadow:none;">
					<tbody>
						<tr><th style="width:140px;">Submitted</th><td><?php echo esc_html($submitted); ?></td></tr>
						<tr><th>Lead ID</th><td><?php echo esc_html((string) $lead_id); ?></td></tr>
						<tr><th>Status</th><td><?php echo strikescope_form_status_badge_html($status); ?></td></tr>
						<tr><th>Update status</th><td><?php echo strikescope_form_status_select_html($lead_id, 'strikescope-lead-status-select widefat'); ?></td></tr>
						<tr class="strikescope-sold-price-row" style="<?php echo $status === 'sold' ? '' : 'display:none;'; ?>">
							<th>Deal amount (optional)</th>
							<td>
								<span style="display:inline-flex;align-items:center;gap:4px;">
									<span>USD</span>
									<input type="text" class="strikescope-sold-price-input regular-text" data-lead-id="<?php echo esc_attr((string) $lead_id); ?>" value="<?php echo esc_attr($sold_price); ?>" placeholder="12500.00">
									<button type="button" class="button button-small strikescope-save-sold-price-btn" data-lead-id="<?php echo esc_attr((string) $lead_id); ?>">Save</button>
								</span>
							</td>
						</tr>
						<tr><th>Sales agent</th><td><?php echo strikescope_form_sales_agent_select_html($lead_id, 'strikescope-lead-agent-select widefat'); ?></td></tr>
						<?php if ($updated) : ?>
							<tr><th>Status updated</th><td><?php echo esc_html(wp_date(get_option('date_format') . ' ' . get_option('time_format'), (int) $updated)); ?></td></tr>
						<?php endif; ?>
						<tr><th>Customer email</th><td><?php echo strikescope_form_customer_email_status_html($lead_id); ?></td></tr>
						<tr><th>Geocode</th><td>
							<?php echo esc_html($geo_st !== '' ? $geo_st : 'unknown'); ?>
							<button type="button" class="button button-small strikescope-retry-geocode-btn" data-lead-id="<?php echo esc_attr((string) $lead_id); ?>">Retry</button>
						</td></tr>
					</tbody>
				</table>
				<?php if (in_array($status, array('spam', 'blacklisted'), true) && current_user_can('manage_options')) : ?>
					<p style="margin-top:12px;"><button type="button" class="button strikescope-blacklist-email-btn" data-lead-id="<?php echo esc_attr((string) $lead_id); ?>">Add email to blacklist</button></p>
				<?php endif; ?>
			</div>

			<div class="postbox" style="padding:16px;">
				<h2 style="margin-top:0;">Quick actions</h2>
				<p>
					<?php if ($maps) : ?><a class="button button-primary" href="<?php echo esc_url($maps); ?>" target="_blank" rel="noopener">Google Maps</a><?php endif; ?>
					<a class="button" href="mailto:<?php echo esc_attr($clean['email'] ?? ''); ?>">Email lead</a>
					<a class="button" href="tel:<?php echo esc_attr(preg_replace('/[^\d+]/', '', $clean['phone'] ?? '')); ?>">Call lead</a>
					<button type="button" class="button strikescope-copy-contact-btn" data-lead-id="<?php echo esc_attr((string) $lead_id); ?>" data-format="tsv">Copy contact (TSV)</button>
					<button type="button" class="button strikescope-copy-contact-btn" data-lead-id="<?php echo esc_attr((string) $lead_id); ?>" data-format="multiline">Copy contact (lines)</button>
				</p>
				<?php if ($address) : ?>
					<p><strong>Property address</strong><br><?php echo esc_html($address); ?></p>
				<?php endif; ?>
			</div>
		</div>

		<div class="postbox" style="padding:16px;margin-top:16px;max-width:1200px;">
			<h2 style="margin-top:0;">Entry details</h2>
			<table class="widefat striped">
				<tbody>
					<tr><th style="width:180px;">First name</th><td><?php echo esc_html($clean['firstName'] ?? ''); ?></td></tr>
					<tr><th>Last name</th><td><?php echo esc_html($clean['lastName'] ?? ''); ?></td></tr>
					<tr><th>Email</th><td><a href="mailto:<?php echo esc_attr($clean['email'] ?? ''); ?>"><?php echo esc_html($clean['email'] ?? ''); ?></a></td></tr>
					<tr><th>Phone</th><td><a href="tel:<?php echo esc_attr(preg_replace('/[^\d+]/', '', $clean['phone'] ?? '')); ?>"><?php echo esc_html($clean['phone'] ?? ''); ?></a></td></tr>
					<tr><th>Street address</th><td><?php echo esc_html($clean['address'] ?? ''); ?></td></tr>
					<tr><th>City</th><td><?php echo esc_html($clean['city'] ?? ''); ?></td></tr>
					<tr><th>State</th><td><?php echo esc_html($clean['state'] ?? ''); ?></td></tr>
					<tr><th>ZIP</th><td><?php echo esc_html($clean['zipCode'] ?? ''); ?></td></tr>
				</tbody>
			</table>
		</div>

		<div class="postbox" style="padding:16px;margin-top:16px;max-width:1200px;">
			<h2 style="margin-top:0;">Project details</h2>
			<table class="widefat striped">
				<tbody>
					<tr><th style="width:180px;">Services</th><td><?php echo esc_html(get_post_meta($lead_id, '_lead_services', true)); ?></td></tr>
					<tr><th>Property type</th><td><?php echo esc_html(strikescope_form_display_label('propertyType', $clean['propertyType'] ?? '')); ?></td></tr>
					<tr><th>Timeline</th><td><?php echo esc_html(strikescope_form_display_label('timeline', $clean['timeline'] ?? '')); ?></td></tr>
					<tr><th>Budget</th><td><?php echo esc_html(strikescope_form_display_label('budget', $clean['budget'] ?? '')); ?></td></tr>
					<tr><th>Additional details</th><td><?php echo nl2br(esc_html($clean['additionalDetails'] ?? '')); ?></td></tr>
				</tbody>
			</table>
		</div>

		<div class="postbox" style="padding:16px;margin-top:16px;max-width:1200px;">
			<h2 style="margin-top:0;">Property map</h2>
			<?php if ($address) : ?>
				<p class="description" style="margin-top:0;"><?php echo esc_html($address); ?></p>
			<?php endif; ?>
			<div id="strikescope-lead-detail-map" class="strikescope-leaflet-map" style="height:420px;border-radius:8px;background:#e8e8e8;border:1px solid #ccd0d4;"
				data-lat="<?php echo esc_attr($coords ? (string) $coords['lat'] : ''); ?>"
				data-lng="<?php echo esc_attr($coords ? (string) $coords['lng'] : ''); ?>"
				data-label="<?php echo esc_attr(get_the_title($lead_id)); ?>"
				data-address="<?php echo esc_attr($address); ?>">
			</div>
			<?php if (!$coords) : ?>
				<p class="description">Map pin appears after geocoding completes. Use <strong>Retry</strong> above if the address was just submitted.</p>
			<?php elseif ($coords) : ?>
				<p class="description">Coordinates: <?php echo esc_html($coords['lat'] . ', ' . $coords['lng']); ?></p>
			<?php endif; ?>
		</div>

		<?php if (function_exists('strikescope_form_render_scheduler_panel')) {
			strikescope_form_render_scheduler_panel($lead_id);
		} ?>
	</div>
	<?php
	return true;
}

function strikescope_form_render_detail_page() {
	if (!strikescope_form_user_can_manage_leads()) {
		wp_die(esc_html__('You do not have permission to view leads.', 'strikescope'));
	}
	$lead_id = isset($_GET['lead_id']) ? absint($_GET['lead_id']) : 0;
	$post    = $lead_id > 0 ? get_post($lead_id) : null;
	if (!$post || $post->post_type !== STRIKESCOPE_LEAD_CPT) {
		echo '<div class="wrap"><h1>Lead not found</h1><p><a href="' . esc_url(admin_url('edit.php?post_type=' . STRIKESCOPE_LEAD_CPT)) . '">Back to leads</a></p></div>';
		return;
	}

	$nonce = wp_create_nonce('strikescope_lead_admin');
	?>
	<div class="wrap strikescope-lead-detail-wrap">
		<h1><?php echo esc_html(get_the_title($lead_id)); ?></h1>
		<p>
			<a href="<?php echo esc_url(admin_url('edit.php?post_type=' . STRIKESCOPE_LEAD_CPT)); ?>">&larr; All leads</a>
			&nbsp;|&nbsp;
			<a href="<?php echo esc_url(admin_url('admin.php?page=strikescope-lead-map')); ?>">Map dashboard</a>
		</p>
		<?php strikescope_form_render_lead_record($lead_id); ?>
	</div>
	<?php
	strikescope_form_print_admin_inline_script($nonce);
}

add_action('admin_menu', static function () {
	$cap = strikescope_form_leads_admin_cap();
	add_submenu_page(
		null,
		'Lead Record',
		'Lead Record',
		$cap,
		'strikescope-lead-detail',
		'strikescope_form_render_detail_page'
	);
	add_submenu_page(
		'edit.php?post_type=' . STRIKESCOPE_LEAD_CPT,
		'Leads Map',
		'Map',
		$cap,
		'strikescope-lead-map',
		'strikescope_form_render_map_page'
	);
}, 20);

function strikescope_form_render_map_page() {
	if (!strikescope_form_user_can_manage_leads()) {
		wp_die(esc_html__('You do not have permission to view leads.', 'strikescope'));
	}
	$nonce = wp_create_nonce('strikescope_lead_admin');
	$rest  = esc_url_raw(rest_url('strikescope/v1/lead-form/leads/map'));
	?>
	<div class="wrap strikescope-lead-map-wrap">
		<h1>Leads Map</h1>
		<p class="description">Open leads with geocoded addresses. Filter by status or service.</p>
		<div style="display:flex;gap:20px;flex-wrap:wrap;align-items:flex-start;">
			<div style="flex:1;min-width:280px;max-width:320px;">
				<div class="postbox" style="padding:12px;">
					<h2 style="margin-top:0;font-size:14px;">Filters</h2>
					<p><label>Status<br>
						<select id="strikescope-map-filter-status" class="widefat">
							<option value="">All open</option>
							<?php foreach (strikescope_form_status_catalog() as $id => $row) : ?>
								<option value="<?php echo esc_attr($id); ?>"><?php echo esc_html($row['label']); ?></option>
							<?php endforeach; ?>
						</select>
					</label></p>
					<p><label>Service<br>
						<select id="strikescope-map-filter-service" class="widefat">
							<option value="">All services</option>
							<?php foreach (strikescope_form_service_ids() as $svc) : ?>
								<option value="<?php echo esc_attr($svc); ?>"><?php echo esc_html(ucfirst($svc)); ?></option>
							<?php endforeach; ?>
						</select>
					</label></p>
					<p id="strikescope-map-count" class="description">Loading…</p>
				</div>
			</div>
			<div style="flex:2;min-width:400px;">
				<div id="strikescope-leads-map" class="strikescope-leaflet-map" style="height:70vh;min-height:480px;border-radius:8px;background:#f0f0f0;"
					data-rest-url="<?php echo esc_url($rest); ?>"
					data-rest-nonce="<?php echo esc_attr(wp_create_nonce('wp_rest')); ?>">
				</div>
			</div>
		</div>
	</div>
	<?php
	strikescope_form_print_admin_inline_script($nonce, true);
}

function strikescope_form_map_leads_data($status_filter = '', $service_filter = '') {
	$meta_query = array(
		array(
			'key'     => '_lead_geocode_status',
			'value'   => 'ok',
			'compare' => '=',
		),
	);

	if ($status_filter !== '' && in_array($status_filter, strikescope_form_allowed_statuses(), true)) {
		$meta_query[] = array(
			'key'     => '_lead_status',
			'value'   => $status_filter,
			'compare' => '=',
		);
	} else {
		$meta_query[] = array(
			'key'     => '_lead_status',
			'value'   => strikescope_form_open_statuses(),
			'compare' => 'IN',
		);
	}

	$q = new WP_Query(
		array(
			'post_type'      => STRIKESCOPE_LEAD_CPT,
			'post_status'    => strikescope_form_lead_post_statuses(),
			'posts_per_page' => 500,
			'meta_query'     => $meta_query,
		)
	);

	$items = array();
	foreach ($q->posts as $post) {
		$coords = strikescope_form_get_lead_coords($post->ID);
		if (!$coords) {
			continue;
		}
		$clean    = strikescope_form_lead_payload_array($post->ID);
		$services = isset($clean['serviceType']) && is_array($clean['serviceType']) ? $clean['serviceType'] : array();
		if ($service_filter !== '' && !in_array($service_filter, $services, true)) {
			continue;
		}
		$status = strikescope_form_get_lead_status($post->ID);
		$items[] = array(
			'id'        => $post->ID,
			'lat'       => $coords['lat'],
			'lng'       => $coords['lng'],
			'label'     => get_the_title($post),
			'status'    => $status,
			'status_label' => strikescope_form_status_catalog()[ $status ]['label'] ?? $status,
			'services'  => get_post_meta($post->ID, '_lead_services', true),
			'service_ids' => $services,
			'admin_url' => strikescope_form_lead_detail_url($post->ID),
			'city'      => $clean['city'] ?? '',
			'state'     => $clean['state'] ?? '',
		);
	}
	return $items;
}

add_action('rest_api_init', static function () {
	register_rest_route(
		'strikescope/v1',
		'/lead-form/leads/map',
		array(
			'methods'             => 'GET',
			'permission_callback' => static function () {
				return strikescope_form_user_can_manage_leads();
			},
			'callback'            => static function ($request) {
				$status  = sanitize_key((string) $request->get_param('status'));
				$service = sanitize_key((string) $request->get_param('service'));
				return array(
					'leads' => strikescope_form_map_leads_data($status, $service),
				);
			},
		)
	);
});

add_action('admin_enqueue_scripts', static function ($hook) {
	if (!strikescope_form_is_lead_admin_screen($hook)) {
		return;
	}
	wp_enqueue_style('leaflet', 'https://unpkg.com/leaflet@1.9.4/dist/leaflet.css', array(), '1.9.4');
	wp_enqueue_script('leaflet', 'https://unpkg.com/leaflet@1.9.4/dist/leaflet.js', array(), '1.9.4', true);
	wp_add_inline_style('leaflet', '.strikescope-lead-status-badge{font-size:12px}.strikescope-lead-detail-wrap .postbox h2{font-size:15px}');
});

function strikescope_form_print_admin_inline_script($nonce, $is_map_dashboard = false) {
	?>
	<script>
	(function () {
		var nonce = <?php echo wp_json_encode($nonce); ?>;
		var ajaxUrl = <?php echo wp_json_encode(admin_url('admin-ajax.php')); ?>;

		function postAction(action, data) {
			var body = new FormData();
			body.append('action', action);
			body.append('nonce', nonce);
			Object.keys(data).forEach(function (k) { body.append(k, data[k]); });
			return fetch(ajaxUrl, { method: 'POST', credentials: 'same-origin', body: body })
				.then(function (r) { return r.json(); });
		}

		document.querySelectorAll('.strikescope-lead-status-select').forEach(function (sel) {
			sel.addEventListener('change', function () {
				var leadId = sel.getAttribute('data-lead-id');
				postAction('strikescope_lead_set_status', { lead_id: leadId, status: sel.value })
					.then(function (res) {
						if (!res.success) return;
						var row = document.querySelector('.strikescope-sold-price-row');
						if (row && res.data && typeof res.data.show_sold_price !== 'undefined') {
							row.style.display = res.data.show_sold_price ? '' : 'none';
						}
					});
			});
		});

		document.querySelectorAll('.strikescope-lead-agent-select').forEach(function (sel) {
			sel.addEventListener('change', function () {
				postAction('strikescope_lead_set_sales_agent', { lead_id: sel.getAttribute('data-lead-id'), agent_id: sel.value });
			});
		});

		document.querySelectorAll('.strikescope-save-sold-price-btn').forEach(function (btn) {
			btn.addEventListener('click', function () {
				var leadId = btn.getAttribute('data-lead-id');
				var input = document.querySelector('.strikescope-sold-price-input[data-lead-id="' + leadId + '"]');
				postAction('strikescope_lead_set_sold_price', { lead_id: leadId, sold_price: input ? input.value : '' })
					.then(function (res) {
						if (res.success && input && res.data && res.data.sold_price !== undefined) {
							input.value = res.data.sold_price;
						}
					});
			});
		});

		function copyContactText(text) {
			if (navigator.clipboard && navigator.clipboard.writeText) {
				return navigator.clipboard.writeText(text);
			}
			var ta = document.createElement('textarea');
			ta.value = text;
			document.body.appendChild(ta);
			ta.select();
			document.execCommand('copy');
			document.body.removeChild(ta);
			return Promise.resolve();
		}

		document.querySelectorAll('.strikescope-copy-contact-btn').forEach(function (btn) {
			btn.addEventListener('click', function (e) {
				e.preventDefault();
				var leadId = btn.getAttribute('data-lead-id');
				var format = btn.getAttribute('data-format') || 'tsv';
				postAction('strikescope_lead_copy_contact', { lead_id: leadId, format: format })
					.then(function (res) {
						if (res.success && res.data && res.data.text) {
							copyContactText(res.data.text).then(function () {
								var old = btn.textContent;
								btn.textContent = 'Copied!';
								setTimeout(function () { btn.textContent = old; }, 1500);
							});
						}
					});
			});
		});

		document.querySelectorAll('.strikescope-schedule-appt').forEach(function (btn) {
			btn.addEventListener('click', function () {
				var leadId = btn.getAttribute('data-lead-id');
				var wrap = btn.closest('.postbox');
				var starts = wrap ? wrap.querySelector('.strikescope-appt-starts') : null;
				var duration = wrap ? wrap.querySelector('.strikescope-appt-duration') : null;
				var type = wrap ? wrap.querySelector('.strikescope-appt-type') : null;
				var notes = wrap ? wrap.querySelector('.strikescope-appt-notes') : null;
				var agent = wrap ? wrap.querySelector('.strikescope-appt-agent') : null;
				postAction('strikescope_lead_schedule_appointment', {
					lead_id: leadId,
					starts_at: starts ? starts.value : '',
					duration: duration ? duration.value : '60',
					type: type ? type.value : 'onsite',
					location_notes: notes ? notes.value : '',
					agent_id: agent ? agent.value : '0'
				}).then(function (res) {
					if (res.success) {
						location.reload();
					} else {
						alert((res.data && res.data.message) ? res.data.message : 'Unable to schedule');
					}
				});
			});
		});

		document.querySelectorAll('.strikescope-cancel-appt').forEach(function (btn) {
			btn.addEventListener('click', function () {
				if (!confirm('Cancel this appointment?')) return;
				postAction('strikescope_lead_cancel_appointment', { lead_id: btn.getAttribute('data-lead-id') })
					.then(function (res) {
						if (res.success) location.reload();
					});
			});
		});

		document.querySelectorAll('.strikescope-blacklist-email-btn').forEach(function (btn) {
			btn.addEventListener('click', function () {
				postAction('strikescope_lead_blacklist_email', { lead_id: btn.getAttribute('data-lead-id') })
					.then(function (res) { alert(res.success ? (res.data.message || 'Done') : (res.data && res.data.message ? res.data.message : 'Failed')); });
			});
		});

		document.querySelectorAll('.strikescope-retry-geocode-btn').forEach(function (btn) {
			btn.addEventListener('click', function () {
				postAction('strikescope_lead_retry_geocode', { lead_id: btn.getAttribute('data-lead-id') })
					.then(function (res) { alert(res.success ? 'Geocode queued — refresh in a few seconds.' : 'Failed'); });
			});
		});

		function initDetailMap() {
			var el = document.getElementById('strikescope-lead-detail-map');
			if (!el || typeof L === 'undefined') return;
			var lat = parseFloat(el.getAttribute('data-lat'));
			var lng = parseFloat(el.getAttribute('data-lng'));
			var hasCoords = isFinite(lat) && isFinite(lng);
			var center = hasCoords ? [lat, lng] : [27.95, -82.45];
			var zoom = hasCoords ? 15 : 7;
			var map = L.map(el).setView(center, zoom);
			L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', { maxZoom: 19, attribution: '&copy; OpenStreetMap' }).addTo(map);
			if (hasCoords) {
				L.marker([lat, lng]).addTo(map).bindPopup(el.getAttribute('data-label') || 'Lead');
			} else {
				var addr = el.getAttribute('data-address') || '';
				if (addr) {
					L.popup({ maxWidth: 320 })
						.setLatLng(center)
						.setContent('<strong>Geocoding pending</strong><br>' + addr)
						.openOn(map);
				}
			}
			setTimeout(function () { map.invalidateSize(); }, 200);
		}

		function initDashboardMap() {
			var el = document.getElementById('strikescope-leads-map');
			if (!el || typeof L === 'undefined') return;
			var restUrl = el.getAttribute('data-rest-url');
			var restNonce = el.getAttribute('data-rest-nonce');
			var statusSel = document.getElementById('strikescope-map-filter-status');
			var serviceSel = document.getElementById('strikescope-map-filter-service');
			var countEl = document.getElementById('strikescope-map-count');
			var map = L.map(el).setView([27.95, -82.45], 8);
			L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', { maxZoom: 19, attribution: '&copy; OpenStreetMap' }).addTo(map);
			var layerGroup = L.layerGroup().addTo(map);

			function load() {
				var url = restUrl + '?status=' + encodeURIComponent(statusSel ? statusSel.value : '') + '&service=' + encodeURIComponent(serviceSel ? serviceSel.value : '');
				fetch(url, { headers: { 'X-WP-Nonce': restNonce } })
					.then(function (r) { return r.json(); })
					.then(function (data) {
						layerGroup.clearLayers();
						var leads = (data && data.leads) ? data.leads : [];
						if (countEl) countEl.textContent = leads.length + ' lead(s) on map';
						var bounds = [];
						leads.forEach(function (lead) {
							var m = L.marker([lead.lat, lead.lng]);
							m.bindPopup('<strong>' + lead.label + '</strong><br>' + lead.services + '<br>' + lead.status_label + '<br><a href="' + lead.admin_url + '">View lead</a>');
							layerGroup.addLayer(m);
							bounds.push([lead.lat, lead.lng]);
						});
						if (bounds.length) map.fitBounds(bounds, { padding: [40, 40] });
					});
			}
			if (statusSel) statusSel.addEventListener('change', load);
			if (serviceSel) serviceSel.addEventListener('change', load);
			load();
		}

		if (typeof L !== 'undefined') {
			if (document.getElementById('strikescope-lead-detail-map')) initDetailMap();
			if (document.getElementById('strikescope-leads-map')) initDashboardMap();
		} else {
			document.addEventListener('DOMContentLoaded', function () {
				setTimeout(function () {
					initDetailMap();
					initDashboardMap();
				}, 500);
			});
		}
	})();
	</script>
	<?php
}

add_action('admin_notices', static function () {
	$screen = function_exists('get_current_screen') ? get_current_screen() : null;
	if (!$screen || $screen->post_type !== STRIKESCOPE_LEAD_CPT) {
		return;
	}
	if (isset($_GET['strikescope_status_updated'])) {
		$count = absint($_GET['strikescope_status_updated']);
		if ($count > 0) {
			echo '<div class="notice notice-success is-dismissible"><p>Updated status on ' . esc_html((string) $count) . ' lead(s).</p></div>';
		}
	}
	if (isset($_GET['strikescope_agent_updated'])) {
		$count = absint($_GET['strikescope_agent_updated']);
		if ($count > 0) {
			echo '<div class="notice notice-success is-dismissible"><p>Assigned agent on ' . esc_html((string) $count) . ' lead(s).</p></div>';
		}
	}
});

add_action('manage_edit-' . STRIKESCOPE_LEAD_CPT . '_posts_custom_column', static function () {
	$screen = function_exists('get_current_screen') ? get_current_screen() : null;
	if (!$screen || $screen->id !== 'edit-' . STRIKESCOPE_LEAD_CPT) {
		return;
	}
	static $done = false;
	if ($done) {
		return;
	}
	$done = true;
	strikescope_form_print_admin_inline_script(wp_create_nonce('strikescope_lead_admin'));
}, 99);