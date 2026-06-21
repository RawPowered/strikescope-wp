<?php
/**
 * Branding settings, merge tags, and admin preview.
 */

if (!defined('ABSPATH')) {
	exit;
}

final class StrikeScope_Branding {
	public static function init(): void {
		add_filter('strikescope_form_brand_defaults', array(__CLASS__, 'filter_brand_defaults'), 10, 1);
		add_filter('strikescope_form_localize_config', array(__CLASS__, 'filter_localize_config'), 10, 1);
	}

	public static function default_branding(): array {
		return array(
			'company_name'        => get_bloginfo('name'),
			'logo_attachment_id'  => 0,
			'phone_display'       => '',
			'footer_text'         => __('Licensed & Insured', 'strikescope-wp'),
			'contact_page_slug'   => 'contact',
			'colors'              => array(
				'primary'    => '#2563eb',
				'accent'     => '#1d4ed8',
				'background' => '#ffffff',
				'secondary'  => '#eff6ff',
				'border'     => '#dbeafe',
				'muted'      => '#64748b',
			),
		);
	}

	public static function get_branding(): array {
		self::maybe_migrate_legacy_contact_settings();
		$stored = get_option(STRIKESCOPE_BRANDING_OPTION, array());
		if (!is_array($stored)) {
			$stored = array();
		}
		return wp_parse_args($stored, self::default_branding());
	}

	public static function phone_display(): string {
		$branding = self::get_branding();
		return sanitize_text_field((string) ($branding['phone_display'] ?? ''));
	}

	public static function contact_page_slug(): string {
		$branding = self::get_branding();
		$slug = sanitize_title((string) ($branding['contact_page_slug'] ?? 'contact'));
		return $slug !== '' ? $slug : 'contact';
	}

	/** One-time move of phone/slug from strikescope_settings into branding. */
	public static function maybe_migrate_legacy_contact_settings(): void {
		if (get_option('strikescope_contact_migrated')) {
			return;
		}

		$legacy = get_option(STRIKESCOPE_SETTINGS_OPTION);
		if (!is_array($legacy)) {
			update_option('strikescope_contact_migrated', 1, false);
			return;
		}

		$stored   = get_option(STRIKESCOPE_BRANDING_OPTION, array());
		$branding = wp_parse_args(is_array($stored) ? $stored : array(), self::default_branding());
		$changed  = false;

		$legacy_phone = isset($legacy['phone_display']) ? trim((string) $legacy['phone_display']) : '';
		if ($legacy_phone === '+1' || $legacy_phone === '+1 ') {
			$legacy_phone = '';
		}
		if (trim((string) ($branding['phone_display'] ?? '')) === '' && $legacy_phone !== '' && $legacy_phone !== '+1 ') {
			$branding['phone_display'] = sanitize_text_field($legacy_phone);
			$changed                   = true;
		}

		$legacy_slug  = isset($legacy['contact_page_slug']) ? sanitize_title((string) $legacy['contact_page_slug']) : '';
		$current_slug = sanitize_title((string) ($branding['contact_page_slug'] ?? 'contact'));
		if ($legacy_slug !== '' && $legacy_slug !== $current_slug && ($current_slug === 'contact' || trim((string) ($stored['contact_page_slug'] ?? '')) === '')) {
			$branding['contact_page_slug'] = $legacy_slug;
			$changed                       = true;
		}

		if ($changed) {
			update_option(STRIKESCOPE_BRANDING_OPTION, $branding, false);
		}

		unset($legacy['phone_display'], $legacy['contact_page_slug']);
		update_option(STRIKESCOPE_SETTINGS_OPTION, $legacy, false);
		update_option('strikescope_contact_migrated', 1, false);
	}

	public static function logo_url(): string {
		$branding = self::get_branding();
		$id       = isset($branding['logo_attachment_id']) ? (int) $branding['logo_attachment_id'] : 0;
		if ($id > 0) {
			$url = wp_get_attachment_image_url($id, 'medium');
			if (is_string($url) && $url !== '') {
				return $url;
			}
		}
		return STRIKESCOPE_WP_URL . 'assets/images/placeholder-logo.svg';
	}

	public static function contact_page_url(): string {
		$slug = self::contact_page_slug();
		if ($slug === '') {
			return home_url('/');
		}
		$page = get_page_by_path($slug);
		if ($page instanceof WP_Post) {
			return get_permalink($page);
		}
		return trailingslashit(home_url()) . $slug;
	}

	public static function merge_tags(): array {
		$branding = self::get_branding();
		$colors   = isset($branding['colors']) && is_array($branding['colors']) ? $branding['colors'] : array();
		return array(
			'%%COMPANY_NAME%%'       => (string) $branding['company_name'],
			'%%LOGO_URL%%'           => esc_url(self::logo_url()),
			'%%SITE_URL%%'           => esc_url(home_url('/')),
			'%%CONTACT_PAGE_URL%%'   => esc_url(self::contact_page_url()),
			'%%COLOR_PRIMARY%%'      => sanitize_hex_color($colors['primary'] ?? '#2563eb') ?: '#2563eb',
			'%%COLOR_SECONDARY%%'    => sanitize_hex_color($colors['secondary'] ?? '#eff6ff') ?: '#eff6ff',
			'%%COLOR_BACKGROUND%%'   => sanitize_hex_color($colors['background'] ?? '#ffffff') ?: '#ffffff',
			'%%COLOR_BORDER%%'       => sanitize_hex_color($colors['border'] ?? '#dbeafe') ?: '#dbeafe',
			'%%COLOR_MUTED%%'        => sanitize_hex_color($colors['muted'] ?? '#64748b') ?: '#64748b',
			'%%FOOTER_TEXT%%'        => (string) $branding['footer_text'],
			'%%PHONE_DISPLAY%%'      => esc_html(self::phone_display()),
			'%%PHONE%%'              => esc_html(self::phone_display()),
			'%%PHONE_TEL%%'          => esc_attr(preg_replace('/[^\d+]/', '', self::phone_display())),
		);
	}

	public static function apply_merge_tags(string $template, array $extra = array()): string {
		$tags = array_merge(self::merge_tags(), $extra);
		return strtr($template, $tags);
	}

	public static function filter_brand_defaults(array $defaults): array {
		$branding = self::get_branding();
		$defaults['logo_url']        = self::logo_url();
		$defaults['site_url']        = home_url('/');
		$defaults['licenses_footer'] = (string) $branding['footer_text'];
		return $defaults;
	}

	public static function filter_localize_config(array $config): array {
		$branding = self::get_branding();
		$colors   = isset($branding['colors']) && is_array($branding['colors']) ? $branding['colors'] : array();
		$config['logoUrl']      = self::logo_url();
		$config['companyName']  = (string) $branding['company_name'];
		$config['footerText']   = (string) $branding['footer_text'];
		$config['phoneDisplay'] = self::phone_display();
		$config['colors']       = array(
			'primary'    => $colors['primary'] ?? '#2563eb',
			'accent'     => $colors['accent'] ?? '#1d4ed8',
			'background' => $colors['background'] ?? '#ffffff',
			'secondary'  => $colors['secondary'] ?? '#eff6ff',
			'border'     => $colors['border'] ?? '#dbeafe',
			'muted'      => $colors['muted'] ?? '#64748b',
			'text'       => '#1c1c1c',
		);
		return $config;
	}

	public static function render_page(): void {
		if (!current_user_can('manage_options')) {
			return;
		}
		$branding = self::get_branding();
		if (isset($_POST['strikescope_branding_nonce']) && wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['strikescope_branding_nonce'])), 'strikescope_branding_save')) {
			$branding = self::save_from_post();
			echo '<div class="notice notice-success"><p>' . esc_html__('Branding saved.', 'strikescope-wp') . '</p></div>';
		}
		$colors = isset($branding['colors']) && is_array($branding['colors']) ? $branding['colors'] : array();
		$preview_email = self::apply_merge_tags(
			'<div style="background:%%COLOR_SECONDARY%%;padding:16px;text-align:center;border-bottom:3px solid %%COLOR_PRIMARY%%;"><img src="%%LOGO_URL%%" alt="%%COMPANY_NAME%%" width="180"></div><p>Hi %%FIRST_NAME%%, thanks for contacting %%COMPANY_NAME%%.</p>',
			array('%%FIRST_NAME%%' => 'Alex')
		);
		?>
		<div class="wrap">
			<h1><?php esc_html_e('StrikeScopeWP Branding', 'strikescope-wp'); ?></h1>
			<form method="post">
				<?php wp_nonce_field('strikescope_branding_save', 'strikescope_branding_nonce'); ?>
				<table class="form-table" role="presentation">
					<tr>
						<th scope="row"><label for="company_name"><?php esc_html_e('Company name', 'strikescope-wp'); ?></label></th>
						<td><input name="company_name" id="company_name" type="text" class="regular-text" value="<?php echo esc_attr((string) $branding['company_name']); ?>"></td>
					</tr>
					<tr>
						<th scope="row"><label for="logo_attachment_id"><?php esc_html_e('Logo attachment ID', 'strikescope-wp'); ?></label></th>
						<td>
							<input name="logo_attachment_id" id="logo_attachment_id" type="number" min="0" value="<?php echo esc_attr((string) (int) $branding['logo_attachment_id']); ?>">
							<p class="description"><?php esc_html_e('Upload a logo in Media, then paste the attachment ID here.', 'strikescope-wp'); ?></p>
							<?php if (self::logo_url()) : ?>
								<p><img src="<?php echo esc_url(self::logo_url()); ?>" alt="" style="max-width:220px;height:auto;"></p>
							<?php endif; ?>
						</td>
					</tr>
					<tr>
						<th scope="row"><label for="phone_display"><?php esc_html_e('Phone display', 'strikescope-wp'); ?></label></th>
						<td>
							<input name="phone_display" id="phone_display" type="text" class="regular-text" value="<?php echo esc_attr((string) $branding['phone_display']); ?>" placeholder="<?php esc_attr_e('(555) 123-4567', 'strikescope-wp'); ?>">
							<p class="description"><?php esc_html_e('Shown on the contact form and in emails (%%PHONE%%).', 'strikescope-wp'); ?></p>
						</td>
					</tr>
					<tr>
						<th scope="row"><label for="footer_text"><?php esc_html_e('Footer text', 'strikescope-wp'); ?></label></th>
						<td><textarea name="footer_text" id="footer_text" class="large-text" rows="3"><?php echo esc_textarea((string) $branding['footer_text']); ?></textarea></td>
					</tr>
					<tr>
						<th scope="row"><label for="contact_page_slug"><?php esc_html_e('Contact page slug', 'strikescope-wp'); ?></label></th>
						<td>
							<input name="contact_page_slug" id="contact_page_slug" type="text" class="regular-text" value="<?php echo esc_attr((string) $branding['contact_page_slug']); ?>">
							<p class="description"><?php esc_html_e('Slug of the page with [strikescope_form]. Used for auto-mount and %%CONTACT_PAGE_URL%%.', 'strikescope-wp'); ?></p>
						</td>
					</tr>
					<?php
					$color_fields = array(
						'primary'    => __('Primary', 'strikescope-wp'),
						'accent'     => __('Accent', 'strikescope-wp'),
						'background' => __('Background', 'strikescope-wp'),
						'secondary'  => __('Secondary', 'strikescope-wp'),
						'border'     => __('Border', 'strikescope-wp'),
						'muted'      => __('Muted text', 'strikescope-wp'),
					);
					foreach ($color_fields as $key => $label) :
						$val = $colors[ $key ] ?? '';
						?>
					<tr>
						<th scope="row"><label for="color_<?php echo esc_attr($key); ?>"><?php echo esc_html($label); ?></label></th>
						<td><input name="colors[<?php echo esc_attr($key); ?>]" id="color_<?php echo esc_attr($key); ?>" type="text" class="regular-text" value="<?php echo esc_attr((string) $val); ?>" placeholder="#2563eb"></td>
					</tr>
					<?php endforeach; ?>
				</table>
				<?php submit_button(__('Save branding', 'strikescope-wp')); ?>
			</form>
			<h2><?php esc_html_e('Preview', 'strikescope-wp'); ?></h2>
			<div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;max-width:960px;">
				<div style="border:1px solid #ccd0d4;border-radius:8px;padding:16px;background:#fff;">
					<h3><?php esc_html_e('Form header mock', 'strikescope-wp'); ?></h3>
					<div style="background:<?php echo esc_attr($colors['background'] ?? '#fff'); ?>;padding:12px;border:1px solid <?php echo esc_attr($colors['border'] ?? '#dbeafe'); ?>;border-radius:8px;">
						<img src="<?php echo esc_url(self::logo_url()); ?>" alt="" style="max-width:180px;display:block;margin:0 auto 8px;">
						<p style="text-align:center;margin:0;color:<?php echo esc_attr($colors['primary'] ?? '#2563eb'); ?>;font-weight:700;"><?php echo esc_html((string) $branding['company_name']); ?></p>
					</div>
				</div>
				<div style="border:1px solid #ccd0d4;border-radius:8px;padding:16px;background:#fff;">
					<h3><?php esc_html_e('Email snippet', 'strikescope-wp'); ?></h3>
					<div style="border:1px solid #eee;padding:12px;"><?php echo wp_kses_post($preview_email); ?></div>
				</div>
			</div>
		</div>
		<?php
	}

	private static function save_from_post(): array {
		$defaults = self::default_branding();
		$colors   = isset($_POST['colors']) && is_array($_POST['colors']) ? wp_unslash($_POST['colors']) : array();
		$clean_colors = array();
		foreach ($defaults['colors'] as $key => $fallback) {
			$raw = isset($colors[ $key ]) ? (string) $colors[ $key ] : $fallback;
			$clean_colors[ $key ] = sanitize_hex_color($raw) ?: $fallback;
		}
		$branding = array(
			'company_name'       => sanitize_text_field(wp_unslash($_POST['company_name'] ?? $defaults['company_name'])),
			'logo_attachment_id' => absint($_POST['logo_attachment_id'] ?? 0),
			'phone_display'      => sanitize_text_field(wp_unslash($_POST['phone_display'] ?? '')),
			'footer_text'        => sanitize_textarea_field(wp_unslash($_POST['footer_text'] ?? $defaults['footer_text'])),
			'contact_page_slug'  => sanitize_title(wp_unslash($_POST['contact_page_slug'] ?? $defaults['contact_page_slug'])),
			'colors'             => $clean_colors,
		);
		update_option(STRIKESCOPE_BRANDING_OPTION, $branding, false);
		return $branding;
	}
}

// Extend merge tag application in core emails.
add_filter(
	'strikescope_form_apply_merge_tags',
	static function ($html, $tags) {
		if (!is_string($html)) {
			return $html;
		}
		return StrikeScope_Branding::apply_merge_tags($html, is_array($tags) ? $tags : array());
	},
	5,
	2
);
