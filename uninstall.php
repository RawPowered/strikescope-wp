<?php
/**
 * StrikeScopeWP uninstall handler.
 */

if (!defined('WP_UNINSTALL_PLUGIN')) {
	exit;
}

if (get_option('strikescope_delete_data_on_uninstall', false)) {
	delete_option('strikescope_settings');
	delete_option('strikescope_branding');
	delete_option('strikescope_delete_data_on_uninstall');

	$post_types = array('strikescope_lead', 'strikescope_help_ticket');
	foreach ($post_types as $post_type) {
		$posts = get_posts(
			array(
				'post_type'      => $post_type,
				'posts_per_page' => -1,
				'post_status'    => 'any',
				'fields'         => 'ids',
			)
		);
		foreach ($posts as $post_id) {
			wp_delete_post((int) $post_id, true);
		}
	}
}

wp_clear_scheduled_hook('strikescope_lead_aging_cron');
wp_clear_scheduled_hook('strikescope_appointment_reminders_cron');
