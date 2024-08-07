<?php

use Tribe__Utils__Array as Arr;

class Tribe__Tickets_Plus__Meta__Render {
	public function __construct() {
		add_filter( 'tribe_tickets_attendee_table_columns', [ $this, 'insert_details_column' ], 20 );
		add_filter( 'tribe_events_tickets_attendees_table_column', [ $this, 'populate_details_column' ], 10, 3 );
		add_action( 'tribe_tickets_ticket_email_ticket_bottom', [ $this, 'ticket_email_meta' ] );
		add_action( 'event_tickets_attendees_table_after_row', [ $this, 'table_meta_data' ] );
		add_action( 'tribe_template_after_include:tickets/admin-views/attendees/modal/attendee/attendee-info', [ $this, 'modal_attendee_meta' ], 10, 3 );
	}

	/**
	 * Register an additional column, to be added next to 'primary_info' column,
	 * to allow access to attendee meta details.
	 *
	 * @param array $columns
	 *
	 * @return array
	 */
	public function insert_details_column( array $columns ) {
		// We only show the details column on the front-end, for Community Tickets as we don't have modal.
		if ( is_admin() ) {
			return $columns;
		}

		return Tribe__Main::array_insert_after_key( 'primary_info', $columns, array(
			'meta_details' => esc_html_x( 'Details', 'attendee table meta', 'event-tickets-plus' ),
		) );
	}

	/**
	 * Populates the meta details column.
	 *
	 * @param string $value
	 * @param array  $item
	 * @param string $column
	 *
	 * @return string
	 */
	public function populate_details_column( $value, $item, $column ) {
		// We only show the details column on the front-end, for Community Tickets as we don't have modal.
		if ( is_admin() ) {
			return $value;
		}

		if ( 'meta_details' !== $column ) {
			return $value;
		}

		$toggle = $this->get_meta_toggle( $item );

		return $toggle;
	}

	public function get_meta_toggle( array $item ) {
		$meta_data = Tribe__Tickets_Plus__Meta::get_attendee_meta_fields( absint( $item['product_id'] ), $item['attendee_id'] );

		if ( ! $meta_data ) {
			return '<span class="event-tickets-no-meta-toggle">&ndash;</span>';
		}


		$view_details = sprintf( esc_html__( 'View details %s', 'event-tickets-plus' ), '&#9660;' ); // "&#9660;" := downward arrow
		$hide_details = sprintf( esc_html__( 'Hide details %s', 'event-tickets-plus' ), '&#9650;' ); // "&#9650;" := upward arrow

		return '
			<a href="#" class="event-tickets-meta-toggle">
				<span class="event-tickets-meta-toggle-view">' . $view_details . '</span>
				<span class="event-tickets-meta-toggle-hide">' . $hide_details . '</span>
			</a>
		';
	}

	public function table_meta_data( $item ) {
		if ( ! isset( $item['product_id'] ) || ! isset( $item['attendee_id'] ) ) {
			return;
		}

		wp_enqueue_style( 'event-tickets-meta' );
		wp_enqueue_script( 'event-tickets-meta-report' );

		// Bail if is in the admin.
		if ( is_admin() ) {
			return;
		}

		$meta_fields = Tribe__Tickets_Plus__Main::instance()->meta()->get_meta_fields_by_ticket( $item['product_id'] );

		$meta_data     = Tribe__Tickets_Plus__Meta::get_attendee_meta_fields( $item['product_id'], $item['attendee_id'] );
		$orphaned_data = (array) $meta_data;

		$valid_meta_html    = '';
		$orphaned_meta_html = '';

		foreach ( $meta_fields as $field ) {
			if ( 'checkbox' === $field->type && isset( $field->extra['options'] ) ) {
				$values = [];

				foreach ( $field->extra['options'] as $option ) {
					if ( '' === $option ) {
						continue;
					}

					// Support longer options by using the hash of the string.
					$key = $field->slug . '_' . md5( sanitize_title( $option ) );

					if ( ! isset( $meta_data[ $key ] ) ) {
						// Support existing fields that did not save with md5 hash.
						$key = $field->slug . '_' . sanitize_title( $option );
					}

					if ( isset( $meta_data[ $key ] ) ) {
						$values[] = $meta_data[ $key ];

						unset( $orphaned_data[ $key ] );
					}
				}

				// There were no values for this checkbox.
				if ( empty( $values ) ) {
					continue;
				}

				$value = implode( ', ', $values );
			} elseif ( isset( $meta_data[ $field->slug ] ) ) {
				$value = $meta_data[ $field->slug ];

				unset( $orphaned_data[ $field->slug ] );
			} else {
				continue;
			}

			if ( $this->is_date_format_field( $field ) ) {
				$value = $this->format_date_value( $value, $field );
			}

			if ( '' === trim( $value ) ) {
				$value = '&nbsp;';
			}

			$valid_meta_html .= sprintf(
				'
					<dt class="event-tickets-meta-label_%1$s">
						%2$s
					</dt>
					<dd class="event-tickets-meta-data_%1$s">
						%3$s
					</dd>
				',
				sanitize_html_class( $field->slug ),
				wp_kses_post( $field->label ),
				wp_kses_post( $value )
			);
		}

		if ( ! empty( $valid_meta_html ) ) {
			$valid_meta_html = '<dl>' . $valid_meta_html . '</dl>';
		}

		/**
		 * Allow filtering of the orphaned data shown on the page.
		 *
		 * @since 5.2.0
		 *
		 * @param array                                              $orphaned_data The orphaned data.
		 * @param Tribe__Tickets_Plus__Meta__Field__Abstract_Field[] $meta_fields   The list of meta field objects for the ticket.
		 * @param array                                              $meta_data     The meta data for all fields.
		 */
		$orphaned_data = apply_filters( 'tribe_tickets_plus_meta_render_table_meta_data_orphaned_data', $orphaned_data, $meta_fields, $meta_data );

		foreach ( $orphaned_data as $key => $value ) {
			// We have to skip these values as they cannot be accurately displayed.
			if ( is_array( $value ) || is_object( $value ) ) {
				continue;
			}

			$value = trim( (string) $value );

			// There is no value for this meta.
			if ( '' === $value ) {
				continue;
			}

			$orphaned_meta_html .= sprintf(
				'
					<dt class="event-tickets-orphaned-meta-label event-tickets-orphaned-meta-label_%1$s">
						%2$s
					</dt>
					<dd class="event-tickets-orphaned-meta-data event-tickets-orphaned-meta-data_%1$s">
						%3$s
					</dd>
				',
				sanitize_html_class( $key ),
				wp_kses_post( $key ),
				wp_kses_post( $value )
			);
		}

		if ( ! empty( $orphaned_meta_html ) ) {
			$orphaned_meta_html = '
				<h4>' . esc_html_x( 'Other attendee data:', 'orphaned attendee meta data', 'event-tickets-plus' ) . '</h4>
				<dl>' . $orphaned_meta_html . '</dl>
			';
		}

		?>
		<tr class="event-tickets-meta-row">
			<th></th>
			<td colspan="6">
				<?php echo $valid_meta_html; ?>
				<?php echo $orphaned_meta_html; ?>
			</td>
		</tr>
		<?php
	}

	/**
	 * Inject custom meta in to tickets
	 *
	 * @param array $item Attendee data
	 */
	public function ticket_email_meta( $item ) {
		if ( ! isset( $item['product_id'] ) ) {
			return;
		}

		$meta_fields = Tribe__Tickets_Plus__Main::instance()->meta()->get_meta_fields_by_ticket( $item['product_id'] );

		/**
		 * Filters the ticket meta fields to be included in the tickets email
		 *
		 * @since 4.5.4
		 *
		 * @param array $meta_fields
		 * @param array $item
		 */
		$meta_fields = apply_filters( 'tribe_event_tickets_plus_email_meta_fields', $meta_fields, $item );

		$meta_data = Tribe__Tickets_Plus__Meta::get_attendee_meta_fields( $item['qr_ticket_id'], $item['attendee_id'] );

		if ( ! $meta_fields || ! $meta_data ) {
			return;
		}

		?>
		<table class="inner-wrapper" border="0" cellpadding="0" cellspacing="0" width="620" bgcolor="#f7f7f7" style="margin:0 auto !important; width:620px; padding:0;">
			<tr>
				<td valign="top" class="ticket-content" align="left" width="580" border="0" cellpadding="20" cellspacing="0" style="padding:20px; background:#f7f7f7;" colspan="2">
					<h6 style="color:#909090 !important; margin:0 0 4px 0; font-family: 'Helvetica Neue', Helvetica, sans-serif; text-transform:uppercase; font-size:13px; font-weight:700 !important;"><?php esc_html_e( 'Attendee Information', 'event-tickets-plus' ); ?></h6>
				</td>
			</tr>
			<?php
			foreach ( $meta_fields as $field ) {
				if ( 'checkbox' === $field->type && isset( $field->extra['options'] ) ) {
					$values = [];

					foreach ( $field->extra['options'] as $option ) {
						if ( '' === $option ) {
							continue;
						}

						// Support longer options by using the hash of the string.
						$key = $field->slug . '_' . md5( sanitize_title( $option ) );

						if ( ! isset( $meta_data[ $key ] ) ) {
							// Support existing fields that did not save with md5 hash.
							$key = $field->slug . '_' . sanitize_title( $option );
						}

						if ( isset( $meta_data[ $key ] ) ) {
							$values[] = $meta_data[ $key ];
						}
					}

					// There were no values for this checkbox.
					if ( empty( $values ) ) {
						continue;
					}

					$value = implode( ', ', $values );
				} elseif ( isset( $meta_data[ $field->slug ] ) ) {
					$value = $meta_data[ $field->slug ];
				} else {
					continue;
				}

				if ( $this->is_date_format_field( $field ) ) {
					$value = $this->format_date_value( $value, $field );
				}

				if ( '' === trim( $value ) ) {
					$value = '&nbsp;';
				}
				?>
				<tr>
					<th valign="top" class="event-tickets-meta-label_<?php echo sanitize_html_class( $field->slug ); ?>" align="left" border="0" cellpadding="20" cellspacing="0" style="padding:0 20px; background:#f7f7f7;min-width:100px;">
						<?php echo wp_kses_post( $field->label ); ?>
					</th>
					<td valign="top" class="event-tickets-meta-data_<?php echo sanitize_html_class( $field->slug ); ?>" align="left" border="0" cellpadding="20" cellspacing="0" style="padding:0 20px; background:#f7f7f7;">
						<?php echo wp_kses_post( $value ); ?>
					</td>
				</tr>
				<?php
			}
			?>
		</table>
		<?php
	}

	/**
	 * Check if the field is a date time format field.
	 *
	 * @since 5.6.5
	 *
	 * @param Tribe__Tickets_Plus__Meta__Field__Abstract_Field $field The field object.
	 *
	 * @return bool Whether the field is a date format field.
	 */
	public function is_date_format_field( $field ) {
		$date_fields = [
			Tribe__Tickets_Plus__Meta__Field__Birth::get_identifier(),
			Tribe__Tickets_Plus__Meta__Field__Datetime::get_identifier(),
		];

		/**
		 * Filters the date fields.
		 *
		 * @since 5.6.5
		 *
		 * @param array $date_fields The date fields array.
		 */
		$date_fields = apply_filters( 'tec_tickets_plus_meta_date_format_fields', $date_fields );

		return in_array( $field->type, $date_fields, true );
	}

	/**
	 * Format the date time field value according to the settings for date and time format.
	 *
	 * @since 5.6.5
	 *
	 * @param string $value The field value.
	 * @param Tribe__Tickets_Plus__Meta__Field__Abstract_Field $field The field object.
	 *
	 * @return string The formatted date time field value.
	 */
	public function format_date_value( $value, $field ) {
		$format = tribe_get_date_format( true );

		$formatted_item = tribe_format_date( $value, false, $format );

		/**
		 * Filters the formatted date value.
		 *
		 * @since 5.6.5
		 *
		 * @param string $formatted_item The formatted date value.
		 * @param string $value The field value.
		 * @param Tribe__Tickets_Plus__Meta__Field__Abstract_Field $field The field object.
		 */
		return apply_filters( 'tec_tickets_plus_meta_date_type_formatted_value', $formatted_item, $value, $field );
	}

	/**
	 * Renders the attendee registration fields in the modal.
	 *
	 * @since 5.10.1
	 *
	 * @param string                       $file        Complete path to include the PHP File.
	 * @param string[]                     $name        Template name.
	 * @param Tribe__Tickets__Admin__Views $et_template Current instance of the Tribe__Template.
	 */
	public function modal_attendee_meta( string $file, array $name, Tribe__Tickets__Admin__Views $et_template ): void {
		$template_vars = $et_template->get_local_values();

		if ( empty( $template_vars['ticket_id'] ) || empty( $template_vars['attendee_id'] ) ) {
			return;
		}

		/** @var \Tribe__Tickets_Plus__Meta $meta */
		$meta          = tribe( 'tickets-plus.meta' );
		$attendee_meta = $meta->get_attendee_meta_values( $template_vars['ticket_id'], $template_vars['attendee_id'] );

		$template_vars['attendee_meta'] = $attendee_meta;

		$admin_views = tribe( 'tickets-plus.admin.views' );
		$admin_views->template( 'attendees/modal/attendee/attendee-fields', $template_vars );
	}
}
