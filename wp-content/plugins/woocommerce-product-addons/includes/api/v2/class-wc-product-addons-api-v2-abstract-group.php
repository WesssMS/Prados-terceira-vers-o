<?php
/**
 * Rest API v2 Abstract Group helper.
 *
 * @package Automattic\WooCommerce\ProductAddons
 * @since 6.9.0
 */

/**
 * WC_Product_Addons_Api_V2_Abstract_Group class.
 */
abstract class WC_Product_Addons_Api_V2_Abstract_Group {
	/**
	 * Group ID.
	 *
	 * @var int
	 */
	protected $id = 0;

	/**
	 * Group name.
	 *
	 * @var int
	 */
	protected $name = '';

	/**
	 * Group priority.
	 *
	 * @var int
	 */
	protected $priority = 1;

	/**
	 * Group fields (add-ons).
	 *
	 * @var array
	 */
	protected $fields = array();

	/**
	 * Persist data.
	 */
	abstract public function save();

	/**
	 * Get id.
	 *
	 * @return int
	 */
	public function get_id() {
		return $this->id;
	}

	/**
	 * Get name.
	 *
	 * @return string
	 */
	public function get_name() {
		return $this->name;
	}

	/**
	 * Set name.
	 *
	 * @param string $value Value to set.
	 */
	public function set_name( string $value ) {
		$this->name = $value;
	}

	/**
	 * Get priority.
	 *
	 * @return string
	 */
	public function get_priority() {
		return $this->priority;
	}

	/**
	 * Set priority.
	 *
	 * @param int $value Value to set.
	 */
	public function set_priority( int $value ) {
		$this->priority = $value;
	}

	/**
	 * Get fields.
	 *
	 * @return array
	 */
	public function get_fields() {
		return $this->fields;
	}

	/**
	 * Set fields.
	 *
	 * @param array $value Value to set.
	 */
	public function set_fields( array $value ) {
		$this->fields = $value;
	}

	/**
	 * Update fields.
	 *
	 * Blend in Add-Ons with matching id, create new Add-Ons if not found.
	 *
	 * @param array $values Values to update.
	 */
	public function update_fields( array $values ) {
		if ( empty( $this->fields ) ) {
			$this->fields = $values;
			return;
		}

		$updated_ids = array();

		// Search for a matching id and update the Add-on if found. Otherwise, create a new Add-On.
		foreach ( $values as $key => $value ) {
			$found_id = false;
			foreach ( $this->fields as $existing_field_key => $existing_field_value ) {
				// Found a matching Add-On key -> update the values.
				if ( isset( $value[ 'id' ] ) && $existing_field_value['id'] === $value[ 'id' ] ) {
					foreach ( $value as $sub_key => $new_value ) {
						$this->fields[ $existing_field_key ][ $sub_key ] = $new_value;
					}
					$found_id = true;
					$updated_ids[ $existing_field_value['id'] ] = 1;
				}
			}
			// No Add-On with matching id -> create a new Add-On.
			if ( ! $found_id ) {
				$this->fields[] = array();
				end($this->fields);
				$last_key = key($this->fields);
				foreach ( $value as $sub_key => $new_value ) {
					$this->fields[ $last_key ][ $sub_key ] = $new_value;
				}
			}
		}

		// Delete Add-ons not referenced in the update (except for those just created).
		foreach ( $this->fields as $existing_field_key => $existing_field_value ) {
			if ( 0 !== $existing_field_value['id'] && ! array_key_exists( $existing_field_value['id'], $updated_ids ) ){
				unset( $this->fields[ $existing_field_key ] );
			}
		}

		// Reindex.
		$this->fields = array_values( $this->fields );
	}

	/**
	 * Format fields from the DB.
	 *
	 * @param array $fields Fields to format.
	 */
	protected function set_fields_from_db( $fields ) {
		$fields = array_filter( (array) $fields );

		foreach ( $fields as $key => $field ) {
			$fields[ $key ] = array(
				'name'               => $field['name'],
				'type'               => $field['type'],
				'position'           => (int) $field['position'],
				'required'           => wc_string_to_bool( $field['required'] ),
				'title_format'       => $field['title_format'],
				'description_enable' => wc_string_to_bool( $field['description_enable'] ),
				'description'        => $field['description'],
				'placeholder_enable' => wc_string_to_bool( $field['placeholder_enable'] ),
				'placeholder'        => $field['placeholder'],
				'display'            => $field['display'],
				'restrictions_type'  => $field['restrictions_type'],
				'adjust_price'       => wc_string_to_bool( $field['adjust_price'] ),
				'price'              => $field['price'],
				'price_type'         => $field['price_type'],
				'restrictions'       => wc_string_to_bool( $field['restrictions'] ),
				'min'                => (int) $field['min'],
				'max'                => (int) $field['max'],
				'options'            => in_array( $field['type'], array(
					'multiple_choice',
					'checkbox'
				), true ) ? array_map( array(
					$this,
					'normalize_field_options_from_db'
				), array_filter( (array) $field['options'] ) ) : array(),
			);

			if ( isset( $field['id'] ) ) {
				$fields[ $key ]['id'] = (int) $field['id'];
			}
		}

		$this->set_fields( $fields );
	}

	/**
	 * Normalize options of a field.
	 *
	 * @param array $option Field option.
	 * @return array
	 */
	protected function normalize_field_options_from_db( array $option ) {
		return array(
			'label'      => $option['label'] ?? '',
			'price'      => $option['price'] ?? '',
			'price_type' => $option['price_type'] ?? '',
			'image'      => absint( $option['image'] ?? 0 ), // Image ID.
		);
	}

	/**
	 * Get fields to save to DB.
	 *
	 * @return array
	 */
	protected function get_fields_for_db() {
		$fields = $this->get_fields();

		foreach ( $fields as $key => $field ) {
			$fields[ $key ]['id']                 = isset( $field['id'] ) && $field['id'] ? $field['id'] : WC_Product_Addons_Helper::generate_id();
			$fields[ $key ]['description_enable'] = ! empty( $field['description'] ) ? 1 : 0; // Set description_enable if description is set.
			$fields[ $key ]['required']           = ! empty( $field['required'] ) ? 1 : 0;
			$fields[ $key ]['restrictions']       = ( ! empty( $field['min'] ) || ! empty( $field['max'] ) ) ? 1 : 0; // Set restrictions if min or max is set.
			$fields[ $key ]['adjust_price']       = ! empty( $field['price'] ) ? 1 : 0; // Set adjust_price if price is set.
			$fields[ $key ]['placeholder_enable'] = ! empty( $field['placeholder'] ) ? 1 : 0; // Set placeholder_enable if placeholder is set.

			if ( ! in_array( $field['type'], WC_Product_Addons_Api_V2_Validation::$options_valid_for ) ) {
				$fields[ $key ]['options']       = array();
				$this->fields[ $key ]['options'] = array();
			}

			// Update local object copy without refresh from the db.
			$this->fields[ $key ]['id']                 = $fields[ $key ]['id'];
			$this->fields[ $key ]['description_enable'] = wc_string_to_bool( $fields[ $key ]['description_enable'] );
			$this->fields[ $key ]['restrictions']       = wc_string_to_bool( $fields[ $key ]['restrictions'] );
			$this->fields[ $key ]['adjust_price']       = wc_string_to_bool( $fields[ $key ]['adjust_price'] );
			$this->fields[ $key ]['placeholder_enable'] = wc_string_to_bool( $fields[ $key ]['placeholder_enable'] );

		}

		return $fields;
	}
}
