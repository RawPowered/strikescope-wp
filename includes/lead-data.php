/**
 * Canonical lead data helpers: sold price, export row, copy text, merge context.
 */

function strikescope_form_get_sold_price($post_id) {
	$raw = get_post_meta($post_id, '_lead_sold_price', true);
	if ($raw === '' || $raw === false) {
		return '';
	}
	return strikescope_form_normalize_sold_price($raw, false);
}

function strikescope_form_normalize_sold_price($value, $allow_empty = true) {
	$value = is_string($value) ? trim($value) : '';
	if ($value === '') {
		return $allow_empty ? '' : '';
	}
	$value = preg_replace('/[^0-9.]/', '', $value);
	if ($value === '' || !is_numeric($value)) {
		return '';
	}
	$num = round((float) $value, 2);
	if ($num <= 0) {
		return '';
	}
	return number_format($num, 2, '.', '');
}

function strikescope_form_set_sold_price($post_id, $value) {
	$post_id = absint($post_id);
	if ($post_id <= 0) {
		return false;
	}
	if (strikescope_form_get_lead_status($post_id) !== 'sold') {
		return false;
	}
	$price = strikescope_form_normalize_sold_price($value, true);
	if ($price === '') {
		delete_post_meta($post_id, '_lead_sold_price');
		return true;
	}
	update_post_meta($post_id, '_lead_sold_price', $price);
	return true;
}

function strikescope_form_format_sold_price_display($price) {
	if ($price === '' || $price === null) {
		return '';
	}
	return '$' . number_format((float) $price, 2);
}

function strikescope_form_get_sales_agent_id($post_id) {
	return absint(get_post_meta($post_id, '_lead_sales_agent', true));
}

function strikescope_form_get_sales_agent_user($post_id) {
	$user_id = strikescope_form_get_sales_agent_id($post_id);
	if ($user_id <= 0) {
		return null;
	}
	$user = get_userdata($user_id);
	return $user instanceof WP_User ? $user : null;
}

function strikescope_form_lead_to_array($post_id) {
	$post_id = absint($post_id);
	$post    = get_post($post_id);
	if (!$post || $post->post_type !== STRIKESCOPE_LEAD_CPT) {
		return array();
	}

	$clean   = strikescope_form_lead_payload_array($post_id);
	$status  = strikescope_form_get_lead_status($post_id);
	$agent   = strikescope_form_get_sales_agent_user($post_id);
	$sold    = strikescope_form_get_sold_price($post_id);
	$submitted = get_post_meta($post_id, '_lead_submitted_at', true);
	$status_updated = get_post_meta($post_id, '_lead_status_updated_at', true);
	$appointment = function_exists('strikescope_form_get_appointment')
		? strikescope_form_get_appointment($post_id)
		: null;

	$first = isset($clean['firstName']) ? (string) $clean['firstName'] : '';
	$last  = isset($clean['lastName']) ? (string) $clean['lastName'] : '';

	return array(
		'lead_id'            => $post_id,
		'submitted_at'       => is_numeric($submitted) ? gmdate('c', (int) $submitted) : gmdate('c', strtotime($post->post_date_gmt . ' UTC')),
		'status'             => $status,
		'status_label'       => strikescope_form_status_catalog()[ $status ]['label'] ?? $status,
		'status_updated_at'  => is_numeric($status_updated) ? gmdate('c', (int) $status_updated) : '',
		'sold_price'         => $sold,
		'sold_price_display' => strikescope_form_format_sold_price_display($sold),
		'sales_agent_id'     => $agent ? (int) $agent->ID : 0,
		'sales_agent'        => $agent ? $agent->display_name : '',
		'sales_agent_email'  => $agent ? $agent->user_email : '',
		'first_name'         => $first,
		'last_name'          => $last,
		'full_name'          => trim($first . ' ' . $last),
		'email'              => isset($clean['email']) ? (string) $clean['email'] : '',
		'phone'              => isset($clean['phone']) ? (string) $clean['phone'] : '',
		'street'             => isset($clean['address']) ? (string) $clean['address'] : '',
		'city'               => isset($clean['city']) ? (string) $clean['city'] : '',
		'state'              => isset($clean['state']) ? (string) $clean['state'] : '',
		'zip'                => isset($clean['zipCode']) ? (string) $clean['zipCode'] : '',
		'country'            => 'US',
		'services'           => get_post_meta($post_id, '_lead_services', true),
		'service_ids'        => isset($clean['serviceType']) && is_array($clean['serviceType']) ? array_values($clean['serviceType']) : array(),
		'property_type'      => isset($clean['propertyType']) ? (string) $clean['propertyType'] : '',
		'timeline'           => isset($clean['timeline']) ? (string) $clean['timeline'] : '',
		'budget'             => isset($clean['budget']) ? (string) $clean['budget'] : '',
		'notes'              => isset($clean['additionalDetails']) ? (string) $clean['additionalDetails'] : '',
		'appointment'        => $appointment,
		'appointment_start'  => ($appointment && !empty($appointment['starts_at'])) ? $appointment['starts_at'] : '',
		'appointment_end'    => ($appointment && !empty($appointment['ends_at'])) ? $appointment['ends_at'] : '',
		'appointment_status' => ($appointment && !empty($appointment['status'])) ? $appointment['status'] : '',
		'source'             => '%%COMPANY_NAME%% Website',
		'admin_url'          => strikescope_form_lead_detail_url($post_id),
		'payload'            => $clean,
	);
}

function strikescope_form_lead_merge_context($post_id) {
	$row = strikescope_form_lead_to_array($post_id);
	return array(
		'{{leadId}}'           => (string) ($row['lead_id'] ?? ''),
		'{{firstName}}'        => (string) ($row['first_name'] ?? ''),
		'{{lastName}}'         => (string) ($row['last_name'] ?? ''),
		'{{fullName}}'         => (string) ($row['full_name'] ?? ''),
		'{{email}}'            => (string) ($row['email'] ?? ''),
		'{{phone}}'            => (string) ($row['phone'] ?? ''),
		'{{street}}'           => (string) ($row['street'] ?? ''),
		'{{city}}'             => (string) ($row['city'] ?? ''),
		'{{state}}'            => (string) ($row['state'] ?? ''),
		'{{zip}}'              => (string) ($row['zip'] ?? ''),
		'{{services}}'         => (string) ($row['services'] ?? ''),
		'{{status}}'           => (string) ($row['status'] ?? ''),
		'{{soldPrice}}'        => (string) ($row['sold_price'] ?? ''),
		'{{salesAgent}}'       => (string) ($row['sales_agent'] ?? ''),
		'{{salesAgentEmail}}'  => (string) ($row['sales_agent_email'] ?? ''),
		'{{appointmentStart}}' => (string) ($row['appointment_start'] ?? ''),
		'{{appointmentEnd}}'   => (string) ($row['appointment_end'] ?? ''),
		'{{adminUrl}}'         => (string) ($row['admin_url'] ?? ''),
		'{{source}}'           => (string) ($row['source'] ?? '%%COMPANY_NAME%% Website'),
	);
}

function strikescope_form_contact_copy_text($post_id, $format = 'tsv') {
	$row = strikescope_form_lead_to_array($post_id);
	if (!$row) {
		return '';
	}
	$lines = array();
	if ($format === 'multiline') {
		$lines[] = $row['full_name'];
		$lines[] = $row['email'];
		$lines[] = $row['phone'];
		$lines[] = trim($row['street'] . ', ' . $row['city'] . ' ' . $row['state'] . ' ' . $row['zip']);
		if ($row['services'] !== '') {
			$lines[] = $row['services'];
		}
		if ($row['sold_price'] !== '') {
			$lines[] = 'Deal: ' . strikescope_form_format_sold_price_display($row['sold_price']);
		}
		return implode("\n", $lines);
	}
	$parts = array(
		$row['full_name'],
		$row['email'],
		$row['phone'],
		$row['street'],
		$row['city'],
		$row['state'],
		$row['zip'],
		$row['services'],
	);
	if ($row['sold_price'] !== '') {
		$parts[] = strikescope_form_format_sold_price_display($row['sold_price']);
	}
	return implode("\t", $parts);
}

function strikescope_form_lead_csv_row($post_id) {
	$row = strikescope_form_lead_to_array($post_id);
	return array(
		'lead_id'            => (string) ($row['lead_id'] ?? ''),
		'submitted_at'       => (string) ($row['submitted_at'] ?? ''),
		'status'             => (string) ($row['status'] ?? ''),
		'status_updated_at'  => (string) ($row['status_updated_at'] ?? ''),
		'sold_price'         => (string) ($row['sold_price'] ?? ''),
		'sales_agent'        => (string) ($row['sales_agent'] ?? ''),
		'sales_agent_email'  => (string) ($row['sales_agent_email'] ?? ''),
		'first_name'         => (string) ($row['first_name'] ?? ''),
		'last_name'          => (string) ($row['last_name'] ?? ''),
		'full_name'          => (string) ($row['full_name'] ?? ''),
		'email'              => (string) ($row['email'] ?? ''),
		'phone'              => (string) ($row['phone'] ?? ''),
		'street'             => (string) ($row['street'] ?? ''),
		'city'               => (string) ($row['city'] ?? ''),
		'state'              => (string) ($row['state'] ?? ''),
		'zip'                => (string) ($row['zip'] ?? ''),
		'country'            => (string) ($row['country'] ?? 'US'),
		'services'           => (string) ($row['services'] ?? ''),
		'property_type'      => (string) ($row['property_type'] ?? ''),
		'timeline'           => (string) ($row['timeline'] ?? ''),
		'budget'             => (string) ($row['budget'] ?? ''),
		'notes'              => (string) ($row['notes'] ?? ''),
		'appointment_start'  => (string) ($row['appointment_start'] ?? ''),
		'appointment_end'    => (string) ($row['appointment_end'] ?? ''),
		'appointment_status' => (string) ($row['appointment_status'] ?? ''),
		'source'             => (string) ($row['source'] ?? '%%COMPANY_NAME%% Website'),
		'admin_url'          => (string) ($row['admin_url'] ?? ''),
	);
}

function strikescope_form_csv_escape($value) {
	$value = (string) $value;
	if (strpos($value, '"') !== false || strpos($value, ',') !== false || strpos($value, "\n") !== false || strpos($value, "\r") !== false) {
		return '"' . str_replace('"', '""', $value) . '"';
	}
	return $value;
}

function strikescope_form_stream_csv_rows($post_ids, $columns) {
	echo "\xEF\xBB\xBF";
	$headers = array();
	foreach ($columns as $col) {
		$headers[] = isset($col['header']) ? $col['header'] : $col['key'];
	}
	echo implode(',', array_map('strikescope_form_csv_escape', $headers)) . "\r\n";
	foreach ((array) $post_ids as $post_id) {
		if (get_post_type($post_id) !== STRIKESCOPE_LEAD_CPT) {
			continue;
		}
		$row = strikescope_form_lead_csv_row($post_id);
		$line = array();
		foreach ($columns as $col) {
			$key = $col['key'];
			$line[] = strikescope_form_csv_escape(isset($row[ $key ]) ? $row[ $key ] : '');
		}
		echo implode(',', $line) . "\r\n";
	}
}

function strikescope_form_export_columns_config() {
	static $columns = null;
	if ($columns !== null) {
		return $columns;
	}
	// STRIKESCOPE_LEAD_FORM_EXPORT_COLUMNS_START
	$columns = array(
		array('key' => "lead_id", 'header' => "Lead ID"),
		array('key' => "submitted_at", 'header' => "Submitted At"),
		array('key' => "status", 'header' => "Status"),
		array('key' => "status_updated_at", 'header' => "Status Updated At"),
		array('key' => "sold_price", 'header' => "Sold Price"),
		array('key' => "sales_agent", 'header' => "Sales Agent"),
		array('key' => "sales_agent_email", 'header' => "Sales Agent Email"),
		array('key' => "first_name", 'header' => "First Name"),
		array('key' => "last_name", 'header' => "Last Name"),
		array('key' => "full_name", 'header' => "Full Name"),
		array('key' => "email", 'header' => "Email"),
		array('key' => "phone", 'header' => "Phone"),
		array('key' => "street", 'header' => "Street Address"),
		array('key' => "city", 'header' => "City"),
		array('key' => "state", 'header' => "State"),
		array('key' => "zip", 'header' => "ZIP"),
		array('key' => "country", 'header' => "Country"),
		array('key' => "services", 'header' => "Services"),
		array('key' => "property_type", 'header' => "Property Type"),
		array('key' => "timeline", 'header' => "Timeline"),
		array('key' => "budget", 'header' => "Budget"),
		array('key' => "notes", 'header' => "Notes"),
		array('key' => "appointment_start", 'header' => "Appointment Start"),
		array('key' => "appointment_end", 'header' => "Appointment End"),
		array('key' => "appointment_status", 'header' => "Appointment Status"),
		array('key' => "source", 'header' => "Lead Source"),
		array('key' => "admin_url", 'header' => "Admin URL"),
	);
	// STRIKESCOPE_LEAD_FORM_EXPORT_COLUMNS_END
	return $columns;
}

function strikescope_form_export_presets_config() {
	static $presets = null;
	if ($presets !== null) {
		return $presets;
	}
	// STRIKESCOPE_LEAD_FORM_EXPORT_PRESETS_START
	$presets = array(
		"all" => array('label' => "All leads", 'statuses' => array()),
		"open" => array('label' => "Open pipeline", 'statuses' => array("new", "aging", "contacted", "scheduled")),
		"sold" => array('label' => "Sold", 'statuses' => array("sold")),
		"lost" => array('label' => "Lost", 'statuses' => array("lost")),
		"closed" => array('label' => "Closed", 'statuses' => array("sold", "lost", "dnq", "dnc", "spam", "blacklisted")),
	);
	// STRIKESCOPE_LEAD_FORM_EXPORT_PRESETS_END
	return $presets;
}

function strikescope_form_query_lead_ids($args = array()) {
	$defaults = array(
		'statuses' => array(),
		'agent_id' => 0,
		'date_from'=> '',
		'date_to'  => '',
		'limit'    => 5000,
	);
	$args = wp_parse_args($args, $defaults);
	$meta_query = array();
	if (!empty($args['statuses'])) {
		$statuses = array_values(array_filter(array_map('sanitize_key', (array) $args['statuses'])));
		if ($statuses) {
			if (in_array('new', $statuses, true) && count($statuses) === 1) {
				$meta_query[] = array(
					'relation' => 'OR',
					array('key' => '_lead_status', 'compare' => 'NOT EXISTS'),
					array('key' => '_lead_status', 'value' => 'new', 'compare' => '='),
				);
			} else {
				$meta_query[] = array(
					'key'     => '_lead_status',
					'value'   => $statuses,
					'compare' => 'IN',
				);
			}
		}
	}
	if ($args['agent_id'] > 0) {
		$meta_query[] = array(
			'key'     => '_lead_sales_agent',
			'value'   => (string) absint($args['agent_id']),
			'compare' => '=',
		);
	}
	$date_query = array();
	if ($args['date_from'] !== '') {
		$date_query['after'] = sanitize_text_field($args['date_from']);
	}
	if ($args['date_to'] !== '') {
		$date_query['before'] = sanitize_text_field($args['date_to']);
	}
	$query_args = array(
		'post_type'      => STRIKESCOPE_LEAD_CPT,
		'post_status'    => strikescope_form_lead_post_statuses(),
		'posts_per_page' => min(5000, max(1, absint($args['limit']))),
		'fields'         => 'ids',
		'orderby'        => 'date',
		'order'          => 'DESC',
	);
	if ($meta_query) {
		$query_args['meta_query'] = $meta_query;
	}
	if ($date_query) {
		$query_args['date_query'] = array($date_query);
	}
	$q = new WP_Query($query_args);
	return $q->posts;
}