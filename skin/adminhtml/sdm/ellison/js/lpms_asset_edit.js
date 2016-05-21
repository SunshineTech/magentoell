/**
 * Add the ability for Magento's form validator to insert Asset validation
 * messages in the correct place
 */
Object.extend(Validation, {
    insertAdvice : function(elm, advice){
        var container = $(elm).up('.field-row');
        var isAssetElement = jQuery(elm).parents("#page_assets_fieldset").length;
        var isInsideClosedVisibiltyOptions = jQuery(elm)
            .parents(".asset-image-visibility-options.hide");
        if (isAssetElement){
            elm.up('div.element-input').insert({bottom: advice});
            if (isInsideClosedVisibiltyOptions.length){
                jQuery(isInsideClosedVisibiltyOptions).prev().trigger('click');
            }
        } else if(container){
            Element.insert(container, {after: advice});
        } else if (elm.up('td.value')) {
            elm.up('td.value').insert({bottom: advice});
        } else if (elm.advaiceContainer && $(elm.advaiceContainer)) {
            $(elm.advaiceContainer).update(advice);
        } else {
            switch (elm.type.toLowerCase()) {
                case 'checkbox':
                case 'radio':
                    var p = elm.parentNode;
                    if(p) {
                        Element.insert(p, {'bottom': advice});
                    } else {
                        Element.insert(elm, {'after': advice});
                    }
                    break;
                default:
                    Element.insert(elm, {'after': advice});
            }
        }
    }
});

/**
 * Rewrite the JS for "Save Page" and "Save and Continue Edit" to
 * add ASSETS.updateAssetDataField();
 */
jQuery(document).ready(function(){
    editForm.submit = function (url){
        ASSETS.updateAssetDataField();
        if (typeof varienGlobalEvents != undefined) {
            varienGlobalEvents.fireEvent('formSubmit', this.formId);
        }
        this.errorSections = $H({});
        this.canShowError = true;
        this.submitUrl = url;
        if(this.validator && this.validator.validate()){
            if(this.validationUrl){
                this._validate();
            }
            else{
                this._submit();
            }
            return true;
        }
        return false;
    }
    function saveAndContinueEdit(urlTemplate) {
        ASSETS.updateAssetDataField();
        var tabsIdValue = page_tabsJsTabs.activeTab.id;
        var tabsBlockPrefix = 'page_tabs_';
        if (tabsIdValue.startsWith(tabsBlockPrefix)) {
            tabsIdValue = tabsIdValue.substr(tabsBlockPrefix.length)
        }
        var template = new Template(urlTemplate, /(^|.|\r|\n)({{(\w+)}})/);
        var url = template.evaluate({tab_id:tabsIdValue});
        editForm.submit(url);
    }
});

/**
 * All of our asset editing logic
 */
var ASSETS;
jQuery.noConflict();
jQuery(function(){var $ = jQuery; $(document).ready(function(){

    /**
     * Asset controller
     */
    var asset_controller = function(){
        // Our array of assets
        this.ASSETS = [];

        // Important objects
        this.assetDataField = null;
        this.formSet = null;
        this.formList = null;
        this.newAssetButton = null;
        this.stores = [];
        this.types = [];
        this.weekDays = [];
        this.imageFormats = [];
        this.assetFieldNames = [];
        this.assetImageFieldNames = [];

        // Data
        this.newId = 1;

        this.init = function(){
            // Target main elements
            this.formSet = jQuery("#page_assets_fieldset");
            this.formList = this.formSet.find('table');
            this.assetDataField = jQuery("#page_lpms_asset_data");

            // Add "new asset" button
            this.formList.append([
                "<tr><td colspan='2'>",
                    "<button id='new-asset' type='button' title='Add Visual Asset' class='scalable add'>",
                        "<span><span><span>Add New Visual Asset</span></span></span>",
                    "</button>",
                "</td></tr>"
            ].join(''));
            this.newAssetButton = jQuery("#new-asset").click(function(){
                ASSETS.createNewAsset();
            });
            this.newAssetInsertPoint = this.newAssetButton.parents('tr').first();

            // Initialize data
            this.initStores();
            this.initTypes();
            this.initImageFormats();
            this.initWeekDays();
            this.initAssetFieldNames();
            this.initAssetImageFieldNames();

            // Load starting data and create any necessary assets
            this.loadStartingData();

            // Setup wysiwyg refresh when clicking to the asset tab
            this.initWysiwygRefresh();

            // Initial asset images
            this.initAllAssetImages();

            return this;
        }

        this.initStores = function(){
            var self = this;
            $("#page_store_id option").each(function(){
                var thisOption = $(this);
                if (thisOption.val()/1 > 0){
                    self.stores.push({
                        'store_id' : thisOption.val()+"", // Convert to string
                        'store_name' : $.trim(thisOption.text())
                    });
                }
            });
            return this;
        }

        this.initTypes = function(){
            // Hard coded for now
            this.types = [
                {'type_code' : 'freeform',  'type_name' : 'Freeform'},
                {'type_code' : 'search',    'type_name' : 'Search'},
                {'type_code' : 'image',     'type_name' : 'Image'},
                {'type_code' : 'products',  'type_name' : 'Products'}
            ];
            return this;
        }

        this.initImageFormats = function(){
            // Hard coded for now
            this.imageFormats = [
                {'format_code' : '1hor',    'format_name' : '1 Horizontal'},
                {'format_code' : '2hor',    'format_name' : '2 Horizontal'},
                {'format_code' : '3hor',    'format_name' : '3 Horizontal'},
                {'format_code' : 'imgtxt',  'format_name' : '1 Image + Text'},
                {'format_code' : 'slider',  'format_name' : 'Slider'}
            ];
            return this;
        }

        this.initWeekDays = function(){
            // Hard coded for now
            this.weekDays = [
                {'day_code' : 'mo',    'day_name' : 'Mo'},
                {'day_code' : 'tu',    'day_name' : 'Tu'},
                {'day_code' : 'we',    'day_name' : 'We'},
                {'day_code' : 'th',    'day_name' : 'Th'},
                {'day_code' : 'fr',    'day_name' : 'Fr'},
                {'day_code' : 'sa',    'day_name' : 'Sa'},
                {'day_code' : 'su',    'day_name' : 'Su'}
            ];
            return this;
        }

        this.initAssetFieldNames = function(){
            // Hard coded for now
            this.assetFieldNames = [
                'name',
                'type',
                'start_date',
                'end_date',
                'content',
                'image_format',
                'is_active',
                'week_days',
                'store_ids',
                'asset_image'
            ];
            return this;
        }

        this.initAssetImageFieldNames = function(){
            // Hard coded for now
            this.assetImageFieldNames = [
                'visibility_options',
                'start_date',
                'end_date',
                'is_active',
                'week_days',
                'store_ids',
                'image_alt',
                'image_href',
                'file'
            ];
            return this;
        }

        this.initAllAssetImages = function(){
            $.each(ASSETS.ASSETS, $.proxy(function(k, asset){
                asset.initAssetImages();
            }, this));
        }

        this.loadStartingData = function(){
            var startingData = $.parseJSON(this.assetDataField.val());
            $.each(startingData, $.proxy(function(k,data){
                this.createNewAsset(data);
            }, this));
            return this;
        }

        this.createNewAsset = function(data){
            var newAsset = new asset;
            this.ASSETS.push(newAsset.init(data));
            return this;
        }

        this.getNewId = function(){
            return "new-" + this.newId++;
        }

        this.getAssetPosition = function(assetId){
            var position = null;
            $.each(this.ASSETS, $.proxy(function(k,asset){
                if (asset.getAssetId() === assetId){
                    position = k;
                }
            }, this));
            return position;
        }

        this.moveAssetUp = function(assetId){
            var position = this.getAssetPosition(assetId);
            if (position > 0){
                // Swap the assets visually
                this.ASSETS[position-1].getElement()
                    .before(this.ASSETS[position].getElement());

                // Swap the assets within our array
                var temp = this.ASSETS[position];
                this.ASSETS[position] = this.ASSETS[position-1];
                this.ASSETS[position-1] = temp;
            }
            return this;
        }

        this.moveAssetDown = function(assetId){
            var position = this.getAssetPosition(assetId);
            if (position < this.ASSETS.length-1){
                // Swap the assets visually
                this.ASSETS[position+1].getElement()
                    .after(this.ASSETS[position].getElement());

                // Swap the assets within our array
                var temp = this.ASSETS[position];
                this.ASSETS[position] = this.ASSETS[position+1];
                this.ASSETS[position+1] = temp;
            }
            return this;
        }

        this.deleteAsset = function(assetId){
            if (confirm('Are you sure you wish to delete this asset?')){
                var position = this.getAssetPosition(assetId);
                this.ASSETS[position].deleteAsset();
                this.ASSETS.splice(position, 1);
            }
            return this;
        }

        this.serializeAssets = function(){
            var allAssetData = [];
            $.each(this.ASSETS, $.proxy(function(k, asset){
                // Make sure all our "other data" gets saved to the object
                asset.saveOtherDataToObject();

                // Get all data from asset and add to our array
                allAssetData.push(asset.getAllData());
            }, this));
            return JSON.stringify(allAssetData);
        }

        this.updateAssetDataField = function(){
            this.assetDataField.val(this.serializeAssets());
        }

        this.initWysiwygRefresh = function(call){
            if (call){
                $.each(this.ASSETS, function(k, asset){
                    asset.wysiwygInit();
                });
            }else{
                $('#page_tabs_assets_section').click($.proxy(function(){
                    ASSETS.initWysiwygRefresh(true);
                }, this));
                setTimeout(function(){
                    ASSETS.initWysiwygRefresh(true);
                }, 500);
            }
        }

        return this;
     }

    /**
     * Asset abstract
     */
    var asset_abstract = function(){
        // The data for this asset
        this.assetData = {};

        // All the possible asset data fields, along with their default values
        this.assetDataDefaults = {
            'id'            : null,
            'type'          : 'freeform',
            'name'          : '',
            'start_date'    : '',
            'end_date'      : '',
            'content'       : '',
            'image_format'  : '',
            'is_active'     : 1,
            'week_days'     : ['mo','tu','we','th','fr','sa','su'],
            'store_ids'     : [0]
        }

        // All the possible asset data fields, along with their default values
        this.assetImageDataDefaults = {
            'id'            : null,
            'start_date'    : '',
            'end_date'      : '',
            'is_active'     : 1,
            'week_days'     : ['mo','tu','we','th','fr','sa','su'],
            'store_ids'     : [0],
            'image_alt'     : '',
            'image_href'     : ''
        }

        this.getElement = function(){
            return this.rowElement;
        }

        this.getElementId = function(name){
            return "data-" + name + "-" + this.getAssetId();
        }

        this.getAssetId = function(){
            return (this.is == 'asset' ? "asset-" : "asset-image-") + this.getData('id');
        }

        this.renderAssetValue = function(){
            return this.getAssetHtml();
        }

        this.loadData = function(data, isStartingData){
            this.assetData = $.extend({}, this.assetData, data);
            if (typeof isStartingData !== 'undefined' && isStartingData){
                this.startingData = data;
            }
            return this;
        }

        this.setData = function(key, value){
            this.assetData[key] = value;
            return this;
        }

        this.setDefaultData = function(){
            this.assetData = this.is == 'asset' ? this.assetDataDefaults : this.assetImageDataDefaults;
            return this;
        }

        this.getData = function(key){
            return typeof this.assetData[key] === 'undefined' ? null : this.assetData[key];
        }

        this.getAllData = function(){
            return this.assetData;
        }

        this.resetData = function(){
            // We don't want to change the ID....
            this.startingData.id = this.getData('id');

            // Load back in the starting data
            this.loadData(this.startingData, true);

            // Reset asset images
            this.initAssetImages();
        }

        this.deleteAsset = function(){
            // Remove all events
            if (this.is == 'asset'){
                this.unsetMainAssetEvents();
            }else{
                this.unsetMainAssetImageEvents();
            }

            // Remove element
            this.getElement().remove();
        }

        this.changeAssetStoreIds = function(){
            var storeIds = [];
            $.each(ASSETS.stores, $.proxy(function(k,store){
                if ($('#' + this.getElementId('store_'+store.store_id)).is(':checked')){
                    storeIds.push(store.store_id);
                }
            }, this));
            this.setData('store_ids', storeIds);
            return this;
        }

        this.changeAssetWeekDays = function(){
            var weekDays = [];
            $.each(ASSETS.weekDays, $.proxy(function(k,day){
                if ($('#' + this.getElementId('week_day_'+day.day_code)).is(':checked')){
                    weekDays.push(day.day_code);
                }
            }, this));
            this.setData('week_days', weekDays);
            return this;
        }
    }

    /**
     * Define our asset object
     */
    var asset = function(){

        this.isInitialDraw = true;
        this.rowElement = null;
        this.rowElementLabel = null;
        this.rowElementValue = null;
        this.ASSETIMAGES = [];

        /**
         * Initialization function
         */
        this.init = function(startingData){
            // Add methods from asset_abstract and asset_renderer
            asset_abstract.apply(this, arguments);
            asset_renderer.apply(this, arguments);
            this.startingData = this.assetDataDefaults;

            // This is an asset
            this.is = 'asset';

            // Set default data
            this.setDefaultData();

            // Load starting data if provided
            if (typeof startingData !== 'undefined'){
                this.loadData(startingData, true);
            }

            // Give it a new ID if necessary
            if (this.getData('id') === null){
                this.setData('id', ASSETS.getNewId());
            }

            // Draw it
            this.draw();

            return this;
        }

        /**
         * Add new asset image obejct to array
         */
        this.addNewAssetImage = function(startingData){
            var newAssetImage = new asset_image;
            newAssetImage.init(this.getAssetId(), startingData);
            this.ASSETIMAGES
                .push(newAssetImage)
        }

        this.initAssetImages = function(){
            // Reset asset images first
            this.deleteAssetImages();

            // Now build them
            var allAssetImageData = this.getData('asset_images');
            if (!allAssetImageData){
                return this;
            }
            $.each(allAssetImageData, $.proxy(function(k, assetImageData){
                this.addNewAssetImage(assetImageData);
            }, this));
            this.setData('asset_images', null);
            return this;
        }

        /**
         * Add or update this asset's HTML
         */
        this.draw = function(){
            // Setup our asset's structure if this the first time it's drawn
            if (this.isInitialDraw){
                ASSETS.newAssetInsertPoint.before([
                    "<tr id='"+this.getAssetId()+"' class='asset-row'>",
                        "<td class='label'>",
                            "<div class='wrap'></div>",
                        "</td>",
                        "<td class='value'>",
                            "<div class='wrap'></div>",
                        "</td>",
                    "</tr>"
                ].join(''));

                // Create jQuery references
                this.rowElement = jQuery(
                    "#" + this.getAssetId()
                );
                this.rowElementLabel = this.rowElement.find('.label .wrap');
                this.rowElementValue = this.rowElement.find('.value .wrap');

                // Apply element label HTML
                this.rowElementLabel.html(this.renderAssetLabel());
            }
            // Apply element value HTML
            this.rowElementValue.html(this.renderAssetValue());

            // Run any "after draw" events
            this.runAfterDraw();
        }

        this.renderAssetLabel = function(){
            return [
                "<ul>",
                    "<li><span class='asset-move-up'>Move Up</span></li>",
                    "<li><span class='asset-move-down'>Move Down</span></li>",
                    "<li><span class='asset-reset'>Reset</span></li>",
                    "<li><span class='asset-delete'>Delete</span></li>",
                "</ul>"
            ].join("");
        }

        this.runAfterDraw = function(){
            if (this.isInitialDraw){
                this.setInitialAssetEvents();
                this.isInitialDraw = false;
            }
            var that = this;
            // Timeouts solve everything
            setTimeout(function() {
                // Initialize our wysiwyg
                that.wysiwygInit();

                // Initialize any date fields
                that.dateFieldInit();

                // Set the events which occur if we change our asset
                that.setMainAssetEvents();

                // Draw image assets
                that.drawAssetImages(true);
            }, 500);
        }

        this.drawAssetImages = function(forceCompleteRedraw){
            $.each(this.ASSETIMAGES, function(k, assetImage){
                if (typeof forceCompleteRedraw !== 'undefined' && forceCompleteRedraw){
                    assetImage.isInitialDraw = true;
                }
                assetImage.draw();
            });
            return this;
        }

        this.deleteAssetImages = function(){
            $.each(this.ASSETIMAGES, function(k, assetImage){
                assetImage.deleteAsset();
            });
            this.ASSETIMAGES = [];
            return this;
        }

        this.newAssetImageInsertPoint = function(){
            return this.rowElement.find('.asset-images');
        }

        this.runAfterMove = function(){
            this.wysiwygInit();
        }

        this.setInitialAssetEvents = function(){
            var self = this;
            this.rowElement
                .find(".asset-move-up")
                    .on('click', function(){
                        ASSETS.moveAssetUp(self.getAssetId());
                        self.runAfterMove();
                        return false;
                    })
                    .end()
                .find(".asset-move-down")
                    .on('click', function(){
                        ASSETS.moveAssetDown(self.getAssetId());
                        self.runAfterMove();
                        return false;
                    })
                    .end()
                .find(".asset-reset")
                    .on('click', function(){
                        if (confirm("Reset asset data?")){
                            self.resetData();
                            self.resetWysiwygContent();
                            self.draw();
                        }
                        return false;
                    })
                    .end()
                .find(".asset-delete")
                    .on('click', function(){
                        ASSETS.deleteAsset(self.getAssetId());
                        return false;
                    })
                    .end();
        }

        this.changeAssetType = function(value){
            // Set new data
            this.setData('type', value);

            // Save all object data, then wipe out content and image_format fields
            this.saveOtherDataToObject();
            this.setData('content', '');
            this.setData('image_format', '');

            // Remove images if this is a search or product type
            if (value !== 'image'){
                this.deleteAssetImages();
            }

            // Run a redraw
            this.draw();

            return this;
        }

        this.changeImageFormat = function(value){
            // Set new data
            this.setData('image_format', value);

            // Save all object data, then wipe out content and image_format fields
            this.saveOtherDataToObject();
            this.setData('content', '');

            // Remove images if this is a search or product type
            if (this.getData('type') !== 'image'){
                this.deleteAssetImages();
            }

            // Run a redraw
            this.draw();
        }

        /**
         * Save data to object that doesn't already get saved onChange
         */
        this.saveOtherDataToObject = function(){
            // Now save this data to the object
            if (this.getData('type') == 'freeform') {
                this.saveWysiwygData();
            } else if (this.getElementId('content')) {
                this.setData('content', jQuery('#' + this.getElementId('content')).val());
            }

            // Next, run a save on all the image sub-assets
            this.saveAssetImages();
        }

        this.saveAssetImages = function(){
            var assetImagesData = [];

            $.each(this.ASSETIMAGES, $.proxy(function(k, assetImage){
                // Don't allow blank (empty) values. Undefined values are OK, since
                // those images were already uploaded, thus the file field is missing
                if ($('#' + assetImage.getElementId('file')).val() !== '') {
                    assetImage.saveDataToAssetImage();
                    assetImagesData.push(assetImage.getAllData());
                }
            }, this));
            this.setData('asset_images', assetImagesData);
        }

        this.saveWysiwygData = function() {
            this.setData(
                'content',
                jQuery("#data-content-"+this.getAssetId()).val()
            );
        }

        this.getAssetImagePosition = function(assetImageId){
            var position = null;
            $.each(this.ASSETIMAGES, $.proxy(function(k,assetImage){
                if (assetImage.getAssetId() === assetImageId){
                    position = k;
                }
            }, this));
            return position;
        }

        this.moveAssetImageUp = function(assetImageId){
            var position = this.getAssetImagePosition(assetImageId);
            if (position > 0){
                // Swap the assets visually
                this.ASSETIMAGES[position-1].getElement()
                    .before(this.ASSETIMAGES[position].getElement());

                // Swap the assets within our array
                var temp = this.ASSETIMAGES[position];
                this.ASSETIMAGES[position] = 
                    this.ASSETIMAGES[position-1];
                this.ASSETIMAGES[position-1] = temp;
            }
            return this;
        }

        this.moveAssetImageDown = function(assetImageId){
            var position = this.getAssetImagePosition(assetImageId);
            if (position < this.ASSETIMAGES.length-1){
                // Swap the assets visually
                this.ASSETIMAGES[position+1].getElement()
                    .after(this.ASSETIMAGES[position].getElement());

                // Swap the assets within our array
                var temp = this.ASSETIMAGES[position];
                this.ASSETIMAGES[position] = 
                    this.ASSETIMAGES[position+1];
                this.ASSETIMAGES[position+1] = temp;
            }
            return this;
        }

        this.deleteAssetImage = function(assetImageId){
            if (confirm('Are you sure you wish to delete this image?')){
                var position = this.getAssetImagePosition(assetImageId);
                this.ASSETIMAGES[position].deleteAsset();
                this.ASSETIMAGES.splice(position, 1);
            }
            return this;
        }

        return this;
    };

    /**
     * Mini class to render our assets as HTML and handle event binding
     */
    var asset_renderer = function(){

        this.setMainAssetEvents = function(){
            // First, remove any change events from all elements
            this.unsetMainAssetEvents();

            // Now, add change events to all elements
            for(var i = 0; ASSETS.assetFieldNames.length > i; i++){
                this.attachMainAssetEvents(ASSETS.assetFieldNames[i]);
            }
        }

        this.unsetMainAssetEvents = function(){
            // Standard asset value changes
            for(var i = 0; ASSETS.assetFieldNames.length > i; i++){
                this.getAssetElementByName(ASSETS.assetFieldNames[i])
                    .off('change');
            }

            // Remove the select all stores click event
            this.rowElement.find('.select-all-stores').off('click');
        }

        this.attachMainAssetEvents = function(name){
            var self = this;
            var element = this.getAssetElementByName(name).off('change');
            switch(name){
                case 'asset_image':
                    element.on('click', function(){
                        self.addNewAssetImage([]);
                    });
                break;
                case 'week_days':
                    element.on('change', function(){
                        self.changeAssetWeekDays();
                    });
                break;
                case 'store_ids':
                    element.on('change', function(){
                        self.changeAssetStoreIds();
                    });
                    element.parent().find('.select-all-stores').on('click', function(){
                        $(this).parent().find('input').attr('checked', 'checked');
                        self.changeAssetStoreIds();
                        return false;
                    });
                break;
                case 'type':
                    element.on('change', function(){
                        self.changeAssetType(this.value);
                    });
                break;
                case 'image_format':
                    element.on('change', function(){
                        self.changeImageFormat(this.value);
                    });
                break;
                default:
                    element.on('change', function(){
                        self.setData(name, this.value);
                    });
                break;
            }
        }

        this.getAssetElementByName = function(name){
            switch(name){
                case 'asset_image':
                    return $('#' + this.getElementId('new-asset-image'));
                break;
                case 'is_active':
                    return $('[name="' + this.getElementId(name) + '"]');
                case 'week_days':
                    var elString = [];
                    $.each(ASSETS.weekDays, $.proxy(function(k,day){
                        elString.push("#" + this.getElementId('week_day_'+day.day_code));
                    }, this));
                    return $(elString.join(', '));
                break;
                case 'store_ids':
                    var elString = [];
                    $.each(ASSETS.stores, $.proxy(function(k,store){
                        elString.push("#" + this.getElementId('store_'+store.store_id));
                    }, this));
                    return $(elString.join(', '));
                break;
                default:
                    return $('#' + this.getElementId(name));
                break;
            }
        }

        this.getAssetHtml = function(){
            var html = [];

            // Name and Type Input
            html.push("<div class='element-input standard'>");
            html.push("<label for='"+this.getElementId('type')+"'>Asset Type</label>");
            html.push("<select id='"+this.getElementId('type')+"' class='select'>");
            $.each(ASSETS.types, $.proxy(function(k,type){
                html.push("<option value='"+type.type_code+"'{{data:type:"+type.type_code+"}}>"+type.type_name+"</option>");
            }, this));
            html.push("</select>");
            html.push("<label for='"+this.getElementId('name')+"'>Asset Name</label>");
            html.push("<input id='"+this.getElementId('name')+"' class='input-text' type='text' value='{{data:name}}' />");
            html.push("</div>");

            // Start Date and End Date
            html.push("<div class='element-input standard'>");
            html.push("<label for='"+this.getElementId('start_date')+"'>Start Date</label>");
            html.push("<input id='"+this.getElementId('start_date')+"' class='input-text validate-date validate-date-range date-range-"+this.getElementId('start_date')+"-from' type='text' value='{{data:start_date}}' />");
            html.push("<label for='"+this.getElementId('end_date')+"'>End Date</label>");
            html.push("<input id='"+this.getElementId('end_date')+"' class='input-text validate-date validate-date-range date-range-"+this.getElementId('end_date')+"-to' type='text' value='{{data:end_date}}' />");
            html.push("</div>");

            // Week Days
            html.push("<div class='element-input checkbox-row'>");
            html.push("<div class='checkbox-wrap left'>");
            html.push("<div class='section-label'>Asset Status</div>");

            html.push("<input id='"+this.getElementId('is_active_yes')+"' name='"+this.getElementId('is_active')+"' {{data:is_active:yes}} type='radio' value='1' />");
            html.push("<label for='"+this.getElementId('is_active_yes')+"'>Enabled</label>");

            html.push("<input id='"+this.getElementId('is_active_no')+"' name='"+this.getElementId('is_active')+"' {{data:is_active:no}} type='radio' value='0' />");
            html.push("<label for='"+this.getElementId('is_active_no')+"'>Disabled</label>");

            html.push("</div>");
            html.push("<div class='checkbox-wrap right'>");
            html.push("<div class='section-label'>Show on</div>");
            $.each(ASSETS.weekDays, $.proxy(function(k,day){
                html.push("<input id='"+this.getElementId('week_day_'+day.day_code)+"' {{data:week_days:"+day.day_code+"}} type='checkbox' />");
                html.push("<label for='"+this.getElementId('week_day_'+day.day_code)+"'>"+day.day_name+"</label>");
            }, this));
            html.push("</div>");
            html.push("</div>");

            // Stores
            html.push("<div class='element-input checkbox-row'>");
            html.push("<div class='checkbox-wrap left'>");
            html.push("<div class='section-label'>Display on{{required_label}}</div>");
            $.each(ASSETS.stores, $.proxy(function(k,store){
                html.push("<input id='"+this.getElementId('store_'+store.store_id)+"' {{data:store:"+store.store_id+"}} name='"+this.getElementId('store')+"' type='checkbox' {{require_one_for_store}} />");
                html.push("<label for='"+this.getElementId('store_'+store.store_id)+"'>"+store.store_name+"</label>");
            }, this));
            html.push("<a href='#' class='select-all-stores'>Select All</a>");
            html.push("</div>");
            html.push("</div>");

            /**
             * Custom html for each Asset Type
             */
            switch(this.getData('type')){
                case 'freeform':
                    html.push("<div class='element-input main tall last'>");
                    html.push("<label for='"+this.getElementId('content')+"'>Freeform Content:</label>");
                    html.push("<textarea id='"+this.getElementId('content')+"' class='textarea'>{{data:content}}</textarea>");
                    html.push("</div>");
                break;
                case 'products':
                    html.push("<div class='element-input main last'>");
                    html.push("<label for='"+this.getElementId('content')+"'>Product SKUs (Seperated by Commas)</label>");
                    html.push("<textarea id='"+this.getElementId('content')+"' class='textarea product-skus'>{{data:content}}</textarea>");
                    html.push("</div>");
                break;
                case 'image':
                    html.push("<div class='element-input main tall'>");
                    html.push("<label for='"+this.getElementId('image_format')+"'>Image Display</label>");
                    html.push("<select id='"+this.getElementId('image_format')+"' class='select'>");
                    $.each(ASSETS.imageFormats, $.proxy(function(k,format){
                        html.push("<option value='"+format.format_code+"'{{data:image_format:"+format.format_code+"}}>"+format.format_name+"</option>");
                    }, this));
                    html.push("</select>");
                    html.push("<div class='image-size'>{{data:image_sizes}}</div>");
                    html.push("</div>");

                    html.push("{{image_plus_text}}");

                    html.push("<div class='element-input image-assets last'>");
                    html.push("{{asset_image_html}}");
                    html.push("</div>");
                break;
                case 'search':
                    html.push("<div class='element-input main last'>");
                    html.push("<label for='"+this.getElementId('content')+"'>Search Query String</label>");
                    html.push("<input id='"+this.getElementId('content')+"' class='input-text search-string' type='text' value='{{data:content}}' />");
                    html.push("</div>");
                break;
            }

            // Final clear
            html.push("<div class='clear'></div>");

            // Apply tags and return
            return this.applyTags(html.join(''));
        }

        this.applyTags = function(html){
            // Image plus text
            html = html.replace(
                /{{image_plus_text}}/g,
                this.getData('image_format')+"" !== "imgtxt" ? "" : "<div class='element-input main last'><label for='"+this.getElementId('content')+"'>Image Text/HTML</label><textarea id='"+this.getElementId('content')+"' class='textarea product-skus'>{{data:content}}</textarea></div>"
            );

            // Get filter HTML
            html = html.replace(/{{asset_image_html}}/g, this.getAssetImageHtml());

            // Static required label
            html = html.replace(/{{required_label}}/g , "<span class='required'>*</span>");

            // Add required for store class (to first item only; remove for the rest)
            html = html.replace("{{require_one_for_store", " class='validate-one-required-by-name'")
            html = html.replace(/{{require_one_for_store}}/g , "");

            // Asset type
            $.each(ASSETS.types, $.proxy(function(k,type){
                html = html.replace(
                    new RegExp("{{data:type:"+type.type_code+"}}", "g"),
                    type.type_code == this.getData('type') ? "selected" : ""
                );
            }, this));

            // Asset Name
            html = html.replace(/{{data:name}}/g , this.getData('name'));

            // Asset Start Date
            html = html.replace(/{{data:start_date}}/g , this.getData('start_date'));

            // Asset End Date
            html = html.replace(/{{data:end_date}}/g , this.getData('end_date'));

            // Asset Content
            html = html.replace(/{{data:content}}/g , this.getData('content'));

            // Asset Content
            html = html.replace(/{{data:image_sizes}}/g , this.getImageSizing());

            // Asset Is Active
            html = html.replace(
                /{{data:is_active:yes}}/g,
                this.getData('is_active')+"" === "1" ? "checked" : ""
            );
            html = html.replace(
                /{{data:is_active:no}}/g,
                this.getData('is_active')+"" === "1" ? "" : "checked"
            );
            
            // Asset Week Days
            $.each(ASSETS.weekDays, $.proxy(function(k,day){
                html = html.replace(
                    new RegExp("{{data:week_days:"+day.day_code+"}}", "g"),
                    $.inArray(day.day_code, this.getData('week_days')) !== -1 ? "checked" : ""
                );
            }, this));

            // Asset Image Format
            $.each(ASSETS.imageFormats, $.proxy(function(k,format){
                html = html.replace(
                    new RegExp("{{data:image_format:"+format.format_code+"}}", "g"),
                    format.format_code === this.getData('image_format') ? "selected" : ""
                );
            }, this));

            // Asset Stores
            $.each(ASSETS.stores, $.proxy(function(k,store){
                html = html.replace(
                    new RegExp("{{data:store:"+store.store_id+"}}", "g"),
                    $.inArray(store.store_id+"", this.getData('store_ids')) !== -1 ? "checked" : ""
                );
            }, this));

            return html;
        }

        this.getImageSizing = function(){
            switch(this.getData('image_format')){
                case '1hor':
                default:
                    return 'Recommended Minimum Width: 1000px';
                case '2hor':
                    return 'Recommended Minimum Width: 770px';
                case '3hor':
                    return 'Recommended Minimum Width: 770px';
                case 'imgtxt':
                    return 'Recommended Image Width: 430px';
                case 'slider':
                    return 'Recommended Image Size: 2400x650';
            }
            return '';
        }

        this.getAssetImageHtml = function(){
            var html = [
                "<ul class='asset-images'></ul>",
                "<button id='" + this.getElementId('new-asset-image') + "' type='button' title='Add New Image' class='scalable add'>",
                    "<span><span><span>Add New Image</span></span></span>",
                "</button>"
            ];
            return html.join('');
        }

        this.dateFieldInit = function(){
            Calendar.setup({
                inputField : this.getElementId('start_date'),
                ifFormat : '%Y-%m-%d',
                button : 'date_from_trig',
                align : 'Bl',
                singleClick : true
            });
            Calendar.setup({
                inputField : this.getElementId('end_date'),
                ifFormat : '%Y-%m-%d',
                button : 'date_from_trig',
                align : 'Bl',
                singleClick : true
            });
        }

        this.wysiwygInit = function(){
            // Only init wysiwyg if this is a freeform asset
            if (this.getData('type') !== 'freeform'){
                return this;
            }

            // JQTE only needs to init once, so we're going to skip
            // secondary inits

            if (this.primaryInitComplete) {
                return this;
            }
            this.primaryInitComplete = true;
            jQuery('#data-content-' + this.getAssetId() + '_parent').show();
            jQuery("#data-content-"+this.getAssetId()).jqte();

            return this;
        }

        this.resetWysiwygContent = function(){
            jQuery("data-content-"+this.getAssetId()).jqteVal(
                this.getData('content')
            );
        }

    }

    var asset_image = function(){
        this.isInitialDraw = true;
        this.parentAssetId = "";
        this.parentAssetElement = "";
        this.element = null;
        this.isInitialDraw = true;
        this.optionsVisible = false;

        /**
         * Initialization function
         */
        this.init = function(parentAssetId, startingData){
            // Add methods from asset_abstract and asset_renderer
            asset_abstract.apply(this, arguments);
            asset_image_renderer.apply(this, arguments);
            this.startingData = this.assetImageDataDefaults;

            // This is an asset image
            this.is = 'asset_image';

            // Set the parentAssetId and parentAssetElement
            this.parentAssetId = parentAssetId;
            this.parentAssetElement = jQuery('#' + parentAssetId);

            // Set default data
            this.setDefaultData();

            // Load starting data if provided
            if (typeof startingData !== 'undefined'){
                this.loadData(startingData, true);
            }

            // Give it a new ID if necessary
            if (this.getData('id') === null){
                this.setData('id', ASSETS.getNewId());
            }

            // Draw it
            this.draw();

            return this;
        }

        this.getParent = function(){
            var position = null;
            $.each(ASSETS.ASSETS, $.proxy(function(k,asset){
                if (asset.getAssetId() === this.parentAssetId){
                    position = k;
                }
            }, this));
            return position !== null ? ASSETS.ASSETS[position] : null;
        }

        this.getParentElement = function(){
            return this.parentAssetElement;
        }

        this.draw = function(){
            // Setup our asset's structure if this the first time it's drawn
            if (this.isInitialDraw && jQuery('#' + this.getAssetId()).length == 0){
                this.getParent()
                    .newAssetImageInsertPoint()
                    .append([
                        "<li id='"+this.getAssetId()+"'>",
                            "<div class='label'>",
                                "<div class='wrap'></div>",
                            "</div>",
                            "<div class='value'>",
                                "<div class='wrap'>",
                                "</div>",
                                "<div class='file-wrap'>",
                                    this.getFileHtml(),
                                "</div>",
                            "</div",
                        "<li>"
                    ].join(''));


                // Create jQuery references
                this.rowElement = jQuery("#" + this.getAssetId());
                this.rowElementLabel = this.rowElement.find('.label .wrap');
                this.rowElementValue = this.rowElement.find('.value .wrap');

                // Apply element label HTML
                this.rowElementLabel.html(this.renderAssetImageLabel());
            }
            // Apply element value HTML
            this.rowElementValue.html(this.renderAssetImageValue());

            // Run any "after draw" events
            this.runAfterDraw();
        }

        this.runAfterDraw = function(){
            if (this.isInitialDraw){
                this.setInitialAssetEvents();
                this.isInitialDraw = false;
            }
            // Initialize any date fields
            this.dateFieldInit();

            // Set the events which occur if we change our asset
            this.setMainAssetImageEvents();
        }

        this.renderAssetImageValue = function(){
            return this.getAssetImageHtml();
        }

        this.renderAssetImageLabel = function(){
            return [
                "<ul>",
                    "<li><span class='asset-image-move-up'></span></li>",
                    "<li><span class='asset-image-move-down'></span></li>",
                    "<li><span class='asset-image-delete'></span></li>",
                "</ul>"
            ].join("");
        }

        this.setInitialAssetEvents = function(){
            var self = this;
            this.rowElement
                .find(".asset-image-move-up")
                    .on('click', function(){
                        self.getParent().moveAssetImageUp(self.getAssetId());
                        return false;
                    })
                    .end()
                .find(".asset-image-move-down")
                    .on('click', function(){
                        self.getParent().moveAssetImageDown(self.getAssetId());
                        return false;
                    })
                    .end()
                .find(".asset-image-delete")
                    .on('click', function(){
                        self.getParent().deleteAssetImage(self.getAssetId());
                        return false;
                    })
                    .end();
        }

        this.getFileHtml = function(){
            if (this.getData('image_url') === null || this.getData('image_url') == ''){
                return "<input type='file' name='"+this.getElementId('file')+"' id='"+this.getElementId('file')+"' class='asset-image-file' />";
            }else{
                return "<a href='/media"+this.getData('image_url')+"' target='_blank'><img src='/media"+this.getData('image_url')+"' class='asset-image-thumb' /></a>";
            }
        }

        this.toggleOptionsVisibility = function(){
            this.optionsVisible = !this.optionsVisible;
            var elements = $('#' + this.getElementId('visibility_options') + ', #' + this.getElementId('visibility_options_toggle')).removeClass('show hide');
            if (this.optionsVisible){
                elements.addClass('show');
            }else{
                elements.addClass('hide');
            }
        }

        this.saveDataToAssetImage = function(){
            this.setData('file', '');
            var fileInput = $('#' + this.getElementId('file'));
            if (fileInput.length){
                this.setData('file', fileInput.attr('id'));
            }
        }
    };
    
    var asset_image_renderer = function(){

        this.setMainAssetImageEvents = function(){
            // First, remove any change events from all elements
            this.unsetMainAssetImageEvents();

            // Now, add change events to all elements
            for(var i = 0; ASSETS.assetImageFieldNames.length > i; i++){
                this.attachMainAssetImageEvents(ASSETS.assetImageFieldNames[i]);
            }
        }

        this.unsetMainAssetImageEvents = function(){
            // Standard asset value changes
            for(var i = 0; ASSETS.assetImageFieldNames.length > i; i++){
                this.getAssetImageElementByName(ASSETS.assetImageFieldNames[i])
                    .off('change');
            }

            // Remove the select all stores click event
            this.rowElement.find('.select-all-stores').off('click');
        }

        this.attachMainAssetImageEvents = function(name){
            var self = this;
            var element = this.getAssetImageElementByName(name).off('change');
            switch(name){
                case 'visibility_options':
                    element.on('click', function(){
                        self.toggleOptionsVisibility();
                    });
                break;
                case 'week_days':
                    element.on('change', function(){
                        self.changeAssetWeekDays();
                    });
                break;
                case 'store_ids':
                    element.on('change', function(){
                        self.changeAssetStoreIds();
                    });
                    element.parent().find('.select-all-stores').on('click', function(){
                        $(this).parent().find('input').attr('checked', 'checked');
                        self.changeAssetStoreIds();
                        return false;
                    });
                break;
                default:
                    element.on('change', function(){
                        self.setData(name, this.value);
                    });
                break;
            }
        }

        this.getAssetImageElementByName = function(name){
            switch(name){
                case 'visibility_options':
                    return $('#' + this.getElementId('visibility_options_toggle'));
                case 'is_active':
                    return $('[name="' + this.getElementId(name) + '"]');
                case 'week_days':
                    var elString = [];
                    $.each(ASSETS.weekDays, $.proxy(function(k,day){
                        elString.push("#" + this.getElementId('week_day_'+day.day_code));
                    }, this));
                    return $(elString.join(', '));
                break;
                case 'store_ids':
                    var elString = [];
                    $.each(ASSETS.stores, $.proxy(function(k,store){
                        elString.push("#" + this.getElementId('store_'+store.store_id));
                    }, this));
                    return $(elString.join(', '));
                break;
                default:
                    return $('#' + this.getElementId(name));
                break;
            }
        }

        this.getAssetImageHtml = function(){
            var html = [];

            // Options visibility wrap
            html.push("<div id='"+this.getElementId('visibility_options_toggle')+"' class='asset-image-visibility-toggle {{options_visible}}'><span>Toggle Visibility Options</span></div>");
            html.push("<div id='"+this.getElementId('visibility_options')+"' class='asset-image-visibility-options {{options_visible}}'>");

            // Start Date and End Date
            html.push("<div class='element-input standard'>");
            html.push("<label for='"+this.getElementId('start_date')+"'>Start Date</label>");
            html.push("<input id='"+this.getElementId('start_date')+"' class='input-text validate-date validate-date-range date-range-"+this.getElementId('start_date')+"-from' type='text' value='{{data:start_date}}' />");
            html.push("<label for='"+this.getElementId('end_date')+"'>End Date</label>");
            html.push("<input id='"+this.getElementId('end_date')+"' class='input-text validate-date validate-date-range date-range-"+this.getElementId('end_date')+"-to' type='text' value='{{data:end_date}}' />");
            html.push("</div>");

            // Week Days
            html.push("<div class='element-input checkbox-row'>");
            html.push("<div class='checkbox-wrap left'>");
            html.push("<div class='section-label'>Asset Status</div>");

            html.push("<input id='"+this.getElementId('is_active_yes')+"' name='"+this.getElementId('is_active')+"' {{data:is_active:yes}} type='radio' value='1' />");
            html.push("<label for='"+this.getElementId('is_active_yes')+"'>Enabled</label>");

            html.push("<input id='"+this.getElementId('is_active_no')+"' name='"+this.getElementId('is_active')+"' {{data:is_active:no}} type='radio' value='0' />");
            html.push("<label for='"+this.getElementId('is_active_no')+"'>Disabled</label>");

            html.push("</div>");
            html.push("<div class='checkbox-wrap right'>");
            html.push("<div class='section-label'>Show on</div>");
            $.each(ASSETS.weekDays, $.proxy(function(k,day){
                html.push("<input id='"+this.getElementId('week_day_'+day.day_code)+"' {{data:week_days:"+day.day_code+"}} type='checkbox' />");
                html.push("<label for='"+this.getElementId('week_day_'+day.day_code)+"'>"+day.day_name+"</label>");
            }, this));
            html.push("</div>");
            html.push("</div>");

            // Stores
            html.push("<div class='element-input checkbox-row'>");
            html.push("<div class='checkbox-wrap left'>");
            html.push("<div class='section-label'>Display on{{required_label}}</div>");
            $.each(ASSETS.stores, $.proxy(function(k,store){
                html.push("<input id='"+this.getElementId('store_'+store.store_id)+"' {{data:store:"+store.store_id+"}} name='"+this.getElementId('store')+"' type='checkbox' {{require_one_for_store}} />");
                html.push("<label for='"+this.getElementId('store_'+store.store_id)+"'>"+store.store_name+"</label>");
            }, this));
            html.push("<a href='#' class='select-all-stores'>Select All</a>");
            html.push("</div>");
            html.push("</div>");

            // Options visibility wrap
            html.push("</div>");

            // Image Alt
            html.push("<div class='element-input standard image-alt'>");
            html.push("<label for='"+this.getElementId('image_alt')+"'>Image Alternate Text</label>");
            html.push("<input id='"+this.getElementId('image_alt')+"' class='input-text' type='text' value='{{data:image_alt}}' />");
            html.push("<label for='"+this.getElementId('image_href')+"'>Image Link</label>");
            html.push("<input id='"+this.getElementId('image_href')+"' class='input-text' type='text' value='{{data:image_href}}' />");
            html.push("</div>");

            // Final clear
            html.push("<div class='clear'></div>");

            return this.applyTags(html.join(''));
        }

        this.applyTags = function(html){
            // Static required label
            html = html.replace(/{{required_label}}/g , "<span class='required'>*</span>");

            // Add required for store class (to first item only; remove for the rest)
            html = html.replace("{{require_one_for_store", " class='validate-one-required-by-name'")
            html = html.replace(/{{require_one_for_store}}/g , "");

            // Options visible
            html = html.replace(/{{options_visible}}/g , this.optionsVisible ? 'show' : 'hide');

            // Asset Image Alt
            html = html.replace(/{{data:image_alt}}/g , this.getData('image_alt'));

            // Asset Image Link
            html = html.replace(/{{data:image_href}}/g , this.getData('image_href'));

            // Asset Start Date
            html = html.replace(/{{data:start_date}}/g , this.getData('start_date'));

            // Asset End Date
            html = html.replace(/{{data:end_date}}/g , this.getData('end_date'));

            // Asset Is Active
            html = html.replace(
                /{{data:is_active:yes}}/g,
                this.getData('is_active')+"" === "1" ? "checked" : ""
            );
            html = html.replace(
                /{{data:is_active:no}}/g,
                this.getData('is_active')+"" === "1" ? "" : "checked"
            );
            
            // Asset Week Days
            $.each(ASSETS.weekDays, $.proxy(function(k,day){
                html = html.replace(
                    new RegExp("{{data:week_days:"+day.day_code+"}}", "g"),
                    $.inArray(day.day_code, this.getData('week_days')) !== -1 ? "checked" : ""
                );
            }, this));

            // Asset Stores
            $.each(ASSETS.stores, $.proxy(function(k,store){
                html = html.replace(
                    new RegExp("{{data:store:"+store.store_id+"}}", "g"),
                    $.inArray(store.store_id+"", this.getData('store_ids')) !== -1 ? "checked" : ""
                );
            }, this));

            return html;
        }

        this.dateFieldInit = function(){
            Calendar.setup({
                inputField : this.getElementId('start_date'),
                ifFormat : '%Y-%m-%d',
                button : 'date_from_trig',
                align : 'Bl',
                singleClick : true
            });
            Calendar.setup({
                inputField : this.getElementId('end_date'),
                ifFormat : '%Y-%m-%d',
                button : 'date_from_trig',
                align : 'Bl',
                singleClick : true
            });
        }

    };

    /**
     * Initialize our controller and get this page started!
     */
    ASSETS = new asset_controller;
    ASSETS.init();

})});