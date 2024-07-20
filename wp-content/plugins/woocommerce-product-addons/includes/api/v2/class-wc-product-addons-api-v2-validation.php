<?php
/**
 * Rest API v2 Validation helper.
 *
 * @package Automattic\WooCommerce\ProductAddons
 * @since 6.9.0
 */

/**
 * WC_Product_Addons_Api_V2_Product_Group class.
 */
class WC_Product_Addons_Api_V2_Validation {

	public static $options_valid_for = array(
		'multiple_choice',
		'checkbox',
	);

	/**
	 * Return true if the object is getting created.
	 *
	 * If the object includes an `id` parameter, then this object is to be updated. Otherwise, assume it's a request to create the object.
	 *
	 * @param array $object Add-on or group object.
	 */
	public static function creating_object( $object ) {

		return ! self::updating_object( $object );
	}

	/**
	 * Return true if the object is getting updated.
	 *
	 * If the object includes an `id` parameter, then this object is to be updated. Otherwise, assume it's a request to create the object.
	 *
	 * @param array $object Add-on or group object.
	 */
	public static function updating_object( $object ) {

		return isset( $object[ 'id' ] );
	}

	/**
	 * Validate Name of Product Add-On.
	 *
	 * This callback will (and can) only be called from Validation::is_array_of_addons(), hence it can have some extra parameters.
	 *
	 * @param any              $value The value to validate.
	 * @param \WP_REST_Request $request The request object.
	 * @param string           $param The parameter name.
	 * @param array            $schema
	 * @param array            $all_values
	 *
	 * @return true|\WP_Error
	 */
	public function validate_addon_name( $value, $request, $param, $schema, $all_values ) {

		return $this->validate_name( $value, self::creating_object( $all_values ), __( 'Add-on', 'woocommerce-product-addons') );

	}

	/**
	 * Validate Name of Global groups.
	 *
	 * This callback will be called from regular WP code, hence it only gets standard set of parameters.
	 *
	 * @param any              $value The value to validate.
	 * @param \WP_REST_Request $request The request object.
	 * @param string           $param The parameter name.
	 *
	 * @return true|\WP_Error
	 */
	public function validate_group_name( $value, $request, $param = '' ) {

		return $this->validate_name( $value, self::creating_object( $request ), __( 'Global group', 'woocommerce-product-addons' ) );

	}

	/**
	 * Validate Name of Product Add-On and Global groups.
	 *
	 * Name is required when creating an object.
	 * Name cannot be longer than 255 characters and it's a string.
	 *
	 * @param string $name The name to validate.
	 * @param bool   $is_create_request Whether the object is being created or updated.
	 * @param string $param_name The parameter name (to be used in error messages).
	 *
	 * @return true|\WP_Error
	 */
	protected function validate_name( $name, $is_create_request, $param_name ) {

		$errors = new \WP_Error();

		// Name is required when creating an object.
		if ( $is_create_request && empty( $name ) ) {
			$errors->add(
				'rest_invalid_param',
				sprintf(
					// translators: %s object type.
					__( 'Name required when creating %s.', 'woocommerce-product-addons' ),
					$param_name
				)
			);
			return $errors;
		}

		// Name has to be a string.
		if ( ! is_string( $name ) ) {
			$errors->add(
				'rest_invalid_param',
				sprintf(
					// translators: %s object type.
					__( '%s name must be a string.', 'woocommerce-product-addons' ),
					$param_name
				)
			);
			return $errors;
		}

		// Name is max 255 characters long.
		// TODO: utf8?
		if ( 255 < mb_strlen( $name ) ) {
			$errors->add(
				'rest_invalid_param',
				sprintf(
					// translators: %s object type.
					__( '%s name can be maximum 255 characters long.', 'woocommerce-product-addons' ),
					$param_name
				)
			);
		}

		return ! empty( $errors->errors ) ? $errors : true;

	}

	/**
	 * Validates that the passed argument is an array of add-on fields. An empty array
	 * IS acceptable. This also validates each option in the field's options array against
	 * the field type.
	 *
	 * @param array            $values The data to validate.
	 * @param \WP_REST_Request $request Request object.
	 * @param string           $param Current param being validated.
	 * @param array            $schema  Field schema.
	 * @return bool|\WP_Error
	 */
	public function is_array_of_addons( $values, $request, $param, $schema ) {
		$errors = new \WP_Error();

		$required_for_create = array(
			'name',
			'type',
		);

		if ( ! is_array( $values ) ) {
			$errors->add(
				'rest_invalid_param',
				__( 'Array expected for fields.', 'woocommerce-product-addons' )
			);
			return $errors;
		}

		foreach ( $values as $value ) {

			foreach ( $schema as $property_key => $property_args ) {
				// Name and type are required for create requests, not for updates.
				if ( self::creating_object( $value ) && in_array( $property_key, $required_for_create ) ) {
					$required = true;
				} else {
					$required = $property_args['required'] ?? false;
				}

				// If field is required, but it's not set.
				if ( $required && ! isset( $value[ $property_key ] ) ) {
					$errors->add(
						'missing_required_field',
						sprintf(
							// translators: %s field name.
							__( '"%s" is required', 'woocommerce-product-addons' ),
							$param . ' > ' . $property_key
						)
					);
					continue;
				}

				if ( 'options' === $property_key ) {
					// Options are required for some Add-on types, can be null for others. Since validate_callback is only
					// called for non-null values, it needs to be checked also here.
					$options_valid = $this->validate_options( $value['options'] ?? null, $request, $param, $schema, $value );
					if ( is_wp_error( $options_valid ) ) {
						$errors->add(
							$options_valid->get_error_code(),
							$options_valid->get_error_message()
						);
						continue;
					}
				}

				if ( isset( $value[ $property_key ] ) ) {
					if ( isset( $property_args['arg_options']['validate_callback'] ) && is_callable( $property_args['arg_options']['validate_callback'] ) ) {
						$result = $property_args['arg_options']['validate_callback']( $value[ $property_key ], $request, $param . ' > ' . $property_key, $property_args, $value );
					} else {
						$result = rest_validate_value_from_schema( $value[ $property_key ], $property_args, $param . ' > ' . $property_key );
					}

					if ( is_wp_error( $result ) ) {
						return $result;
					}
				}
			}
		}

		return ! empty( $errors->errors ) ? $errors : true;
	}

	/**
	 * Sanitizes fields with defaults and schema properties.
	 *
	 * @param array            $addons The data to sanitize.
	 * @param \WP_REST_Request $request Request object.
	 * @param string           $param Current param being sanitized.
	 * @param array            $schema  Field schema.
	 *
	 * @return array
	 */
	public function sanitize_array_of_addons( $addons, $request, $param, $schema ) {
		$return = array();

		foreach ( $addons as $index => $addon ) {
			foreach ( $schema as $property_key => $property_args ) {
				// In case of updating an Add-on, only change values specified in the request.
				if ( self::updating_object( $addon ) && ! isset( $addon[ $property_key ] ) ) {
					continue;
				}

				$default = $property_args['arg_options']['default'] ?? null;
				if ( isset( $property_args['arg_options']['sanitize_callback'] ) && is_callable( $property_args['arg_options']['sanitize_callback'] ) ) {
					$result = $property_args['arg_options']['sanitize_callback']( wc_clean( wp_unslash( $addon[ $property_key ] ?? $default ) ), $property_args, $param . ' > ' . $property_key );
				} else {
					$result = rest_sanitize_value_from_schema( wc_clean( wp_unslash( $addon[ $property_key ] ?? $default ) ), $property_args, $param . ' > ' . $property_key );
				}

				if ( is_wp_error( $result ) ) {
					return $result;
				}

				$return[ $index ][ $property_key ] = $result;
			}
		}

		return $return;
	}

	/**
	 * Validates that the passed argument is empty or a float.
	 *
	 * @param string           $value The data to validate.
	 * @param \WP_REST_Request $request Request object.
	 * @param string           $param Current param being validated.
	 * @return bool|\WP_Error
	 */
	public function is_empty_or_float( $value, $request, $param ) {
		if ( ! empty( $value ) && ! is_numeric( wc_format_decimal( $value ) ) ) {
			return new \WP_Error(
				'rest_invalid_type',
				sprintf(
					// translators: %s field name.
					__( 'Integer (or empty string) expected for "%s"', 'woocommerce-product-addons' ),
					$param
				)
			);
		}

		return true;
	}

	/**
	 * Validate min and max values and the correct relation between them.
	 *
	 * This callback will (and can) only be called from Validation::is_array_of_addons(), hence it can have some extra parameters.
	 *
	 * @param string           $value The data to validate.
	 * @param \WP_REST_Request $request Request object.
	 * @param string           $param Current param being validated.
	 * @param array            $all_values All values.
	 *
	 * @return bool|\WP_Error
	 */
	public function validate_min_max( $value, $request, $param, $schema, $all_values ) {
		// Check that it's an integer.
		$result = rest_validate_value_from_schema( $value, $schema, $param );

		if ( is_wp_error( $result ) ) {
			return $result;
		}

		if ( ! isset( $all_values[ 'min' ] ) || ! isset( $all_values[ 'max' ] ) ) {
			return true;
		}

		if ( (int) $all_values[ 'min' ] <= (int) $all_values[ 'max' ] ) {
			return true;
		}

		return new \WP_Error(
			'rest_invalid_param',
			sprintf(
				// translators: 1: min value, 2: max value.
				__( 'Provided min value (%1$d) must be less than or equal to the max value (%2$d).', 'woocommerce-product-addons' ),
				$all_values[ 'min' ],
				$all_values[ 'max' ]
			)
		);

	}

	/**
	 * Validate Add-on options:
	 *  - options are defined for multiple_choice or checkbox Add-ons
	 *  - options are not defined for other Add-ons
	 *  - options always have a name
	 *
	 * This callback will (and can) only be called from Validation::is_array_of_addons(), hence it can have some extra parameters.
	 *
	 * @param array            $value The data to validate.
	 * @param \WP_REST_Request $request Request object.
	 * @param string           $param Current param being validated.
	 * @param array            $all_values All values.
	 *
	 * @return true|\WP_Error
	 */
	public function validate_options( $value, $request, $param, $schema, $all_values ) {
		$type = null;
		if ( self::creating_object( $all_values) ) {
			if ( ! isset( $all_values[ 'type' ] ) ) {
				return new \WP_Error(
					'rest_invalid_param',
					__( 'Type must be defined when creating an Add-on.', 'woocommerce-product-addons' )
				);
			}
			$type = $all_values[ 'type' ];
		} else {
			$id = $request->get_param( 'id' );
			if ( \WC_Product_Addons_Api_V2_Global_Group::is_a_global_group_id( $id ) ) {
				$group = new WC_Product_Addons_Api_V2_Global_Group( $id );
			} else {
				$group = new WC_Product_Addons_Api_V2_Product_Group( $id );
			}
			$addons = $group->get_fields();

			// When updating, type doesn't have to be specified in the request -> get it from the object if it's not specified in request.
			if ( isset( $all_values[ 'type' ] ) ) {
				$type = $all_values[ 'type' ];
			}
			else {
				// Find the correct Add-on and get type from the add-on.
				foreach ( $addons as $addon ) {
					if ( $addon['id'] === $all_values['id'] ) {
						$type = $addon['type'];
					}
				}
			}

			// Handle value correctly when updating: if it's in the request, test that, otherwise check against stored value.
			if ( ! isset ( $all_values['options'] ) ) {
				foreach ( $addons as $addon ) {
					if ( $addon['id'] === $all_values['id'] ) {
						$value = $addon['options'];
					}
				}
			}
		}
		// Options make sense only for multiple choice and checkbox Add-ons.
		if ( ! in_array( $type, self::$options_valid_for, true ) && ! empty( $value ) ) {
			return new \WP_Error(
				'rest_invalid_param',
					__( 'Options can only be defined/set for "Multiple Choice" and "Checkboxes" add-on types.', 'woocommerce-product-addons' )
			);
		}

		// At least one option must be defined for "multiple choice" and "checkbox" Add-Ons.
		if ( in_array( $type, self::$options_valid_for, true ) && empty( $value ) ) {
			return new \WP_Error(
				'rest_invalid_param',
				__( '"Multiple Choice" and "Checkboxes" add-on types require at lease one option.', 'woocommerce-product-addons' )
			);
		}

		// Options not required and they're empty -> OK.
		if ( empty( $value ) ) {
			return true;
		}

		// Each option must have a label.
		foreach ( $value as $option ) {
			if ( empty( $option[ 'label' ] ) ) {
				return new \WP_Error(
					'rest_invalid_type',
					__( 'Each "option" must have a "label" property defined.', 'woocommerce-product-addons' )
				);
			}
		}

		return true;

	}

	/**
	 * Sanitize an empty string or integer.
	 *
	 * @param string $value The data to validate.
	 * @return string|int
	 */
	public function sanitize_empty_or_integer( $value ) {
		if ( '' !== $value ) {
			return intval( $value );
		}
		return '';
	}
}
