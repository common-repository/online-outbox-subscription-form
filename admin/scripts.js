jQuery(document).ready(function() {
	try {
		jQuery.extend(jQuery.tgPanes, _oosf.tagGenerators);
		jQuery('#taggenerator').tagGenerator(_oosfL10n.generateTag,
			{ dropdownIconUrl: _oosf.pluginUrl + '/images/dropdown.gif' });

		jQuery('input#oosf-title:enabled').css({
			cursor: 'pointer'
		});

		jQuery('input#oosf-title').mouseover(function() {
			jQuery(this).not('.focus').css({
				'background-color': '#ffffdd'
			});
		});

		jQuery('input#oosf-title').mouseout(function() {
			jQuery(this).css({
				'background-color': '#fff'
			});
		});

		jQuery('input#oosf-title').focus(function() {
			jQuery(this).addClass('focus');
			jQuery(this).css({
				cursor: 'text',
				color: '#333',
				border: '1px solid #777',
				font: 'normal 13px Verdana, Arial, Helvetica, sans-serif',
				'background-color': '#fff'
			});
		});

		jQuery('input#oosf-title').blur(function() {
			jQuery(this).removeClass('focus');
			jQuery(this).css({
				cursor: 'pointer',
				color: '#555',
				border: 'none',
				font: 'bold 20px serif',
				'background-color': '#fff'
			});
		});

		jQuery('input#oosf-title').change(function() {
			updateTag();
		});

		updateTag();

		if (jQuery.support.objectAll) {
			if (! jQuery('#oosf-mail-2-active').is(':checked'))
				jQuery('#mail-2-fields').hide();

			jQuery('#oosf-mail-2-active').click(function() {
				if (jQuery('#mail-2-fields').is(':hidden')
				&& jQuery('#oosf-mail-2-active').is(':checked')) {
					jQuery('#mail-2-fields').slideDown('fast');
				} else if (jQuery('#mail-2-fields').is(':visible')
				&& jQuery('#oosf-mail-2-active').not(':checked')) {
					jQuery('#mail-2-fields').slideUp('fast');
				}
			});
		}

		jQuery('#message-fields-toggle-switch').text(_oosfL10n.show);
		jQuery('#message-fields').hide();

		jQuery('#message-fields-toggle-switch').click(function() {
			if (jQuery('#message-fields').is(':hidden')) {
				jQuery('#message-fields').slideDown('fast');
				jQuery('#message-fields-toggle-switch').text(_oosfL10n.hide);
			} else {
				jQuery('#message-fields').hide('fast');
				jQuery('#message-fields-toggle-switch').text(_oosfL10n.show);
			}
		});

		if ('' == jQuery.trim(jQuery('#oosf-additional-settings').text())) {
			jQuery('#additional-settings-fields-toggle-switch').text(_osfL10n.show);
			jQuery('#additional-settings-fields').hide();
		} else {
			jQuery('#additional-settings-fields-toggle-switch').text(_oosfL10n.hide);
			jQuery('#additional-settings-fields').show();
		}

		jQuery('#additional-settings-fields-toggle-switch').click(function() {
			if (jQuery('#additional-settings-fields').is(':hidden')) {
				jQuery('#additional-settings-fields').slideDown('fast');
				jQuery('#additional-settings-fields-toggle-switch').text(_oosfL10n.hide);
			} else {
				jQuery('#additional-settings-fields').hide('fast');
				jQuery('#additional-settings-fields-toggle-switch').text(_oosfL10n.show);
			}
		});

	} catch (e) {
	}
});

function updateTag() {
	var title = jQuery('input#oosf-title').val();

	if (title)
		title = title.replace(/["'\[\]]/g, '');

	jQuery('input#oosf-title').val(title);
	var current = jQuery('input#oosf-id').val();
	var tag = '[oo-subscribe-form ' + current + ' "' + title + '"]';

	jQuery('input#subscribe-form-anchor-text').val(tag);
}
