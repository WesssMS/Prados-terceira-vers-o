<?php

namespace TEC\Tickets_Plus\Admin\Tabs;

use Tribe\Tickets\Admin\Settings as Plugin_Settings;
use Tribe\Tickets\Plus\Attendee_Registration\IAC;

/**
 * Class Attendee_Registration
 *
 * @package TEC\Tickets_Plus\Admin\Tabs
 *
 * @since 5.5.1
 */
Class Attendee_Registration {

	/**
	 * Slug for the tab.
	 *
	 * @since 5.5.1
	 *
	 * @var string
	 */
	public static $slug = 'attendee-registration';

	/**
	 * Register the Tab.
	 *
	 * @param string Admin page id.
	 *
	 * @since 5.5.1
	 */
	public function register_tab( $admin_page ) {
		if ( ! empty( $admin_page ) && Plugin_Settings::$settings_page_id !== $admin_page ) {
			return;
		}

		// If we fail here we dont have attendee registration so we bail.
		try {
			/** @var \Tribe__Tickets__Attendee_Registration__Main $attendee_registration */
			$attendee_registration = tribe( 'tickets.attendee_registration' );
		} catch ( \Exception $e ) {
			return;
		}

		$tab_settings = [
			'priority'  => 30,
			'fields'    => $this->get_fields(),
			'show_save' => true,
		];

		/**
		 * Filter the tab settings options.
		 *
		 * @since 5.5.1
		 *
		 * @param array Key value pairs of setting options.
		 */
		$tab_settings = apply_filters( 'tec_tickets_plus_attendee_registration_tab_settings', $tab_settings );

		new \Tribe__Settings_Tab( static::$slug, esc_html__( 'Attendee Registration', 'event-tickets-plus' ), $tab_settings );
	}

	/**
	 * Register tab ID for network mode support.
	 *
	 * @since 5.5.1
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
	 * @since 5.5.1
	 *
	 * @return array[] Key value pair for setting options.
	 */
	public function get_fields(): array {
		$ar_page_description = __( 'Optional: select an existing page to act as your attendee registration page. <strong>Requires</strong> use of the `[tribe_attendee_registration]` shortcode and overrides the above template and URL slug.', 'event-tickets-plus' );

		try {
			/** @var \Tribe__Tickets__Attendee_Registration__Main $attendee_registration */
			$attendee_registration = tribe( 'tickets.attendee_registration' );
		} catch ( \Exception $e ) {
			return [];
		}

		$ar_page = $attendee_registration->get_attendee_registration_page();

		// This is hooked too early for has_shortcode() to work properly, so regex to the rescue!
		if ( ! empty( $ar_page ) && ! preg_match( '/\[tribe_attendee_registration\/?\]/', $ar_page->post_content ) ) {
			$ar_page_description = __( '<span class="notice notice-error">Selected page <strong>must</strong> use the `[tribe_attendee_registration]` shortcode. While the shortcode is missing the default redirect will be used.</span>', 'event-tickets-plus' );
		}

		$settings_start = [

			'info-start'           => [
				'type' => 'html',
				'html' => '<div class="tec-settings-header">',
			],
			'info-box-title'       => [
				'type' => 'html',
				'html' => '<h2>' . _x( 'Attendee Registration Settings', 'Attendee Registration tab header', 'event-tickets-plus' ) . '</h2>',
			],
			'info-box-description' => [
				'type' => 'html',
				'html' => '<p>'
						. sprintf(
							// Translators: %1$s: opening of HTML link. %2$s: closing of HTML link.
							__( 'Collecting information about your attendees — such as their names and emails addresses — can give you insights about who is coming to your event, how to communicate with them, and even create personalized experiences for them. %1$sLearn more%2$s', 'event-tickets-plus' ),
							'<a href="https://evnt.is/attendee-registration" target="_blank" rel="noopener noreferrer">',
							'</a>'
						)
						. '</p>',
			],
			'info-end'             => [
				'type' => 'html',
				'html' => '</div>',
			],
			'settings-start'       => [
				'type' => 'html',
				'html' => '<div class="tribe-settings-form-wrap tec-tickets-plus-settings-ar">',
			],
		];

		$settings_end = [
			'settings-end' => [
				'type' => 'html',
				'html' => '</div>',
			],
		];

		$ar_fields = [
			'ar-heading' => [
				'type' => 'html',
				'html' => '<h3>' . __( 'Attendee Registration', 'event-tickets-plus' ) . '</h3>',
			],
			'ticket-attendee-modal'         => [
				'type'            => 'toggle',
				'label'           => esc_html__( 'Attendee Registration Modal ', 'event-tickets-plus' ),
				'tooltip'         => sprintf(
				// Translators: %1$s: dynamic "tickets" text. %2$s: opening of HTML link. %3$s: closing of HTML link.
					esc_html_x(
						'Enabling the Attendee Registration Modal provides a new sales flow for purchasing %1$s that include Attendee Registration. [%2$sLearn more%3$s]',
						'checkbox to enable Attendee Registration Modal',
						'event-tickets-plus'
					),
					tribe_get_ticket_label_plural_lowercase( 'modal_notice_tooltip' ),
					'<a href="https://evnt.is/attendee-registration" target="_blank" rel="noopener noreferrer">',
					'</a>'
				),
				'size'            => 'medium',
				'default'         => true,
				'validation_type' => 'boolean',
				'attributes'      => [ 'id' => 'ticket-attendee-enable-modal' ],
			],
			'ticket-attendee-info-slug'     => [
				'type'                => 'text',
				'label'               => esc_html__( 'Attendee Registration URL slug', 'event-tickets-plus' ),
				'tooltip'             => esc_html__( 'The slug used for building the URL for the Attendee Registration Info page.', 'event-tickets-plus' ),
				'size'                => 'medium',
				'default'             => $attendee_registration->get_slug(),
				'validation_callback' => 'is_string',
				'validation_type'     => 'slug',
			],
			'ticket-attendee-info-template' => [
				'type'            => 'dropdown',
				'label'           => esc_html__( 'Attendee Registration template', 'event-tickets-plus' ),
				'tooltip'         => esc_html__( 'Choose a page template to control the appearance of your attendee registration page.', 'event-tickets-plus' ),
				'validation_type' => 'options',
				'size'            => 'large',
				'default'         => 'default',
				'options'         => $this->get_template_options(),
			],
			'ticket-attendee-page-id'       => [
				'type'            => 'dropdown',
				'label'           => esc_html__( 'Attendee Registration page', 'event-tickets-plus' ),
				'tooltip'         => $ar_page_description,
				'validation_type' => 'options',
				'size'            => 'large',
				'default'         => 'default',
				'options'         => $this->get_page_options(),
			],
		];

		return array_merge( $settings_start, $this->get_iac_fields(), $ar_fields, $settings_end );
	}

	/**
	 * Get the IAC options field.
	 *
	 * @since 5.5.1
	 *
	 * @return array Key value pair for IAC options.
	 */
	public function get_iac_fields(): array {
		$iac_tooltip = esc_html_x(
			'The default Individual Attendee Collection option when you create new tickets, which may be customized per ticket.',
			'tooltip for Individual Attendee Collection setting',
			'event-tickets-plus'
		);

		/** @var IAC $iac */
		$iac = tribe( 'tickets-plus.attendee-registration.iac' );

		$iac_option_name = $iac->get_default_iac_setting_option_name();
		$iac_default     = $iac->get_default_iac_setting();
		$iac_options     = $iac->get_iac_setting_options();

		$options = [
			'iac-heading' => [
				'type' => 'html',
				'html' => '<h3>' . __( 'Individual Attendee Collection', 'event-tickets-plus' ) . '</h3>',
			],
			$iac_option_name => [
				'type'            => 'dropdown',
				'label'           => esc_html_x( 'Individual Attendee Collection Default Setting', 'Individual Attendee Collection settings label', 'event-tickets-plus' ),
				'tooltip'         => $iac_tooltip,
				'validation_type' => 'options',
				'size'            => 'large',
				'default'         => $iac_default,
				'options'         => $iac_options,
			],
		];

		return $options;
	}

	/**
	 * Get the page template options.
	 *
	 * @since 5.5.1
	 *
	 * @return array Key value pair for available templates.
	 */
	public function get_template_options() : array {
		$template_options = [
			'default' => esc_html_x( 'Default Page Template', 'dropdown option', 'event-tickets-plus' ),
		];

		if ( class_exists( 'Tribe__Events__Main' ) ) {
			$template_options['same'] = esc_html__( 'Same as Event Page Template', 'event-tickets-plus' );
		}

		$templates = get_page_templates();

		ksort( $templates );

		foreach ( array_keys( $templates ) as $template ) {
			$template_options[ $templates[ $template ] ] = $template;
		}

		return $template_options;
	}

	/**
	 * Get the pages option.
	 *
	 * @since 5.5.1
	 *
	 * @return array Key value pair for pages options.
	 */
	public function get_page_options() : array {

		// Show invalid option, if no pages are created.
		$page_options = [ '' => esc_html__( 'You must create a page before using this functionality', 'event-tickets-plus' ) ];

		$pages = get_pages();

		if ( $pages ) {
			$page_options = [ '' => esc_html__( 'Choose a page or leave blank.', 'event-tickets-plus' ) ];

			foreach ( $pages as $page ) {
				$page_options[ $page->ID ] = $page->post_title;
			}
		}

		return $page_options;
	}
}