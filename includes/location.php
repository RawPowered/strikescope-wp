/**
 * Google Maps URLs, geocoding, property lookup HTML.
 */

function strikescope_form_format_address_line(array $clean) {
	$parts = array_filter(
		array(
			isset($clean['address']) ? trim((string) $clean['address']) : '',
			isset($clean['city']) ? trim((string) $clean['city']) : '',
			isset($clean['state']) ? trim((string) $clean['state']) : '',
			isset($clean['zipCode']) ? trim((string) $clean['zipCode']) : '',
		)
	);
	return implode(', ', $parts);
}

function strikescope_form_google_maps_url(array $clean) {
	$query = strikescope_form_format_address_line($clean);
	if ($query === '') {
		return '';
	}
	return 'https://www.google.com/maps/search/?api=1&query=' . rawurlencode($query);
}

function strikescope_form_lead_admin_url($lead_id) {
	if (function_exists('strikescope_form_lead_detail_url')) {
		return strikescope_form_lead_detail_url($lead_id);
	}
	$lead_id = absint($lead_id);
	if ($lead_id <= 0) {
		return '';
	}
	return admin_url('admin.php?page=strikescope-lead-detail&lead_id=' . $lead_id);
}

function strikescope_form_property_lookup_html(array $clean, $lead_id = 0) {
	$maps  = strikescope_form_google_maps_url($clean);
	$admin = strikescope_form_lead_admin_url($lead_id);

	$buttons = array();
	if ($maps !== '') {
		$buttons[] = '<a href="' . esc_url($maps) . '" style="display:inline-block;background-color:#4285f4;color:#ffffff;text-decoration:none;font-size:14px;font-weight:700;padding:12px 20px;border-radius:8px;margin:4px 6px 4px 0;">Open in Google Maps</a>';
	}
	if ($admin !== '') {
		$buttons[] = '<a href="' . esc_url($admin) . '" style="display:inline-block;background-color:%%COLOR_PRIMARY%%;color:#ffffff;text-decoration:none;font-size:14px;font-weight:700;padding:12px 20px;border-radius:8px;margin:4px 6px 4px 0;">View lead in admin</a>';
	}
	if (!$buttons) {
		return '';
	}
	return '<div style="text-align:center;padding:8px 0 16px;">' . implode('', $buttons) . '</div>';
}

function strikescope_form_location_merge_tags(array $clean, $lead_id = 0) {
	$maps  = strikescope_form_google_maps_url($clean);
	$admin = strikescope_form_lead_admin_url($lead_id);

	return array(
		'%%GOOGLE_MAPS_URL%%'       => esc_url($maps),
		'%%LEAD_ADMIN_URL%%'        => esc_url($admin),
		'%%PROPERTY_LOOKUP_HTML%%'  => strikescope_form_property_lookup_html($clean, $lead_id),
	);
}

function strikescope_form_geocode_address_string(array $clean) {
	return strikescope_form_format_address_line($clean);
}

function strikescope_geocode_lead($post_id) {
	$post_id = absint($post_id);
	if ($post_id <= 0 || get_post_type($post_id) !== STRIKESCOPE_LEAD_CPT) {
		return;
	}

	$raw = get_post_meta($post_id, '_lead_payload', true);
	$clean = json_decode((string) $raw, true);
	if (!is_array($clean)) {
		update_post_meta($post_id, '_lead_geocode_status', 'failed');
		return;
	}

	$query = strikescope_form_geocode_address_string($clean);
	if ($query === '') {
		update_post_meta($post_id, '_lead_geocode_status', 'failed');
		return;
	}

	$url = add_query_arg(
		array(
			'q'              => $query,
			'format'         => 'json',
			'limit'          => 1,
			'addressdetails' => 0,
		),
		'https://nominatim.openstreetmap.org/search'
	);

	$site_name = get_bloginfo('name');
	if ($site_name === '') {
		$site_name = 'StrikeScopeWP';
	}

	$response = wp_remote_get(
		$url,
		array(
			'timeout' => 15,
			'headers' => array(
				'User-Agent' => $site_name . ' LeadForm/1.0 (contact form geocoder; ' . home_url('/') . ')',
			),
		)
	);

	if (is_wp_error($response)) {
		update_post_meta($post_id, '_lead_geocode_status', 'failed');
		return;
	}

	$body = json_decode(wp_remote_retrieve_body($response), true);
	if (!is_array($body) || empty($body[0]['lat']) || empty($body[0]['lon'])) {
		update_post_meta($post_id, '_lead_geocode_status', 'failed');
		return;
	}

	update_post_meta($post_id, '_lead_lat', (string) $body[0]['lat']);
	update_post_meta($post_id, '_lead_lng', (string) $body[0]['lon']);
	update_post_meta($post_id, '_lead_geocode_status', 'ok');
}

add_action('strikescope_geocode_lead', 'strikescope_geocode_lead');

function strikescope_form_schedule_geocode($post_id) {
	$post_id = absint($post_id);
	if ($post_id <= 0) {
		return;
	}
	update_post_meta($post_id, '_lead_geocode_status', 'pending');
	wp_schedule_single_event(time() + 5, 'strikescope_geocode_lead', array($post_id));
}

function strikescope_form_get_lead_coords($post_id) {
	$lat = get_post_meta($post_id, '_lead_lat', true);
	$lng = get_post_meta($post_id, '_lead_lng', true);
	if ($lat === '' || $lng === '') {
		return null;
	}
	return array('lat' => (float) $lat, 'lng' => (float) $lng);
}

function strikescope_form_lead_payload_array($post_id) {
	$raw = get_post_meta($post_id, '_lead_payload', true);
	$data = json_decode((string) $raw, true);
	return is_array($data) ? $data : array();
}
