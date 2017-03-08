<?php

/**
 * Fields API
 *
 * @package     Give
 * @subpackage  Classes/Give_Fields_API
 * @copyright   Copyright (c) 2016, WordImpress
 * @license     https://opensource.org/licenses/gpl-license GNU Public License
 * @since       1.9
 */
class Give_Fields_API {
	/**
	 * Instance.
	 *
	 * @since  1.9
	 * @access private
	 * @var Give_Fields_API
	 */
	static private $instance;

	/**
	 * The defaults for all elements
	 *
	 * @since  1.9
	 * @access static
	 */
	static $field_defaults = array(
		'type'       => '',
		'label'      => '',
		'name'       => '',
		'data_type'  => '',
		'value'      => '',
		'default'    => '',
		'template'   => '',
		'tooltip'    => '',
		'required'   => false,
		'attributes' => array(),
	);


	private function __construct() {
	}


	/**
	 * Get instance.
	 *
	 * @return static
	 */
	public static function get_instance() {
		if ( is_null( static::$instance ) ) {
			self::$instance = new static();
		}

		return self::$instance;
	}

	/**
	 * Initialize this module
	 *
	 * @since  1.9
	 * @access static
	 */
	public function init() {
		add_filter( 'give_form_api_render_form_tags', array( $this, 'render_tags' ), 10, 2 );
	}

	/**
	 * Render tag
	 *
	 * @since   1.9
	 * @access  public
	 *
	 * @param $field
	 * @param $form
	 *
	 * @return string
	 */
	public static function render_tag( $field, $form ) {
		$field_html     = '';
		$functions_name = "render_{$field['type']}_field";
		$field          = self::get_instance()->set_default_values( $field );

		if ( method_exists( self::$instance, $functions_name ) ) {
			$field_html .= self::$instance->{$functions_name}( $field );
		} else {
			$field_html .= apply_filters( "give_fields_api_render_{$field['type']}_field", '', $field, $form );
		}

		return $field_html;
	}


	/**
	 * Render `{{form_fields}}` tag.
	 *
	 * @since  1.9
	 * @access private
	 *
	 * @param  string $form_html
	 * @param  array  $form
	 *
	 * @return string
	 */
	public function render_tags( $form_html, $form ) {
		// Bailout: If form does not contain any field.
		if ( empty( $form['fields'] ) ) {
			str_replace( '{{form_fields}}', '', $form_html );

			return $form_html;
		}

		$fields_html = '';

		foreach ( $form['fields'] as $key => $field ) {
			$field['name'] = empty( $field['name'] ) ? $key : $field['name'];
			$fields_html .= self::render_tag( $field, $form );
		}


		$form_html = str_replace( '{{form_fields}}', $fields_html, $form_html );

		return $form_html;
	}


	/**
	 * Render text field.
	 *
	 * @since  1.9
	 * @access private
	 *
	 * @param  array $field
	 *
	 * @return string
	 */
	public static function render_text_field( $field ) {
		ob_start();
		?>
		<p>
			<?php echo self::$instance->render_label( $field ); ?>
			<input
				type="<?php echo $field['type']; ?>"
				name="<?php echo $field['name']; ?>"
				value="<?php echo $field ['value']; ?>"
				<?php echo( $field['required'] ? 'required=""' : '' ); ?>
				<?php echo self::$instance->get_attributes( $field ); ?>
			>
		</p>
		<?php
		return ob_get_clean();
	}

	/**
	 * Render text field.
	 *
	 * @since  1.9
	 * @access private
	 *
	 * @param  array $field
	 *
	 * @return string
	 */
	public static function render_submit_field( $field ) {
		return self::$instance->render_text_field( $field );
	}

	/**
	 * Render label
	 *
	 * @since  1.9
	 * @access public
	 *
	 * @param $field
	 *
	 * @return string
	 */
	public static function render_label( $field ) {
		ob_start();
		?>
		<?php if ( ! empty( $field['label'] ) ): ?>
			<label class="give-label" for="<?php echo $field['name']; ?>">
				<?php echo $field['label']; ?>
				<?php if ( $field['required'] ) : ?>
					<span class="give-required-indicator">*</span>
				<?php endif; ?>

				<?php if ( $field['tooltip'] ) : ?>
					<span class="give-tooltip give-icon give-icon-question" data-tooltip="<?php echo $field['tooltip'] ?>"></span>
				<?php endif; ?>
			</label>
		<?php endif; ?>
		<?php
		return ob_get_clean();
	}

	/**
	 * Get attribute string from field arguments.
	 *
	 * @since  1.9
	 * @access private
	 *
	 * @param $field
	 *
	 * @return array|string
	 */
	private function get_attributes( $field ) {
		$field_attributes_val = '';

		if ( ! empty( $field['attributes'] ) ) {
			foreach ( $field['attributes'] as $attribute_name => $attribute_val ) {
				$field_attributes_val[] = "{$attribute_name}=\"{$attribute_val}\"";
			}
		}

		if ( ! empty( $field_attributes_val ) ) {
			$field_attributes_val = implode( ' ', $field_attributes_val );
		}

		return $field_attributes_val;
	}

	/**
	 * Set default values for fields
	 *
	 * @since  1.0
	 * @access private
	 *
	 * @param array $field
	 *
	 * @return array
	 */
	private function set_default_values( $field ) {
		return wp_parse_args( $field, self::$field_defaults );
	}

	/**
	 * Is the element a button?
	 *
	 * @since  1.9
	 * @access static
	 *
	 * @param array $element
	 *
	 * @return bool
	 */
	static function is_button( $element ) {
		return preg_match( '/^button|submit$/', $element['#type'] );
	}
}
