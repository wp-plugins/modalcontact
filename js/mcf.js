// make sure jQuery and SimpleModal are loaded
if (typeof jQuery !== "undefined" && typeof jQuery.modal !== "undefined") {
	jQuery(function ($) {
		$('.mcf_link, .mcf-link').click(function (e) { // added .mcf_link for previous version
			e.preventDefault();
			// display the contact form
			$('#mcf-content').modal({
				closeHTML: "<a href='#' title='Close' class='modalCloseX simplemodal-close'>x</a>",
				position: ["15%",],
				overlayId: 'mcf-overlay',
				containerId: 'mcf-container',
				onOpen: contact.open,
				onShow: contact.show,
				onClose: contact.close
			});
		});

		// preload images
		var img = ['cancel.png','form_bottom.gif','form_top.gif','loading.gif','send.png'];
		if ($('#mcf-content form').length > 0) {
			var url = $('#mcf-content form').attr('action').replace(/mcf_data\.php/, 'img/');
			$(img).each(function () {
				var i = new Image();
				i.src = url + this;
			});
		}

		var contact = {
			message: null,
			open: function (d) {
				// dynamically determine height
				var h = 280;
				if ($('#mcf-subject').length) {
					h += 26;
				}
				if ($('#mcf-cc').length) {
					h += 22;
				}
	
				// resize the textarea for safari
				if ($.browser.safari) {
					$('#mcf-container .mcf-input').css({
						'font-size': '.9em'
					});
				}
	
				// add padding to the buttons in firefox/mozilla
				if ($.browser.mozilla) {
					$('#mcf-container .mcf-button').css({
						'padding-bottom': '2px'
					});
				}
	
				var title = $('#mcf-container .mcf-title').html();
				$('#mcf-container .mcf-title').html(mcf_messages.loading);
				d.overlay.fadeIn(200, function () {
					d.container.fadeIn(200, function () {
						d.data.fadeIn(200, function () {
							$('#mcf-container .mcf-content').animate({
								height: h
							}, function () {
								$('#mcf-container .mcf-title').html(title);
								$('#mcf-container form').fadeIn(200, function () {
									$('#mcf-container #mcf-name').focus();
	
									$('#mcf-container .mcf-cc').click(function () {
										var cc = $('#mcf-container #mcf-cc');
										cc.is(':checked') ? cc.attr('checked', '') : cc.attr('checked', 'checked');
									});
	
									// fix png's for IE 6
									if ($.browser.msie && $.browser.version < 7) {
										$('#mcf-container .mcf-button').each(function () {
											if ($(this).css('backgroundImage').match(/^url[("']+(.*\.png)[)"']+$/i)) {
												var src = RegExp.$1;
												$(this).css({
													backgroundImage: 'none',
													filter: 'progid:DXImageTransform.Microsoft.AlphaImageLoader(src="' +  src + '", sizingMethod="crop")'
												});
											}
										});
									}
								});
							});
						});
					});
				});
			},
			show: function (d) {
				$('#mcf-container .mcf-send').click(function (e) {
					e.preventDefault();
					// validate form
					if (contact.validate()) {
						$('#mcf-container .mcf-message').fadeOut(function () {
							$('#mcf-container .mcf-message').removeClass('mcf-error').empty();
						});
						$('#mcf-container .mcf-title').html(mcf_messages.sending);
						$('#mcf-container form').fadeOut(200);
						$('#mcf-container .mcf-content').animate({
							height: '90px'
						}, function () {
							$('#mcf-container .mcf-loading').fadeIn(200, function () {
								$.ajax({
									url: $('#mcf-content form').attr('action'),
									data: $('#mcf-container form').serialize() + '&action=send',
									type: 'post',
									cache: false,
									dataType: 'html',
									success: function (data) {
										$('#mcf-container .mcf-loading').fadeOut(200, function () {
											$('#mcf-container .mcf-title').html(mcf_messages.thankyou);
											$('#mcf-container .mcf-message').html(data).fadeIn(200);
										});
									},
									error: function (xhr) {
										$('#mcf-container .mcf-loading').fadeOut(200, function () {
											$('#mcf-container .mcf-title').html(mcf_messages.error);
											$('#mcf-container .mcf-message').html(xhr.status + ': ' + xhr.statusText).fadeIn(200);
										});
									}
								});
							});
						});
					}
					else {
						if ($('#mcf-container .mcf-message:visible').length > 0) {
						var msg = $('#mcf-container .mcf-message div');
							msg.fadeOut(200, function () {
								msg.empty();
								contact.showError();
								msg.fadeIn(200);
							});
						}
						else {
							$('#mcf-container .mcf-message').animate({
								height: '30px'
							}, contact.showError);
						}
					}
				});
			},
			close: function (d) {
				$('#mcf-container .mcf-message').fadeOut();
				$('#mcf-container .mcf-title').html(mcf_messages.goodbye);
				$('#mcf-container form').fadeOut(200);
				$('#mcf-container .mcf-content').animate({
					height: '40px'
				}, function () {
					d.data.fadeOut(200, function () {
						d.container.fadeOut(200, function () {
							d.overlay.fadeOut(200, function () {
								$.modal.close();
							});
						});
					});
				});
			},
			validate: function () {
				contact.message = '';
				var req = [],
					invalid = "";
	
				if (!$('#mcf-container #mcf-name').val()) {
					req.push(mcf_messages.name);
				}
	
				var email = $('#mcf-container #mcf-email').val();
				if (!email) {
					req.push(mcf_messages.email);
				}
				else {
					if (!contact.validateEmail(email)) {
						invalid = mcf_messages.emailinvalid;
					}
				}
	
				if (!$('#mcf-container #mcf-message').val()) {
					req.push(mcf_messages.message);
				}
	
				if (req.length > 0) {
					var fields = req.join(', ');
					contact.message += req.length > 1 ?
						fields.replace(/(.*),/,'$1 ' + mcf_messages.and) + ' ' + mcf_messages.are :
						fields + ' ' + mcf_messages.is;
					contact.message += ' ' + mcf_messages.required;
				}
	
				if (invalid.length > 0) {
					contact.message += (req.length > 0 ? ' ' : '') + mcf_messages.emailinvalid;
				}
	
				if (contact.message.length > 0) {
					return false;
				}
				else {
					return true;
				}
			},
			validateEmail: function (email) {
				var at = email.lastIndexOf("@");
	
				// Make sure the at (@) sybmol exists and  
				// it is not the first or last character
				if (at < 1 || (at + 1) === email.length)
					return false;
	
				// Make sure there aren't multiple periods together
				if (/(\.{2,})/.test(email))
					return false;
	
				// Break up the local and domain portions
				var local = email.substring(0, at);
				var domain = email.substring(at + 1);
	
				// Check lengths
				if (local.length < 1 || local.length > 64 || domain.length < 4 || domain.length > 255)
					return false;
	
				// Make sure local and domain don't start with or end with a period
				if (/(^\.|\.$)/.test(local) || /(^\.|\.$)/.test(domain))
					return false;
	
				// Check for quoted-string addresses
				// Since almost anything is allowed in a quoted-string address,
				// we're just going to let them go through
				if (!/^"(.+)"$/.test(local)) {
					// It's a dot-string address...check for valid characters
					if (!/^[-a-zA-Z0-9!#$%*\/?|^{}`~&'+=_\.]*$/.test(local))
						return false;
				}
	
				// Make sure domain contains only valid characters and at least one period
				if (!/^[-a-zA-Z0-9\.]*$/.test(domain) || domain.indexOf(".") === -1)
					return false;	
	
				return true;
			},
			showError: function () {
				$('#mcf-container .mcf-message')
					.html($('<div/>').addClass('mcf-error').append(contact.message))
					.fadeIn(200);
			}
		};
	});
}