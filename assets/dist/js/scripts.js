"use strict";

(function ($, window, document, undefined) {
  /**
   * Display a popup modal with message.
   *
   * @param {string}  id
   * @param {type}    type
   * @param {message} message
   */
  window.xbeeLoadModal = function xbeeLoadModal() {
    var id = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : '';
    var title = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : '';
    var message = arguments.length > 2 && arguments[2] !== undefined ? arguments[2] : '';
    var type = arguments.length > 3 && arguments[3] !== undefined ? arguments[3] : '';

    if ($('.xbee-modal[data-xbee-modal="' + id + '"]').length === 0) {
      var modalContainer = $('<div></div>').addClass('xbee-modal-container ' + type);
      var modal = $('<div></div>').addClass('xbee-modal').attr('data-xbee-modal', id).attr('data-xbee-modal-type', type);
      var modalHeader = $('<div></div>').addClass('xbee-modal-header');
      var modalTitle = $('<span></span>').addClass('xbee-modal-title').html(title);
      var modalClose = $('<img />').addClass('xbee-modal-close').attr('src', xbeeParams.images.close);
      var modalMessage = $('<div></div>').addClass('xbee-modal-message').html(message);
      $(modalTitle).appendTo(modalHeader);
      $(modalClose).appendTo(modalHeader);
      $(modalHeader).appendTo(modal);
      $(modalMessage).appendTo(modal);
      $(modal).appendTo(modalContainer);
      $('body').append(modalContainer);
    }
  };
  /**
   * Check whether the current visitor is logged in to XcooBee.
   */


  window.xbeeIsLoggedIn = function xbeeIsLoggedIn() {
    // Flag to make sure status check is completed.
    localStorage.setItem('xbeeStatusCheck', false);
    var xbeeIframe = document.getElementById('xbeeStatusCheck');

    if (!xbeeIframe) {
      xbeeIframe = document.createElement('iframe');
      xbeeIframe.id = 'xbeeStatusCheck';
      xbeeIframe.style.width = '0';
      xbeeIframe.style.height = '0';
      xbeeIframe.style.display = 'none';
      document.body.appendChild(xbeeIframe);
    }

    var xbeeUrl = 'https://app.xcoobee.net';

    if (xbeeParams.env === 'test') {
      xbeeUrl = 'https://testapp.xcoobee.net';
    }

    xbeeIframe.setAttribute('src', xbeeUrl + '/scripts/status/statuscheck.html'); // On iframe load.

    xbeeIframe.onload = function () {
      this.contentWindow.postMessage(JSON.stringify({
        action: "loginstatus"
      }), xbeeUrl);
    }; // Listen to messages from the iframe.


    window.addEventListener('message', function (event) {
      var data = JSON.parse(event.data);

      if (event.origin === xbeeUrl) {
        localStorage.setItem('xbeeLogin', data.loginstatus);
        localStorage.setItem('xbeeStatusCheck', true);
      }
    }, false); // Don't return the result until status check is completed.

    while (localStorage.getItem('xbeeStatusCheck') === 'false') {
      if (localStorage.xbeeLogin && localStorage.xbeeLogin === 'true') {
        return true;
      }

      return false;
    }
  };
  /**
   * On document ready.
   */


  $(document).ready(function () {
    /**
     * Remove modal on close.
     */
    $(document).on('click', '.xbee-modal-close', function () {
      $(this).closest('.xbee-modal-container').remove();
    });
  });
})(jQuery, window, document);