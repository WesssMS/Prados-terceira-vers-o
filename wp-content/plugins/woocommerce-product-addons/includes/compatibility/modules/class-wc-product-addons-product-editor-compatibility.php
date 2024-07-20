<?php
/**
 * WC_Product_Addons_Product_Editor_Compatibility class
 *
 * @package  Woo Product Add-Ons
 * @since    x.x.x
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * WooCommerce Blocks Compatibility.
 *
 * @version x.x.x
 */
class WC_Product_Addons_Product_Editor_Compatibility {

	/**
	 * Initialize.
	 */
	public static function init() {
		add_action( 'woocommerce_block_template_area_product-form_after_add_block_linked-products', array( __CLASS__, 'add_addons_group' ) );
		add_filter( 'woocommerce_rest_prepare_product_object', array( __CLASS__, 'rest_handle_product_response' ), 90, 3 );
		add_filter( 'woocommerce_rest_pre_insert_product_object', array( __CLASS__, 'rest_handle_product_save' ), 10, 2 );

		add_action( 'init', array( __CLASS__, 'register_blocks' ) );
	}

	/**
	 * Register product editor blocks.
	 */
	public static function register_blocks() {
		if ( ! class_exists( 'Automattic\WooCommerce\Admin\PageController' ) || ! Automattic\WooCommerce\Admin\PageController::is_admin_page() ) {
			return;
		}

		if ( ! class_exists( 'Automattic\WooCommerce\Admin\Features\ProductBlockEditor\BlockRegistry' ) ) {
			return;
		}

		// Add register calls here.
		Automattic\WooCommerce\Admin\Features\ProductBlockEditor\BlockRegistry::get_instance()->register_block_type_from_metadata( WC_PRODUCT_ADDONS_PLUGIN_PATH . '/assets/dist/admin/product-editor/blocks/product-addons/block.json' );
	}

	/**
	 * Add Add-Ons group after Linked Products.
	 *
	 * @param BlockInterface $linked_products_group The template interface.
	 */
	public static function add_addons_group( $linked_products_group ) {

		$parent = $linked_products_group->get_parent();

		if ( ! method_exists( $parent, 'add_group' ) ) {
			return;
		}

		$addons_group = $parent->add_group(
			array(
				'id'         => 'wc-pao-addons',
				'order'      => $linked_products_group->get_order() + 5,
				'attributes' => array(
					'title' => __( 'Add-ons', 'woocommerce-product-addons' ),
				),
				'hideConditions' => array(
					array(
						// Linked products do not show up for variation templates, but added a defensive check to be on the safe side.
						'expression' => "postType === 'product_variation'",
					)
				),
			)
		);

		$global_addons_section = $addons_group->add_section(
			array(
				'id'             => 'wc-pao-addons-section-global-addons',
				'attributes'     => array(
					'title' => __( 'Global add-ons', 'woocommerce-product-addons' ),
				),
			)
		);

		$global_addons_section->add_block(
			array(
				'id'         => 'wc-pao-addons-section-global-addons-toggle',
				'blockName'  => 'woocommerce/product-toggle-field',
				'attributes' => array(
					'label'          => __(
						'Apply global add-ons to this product',
						'woocommerce-product-addons'
					),
					'help'           => sprintf( __(
						'Manage add-ons for multiple products using <a href="%s">global add-ons</a>.',
						'woocommerce-product-addons'
					), 'edit.php?post_type=product&page=addons' ),
					'property'       => '_product_addons_exclude_global',
					'checkedValue'   => 'yes',
					'uncheckedValue' => 'no',
				),
			)
		);

		$custom_addons_section = $addons_group->add_section(
			array(
				'id' => 'wc-pao-addons-section-custom-addons',
				'attributes' => array(
					'title'       => __( 'Custom add-ons', 'woocommerce-product-addons' ),
					'description' => __( 'Add paid or free options to this product.', 'woocommerce-product-addons' ),
				),
			)
		);

		$custom_addons_section->add_block(
			array(
				'id'        => 'wc-pao-addons-section-custom-addons-list',
				'blockName' => 'woocommerce-product-addons/product-addons',
				'order'     => 20,
			)
		);
	}

	/**
	 * Add the meta data to the rest response.
	 *
	 * @param WP_REST_Response $response The response.
	 * @param WC_Product       $product The product.
	 * @param WP_REST_Request  $request The request.
	 *
	 * @return array
	 */
	public static function rest_handle_product_response( $response, $product, $request ) {

		if ( 'edit' !== $request->get_param( 'context' ) ) {
			return $response;
		}

		// Handle addons.
		$addons                              = $product->get_meta( '_product_addons', true );
		$response->data[ '_product_addons' ] = empty( $addons ) ? array() : maybe_unserialize( $addons );

		// Handle negative logic.
		$has_feature_disabled                               = 1 === (int) $product->get_meta( '_product_addons_exclude_global', true );
		$response->data[ '_product_addons_exclude_global' ] = $has_feature_disabled ? 'no' : 'yes';

		return $response;
	}

	/**
	 * Save the meta data to the rest response.
	 *
	 * @param WC_Product      $product The product.
	 * @param WP_REST_Request $request The request.
	 *
	 * @return WC_Product
	 */
	public static function rest_handle_product_save( $product, $request ) {

		// Prevent from saving.
		if ( $request->has_param( '_product_addons_exclude_global' ) ) {

			$is_enabled = 'yes' === $request->get_param( '_product_addons_exclude_global' );
			if ( $is_enabled ) {
				$product->add_meta_data( '_product_addons_exclude_global', 0, true );
			} else {
				$product->add_meta_data( '_product_addons_exclude_global', 1, true );
			}
		}

		// Serialize the addons.
		if ( $request->has_param( '_product_addons' ) ) {
			$addons = $request->get_param( '_product_addons' );
			if ( ! is_array( $addons ) ) {
				$addons = array();
			}

			$product->add_meta_data( '_product_addons', self::rest_prepare_addons_for_db( $addons ), true );
		}

		return $product;
	}

	/**
	 * Format the addons for saving.
	 * Hint: Used to fill legacy data for the classic product editor (unfortunately to remove this will need general refactors in the plugin's core.)
	 *
	 * @param array $addons The addons.
	 * @return array
	 */
	private static function rest_prepare_addons_for_db( $addons ) {

		$formatted_addons = array();
		foreach ( $addons as $addon ) {

			// Handle Description.
			$addon['description']        = ! empty( $addon['description'] ) ? wp_kses_post( wp_unslash( $addon['description'] ) ) : '';
			$addon['description_enable'] = ! empty( $addon['description'] ) ? 1 : 0;

			// Handle Title format.
			$addon['title_format']       = ! empty( $addon['title_format'] ) ? sanitize_text_field( wp_unslash( $addon['title_format'] ) ) : 'label';

			// Handle Placeholder.
			$addon['placeholder']        = ! empty( $addon['placeholder'] ) ? wp_strip_all_tags( $addon['placeholder'] ) : '';
			$addon['placeholder_enable'] = ! empty( $addon['placeholder'] ) ? 1 : 0;

			// Handle Position.
			$addon['position']           = ! empty( $addon['position'] ) ? absint( $addon['position'] ) : 0;

			// Ηandle Display.
			$addon['display']            = ! empty( $addon['display'] ) ? sanitize_text_field( wp_unslash( $addon['display'] ) ) : 'select';

			// Handle Required.
			$addon['required']           = ! empty( $addon['required'] ) ? 1 : 0;

			// Handle Restrictions.
			$addon['restrictions_type']  = ! empty( $addon['restrictions_type'] ) ? sanitize_text_field( wp_unslash( $addon['restrictions_type'] ) ) : 'any_text';

			// Handle Price.
			$addon['price']              = ! empty( $addon['price'] ) ? wc_format_decimal( sanitize_text_field( wp_unslash( $addon[ 'price' ] ) ) ) : '';
			$addon['price_type']         = ! empty( $addon['price'] ) && ! empty( $addon['price_type'] ) ? sanitize_text_field( wp_unslash( $addon[ 'price_type' ] ) ) : 'flat_fee';
			$addon['adjust_price']       = ! empty( $addon['price'] ) ? 1 : 0;

			// Handle min, max.
			$addon['min']                = ! empty( $addon['min'] ) ? absint( $addon['min'] ) : 0;
			$addon['max']                = ! empty( $addon['max'] ) ? absint( $addon['max'] ) : 0;

			// This is required for the legacy editor to show the Limit price/quantity/character length checkbox enabled when a min/max rule is set.
			$addon['restrictions']       = ! empty( $addon['restrictions'] ) || ! empty( $addon['min'] ) || ! empty( $addon['max'] ) ? 1 : 0;

			// Handle options.
			$addon['options']            = ! empty( $addon['options'] ) ? $addon['options'] : array();

			// Add to the formatted addons.
			$formatted_addons[] = $addon;
		}

		return $formatted_addons;
	}
}

WC_Product_Addons_Product_Editor_Compatibility::init();
