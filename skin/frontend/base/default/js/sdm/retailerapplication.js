/**
 * Separation Degrees One
 *
 * Manages the retailer application
 *
 * @category  SDM
 * @package   SDM_RetailerApplication
 * @author    Separation Degrees One <magento@separationdegrees.com>
 * @copyright Copyright (c) 2015 Separation Degrees One (http://www.separationdegrees.com)
 */

var defaultRegionValues = {};
SDMRegionUpdater = {};
(function($) {
    $.noConflict();
    $(document).ready(function() {

        var validationClasses = [
            'required-entry',
            'validate-select'
        ];

        // Check if we can modify the form
        var application = $('#retailer_application');
        var status = application.attr('data-application-status');
        var readOnlyApplication = status !== 'pend' && status !== 'decl';
        if (readOnlyApplication) {
            application.find('input, select, textarea')
                .attr('disabled', 'disabled')
                .css('border', '1px solid #aaa')
                .css('background', '#f9f9f9')
                .css('cursor', 'not-allowed')
                .on('click keydown keyup focus', function(){$(this).blur();return false});
        }

        /**
         * No vals
         */
        $('.noval').each(function(){
            var novalDiv = $(this);
            var theInput = novalDiv.prev('input');
            var theBox = novalDiv.find('input');

            if (theInput.val().toLowerCase() == 'n/a') {
                theInput.attr('disabled', 'disabled');
                theBox.attr('checked', 'checked');
            }

            theBox.on('change', function() {
                if (theBox.is(':checked')) {
                    theInput.val('N/A').attr('disabled', 'disabled');
                } else {
                    theInput.val('').removeAttr('disabled');
                }
            });

        });

        /**
         * Save buttons
         */
        if (readOnlyApplication){
            $('.submit-application, .save-progress').remove();
        } else {
            $('.submit-application').on('click', function(event){
                $('#retailer_application').find('input, select').each(function(){
                    var field = $(this);
                    $.each(validationClasses, function(k, validationClass){
                        if (field.is(validationClass + "-disabled")) {
                            field
                                .removeClass(validationClass + "-disabled")
                                .addClass(validationClass);
                        }
                    })
                });
                $('.validation-advice').remove();
                if (dataForm.validator.validate()){
                    if (confirm("Are you sure you wish to submit your application for review? If you just want to save your progress, use the \"Save Progress\" button.")) {
                        $('#application_submit_review').val('1');
                        $('#retailer_application').submit();
                    }
                }
                return false;
            });
            $('.save-progress').on('click', function(){
                $('#retailer_application').find('input, select').each(function(){
                    var field = $(this);
                    $.each(validationClasses, function(k, validationClass){
                        if (field.is(validationClass)) {
                            field
                                .addClass(validationClass + "-disabled")
                                .removeClass(validationClass);
                        }
                    })
                });
                $('#application_submit_review').val('');
                $('#retailer_application').submit();
            });
        }

        /**
         * File considerations
         */
        var updateFileFax = function(file, fax) {
            if (fax.is(':checked')) {
                file.attr('disabled','disabled')
                    .removeClass('required-entry required-entry-disabled');
            } else {
                file.removeAttr('disabled');
                if(!file.is('.has-file')){
                    file.addClass('required-entry');
                }
            }
        }
        $('.field.file').each(function(){
            var thisGroup = $(this);
            var file = thisGroup.find('.file-field');
            var fax  = thisGroup.find('.fax-field');

            // Set events
            fax.on('change', function(){
                updateFileFax(file, fax);
            });
            updateFileFax(file, fax);
        });

        /**
         * Region Updater
         */
        SDMRegionUpdater = function() {

            this.countryEl = null;
            this.regionEl = null;
            this.regionSelectEl = null;
            this.regions = {};
            this.initialLoad = true;

            this.init = function(countryEl, regionEl, regionSelectEl, regions)
            {
                this.countryEl = $('#' + countryEl);
                this.regionEl = $('#' + regionEl);
                this.regionSelectEl = $('#' + regionSelectEl);
                this.regions = regions;

                this.setEvents();
                this.resetRegions();
                this.setRegionDefaults();

                this.initialLoad = false;
                return this;
            }

            this.setEvents = function(){
                var self = this;
                this.countryEl.on('change', function(){
                    self.resetRegions();
                });
                return this;
            }

            this.resetRegions = function(){
                if (typeof this.regions[this.countryEl.val()] === 'undefined') {
                    this.showText();
                } else {
                    this.showDropdown(this.regions[this.countryEl.val()]);
                }
                return this;
            }

            this.showDropdown = function(regions){
                this.regionEl.parents('.field').find('label').addClass('required');
                this.regionEl.hide().attr('disabled','disabled');
                this.regionSelectEl.removeAttr('disabled').parent().show();
                if (!this.initialLoad){
                    this.regionEl.val('');
                    this.regionSelectEl.val('');
                }
                var html = "<option>Please select region, state or province</option>";
                $.each(regions, function(k ,v){
                    html += "<option value='"+k+"'>"+v['name']+"</option>";
                });
                this.regionSelectEl.html(html);
                return this;
            }

            this.showText = function(){
                this.regionEl.parents('.field').find('label').removeClass('required');
                this.regionEl.show().removeAttr('disabled');
                this.regionSelectEl.attr('disabled','disabled').parent().hide();
                if (!this.initialLoad){
                    this.regionEl.val('');
                    this.regionSelectEl.val('');
                }
                return this;
            }

            this.setRegionDefaults = function(){
                $.each(defaultRegionValues, function(k,v){
                    if (v != 0) {
                        $(k).val(v);
                    }
                });
            }
        }
    });
})(jQuery);

