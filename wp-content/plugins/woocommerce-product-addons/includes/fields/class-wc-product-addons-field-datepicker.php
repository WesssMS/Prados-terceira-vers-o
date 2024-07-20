<?php
/**
 * Date picker field
 *
 * @since   6.8.0
 * @version 6.8.0
 */
class WC_Product_Addons_Field_Datepicker extends WC_Product_Addons_Field {

	/**
	 * The addon field
	 *
	 * @var array
	 */
	public $addon;

	/**
	 * The selected value/date.
	 *
	 * @var string
	 */
	public $value;

	/**
	 * The timestamp of the selected value/date.
	 *
	 * @var int
	 */
	public $timestamp;

	/**
	 * The offset of the selected value/date.
	 *
	 * @var float
	 */
	public $offset;

	/**
	 * Constructor
	 */
	public function __construct( $addon, $value = '', $timestamp = '', $offset = '' ) {
		$this->addon     = $addon;
		$this->value     = $value;
		$this->timestamp = $timestamp;
		$this->offset    = $offset;
	}

	/**
	 * Validate an addon
	 * @return bool pass, or WP_Error
	 */
	public function validate() {
		$posted = isset( $this->value ) ? $this->value : '';

		if ( ! empty( $this->addon['required'] ) && '' === $posted ) {
			/* translators: %s Addon name */
			return new WP_Error( 'error', esc_html( sprintf( __( '"%s" is a required field.', 'woocommerce-product-addons' ), $this->addon['name'] ) ) );
		}

		return true;
	}

	/**
	 * Process this field after being posted
	 * @return array on success, WP_ERROR on failure
	 */
	public function get_cart_item_data() {
		$cart_item_data = array();
		$adjust_price   = $this->addon['adjust_price'];
		$this_data      = array(
			'name'       => sanitize_text_field( $this->addon[ 'name' ] ),
			'price'      => '1' != $adjust_price ? 0 : floatval( sanitize_text_field( $this->addon[ 'price' ] ) ),
			'value'      => '',
			'display'    => '',
			'field_name' => $this->addon[ 'field_name' ],
			'field_type' => $this->addon[ 'type' ],
			'id'         => isset( $this->addon[ 'id' ] ) ? $this->addon[ 'id' ] : 0,
			'price_type' => $this->addon[ 'price_type' ],
			'timestamp'  => $this->timestamp,
			'offset'     => $this->offset
		);

		if ( ! empty( $this->value ) ) {
			$this_data['value'] = wc_clean( $this->value );

			if ( ! empty( $this->timestamp ) ) {
				$this_data['value'] = date_i18n( get_option( 'date_format' ), WC_Product_Addons_Helper::wc_pao_convert_timestamp_to_gmt_offset( $this->timestamp, -1 * (float) $this->offset ) );
			}

			$cart_item_data[] = $this_data;
		}

		return $cart_item_data;
	}
}
