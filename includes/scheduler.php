/**
 * Optional appointment scheduler: meta, UI, emails, ICS, cron, OAuth-ready hooks.
 */

// STRIKESCOPE_LEAD_FORM_APPOINTMENT_EMAILS_START
function strikescope_form_appointment_email_templates() {
	return array(
		"customer-confirmation" => "<!DOCTYPE html>\n<html lang=\"en\"><head><meta charset=\"UTF-8\"><title>Appointment confirmation</title></head>\n<body style=\"margin:0;padding:0;background:%%COLOR_BACKGROUND%%;font-family:Montserrat,Arial,sans-serif;color:#1c1c1c;\">\n<table role=\"presentation\" width=\"100%\" cellpadding=\"0\" cellspacing=\"0\" style=\"background:%%COLOR_BACKGROUND%%;\"><tr><td align=\"center\" style=\"padding:32px 16px;\">\n<table role=\"presentation\" width=\"600\" cellpadding=\"0\" cellspacing=\"0\" style=\"max-width:600px;background:#fff;border-radius:16px;border:1px solid %%COLOR_BORDER%%;\">\n<tr><td style=\"background:%%COLOR_SECONDARY%%;padding:24px;text-align:center;border-bottom:3px solid %%COLOR_PRIMARY%%;\"><img src=\"%%LOGO_URL%%\" alt=\"%%COMPANY_NAME%%\" width=\"200\" style=\"max-width:200px;height:auto;\"></td></tr>\n<tr><td style=\"padding:32px;\">\n<h1 style=\"margin:0 0 12px;font-size:24px;\">Your appointment is confirmed</h1>\n<p style=\"margin:0 0 16px;font-size:16px;line-height:1.6;\">Hi %%FIRST_NAME%%, we scheduled your consultation for <strong>%%APPOINTMENT_DATE%%</strong> at <strong>%%APPOINTMENT_TIME%%</strong>.</p>\n<p style=\"margin:0 0 8px;\"><strong>Type:</strong> %%APPOINTMENT_TYPE%%</p>\n<p style=\"margin:0 0 8px;\"><strong>Location / notes:</strong> %%APPOINTMENT_LOCATION%%</p>\n<p style=\"margin:0 0 8px;\"><strong>Your specialist:</strong> %%SALES_AGENT_NAME%% (%%SALES_AGENT_EMAIL%%)</p>\n<p style=\"margin:16px 0 0;font-size:14px;color:%%COLOR_MUTED%%;\">Questions? Call %%PHONE%% or reply to this email.</p>\n</td></tr></table></td></tr></table></body></html>",
		"agent-confirmation" => "<!DOCTYPE html>\n<html lang=\"en\"><head><meta charset=\"UTF-8\"><title>Appointment assigned</title></head>\n<body style=\"margin:0;padding:0;background:%%COLOR_BACKGROUND%%;font-family:Montserrat,Arial,sans-serif;color:#1c1c1c;\">\n<table role=\"presentation\" width=\"100%\" cellpadding=\"0\" cellspacing=\"0\" style=\"background:%%COLOR_BACKGROUND%%;\"><tr><td align=\"center\" style=\"padding:32px 16px;\">\n<table role=\"presentation\" width=\"600\" cellpadding=\"0\" cellspacing=\"0\" style=\"max-width:600px;background:#fff;border-radius:16px;border:1px solid %%COLOR_BORDER%%;\">\n<tr><td style=\"background:%%COLOR_SECONDARY%%;padding:24px;text-align:center;border-bottom:3px solid %%COLOR_PRIMARY%%;\"><img src=\"%%LOGO_URL%%\" alt=\"%%COMPANY_NAME%%\" width=\"200\" style=\"max-width:200px;height:auto;\"></td></tr>\n<tr><td style=\"padding:32px;\">\n<h1 style=\"margin:0 0 12px;font-size:24px;\">New lead appointment</h1>\n<p style=\"margin:0 0 16px;font-size:16px;line-height:1.6;\"><strong>%%FULL_NAME%%</strong> — %%APPOINTMENT_DATE%% at %%APPOINTMENT_TIME%%</p>\n<p style=\"margin:0 0 8px;\"><strong>Type:</strong> %%APPOINTMENT_TYPE%%</p>\n<p style=\"margin:0 0 8px;\"><strong>Location:</strong> %%APPOINTMENT_LOCATION%%</p>\n<p style=\"margin:0 0 8px;\"><strong>Lead phone:</strong> %%LEAD_PHONE%%</p>\n<p style=\"margin:0 0 8px;\"><strong>Lead email:</strong> %%LEAD_EMAIL%%</p>\n<p style=\"margin:16px 0 0;\"><a href=\"%%LEAD_ADMIN_URL%%\" style=\"color:%%COLOR_PRIMARY%%;font-weight:700;\">Open lead in admin</a></p>\n</td></tr></table></td></tr></table></body></html>",
		"customer-reminder" => "<!DOCTYPE html>\n<html lang=\"en\"><head><meta charset=\"UTF-8\"><title>Appointment reminder</title></head>\n<body style=\"margin:0;padding:0;background:%%COLOR_BACKGROUND%%;font-family:Montserrat,Arial,sans-serif;color:#1c1c1c;\">\n<table role=\"presentation\" width=\"100%\" cellpadding=\"0\" cellspacing=\"0\" style=\"background:%%COLOR_BACKGROUND%%;\"><tr><td align=\"center\" style=\"padding:32px 16px;\">\n<table role=\"presentation\" width=\"600\" cellpadding=\"0\" cellspacing=\"0\" style=\"max-width:600px;background:#fff;border-radius:16px;border:1px solid %%COLOR_BORDER%%;\">\n<tr><td style=\"padding:32px;\">\n<h1 style=\"margin:0 0 12px;font-size:24px;\">Reminder: your appointment</h1>\n<p style=\"margin:0 0 16px;font-size:16px;line-height:1.6;\">Hi %%FIRST_NAME%%, this is a reminder for your appointment on <strong>%%APPOINTMENT_DATE%%</strong> at <strong>%%APPOINTMENT_TIME%%</strong>.</p>\n<p style=\"margin:0 0 8px;\"><strong>Type:</strong> %%APPOINTMENT_TYPE%%</p>\n<p style=\"margin:0 0 8px;\"><strong>Location / notes:</strong> %%APPOINTMENT_LOCATION%%</p>\n<p style=\"margin:0;\">Specialist: %%SALES_AGENT_NAME%% — %%SALES_AGENT_PHONE%%</p>\n</td></tr></table></td></tr></table></body></html>",
		"agent-reminder" => "<!DOCTYPE html>\n<html lang=\"en\"><head><meta charset=\"UTF-8\"><title>Appointment reminder</title></head>\n<body style=\"margin:0;padding:0;background:%%COLOR_BACKGROUND%%;font-family:Montserrat,Arial,sans-serif;color:#1c1c1c;\">\n<table role=\"presentation\" width=\"100%\" cellpadding=\"0\" cellspacing=\"0\" style=\"background:%%COLOR_BACKGROUND%%;\"><tr><td align=\"center\" style=\"padding:32px 16px;\">\n<table role=\"presentation\" width=\"600\" cellpadding=\"0\" cellspacing=\"0\" style=\"max-width:600px;background:#fff;border-radius:16px;border:1px solid %%COLOR_BORDER%%;\">\n<tr><td style=\"padding:32px;\">\n<h1 style=\"margin:0 0 12px;font-size:24px;\">Upcoming lead appointment</h1>\n<p style=\"margin:0 0 16px;font-size:16px;line-height:1.6;\"><strong>%%FULL_NAME%%</strong> — %%APPOINTMENT_DATE%% at %%APPOINTMENT_TIME%%</p>\n<p style=\"margin:0 0 8px;\"><strong>Phone:</strong> %%LEAD_PHONE%%</p>\n<p style=\"margin:0 0 8px;\"><strong>Location:</strong> %%APPOINTMENT_LOCATION%%</p>\n<p style=\"margin:16px 0 0;\"><a href=\"%%LEAD_ADMIN_URL%%\" style=\"color:%%COLOR_PRIMARY%%;font-weight:700;\">View lead</a></p>\n</td></tr></table></td></tr></table></body></html>",
	);
}
// STRIKESCOPE_LEAD_FORM_APPOINTMENT_EMAILS_END

function strikescope_form_scheduler_config() {
	static $cfg = null;
	if ($cfg !== null) {
		return $cfg;
	}
	$cfg = array(
		'default_duration_minutes' => 60,
		'duration_presets'         => array(30, 60, 90),
		'appointment_types'        => array('onsite' => 'On-site visit', 'phone' => 'Phone call', 'video' => 'Video call'),
		'reminder_offsets_minutes' => array(1440, 60),
		'auto_status_on_schedule'  => true,
		'auto_status_targets'      => array('new', 'aging', 'contacted'),
	);
	$settings = strikescope_form_get_settings();
	if (isset($settings['scheduler_auto_status'])) {
		$cfg['auto_status_on_schedule'] = !empty($settings['scheduler_auto_status']);
	}
	return $cfg;
}

function strikescope_form_scheduler_enabled() {
	$settings = strikescope_form_get_settings();
	return !empty($settings['scheduler_enabled']);
}

function strikescope_form_get_appointment($post_id) {
	$raw = get_post_meta($post_id, '_lead_appointment', true);
	if ($raw === '' || $raw === false) {
		return null;
	}
	$data = is_string($raw) ? json_decode($raw, true) : $raw;
	return is_array($data) ? $data : null;
}

function strikescope_form_save_appointment($post_id, array $appointment) {
	update_post_meta($post_id, '_lead_appointment', wp_json_encode($appointment));
}

function strikescope_form_push_appointment_log($post_id, array $appointment) {
	$log = get_post_meta($post_id, '_lead_appointment_log', true);
	if (!is_array($log)) {
		$log = array();
	}
	array_unshift($log, $appointment);
	update_post_meta($post_id, '_lead_appointment_log', array_slice($log, 0, 20));
}

function strikescope_form_appointment_types() {
	$cfg = strikescope_form_scheduler_config();
	return isset($cfg['appointment_types']) && is_array($cfg['appointment_types'])
		? $cfg['appointment_types']
		: array('onsite' => 'On-site visit', 'phone' => 'Phone call', 'video' => 'Video call');
}

function strikescope_form_parse_appointment_input(array $input, $post_id) {
	$starts = isset($input['starts_at']) ? sanitize_text_field($input['starts_at']) : '';
	$duration = isset($input['duration_minutes']) ? absint($input['duration_minutes']) : 0;
	if ($duration <= 0) {
		$duration = (int) (strikescope_form_scheduler_config()['default_duration_minutes'] ?? 60);
	}
	$type = isset($input['type']) ? sanitize_key($input['type']) : 'onsite';
	if (!isset(strikescope_form_appointment_types()[ $type ])) {
		$type = 'onsite';
	}
	$notes = isset($input['location_notes']) ? sanitize_textarea_field($input['location_notes']) : '';
	$agent = isset($input['assigned_agent_id']) ? absint($input['assigned_agent_id']) : strikescope_form_get_sales_agent_id($post_id);
	if ($starts === '') {
		return new WP_Error('invalid_start', 'Start date/time required');
	}
	try {
		$tz = wp_timezone();
		$dt = new DateTime($starts, $tz);
	} catch (Exception $e) {
		return new WP_Error('invalid_start', 'Invalid date/time');
	}
	$end = clone $dt;
	$end->modify('+' . $duration . ' minutes');
	$existing = strikescope_form_get_appointment($post_id);
	$uid = ($existing && !empty($existing['calendar']['ics_uid']))
		? $existing['calendar']['ics_uid']
		: 'strikescope-lead-' . $post_id . '@%%SITE_URL%%';
	return array(
		'id'                => ($existing && !empty($existing['id'])) ? $existing['id'] : wp_generate_uuid4(),
		'starts_at'         => $dt->format('c'),
		'ends_at'           => $end->format('c'),
		'timezone'          => $tz->getName(),
		'type'              => $type,
		'location_notes'    => $notes,
		'assigned_agent_id' => $agent,
		'status'            => 'scheduled',
		'reminders_sent'    => array(),
		'calendar'          => array(
			'ics_uid'          => $uid,
			'google_event_id'  => $existing['calendar']['google_event_id'] ?? '',
			'outlook_event_id' => $existing['calendar']['outlook_event_id'] ?? '',
		),
	);
}

function strikescope_form_schedule_appointment($post_id, array $input, $is_reschedule = false) {
	$post_id = absint($post_id);
	if ($post_id <= 0 || !strikescope_form_scheduler_enabled()) {
		return new WP_Error('disabled', 'Scheduler disabled');
	}
	$appointment = strikescope_form_parse_appointment_input($input, $post_id);
	if (is_wp_error($appointment)) {
		return $appointment;
	}
	$prev = strikescope_form_get_appointment($post_id);
	if ($prev) {
		strikescope_form_push_appointment_log($post_id, $prev);
	}
	strikescope_form_save_appointment($post_id, $appointment);
	$cfg = strikescope_form_scheduler_config();
	if (!empty($cfg['auto_status_on_schedule'])) {
		$status = strikescope_form_get_lead_status($post_id);
		$targets = isset($cfg['auto_status_targets']) ? (array) $cfg['auto_status_targets'] : array('new', 'aging', 'contacted');
		if (in_array($status, $targets, true)) {
			strikescope_form_set_lead_status($post_id, 'scheduled');
		}
	}
	if ($appointment['assigned_agent_id'] > 0) {
		strikescope_form_set_sales_agent($post_id, (int) $appointment['assigned_agent_id']);
	}
	strikescope_form_send_appointment_emails($post_id, $is_reschedule ? 'reschedule' : 'schedule');
	strikescope_form_sync_appointment_calendars($post_id, $appointment);
	$event = $is_reschedule ? 'lead.appointment_rescheduled' : 'lead.appointment_scheduled';
	if (function_exists('strikescope_form_dispatch_integration_event')) {
		strikescope_form_dispatch_integration_event($event, $post_id, array('appointment' => $appointment));
	}
	return $appointment;
}

function strikescope_form_cancel_appointment($post_id) {
	$post_id = absint($post_id);
	$prev = strikescope_form_get_appointment($post_id);
	if (!$prev) {
		return new WP_Error('not_found', 'No appointment');
	}
	strikescope_form_push_appointment_log($post_id, $prev);
	$prev['status'] = 'cancelled';
	strikescope_form_save_appointment($post_id, $prev);
	strikescope_form_send_appointment_emails($post_id, 'cancel');
	strikescope_form_sync_appointment_calendars($post_id, $prev, true);
	if (function_exists('strikescope_form_dispatch_integration_event')) {
		strikescope_form_dispatch_integration_event('lead.appointment_cancelled', $post_id, array('appointment' => $prev));
	}
	delete_post_meta($post_id, '_lead_appointment');
	return true;
}

function strikescope_form_appointment_merge_tags($post_id, array $appointment) {
	$clean = strikescope_form_lead_payload_array($post_id);
	$settings = strikescope_form_get_settings();
	$agent_id = (int) ($appointment['assigned_agent_id'] ?? 0);
	$agent = $agent_id > 0 ? get_userdata($agent_id) : null;
	$start = new DateTime($appointment['starts_at']);
	$start->setTimezone(wp_timezone());
	$types = strikescope_form_appointment_types();
	$type_label = isset($types[ $appointment['type'] ?? '' ]) ? $types[ $appointment['type'] ] : ($appointment['type'] ?? '');
	$logo = 'https://%%SITE_URL%%/wp-content/uploads/2025/11/%%COMPANY_NAME%%-Text-Logo.png';
	$base = strikescope_form_lead_merge_tags($clean, $settings, strikescope_form_primary_service($clean['serviceType'] ?? array('roofing')), $post_id);
	return array_merge(
		$base,
		array(
			'%%APPOINTMENT_DATE%%'     => wp_date(get_option('date_format'), $start->getTimestamp()),
			'%%APPOINTMENT_TIME%%'     => wp_date(get_option('time_format'), $start->getTimestamp()),
			'%%APPOINTMENT_TYPE%%'     => $type_label,
			'%%APPOINTMENT_LOCATION%%' => (string) ($appointment['location_notes'] ?? ''),
			'%%SALES_AGENT_NAME%%'     => $agent ? $agent->display_name : '',
			'%%SALES_AGENT_EMAIL%%'    => $agent ? $agent->user_email : '',
			'%%SALES_AGENT_PHONE%%'    => StrikeScope_Branding::phone_display(),
			'%%LEAD_ADMIN_URL%%'       => strikescope_form_lead_detail_url($post_id),
			'%%LOGO_URL%%'             => $logo,
		)
	);
}

function strikescope_form_get_appointment_email_html($template_key) {
	$templates = strikescope_form_appointment_email_templates();
	return isset($templates[ $template_key ]) ? $templates[ $template_key ] : '';
}

function strikescope_form_send_appointment_emails($post_id, $action) {
	$appointment = strikescope_form_get_appointment($post_id);
	if (!$appointment && $action !== 'cancel') {
		return;
	}
	$clean = strikescope_form_lead_payload_array($post_id);
	if ($action === 'cancel' && !$appointment) {
		return;
	}
	$tags = strikescope_form_appointment_merge_tags($post_id, $appointment);
	$customer_tpl = $action === 'cancel' ? '' : strikescope_form_get_appointment_email_html('customer-confirmation');
	$agent_tpl    = strikescope_form_get_appointment_email_html('agent-confirmation');
	if ($action === 'cancel') {
		$subject_c = 'Your %%COMPANY_NAME%% appointment was cancelled';
		$subject_a = 'Lead appointment cancelled — ' . ($tags['%%FULL_NAME%%'] ?? '');
		$body_c = '<p>Hi ' . esc_html($tags['%%FIRST_NAME%%'] ?? '') . ', your appointment has been cancelled.</p>';
		$body_a = '<p>Appointment cancelled for ' . esc_html($tags['%%FULL_NAME%%'] ?? '') . '.</p>';
	} else {
		$subject_c = $action === 'reschedule' ? 'Your %%COMPANY_NAME%% appointment was rescheduled' : 'Your %%COMPANY_NAME%% appointment is confirmed';
		$subject_a = 'Lead appointment — ' . ($tags['%%FULL_NAME%%'] ?? '');
		$body_c = strikescope_form_apply_merge_tags($customer_tpl, $tags);
		$body_a = strikescope_form_apply_merge_tags($agent_tpl, $tags);
	}
	$headers = array('Content-Type: text/html; charset=UTF-8');
	if (is_email($clean['email'] ?? '')) {
		wp_mail($clean['email'], $subject_c, $body_c, $headers);
	}
	$agent_id = (int) ($appointment['assigned_agent_id'] ?? 0);
	$agent = $agent_id > 0 ? get_userdata($agent_id) : null;
	if ($agent && is_email($agent->user_email)) {
		wp_mail($agent->user_email, $subject_a, $body_a, $headers);
	}
}

function strikescope_form_build_ics($post_id, array $appointment, $cancel = false) {
	$clean = strikescope_form_lead_payload_array($post_id);
	$uid   = $appointment['calendar']['ics_uid'] ?? ('strikescope-lead-' . $post_id . '@%%SITE_URL%%');
	$start = new DateTime($appointment['starts_at']);
	$end   = new DateTime($appointment['ends_at']);
	$start->setTimezone(new DateTimeZone('UTC'));
	$end->setTimezone(new DateTimeZone('UTC'));
	$summary = '%%COMPANY_NAME%% consultation — ' . trim(($clean['firstName'] ?? '') . ' ' . ($clean['lastName'] ?? ''));
	$location = (string) ($appointment['location_notes'] ?? '');
	$method = $cancel ? 'CANCEL' : 'REQUEST';
	$status = $cancel ? 'CANCELLED' : 'CONFIRMED';
	$lines = array(
		'BEGIN:VCALENDAR',
		'VERSION:2.0',
		'PRODID:-//%%COMPANY_NAME%%//Lead Scheduler//EN',
		'CALSCALE:GREGORIAN',
		'METHOD:' . $method,
		'BEGIN:VEVENT',
		'UID:' . $uid,
		'DTSTAMP:' . gmdate('Ymd\THis\Z'),
		'DTSTART:' . $start->format('Ymd\THis\Z'),
		'DTEND:' . $end->format('Ymd\THis\Z'),
		'SUMMARY:' . strikescope_form_ics_escape($summary),
		'LOCATION:' . strikescope_form_ics_escape($location),
		'STATUS:' . $status,
		'END:VEVENT',
		'END:VCALENDAR',
	);
	return implode("\r\n", $lines);
}

function strikescope_form_ics_escape($text) {
	return str_replace(array('\\', ',', ';', "\n"), array('\\\\', '\\,', '\\;', '\\n'), $text);
}

function strikescope_form_google_calendar_url(array $appointment, $post_id) {
	$clean = strikescope_form_lead_payload_array($post_id);
	$start = new DateTime($appointment['starts_at']);
	$end   = new DateTime($appointment['ends_at']);
	$start->setTimezone(new DateTimeZone('UTC'));
	$end->setTimezone(new DateTimeZone('UTC'));
	$params = array(
		'action'   => 'TEMPLATE',
		'text'     => '%%COMPANY_NAME%% — ' . trim(($clean['firstName'] ?? '') . ' ' . ($clean['lastName'] ?? '')),
		'dates'    => $start->format('Ymd\THis\Z') . '/' . $end->format('Ymd\THis\Z'),
		'details'  => (string) ($appointment['location_notes'] ?? ''),
		'location' => (string) ($appointment['location_notes'] ?? ''),
	);
	return 'https://calendar.google.com/calendar/render?' . http_build_query($params, '', '&', PHP_QUERY_RFC3986);
}

function strikescope_form_outlook_calendar_url(array $appointment, $post_id) {
	$clean = strikescope_form_lead_payload_array($post_id);
	$start = new DateTime($appointment['starts_at']);
	$end   = new DateTime($appointment['ends_at']);
	$params = array(
		'path'         => '/calendar/action/compose',
		'rru'          => 'addevent',
		'subject'      => '%%COMPANY_NAME%% — ' . trim(($clean['firstName'] ?? '') . ' ' . ($clean['lastName'] ?? '')),
		'startdt'      => $start->format('Y-m-d\TH:i:s'),
		'enddt'        => $end->format('Y-m-d\TH:i:s'),
		'body'         => (string) ($appointment['location_notes'] ?? ''),
		'location'     => (string) ($appointment['location_notes'] ?? ''),
	);
	return 'https://outlook.live.com/calendar/0/deeplink/compose?' . http_build_query($params, '', '&', PHP_QUERY_RFC3986);
}

function strikescope_form_sync_appointment_calendars($post_id, array $appointment, $cancel = false) {
	// OAuth push placeholder — stores IDs when calendar API modules connect.
	do_action('strikescope_form_calendar_sync', $post_id, $appointment, $cancel);
}

add_action('admin_post_strikescope_download_ics', static function () {
	if (!strikescope_form_user_can_manage_leads()) {
		wp_die('Forbidden');
	}
	$lead_id = isset($_GET['lead_id']) ? absint($_GET['lead_id']) : 0;
	check_admin_referer('strikescope_ics_' . $lead_id);
	$appointment = strikescope_form_get_appointment($lead_id);
	if (!$appointment) {
		wp_die('No appointment');
	}
	$ics = strikescope_form_build_ics($lead_id, $appointment, ($appointment['status'] ?? '') === 'cancelled');
	header('Content-Type: text/calendar; charset=utf-8');
	header('Content-Disposition: attachment; filename="strikescope-appointment-' . $lead_id . '.ics"');
	echo $ics;
	exit;
});

function strikescope_form_render_scheduler_panel($lead_id) {
	if (!strikescope_form_scheduler_enabled()) {
		return;
	}
	$lead_id     = absint($lead_id);
	$appointment = strikescope_form_get_appointment($lead_id);
	$types       = strikescope_form_appointment_types();
	$cfg         = strikescope_form_scheduler_config();
	$durations   = isset($cfg['duration_presets']) ? (array) $cfg['duration_presets'] : array(30, 60, 90);
	$ics_url     = wp_nonce_url(admin_url('admin-post.php?action=strikescope_download_ics&lead_id=' . $lead_id), 'strikescope_ics_' . $lead_id);
	?>
	<div class="postbox" style="padding:16px;margin-top:16px;max-width:1200px;">
		<h2 style="margin-top:0;">Appointments</h2>
		<?php if ($appointment && ($appointment['status'] ?? '') === 'scheduled') : ?>
			<p><strong>Scheduled:</strong> <?php echo esc_html(wp_date(get_option('date_format') . ' ' . get_option('time_format'), strtotime($appointment['starts_at']))); ?>
			— <?php echo esc_html($types[ $appointment['type'] ] ?? $appointment['type']); ?></p>
			<p class="description"><?php echo esc_html($appointment['location_notes'] ?? ''); ?></p>
			<p>
				<a class="button" href="<?php echo esc_url($ics_url); ?>">Download .ics</a>
				<a class="button" href="<?php echo esc_url(strikescope_form_google_calendar_url($appointment, $lead_id)); ?>" target="_blank" rel="noopener">Add to Google Calendar</a>
				<a class="button" href="<?php echo esc_url(strikescope_form_outlook_calendar_url($appointment, $lead_id)); ?>" target="_blank" rel="noopener">Add to Outlook</a>
			</p>
		<?php endif; ?>
		<table class="form-table" role="presentation">
			<tr><th>Start</th><td><input type="datetime-local" class="strikescope-appt-starts widefat" data-lead-id="<?php echo esc_attr((string) $lead_id); ?>" value="<?php echo $appointment ? esc_attr(wp_date('Y-m-d\TH:i', strtotime($appointment['starts_at']))) : ''; ?>"></td></tr>
			<tr><th>Duration</th><td><select class="strikescope-appt-duration">
				<?php foreach ($durations as $d) : ?>
					<option value="<?php echo esc_attr((string) $d); ?>"><?php echo esc_html((string) $d); ?> min</option>
				<?php endforeach; ?>
			</select></td></tr>
			<tr><th>Type</th><td><select class="strikescope-appt-type">
				<?php foreach ($types as $key => $label) : ?>
					<option value="<?php echo esc_attr($key); ?>" <?php selected($appointment['type'] ?? 'onsite', $key); ?>><?php echo esc_html($label); ?></option>
				<?php endforeach; ?>
			</select></td></tr>
			<tr><th>Location / notes</th><td><textarea class="strikescope-appt-notes large-text" rows="2"><?php echo esc_textarea($appointment['location_notes'] ?? ''); ?></textarea></td></tr>
			<tr><th>Sales agent</th><td><?php echo strikescope_form_sales_agent_select_html($lead_id, 'strikescope-appt-agent widefat'); ?></td></tr>
		</table>
		<p>
			<button type="button" class="button button-primary strikescope-schedule-appt" data-lead-id="<?php echo esc_attr((string) $lead_id); ?>"><?php echo $appointment ? 'Reschedule' : 'Schedule'; ?></button>
			<?php if ($appointment && ($appointment['status'] ?? '') === 'scheduled') : ?>
				<button type="button" class="button strikescope-cancel-appt" data-lead-id="<?php echo esc_attr((string) $lead_id); ?>">Cancel appointment</button>
			<?php endif; ?>
		</p>
	</div>
	<?php
}

add_action('wp_ajax_strikescope_lead_schedule_appointment', static function () {
	if (!strikescope_form_user_can_manage_leads()) {
		wp_send_json_error(array('message' => 'Forbidden'), 403);
	}
	check_ajax_referer('strikescope_lead_admin', 'nonce');
	$post_id = isset($_POST['lead_id']) ? absint($_POST['lead_id']) : 0;
	$reschedule = !empty($_POST['reschedule']);
	$input = array(
		'starts_at'         => isset($_POST['starts_at']) ? wp_unslash($_POST['starts_at']) : '',
		'duration_minutes'  => isset($_POST['duration_minutes']) ? absint($_POST['duration_minutes']) : 60,
		'type'              => isset($_POST['type']) ? wp_unslash($_POST['type']) : 'onsite',
		'location_notes'    => isset($_POST['location_notes']) ? wp_unslash($_POST['location_notes']) : '',
		'assigned_agent_id' => isset($_POST['agent_id']) ? absint($_POST['agent_id']) : 0,
	);
	$result = strikescope_form_schedule_appointment($post_id, $input, $reschedule);
	if (is_wp_error($result)) {
		wp_send_json_error(array('message' => $result->get_error_message()), 400);
	}
	wp_send_json_success(array('appointment' => $result));
});

add_action('wp_ajax_strikescope_lead_cancel_appointment', static function () {
	if (!strikescope_form_user_can_manage_leads()) {
		wp_send_json_error(array('message' => 'Forbidden'), 403);
	}
	check_ajax_referer('strikescope_lead_admin', 'nonce');
	$post_id = isset($_POST['lead_id']) ? absint($_POST['lead_id']) : 0;
	$result = strikescope_form_cancel_appointment($post_id);
	if (is_wp_error($result)) {
		wp_send_json_error(array('message' => $result->get_error_message()), 400);
	}
	wp_send_json_success(array('cancelled' => true));
});

function strikescope_form_run_appointment_reminders() {
	if (!strikescope_form_scheduler_enabled()) {
		return;
	}
	$cfg = strikescope_form_scheduler_config();
	$offsets = isset($cfg['reminder_offsets_minutes']) ? array_map('intval', (array) $cfg['reminder_offsets_minutes']) : array(1440, 60);
	$q = new WP_Query(
		array(
			'post_type'      => STRIKESCOPE_LEAD_CPT,
			'post_status'    => strikescope_form_lead_post_statuses(),
			'posts_per_page' => 100,
			'meta_query'     => array(
				array('key' => '_lead_appointment', 'compare' => 'EXISTS'),
			),
		)
	);
	$now = time();
	foreach ($q->posts as $post) {
		$appointment = strikescope_form_get_appointment($post->ID);
		if (!$appointment || ($appointment['status'] ?? '') !== 'scheduled') {
			continue;
		}
		$start = strtotime($appointment['starts_at']);
		if (!$start || $start < $now) {
			continue;
		}
		foreach ($offsets as $minutes) {
			$key_c = 'customer_' . $minutes;
			$key_a = 'agent_' . $minutes;
			$due = $start - ($minutes * 60);
			if ($now < $due || $now > $start) {
				continue;
			}
			$sent = isset($appointment['reminders_sent']) && is_array($appointment['reminders_sent']) ? $appointment['reminders_sent'] : array();
			if (empty($sent[ $key_c ])) {
				strikescope_form_send_appointment_reminder($post->ID, $appointment, 'customer');
				$sent[ $key_c ] = true;
			}
			if (empty($sent[ $key_a ])) {
				strikescope_form_send_appointment_reminder($post->ID, $appointment, 'agent');
				$sent[ $key_a ] = true;
			}
			$appointment['reminders_sent'] = $sent;
			strikescope_form_save_appointment($post->ID, $appointment);
		}
	}
}

function strikescope_form_send_appointment_reminder($post_id, array $appointment, $target) {
	$clean = strikescope_form_lead_payload_array($post_id);
	$tags  = strikescope_form_appointment_merge_tags($post_id, $appointment);
	$tpl   = strikescope_form_get_appointment_email_html($target === 'agent' ? 'agent-reminder' : 'customer-reminder');
	$body  = strikescope_form_apply_merge_tags($tpl, $tags);
	$subject = $target === 'agent' ? 'Reminder: lead appointment — ' . ($tags['%%FULL_NAME%%'] ?? '') : 'Reminder: your %%COMPANY_NAME%% appointment';
	$headers = array('Content-Type: text/html; charset=UTF-8');
	if ($target === 'customer' && is_email($clean['email'] ?? '')) {
		wp_mail($clean['email'], $subject, $body, $headers);
		return;
	}
	$agent_id = (int) ($appointment['assigned_agent_id'] ?? 0);
	$agent = $agent_id > 0 ? get_userdata($agent_id) : null;
	if ($agent && is_email($agent->user_email)) {
		wp_mail($agent->user_email, $subject, $body, $headers);
	}
}

add_action('init', static function () {
	if (!wp_next_scheduled('strikescope_lead_appointment_reminders_cron')) {
		wp_schedule_event(time() + 300, 'strikescope_15min', 'strikescope_lead_appointment_reminders_cron');
	}
});

add_filter('cron_schedules', static function ($schedules) {
	$schedules['strikescope_15min'] = array('interval' => 900, 'display' => 'Every 15 minutes');
	return $schedules;
});

add_action('strikescope_lead_appointment_reminders_cron', 'strikescope_form_run_appointment_reminders');

add_action('rest_api_init', static function () {
	register_rest_route(
		'strikescope/v1',
		'/lead-form/leads/(?P<id>\d+)/appointment',
		array(
			array(
				'methods'             => 'GET',
				'permission_callback' => static function ($request) {
					return strikescope_form_authenticate_api_request($request, 'leads:read') || strikescope_form_user_can_manage_leads();
				},
				'callback'            => static function ($request) {
					$id = absint($request['id']);
					$ap = strikescope_form_get_appointment($id);
					return array('appointment' => $ap);
				},
			),
			array(
				'methods'             => 'POST',
				'permission_callback' => static function ($request) {
					return strikescope_form_authenticate_api_request($request, 'leads:write') || strikescope_form_user_can_manage_leads();
				},
				'callback'            => static function ($request) {
					$id = absint($request['id']);
					$data = $request->get_json_params();
					$result = strikescope_form_schedule_appointment($id, is_array($data) ? $data : array(), !empty($data['reschedule']));
					if (is_wp_error($result)) {
						return $result;
					}
					return array('appointment' => $result);
				},
			),
			array(
				'methods'             => 'DELETE',
				'permission_callback' => static function ($request) {
					return strikescope_form_authenticate_api_request($request, 'leads:write') || strikescope_form_user_can_manage_leads();
				},
				'callback'            => static function ($request) {
					$id = absint($request['id']);
					$result = strikescope_form_cancel_appointment($id);
					if (is_wp_error($result)) {
						return $result;
					}
					return array('cancelled' => true);
				},
			),
		)
	);

	register_rest_route(
		'strikescope/v1',
		'/lead-form/calendar/google/callback',
		array(
			'methods'             => 'GET',
			'permission_callback' => static function () {
				return is_user_logged_in();
			},
			'callback'            => static function ($request) {
				// OAuth token exchange placeholder for Google Calendar API.
				return array('status' => 'oauth_ready', 'message' => 'Configure Google credentials in Lead Form settings.');
			},
		)
	);

	register_rest_route(
		'strikescope/v1',
		'/lead-form/calendar/outlook/callback',
		array(
			'methods'             => 'GET',
			'permission_callback' => static function () {
				return is_user_logged_in();
			},
			'callback'            => static function ($request) {
				return array('status' => 'oauth_ready', 'message' => 'Configure Microsoft Graph credentials in Lead Form settings.');
			},
		)
	);
});