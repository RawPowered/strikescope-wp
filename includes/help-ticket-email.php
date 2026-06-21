/**
 * Help Desk: threaded email, inbound webhook, status notifications.
 */

function strikescope_help_ticket_embedded_email_templates() {
	// STRIKESCOPE_HELP_TICKET_EMAILS_START
	return array(
		"new-ticket-customer" => "<!DOCTYPE html>\n<html lang=\"en\"><head><meta charset=\"UTF-8\"><title>Help ticket received</title></head>\n<body style=\"margin:0;padding:0;background:%%COLOR_BACKGROUND%%;font-family:Montserrat,Arial,sans-serif;color:#1c1c1c;\">\n<table role=\"presentation\" width=\"100%\" cellpadding=\"0\" cellspacing=\"0\" style=\"background:%%COLOR_BACKGROUND%%;\"><tr><td align=\"center\" style=\"padding:32px 16px;\">\n<table role=\"presentation\" width=\"600\" cellpadding=\"0\" cellspacing=\"0\" style=\"max-width:600px;background:#fff;border-radius:16px;border:1px solid %%COLOR_BORDER%%;\">\n<tr><td style=\"background:%%COLOR_SECONDARY%%;padding:24px;text-align:center;border-bottom:3px solid %%COLOR_PRIMARY%%;\"><img src=\"%%LOGO_URL%%\" alt=\"%%COMPANY_NAME%%\" width=\"200\" style=\"max-width:200px;height:auto;\"></td></tr>\n<tr><td style=\"padding:32px;\">\n<p style=\"margin:0 0 8px;font-size:13px;font-weight:700;text-transform:uppercase;color:%%COLOR_PRIMARY%%;\">Ticket %%TICKET_NUMBER%%</p>\n<h1 style=\"margin:0 0 16px;font-size:26px;\">We received your help request</h1>\n<p style=\"margin:0 0 16px;font-size:16px;line-height:1.6;\">Hi %%CUSTOMER_NAME%%, our support team has your ticket and will follow up soon.</p>\n<p style=\"margin:0 0 8px;\"><strong>Service address:</strong> %%SERVICE_ADDRESS%%</p>\n<p style=\"margin:0 0 16px;\"><strong>Issue:</strong><br>%%ISSUE_SUMMARY%%</p>\n<p style=\"margin:0;font-size:15px;line-height:1.6;color:%%COLOR_MUTED%%;\">Reply to this email to add information to your ticket. We typically respond within <strong style=\"color:%%COLOR_PRIMARY%%;\">24 hours</strong> on business days.</p>\n</td></tr>\n<tr><td style=\"background:#fdf7f0;padding:20px 32px;border-top:1px solid %%COLOR_BORDER%%;text-align:center;\">\n<p style=\"margin:0 0 8px;font-size:14px;\">Questions? Call <a href=\"tel:%%PHONE_TEL%%\" style=\"color:%%COLOR_PRIMARY%%;font-weight:700;text-decoration:none;\">%%PHONE%%</a></p>\n<p style=\"margin:0;font-size:11px;color:%%COLOR_MUTED%%;\">%%LICENSES_FOOTER%%</p>\n</td></tr></table></td></tr></table></body></html>",
		"new-ticket-staff" => "<!DOCTYPE html>\n<html lang=\"en\"><head><meta charset=\"UTF-8\"><title>New help ticket</title></head>\n<body style=\"margin:0;padding:0;background:%%COLOR_BACKGROUND%%;font-family:Montserrat,Arial,sans-serif;color:#1c1c1c;\">\n<table role=\"presentation\" width=\"100%\" cellpadding=\"0\" cellspacing=\"0\" style=\"background:%%COLOR_BACKGROUND%%;\"><tr><td align=\"center\" style=\"padding:32px 16px;\">\n<table role=\"presentation\" width=\"600\" cellpadding=\"0\" cellspacing=\"0\" style=\"max-width:600px;background:#fff;border-radius:16px;border:1px solid %%COLOR_BORDER%%;\">\n<tr><td style=\"background:#1c1c1c;padding:20px;text-align:center;\"><img src=\"%%LOGO_URL%%\" alt=\"%%COMPANY_NAME%%\" width=\"180\" style=\"max-width:180px;height:auto;\"></td></tr>\n<tr><td style=\"background:%%COLOR_PRIMARY%%;padding:12px 24px;\"><p style=\"margin:0;font-size:13px;font-weight:700;text-transform:uppercase;color:#fff;\">New Help Desk ticket · %%TICKET_NUMBER%%</p></td></tr>\n<tr><td style=\"padding:28px 32px;\">\n<h1 style=\"margin:0 0 8px;font-size:22px;\">%%CUSTOMER_NAME%%</h1>\n<p style=\"margin:0 0 16px;color:%%COLOR_MUTED%%;\">Submitted %%SUBMISSION_DATE%%</p>\n<p style=\"margin:0 0 8px;\"><strong>Email:</strong> <a href=\"mailto:%%CUSTOMER_EMAIL%%\">%%CUSTOMER_EMAIL%%</a></p>\n<p style=\"margin:0 0 8px;\"><strong>Service address:</strong> %%SERVICE_ADDRESS%%</p>\n<p style=\"margin:0 0 16px;\"><strong>Issue:</strong><br>%%ISSUE_SUMMARY%%</p>\n<p style=\"margin:0;\"><a href=\"%%TICKET_ADMIN_URL%%\" style=\"display:inline-block;background:%%COLOR_PRIMARY%%;color:#fff;text-decoration:none;font-weight:700;padding:12px 24px;border-radius:8px;\">Open in Help Desk</a></p>\n</td></tr></table></td></tr></table></body></html>",
		"staff-reply" => "<!DOCTYPE html>\n<html lang=\"en\"><head><meta charset=\"UTF-8\"><title>Ticket update</title></head>\n<body style=\"margin:0;padding:0;background:%%COLOR_BACKGROUND%%;font-family:Montserrat,Arial,sans-serif;color:#1c1c1c;\">\n<table role=\"presentation\" width=\"100%\" cellpadding=\"0\" cellspacing=\"0\" style=\"background:%%COLOR_BACKGROUND%%;\"><tr><td align=\"center\" style=\"padding:32px 16px;\">\n<table role=\"presentation\" width=\"600\" cellpadding=\"0\" cellspacing=\"0\" style=\"max-width:600px;background:#fff;border-radius:16px;border:1px solid %%COLOR_BORDER%%;\">\n<tr><td style=\"background:%%COLOR_SECONDARY%%;padding:20px;text-align:center;border-bottom:3px solid %%COLOR_PRIMARY%%;\"><img src=\"%%LOGO_URL%%\" alt=\"%%COMPANY_NAME%%\" width=\"180\" style=\"max-width:180px;height:auto;\"></td></tr>\n<tr><td style=\"padding:28px 32px;\">\n<p style=\"margin:0 0 8px;font-size:13px;font-weight:700;text-transform:uppercase;color:%%COLOR_PRIMARY%%;\">Ticket %%TICKET_NUMBER%%</p>\n<h1 style=\"margin:0 0 12px;font-size:22px;\">Reply from %%STAFF_NAME%%</h1>\n<div style=\"font-size:16px;line-height:1.6;color:#1c1c1c;\">%%STAFF_MESSAGE%%</div>\n<p style=\"margin:20px 0 0;font-size:14px;color:%%COLOR_MUTED%%;\">Reply to this email to continue the conversation about your service at %%SERVICE_ADDRESS%%.</p>\n</td></tr>\n<tr><td style=\"background:#fdf7f0;padding:16px 32px;border-top:1px solid %%COLOR_BORDER%%;text-align:center;\">\n<p style=\"margin:0;font-size:11px;color:%%COLOR_MUTED%%;\">%%LICENSES_FOOTER%%</p>\n</td></tr></table></td></tr></table></body></html>",
		"status-closed" => "<!DOCTYPE html>\n<html lang=\"en\"><head><meta charset=\"UTF-8\"><title>Ticket closed</title></head>\n<body style=\"margin:0;padding:0;background:%%COLOR_BACKGROUND%%;font-family:Montserrat,Arial,sans-serif;color:#1c1c1c;\">\n<table role=\"presentation\" width=\"100%\" cellpadding=\"0\" cellspacing=\"0\" style=\"background:%%COLOR_BACKGROUND%%;\"><tr><td align=\"center\" style=\"padding:32px 16px;\">\n<table role=\"presentation\" width=\"600\" cellpadding=\"0\" cellspacing=\"0\" style=\"max-width:600px;background:#fff;border-radius:16px;border:1px solid %%COLOR_BORDER%%;\">\n<tr><td style=\"padding:28px 32px;\">\n<p style=\"margin:0 0 8px;font-size:13px;font-weight:700;text-transform:uppercase;color:%%COLOR_PRIMARY%%;\">Ticket %%TICKET_NUMBER%%</p>\n<h1 style=\"margin:0 0 12px;font-size:22px;\">Your ticket is closed</h1>\n<p style=\"margin:0 0 16px;font-size:16px;line-height:1.6;\">Hi %%CUSTOMER_NAME%%, ticket %%TICKET_NUMBER%% for %%SERVICE_ADDRESS%% has been closed.</p>\n<p style=\"margin:0 0 16px;padding:16px;background:%%COLOR_SECONDARY%%;border-radius:8px;border:1px solid %%COLOR_BORDER%%;\">%%STATUS_NOTE%%</p>\n<p style=\"margin:0;font-size:14px;color:%%COLOR_MUTED%%;\">Need more help? Submit a new ticket at <a href=\"%%SITE_URL%%%%CONTACT_PAGE_URL%%\" style=\"color:%%COLOR_PRIMARY%%;\">%%SITE_URL%%/contact-us</a>.</p>\n</td></tr></table></td></tr></table></body></html>",
		"status-in_progress" => "<!DOCTYPE html>\n<html lang=\"en\"><head><meta charset=\"UTF-8\"><title>Ticket update</title></head>\n<body style=\"margin:0;padding:0;background:%%COLOR_BACKGROUND%%;font-family:Montserrat,Arial,sans-serif;color:#1c1c1c;\">\n<table role=\"presentation\" width=\"100%\" cellpadding=\"0\" cellspacing=\"0\" style=\"background:%%COLOR_BACKGROUND%%;\"><tr><td align=\"center\" style=\"padding:32px 16px;\">\n<table role=\"presentation\" width=\"600\" cellpadding=\"0\" cellspacing=\"0\" style=\"max-width:600px;background:#fff;border-radius:16px;border:1px solid %%COLOR_BORDER%%;\">\n<tr><td style=\"padding:28px 32px;\">\n<p style=\"margin:0 0 8px;font-size:13px;font-weight:700;text-transform:uppercase;color:%%COLOR_PRIMARY%%;\">Ticket %%TICKET_NUMBER%%</p>\n<h1 style=\"margin:0 0 12px;font-size:22px;\">Your ticket is in progress</h1>\n<p style=\"margin:0 0 16px;font-size:16px;line-height:1.6;\">Hi %%CUSTOMER_NAME%%, our team is actively working on your request at %%SERVICE_ADDRESS%%.</p>\n<p style=\"margin:0 0 16px;padding:16px;background:%%COLOR_SECONDARY%%;border-radius:8px;border:1px solid %%COLOR_BORDER%%;\">%%STATUS_NOTE%%</p>\n<p style=\"margin:0;font-size:14px;color:%%COLOR_MUTED%%;\">Reply to this email if you have additional information.</p>\n</td></tr></table></td></tr></table></body></html>",
		"status-open" => "<!DOCTYPE html>\n<html lang=\"en\"><head><meta charset=\"UTF-8\"><title>Ticket open</title></head>\n<body style=\"margin:0;padding:0;background:%%COLOR_BACKGROUND%%;font-family:Montserrat,Arial,sans-serif;color:#1c1c1c;\">\n<table role=\"presentation\" width=\"100%\" cellpadding=\"0\" cellspacing=\"0\" style=\"background:%%COLOR_BACKGROUND%%;\"><tr><td align=\"center\" style=\"padding:32px 16px;\">\n<table role=\"presentation\" width=\"600\" cellpadding=\"0\" cellspacing=\"0\" style=\"max-width:600px;background:#fff;border-radius:16px;border:1px solid %%COLOR_BORDER%%;\">\n<tr><td style=\"padding:28px 32px;\">\n<p style=\"margin:0 0 8px;font-size:13px;font-weight:700;text-transform:uppercase;color:%%COLOR_PRIMARY%%;\">Ticket %%TICKET_NUMBER%%</p>\n<h1 style=\"margin:0 0 12px;font-size:22px;\">Status: %%STATUS_LABEL%%</h1>\n<p style=\"margin:0 0 16px;font-size:16px;line-height:1.6;\">Hi %%CUSTOMER_NAME%%, your help ticket has been updated.</p>\n<p style=\"margin:0 0 16px;padding:16px;background:%%COLOR_SECONDARY%%;border-radius:8px;border:1px solid %%COLOR_BORDER%%;\">%%STATUS_NOTE%%</p>\n</td></tr></table></td></tr></table></body></html>",
		"status-resolved" => "<!DOCTYPE html>\n<html lang=\"en\"><head><meta charset=\"UTF-8\"><title>Ticket resolved</title></head>\n<body style=\"margin:0;padding:0;background:%%COLOR_BACKGROUND%%;font-family:Montserrat,Arial,sans-serif;color:#1c1c1c;\">\n<table role=\"presentation\" width=\"100%\" cellpadding=\"0\" cellspacing=\"0\" style=\"background:%%COLOR_BACKGROUND%%;\"><tr><td align=\"center\" style=\"padding:32px 16px;\">\n<table role=\"presentation\" width=\"600\" cellpadding=\"0\" cellspacing=\"0\" style=\"max-width:600px;background:#fff;border-radius:16px;border:1px solid %%COLOR_BORDER%%;\">\n<tr><td style=\"padding:28px 32px;\">\n<p style=\"margin:0 0 8px;font-size:13px;font-weight:700;text-transform:uppercase;color:%%COLOR_PRIMARY%%;\">Ticket %%TICKET_NUMBER%%</p>\n<h1 style=\"margin:0 0 12px;font-size:22px;\">Your ticket is resolved</h1>\n<p style=\"margin:0 0 16px;font-size:16px;line-height:1.6;\">Hi %%CUSTOMER_NAME%%, we believe your help request has been resolved.</p>\n<p style=\"margin:0 0 16px;padding:16px;background:%%COLOR_SECONDARY%%;border-radius:8px;border:1px solid %%COLOR_BORDER%%;\">%%STATUS_NOTE%%</p>\n<p style=\"margin:0;font-size:14px;color:%%COLOR_MUTED%%;\">If you still need help, reply to this email and we will reopen your ticket.</p>\n</td></tr></table></td></tr></table></body></html>",
		"status-waiting_customer" => "<!DOCTYPE html>\n<html lang=\"en\"><head><meta charset=\"UTF-8\"><title>Ticket update</title></head>\n<body style=\"margin:0;padding:0;background:%%COLOR_BACKGROUND%%;font-family:Montserrat,Arial,sans-serif;color:#1c1c1c;\">\n<table role=\"presentation\" width=\"100%\" cellpadding=\"0\" cellspacing=\"0\" style=\"background:%%COLOR_BACKGROUND%%;\"><tr><td align=\"center\" style=\"padding:32px 16px;\">\n<table role=\"presentation\" width=\"600\" cellpadding=\"0\" cellspacing=\"0\" style=\"max-width:600px;background:#fff;border-radius:16px;border:1px solid %%COLOR_BORDER%%;\">\n<tr><td style=\"padding:28px 32px;\">\n<p style=\"margin:0 0 8px;font-size:13px;font-weight:700;text-transform:uppercase;color:%%COLOR_PRIMARY%%;\">Ticket %%TICKET_NUMBER%%</p>\n<h1 style=\"margin:0 0 12px;font-size:22px;\">Status: %%STATUS_LABEL%%</h1>\n<p style=\"margin:0 0 16px;font-size:16px;line-height:1.6;\">Hi %%CUSTOMER_NAME%%, we need a response from you to continue with your help request.</p>\n<p style=\"margin:0 0 16px;padding:16px;background:%%COLOR_SECONDARY%%;border-radius:8px;border:1px solid %%COLOR_BORDER%%;\">%%STATUS_NOTE%%</p>\n<p style=\"margin:0;font-size:14px;color:%%COLOR_MUTED%%;\">Reply to this email with any details we requested.</p>\n</td></tr></table></td></tr></table></body></html>",
	);
	// STRIKESCOPE_HELP_TICKET_EMAILS_END
}

function strikescope_help_ticket_get_fallback_html($template_key) {
	$template_key = sanitize_key(str_replace('_', '-', $template_key));
	$upload       = wp_upload_dir();
	if (empty($upload['error'])) {
		$base = trailingslashit($upload['basedir']) . 'strikescope-form/emails/help-ticket/';
		$paths = array();
		if (strpos($template_key, 'status-') === 0) {
			$paths[] = $base . 'status/' . substr($template_key, 7) . '.html';
		} else {
			$paths[] = $base . $template_key . '.html';
		}
		foreach ($paths as $path) {
			if (file_exists($path)) {
				$raw = file_get_contents($path);
				if (is_string($raw) && trim($raw) !== '') {
					return $raw;
				}
			}
		}
	}

	$embedded = strikescope_help_ticket_embedded_email_templates();
	if (isset($embedded[ $template_key ]) && is_string($embedded[ $template_key ]) && $embedded[ $template_key ] !== '') {
		return $embedded[ $template_key ];
	}
	return '';
}

function strikescope_help_ticket_template_row($template_key) {
	$ht   = strikescope_help_ticket_get_settings();
	$rows = $ht['email_templates'];
	$key  = sanitize_key(str_replace('_', '-', $template_key));
	if (!isset($rows[ $key ]) || !is_array($rows[ $key ])) {
		return array('enabled' => 1, 'elemailer_id' => 0, 'subject' => '');
	}
	return wp_parse_args($rows[ $key ], array('enabled' => 1, 'elemailer_id' => 0, 'subject' => ''));
}

function strikescope_help_ticket_merge_tags($ticket_id, $extra = array()) {
	$ticket_id = absint($ticket_id);
	$clean     = strikescope_help_ticket_payload_array($ticket_id);
	$brand     = strikescope_form_brand_defaults();
	$number    = get_post_meta($ticket_id, '_ticket_number', true);
	$name      = get_post_meta($ticket_id, '_ticket_customer_name', true);
	$phone     = StrikeScope_Branding::phone_display();
	$phone_tel = preg_replace('/[^\d+]/', '', $phone);
	$catalog   = strikescope_help_ticket_status_catalog();
	$status    = strikescope_help_ticket_get_status($ticket_id);
	$status_l  = isset($catalog[ $status ]['label']) ? $catalog[ $status ]['label'] : ucfirst($status);

	$tags = array(
		'%%TICKET_NUMBER%%'    => esc_html($number),
		'%%TICKET_ID%%'        => esc_html((string) $ticket_id),
		'%%CUSTOMER_NAME%%'    => esc_html($name),
		'%%CUSTOMER_EMAIL%%'   => esc_html($clean['email'] ?? ''),
		'%%ISSUE_SUMMARY%%'    => esc_html($clean['issueDescription'] ?? ''),
		'%%SERVICE_ADDRESS%%'  => esc_html(strikescope_help_ticket_format_address($clean)),
		'%%STATUS_LABEL%%'     => esc_html($status_l),
		'%%STATUS_NOTE%%'      => esc_html($extra['status_note'] ?? ''),
		'%%STAFF_NAME%%'       => esc_html($extra['staff_name'] ?? '%%COMPANY_NAME%% Support'),
		'%%STAFF_MESSAGE%%'    => wp_kses_post($extra['staff_message'] ?? ''),
		'%%TICKET_ADMIN_URL%%' => esc_url(strikescope_help_ticket_admin_url($ticket_id)),
		'%%SUBMISSION_DATE%%'  => esc_html(wp_date(get_option('date_format') . ' ' . get_option('time_format'))),
		'%%LOGO_URL%%'         => esc_url($brand['logo_url']),
		'%%PHONE%%'            => esc_html($phone),
		'%%PHONE_TEL%%'        => esc_attr($phone_tel),
		'%%SITE_URL%%'         => esc_url(home_url('/')),
		'%%LICENSES_FOOTER%%'  => esc_html($brand['licenses_footer']),
	);

	return array_merge($tags, is_array($extra['tags'] ?? null) ? $extra['tags'] : array());
}

function strikescope_help_ticket_apply_merge_tags($html, array $tags) {
	$out = (string) $html;
	foreach ($tags as $token => $value) {
		$out = str_replace($token, $value, $out);
	}
	return $out;
}

function strikescope_help_ticket_build_email_body($template_key, $ticket_id, $extra = array()) {
	$row = strikescope_help_ticket_template_row($template_key);
	if (empty($row['enabled'])) {
		return '';
	}

	$tags          = strikescope_help_ticket_merge_tags($ticket_id, $extra);
	$template_id   = absint($row['elemailer_id']);
	$elemailer_html = strikescope_form_render_eleemailer_template($template_id, $tags);
	if ($elemailer_html !== '') {
		return $elemailer_html;
	}

	$fallback = strikescope_help_ticket_get_fallback_html($template_key);
	if ($fallback === '') {
		return '';
	}
	return strikescope_help_ticket_apply_merge_tags($fallback, $tags);
}

function strikescope_help_ticket_resolve_subject($template_key, $ticket_id, $extra = array()) {
	$row     = strikescope_help_ticket_template_row($template_key);
	$subject = trim((string) ($row['subject'] ?? ''));
	if ($subject === '') {
		$defaults = strikescope_help_ticket_email_subject_defaults();
		$key      = sanitize_key(str_replace('_', '-', $template_key));
		$subject  = isset($defaults[ $key ]) ? $defaults[ $key ] : 'Help Desk update';
	}
	$tags = strikescope_help_ticket_merge_tags($ticket_id, $extra);
	return strikescope_help_ticket_apply_merge_tags($subject, $tags);
}

function strikescope_help_ticket_collect_thread_message_ids($ticket_id) {
	$ids = array();
	foreach (strikescope_help_ticket_get_messages($ticket_id) as $msg) {
		if (!empty($msg['email_message_id'])) {
			$ids[] = $msg['email_message_id'];
		}
	}
	return $ids;
}

function strikescope_help_ticket_send_threaded_mail($ticket_id, $to, $subject, $body, array $message_meta) {
	$ticket_id = absint($ticket_id);
	$ht        = strikescope_help_ticket_get_settings();
	$token     = (string) get_post_meta($ticket_id, '_ticket_reply_token', true);
	$reply_to  = strikescope_help_ticket_reply_address($ticket_id, $token);

	$refs = strikescope_help_ticket_collect_thread_message_ids($ticket_id);
	$in_reply_to = !empty($message_meta['in_reply_to']) ? $message_meta['in_reply_to'] : (end($refs) ?: '');

	$headers = array(
		'Content-Type: text/html; charset=UTF-8',
		'Reply-To: ' . $reply_to,
	);
	if (!empty($message_meta['email_message_id'])) {
		$headers[] = 'Message-ID: ' . $message_meta['email_message_id'];
	}
	if ($in_reply_to !== '') {
		$headers[] = 'In-Reply-To: ' . $in_reply_to;
		$ref_chain = array_merge($refs, array($message_meta['email_message_id'] ?? ''));
		$ref_chain = array_values(array_filter(array_unique($ref_chain)));
		if ($ref_chain) {
			$headers[] = 'References: ' . implode(' ', $ref_chain);
		}
	}

	add_filter(
		'wp_mail_from_name',
		static function () use ($ht) {
			return $ht['from_name'];
		}
	);

	$sent = wp_mail($to, $subject, $body, $headers);
	remove_all_filters('wp_mail_from_name');

	if ($sent) {
		strikescope_help_ticket_append_message($ticket_id, $message_meta);
	}

	return $sent;
}

function strikescope_help_ticket_send_new_ticket_emails($ticket_id) {
	$ticket_id = absint($ticket_id);
	$clean     = strikescope_help_ticket_payload_array($ticket_id);
	$ht        = strikescope_help_ticket_get_settings();

	$customer_body = strikescope_help_ticket_build_email_body('new-ticket-customer', $ticket_id);
	if ($customer_body !== '' && is_email($clean['email'] ?? '')) {
		$msg_id = 'msg_' . wp_generate_password(12, false, false);
		strikescope_help_ticket_send_threaded_mail(
			$ticket_id,
			sanitize_email($clean['email']),
			strikescope_help_ticket_resolve_subject('new-ticket-customer', $ticket_id),
			$customer_body,
			array(
				'id'               => $msg_id,
				'direction'        => 'outbound',
				'author_type'      => 'system',
				'author_name'      => $ht['from_name'],
				'author_user_id'   => 0,
				'subject'          => strikescope_help_ticket_resolve_subject('new-ticket-customer', $ticket_id),
				'body_html'        => $customer_body,
				'body_text'        => wp_strip_all_tags($customer_body),
				'email_message_id' => strikescope_help_ticket_new_message_id($ticket_id, $msg_id),
				'in_reply_to'      => '',
				'created_at'       => time(),
			)
		);
	}

	$staff_body = strikescope_help_ticket_build_email_body('new-ticket-staff', $ticket_id);
	if ($staff_body !== '' && is_email($ht['support_email'])) {
		wp_mail(
			$ht['support_email'],
			strikescope_help_ticket_resolve_subject('new-ticket-staff', $ticket_id),
			$staff_body,
			array('Content-Type: text/html; charset=UTF-8')
		);
	}
}

function strikescope_help_ticket_send_staff_reply($ticket_id, $body_html, $body_text = '', $staff_user_id = 0) {
	$ticket_id = absint($ticket_id);
	$clean     = strikescope_help_ticket_payload_array($ticket_id);
	if (!is_email($clean['email'] ?? '')) {
		return false;
	}

	$user        = $staff_user_id > 0 ? get_userdata($staff_user_id) : wp_get_current_user();
	$staff_name  = $user && $user->exists() ? $user->display_name : '%%COMPANY_NAME%% Support';
	$extra       = array(
		'staff_name'    => $staff_name,
		'staff_message' => $body_html,
	);
	$email_body  = strikescope_help_ticket_build_email_body('staff-reply', $ticket_id, $extra);
	if ($email_body === '') {
		$email_body = wpautop(wp_kses_post($body_html));
	}

	$number  = get_post_meta($ticket_id, '_ticket_number', true);
	$subject = 'Re: Ticket ' . $number;
	$msg_id  = 'msg_' . wp_generate_password(12, false, false);
	$refs    = strikescope_help_ticket_collect_thread_message_ids($ticket_id);
	$last_ref = end($refs) ?: '';

	return strikescope_help_ticket_send_threaded_mail(
		$ticket_id,
		sanitize_email($clean['email']),
		$subject,
		$email_body,
		array(
			'id'               => $msg_id,
			'direction'        => 'outbound',
			'author_type'      => 'staff',
			'author_name'      => $staff_name,
			'author_user_id'   => $staff_user_id > 0 ? $staff_user_id : get_current_user_id(),
			'subject'          => $subject,
			'body_html'        => wpautop(wp_kses_post($body_html)),
			'body_text'        => $body_text !== '' ? $body_text : wp_strip_all_tags($body_html),
			'email_message_id' => strikescope_help_ticket_new_message_id($ticket_id, $msg_id),
			'in_reply_to'      => $last_ref,
			'created_at'       => time(),
		)
	);
}

function strikescope_help_ticket_send_status_email($ticket_id, $status, $status_note = '') {
	$ticket_id = absint($ticket_id);
	$status    = strikescope_help_ticket_normalize_status($status);
	$catalog   = strikescope_help_ticket_status_catalog();
	if (empty($catalog[ $status ]['notify_customer'])) {
		return false;
	}

	$template_key = 'status-' . str_replace('_', '-', $status);
	$row          = strikescope_help_ticket_template_row($template_key);
	if (empty($row['enabled'])) {
		return false;
	}

	$clean = strikescope_help_ticket_payload_array($ticket_id);
	if (!is_email($clean['email'] ?? '')) {
		return false;
	}

	$note = trim((string) $status_note);
	if ($note === '') {
		$note = 'Your ticket status is now: ' . ($catalog[ $status ]['label'] ?? $status) . '.';
	}

	$extra = array('status_note' => $note);
	$body  = strikescope_help_ticket_build_email_body($template_key, $ticket_id, $extra);
	if ($body === '') {
		return false;
	}

	$msg_id = 'msg_' . wp_generate_password(12, false, false);
	$refs   = strikescope_help_ticket_collect_thread_message_ids($ticket_id);
	return strikescope_help_ticket_send_threaded_mail(
		$ticket_id,
		sanitize_email($clean['email']),
		strikescope_help_ticket_resolve_subject($template_key, $ticket_id, $extra),
		$body,
		array(
			'id'               => $msg_id,
			'direction'        => 'outbound',
			'author_type'      => 'system',
			'author_name'      => strikescope_help_ticket_get_settings()['from_name'],
			'author_user_id'   => 0,
			'subject'          => strikescope_help_ticket_resolve_subject($template_key, $ticket_id, $extra),
			'body_html'        => $body,
			'body_text'        => wp_strip_all_tags($body),
			'email_message_id' => strikescope_help_ticket_new_message_id($ticket_id, $msg_id),
			'in_reply_to'      => end($refs) ?: '',
			'created_at'       => time(),
		)
	);
}

function strikescope_help_ticket_notify_staff_inbound($ticket_id) {
	$ticket_id = absint($ticket_id);
	$ht        = strikescope_help_ticket_get_settings();
	$number    = get_post_meta($ticket_id, '_ticket_number', true);
	$name      = get_post_meta($ticket_id, '_ticket_customer_name', true);
	if (!is_email($ht['support_email'])) {
		return;
	}
	$subject = 'Customer reply on ticket ' . $number;
	$body    = '<p><strong>' . esc_html($name) . '</strong> replied to ticket <strong>' . esc_html($number) . '</strong>.</p>'
		. '<p><a href="' . esc_url(strikescope_help_ticket_admin_url($ticket_id)) . '">Open in Help Desk</a></p>';
	wp_mail($ht['support_email'], $subject, $body, array('Content-Type: text/html; charset=UTF-8'));
}

function strikescope_help_ticket_parse_plus_address($address) {
	$address = strtolower(trim((string) $address));
	if (preg_match('/ticket\+(\d+)\+([a-f0-9]+)@/', $address, $m)) {
		return array('ticket_id' => (int) $m[1], 'token' => $m[2]);
	}
	return null;
}

function strikescope_help_ticket_find_by_message_id($message_id) {
	$message_id = trim((string) $message_id);
	if ($message_id === '') {
		return 0;
	}
	$q = new WP_Query(
		array(
			'post_type'      => STRIKESCOPE_HELP_TICKET_CPT,
			'post_status'    => 'publish',
			'posts_per_page' => 50,
			'fields'         => 'ids',
			'orderby'        => 'ID',
			'order'          => 'DESC',
		)
	);
	foreach ($q->posts as $post_id) {
		foreach (strikescope_help_ticket_get_messages((int) $post_id) as $msg) {
			if (!empty($msg['email_message_id']) && stripos($msg['email_message_id'], $message_id) !== false) {
				return (int) $post_id;
			}
			if (!empty($msg['email_message_id']) && stripos($message_id, trim($msg['email_message_id'], '<>')) !== false) {
				return (int) $post_id;
			}
		}
	}
	return 0;
}

function strikescope_help_ticket_match_ticket_from_addresses(array $addresses) {
	foreach ($addresses as $address) {
		$parsed = strikescope_help_ticket_parse_plus_address($address);
		if (!$parsed) {
			continue;
		}
		$post = get_post($parsed['ticket_id']);
		if (!$post || $post->post_type !== STRIKESCOPE_HELP_TICKET_CPT) {
			continue;
		}
		$stored = (string) get_post_meta($parsed['ticket_id'], '_ticket_reply_token', true);
		if ($stored !== '' && hash_equals($stored, $parsed['token'])) {
			return (int) $parsed['ticket_id'];
		}
	}
	return 0;
}

function strikescope_help_ticket_normalize_inbound_payload($request) {
	$params = $request->get_json_params();
	if (!is_array($params)) {
		$params = $request->get_body_params();
	}
	if (!is_array($params)) {
		$params = array();
	}

	if (isset($params['FromFull'], $params['TextBody'])) {
		$to_list = array();
		if (!empty($params['ToFull']) && is_array($params['ToFull'])) {
			foreach ($params['ToFull'] as $row) {
				if (!empty($row['Email'])) {
					$to_list[] = strtolower($row['Email']);
				}
			}
		}
		if (!empty($params['OriginalRecipient'])) {
			$to_list[] = strtolower((string) $params['OriginalRecipient']);
		}
		return array(
			'from'         => isset($params['FromFull']['Email']) ? (string) $params['FromFull']['Email'] : (string) ($params['From'] ?? ''),
			'from_name'    => isset($params['FromFull']['Name']) ? (string) $params['FromFull']['Name'] : '',
			'to'           => $to_list,
			'subject'      => (string) ($params['Subject'] ?? ''),
			'text'         => (string) ($params['TextBody'] ?? ''),
			'html'         => (string) ($params['HtmlBody'] ?? ''),
			'message_id'   => (string) ($params['MessageID'] ?? ''),
			'in_reply_to'  => (string) ($params['Headers'] ?? ''),
			'provider'     => 'postmark',
		);
	}

	if (isset($params['from'], $params['subject'])) {
		$to = $params['to'] ?? '';
		if (is_array($to)) {
			$to_list = array_map('strtolower', $to);
		} else {
			$to_list = array(strtolower((string) $to));
		}
		return array(
			'from'        => (string) $params['from'],
			'from_name'   => (string) ($params['from_name'] ?? ''),
			'to'          => $to_list,
			'subject'     => (string) $params['subject'],
			'text'        => (string) ($params['text'] ?? ''),
			'html'        => (string) ($params['html'] ?? ''),
			'message_id'  => (string) ($params['message_id'] ?? ''),
			'in_reply_to' => (string) ($params['in_reply_to'] ?? ''),
			'provider'    => 'generic',
		);
	}

	return null;
}

function strikescope_help_ticket_process_inbound(array $payload) {
	$ticket_id = strikescope_help_ticket_match_ticket_from_addresses($payload['to'] ?? array());
	if ($ticket_id <= 0 && !empty($payload['in_reply_to'])) {
		$ticket_id = strikescope_help_ticket_find_by_message_id($payload['in_reply_to']);
	}
	if ($ticket_id <= 0) {
		return new WP_Error('ticket_not_found', 'No matching ticket.', array('status' => 404));
	}

	$status = strikescope_help_ticket_get_status($ticket_id);
	if (in_array($status, array('closed', 'resolved'), true)) {
		strikescope_help_ticket_set_status($ticket_id, 'open');
	}

	$body_text = trim((string) ($payload['text'] ?? ''));
	$body_html = trim((string) ($payload['html'] ?? ''));
	if ($body_text === '' && $body_html !== '') {
		$body_text = wp_strip_all_tags($body_html);
	}
	if ($body_html === '' && $body_text !== '') {
		$body_html = wpautop(esc_html($body_text));
	}

	$from_email = sanitize_email($payload['from'] ?? '');
	$expected   = sanitize_email(get_post_meta($ticket_id, '_ticket_email', true));
	if ($expected !== '' && $from_email !== '' && !hash_equals($expected, $from_email)) {
		return new WP_Error('sender_mismatch', 'Sender does not match ticket customer.', array('status' => 403));
	}

	$msg_id = 'msg_' . wp_generate_password(12, false, false);
	strikescope_help_ticket_append_message(
		$ticket_id,
		array(
			'id'               => $msg_id,
			'direction'        => 'inbound',
			'author_type'      => 'customer',
			'author_name'      => sanitize_text_field($payload['from_name'] ?? '') ?: get_post_meta($ticket_id, '_ticket_customer_name', true),
			'author_user_id'   => 0,
			'subject'          => sanitize_text_field($payload['subject'] ?? ''),
			'body_html'        => wp_kses_post($body_html),
			'body_text'        => sanitize_textarea_field($body_text),
			'email_message_id' => !empty($payload['message_id']) ? sanitize_text_field($payload['message_id']) : strikescope_help_ticket_new_message_id($ticket_id, $msg_id),
			'in_reply_to'      => sanitize_text_field($payload['in_reply_to'] ?? ''),
			'created_at'       => time(),
		)
	);

	strikescope_help_ticket_set_status($ticket_id, 'waiting_staff');
	strikescope_help_ticket_notify_staff_inbound($ticket_id);

	return array('success' => true, 'ticket_id' => $ticket_id);
}

function strikescope_help_ticket_send_test_email($to_email, $template_key) {
	if (!is_email($to_email)) {
		return new WP_Error('invalid_email', 'Enter a valid test email address.', array('status' => 400));
	}

	$sample_id = 0;
	$q         = new WP_Query(
		array(
			'post_type'      => STRIKESCOPE_HELP_TICKET_CPT,
			'post_status'    => 'publish',
			'posts_per_page' => 1,
			'fields'         => 'ids',
			'orderby'        => 'ID',
			'order'          => 'DESC',
		)
	);
	if (!empty($q->posts[0])) {
		$sample_id = (int) $q->posts[0];
	}

	$extra = array(
		'status_note'   => 'Sample status note for template testing.',
		'staff_name'    => '%%COMPANY_NAME%% Support',
		'staff_message' => '<p>This is a sample staff reply for template testing.</p>',
	);

	if ($sample_id <= 0) {
		$tags = array(
			'%%TICKET_NUMBER%%'    => 'HT-SAMPLE-0001',
			'%%TICKET_ID%%'        => '0',
			'%%CUSTOMER_NAME%%'    => 'Alex Sample',
			'%%CUSTOMER_EMAIL%%'   => esc_html($to_email),
			'%%ISSUE_SUMMARY%%'    => 'Sample issue description for testing.',
			'%%SERVICE_ADDRESS%%'  => '123 Sample St, Springfield, IL 62704',
			'%%STATUS_LABEL%%'     => 'In progress',
			'%%STATUS_NOTE%%'      => esc_html($extra['status_note']),
			'%%STAFF_NAME%%'       => esc_html($extra['staff_name']),
			'%%STAFF_MESSAGE%%'    => wp_kses_post($extra['staff_message']),
			'%%TICKET_ADMIN_URL%%' => esc_url(admin_url('admin.php?page=strikescope-help-ticket-detail&ticket_id=0')),
			'%%SUBMISSION_DATE%%'  => esc_html(wp_date(get_option('date_format') . ' ' . get_option('time_format'))),
			'%%LOGO_URL%%'         => esc_url(strikescope_form_brand_defaults()['logo_url']),
			'%%PHONE%%'            => esc_html(StrikeScope_Branding::phone_display()),
			'%%PHONE_TEL%%'        => esc_attr(preg_replace('/[^\d+]/', '', StrikeScope_Branding::phone_display())),
			'%%SITE_URL%%'         => esc_url(home_url('/')),
			'%%LICENSES_FOOTER%%'  => esc_html(strikescope_form_brand_defaults()['licenses_footer']),
		);
		$body = strikescope_help_ticket_get_fallback_html($template_key);
		if ($body === '') {
			return new WP_Error('empty_body', 'No template body found.', array('status' => 400));
		}
		$body    = strikescope_help_ticket_apply_merge_tags($body, $tags);
		$subject = strikescope_help_ticket_apply_merge_tags(
			strikescope_help_ticket_email_subject_defaults()[ sanitize_key(str_replace('_', '-', $template_key)) ] ?? 'Test',
			$tags
		);
	} else {
		$body    = strikescope_help_ticket_build_email_body($template_key, $sample_id, $extra);
		$subject = strikescope_help_ticket_resolve_subject($template_key, $sample_id, $extra);
	}

	if ($body === '') {
		return new WP_Error('empty_body', 'Template is disabled or empty.', array('status' => 400));
	}

	$sent = wp_mail($to_email, $subject, $body, array('Content-Type: text/html; charset=UTF-8'));
	if (!$sent) {
		return new WP_Error('send_failed', 'Unable to send test email.', array('status' => 500));
	}

	return array(
		'success'      => true,
		'message'      => 'Test email sent to ' . sanitize_email($to_email) . ' (' . sanitize_key($template_key) . ').',
		'template_key' => sanitize_key(str_replace('_', '-', $template_key)),
	);
}

add_action('rest_api_init', static function () {
	register_rest_route(
		'strikescope/v1',
		'/help-ticket/inbound',
		array(
			'methods'             => 'POST',
			'permission_callback' => static function ($request) {
				strikescope_help_ticket_ensure_inbound_secret();
				$ht     = strikescope_help_ticket_get_settings();
				$secret = (string) $ht['inbound_secret'];
				$header = $request->get_header('x-strikescope-inbound-secret');
				if ($secret === '' || $header === '') {
					return false;
				}
				return hash_equals($secret, trim((string) $header));
			},
			'callback'            => static function ($request) {
				$payload = strikescope_help_ticket_normalize_inbound_payload($request);
				if ($payload === null) {
					return new WP_Error('invalid_payload', 'Unrecognized inbound payload.', array('status' => 400));
				}
				$result = strikescope_help_ticket_process_inbound($payload);
				if (is_wp_error($result)) {
					return $result;
				}
				return $result;
			},
		)
	);

	register_rest_route(
		'strikescope/v1',
		'/help-ticket/email/test',
		array(
			'methods'             => 'POST',
			'permission_callback' => static function () {
				return current_user_can('manage_options');
			},
			'callback'            => static function ($request) {
				$params = $request->get_json_params();
				$params = is_array($params) ? $params : array();
				$to     = isset($params['email']) ? sanitize_email((string) $params['email']) : '';
				$key    = isset($params['template']) ? sanitize_key(str_replace('_', '-', (string) $params['template'])) : 'new-ticket-customer';
				if ($to === '' || !is_email($to)) {
					$to = wp_get_current_user()->user_email;
				}
				$result = strikescope_help_ticket_send_test_email($to, $key);
				if (is_wp_error($result)) {
					return $result;
				}
				return $result;
			},
		)
	);
});