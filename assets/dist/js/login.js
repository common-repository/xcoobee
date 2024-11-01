"use strict";

(function ($) {
  $(document).ready(function () {
    // Update user label text.
    // I know this is ugly, but it is the best available solution.
    $('#loginform').find('label[for="user_login"]').contents().filter(function () {
      return this.nodeType == 3;
    }).each(function () {
      if (this.textContent.indexOf('\n') == -1) {
        this.textContent = xbeeLoginParams.userLogin;
      }
    });
    /**
     * Toggle the registeration confirmation message.
     */

    function regConfirmation(el) {
      if ($(el).is(':checked')) {
        $('#reg_passmail').css('display', 'none');
        $('#reg_xbee').css('display', 'block');
      } else {
        $('#reg_passmail').css('display', 'block');
        $('#reg_xbee').css('display', 'none');
      }
    }

    $('[name="xbee_message"]').on('change', function (e) {
      regConfirmation(this);
    });
    regConfirmation('[name="xbee_message"]');
  });
})(jQuery);