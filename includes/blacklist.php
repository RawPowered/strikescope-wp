/**
 * Submission blacklist — silent reject at validate time.
 */

function strikescope_form_blacklist_defaults() {
	return array(
		'blacklist_emails'  => '',
		'blacklist_zips'    => '',
		'blacklist_cities'  => '',
		'blacklist_states'  => '',
	);
}

function strikescope_form_parse_blacklist_lines($raw) {
	$items = array();
	foreach (preg_split('/\r\n|\r|\n|,/', (string) $raw) as $line) {
		$line = trim($line);
		if ($line !== '') {
			$items[] = $line;
		}
	}
	return $items;
}

function strikescope_form_normalize_blacklist_email($email) {
	return strtolower(sanitize_email($email));
}

function strikescope_form_normalize_blacklist_zip($zip) {
	$digits = preg_replace('/\D/', '', (string) $zip);
	return strlen($digits) >= 5 ? substr($digits, 0, 5) : '';
}

function strikescope_form_normalize_blacklist_city($city) {
	return strtolower(trim(sanitize_text_field((string) $city)));
}

function strikescope_form_normalize_blacklist_state($state) {
	$state = strtoupper(trim(sanitize_text_field((string) $state)));
	return strlen($state) === 2 ? $state : '';
}

function strikescope_form_get_blacklist_sets(array $settings) {
	$emails = array();
	foreach (strikescope_form_parse_blacklist_lines($settings['blacklist_emails'] ?? '') as $line) {
		$email = strikescope_form_normalize_blacklist_email($line);
		if ($email !== '' && is_email($email)) {
			$emails[ $email ] = true;
		}
	}

	$zips = array();
	foreach (strikescope_form_parse_blacklist_lines($settings['blacklist_zips'] ?? '') as $line) {
		$zip = strikescope_form_normalize_blacklist_zip($line);
		if ($zip !== '') {
			$zips[ $zip ] = true;
		}
	}

	$cities = array();
	foreach (strikescope_form_parse_blacklist_lines($settings['blacklist_cities'] ?? '') as $line) {
		$city = strikescope_form_normalize_blacklist_city($line);
		if ($city !== '') {
			$cities[ $city ] = true;
		}
	}

	$states = array();
	foreach (strikescope_form_parse_blacklist_lines($settings['blacklist_states'] ?? '') as $line) {
		$state = strikescope_form_normalize_blacklist_state($line);
		if ($state !== '') {
			$states[ $state ] = true;
		}
	}

	return array(
		'emails' => $emails,
		'zips'   => $zips,
		'cities' => $cities,
		'states' => $states,
	);
}

function strikescope_form_check_blacklist(array $clean, array $settings) {
	$sets = strikescope_form_get_blacklist_sets($settings);

	$email = strikescope_form_normalize_blacklist_email($clean['email'] ?? '');
	if ($email !== '' && isset($sets['emails'][ $email ])) {
		return true;
	}

	$zip = strikescope_form_normalize_blacklist_zip($clean['zipCode'] ?? '');
	if ($zip !== '' && isset($sets['zips'][ $zip ])) {
		return true;
	}

	$city  = strikescope_form_normalize_blacklist_city($clean['city'] ?? '');
	$state = strikescope_form_normalize_blacklist_state($clean['state'] ?? '');

	if ($city !== '' && isset($sets['cities'][ $city ])) {
		return true;
	}

	if ($state !== '' && isset($sets['states'][ $state ])) {
		return true;
	}

	return false;
}

function strikescope_form_add_email_to_blacklist($email) {
	$email = strikescope_form_normalize_blacklist_email($email);
	if ($email === '' || !is_email($email)) {
		return false;
	}
	$settings = strikescope_form_get_settings();
	$sets     = strikescope_form_get_blacklist_sets($settings);
	if (isset($sets['emails'][ $email ])) {
		return true;
	}
	$lines = strikescope_form_parse_blacklist_lines($settings['blacklist_emails'] ?? '');
	$lines[] = $email;
	strikescope_form_update_settings(array(
		'blacklist_emails' => implode("\n", array_unique($lines)),
	));
	return true;
}

function strikescope_form_sanitize_blacklist_settings($input, $current) {
	$out = strikescope_form_blacklist_defaults();
	if (!is_array($input)) {
		return wp_parse_args(is_array($current) ? $current : array(), $out);
	}
	$out['blacklist_emails'] = isset($input['blacklist_emails']) ? sanitize_textarea_field($input['blacklist_emails']) : ($current['blacklist_emails'] ?? '');
	$out['blacklist_zips']   = isset($input['blacklist_zips']) ? sanitize_textarea_field($input['blacklist_zips']) : ($current['blacklist_zips'] ?? '');
	$out['blacklist_cities'] = isset($input['blacklist_cities']) ? sanitize_textarea_field($input['blacklist_cities']) : ($current['blacklist_cities'] ?? '');
	$out['blacklist_states'] = isset($input['blacklist_states']) ? sanitize_textarea_field($input['blacklist_states']) : ($current['blacklist_states'] ?? '');
	return $out;
}