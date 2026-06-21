/**
 * StrikeScopeWP — dedicated admin hub (outside Settings).
 */

define('STRIKESCOPE_MENU_SLUG', 'strikescope-wp');

define('STRIKESCOPE_CONTACT_FORM_SLUG', 'strikescope-contact-form');

function strikescope_admin_pages() {
	return array(
		STRIKESCOPE_MENU_SLUG => array(
			'title'    => 'StrikeScope',
			'callback' => 'strikescope_render_dashboard_page',
			'cap'      => 'manage_options',
		),
		STRIKESCOPE_CONTACT_FORM_SLUG => array(
			'title'    => 'Contact Form',
			'callback' => 'strikescope_render_contact_form_page',
			'cap'      => 'manage_options',
		),
		'strikescope-integrations' => array(
			'title'    => 'Integrations',
			'callback' => 'strikescope_render_integrations_page',
			'cap'      => 'manage_options',
		),
		'strikescope-api-webhooks' => array(
			'title'    => 'API / Webhooks',
			'callback' => 'strikescope_render_api_webhooks_page',
			'cap'      => 'manage_options',
		),
	);
}

function strikescope_contact_form_tabs() {
	return array(
		'form'          => array('label' => 'Form', 'icon' => 'dashicons-forms'),
		'notifications' => array('label' => 'Notifications', 'icon' => 'dashicons-email'),
		'emails'        => array('label' => 'Emails', 'icon' => 'dashicons-email-alt'),
		'help-desk'     => array('label' => 'Help Desk', 'icon' => 'dashicons-sos'),
	);
}

function strikescope_contact_form_active_tab() {
	$tabs = strikescope_contact_form_tabs();
	$tab  = isset($_GET['tab']) ? sanitize_key((string) $_GET['tab']) : 'form';
	return isset($tabs[ $tab ]) ? $tab : 'form';
}

function strikescope_contact_form_tab_url($tab) {
	return add_query_arg(
		array(
			'page' => STRIKESCOPE_CONTACT_FORM_SLUG,
			'tab'  => sanitize_key($tab),
		),
		admin_url('admin.php')
	);
}

function strikescope_admin_page_url($slug) {
	return add_query_arg(array('page' => sanitize_key($slug)), admin_url('admin.php'));
}

function strikescope_admin_current_page() {
	$page = isset($_GET['page']) ? sanitize_key((string) $_GET['page']) : STRIKESCOPE_MENU_SLUG;
	$pages = strikescope_admin_pages();
	return isset($pages[ $page ]) ? $page : STRIKESCOPE_MENU_SLUG;
}

function strikescope_admin_screen_hooks() {
	$hooks = array('toplevel_page_' . STRIKESCOPE_MENU_SLUG);
	foreach (array_keys(strikescope_admin_pages()) as $slug) {
		if ($slug === STRIKESCOPE_MENU_SLUG) {
			continue;
		}
		$hooks[] = STRIKESCOPE_MENU_SLUG . '_page_' . $slug;
	}
	return $hooks;
}

function strikescope_count_cpt($post_type) {
	$q = new WP_Query(
		array(
			'post_type'      => $post_type,
			'post_status'    => 'publish',
			'posts_per_page' => 1,
			'fields'         => 'ids',
		)
	);
	return (int) $q->found_posts;
}

function strikescope_register_admin_menus() {
	$parent = STRIKESCOPE_MENU_SLUG;
	add_menu_page(
		'StrikeScopeWP',
		'StrikeScopeWP',
		'manage_options',
		$parent,
		'strikescope_render_dashboard_page',
		'dashicons-chart-area',
		58
	);

	foreach (strikescope_admin_pages() as $slug => $page) {
		add_submenu_page(
			$parent,
			$page['title'],
			$page['title'],
			$page['cap'],
			$slug,
			$page['callback']
		);
	}

	if (defined('STRIKESCOPE_HELP_TICKET_CPT') && post_type_exists(STRIKESCOPE_HELP_TICKET_CPT)) {
		add_submenu_page(
			$parent,
			'Help Desk',
			'Help Desk',
			'edit_posts',
			'edit.php?post_type=' . STRIKESCOPE_HELP_TICKET_CPT
		);
	}

	remove_submenu_page('options-general.php', 'strikescope-form');
}

add_action('admin_menu', 'strikescope_register_admin_menus', 9);

add_action('admin_enqueue_scripts', static function ($hook) {
	if (!in_array($hook, strikescope_admin_screen_hooks(), true)) {
		return;
	}
	wp_add_inline_style(
		'wp-admin',
		'
		.strikescope-admin { max-width: 1280px; }
		.strikescope-admin .strikescope-brandbar { display:flex; align-items:flex-start; justify-content:space-between; gap:16px; margin:8px 0 16px; padding-bottom:16px; border-bottom:1px solid #c3c4c7; }
		.strikescope-admin .strikescope-brandbar h1 { margin:0; font-size:23px; }
		.strikescope-admin .strikescope-brandbar .strikescope-tagline { margin:4px 0 0; color:#646970; }
		.strikescope-admin .strikescope-badge { display:inline-block; background:#1d2327; color:#fff; font-size:11px; font-weight:600; letter-spacing:.04em; padding:3px 8px; border-radius:3px; margin-left:8px; vertical-align:middle; }
		.strikescope-panel { background:#fff; border:1px solid #c3c4c7; padding:20px 24px 8px; margin-bottom:16px; }
		.strikescope-panel .title { margin-top:0; }
		.strikescope-cards { display:grid; grid-template-columns:repeat(auto-fill,minmax(220px,1fr)); gap:16px; margin:0 0 20px; }
		.strikescope-card { background:#fff; border:1px solid #c3c4c7; border-radius:4px; padding:16px; }
		.strikescope-card h3 { margin:0 0 8px; font-size:14px; }
		.strikescope-card .stat { font-size:28px; font-weight:700; line-height:1.1; color:%%COLOR_PRIMARY%%; }
		.strikescope-card p { margin:8px 0 0; color:#646970; font-size:13px; }
		.strikescope-card .button { margin-top:10px; }
		.strikescope-savebar { position:sticky; bottom:0; background:#f0f0f1; border-top:1px solid #c3c4c7; margin:0 -20px -8px; padding:12px 24px; display:flex; align-items:center; justify-content:space-between; z-index:5; }
		.strikescope-savebar .description { margin:0; }
		.strikescope-quicklinks { display:flex; flex-wrap:wrap; gap:8px; margin:0 0 16px; }
		.strikescope-nav-hint { color:#646970; font-size:13px; margin:0 0 12px; }
		.strikescope-tabs { margin:0 0 0; padding:0; background:#fff; border:1px solid #c3c4c7; border-bottom:none; display:flex; flex-wrap:wrap; gap:0; }
		.strikescope-tabs a { display:inline-flex; align-items:center; gap:6px; padding:12px 16px; text-decoration:none; color:#1d2327; border-bottom:3px solid transparent; margin-bottom:-1px; }
		.strikescope-tabs a:hover { color:%%COLOR_PRIMARY%%; background:#fffbf4; }
		.strikescope-tabs a.is-active { border-bottom-color:%%COLOR_PRIMARY%%; font-weight:600; color:#1d2327; background:#fff; }
		.strikescope-tab-panel { display:none; }
		.strikescope-tab-panel.is-active { display:block; }
		@media (max-width:782px) { .strikescope-tabs a { padding:10px 12px; font-size:13px; } }
		'
	);
});

function strikescope_form_render_settings_page() {
	strikescope_render_dashboard_page();
}

function strikescope_render_admin_shell($title, $description, callable $body, $show_save = false, $tab_nav = null) {
	if (!current_user_can('manage_options')) {
		return;
	}

	$settings    = strikescope_form_get_settings();
	$contact_url = StrikeScope_Branding::contact_page_url();
	if (isset($_GET['settings-updated'])) {
		add_settings_error('strikescope_form', 'strikescope_form_updated', 'Settings saved.', 'updated');
	}

	$test_nonce    = wp_create_nonce('wp_rest');
	$test_endpoint = esc_url_raw(rest_url('strikescope/v1/lead-form/email/test'));
	$admin_nonce   = wp_create_nonce('strikescope_lead_admin');
	$ajax_url      = admin_url('admin-ajax.php');
	?>
	<div class="wrap strikescope-admin">
		<?php settings_errors('strikescope_form'); ?>
		<div class="strikescope-brandbar">
			<div>
				<h1><?php echo esc_html($title); ?><span class="strikescope-badge">StrikeScopeWP</span></h1>
				<p class="strikescope-tagline"><?php echo esc_html($description); ?></p>
			</div>
			<p>
				<a class="button" href="<?php echo esc_url($contact_url); ?>" target="_blank" rel="noopener">View contact page</a>
			</p>
		</div>
		<?php if (is_callable($tab_nav)) {
			$tab_nav();
		} ?>

		<form method="post" action="options.php" id="strikescope-form-settings">
			<?php settings_fields('strikescope_form'); ?>
			<div class="strikescope-panel">
				<?php $body($settings); ?>
				<?php if ($show_save) : ?>
					<div class="strikescope-savebar">
						<p class="description">Changes apply to the live site after you save.</p>
						<?php submit_button('Save settings', 'primary', 'submit', false); ?>
					</div>
				<?php endif; ?>
			</div>
		</form>
	</div>
	<?php
	strikescope_print_settings_admin_script($test_endpoint, $test_nonce, $admin_nonce, $ajax_url);
}

function strikescope_render_contact_form_tab_nav() {
	$active = strikescope_contact_form_active_tab();
	$tabs   = strikescope_contact_form_tabs();
	?>
	<nav class="strikescope-tabs" aria-label="Contact form settings sections">
		<?php foreach ($tabs as $id => $tab) : ?>
			<a class="<?php echo $active === $id ? 'is-active' : ''; ?>" href="<?php echo esc_url(strikescope_contact_form_tab_url($id)); ?>">
				<span class="dashicons <?php echo esc_attr($tab['icon']); ?>" aria-hidden="true"></span>
				<?php echo esc_html($tab['label']); ?>
			</a>
		<?php endforeach; ?>
	</nav>
	<?php
}

function strikescope_render_dashboard_page() {
	strikescope_render_admin_shell(
		'StrikeScope',
		'Operations dashboard for leads, help tickets, and form health.',
		static function ($settings) {
			$elemailer_active = strikescope_form_is_eleemailer_active();
			$contact_url      = StrikeScope_Branding::contact_page_url();
			strikescope_render_dashboard_body($settings, $elemailer_active, $contact_url);
		},
		false
	);
}

function strikescope_render_contact_form_page() {
	$active_tab = strikescope_contact_form_active_tab();
	strikescope_render_admin_shell(
		'Contact Form',
		'Configure the public contact form, notifications, emails, and Help Desk.',
		static function ($settings) use ($active_tab) {
			$panels = array(
				'form'          => 'strikescope_render_contact_form_tab_form',
				'notifications' => 'strikescope_render_contact_form_tab_notifications',
				'emails'        => 'strikescope_render_contact_form_tab_emails',
				'help-desk'     => 'strikescope_render_contact_form_tab_help_desk',
			);
			foreach ($panels as $tab_id => $callback) {
				$class = $active_tab === $tab_id ? 'strikescope-tab-panel is-active' : 'strikescope-tab-panel';
				echo '<div class="' . esc_attr($class) . '" data-tab="' . esc_attr($tab_id) . '">';
				if (is_callable($callback)) {
					$callback($settings);
				}
				echo '</div>';
			}
		},
		true,
		'strikescope_render_contact_form_tab_nav'
	);
}

function strikescope_render_integrations_page() {
	strikescope_render_admin_shell(
		'Integrations',
		'Calendar OAuth and connected services for appointments.',
		static function ($settings) {
			if (function_exists('strikescope_form_render_calendar_integrations_settings')) {
				strikescope_form_render_calendar_integrations_settings($settings, false);
			}
			strikescope_render_rest_reference();
		},
		true
	);
}

function strikescope_render_api_webhooks_page() {
	strikescope_render_admin_shell(
		'API / Webhooks',
		'REST API keys and outbound webhook delivery for lead events.',
		static function ($settings) {
			if (function_exists('strikescope_form_render_api_webhooks_settings')) {
				strikescope_form_render_api_webhooks_settings($settings, false);
			}
		},
		true
	);
}

function strikescope_render_dashboard_body($settings, $elemailer_active, $contact_url) {
	$lead_count   = strikescope_count_cpt(STRIKESCOPE_LEAD_CPT);
	$ticket_count = defined('STRIKESCOPE_HELP_TICKET_CPT') ? strikescope_count_cpt(STRIKESCOPE_HELP_TICKET_CPT) : 0;
	?>
	<div class="strikescope-quicklinks">
		<a class="button button-primary" href="<?php echo esc_url(admin_url('edit.php?post_type=' . STRIKESCOPE_LEAD_CPT)); ?>">Leads</a>
		<?php if (defined('STRIKESCOPE_HELP_TICKET_CPT')) : ?>
			<a class="button" href="<?php echo esc_url(admin_url('edit.php?post_type=' . STRIKESCOPE_HELP_TICKET_CPT)); ?>">Help Desk</a>
		<?php endif; ?>
		<a class="button" href="<?php echo esc_url(strikescope_contact_form_tab_url('form')); ?>">Contact Form</a>
		<a class="button" href="<?php echo esc_url(strikescope_contact_form_tab_url('emails')); ?>">Email templates</a>
		<a class="button" href="<?php echo esc_url(strikescope_admin_page_url('strikescope-api-webhooks')); ?>">API / Webhooks</a>
		<a class="button" href="<?php echo esc_url($contact_url); ?>" target="_blank" rel="noopener">Contact page</a>
	</div>
	<div class="strikescope-cards">
		<div class="strikescope-card">
			<h3>Leads</h3>
			<div class="stat"><?php echo esc_html((string) $lead_count); ?></div>
			<p>Quote requests from the contact form</p>
			<a class="button button-small" href="<?php echo esc_url(admin_url('edit.php?post_type=' . STRIKESCOPE_LEAD_CPT)); ?>">Open leads</a>
		</div>
		<div class="strikescope-card">
			<h3>Help tickets</h3>
			<div class="stat"><?php echo esc_html((string) $ticket_count); ?></div>
			<p>Existing customer support requests</p>
			<?php if (defined('STRIKESCOPE_HELP_TICKET_CPT')) : ?>
				<a class="button button-small" href="<?php echo esc_url(admin_url('edit.php?post_type=' . STRIKESCOPE_HELP_TICKET_CPT)); ?>">Open Help Desk</a>
			<?php endif; ?>
		</div>
		<div class="strikescope-card">
			<h3>Form assets</h3>
			<p><strong><?php echo esc_html(strikescope_form_asset_status_label($settings)); ?></strong></p>
			<p><?php echo !empty($settings['js_version']) ? 'Build ' . esc_html($settings['js_version']) : 'Deploy from repo to update JS/CSS.'; ?></p>
			<a class="button button-small" href="<?php echo esc_url(strikescope_contact_form_tab_url('form')); ?>">Assets &amp; security</a>
		</div>
		<div class="strikescope-card">
			<h3>Elemailer</h3>
			<p><strong style="color:<?php echo $elemailer_active ? '#008a20' : '#b32d2e'; ?>;"><?php echo $elemailer_active ? 'Active' : 'Not detected'; ?></strong></p>
			<p><?php echo $elemailer_active ? 'Custom Elementor templates available.' : 'Using bundled HTML email fallbacks.'; ?></p>
			<a class="button button-small" href="<?php echo esc_url(strikescope_contact_form_tab_url('emails')); ?>">Email templates</a>
		</div>
		<div class="strikescope-card">
			<h3>Help Desk</h3>
			<p><strong><?php echo !empty($settings['help_desk_enabled']) ? 'Enabled' : 'Disabled'; ?></strong></p>
			<p>Existing Customer flow on the contact form</p>
			<a class="button button-small" href="<?php echo esc_url(strikescope_contact_form_tab_url('help-desk')); ?>">Configure</a>
		</div>
		<div class="strikescope-card">
			<h3>Primary inbox</h3>
			<p><strong><?php echo esc_html($settings['primary_email']); ?></strong></p>
			<p>Lead notifications &amp; default routing</p>
			<a class="button button-small" href="<?php echo esc_url(strikescope_contact_form_tab_url('notifications')); ?>">Notifications</a>
		</div>
	</div>
	<?php
}

function strikescope_render_contact_form_tab_form($settings) {
	$catalog          = strikescope_form_service_email_catalog();
	$enabled_services = isset($settings['service_types_enabled']) && is_array($settings['service_types_enabled'])
		? $settings['service_types_enabled']
		: strikescope_form_default_service_types_enabled();
	?>
	<h2 class="title">Form availability</h2>
	<p class="description">Control which options appear on the public contact form.</p>
	<table class="form-table" role="presentation">
		<tr>
			<th scope="row">Help Desk</th>
			<td>
				<label>
					<input type="checkbox" name="<?php echo esc_attr(STRIKESCOPE_SETTINGS_OPTION); ?>[help_desk_enabled]" value="1" <?php checked(!empty($settings['help_desk_enabled'])); ?>>
					Show <strong>Existing Customer / Open a Help Ticket</strong> on the contact form
				</label>
			</td>
		</tr>
		<tr>
			<th scope="row">Service types</th>
			<td>
				<fieldset>
					<legend class="screen-reader-text">Service types</legend>
					<?php foreach (strikescope_form_service_ids() as $service_id) :
						$label = isset($catalog[ $service_id ]['label']) ? $catalog[ $service_id ]['label'] : ucfirst($service_id);
						?>
						<label style="display:block;margin-bottom:8px;">
							<input type="checkbox" name="<?php echo esc_attr(STRIKESCOPE_SETTINGS_OPTION); ?>[service_types_enabled][<?php echo esc_attr($service_id); ?>]" value="1" <?php checked(!empty($enabled_services[ $service_id ])); ?>>
							<?php echo esc_html($label); ?> <code><?php echo esc_html($service_id); ?></code>
						</label>
					<?php endforeach; ?>
				</fieldset>
				<p class="description">At least one service should stay enabled for quote requests.</p>
			</td>
		</tr>
		<tr>
			<th scope="row"><?php esc_html_e('Phone & contact page', 'strikescope-wp'); ?></th>
			<td>
				<p><strong><?php esc_html_e('Phone', 'strikescope-wp'); ?>:</strong> <?php echo esc_html(StrikeScope_Branding::phone_display() ?: '—'); ?></p>
				<p><strong><?php esc_html_e('Contact slug', 'strikescope-wp'); ?>:</strong> <code><?php echo esc_html(StrikeScope_Branding::contact_page_slug()); ?></code></p>
				<p class="description">
					<?php esc_html_e('Edit under', 'strikescope-wp'); ?>
					<a href="<?php echo esc_url(admin_url('admin.php?page=strikescope-branding')); ?>"><?php esc_html_e('StrikeScopeWP → Branding', 'strikescope-wp'); ?></a>.
				</p>
			</td>
		</tr>
	</table>

	<h2 class="title">Security</h2>
	<table class="form-table" role="presentation">
		<tr>
			<th scope="row"><label for="turnstile_site_key">Turnstile site key</label></th>
			<td><input name="<?php echo esc_attr(STRIKESCOPE_SETTINGS_OPTION); ?>[turnstile_site_key]" id="turnstile_site_key" type="text" class="regular-text" value="<?php echo esc_attr($settings['turnstile_site_key']); ?>"></td>
		</tr>
		<tr>
			<th scope="row"><label for="turnstile_secret_key">Turnstile secret key</label></th>
			<td><input name="<?php echo esc_attr(STRIKESCOPE_SETTINGS_OPTION); ?>[turnstile_secret_key]" id="turnstile_secret_key" type="password" class="regular-text" value="<?php echo esc_attr($settings['turnstile_secret_key']); ?>" autocomplete="new-password"></td>
		</tr>
		<tr>
			<th scope="row"><label for="rate_limit_per_hour">Rate limit (per IP / hour)</label></th>
			<td><input name="<?php echo esc_attr(STRIKESCOPE_SETTINGS_OPTION); ?>[rate_limit_per_hour]" id="rate_limit_per_hour" type="number" min="1" max="50" value="<?php echo esc_attr((string) $settings['rate_limit_per_hour']); ?>"></td>
		</tr>
	</table>

	<h2 class="title">Deployed assets</h2>
	<table class="form-table" role="presentation">
		<tr>
			<th scope="row">Build status</th>
			<td>
				<p><strong>Status:</strong> <?php echo esc_html(strikescope_form_asset_status_label($settings)); ?></p>
				<p><strong>JS:</strong> <?php echo $settings['js_url'] ? esc_html($settings['js_url']) : 'Not deployed yet'; ?></p>
				<p><strong>CSS:</strong> <?php echo $settings['css_url'] ? esc_html($settings['css_url']) : 'Not deployed yet'; ?></p>
				<?php if (!empty($settings['js_version'])) : ?>
					<p><strong>Version:</strong> <?php echo esc_html($settings['js_version']); ?></p>
				<?php endif; ?>
				<p class="description">Run <code>python scripts/deploy_lead_form.py</code> from the project repo.</p>
			</td>
		</tr>
	</table>
	<?php
}

function strikescope_render_contact_form_tab_notifications($settings) {
	$routes = '';
	if (is_array($settings['service_routes'])) {
		foreach ($settings['service_routes'] as $service => $email) {
			$routes .= $service . ': ' . $email . "\n";
		}
	}
	?>
	<h2 class="title">Lead notifications</h2>
	<p class="description">Who receives new lead alerts and how submissions are routed by service.</p>
	<table class="form-table" role="presentation">
		<tr>
			<th scope="row"><label for="primary_email">Primary recipient</label></th>
			<td><input name="<?php echo esc_attr(STRIKESCOPE_SETTINGS_OPTION); ?>[primary_email]" id="primary_email" type="email" class="regular-text" value="<?php echo esc_attr($settings['primary_email']); ?>" required></td>
		</tr>
		<tr>
			<th scope="row"><label for="cc_emails">CC recipients</label></th>
			<td><input name="<?php echo esc_attr(STRIKESCOPE_SETTINGS_OPTION); ?>[cc_emails]" id="cc_emails" type="text" class="large-text" value="<?php echo esc_attr($settings['cc_emails']); ?>" placeholder="sales@example.com, ops@example.com"><p class="description">Comma-separated email addresses.</p></td>
		</tr>
		<tr>
			<th scope="row"><label for="service_routes">Service routing</label></th>
			<td><textarea name="<?php echo esc_attr(STRIKESCOPE_SETTINGS_OPTION); ?>[service_routes]" id="service_routes" class="large-text code" rows="5"><?php echo esc_textarea(trim($routes)); ?></textarea><p class="description">One per line: <code>service-id: email@domain.com</code> (roofing, kitchen, bathroom, addons, solar).</p></td>
		</tr>
	</table>

	<h2 class="title">Submission blacklist</h2>
	<p class="description">Matching submissions are silently rejected (generic error, no lead record, no emails).</p>
	<table class="form-table" role="presentation">
		<tr>
			<th scope="row"><label for="blacklist_emails">Blacklisted emails</label></th>
			<td><textarea name="<?php echo esc_attr(STRIKESCOPE_SETTINGS_OPTION); ?>[blacklist_emails]" id="blacklist_emails" class="large-text code" rows="4"><?php echo esc_textarea($settings['blacklist_emails'] ?? ''); ?></textarea></td>
		</tr>
		<tr>
			<th scope="row"><label for="blacklist_zips">Blacklisted ZIP codes</label></th>
			<td><textarea name="<?php echo esc_attr(STRIKESCOPE_SETTINGS_OPTION); ?>[blacklist_zips]" id="blacklist_zips" class="large-text code" rows="3"><?php echo esc_textarea($settings['blacklist_zips'] ?? ''); ?></textarea></td>
		</tr>
		<tr>
			<th scope="row"><label for="blacklist_cities">Blacklisted cities</label></th>
			<td><textarea name="<?php echo esc_attr(STRIKESCOPE_SETTINGS_OPTION); ?>[blacklist_cities]" id="blacklist_cities" class="large-text code" rows="3"><?php echo esc_textarea($settings['blacklist_cities'] ?? ''); ?></textarea></td>
		</tr>
		<tr>
			<th scope="row"><label for="blacklist_states">Blacklisted states</label></th>
			<td><textarea name="<?php echo esc_attr(STRIKESCOPE_SETTINGS_OPTION); ?>[blacklist_states]" id="blacklist_states" class="large-text code" rows="2"><?php echo esc_textarea($settings['blacklist_states'] ?? ''); ?></textarea></td>
		</tr>
	</table>

	<h2 class="title">Appointment scheduler</h2>
	<table class="form-table" role="presentation">
		<tr>
			<th scope="row">Enable scheduler</th>
			<td><label><input type="checkbox" name="<?php echo esc_attr(STRIKESCOPE_SETTINGS_OPTION); ?>[scheduler_enabled]" value="1" <?php checked(!empty($settings['scheduler_enabled'])); ?>> Show appointment panel on lead detail pages</label></td>
		</tr>
		<tr>
			<th scope="row">Auto-set status</th>
			<td><label><input type="checkbox" name="<?php echo esc_attr(STRIKESCOPE_SETTINGS_OPTION); ?>[scheduler_auto_status]" value="1" <?php checked(!empty($settings['scheduler_auto_status'])); ?>> Set lead status to Scheduled when booking (new/aging/contacted)</label></td>
		</tr>
	</table>
	<?php
}

function strikescope_render_contact_form_tab_emails($settings) {
	$elemailer_active = strikescope_form_is_eleemailer_active();
	$template_id      = isset($settings['elemailer_template_id']) ? absint($settings['elemailer_template_id']) : 0;
	$elementor_edit   = $template_id > 0 ? admin_url('post.php?post=' . $template_id . '&action=elementor') : '';
	$current_user     = wp_get_current_user();
	?>
	<h2 class="title">Customer follow-up email</h2>
	<p class="description">When <strong>two or more services</strong> are selected, the <strong>Multiple Services</strong> template is used. Elemailer: <?php echo $elemailer_active ? '<span style="color:#008a20;">Active</span>' : '<span style="color:#b32d2e;">Not detected — HTML fallbacks</span>'; ?>.</p>
	<table class="form-table" role="presentation">
		<tr>
			<th scope="row">Enable follow-up</th>
			<td><label><input type="checkbox" name="<?php echo esc_attr(STRIKESCOPE_SETTINGS_OPTION); ?>[customer_follow_up_enabled]" value="1" <?php checked(!empty($settings['customer_follow_up_enabled'])); ?>> Send branded confirmation email to the customer</label></td>
		</tr>
		<tr>
			<th scope="row"><label for="customer_from_name">From name</label></th>
			<td><input name="<?php echo esc_attr(STRIKESCOPE_SETTINGS_OPTION); ?>[customer_from_name]" id="customer_from_name" type="text" class="regular-text" value="<?php echo esc_attr($settings['customer_from_name']); ?>"></td>
		</tr>
		<tr>
			<th scope="row"><label for="customer_reply_to">Reply-To</label></th>
			<td><input name="<?php echo esc_attr(STRIKESCOPE_SETTINGS_OPTION); ?>[customer_reply_to]" id="customer_reply_to" type="email" class="regular-text" value="<?php echo esc_attr($settings['customer_reply_to']); ?>" placeholder="<?php echo esc_attr($settings['primary_email']); ?>"></td>
		</tr>
		<tr>
			<th scope="row"><label for="elemailer_template_id">Global fallback template ID</label></th>
			<td>
				<input name="<?php echo esc_attr(STRIKESCOPE_SETTINGS_OPTION); ?>[elemailer_template_id]" id="elemailer_template_id" type="number" min="0" class="small-text" value="<?php echo esc_attr((string) $template_id); ?>">
				<?php if ($elementor_edit) : ?><a class="button button-secondary" href="<?php echo esc_url($elementor_edit); ?>" target="_blank" rel="noopener">Edit in Elementor</a><?php endif; ?>
			</td>
		</tr>
		<tr>
			<th scope="row"><label for="customer_follow_up_subject">Global fallback subject</label></th>
			<td><input name="<?php echo esc_attr(STRIKESCOPE_SETTINGS_OPTION); ?>[customer_follow_up_subject]" id="customer_follow_up_subject" type="text" class="large-text" value="<?php echo esc_attr($settings['customer_follow_up_subject']); ?>"></td>
		</tr>
	</table>
	<h3 class="title">Per-service templates</h3>
	<?php strikescope_render_service_email_templates_table($settings, $current_user); ?>
	<?php
}

function strikescope_render_contact_form_tab_help_desk($settings) {
	?>
	<h2 class="title">Help Desk</h2>
	<?php
	if (function_exists('strikescope_help_ticket_render_settings_section')) {
		strikescope_help_ticket_render_settings_section(false, false);
	} else {
		echo '<p>Help Desk module not loaded.</p>';
	}
}

function strikescope_render_rest_reference() {
	?>
	<h2 class="title">REST reference</h2>
	<p class="description">Base namespace: <code><?php echo esc_html(rest_url('strikescope/v1')); ?></code></p>
	<ul class="description" style="max-width:900px;">
		<li><code>GET /lead-form/leads</code> — list leads (API key or admin)</li>
		<li><code>GET /lead-form/leads/{id}</code> — single lead</li>
		<li><code>PATCH /lead-form/leads/{id}</code> — update lead</li>
		<li><code>GET /lead-form/leads/export</code> — CSV export</li>
		<li><code>POST /lead-form/submit</code> — public form submit</li>
		<li><code>POST /help-ticket/submit</code> — public help ticket submit</li>
	</ul>
	<?php
}

function strikescope_render_service_email_templates_table($settings, $current_user) {
	$catalog = strikescope_form_service_email_catalog();
	?>
	<table class="widefat striped" style="max-width:1100px;margin-bottom:16px;">
		<thead>
			<tr>
				<th>Service</th>
				<th>Customer Elemailer ID</th>
				<th>Customer subject</th>
				<th>Admin Elemailer ID</th>
				<th>Admin subject</th>
				<th>Test</th>
			</tr>
		</thead>
		<tbody>
			<?php foreach (strikescope_form_email_template_keys() as $service_id) :
				$customer_row  = strikescope_form_service_template_row($settings, 'customer', $service_id);
				$admin_row     = strikescope_form_service_template_row($settings, 'admin', $service_id);
				$label         = isset($catalog[ $service_id ]['label']) ? $catalog[ $service_id ]['label'] : ucfirst($service_id);
				$customer_edit = absint($customer_row['elemailer_id']) > 0 ? admin_url('post.php?post=' . absint($customer_row['elemailer_id']) . '&action=elementor') : '';
				$admin_edit    = absint($admin_row['elemailer_id']) > 0 ? admin_url('post.php?post=' . absint($admin_row['elemailer_id']) . '&action=elementor') : '';
				?>
				<tr>
					<td><strong><?php echo esc_html($label); ?></strong><br><code><?php echo esc_html($service_id); ?></code></td>
					<td>
						<input type="number" min="0" class="small-text" name="<?php echo esc_attr(STRIKESCOPE_SETTINGS_OPTION); ?>[customer_service_templates][<?php echo esc_attr($service_id); ?>][elemailer_id]" value="<?php echo esc_attr((string) absint($customer_row['elemailer_id'])); ?>">
						<?php if ($customer_edit) : ?><a href="<?php echo esc_url($customer_edit); ?>" target="_blank" rel="noopener">Edit</a><?php endif; ?>
					</td>
					<td><input type="text" class="regular-text" name="<?php echo esc_attr(STRIKESCOPE_SETTINGS_OPTION); ?>[customer_service_templates][<?php echo esc_attr($service_id); ?>][subject]" value="<?php echo esc_attr($customer_row['subject']); ?>" placeholder="<?php echo esc_attr($catalog[ $service_id ]['customer_subject_default'] ?? ''); ?>"></td>
					<td>
						<input type="number" min="0" class="small-text" name="<?php echo esc_attr(STRIKESCOPE_SETTINGS_OPTION); ?>[admin_service_templates][<?php echo esc_attr($service_id); ?>][elemailer_id]" value="<?php echo esc_attr((string) absint($admin_row['elemailer_id'])); ?>">
						<?php if ($admin_edit) : ?><a href="<?php echo esc_url($admin_edit); ?>" target="_blank" rel="noopener">Edit</a><?php endif; ?>
					</td>
					<td><input type="text" class="regular-text" name="<?php echo esc_attr(STRIKESCOPE_SETTINGS_OPTION); ?>[admin_service_templates][<?php echo esc_attr($service_id); ?>][subject]" value="<?php echo esc_attr($admin_row['subject']); ?>" placeholder="<?php echo esc_attr($catalog[ $service_id ]['admin_subject_default'] ?? ''); ?>"></td>
					<td>
						<button type="button" class="button strikescope-test-service-email" data-service="<?php echo esc_attr($service_id); ?>" data-type="customer">Customer</button>
						<button type="button" class="button strikescope-test-service-email" data-service="<?php echo esc_attr($service_id); ?>" data-type="admin">Admin</button>
					</td>
				</tr>
			<?php endforeach; ?>
		</tbody>
	</table>
	<p class="description">Merge tags: <code><?php echo esc_html(implode(', ', strikescope_form_merge_tag_keys())); ?></code></p>
	<p>
		<label for="strikescope_form_test_email">Test inbox:</label>
		<input id="strikescope_form_test_email" type="email" class="regular-text" value="<?php echo esc_attr($current_user->user_email); ?>">
		<span id="strikescope_form_test_email_status" style="margin-left:8px;"></span>
	</p>
	<?php
}

function strikescope_print_settings_admin_script($test_endpoint, $test_nonce, $admin_nonce, $ajax_url) {
	?>
	<script>
	(function () {
		var endpoint = <?php echo wp_json_encode($test_endpoint); ?>;
		var nonce = <?php echo wp_json_encode($test_nonce); ?>;
		var status = document.getElementById('strikescope_form_test_email_status');

		function sendTest(type, service) {
			var email = document.getElementById('strikescope_form_test_email');
			if (!status || !email) return;
			status.textContent = 'Sending ' + type + ' (' + service + ')...';
			status.style.color = '';
			fetch(endpoint, {
				method: 'POST',
				headers: { 'Content-Type': 'application/json', 'X-WP-Nonce': nonce },
				body: JSON.stringify({ email: email.value, type: type, service: service })
			}).then(function (res) {
				return res.json().then(function (body) { return { ok: res.ok, body: body }; });
			}).then(function (result) {
				if (result.ok) {
					status.textContent = result.body.message || 'Test email sent.';
					status.style.color = '#008a20';
				} else {
					status.textContent = (result.body && result.body.message) ? result.body.message : 'Send failed.';
					status.style.color = '#b32d2e';
				}
			}).catch(function () {
				status.textContent = 'Request failed.';
				status.style.color = '#b32d2e';
			});
		}

		document.querySelectorAll('.strikescope-test-service-email').forEach(function (btn) {
			btn.addEventListener('click', function () {
				sendTest(btn.getAttribute('data-type') || 'customer', btn.getAttribute('data-service') || 'roofing');
			});
		});

		var adminNonce = <?php echo wp_json_encode($admin_nonce); ?>;
		var ajaxUrl = <?php echo wp_json_encode($ajax_url); ?>;
		function adminPost(action, data) {
			var body = new FormData();
			body.append('action', action);
			body.append('nonce', adminNonce);
			Object.keys(data || {}).forEach(function (k) {
				var v = data[k];
				if (Array.isArray(v)) { v.forEach(function (item) { body.append(k, item); }); }
				else { body.append(k, v); }
			});
			return fetch(ajaxUrl, { method: 'POST', credentials: 'same-origin', body: body }).then(function (r) { return r.json(); });
		}
		var genBtn = document.getElementById('strikescope-generate-api-key');
		if (genBtn) {
			genBtn.addEventListener('click', function () {
				var label = (document.getElementById('strikescope-new-api-key-label') || {}).value || 'API key';
				adminPost('strikescope_lead_generate_api_key', { label: label, scopes: ['leads:read', 'leads:write', 'leads:export'] }).then(function (res) {
					var el = document.getElementById('strikescope-new-api-key-result');
					if (el && res.success) {
						el.textContent = 'Copy now (shown once): ' + res.data.key;
						el.style.color = '#008a20';
					}
				});
			});
		}
		document.querySelectorAll('.strikescope-revoke-api-key').forEach(function (btn) {
			btn.addEventListener('click', function () {
				adminPost('strikescope_lead_revoke_api_key', { key_id: btn.getAttribute('data-key-id') }).then(function () { location.reload(); });
			});
		});
		var testHook = document.getElementById('strikescope-test-webhook');
		if (testHook) {
			testHook.addEventListener('click', function () {
				var urlInput = document.querySelector('input[name="<?php echo esc_js(STRIKESCOPE_SETTINGS_OPTION); ?>[webhooks][0][url]"]');
				adminPost('strikescope_lead_test_webhook', { url: urlInput ? urlInput.value : '' }).then(function (res) {
					alert(res.success ? 'Webhook responded HTTP ' + res.data.code : 'Failed');
				});
			});
		}
	})();
	</script>
	<?php
}

add_action('admin_init', static function () {
	if (!isset($_GET['page'])) {
		return;
	}
	$page = sanitize_key((string) $_GET['page']);
	$legacy = array('strikescope-form', 'strikescope-forms');
	if (in_array($page, $legacy, true)) {
		wp_safe_redirect(strikescope_admin_page_url(STRIKESCOPE_MENU_SLUG));
		exit;
	}
	$legacy_contact = array(
		'strikescope-contact-forms' => 'form',
		'strikescope-settings'      => 'help-desk',
	);
	if (isset($legacy_contact[ $page ])) {
		wp_safe_redirect(strikescope_contact_form_tab_url($legacy_contact[ $page ]));
		exit;
	}
});

// Reorder StrikeScopeWP submenus: StrikeScope, Leads, Help Desk, Contact Form, Integrations, API/Webhooks.
add_filter('custom_menu_order', '__return_true');
add_filter('menu_order', static function ($menu_order) {
	return is_array($menu_order) ? $menu_order : array();
});
add_action('admin_menu', static function () {
	global $submenu;
	if (!isset($submenu[ STRIKESCOPE_MENU_SLUG ])) {
		return;
	}
	$desired = array(
		STRIKESCOPE_MENU_SLUG,
		'edit.php?post_type=' . STRIKESCOPE_LEAD_CPT,
		'edit.php?post_type=' . (defined('STRIKESCOPE_HELP_TICKET_CPT') ? STRIKESCOPE_HELP_TICKET_CPT : ''),
		STRIKESCOPE_CONTACT_FORM_SLUG,
		'strikescope-integrations',
		'strikescope-api-webhooks',
	);
	$indexed = array();
	foreach ($submenu[ STRIKESCOPE_MENU_SLUG ] as $item) {
		$indexed[ $item[2] ] = $item;
	}
	$ordered = array();
	foreach ($desired as $slug) {
		if ($slug !== '' && isset($indexed[ $slug ])) {
			$ordered[] = $indexed[ $slug ];
			unset($indexed[ $slug ]);
		}
	}
	foreach ($indexed as $item) {
		$ordered[] = $item;
	}
	$submenu[ STRIKESCOPE_MENU_SLUG ] = $ordered;
}, 999);