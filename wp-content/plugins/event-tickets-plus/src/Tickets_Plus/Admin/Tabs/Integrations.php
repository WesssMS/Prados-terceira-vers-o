<?php

namespace TEC\Tickets_Plus\Admin\Tabs;

use Tribe\Tickets\Admin\Settings as Plugin_Settings;

/**
 * Class Integrations
 *
 * @package TEC\Tickets_Plus\Admin\Tabs
 *
 * @since 5.6.2
 */
Class Integrations {

	/**
	 * Slug for the tab.
	 *
	 * @since 5.6.2
	 *
	 * @var string
	 */
	public static $slug = 'integrations';

	/**
	 * Register the Tab.
	 *
	 * @param string Admin page id.
	 *
	 * @since 5.6.2
	 */
	public function register_tab( $admin_page ) {
		if ( ! empty( $admin_page ) && Plugin_Settings::$settings_page_id !== $admin_page ) {
			return;
		}

		$tab_settings = [
			'priority'  => 35,
			'fields'    => $this->get_fields(),
			'show_save' => true,
		];

		/**
		 * Filter the tab settings options.
		 *
		 * @since 5.6.2
		 *
		 * @param array Key value pairs of setting options.
		 */
		$tab_settings = apply_filters( 'tec_tickets_plus_integrations_tab_settings', $tab_settings );

		new \Tribe__Settings_Tab( static::$slug, esc_html__( 'Integrations', 'event-tickets-plus' ), $tab_settings );
	}

	/**
	 * Register tab ID for network mode support.
	 *
	 * @since 5.6.2
	 *
	 * @param array $tabs Array of tabs IDs for the Events settings page.
	 *
	 * @return array
	 */
	public function register_tab_id( array $tabs ): array {
		$tabs[] = static::$slug;
		return $tabs;
	}

	/**
	 * Gets the settings.
	 *
	 * @since 5.6.2
	 *
	 * @return array[] Key value pair for setting options.
	 */
	public function get_fields(): array {
		$settings_start = [
			'info-start'           => [
				'type' => 'html',
				'html' => '<div class="tec-settings-header">',
			],
			'info-box-title'       => [
				'type' => 'html',
				'html' => '<h2>' . _x( 'Integrations', 'Integrations tab header', 'event-tickets-plus' ) . '</h2>',
			],
			'info-box-description' => [
				'type' => 'html',
				'html' => '<p>' .
					esc_html__(
						'Event Tickets and its add-ons integrate with other online tools and services to bring you additional features. Use the settings below to connect to our mobile app and manage your integrations.',
						'event-tickets-plus'
					)
					. '</p>',
			],
			'info-end'             => [
				'type' => 'html',
				'html' => '</div>',
			],
			'settings-start' => [
				'type' => 'html',
				'html' => '<div class="tribe-settings-form-wrap">',
			],
		];

		$settings_end = [
			'info-end' => [
				'type' => 'html',
				'html' => '</div>',
			]
		];

		$fields = apply_filters( 'tec_tickets_plus_integrations_tab_fields', [] );

		return array_merge( $settings_start, $fields, $settings_end );
	}
}