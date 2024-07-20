<?php
/**
 * Addons for order item
 *
 * @var array $product_addons Product addons available for the item
 * @var array $addon_values   Existing addon values for the item
 *
 * @version  6.9.0
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<div>
	<?php
	foreach ( $product_addons as $addon ) {
		if ( ! isset( $addon[ 'field_name' ] ) ) {
			continue;
		}

		// If type is heading, skip.
		if ( 'heading' === $addon[ 'type' ] ) {
			continue;
		}

		$key = $addon[ 'id' ] ?? sanitize_text_field( $addon[ 'name' ] );
		$args = array(
			'addon'               => $addon,
			'required'            => WC_Product_Addons_Helper::is_addon_required( $addon ),
			'name'                => $addon[ 'name' ],
			'description'         => $addon[ 'description' ],
			'display_description' => WC_Product_Addons_Helper::should_display_description( $addon ),
			'type'                => $addon[ 'type' ],
			'value'               => $addon_values[ $key ] ?? null,
		);
		extract( $args );  // @codingStandardsIgnoreLine

		include WC_PRODUCT_ADDONS_PLUGIN_PATH . '/includes/admin/views/addons/addon-start.php';

		switch ( $addon[ 'type' ] ) {
			case 'multiple_choice':
				switch ( $addon[ 'display' ] ) {
					case 'images':
						$field_type = 'image';
						break;
					case 'radiobutton':
						$field_type = 'radiobutton';
						break;
					case 'select':
						$field_type = 'select';
						break;
				}
				break;
			default:
				$field_type = $addon[ 'type' ];
		}
		include WC_PRODUCT_ADDONS_PLUGIN_PATH . '/includes/admin/views/addons/' . $field_type . '.php';

		include WC_PRODUCT_ADDONS_PLUGIN_PATH . '/includes/admin/views/addons/addon-end.php';
	}
	?>
</div>
