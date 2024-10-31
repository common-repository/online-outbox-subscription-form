(function($) {
	$(function() {
		try {
			if (typeof _oosf == 'undefined' || _oosf === null)
				_oosf = {};

			_oosf = $.extend({ cached: 0 }, _oosf);

			$('div.oosf > form').ajaxForm({
				beforeSubmit: function(formData, jqForm, options) {
					jqForm.oosfClearResponseOutput();
					jqForm.find('img.ajax-loader').css({ visibility: 'visible' });
					return true;
				},
				beforeSerialize: function(jqForm, options) {
					jqForm.find('.oosf-use-title-as-watermark.watermark').each(function(i, n) {
						$(n).val('');
					});
					return true;
				},
				data: { '_oosf_is_ajax_call': 1 },
			dataType: 'json',
				success: function(data) {
					var ro = $(data.into).find('div.oosf-response-output');
					$(data.into).oosfClearResponseOutput();

					if (data.invalids) {
						$.each(data.invalids, function(i, n) {
							$(data.into).find(n.into).oosfNotValidTip(n.message);
						});
						ro.addClass('oosf-validation-errors');
					}

					if (data.captcha)
						$(data.into).oosfRefillCaptcha(data.captcha);

					if (data.quiz)
						$(data.into).oosfRefillQuiz(data.quiz);

					if (1 == data.spam)
						ro.addClass('oosf-spam-blocked');

					if (1 == data.mailSent) {
						$(data.into).find('form').resetForm().clearForm();
						ro.addClass('oosf-mail-sent-ok');

						if (data.onSentOk)
							$.each(data.onSentOk, function(i, n) { eval(n) });
					} else {
						ro.addClass('oosf-mail-sent-ng');
					}

					if (data.onSubmit)
						$.each(data.onSubmit, function(i, n) { eval(n) });

					$(data.into).find('.oosf-use-title-as-watermark.watermark').each(function(i, n) {
						$(n).val($(n).attr('title'));
					});

					ro.append(data.message).slideDown('fast');
				}
			});

			$('div.oosf > form').each(function(i, n) {
				if (_oosf.cached)
					$(n).oosfOnloadRefill();

				$(n).oosfToggleSubmit();

				$(n).find('.oosf-acceptance').click(function() {
					$(n).oosfToggleSubmit();
				});

				$(n).find('.oosf-exclusive-checkbox').each(function(i, n) {
					$(n).find('input:checkbox').click(function() {
						$(n).find('input:checkbox').not(this).removeAttr('checked');
					});
				});

				$(n).find('.oosf-use-title-as-watermark').each(function(i, n) {
					var input = $(n);
					input.val(input.attr('title'));
					input.addClass('watermark');

					input.focus(function() {
						if ($(this).hasClass('watermark'))
							$(this).val('').removeClass('watermark');
					});

					input.blur(function() {
						if ('' == $(this).val())
							$(this).val($(this).attr('title')).addClass('watermark');
					});
				});
			});

		} catch (e) {
		}
	});

	$.fn.oosfToggleSubmit = function() {
		return this.each(function() {
			var form = $(this);
			if (this.tagName.toLowerCase() != 'form')
				form = $(this).find('form').first();

			if (form.hasClass('oosf-acceptance-as-validation'))
				return;

			var submit = form.find('input:submit');
			if (! submit.length) return;

			var acceptances = form.find('input:checkbox.oosf-acceptance');
	if (! acceptances.length) return;

			submit.removeAttr('disabled');
			acceptances.each(function(i, n) {
				n = $(n);
				if (n.hasClass('oosf-invert') && n.is(':checked')
				|| ! n.hasClass('oosf-invert') && ! n.is(':checked'))
					submit.attr('disabled', 'disabled');
			});
		});
	};

	$.fn.oosfNotValidTip = function(message) {
		return this.each(function() {
			var into = $(this);
			into.append('<span class="oosf-not-valid-tip">' + message + '</span>');
			$('span.oosf-not-valid-tip').mouseover(function() {
				$(this).fadeOut('fast');
			});
			into.find(':input').mouseover(function() {
				into.find('.oosf-not-valid-tip').not(':hidden').fadeOut('fast');
			});
			into.find(':input').focus(function() {
				into.find('.oosf-not-valid-tip').not(':hidden').fadeOut('fast');
			});
		});
	};

	$.fn.oosfOnloadRefill = function() {
		return this.each(function() {
			var url = $(this).attr('action');
			if (0 < url.indexOf('#'))
				url = url.substr(0, url.indexOf('#'));

			var id = $(this).find('input[name="_oosf"]').val();
			var unitTag = $(this).find('input[name="_oosf_unit_tag"]').val();

			$.getJSON(url,
				{ _oosf_is_ajax_call: 1, _oosf: id },
				function(data) {
					if (data && data.captcha)
						$('#' + unitTag).oosfRefillCaptcha(data.captcha);

					if (data && data.quiz)
						$('#' + unitTag).oosfRefillQuiz(data.quiz);
				}
			);
		});
	};

	$.fn.oosfRefillCaptcha = function(captcha) {
		return this.each(function() {
			var form = $(this);

			$.each(captcha, function(i, n) {
				form.find(':input[name="' + i + '"]').clearFields();
				form.find('img.oosf-captcha-' + i).attr('src', n);
				var match = /([0-9]+)\.(png|gif|jpeg)$/.exec(n);
				form.find('input:hidden[name="_oosf_captcha_challenge_' + i + '"]').attr('value', match[1]);
			});
		});
	};

	$.fn.oosfRefillQuiz = function(quiz) {
		return this.each(function() {
			var form = $(this);

			$.each(quiz, function(i, n) {
				form.find(':input[name="' + i + '"]').clearFields();
				form.find(':input[name="' + i + '"]').siblings('span.oosf-quiz-label').text(n[0]);
				form.find('input:hidden[name="_oosf_quiz_answer_' + i + '"]').attr('value', n[1]);
			});
		});
	};

	$.fn.oosfClearResponseOutput = function() {
		return this.each(function() {
			$(this).find('div.oosf-response-output').hide().empty().removeClass('oosf-mail-sent-ok oosf-mail-sent-ng oosf-validation-errors oosf-spam-blocked');
			$(this).find('span.oosf-not-valid-tip').remove();
			$(this).find('img.ajax-loader').css({ visibility: 'hidden' });
		});
	};

})(jQuery);