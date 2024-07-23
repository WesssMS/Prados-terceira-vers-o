<?php
namespace TEC\Tickets_Plus\Emails;

/**
 * Class Settings
 *
 * @since   5.6.6
 *
 * @package \TEC\Tickets_Plus\Emails
 */

class Settings {

	/**
	 * The option key for the email footer credit.
	 *
	 * @since 5.6.6
	 *
	 * @var string
	 */
	public static $option_footer_credit = 'tec-tickets-emails-footer-credit';

	/**
	 * Add footer credit setting to main Tickets Emails settings page.
	 *
	 * @since 5.6.6
	 *
	 * @param array $fields Array of settings fields from Tickets Emails.
	 *
	 * @return array $fields Modified array of settings fields.
	 */
	public function add_footer_credit_setting( $fields ): array {

		$fields[ self::$option_footer_credit ] = [
			'type'            => 'checkbox_bool',
			'label'           => esc_html__( 'Footer Credit', 'event-tickets-plus' ),
			'tooltip'         => esc_html__( 'Include "Powered by Event Tickets" in the footer', 'event-tickets-plus' ),
			'default'         => true,
			'validation_type' => 'boolean',
		];

		return $fields;
	}
}
