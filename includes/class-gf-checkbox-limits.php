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
	protected $_slug = 'gf-checkbox-limits';

	/**
	 * Relative path from wp-content/plugins.
	 *
	 * @var string
	 */
	protected $_path = 'gf-checkbox-limits/gf-checkbox-limits.php';

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
	protected $_title = 'Gravity Forms Checkbox Limits';

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
	 * @return array
	 */
	public function plugin_settings_fields() {
		return array(
			array(
				'title'  => esc_html__( 'Checkbox Limits', 'gf-checkbox-limits' ),
				'fields' => array(
					array(
						'name'          => 'default_validation_message',
						'label'         => esc_html__( 'Default validation message', 'gf-checkbox-limits' ),
						'type'          => 'text',
						'class'         => 'large',
						'default_value' => esc_html__( 'Please choose {min} to {max} options.', 'gf-checkbox-limits' ),
						'tooltip'       => esc_html__( 'Used when a checkbox field has min/max limits but no custom message. Placeholders: {min}, {max}.', 'gf-checkbox-limits' ),
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
				<?php esc_html_e( 'Min selections', 'gf-checkbox-limits' ); ?>
				<?php gform_tooltip( 'gfcl_min_selections' ); ?>
			</label>
			<input type="number" id="gfcl_min_selections" min="0" step="1" class="small"
				onchange="SetFieldProperty('gfclMinSelections', this.value);"
				oninput="SetFieldProperty('gfclMinSelections', this.value);" />
		</li>
		<li class="gfcl_setting field_setting">
			<label for="gfcl_max_selections" class="section_label">
				<?php esc_html_e( 'Max selections', 'gf-checkbox-limits' ); ?>
				<?php gform_tooltip( 'gfcl_max_selections' ); ?>
			</label>
			<input type="number" id="gfcl_max_selections" min="0" step="1" class="small"
				onchange="SetFieldProperty('gfclMaxSelections', this.value);"
				oninput="SetFieldProperty('gfclMaxSelections', this.value);" />
		</li>
		<li class="gfcl_setting field_setting">
			<label for="gfcl_validation_message" class="section_label">
				<?php esc_html_e( 'Selection limit message', 'gf-checkbox-limits' ); ?>
				<?php gform_tooltip( 'gfcl_validation_message' ); ?>
			</label>
			<input type="text" id="gfcl_validation_message" class="fieldwidth-3"
				placeholder="<?php echo esc_attr__( 'Please choose {min} to {max} options.', 'gf-checkbox-limits' ); ?>"
				onchange="SetFieldProperty('gfclValidationMessage', this.value);"
				oninput="SetFieldProperty('gfclValidationMessage', this.value);" />
			<p class="gfcl-help-text" style="margin:4px 0 0;">
				<?php esc_html_e( 'Optional. Placeholders: {min}, {max}. Leave blank to use the add-on default.', 'gf-checkbox-limits' ); ?>
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
						tooltips.gfcl_min_selections = '<h6><?php echo esc_js( __( 'Min selections', 'gf-checkbox-limits' ) ); ?></h6><?php echo esc_js( __( 'Minimum number of checkboxes the user must select. Leave blank for no minimum.', 'gf-checkbox-limits' ) ); ?>';
						tooltips.gfcl_max_selections = '<h6><?php echo esc_js( __( 'Max selections', 'gf-checkbox-limits' ) ); ?></h6><?php echo esc_js( __( 'Maximum number of checkboxes the user may select. Remaining boxes are disabled once this limit is reached. Leave blank for no maximum.', 'gf-checkbox-limits' ) ); ?>';
						tooltips.gfcl_validation_message = '<h6><?php echo esc_js( __( 'Selection limit message', 'gf-checkbox-limits' ) ); ?></h6><?php echo esc_js( __( 'Shown when the selection count is outside the min/max range. Use {min} and {max} placeholders.', 'gf-checkbox-limits' ) ); ?>';
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
	 * @param array $form         Form object.
	 * @param array $field_values Field values.
	 * @param bool  $is_ajax      Whether AJAX is enabled.
	 * @return void
	 */
	public function register_init_scripts( $form, $field_values, $is_ajax ) {
		unset( $field_values, $is_ajax );

		$limits = $this->get_form_checkbox_limits( $form );
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
	 * @param array $form Form object.
	 * @return array<int, array{min:?int,max:?int,message:string}>
	 */
	public function get_form_checkbox_limits( $form ) {
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

			$limits[ (int) $field->id ] = array(
				'min'     => $min,
				'max'     => $max,
				'message' => $this->build_message( $field, $min, $max ),
			);
		}

		return $limits;
	}

	/**
	 * Server-side validation, current page only (multi-page safe).
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
		$source_page = (int) rgpost( 'gform_source_page_number_' . $form_id );
		if ( $source_page < 1 ) {
			$source_page = 1;
		}

		$failed = false;

		foreach ( $form['fields'] as &$field ) {
			if ( ! is_object( $field ) || 'checkbox' !== $field->type ) {
				continue;
			}

			// Only validate fields on the page being submitted.
			$field_page = isset( $field->pageNumber ) ? (int) $field->pageNumber : 1;
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
	 * @param int      $form_id Form ID.
	 * @param GF_Field $field   Field object.
	 * @return int
	 */
	protected function count_checkbox_selections( $form_id, $field ) {
		$count   = 0;
		$choices = is_array( $field->inputs ) ? $field->inputs : array();

		if ( ! empty( $choices ) ) {
			foreach ( $choices as $input ) {
				$input_id = rgar( $input, 'id' );
				if ( '' === $input_id || null === $input_id ) {
					continue;
				}
				// POST key uses underscore: input_10_1 for input id 10.1
				$post_key = 'input_' . str_replace( '.', '_', (string) $input_id );
				$value    = rgpost( $post_key );
				if ( '' !== $value && null !== $value ) {
					$count++;
				}
			}
			return $count;
		}

		// Fallback: scan POST for input_{fieldId}_*
		$prefix = 'input_' . (int) $field->id . '_';
		// phpcs:ignore WordPress.Security.NonceVerification.Missing -- GF handles nonce.
		foreach ( $_POST as $key => $value ) {
			if ( 0 === strpos( (string) $key, $prefix ) && '' !== $value && null !== $value ) {
				$count++;
			}
		}

		return $count;
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
		if ( ! is_numeric( $value ) ) {
			return null;
		}
		$int = (int) $value;
		return $int < 0 ? null : $int;
	}

	/**
	 * Build the validation / help message with {min}/{max} replaced.
	 *
	 * @param GF_Field|array $field Field.
	 * @param int|null       $min   Min.
	 * @param int|null       $max   Max.
	 * @return string
	 */
	protected function build_message( $field, $min, $max ) {
		$custom = rgar( $field, 'gfclValidationMessage' );
		if ( is_string( $custom ) ) {
			$custom = trim( $custom );
		} else {
			$custom = '';
		}

		if ( '' === $custom ) {
			$custom = $this->get_plugin_setting( 'default_validation_message' );
		}

		if ( ! is_string( $custom ) || '' === trim( $custom ) ) {
			$custom = $this->default_message_for_limits( $min, $max );
		}

		$min_label = ( null === $min ) ? '0' : (string) $min;
		$max_label = ( null === $max ) ? '∞' : (string) $max;

		$message = str_replace(
			array( '{min}', '{max}' ),
			array( $min_label, $max_label ),
			$custom
		);

		return $message;
	}

	/**
	 * Sensible default message when none is configured.
	 *
	 * @param int|null $min Min.
	 * @param int|null $max Max.
	 * @return string
	 */
	protected function default_message_for_limits( $min, $max ) {
		if ( null !== $min && null !== $max ) {
			if ( $min === $max ) {
				/* translators: %d: exact number of options required */
				return sprintf( esc_html__( 'Please choose exactly %d option(s).', 'gf-checkbox-limits' ), $min );
			}
			/* translators: 1: min 2: max */
			return sprintf( esc_html__( 'Please choose %1$d to %2$d options.', 'gf-checkbox-limits' ), $min, $max );
		}
		if ( null !== $min ) {
			/* translators: %d: minimum number of options */
			return sprintf( esc_html__( 'Please choose at least %d option(s).', 'gf-checkbox-limits' ), $min );
		}
		if ( null !== $max ) {
			/* translators: %d: maximum number of options */
			return sprintf( esc_html__( 'Please choose at most %d option(s).', 'gf-checkbox-limits' ), $max );
		}
		return esc_html__( 'Please adjust your selections.', 'gf-checkbox-limits' );
	}
}
