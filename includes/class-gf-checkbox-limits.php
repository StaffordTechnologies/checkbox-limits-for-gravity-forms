<?php
/**
 * Gravity Forms Checkbox Limits add-on class.
 *
 * @package GFCheckboxLimits
 */

defined( 'ABSPATH' ) || exit;

GFForms::include_addon_framework();

/**
 * Class GF_Checkbox_Limits
 */
class GF_Checkbox_Limits extends GFAddOn {

	// phpcs:disable PSR2.Classes.PropertyDeclaration.Underscore -- GFAddOn requires underscore-prefixed properties.

	/**
	 * Add-on version.
	 *
	 * @var string
	 */
	protected $_version = GF_CHECKBOX_LIMITS_VERSION;

	/**
	 * Minimum Gravity Forms version.
	 *
	 * @var string
	 */
	protected $_min_gravityforms_version = '2.5';

	/**
	 * Add-on slug / text domain.
	 *
	 * @var string
	 */
	protected $_slug = 'checkbox-limits-for-gravity-forms';

	/**
	 * Relative path from wp-content/plugins.
	 *
	 * @var string
	 */
	protected $_path = 'checkbox-limits-for-gravity-forms/checkbox-limits-for-gravity-forms.php';

	/**
	 * Absolute path to main plugin file.
	 *
	 * @var string
	 */
	protected $_full_path = GF_CHECKBOX_LIMITS_FILE;

	/**
	 * Full title.
	 *
	 * @var string
	 */
	protected $_title = 'Checkbox Limits for Gravity Forms';

	/**
	 * Short title.
	 *
	 * @var string
	 */
	protected $_short_title = 'Checkbox Limits';

	/**
	 * Capabilities (registered with Members if present; admins get them on install).
	 *
	 * @var array
	 */
	protected $_capabilities = array(
		'gravityforms_checkbox_limits',
		'gravityforms_checkbox_limits_uninstall',
	);

	/**
	 * Settings capability.
	 *
	 * @var string
	 */
	protected $_capabilities_settings_page = 'gravityforms_checkbox_limits';

	/**
	 * Form settings capability — fall back to edit_forms so editors can configure fields.
	 *
	 * @var string
	 */
	protected $_capabilities_form_settings = 'gravityforms_edit_forms';

	/**
	 * Uninstall capability.
	 *
	 * @var string
	 */
	protected $_capabilities_uninstall = 'gravityforms_checkbox_limits_uninstall';

	/**
	 * Singleton instance.
	 *
	 * @var GF_Checkbox_Limits|null
	 */
	private static $_instance = null;

	/**
	 * Get singleton instance.
	 *
	 * @return GF_Checkbox_Limits
	 */
	public static function get_instance() {
		if ( null === self::$_instance ) {
			self::$_instance = new self();
		}
		return self::$_instance;
	}

	/**
	 * Init (admin + front + ajax).
	 *
	 * @return void
	 */
	public function init() {
		parent::init();
		add_filter( 'gform_validation', array( $this, 'validate_checkbox_limits' ) );
		// Sanitize limit field properties whenever form meta is written.
		add_filter( 'gform_form_update_meta', array( $this, 'sanitize_form_meta_limits' ), 10, 3 );
	}

	/**
	 * Admin-only hooks: field settings UI + editor JS.
	 *
	 * @return void
	 */
	public function init_admin() {
		parent::init_admin();
		add_action( 'gform_field_standard_settings', array( $this, 'field_standard_settings' ), 25, 2 );
		add_action( 'gform_editor_js', array( $this, 'editor_js' ) );
	}

	/**
	 * Front-end hooks: register init scripts with per-form limit data.
	 *
	 * @return void
	 */
	public function init_frontend() {
		parent::init_frontend();
		add_action( 'gform_register_init_scripts', array( $this, 'register_init_scripts' ), 10, 3 );
	}

	/**
	 * Plugin settings: optional global default validation message.
	 *
	 * GFAddOn saves text fields through its settings API (capability + nonce).
	 *
	 * @return array
	 */
	public function plugin_settings_fields() {
		return array(
			array(
				'title'  => esc_html__( 'Checkbox Limits', 'checkbox-limits-for-gravity-forms' ),
				'fields' => array(
					array(
						'name'                  => 'default_validation_message',
						'label'                 => esc_html__( 'Default validation message', 'checkbox-limits-for-gravity-forms' ),
						'type'                  => 'text',
						'class'                 => 'large',
						'default_value'         => __( 'Please choose {min} to {max} options.', 'checkbox-limits-for-gravity-forms' ),
						'tooltip'               => esc_html__( 'Used when a checkbox field has min/max limits but no custom message. Placeholders: {min}, {max}.', 'checkbox-limits-for-gravity-forms' ),
						// Strip tags/HTML so stored default cannot carry markup into validation output.
						'sanitization_callback' => 'sanitize_text_field',
					),
				),
			),
		);
	}

	/**
	 * Scripts for the form editor and front end.
	 *
	 * @return array
	 */
	public function scripts() {
		$scripts = array(
			array(
				'handle'  => 'gf_checkbox_limits_frontend',
				'src'     => GF_CHECKBOX_LIMITS_URL . 'js/frontend.js',
				'version' => $this->_version,
				'deps'    => array( 'jquery', 'gform_gravityforms' ),
				'enqueue' => array(
					array( 'field_types' => array( 'checkbox' ) ),
				),
			),
		);
		return array_merge( parent::scripts(), $scripts );
	}

	/**
	 * Render Min / Max / Message settings on checkbox fields in the form editor.
	 *
	 * @param int $position Setting position.
	 * @param int $form_id  Form ID.
	 * @return void
	 */
	public function field_standard_settings( $position, $form_id ) {
		if ( 25 !== (int) $position ) {
			return;
		}
		unset( $form_id );
		?>
		<li class="gfcl_setting field_setting">
			<label for="gfcl_min_selections" class="section_label">
				<?php esc_html_e( 'Min selections', 'checkbox-limits-for-gravity-forms' ); ?>
				<?php gform_tooltip( 'gfcl_min_selections' ); ?>
			</label>
			<input type="number" id="gfcl_min_selections" min="0" step="1" class="small"
				onchange="SetFieldProperty('gfclMinSelections', this.value);"
				oninput="SetFieldProperty('gfclMinSelections', this.value);" />
		</li>
		<li class="gfcl_setting field_setting">
			<label for="gfcl_max_selections" class="section_label">
				<?php esc_html_e( 'Max selections', 'checkbox-limits-for-gravity-forms' ); ?>
				<?php gform_tooltip( 'gfcl_max_selections' ); ?>
			</label>
			<input type="number" id="gfcl_max_selections" min="0" step="1" class="small"
				onchange="SetFieldProperty('gfclMaxSelections', this.value);"
				oninput="SetFieldProperty('gfclMaxSelections', this.value);" />
		</li>
		<li class="gfcl_setting field_setting">
			<label for="gfcl_validation_message" class="section_label">
				<?php esc_html_e( 'Selection limit message', 'checkbox-limits-for-gravity-forms' ); ?>
				<?php gform_tooltip( 'gfcl_validation_message' ); ?>
			</label>
			<input type="text" id="gfcl_validation_message" class="fieldwidth-3"
				placeholder="<?php echo esc_attr__( 'Please choose {min} to {max} options.', 'checkbox-limits-for-gravity-forms' ); ?>"
				onchange="SetFieldProperty('gfclValidationMessage', this.value);"
				oninput="SetFieldProperty('gfclValidationMessage', this.value);" />
			<p class="gfcl-help-text" style="margin:4px 0 0;">
				<?php esc_html_e( 'Optional. Placeholders: {min}, {max}. Leave blank to use the add-on default.', 'checkbox-limits-for-gravity-forms' ); ?>
			</p>
		</li>
		<?php
	}

	/**
	 * Editor JS: show settings on checkbox fields and hydrate values.
	 *
	 * @return void
	 */
	public function editor_js() {
		?>
		<script type="text/javascript">
			(function ($) {
				if (typeof fieldSettings !== 'undefined') {
					fieldSettings.checkbox += ', .gfcl_setting';
				}

				if (typeof gform !== 'undefined' && gform.addFilter) {
					gform.addFilter('gform_tooltips', function (tooltips) {
						tooltips.gfcl_min_selections = '<h6><?php echo esc_js( __( 'Min selections', 'checkbox-limits-for-gravity-forms' ) ); ?></h6><?php echo esc_js( __( 'Minimum number of checkboxes the user must select. Leave blank for no minimum.', 'checkbox-limits-for-gravity-forms' ) ); ?>';
						tooltips.gfcl_max_selections = '<h6><?php echo esc_js( __( 'Max selections', 'checkbox-limits-for-gravity-forms' ) ); ?></h6><?php echo esc_js( __( 'Maximum number of checkboxes the user may select. Remaining boxes are disabled once this limit is reached. Leave blank for no maximum.', 'checkbox-limits-for-gravity-forms' ) ); ?>';
						tooltips.gfcl_validation_message = '<h6><?php echo esc_js( __( 'Selection limit message', 'checkbox-limits-for-gravity-forms' ) ); ?></h6><?php echo esc_js( __( 'Shown when the selection count is outside the min/max range. Use {min} and {max} placeholders.', 'checkbox-limits-for-gravity-forms' ) ); ?>';
						return tooltips;
					});
				}

				$(document).on('gform_load_field_settings', function (event, field) {
					if (!field || field.type !== 'checkbox') {
						return;
					}
					$('#gfcl_min_selections').val(field.gfclMinSelections !== undefined && field.gfclMinSelections !== null ? field.gfclMinSelections : '');
					$('#gfcl_max_selections').val(field.gfclMaxSelections !== undefined && field.gfclMaxSelections !== null ? field.gfclMaxSelections : '');
					$('#gfcl_validation_message').val(field.gfclValidationMessage ? field.gfclValidationMessage : '');
				});
			})(jQuery);
		</script>
		<?php
	}

	/**
	 * Register front-end init script with limit config for this form.
	 *
	 * Only min/max are sent to the browser (message is server-side only) to
	 * avoid putting admin-entered strings into front-end script output.
	 *
	 * @param array $form         Form object.
	 * @param array $field_values Field values.
	 * @param bool  $is_ajax      Whether AJAX is enabled.
	 * @return void
	 */
	public function register_init_scripts( $form, $field_values, $is_ajax ) {
		unset( $field_values, $is_ajax );

		$limits = $this->get_form_checkbox_limits( $form, false );
		if ( empty( $limits ) ) {
			return;
		}

		$form_id = (int) rgar( $form, 'id' );
		$args    = wp_json_encode(
			array(
				'formId' => $form_id,
				'fields' => $limits,
			)
		);

		$script = 'if (window.GFCheckboxLimits) { GFCheckboxLimits.init(' . $args . '); }';

		GFFormDisplay::add_init_script( $form_id, 'gf_checkbox_limits', GFFormDisplay::ON_PAGE_RENDER, $script );
	}

	/**
	 * Collect checkbox fields that have min and/or max configured.
	 *
	 * @param array $form            Form object.
	 * @param bool  $include_message Whether to include sanitized validation message (server use).
	 * @return array<int, array{min:?int,max:?int,message?:string}>
	 */
	public function get_form_checkbox_limits( $form, $include_message = true ) {
		$limits = array();
		$fields = rgar( $form, 'fields' );
		if ( empty( $fields ) || ! is_array( $fields ) ) {
			return $limits;
		}

		foreach ( $fields as $field ) {
			if ( ! is_object( $field ) || 'checkbox' !== $field->type ) {
				continue;
			}

			$min = $this->normalize_limit( rgar( $field, 'gfclMinSelections' ) );
			$max = $this->normalize_limit( rgar( $field, 'gfclMaxSelections' ) );

			if ( null === $min && null === $max ) {
				continue;
			}

			$entry = array(
				'min' => $min,
				'max' => $max,
			);

			if ( $include_message ) {
				$entry['message'] = $this->build_message( $field, $min, $max );
			}

			$limits[ (int) $field->id ] = $entry;
		}

		return $limits;
	}

	/**
	 * Sanitize limit properties on checkbox fields when form meta is saved.
	 *
	 * @param array  $form_meta Form meta / form array being saved.
	 * @param int    $form_id   Form ID.
	 * @param string $meta_name Meta key (display_meta holds fields).
	 * @return array
	 */
	public function sanitize_form_meta_limits( $form_meta, $form_id, $meta_name ) {
		unset( $form_id );

		if ( 'display_meta' !== $meta_name || ! is_array( $form_meta ) ) {
			return $form_meta;
		}

		if ( empty( $form_meta['fields'] ) || ! is_array( $form_meta['fields'] ) ) {
			return $form_meta;
		}

		foreach ( $form_meta['fields'] as &$field ) {
			// Fields may be arrays or GF_Field objects in meta.
			$is_object = is_object( $field );
			$type      = $is_object ? ( isset( $field->type ) ? $field->type : '' ) : rgar( $field, 'type' );

			if ( 'checkbox' !== $type ) {
				continue;
			}

			$min_raw = $is_object ? rgar( $field, 'gfclMinSelections' ) : rgar( $field, 'gfclMinSelections' );
			$max_raw = $is_object ? rgar( $field, 'gfclMaxSelections' ) : rgar( $field, 'gfclMaxSelections' );
			$msg_raw = $is_object ? rgar( $field, 'gfclValidationMessage' ) : rgar( $field, 'gfclValidationMessage' );

			$min = $this->normalize_limit( $min_raw );
			$max = $this->normalize_limit( $max_raw );
			$msg = is_string( $msg_raw ) ? sanitize_text_field( $msg_raw ) : '';

			if ( $is_object ) {
				// phpcs:ignore WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase -- GF field props + editor JS use camelCase.
				$field->gfclMinSelections = ( null === $min ) ? '' : $min;
				// phpcs:ignore WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase -- GF field props + editor JS use camelCase.
				$field->gfclMaxSelections = ( null === $max ) ? '' : $max;
				// phpcs:ignore WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase -- GF field props + editor JS use camelCase.
				$field->gfclValidationMessage = $msg;
			} else {
				$field['gfclMinSelections']     = ( null === $min ) ? '' : $min;
				$field['gfclMaxSelections']     = ( null === $max ) ? '' : $max;
				$field['gfclValidationMessage'] = $msg;
			}
		}
		unset( $field );

		return $form_meta;
	}

	/**
	 * Server-side validation, current page only (multi-page safe).
	 *
	 * Counts actual POST values; ignores client disabled state.
	 *
	 * @param array $validation_result Validation result.
	 * @return array
	 */
	public function validate_checkbox_limits( $validation_result ) {
		$form = rgar( $validation_result, 'form' );
		if ( empty( $form ) || empty( $form['fields'] ) ) {
			return $validation_result;
		}

		$form_id     = (int) rgar( $form, 'id' );
		$source_page = absint( rgpost( 'gform_source_page_number_' . $form_id ) );
		if ( $source_page < 1 ) {
			$source_page = 1;
		}

		$failed = false;

		foreach ( $form['fields'] as &$field ) {
			if ( ! is_object( $field ) || 'checkbox' !== $field->type ) {
				continue;
			}

			// Only validate fields on the page being submitted.
			// phpcs:ignore WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase -- Gravity Forms GF_Field::$pageNumber.
			$field_page = isset( $field->pageNumber ) ? absint( $field->pageNumber ) : 1;
			if ( $field_page < 1 ) {
				$field_page = 1;
			}
			if ( $field_page !== $source_page ) {
				continue;
			}

			$min = $this->normalize_limit( rgar( $field, 'gfclMinSelections' ) );
			$max = $this->normalize_limit( rgar( $field, 'gfclMaxSelections' ) );

			if ( null === $min && null === $max ) {
				continue;
			}

			$count = $this->count_checkbox_selections( $form_id, $field );

			$too_few  = ( null !== $min && $count < $min );
			$too_many = ( null !== $max && $count > $max );

			if ( ! $too_few && ! $too_many ) {
				continue;
			}

			$field->failed_validation  = true;
			$field->validation_message = $this->build_message( $field, $min, $max );
			$failed                    = true;
		}
		unset( $field );

		if ( $failed ) {
			$validation_result['is_valid'] = false;
		}

		$validation_result['form'] = $form;
		return $validation_result;
	}

	/**
	 * Count selected checkbox inputs for a field from the current POST.
	 *
	 * Ignores array/non-scalar forged values so they cannot inflate the count.
	 *
	 * @param int      $form_id Form ID.
	 * @param GF_Field $field   Field object.
	 * @return int
	 */
	protected function count_checkbox_selections( $form_id, $field ) {
		unset( $form_id );

		$count   = 0;
		$choices = is_array( $field->inputs ) ? $field->inputs : array();

		if ( ! empty( $choices ) ) {
			foreach ( $choices as $input ) {
				$input_id = rgar( $input, 'id' );
				if ( '' === $input_id || null === $input_id ) {
					continue;
				}
				// POST key uses underscore: input_10_1 for input id 10.1.
				$post_key = 'input_' . str_replace( '.', '_', (string) $input_id );
				$value    = rgpost( $post_key );
				if ( $this->is_selected_checkbox_value( $value ) ) {
					++$count;
				}
			}
			return $count;
		}

		// Fallback: scan POST for input_{fieldId}_*.
		$prefix = 'input_' . (int) $field->id . '_';
		// phpcs:ignore WordPress.Security.NonceVerification.Missing -- Gravity Forms verifies the form submission nonce before gform_validation runs.
		$post = wp_unslash( $_POST );
		foreach ( $post as $key => $value ) {
			$key = is_string( $key ) ? $key : '';
			if ( '' === $key || 0 !== strpos( $key, $prefix ) ) {
				continue;
			}
			if ( $this->is_selected_checkbox_value( $value ) ) {
				++$count;
			}
		}

		return $count;
	}

	/**
	 * Whether a posted checkbox value counts as selected.
	 *
	 * @param mixed $value Raw POST value.
	 * @return bool
	 */
	protected function is_selected_checkbox_value( $value ) {
		if ( null === $value || is_array( $value ) || is_object( $value ) ) {
			return false;
		}
		if ( ! is_scalar( $value ) ) {
			return false;
		}
		return '' !== (string) $value;
	}

	/**
	 * Normalize a limit setting to a non-negative int or null.
	 *
	 * @param mixed $value Raw setting.
	 * @return int|null
	 */
	protected function normalize_limit( $value ) {
		if ( '' === $value || null === $value || false === $value ) {
			return null;
		}
		if ( is_array( $value ) || is_object( $value ) ) {
			return null;
		}
		if ( ! is_numeric( $value ) ) {
			return null;
		}
		$int = (int) $value;
		return $int < 0 ? null : $int;
	}

	/**
	 * Build the validation / help message with {min}/{max} replaced.
	 *
	 * Always returns plain text (sanitize_text_field) so stored admin messages
	 * cannot inject HTML/JS into the validation output.
	 *
	 * @param GF_Field|array $field Field.
	 * @param int|null       $min   Min.
	 * @param int|null       $max   Max.
	 * @return string
	 */
	protected function build_message( $field, $min, $max ) {
		$custom = rgar( $field, 'gfclValidationMessage' );
		if ( is_string( $custom ) ) {
			$custom = sanitize_text_field( $custom );
		} else {
			$custom = '';
		}

		if ( '' === $custom ) {
			$setting = $this->get_plugin_setting( 'default_validation_message' );
			$custom  = is_string( $setting ) ? sanitize_text_field( $setting ) : '';
		}

		if ( '' === $custom ) {
			$custom = $this->default_message_for_limits( $min, $max );
		}

		$min_label = ( null === $min ) ? '0' : (string) (int) $min;
		$max_label = ( null === $max ) ? '∞' : (string) (int) $max;

		$message = str_replace(
			array( '{min}', '{max}' ),
			array( $min_label, $max_label ),
			$custom
		);

		return sanitize_text_field( $message );
	}

	/**
	 * Sensible default message when none is configured.
	 *
	 * Returns unescaped translated text; callers sanitize / GF escapes on output.
	 *
	 * @param int|null $min Min.
	 * @param int|null $max Max.
	 * @return string
	 */
	protected function default_message_for_limits( $min, $max ) {
		if ( null !== $min && null !== $max ) {
			if ( $min === $max ) {
				/* translators: %d: exact number of options required */
				return sprintf( __( 'Please choose exactly %d option(s).', 'checkbox-limits-for-gravity-forms' ), $min );
			}
			/* translators: 1: min 2: max */
			return sprintf( __( 'Please choose %1$d to %2$d options.', 'checkbox-limits-for-gravity-forms' ), $min, $max );
		}
		if ( null !== $min ) {
			/* translators: %d: minimum number of options */
			return sprintf( __( 'Please choose at least %d option(s).', 'checkbox-limits-for-gravity-forms' ), $min );
		}
		if ( null !== $max ) {
			/* translators: %d: maximum number of options */
			return sprintf( __( 'Please choose at most %d option(s).', 'checkbox-limits-for-gravity-forms' ), $max );
		}
		return __( 'Please adjust your selections.', 'checkbox-limits-for-gravity-forms' );
	}
}
