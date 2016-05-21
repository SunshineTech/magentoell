jQuery.noConflict();

// Rewriting of tinyMCE setup
if (typeof(tinyMceWysiwygSetup) != 'undefined') {

    // Rewrite initialization script
    tinyMceWysiwygSetup.prototype.initialize = function(htmlId, config)
    {
        this.id = htmlId;
        this.config = config;
        varienGlobalEvents.attachEventHandler('tinymceChange', this.onChangeContent.bind(this));

        // Here goes binding of new event and it's listener
        varienGlobalEvents.attachEventHandler('tinymceKeyUp', this.onKeyUp.bind(this));
        this.notifyFirebug();
        if(typeof tinyMceEditors == 'undefined') {
            tinyMceEditors = $H({});
        }
        tinyMceEditors.set(this.id, this);
    }

    // New listener member - here key pressing detected and handled
    tinyMceWysiwygSetup.prototype.onKeyUp = function () {
        if(tinyMCE.activeEditor && $('saveTicketBtn')) {
            if(tinyMCE.activeEditor.getContent() == '') {
                $('saveTicketBtn').innerHTML = '<span>Update</span>';
                $('saveAndContinueTicketBtn').innerHTML = '<span>Update And Continue Edit</span>';
            } else {
                $('saveTicketBtn').innerHTML = '<span> Send </span>';
                $('saveAndContinueTicketBtn').innerHTML = '<span> Send And Continue Edit </span>';
            }
        }
    }
}

// Rewrite tinyMCE settings
(function (getSettings) {
    tinyMceWysiwygSetup.prototype.getSettings = function (mode) {
        var oSettings = getSettings.call(this, mode);

        // Brute-force rewrite of setup function
        oSettings.setup = function (ed){
                    // Internal event is handled and fired to global scope
                    ed.onKeyUp.add(function(ed, l) {
                        varienGlobalEvents.fireEvent('tinymceKeyUp', l);
                    });
                    ed.onChange.add(function(ed, l) {
                        varienGlobalEvents.fireEvent('tinymceChange', l);
                    });
        };

        return oSettings;
    };
}(tinyMceWysiwygSetup.prototype.getSettings));

jQuery(document).ready(function($) {
    var el   = $('#reply');
    var f    = $('#is_internal');
    var note = $('#reply_note');

    var updateSaveBtn = function () {
        if ($('#reply').val() == '') {
            $('.saveTicketBtn').html('<span>Update</span>');
            $('.saveAndContinueTicketBtn').html('<span>Update And Continue Edit</span>');
        } else {
            $('.saveTicketBtn').html('<span> Send </span>');
            $('.saveAndContinueTicketBtn').html('<span> Send And Continue Edit </span>');
        }
    }

    $('#third_party_email').parent().parent().hide();
    $('#third_party_email').removeClass('required-entry');
    $('#reply_type').change(function() {
        var type = $('#reply_type').val();
        var email = $('#third_party_email').parent().parent();
        var emailInput = $('#third_party_email');
        el.removeClass('internal');
        if (type == 'public') {
            note.html('');
            email.hide();
            emailInput.removeClass('required-entry');
        } else if (type == 'internal') {
            el.addClass('internal');
            note.html('Only helpdesk staff will see this message');
            email.hide();
            emailInput.removeClass('required-entry');
        } else if (type == 'public_third') {
            note.html('Your message will be emailed to the third party.<br> Customer will see it and all third party replies.');
            email.show();
            emailInput.addClass('required-entry');
        } else if (type == 'internal_third') {
            el.addClass('internal');
            note.html('Your message will be emailed to the third party. <br>Customer will NOT see it and all third party replies.');
            email.show();
            emailInput.addClass('required-entry');
        }
    });
    $('#public_reply_btn').click(function() {
        el.removeClass('internal');
        $('#public_reply_btn').addClass('active');
        $('#internal_reply_btn').removeClass('active');
        f.val(0);
        note.html('');
        updateSaveBtn();
    });

    $('#internal_reply_btn').click(function() {
        el.addClass('internal');
        $('#public_reply_btn').removeClass('active');
        $('#internal_reply_btn').addClass('active');
        f.val(1);
        note.html('Only helpdesk staff will see this message');
        updateSaveBtn();
    });

    $('#reply').keyup(function() {
        updateSaveBtn();
    });
    var searchResults;
    var fillOrders = function() {
        $('#view_customer_link').hide();
        $('#view_order_link').hide();
        var customer_id = $('#customer_id').val();
        $('#order_id').empty();
        if (customer_id !== 0) {
            $.each(searchResults, function (index, value) {
                if (value['id'] == customer_id) {
                    $.each(value['orders'], function(index, value) {
                        id = value['id'];
                        text = value['name'];
                        $('#order_id').append(
                            $('<option></option>').val(id).html(text)
                        );
                    });
                    if ($('#ticket_id').length == 0) {
                        $('#customer_email').val(value['email']);
                    }
                }
            });

        }
        $('#order_id').show();

    }

    $('#find-customer-btn').click(function() {
        $('#loading-mask').show();
        var url = $('#find-customer-btn').attr('data-url') + '?q=' +$('#customer_query').val();
        $.getJSON(url, function(data){
            $('#customer_id').empty();
            searchResults = data;
            $.each(data, function(index, text) {
                $('#customer_id').append(
                    $('<option></option>').val(text['id']).html(text['name'] )
                );
                $('#customer_id').show();
            });
            fillOrders();
            $('#loading-mask').hide();
        });
    });
    $('#customer_id').change(fillOrders);
    $('#order_id').change(function() {
        $('#view_order_link').hide();
    });
    $('#template_id').change(function() {
        var id = $('#template_id').val();
        if (id != 0) {
            template = $('#htmltemplate-' + id).text();
            var val = $('#reply').val();
            if (val != '') {
                val = val + '\n';
            }
            $('#reply').val(val + template);
            $('#template_id').val(0);
            updateSaveBtn();
        }
    });

    // FOLLOW UP
    var period_date = $('#fp_execute_at').parent().parent();
    var period_value = $('#fp_period_value').parent().parent();
    var periodInit = function() {
      var unit = $('#fp_period_unit').val();
      if (unit == 'custom') {
        period_value.hide();
        period_date.show();
      } else {
        period_value.show();
        period_date.hide();
      }
    }
    periodInit();
    $('#fp_period_unit').bind('change', periodInit);

    var remind_email = $('#fp_remind_email').parent().parent();
    var remindInit = function() {
          var state = $('#fp_is_remind').is(':checked');
          if (state == 1) {
            remind_email.show();
          } else {
            remind_email.hide();
          }
    };
    remindInit();
    $('#fp_is_remind').bind('change', remindInit);


});