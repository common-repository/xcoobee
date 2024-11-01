"use strict";

(function ($, window, document, undefined) {
  /**
   * Display a spinner.
   * @param {string} spinner - Spinner data Id. 
   */
  window.xbeeShowSpinner = function xbeeShowSpinner(spinner) {
    $('.xbee-spinner[data-spinner="' + spinner + '"]').css('display', 'inline-block');
  };
  /**
   * Hide a spinner
   * @param {string} spinner - Spinner data Id.
   */


  window.xbeeHideSpinner = function xbeeHideSpinner(spinner) {
    $('.xbee-spinner[data-spinner="' + spinner + '"').css('display', 'none');
  };
  /**
   * Append overlay if not there.
   */


  window.xbeeLoadOverlay = function xbeeLoadOverlay() {
    if ($('#xbee-overlay').length === 0) {
      var overlay = $('<div></div>').attr('id', 'xbee-overlay');
      var loader = $('<div></div>').attr('class', 'loader');
      var xbeeImg = $('<img class="icon-xcoobee" />').attr('src', xbeeAdminParams.images.iconXcooBee);
      var loaderImg = $('<img class="loader" />').attr('src', xbeeAdminParams.images.loader);
      $(xbeeImg).appendTo(loader);
      $(loaderImg).appendTo(loader);
      $(loader).appendTo(overlay);
      $('body').append(overlay);
    }
  };
  /**
   * Display overlay.
   */


  window.xbeeShowOverlay = function xbeeShowOverlay() {
    $('#xbee-overlay').css('display', 'table').animate({
      opacity: 1
    }, 200);
  };
  /**
   * Hide overlay.
   */


  window.xbeeHideOverlay = function xbeeHideOverlay() {
    $('#xbee-overlay').animate({
      opacity: 0
    }, 200, function () {
      return $('#xbee-overlay').css('display', 'none');
    });
  };
  /**
   * Display inline notification.
   *
   * @param {string} notification - Notification Id.
   * @param {string} type - Notification type.
   * @param {string} message - Notification message.
   */


  window.xbeeNotification = function xbeeNotification(notification, type, message) {
    notification = $('.xbee-notification[data-notification="' + notification + '"]'); // Remove any appeneded classes.

    notification.attr('class', 'xbee-notification'); // Append notification type class.

    var typeClass = 'xbee-' + type;
    notification.addClass(typeClass); // Add notification message.

    notification.html('<span class="message">' + message + '</span>'); // Display notification message.

    notification.slideDown(500);
  };
  /**
   * Disallow specific characters on input fields.
   * 
   * @param {object} el - Input element.
   * @param {array} chars - The characters to disallow.
   */


  window.xbeeInputDisallowChars = function xbeeInputDisallowChars(el, chars) {
    // Prevent pasting.
    el.on('paste', function (e) {
      e.preventDefault();
    }); // Disallow chars on keypress.

    el.on('keypress', function (e) {
      chars.forEach(function (_char) {
        if (e.which === _char.charCodeAt(0)) {
          e.preventDefault();
        }
      });
    });
  };
  /**
   * On document ready.
   */


  $(document).ready(function () {
    /**
     * Hide notifications on click.
     */
    $('.xbee-notification').on('click', function () {
      $(this).slideUp(500);
    });
    /**
     * Disallow characters on input fields.
     */

    $('[data-xbee-disallow-chars]').each(function () {
      var chars = $(this).data('xbee-disallow-chars').split('');
      var el = $(this);
      xbeeInputDisallowChars(el, chars);
    });
    $('[data-xbee-show-if-checked]').each(function () {
      var checked = $(this).data('xbee-show-if-checked');
      var checkedEl = $('#' + checked);
      var el = $(this);
      checkedEl.on('change', function () {
        if ($(this).is(':checked')) {
          el.show();
        } else {
          el.hide();
        }
      }).trigger('change');
    });
    /**
     * Radio buttons group.
     */

    $('.radio-buttons-group input').each(function () {
      if ($(this).is(':checked')) {
        var id = $(this).attr('id');
        $('.radio-buttons-group label[for="' + id + '"]').addClass('checked');
      }
    });
    $('.radio-buttons-group label').on('click', function () {
      $(this).closest('.radio-buttons-group').find('input').prop('checked', false);
      $(this).closest('.radio-buttons-group').find('label').removeClass('checked');
      $(this).find('input').prop('checked', true);
      $(this).addClass('checked');
    }); // Tabs.

    $('.xbee .tabs .tabs-nav .nav').on('click', function (e) {
      e.preventDefault();
      var tabs = $(this).closest('.tabs');
      var nav = $(this);

      if (!nav.hasClass('active')) {
        var navId = nav.data('nav');
        tabs.find('.tabs-nav .nav').removeClass('active');
        tabs.find('.tabs-content .content').removeClass('active');
        nav.addClass('active');
        $('.tabs-content .content[data-nav="' + navId + '"]').addClass('active');
      }
    }); // Show overlay.

    xbeeLoadOverlay(); // Clear user message logs.

    $('#xbee-clear-message-logs').on('click', function (e) {
      e.preventDefault(); // Request data.

      var data = {
        'action': 'xbee_clear_message_logs',
        'userId': $(e.target).data('userId')
      }; // Response message.

      var message = '';
      $.ajax({
        url: xbeeAdminParams.ajaxUrl,
        method: 'post',
        data: data,
        success: function success(response) {
          response = JSON.parse(response);

          if (response.result) {
            message = xbeeAdminParams.messages.successClearMessageLogs;
          } else if (response.errors) {
            message = response.errors.join(' ');
          } else {
            message = xbeeAdminParams.messages.errorClearMessageLogs;
          }

          $('#xbee-message-logs tbody tr').css('background-color', '#e1615f').fadeOut(500, function () {
            $('#xbee-message-logs tbody tr').remove();
            $('#xbee-message-logs tbody').append('<tr><td>' + message + '</td></tr>');
          });
          $('.xbee-information .xbee-clear-message-logs').remove();
        },
        error: function error() {
          // Hide overlay.
          xbeeHideOverlay();
        }
      });
    }); // Test API keys.

    $('#xbee-settings-general #test-keys').on('click', function (e) {
      e.preventDefault(); // Request data.

      var data = {
        'action': 'xbee_test_keys',
        'apiKey': $('#xbee-settings-general [name="xbee_api_key"]').val(),
        'apiSecret': $('#xbee-settings-general [name="xbee_api_secret"]').val()
      }; // Response message.

      var message = '';
      $.ajax({
        url: xbeeAdminParams.ajaxUrl,
        method: 'post',
        data: data,
        beforeSend: function beforeSend() {
          // Show overlay.
          xbeeShowOverlay();
        },
        success: function success(response) {
          // Hide overlay.
          xbeeHideOverlay();
          response = JSON.parse(response);

          if (response.result) {
            message = xbeeAdminParams.messages.successValidKeys;
          } else if (response.errors) {
            message = response.errors.join(' ');
          } else {
            message = xbeeAdminParams.messages.errorTestKeys;
          } // Response notification.


          xbeeNotification('test-api-keys', response.status, message);
        },
        error: function error() {
          // Hide overlay.
          xbeeHideOverlay(); // Response notification.

          xbeeNotification('test-api-keys', response.status, xbeeAdminParams.messages.errorTestKeys.message);
        }
      });
    });
    /**
     * Highlight input fields when maximum length is reached.
     */

    $('textarea, input').each(function () {
      var el = $(this);
      var maxLength = el.attr('maxlength');

      if (maxLength) {
        el.on('keyup change paste', function (event) {
          if (el.data('xbee-maxlen') == '1' && event.target.value.length >= maxLength) {
            el.css('border-color', '#f00');
            el.css('box-shadow', '0 0 5px #ff7e7e');
            setTimeout(function () {
              el.css('border-color', '');
              el.css('box-shadow', '');
            }, 100);
          } else if (event.target.value.length >= maxLength) {
            el.data('xbee-maxlen', '1');
          } else {
            el.data('xbee-maxlen', '0');
          }
        });
      }
    });
    /**
     * Prevent typing values less/greater than the min/max values of a number input field.
     */

    $('input[type="number"]').each(function () {
      var el = $(this);
      var max = Number(el.attr('max'));
      var min = Number(el.attr('min'));
      el.on('keyup keydown change paste', function (event) {
        var val = Number(event.target.value);
        console.log(max);

        if (typeof max === 'number' && val > max) {
          el.val(max);
          event.preventDefault();
        }

        if (typeof min === 'number' && val < min) {
          el.val(min);
          event.preventDefault();
        }
      });
    });
    /**
     * Create and/or display tooltip on mouseover.
     */

    $('.xbee-tooltip').on('mouseover', function () {
      var tooltip = $(this).children('.tt');

      if (tooltip.length === 0) {
        $(this).append("<span class=\"tt\">".concat($(this).data('tooltip'), "</span>"));
      }

      $(this).find('.tt').show();
    });
    /**
     * Hide tooltip on mouseout.
     */

    $('.xbee-tooltip').on('mouseout', function () {
      $(this).find('.tt').hide();
    });
  });
})(jQuery, window, document);