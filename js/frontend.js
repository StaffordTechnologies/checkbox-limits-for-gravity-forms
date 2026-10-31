/**
 * Gravity Forms Checkbox Limits — front-end enforcer.
 * Disables remaining unchecked boxes once max is reached; re-enables on uncheck.
 * Re-applies after multi-page AJAX renders via gform_post_render / init scripts.
 */
(function (window, $) {
	'use strict';

	var store = {};

	function fieldSelector(formId, fieldId) {
		return '#field_' + formId + '_' + fieldId;
	}

	function getCheckboxes($field) {
		return $field.find('input[type="checkbox"]').filter(function () {
			// Exclude GF "Select All" helper if present.
			var name = this.name || '';
			return name.indexOf('input_') === 0;
		});
	}

	function applyField(formId, fieldId, config) {
		var $field = $(fieldSelector(formId, fieldId));
		if (!$field.length || !config) {
			return;
		}

		var max = config.max;
		if (max === null || typeof max === 'undefined' || max === '') {
			return; // Min-only: server validates; no disable needed.
		}
		max = parseInt(max, 10);
		if (isNaN(max) || max < 0) {
			return;
		}

		var $boxes = getCheckboxes($field);
		var checked = $boxes.filter(':checked').length;

		$boxes.each(function () {
			var $box = $(this);
			if ($box.is(':checked')) {
				$box.prop('disabled', false);
				$box.closest('li, .gchoice').removeClass('gfcl-disabled');
			} else if (checked >= max) {
				$box.prop('disabled', true);
				$box.closest('li, .gchoice').addClass('gfcl-disabled');
			} else {
				$box.prop('disabled', false);
				$box.closest('li, .gchoice').removeClass('gfcl-disabled');
			}
		});
	}

	function applyForm(formId) {
		var cfg = store[formId];
		if (!cfg || !cfg.fields) {
			return;
		}
		Object.keys(cfg.fields).forEach(function (fieldId) {
			applyField(formId, fieldId, cfg.fields[fieldId]);
		});
	}

	function bindForm(formId) {
		var $form = $('#gform_' + formId);
		if (!$form.length) {
			return;
		}
		// Namespace so re-init does not stack handlers.
		$form.off('change.gfcl').on('change.gfcl', 'input[type="checkbox"]', function () {
			var name = this.name || '';
			var match = name.match(/^input_(\d+)/);
			if (!match) {
				return;
			}
			var fieldId = match[1];
			var cfg = store[formId] && store[formId].fields && store[formId].fields[fieldId];
			if (cfg) {
				applyField(formId, fieldId, cfg);
			}
		});
	}

	window.GFCheckboxLimits = {
		/**
		 * Called from gform_register_init_scripts on each page render.
		 * @param {{formId:number, fields:Object}} config
		 */
		init: function (config) {
			if (!config || !config.formId) {
				return;
			}
			var formId = config.formId;
			store[formId] = config;
			bindForm(formId);
			applyForm(formId);
		}
	};

	// Safety net for AJAX page changes / partial re-renders.
	$(document).on('gform_post_render', function (event, formId) {
		if (store[formId]) {
			bindForm(formId);
			applyForm(formId);
		}
	});
})(window, jQuery);
