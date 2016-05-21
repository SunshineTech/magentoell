/**
 * Separation Degrees One
 *
 * eCal Lite download request extension
 *
 * @category  SDM
 * @package   SDM_EcalLite
 * @author    Separation Degrees One <magento@separationdegrees.com>
 * @copyright Copyright (c) 2015 Separation Degrees One (http://www.separationdegrees.com)
 */

(function($) {
    $.noConflict();
    $(document).ready(function() {

        // This forces input switch when trying to correct an input
        // $(".ecal-code-input").keyup(function () {
        //     if (this.value.length == this.maxLength) {
        //       $(this).next('.ecal-code-input').focus();
        //     }
        // });

    });


})(jQuery);

Validation.addAllThese([
    ['validate-cemail', 'Please make sure your emails match.', function(v) {
        var conf = $('confirmation') ? $('confirmation') : $$('.validate-cemail')[0];
        var pass = false;
        var confirm;
        if ($('email')) {
            pass = $('email');
        }
        confirm =conf.value;
        if(!confirm && $('email2')) {
            confirm = $('email2').value;
        }
        return (pass.value == confirm);
    }],
]);