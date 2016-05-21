/**
 * @extends storeLocator.StaticDataFeed
 * @constructor
 */
 var SDMStoreLocator = function(mapId, standardResultsId, onlineResultsId)
 {
    this.init = function(mapId, standardResultsId, onlineResultsId) {
        this.activeAjax = false;
        this.onlineStores = [];
        this.standardStores = [];
        this.onlineResultsEl = jQuery('#' + onlineResultsId).find('ul');
        this.standardResultsEl = jQuery('#' + standardResultsId).find('ul');
        this.onlineNoResults = this.onlineResultsEl.parent().find('.no-results');
        this.standardNoResults = this.standardResultsEl.parent().find('.no-results');
        this.standardHeading = this.standardResultsEl.parent().find('.standard-heading');
        this.listingTemplate = this.standardResultsEl.parent().find('.item-template').html();
        this.map = new google.maps.Map(document.getElementById(mapId), {
            center: new google.maps.LatLng(GMAPS.defaultLat, GMAPS.defaultLng),
            zoom: GMAPS.defaultZoom,
            mapTypeId: google.maps.MapTypeId.ROADMAP
        });
        this.initTabs();
        this.newAjaxRequest('online');
        this.initSearch();
    };

    /**
     * Parse store data from JSON
     * 
     * @param  {string} jsonString
     * @return this
     */
    this.parseStoreJSON = function(jsonString)
    {
        if (this.currentListingType === 'online') {
            var rawStoreData = jsonString;

            this.onlineNoResults.hide();

            jQuery.each(rawStoreData, jQuery.proxy(function(k, store){
                this.onlineStores.push(new SDMStoreLocatorStore(store, this));
            }, this));

            if (!this.onlineStores.length) {
                this.onlineNoResults.show();
            }

            this.renderOnlineCountryHeadings();
        } else {    
            this.deleteAllStores();

            var rawStoreData = jsonString;

            this.standardNoResults.hide();
            this.standardHeading.show();
            jQuery.each(rawStoreData, jQuery.proxy(function(k, store){
                this.standardStores.push(new SDMStoreLocatorStore(store, this));
            }, this));

            if (!this.standardStores.length) {
                this.standardNoResults.show();
            }
        }

        return this;
    };

    this.startAjaxLoading = function()
    {
        this.activeAjax = true;
        if (this.currentListingType === 'standard') {
            jQuery('#tab-content-stores').addClass('is-loading');
        } else {
            jQuery('#tab-content-online').addClass('is-loading');
        }
    }

    this.endAjaxLoading = function()
    {
        this.activeAjax = false;
        if (this.currentListingType === 'standard') {
            jQuery('#tab-content-stores').removeClass('is-loading');
        } else {
            jQuery('#tab-content-online').removeClass('is-loading');
        }
    }

    this.isAjaxLoading = function()
    {
        return this.activeAjax;
    }

    /**
     * Create a new ajax request to grab stores
     * 
     * @return this
     */
    this.newAjaxRequest = function(listingType, searchType)
    {
        if (this.isAjaxLoading()){
            alert("Please wait for the current request to complete.");
            return;
        }
        this.currentSearchType  = searchType  || null;
        this.currentListingType = listingType || null;
        this.startAjaxLoading();

        if (this.currentSearchType === 'location') {
            /**
             * Do the more complex location search
             * by first querying Google geocode API
             */
            var searchAddress = this.getSearchAddress();
            if (searchAddress && searchAddress.length && searchAddress !== GMAPS.defaultCountry){
                // Get lat lon from search address, then request search ajax
                var url = "https://maps.googleapis.com/maps/api/geocode/json?address="+searchAddress+"&key=" + GMAPS.apiKey; 
                this.searchLat   = 'no';
                this.searchLng   = 'no';
                this.searchState = 'no';
                jQuery.get(url)
                    .done(jQuery.proxy(function(result){
                        if (result.status !== 'OK'){
                            alert(result.error_message);
                            this.endAjaxLoading();
                        }else {
                            this.searchState = this._getSearchState(result['results']['0']['address_components']);
                            this.searchLat = result['results']['0']['geometry']['location']['lat'];
                            this.searchLng = result['results']['0']['geometry']['location']['lng'];
                            this._newAjaxRequest();
                        }
                    }, this));
            } else {
                // Request search ajax directly
                this._newAjaxRequest();
            }
        } else {
            /**
             * Do the simple search
             */
            this._newAjaxRequest();
        }

        return this;
    }

    this._newAjaxRequest = function()
    {
        jQuery.ajax({
            url     : 'gmapstrlocator/index/ajax',
            method  : 'POST',
            data    : this.getRequestParams()
        })
        .done(jQuery.proxy(function(jsonString) {
            this.deleteAllStores();
            this.parseStoreJSON(jsonString);
            this.recenterMap();
        }, this))
        .always(jQuery.proxy(function(){
            this.endAjaxLoading();
        }, this));
    }

    this._getSearchState = function(components)
    {
        var country = '';
        jQuery.each(components, function(k,v){
            if (v['types'][0] === 'country') {
                country = v.short_name;
            }
        });

        if (country !== 'US'){
            return 'no';
        }

        var state = '';
        jQuery.each(components, function(k,v){
            if (v['types'][0] === 'administrative_area_level_1') {
                state = v.short_name;
            }
        });

        return state.length ? state : 'no';
    }

    this.getSearchAddress = function()
    {
        var searchAddress = jQuery('#search-address').val();
        if (searchAddress.length){ 
            searchAddress = [
                searchAddress,
                jQuery('#search-location-country').val()
            ];
            return searchAddress
                .filter(function(n){ return n != undefined && n.length })
                .join(', ')
                .split(' ')
                .join('+');
        }
        return '';
    }

    this.getRequestParams = function()
    {
        if (this.currentListingType === 'online'){
            return {
                listing_type      : this.currentListingType
            };
        } else if (this.currentSearchType === 'name') {
            this.currentRadius  = '';
            this.searchLat      = 'no';
            this.searchLng      = 'no';
            this.searchState    = '';
            this.currentName    = jQuery('#search-name').val();
            return {
                listing_type      : this.currentListingType,
                search_country    : jQuery('#search-name-country').val(),
                search_name       : this.currentName,
                search_type       : 'name'
            };
        } else {
            this.currentRadius  = jQuery('#search-radius').val();
            this.currentName    = '';
            return {
                listing_type      : this.currentListingType,
                search_lat        : this.searchLat,
                search_lng        : this.searchLng,
                search_state      : this.searchState,
                search_radius     : this.currentRadius,
                search_type       : 'location'
            };
        }
    }

    this.renderOnlineCountryHeadings = function()
    {
        var currentCountry = false;
        this.onlineResultsEl.find('li').each(function(){
            var thisEl = jQuery(this);
            var thisCountry = thisEl.find('.address').attr('data-country');
            if (thisCountry !== currentCountry){
                currentCountry = thisCountry;
                thisEl.before([
                    "<li class='country-heading'>",
                        "<h3>",
                            currentCountry,
                            " Retailers",
                        "</h3>",
                    "</li>"
                ].join(''));
            }
        });
    }

    this.initTabs = function()
    {
        this.tabs = jQuery('#store-locator-tabs > li');
        this.tabContents = jQuery('#store-locator-tab-contents > li');
        this.searchBox = jQuery('#store-locator-search');
        this.activeTab = 'stores';

        var that = this;
        this.tabs.find('a').click(function(){
            // Show correct tab
            that.tabs.removeClass('active');
            that.tabContents.removeClass('active');
            var tabId = jQuery(this).parent().addClass('active').attr('data-tab-id');
            jQuery('#' + tabId).addClass('active');

            // Show/hide search box
            that.activeTab = tabId.replace('tab-content-','');
            if (that.activeTab == 'stores') {
                that.searchBox.show();
            } else {
                that.searchBox.hide();
            }

            // Redraw map
            that.recenterMap();

            return false;
        });

        return this;
    }

    this.initSearch = function()
    {
        this.searchByNameBtn = jQuery('#search-by-name');
        this.searchByNameBtn.click(jQuery.proxy(function(){
            this.newAjaxRequest('standard', 'name');
        }, this));

        this.searchByLocationBtn = jQuery('#search-by-location');
        this.searchByLocationBtn.click(jQuery.proxy(function(){
            this.newAjaxRequest('standard', 'location');
        }, this));
    }

    this.deleteAllStores = function()
    {
        jQuery.each(this.standardStores, function(k, store) {
            store.removeMarker();
        });
        this.standardResultsEl.html('');
        this.standardStores = [];
        return this;
    }

    this.recenterMap = function()
    {
        // Redraw map
        google.maps.event.trigger(this.map,'resize');

        //  Make an array of the LatLng's of the markers you want to show
        var bounds = new google.maps.LatLngBounds();
        var haveBounds = false;
        jQuery.each(this.standardStores, jQuery.proxy(function(k, store){
            // Only add results within our radius
            if (store.data.distance <= this.currentRadius) {
                haveBounds = true;
                bounds.extend(store.getPosition());
            }
        }, this));

        if (haveBounds) {
            //  Fit these bounds to the map
            this.map.fitBounds(bounds);
        } else {
            // Extend map to fit default lat lng
            bounds.extend(new google.maps.LatLng(GMAPS.defaultLat, GMAPS.defaultLng));
            this.map.fitBounds(bounds);
            this.map.setZoom(GMAPS.defaultZoom);
        }

        if (this.map.getZoom() > 18) {
            this.map.setZoom(18);
        }

        return this;
    }

    this.closeAllInfoWindows = function()
    {
        jQuery.each(this.standardStores, jQuery.proxy(function(k, store){
            if (store.infoWindow) {
                store.infoWindow.close();
            }
        }, this));
        return this;
    }

    // Le init command
    this.init(mapId, standardResultsId, onlineResultsId);
}

var SDMStoreLocatorStore = function(data, locator)
{
    /**
     * Initialize store
     */
    this.init = function(data, locator)
    {
        this.locator = locator;
        this.map = this.locator.map;
        this.data = data;
        this.template = this.locator.listingTemplate;
        this.onlineResultsEl = this.locator.onlineResultsEl;
        this.standardResultsEl = this.locator.standardResultsEl;
        this.listingEl = null;
        this.position = new google.maps.LatLng(this.data.lat, this.data.lng);
        this.marker = new google.maps.Marker({
            position : this.position,
            map : this.map,
            title : this.data.name
        });
        this.listingType = this.locator.currentListingType;
        this.renderMarker(this.map);
        return this;
    }

    /**
     * Removes marker from map and sidebar
     */
    this.removeMarker = function()
    {
        this.marker.setMap(null);
        return this;
    }

    /**
     * Adds marker to map and sidebar
     */
    this.renderMarker = function(map)
    {
        // Remove first before redrawing
        this.removeMarker();
        if (this.listingType == 'standard') {
            // Check if we're within the search radius (or if we're a "agent serving" result)
            if (this.data.distance <= parseInt(this.locator.currentRadius)) {
                // Assign to this map
                this.marker.setMap(typeof map !== 'undefined' ? map : this.map);
            } else if (this.locator.currentSearchType == 'name' && this.data.lat.length && this.data.lng.length) {
                // Assign to this map
                this.marker.setMap(typeof map !== 'undefined' ? map : this.map);
            }
        }

        // Render side panel
        if (this.listingType == 'standard') {
            // Get panel HTML
            if (this.listingEl === null) {
                this.listingEl = this.standardResultsEl.append(
                    this.getPanelHtml()
                );
            }

            // Create info window
            this.infoWindow = new google.maps.InfoWindow({
                content: this.getInfoWindowHtml()
            });

            // Add click event to open info window from clicking marker
            google.maps.event.addListener(this.marker, 'click', jQuery.proxy(function() {
                this.locator.closeAllInfoWindows();
                this.infoWindow.open(this.map, this.marker);
            }, this));

            // Add click event to open info window from clicking name
            this.listingEl.find('.name').click(jQuery.proxy(function() {
                this.locator.closeAllInfoWindows();
                this.infoWindow.open(this.map, this.marker);
                jQuery("html, body").animate({ scrollTop: 0 }, 500);
                return false;
            }, this));
        } else {
            if (this.listingEl === null) {
                this.listingEl = this.onlineResultsEl.append(
                    this.getPanelHtml()
                );
            }
        }

        return this;
    }

    this.getPanelHtml = function()
    {
        var html = jQuery("<li>" + this.template + "</li>"),
            typeHtml = '';

        if (/catalog/.test(this.data.type)) {
            if (/online/.test(this.data.type)) {
                typeHtml = '<span class="storeType">Online and Catalog only</span>';
            } else {
                typeHtml = '<span class="storeType">Catalog only</span>';
            }
        }

        // Set Name
        if (this.locator.currentListingType === 'online') {
            html.find('.name').replaceWith("<span>"+this.data.name+"</span>" + typeHtml);
        } else {
            html.find('.name').html(this.data.name + typeHtml);
        }

        // Set Image
        var storeImage = "../media/" + this.data.image;
        var noImage = "../skin/frontend/base/default/sdm/gmapstrlocator/images/nostoreimage.jpg"
        if (this.data.image) {
            html.find('.image').attr('src', storeImage);
        } else {
            html.find('.image').attr('src', noImage);
        }

        // Set Phone
        this._updateTextOrRemove(
            html.find('.phone span'),
            this.data.phone,
            html.find('.phone')
        );

        // Set Fax
        this._updateTextOrRemove(
            html.find('.fax span'),
            this.data.fax,
            html.find('.fax')
        );

        // Set Address
        this._updateTextOrRemove(
            html.find('.address'),
            this.data.pretty_address.join('<br>'),
            html.find('.address'),
            'html'
        );
        
        // Set website URL
        this._updateLinkOrRemove(
            html.find('.website a'),
            this.data.website,
            html.find('.website')
        );

        // Set email URL
        this._updateLinkOrRemove(
            html.find('.email a'),
            this.data.email,
            html.find('.email'),
            'mailto:'
        );

        // Set directions URL
        this._updateLinkOrRemove(
            html.find('.directions a'),
            "https://maps.google.com?daddr="+this.data.pretty_address.join('+').split(' ').join('+'),
            html.find('.directions')
        );

        // Set representative service
        if (this.data.rep_serving && this.data.rep_serving.length) {
            this._updateTextOrRemove(
                html.find('.rep-serving span'),
                this.data.rep_serving.split('|').filter(Boolean).join(', '),
                html.find('.rep-serving'),
                'html'
            );
        } else {
            html.find('.rep-serving').remove();
        }

        // Set representative service
        if (this.data.distance.length) {
            this._updateTextOrRemove(
                html.find('.distance'),
                this.data.distance + " miles",
                html.find('.distance'),
                'html'
            );
        } else {
            html.find('.distance').remove();
        }

        // Set brands
        var brands = html.find('.brands span');
        if (this.data.product_lines && this.data.product_lines.length) {
            jQuery.each(this.data.product_lines.split('|'), function(k, brand){
                if (brand.length) {
                    brands = brands.filter(':not(.'+brand.toLowerCase()+')');
                }
            });
        }
        brands.remove();

        // Set design center
        if (this.data.design_center == 0) {
            html.find(".design-center").remove();
        }

        // Set country to address
        html.find('.address').attr('data-country', this.data.country);

        return html;
    }

    this.getInfoWindowHtml = function()
    {
        var html = "<p class='name'><strong>" + this.data.name + "</strong></p>";

        html += "<p class='address'>" + this.data.pretty_address.join('<br>') + "</p>";

        if (this.data.phone && this.data.phone.length) {
            html += "<p class='phone'><strong>Phone:</strong> " + this.data.phone + "</p>";
        }

        if (this.data.fax && this.data.fax.length) {
            html += "<p class='fax'><strong>Fax:</strong> " + this.data.fax + "</p>";
        }

        if (this.data.email && this.data.email.length) {
            html += "<p class='fax'><strong>Email:</strong> " + this.data.email + "</p>";
        }

        if (this.data.pretty_address && this.data.pretty_address.length) {
            html += "<p class='direction' style='text-decoration: underline;'><a href='https://maps.google.com?daddr=" + this.data.pretty_address.join('+').split(' ').join('+') + "'>Get Directions</a></p>";
        }

        return '<div class="store-locator-info-window">'+html+'</div>';
    }

    /**
     * Quick function to check if we have a value. If so, we add it to
     * the element. If not, we remove the element.
     */
    this._updateTextOrRemove = function(el, data, removeEl, updateAs)
    {
        if (typeof data === 'undefined' || data === null || data === false || data.length === 0) {
            removeEl.remove();
        } else {
            if (updateAs === 'html') {
                el.html(data);
            } else {
                el.text(data);
            }
        }
    }

    this._updateLinkOrRemove = function(el, data, removeEl, urlPrefix)
    {
        if (typeof data === 'undefined' || data === null || data === false || data.length === 0) {
            removeEl.remove();
        } else {
            el.attr('href', (urlPrefix || "") + data);
        }
    }

    this.getPosition = function()
    {
        return this.marker.getPosition();
    }

    // Le init command
    this.init(data, locator);
}
