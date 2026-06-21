/**
 * Help Desk admin UI: list, detail thread, AJAX, settings panel.
 */

function strikescope_help_ticket_admin_screen_ids() {
	return array(
		'edit-' . STRIKESCOPE_HELP_TICKET_CPT,
		'admin_page_strikescope-help-ticket-detail',
	);
}

add_filter('manage_' . STRIKESCOPE_HELP_TICKET_CPT . '_posts_columns', static function ($columns) {
	$new = array();
	foreach ($columns as $key => $label) {
		if ($key === 'title') {
			$new['title'] = 'Ticket';
		} else {
			$new[ $key ] = $label;
		}
	}
	$insert = array(
		'ticket_number' => 'Ticket #',
		'customer'      => 'Customer',
		'email'         => 'Email',
		'ticket_status' => 'Status',
		'location'      => 'Service address',
		'last_activity' => 'Last activity',
	);
	$out = array();
	foreach ($new as $key => $label) {
		$out[ $key ] = $label;
		if ($key === 'title') {
			$out = array_merge($out, $insert);
		}
	}
	return $out;
});

add_action('manage_' . STRIKESCOPE_HELP_TICKET_CPT . '_posts_custom_column', static function ($column, $post_id) {
	switch ($column) {
		case 'ticket_number':
			echo esc_html(get_post_meta($post_id, '_ticket_number', true));
			break;
		case 'customer':
			echo esc_html(get_post_meta($post_id, '_ticket_customer_name', true));
			break;
		case 'email':
			$email = get_post_meta($post_id, '_ticket_email', true);
			echo $email ? '<a href="mailto:' . esc_attr($email) . '">' . esc_html($email) . '</a>' : '';
			break;
		case 'ticket_status':
			echo strikescope_help_ticket_status_badge_html(strikescope_help_ticket_get_status($post_id));
			break;
		case 'location':
			$city  = get_post_meta($post_id, '_ticket_city', true);
			$state = get_post_meta($post_id, '_ticket_state', true);
			echo esc_html(trim($city . ', ' . $state, ', '));
			break;
		case 'last_activity':
			$ts = get_post_meta($post_id, '_ticket_last_activity_at', true);
			if ($ts === '') {
				$ts = get_post_meta($post_id, '_ticket_submitted_at', true);
			}
			echo $ts !== '' ? esc_html(wp_date(get_option('date_format') . ' ' . get_option('time_format'), (int) $ts)) : '—';
			break;
	}
}, 10, 2);

add_filter('post_row_actions', static function ($actions, $post) {
	if (!$post || $post->post_type !== STRIKESCOPE_HELP_TICKET_CPT) {
		return $actions;
	}
	$actions['view_ticket'] = '<a href="' . esc_url(strikescope_help_ticket_admin_url($post->ID)) . '">View ticket</a>';
	return $actions;
}, 10, 2);

add_filter('post_type_link', static function ($link, $post) {
	if ($post && $post->post_type === STRIKESCOPE_HELP_TICKET_CPT) {
		return strikescope_help_ticket_admin_url($post->ID);
	}
	return $link;
}, 10, 2);

add_filter('the_title', static function ($title, $post_id) {
	if (!is_admin() || !function_exists('get_current_screen')) {
		return $title;
	}
	$screen = get_current_screen();
	if (!$screen || $screen->post_type !== STRIKESCOPE_HELP_TICKET_CPT || $screen->base !== 'edit') {
		return $title;
	}
	$number = get_post_meta($post_id, '_ticket_number', true);
	$name   = get_post_meta($post_id, '_ticket_customer_name', true);
	if ($number !== '') {
		return $number . ' — ' . $name;
	}
	return $title;
}, 10, 2);

add_action('admin_menu', static function () {
	add_submenu_page(
		null,
		'Help Ticket',
		'Help Ticket',
		'edit_posts',
		'strikescope-help-ticket-detail',
		'strikescope_help_ticket_render_detail_page'
	);
});

function strikescope_help_ticket_render_detail_page() {
	if (!strikescope_help_ticket_user_can_manage()) {
		wp_die(esc_html__('You do not have permission to view this ticket.', 'default'));
	}
	$ticket_id = isset($_GET['ticket_id']) ? absint($_GET['ticket_id']) : 0;
	$post      = get_post($ticket_id);
	if (!$post || $post->post_type !== STRIKESCOPE_HELP_TICKET_CPT) {
		wp_die(esc_html__('Ticket not found.', 'default'));
	}
	?>
	<div class="wrap">
		<h1><?php echo esc_html(get_post_meta($ticket_id, '_ticket_number', true)); ?></h1>
		<p><a href="<?php echo esc_url(admin_url('edit.php?post_type=' . STRIKESCOPE_HELP_TICKET_CPT)); ?>">&larr; Back to Help Desk</a></p>
		<?php strikescope_help_ticket_render_record($ticket_id); ?>
	</div>
	<?php
}

function strikescope_help_ticket_render_record($ticket_id) {
	$ticket_id = absint($ticket_id);
	$clean     = strikescope_help_ticket_payload_array($ticket_id);
	$status    = strikescope_help_ticket_get_status($ticket_id);
	$number    = get_post_meta($ticket_id, '_ticket_number', true);
	$address   = strikescope_help_ticket_format_address($clean);
	$submitted = get_post_meta($ticket_id, '_ticket_submitted_at', true);
	$maps      = function_exists('strikescope_form_google_maps_url') ? strikescope_form_google_maps_url($clean) : '';
	$messages  = strikescope_help_ticket_get_messages($ticket_id);
	?>
	<div class="strikescope-help-ticket-record" style="max-width:1200px;">
		<div style="display:flex;flex-wrap:wrap;gap:12px;align-items:center;margin-bottom:20px;">
			<?php echo strikescope_help_ticket_status_badge_html($status); ?>
			<span class="description">Ticket #<?php echo esc_html($number); ?></span>
			<?php if ($submitted) : ?>
				<span class="description">Opened <?php echo esc_html(wp_date(get_option('date_format') . ' ' . get_option('time_format'), (int) $submitted)); ?></span>
			<?php endif; ?>
		</div>

		<div style="display:grid;grid-template-columns:1fr 1fr;gap:24px;margin-bottom:24px;">
			<div class="postbox" style="padding:16px;">
				<h2 style="margin-top:0;">Ticket details</h2>
				<table class="widefat striped" style="border:0;box-shadow:none;">
					<tbody>
						<tr><th style="width:140px;">Customer</th><td><?php echo esc_html(get_post_meta($ticket_id, '_ticket_customer_name', true)); ?></td></tr>
						<tr><th>Email</th><td><a href="mailto:<?php echo esc_attr($clean['email'] ?? ''); ?>"><?php echo esc_html($clean['email'] ?? ''); ?></a></td></tr>
						<tr><th>Phone</th><td><a href="tel:<?php echo esc_attr(preg_replace('/[^\d+]/', '', $clean['phone'] ?? '')); ?>"><?php echo esc_html($clean['phone'] ?? ''); ?></a></td></tr>
						<tr><th>Service address</th><td><?php echo esc_html($address); ?></td></tr>
						<tr><th>Status</th><td><?php echo strikescope_help_ticket_status_select_html($ticket_id, 'strikescope-ticket-status-select widefat'); ?></td></tr>
						<tr><th>Status note</th><td><textarea class="strikescope-ticket-status-note widefat" rows="3" placeholder="Optional note included in customer status email"></textarea></td></tr>
						<tr><th>Notify customer</th><td><label><input type="checkbox" class="strikescope-ticket-status-notify" checked> Send status update email</label></td></tr>
						<tr><th></th><td><button type="button" class="button button-primary strikescope-ticket-save-status-btn" data-ticket-id="<?php echo esc_attr((string) $ticket_id); ?>">Update status</button></td></tr>
					</tbody>
				</table>
			</div>
			<div class="postbox" style="padding:16px;">
				<h2 style="margin-top:0;">Quick actions</h2>
				<p>
					<?php if ($maps) : ?><a class="button button-primary" href="<?php echo esc_url($maps); ?>" target="_blank" rel="noopener">Google Maps</a><?php endif; ?>
					<a class="button" href="mailto:<?php echo esc_attr($clean['email'] ?? ''); ?>">Email customer</a>
				</p>
				<p class="description"><strong>Reply address:</strong><br><code><?php echo esc_html(strikescope_help_ticket_reply_address($ticket_id)); ?></code></p>
			</div>
		</div>

		<div class="postbox" style="padding:16px;margin-bottom:24px;">
			<h2 style="margin-top:0;">Conversation</h2>
			<div class="strikescope-ticket-thread" style="display:flex;flex-direction:column;gap:12px;max-height:520px;overflow-y:auto;margin-bottom:16px;">
				<?php foreach ($messages as $msg) :
					$is_staff = ($msg['direction'] ?? '') === 'outbound';
					$align    = $is_staff ? 'flex-end' : 'flex-start';
					$bg       = $is_staff ? '%%COLOR_SECONDARY%%' : '#f0f6fc';
					?>
					<div style="display:flex;justify-content:<?php echo esc_attr($align); ?>;">
						<div style="max-width:75%;padding:12px 16px;border-radius:12px;background:<?php echo esc_attr($bg); ?>;border:1px solid #ddd;">
							<p style="margin:0 0 6px;font-size:12px;font-weight:600;color:#646970;">
								<?php echo esc_html($msg['author_name'] ?? 'Unknown'); ?>
								· <?php echo esc_html(!empty($msg['created_at']) ? wp_date(get_option('date_format') . ' ' . get_option('time_format'), (int) $msg['created_at']) : ''); ?>
								· <?php echo esc_html(ucfirst($msg['direction'] ?? '')); ?>
							</p>
							<div style="font-size:14px;line-height:1.5;"><?php echo wp_kses_post($msg['body_html'] ?? wpautop(esc_html($msg['body_text'] ?? ''))); ?></div>
						</div>
					</div>
				<?php endforeach; ?>
				<?php if (!$messages) : ?>
					<p class="description">No messages yet.</p>
				<?php endif; ?>
			</div>

			<h3>Reply to customer</h3>
			<textarea class="strikescope-ticket-reply-body widefat" rows="6" placeholder="Type your reply…"></textarea>
			<p style="margin-top:12px;">
				<button type="button" class="button button-primary strikescope-ticket-send-reply-btn" data-ticket-id="<?php echo esc_attr((string) $ticket_id); ?>">Send reply</button>
				<span class="strikescope-ticket-reply-status description" style="margin-left:8px;"></span>
			</p>
		</div>
	</div>
	<?php
}

function strikescope_help_ticket_render_settings_section($show_heading = true, $show_enable_toggle = true) {
	strikescope_help_ticket_ensure_inbound_secret();
	$ht           = strikescope_help_ticket_get_settings();
	$settings     = strikescope_form_get_settings();
	$option       = STRIKESCOPE_SETTINGS_OPTION;
	$inbound_url  = esc_url_raw(rest_url('strikescope/v1/help-ticket/inbound'));
	$template_keys = strikescope_help_ticket_email_template_keys();
	$labels = array(
		'new-ticket-customer'     => 'New ticket — customer',
		'new-ticket-staff'        => 'New ticket — staff alert',
		'staff-reply'             => 'Staff reply wrapper',
		'status-open'             => 'Status: Open',
		'status-waiting_customer' => 'Status: Waiting on customer',
		'status-waiting_staff'    => 'Status: Waiting on staff',
		'status-in_progress'      => 'Status: In progress',
		'status-resolved'         => 'Status: Resolved',
		'status-closed'           => 'Status: Closed',
	);
	?>
	<?php if ($show_heading) : ?>
		<h2 class="title">Help Desk</h2>
	<?php endif; ?>
	<p class="description">Existing customer help tickets with email threading. Customers select <strong>Existing Customer</strong> on the contact form.</p>
	<table class="form-table" role="presentation">
		<?php if ($show_enable_toggle) : ?>
		<tr>
			<th scope="row">Enable Help Desk</th>
			<td>
				<label>
					<input type="checkbox" name="<?php echo esc_attr($option); ?>[help_desk_enabled]" value="1" <?php checked(!empty($settings['help_desk_enabled'])); ?>>
					Accept help ticket submissions from the contact form
				</label>
			</td>
		</tr>
		<?php endif; ?>
		<tr>
			<th scope="row"><label for="help_desk_support_email">Support inbox</label></th>
			<td>
				<input type="email" class="regular-text" id="help_desk_support_email" name="<?php echo esc_attr($option); ?>[help_desk_support_email]" value="<?php echo esc_attr($settings['help_desk_support_email'] ?? ''); ?>" placeholder="<?php echo esc_attr($settings['primary_email']); ?>">
				<p class="description">Receives new ticket alerts and customer reply notifications.</p>
			</td>
		</tr>
		<tr>
			<th scope="row"><label for="help_desk_from_name">From name</label></th>
			<td><input type="text" class="regular-text" id="help_desk_from_name" name="<?php echo esc_attr($option); ?>[help_desk_from_name]" value="<?php echo esc_attr($settings['help_desk_from_name'] ?? 'Help Desk'); ?>"></td>
		</tr>
		<tr>
			<th scope="row"><label for="help_desk_inbound_domain">Inbound reply domain</label></th>
			<td>
				<input type="text" class="regular-text" id="help_desk_inbound_domain" name="<?php echo esc_attr($option); ?>[help_desk_inbound_domain]" value="<?php echo esc_attr($settings['help_desk_inbound_domain'] ?? 'inbound.%%SITE_URL%%'); ?>">
				<p class="description">Used for Reply-To addresses: <code>ticket+{id}+{token}@your-domain</code></p>
			</td>
		</tr>
	</table>

	<h3 class="title">Inbound email (Postmark recommended)</h3>
	<ol class="description" style="max-width:900px;margin-left:1.5em;">
		<li>Add DNS MX for your inbound domain pointing to your provider (Postmark: <code>inbound.postmarkapp.com</code> priority 10).</li>
		<li>In Postmark → <strong>Servers → Inbound</strong>, set webhook URL:<br><code><?php echo esc_html($inbound_url); ?></code></li>
		<li>Add custom header on the webhook (or use Postmark HTTP auth): <code>X-StrikeScope-Inbound-Secret: <?php echo esc_html($ht['inbound_secret']); ?></code></li>
		<li>Configure your outbound mail (WP Mail SMTP / Postmark) so Reply-To plus-addresses route to the inbound domain.</li>
		<li>Send a test reply to an open ticket’s Reply-To address and confirm the message appears in Help Desk.</li>
	</ol>
	<p class="description"><strong>Inbound secret</strong> (read-only — regenerate by clearing option in DB if compromised): <code><?php echo esc_html($ht['inbound_secret']); ?></code></p>

	<h3 class="title">Help Desk email templates</h3>
	<p class="description">Merge tags: <code>%%TICKET_NUMBER%%</code>, <code>%%CUSTOMER_NAME%%</code>, <code>%%ISSUE_SUMMARY%%</code>, <code>%%SERVICE_ADDRESS%%</code>, <code>%%STATUS_LABEL%%</code>, <code>%%STATUS_NOTE%%</code>, <code>%%STAFF_MESSAGE%%</code>, <code>%%TICKET_ADMIN_URL%%</code>, <code>%%LOGO_URL%%</code>, <code>%%PHONE%%</code></p>
	<table class="widefat striped" style="max-width:1100px;margin-bottom:16px;">
		<thead>
			<tr>
				<th>Template</th>
				<th>Enabled</th>
				<th>Elemailer ID</th>
				<th>Subject</th>
				<th>Test</th>
			</tr>
		</thead>
		<tbody>
			<?php foreach ($template_keys as $key) :
				$row = strikescope_help_ticket_template_row($key);
				$label = isset($labels[ $key ]) ? $labels[ $key ] : $key;
				$defaults = strikescope_help_ticket_email_subject_defaults();
				?>
				<tr>
					<td><strong><?php echo esc_html($label); ?></strong><br><code><?php echo esc_html($key); ?></code></td>
					<td><input type="checkbox" name="<?php echo esc_attr($option); ?>[help_desk_email_templates][<?php echo esc_attr($key); ?>][enabled]" value="1" <?php checked(!empty($row['enabled'])); ?>></td>
					<td><input type="number" min="0" class="small-text" name="<?php echo esc_attr($option); ?>[help_desk_email_templates][<?php echo esc_attr($key); ?>][elemailer_id]" value="<?php echo esc_attr((string) absint($row['elemailer_id'])); ?>"></td>
					<td><input type="text" class="regular-text" name="<?php echo esc_attr($option); ?>[help_desk_email_templates][<?php echo esc_attr($key); ?>][subject]" value="<?php echo esc_attr($row['subject']); ?>" placeholder="<?php echo esc_attr($defaults[ $key ] ?? ''); ?>"></td>
					<td><button type="button" class="button strikescope-test-help-ticket-email" data-template="<?php echo esc_attr($key); ?>">Test</button></td>
				</tr>
			<?php endforeach; ?>
		</tbody>
	</table>
	<p>
		<label for="strikescope_help_ticket_test_email">Test inbox:</label>
		<input id="strikescope_help_ticket_test_email" type="email" class="regular-text" value="<?php echo esc_attr(wp_get_current_user()->user_email); ?>">
		<span id="strikescope_help_ticket_test_status" style="margin-left:8px;"></span>
	</p>
	<?php
}

add_action('admin_enqueue_scripts', static function ($hook) {
	if (!in_array($hook, strikescope_help_ticket_admin_screen_ids(), true)) {
		$strikescope_hooks = function_exists('strikescope_admin_screen_hooks') ? strikescope_admin_screen_hooks() : array();
		if (!in_array($hook, $strikescope_hooks, true)) {
			return;
		}
	}
	wp_enqueue_script('jquery');
	add_action('admin_footer', 'strikescope_help_ticket_print_admin_script');
});

function strikescope_help_ticket_print_admin_script() {
	if (!strikescope_help_ticket_user_can_manage()) {
		return;
	}
	$nonce = wp_create_nonce('strikescope_help_ticket_admin');
	$test_url = esc_url_raw(rest_url('strikescope/v1/help-ticket/email/test'));
	?>
	<script>
	(function($){
		var nonce = <?php echo wp_json_encode($nonce); ?>;
		var testUrl = <?php echo wp_json_encode($test_url); ?>;

		$(document).on('click', '.strikescope-ticket-save-status-btn', function(){
			var ticketId = $(this).data('ticket-id');
			var status = $('.strikescope-ticket-status-select').val();
			var note = $('.strikescope-ticket-status-note').val();
			var notify = $('.strikescope-ticket-status-notify').is(':checked') ? 1 : 0;
			$.post(ajaxurl, {
				action: 'strikescope_help_ticket_set_status',
				nonce: nonce,
				ticket_id: ticketId,
				status: status,
				status_note: note,
				send_email: notify
			}).done(function(resp){
				if (resp && resp.success) { location.reload(); }
				else { alert((resp && resp.data) ? resp.data : 'Update failed'); }
			});
		});

		$(document).on('click', '.strikescope-ticket-send-reply-btn', function(){
			var btn = $(this);
			var ticketId = btn.data('ticket-id');
			var body = $('.strikescope-ticket-reply-body').val();
			if (!body || !body.trim()) { alert('Enter a reply message.'); return; }
			btn.prop('disabled', true);
			$.post(ajaxurl, {
				action: 'strikescope_help_ticket_send_reply',
				nonce: nonce,
				ticket_id: ticketId,
				body: body
			}).done(function(resp){
				btn.prop('disabled', false);
				if (resp && resp.success) { location.reload(); }
				else { $('.strikescope-ticket-reply-status').text((resp && resp.data) ? resp.data : 'Send failed'); }
			}).fail(function(){ btn.prop('disabled', false); });
		});

		$(document).on('click', '.strikescope-test-help-ticket-email', function(){
			var template = $(this).data('template');
			var email = $('#strikescope_help_ticket_test_email').val();
			$('#strikescope_help_ticket_test_status').text('Sending…');
			fetch(testUrl, {
				method: 'POST',
				headers: { 'Content-Type': 'application/json', 'X-WP-Nonce': <?php echo wp_json_encode(wp_create_nonce('wp_rest')); ?> },
				credentials: 'same-origin',
				body: JSON.stringify({ email: email, template: template })
			}).then(function(r){ return r.json(); }).then(function(data){
				if (data.message) { $('#strikescope_help_ticket_test_status').text(data.message); }
				else if (data.message && data.code) { $('#strikescope_help_ticket_test_status').text(data.message); }
				else { $('#strikescope_help_ticket_test_status').text('Sent or check mail logs.'); }
			}).catch(function(){ $('#strikescope_help_ticket_test_status').text('Request failed'); });
		});
	})(jQuery);
	</script>
	<?php
}

add_action('wp_ajax_strikescope_help_ticket_set_status', static function () {
	check_ajax_referer('strikescope_help_ticket_admin', 'nonce');
	if (!strikescope_help_ticket_user_can_manage()) {
		wp_send_json_error('Permission denied');
	}
	$ticket_id   = isset($_POST['ticket_id']) ? absint($_POST['ticket_id']) : 0;
	$status      = isset($_POST['status']) ? sanitize_key((string) $_POST['status']) : '';
	$status_note = isset($_POST['status_note']) ? sanitize_textarea_field((string) $_POST['status_note']) : '';
	$send_email  = !empty($_POST['send_email']);
	if ($ticket_id <= 0) {
		wp_send_json_error('Invalid ticket');
	}
	strikescope_help_ticket_set_status($ticket_id, $status, $send_email, $status_note);
	wp_send_json_success(array('ticket_id' => $ticket_id, 'status' => $status));
});

add_action('wp_ajax_strikescope_help_ticket_send_reply', static function () {
	check_ajax_referer('strikescope_help_ticket_admin', 'nonce');
	if (!strikescope_help_ticket_user_can_manage()) {
		wp_send_json_error('Permission denied');
	}
	$ticket_id = isset($_POST['ticket_id']) ? absint($_POST['ticket_id']) : 0;
	$body      = isset($_POST['body']) ? wp_kses_post((string) $_POST['body']) : '';
	if ($ticket_id <= 0 || trim(wp_strip_all_tags($body)) === '') {
		wp_send_json_error('Invalid reply');
	}
	$sent = strikescope_help_ticket_send_staff_reply($ticket_id, $body, wp_strip_all_tags($body), get_current_user_id());
	if (!$sent) {
		wp_send_json_error('Email failed to send');
	}
	strikescope_help_ticket_set_status($ticket_id, 'waiting_customer');
	wp_send_json_success(array('sent' => true));
});