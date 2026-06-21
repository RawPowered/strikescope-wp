<?php
/**
 * Privacy exporters and erasers (WordPress.org baseline).
 */

if (!defined('ABSPATH')) {
	exit;
}

final class StrikeScope_Privacy {
	public static function init(): void {
		add_filter('wp_privacy_personal_data_exporters', array(__CLASS__, 'register_exporters'));
		add_filter('wp_privacy_personal_data_erasers', array(__CLASS__, 'register_erasers'));
	}

	public static function register_exporters(array $exporters): array {
		$exporters['strikescope-wp-leads'] = array(
			'exporter_friendly_name' => __('StrikeScopeWP Leads', 'strikescope-wp'),
			'callback'               => array(__CLASS__, 'export_leads'),
		);
		$exporters['strikescope-wp-help-tickets'] = array(
			'exporter_friendly_name' => __('StrikeScopeWP Help Desk Tickets', 'strikescope-wp'),
			'callback'               => array(__CLASS__, 'export_tickets'),
		);
		return $exporters;
	}

	public static function register_erasers(array $erasers): array {
		$erasers['strikescope-wp-leads'] = array(
			'eraser_friendly_name' => __('StrikeScopeWP Leads', 'strikescope-wp'),
			'callback'             => array(__CLASS__, 'erase_leads'),
		);
		return $erasers;
	}

	public static function export_leads(string $email, int $page = 1): array {
		$email = sanitize_email($email);
		$data  = array('data' => array(), 'done' => true);
		if ($email === '') {
			return $data;
		}
		$posts = get_posts(
			array(
				'post_type'      => STRIKESCOPE_LEAD_CPT,
				'posts_per_page' => 20,
				'paged'          => max(1, $page),
				'meta_query'     => array(
					array(
						'key'     => 'email',
						'value'   => $email,
						'compare' => '=',
					),
				),
			)
		);
		foreach ($posts as $post) {
			$data['data'][] = array(
				'group_id'    => 'strikescope-leads',
				'group_label' => __('Lead submissions', 'strikescope-wp'),
				'item_id'     => 'lead-' . $post->ID,
				'data'        => array(
					array('name' => __('Submitted', 'strikescope-wp'), 'value' => $post->post_date),
					array('name' => __('Title', 'strikescope-wp'), 'value' => $post->post_title),
				),
			);
		}
		$data['done'] = count($posts) < 20;
		return $data;
	}

	public static function export_tickets(string $email, int $page = 1): array {
		$email = sanitize_email($email);
		$data  = array('data' => array(), 'done' => true);
		if ($email === '') {
			return $data;
		}
		$posts = get_posts(
			array(
				'post_type'      => 'strikescope_help_ticket',
				'posts_per_page' => 20,
				'paged'          => max(1, $page),
				'meta_query'     => array(
					array(
						'key'     => 'customer_email',
						'value'   => $email,
						'compare' => '=',
					),
				),
			)
		);
		foreach ($posts as $post) {
			$data['data'][] = array(
				'group_id'    => 'strikescope-tickets',
				'group_label' => __('Help Desk tickets', 'strikescope-wp'),
				'item_id'     => 'ticket-' . $post->ID,
				'data'        => array(
					array('name' => __('Submitted', 'strikescope-wp'), 'value' => $post->post_date),
					array('name' => __('Subject', 'strikescope-wp'), 'value' => $post->post_title),
				),
			);
		}
		$data['done'] = count($posts) < 20;
		return $data;
	}

	public static function erase_leads(string $email, int $page = 1): array {
		$export = self::export_leads($email, $page);
		foreach ($export['data'] as $item) {
			if (preg_match('/^lead-(\d+)$/', (string) $item['item_id'], $m)) {
				wp_delete_post((int) $m[1], true);
			}
		}
		return array(
			'items_removed'  => count($export['data']),
			'items_retained' => 0,
			'messages'       => array(),
			'done'           => $export['done'],
		);
	}
}
