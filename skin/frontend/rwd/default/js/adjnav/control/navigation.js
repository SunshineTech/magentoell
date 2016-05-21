
/**
 * Layered Navigation Pro
 *
 * @category:    AdjustWare
 * @package:     AdjustWare_Nav
 * @version      2.6.1
 * @license:     n/a
 * @copyright:   Copyright (c) 2015 AITOC, Inc. (http://www.aitoc.com)
 */
var AdjnavNavigation = Class.create({
    initialize: function() {
        this.blocks = {};
        this.onUpdateLayer = this.updateLayer.bindAsEventListener(this);
        this.ajdnavExpandedLoaded = false;
    },

    addBlock: function(block) {
        this.blocks[block.blockID] = block;
    },

    updateLayer: function(transport) {
        var resp = {} ;
        if (transport && transport.responseText){
            try {
                resp = eval('(' + transport.responseText + ')');
            }
            catch (e) {
                resp = {};
            }
        }

        $$('div.category-title').each(function(el) {
            el.select('h1').each(function(e) {
                e.update(resp.category_name);
            });
        });

        if (resp.products){
            var ajaxUrl = $(nav_ajaxurl).value;

            $('adj-nav-container').update(resp.products.gsub(ajaxUrl, $(nav_url).value));
            adjnavToolbar.init();
            if(typeof ProductMediaManager != 'undefined')
            {
                //magic! eval in update() start after timeout
                triggerScript = function(){
                    jQuery(document).trigger('product-media-loaded', ProductMediaManager);
                };
                triggerScript.defer();
            }

            $('nav_params').replace(resp.params);
            if($('adj-nav-navigation') && $('adj-nav-navigation').childElements() != "")
            {
                $('adj-nav-navigation').replace(resp.layer.gsub(ajaxUrl, $(nav_url).value));
            }
            if($('adj-nav-navigation-top') && $('adj-nav-navigation-top').childElements() != "")
            {
                $('adj-nav-navigation-top').replace(resp.layer_top.gsub(ajaxUrl, $(nav_url).value));
            }

            $(nav_ajaxurl).value = ajaxUrl;
        }

        for (var block in this.blocks) {
            if(this.blocks.hasOwnProperty(block)){
                this.blocks[block].showProducts();
            }
        }

        this.updateToggle();
        this.updateRelLinks();
    },

    addParams: function(k, v, isSingleVal) {
        $(nav_params).value = $(nav_params).value.gsub(/\+/, ' ');
        var params = $(nav_params).value.parseQuery();

        var strVal = params[k];
        if (typeof strVal == 'undefined' || !strVal.length){
            params[k] = v;
        }
        else if('clear' == v ){
            params[k] = 'clear';
        }
        else if (k == 'price' && isSingleVal && v.indexOf('-')!=-1){
            //magento 1.7+ "from-to" prices
            params[k] = v;
        } else {
            if (k == 'price')
                var values = strVal.split(',');
            else
                var values = strVal.split('-');

            if (-1 == values.indexOf(v)){
                if (isSingleVal)
                    values = [v];
                else
                    values.push(v);
            }
            else {
                values = values.without(v);
            }

            params[k] = values.join('-');
        }

        $(nav_params).value = Object.toQueryString(params);
    },

    getAitanswer: function() {
        var aitanswer = '';

        if(typeof getAitUrlParam == 'function' && getAitUrlParam('aitanswer'))
        {
            aitanswer = '&aitanswer=' + getAitUrlParam('aitanswer');
        }

        return aitanswer;
    },

    initOther: function() {
        if(false == this.ajdnavExpandedLoaded ) {
            for (var block in this.blocks) {
                if(this.blocks.hasOwnProperty(block)){
                    this.blocks[block].init(true);
                }
            }
            this.ajdnavExpandedLoaded = true;
        }
    },

    makeRequest: function() {
        for (var block in this.blocks) {
            if(this.blocks.hasOwnProperty(block)){
                this.blocks[block].hideProducts();
            }
        }
        this.prepareParams();

        if (document.getElementById('aitshopassist_category_id'))
        {
            $('aitshopassist').remove();
        }

        var url = $(nav_ajaxurl).value + '?' + $(nav_params).value + '&no_cache=true&home=' + lookhomepage + this.getAitanswer();
        new Ajax.Request(url,
            {method: 'get', onSuccess: this.onUpdateLayer}
        );
    },

    prepareParams: function() {
        $(nav_params).value = $(nav_params).value.gsub(/\+/, ' ');
        var params = $(nav_params).value.parseQuery();

        // Shop by brands compatibility
        if (typeof currentShopByAttribute != "undefined" && typeof currentShopByAttributeValue != "undefined" && !params['shopby_attribute']) {
            params['shopby_attribute'] = currentShopByAttribute;
            params[currentShopByAttribute] = currentShopByAttributeValue;
        }

        if (!params['order']) // Respect Sort By settings!
        {
            var select = null;
            $$('select').each(function(el) {
                if (el.onchange)
                {
                    if (el.onchange.toString().match(/adjnavToolbar\.makeRequest/))
                    {
                        select = el;
                    }
                }
            });

            if (select)
            {
                var selectParams = select.value.parseQuery();

                if (selectParams && selectParams['order'])
                {
                    params['order'] = selectParams['order'];
                }
            }
        }

        $(nav_params).value = Object.toQueryString(params);

        if (canChangeLocationHash)
        {
            isProcessHashChange = false;
            wasUrlHashed        = true;
            location.hash = '!/' + $(nav_params).value;
        }
    },

    updateLinks: function(evt, className, isSingleVal) {
        var link = Event.findElement(evt, 'A'),
            sel = className + '-selected';

        if (link.hasClassName(sel))
            link.removeClassName(sel);
        else
            link.addClassName(sel);

        //only one  price-range can be selected
        if (isSingleVal){
            var items = document.getElementsByClassName(className);
            var i, n = items.length;
            for (i=0; i<n; ++i){
                if (items[i].hasClassName(sel) && items[i].id != link.id)
                    items[i].removeClassName(sel);
            }
        }

        var pos = link.id.indexOf('-');
        this.addParams(link.id.substr(0,pos), link.id.substr(pos+1), isSingleVal);

        this.makeRequest();

        Event.stop(evt);
    },

    updateRelLinks: function() {
        var params = $(nav_params).value.parseQuery();
        if (!params['order']) params['order'] = 'position';
        if (!params['dir']) params['dir'] = 'desc';
        if (!params['limit']) params['limit'] = '12';
        if (!params['p']) params['p'] = jQuery("li.current").html();
        delete params['no_cache'];


        if(jQuery("link[rel=canonical]").length > 0)
        {
            jQuery("link[rel=canonical]").attr("href", this.url.value + '?' + Object.toQueryString(params));
        }

        if(jQuery("link[rel=prev]").length > 0 || jQuery("link[rel=next]").length > 0)
        {
            if (jQuery("a.previous").length == 0) {
                if (jQuery("link[rel=prev]").length != 0) // no rel=prev on the first page, remove if exists
                    jQuery("link[rel=prev]").remove();
            } else {
                if (jQuery("link[rel=prev]").length == 0) // create rel=prev if not exists
                    jQuery('head').append('<link rel="prev">');
                var paramsprev = jQuery("a.previous").attr("href").parseQuery();
                params['p'] = paramsprev['p'];
                jQuery("link[rel=prev]").attr("href", $(nav_url).value + '?' + Object.toQueryString(params));
            }

            if (jQuery("a.next").length == 0) {
                if (jQuery("link[rel=next]").length != 0) // no rel=next on the last page, remove if exists
                    jQuery("link[rel=next]").remove();
            } else {
                if (jQuery("link[rel=next]").length == 0) // create rel=next if not exists
                    jQuery('head').append('<link rel="next">');
                var paramsnext = jQuery("a.next").attr("href").parseQuery();
                params['p'] = paramsnext['p'];
                jQuery("link[rel=next]").attr("href", this.url.value + '?' + Object.toQueryString(params));
            }
        }
    },

    attributeMakeRequest: function(href) {
        href = href.gsub(/\+/, ' ');
        var params = $('adj-nav-params').value.parseQuery();
        if (href.indexOf('?') > -1) {
            var href = href.parseQuery();
            params = Object.extend(params, href);
            $(nav_params).value = Object.toQueryString(params);
        }
        this.makeRequest();
    },

    categoryMakeRequest: function(catId, reload) {
        // param for search pages
        var query = document.URL.parseQuery().q;
        if (!query || query == 'undefined') {
            query = '';
        }

        if (reload) { // categories behavior switch
            setLocation('?dir=desc&p=clear&cat='+catId+(query ? '&q='+query : ''));
        } else {
            this.addParams('cat', catId, 1);
            this.addParams('p', 'clear', 1);
            this.makeRequest();
        }
    },


    updateToggle: function() {

        $j('p.block-subtitle.block-subtitle--filter').on('click', function () {
            $j(this)
                .toggleClass('active')
                .next()
                .toggleClass('no-display');
        });
        // Hide the content
        $j('p.block-subtitle.block-subtitle--filter').addClass('active');
        //$j('p.block-subtitle.block-subtitle--filter').next().addClass('no-display');


        this.adjnavMenuEnquireRegister(false);


        $j('.block-layered-nav > div.toggle-content').each(this.updateToggleContent.bind(this));


    },

    updateToggleContent: function(index,value){
        var wrapper = jQuery(value);
        var dl = wrapper.find('dl:first');
        var dts = dl.find('dt');
        var panes = dl.find('dd');
        var groups = new Array(dts, panes);

        //Add "last" classes.
        var i;
        for (i = 0; i < groups.length; i++) {
            groups[i].filter(':last').addClass('last');
        }


        //Toggle on tab (dt) click.
        this.updateDtsToggle(dts,groups);
    },

    updateDtsToggle: function(dts,groups){

        function toggleClasses(clickedItem, group) {
            var index = group.index(clickedItem);
            var i;
            for (i = 0; i < groups.length; i++) {
                groups[i].removeClass('current');
                groups[i].eq(index).addClass('current');
            }
        }


        dts.unbind('click');
        dts.on('click', function (e) {
            e.stopImmediatePropagation();
                        //They clicked the current dt to close it. Restore the wrapper to unclicked state.
            if (jQuery(this).hasClass('current') && jQuery(this).closest('div.toggle-content').hasClass('accordion-open')) {
                jQuery(this).closest('div.toggle-content').removeClass('accordion-open');
            } else {
                //They're clicking something new. Reflect the explicit user interaction.
                jQuery(this).closest('div.toggle-content').addClass('accordion-open');
            }
            toggleClasses(jQuery(this), dts);
        });
    },

    adjnavMenuEnquireRegister: function(updateDtsOnMatch){
        if ($j('.col-left-first > #adj-nav-navigation').length && $j('.category-products').length) {
            enquire.register('screen and (max-width: ' + bp.medium + 'px)', {
                match: function () {
                    $j('.col-left-first').insertBefore($j('#adj-nav-container'));
                    $j('#narrow-by-list-top div').children('dd,dt').show().each(function(index,value){
                        value.removeClassName('last').removeClassName('adj-nav-dt-selected').addClassName('top');
                    }).insertBefore($j('#narrow-by-list dt:first'));
                    $j('#narrow-by-list-top div.adjnav-filter').remove();
                    $j('#narrow-by-list').addClass('no-display');

                    $j('#narrow-by-list-top').addClass('no-display');
                    $j('p.block-subtitle.block-subtitle--filter').removeClass('active');
                    if(updateDtsOnMatch){
                        adjnavNavigation.updateDtsToggle($j('#narrow-by-list dt'),new Array($j('#narrow-by-list dt'),$j('#narrow-by-list dd')));
                    }
                    adjnavNavigation.clearObservers('#narrow-by-list a.attr-val-more');
                    featured_values = new AdjnavAttributeValues('#narrow-by-list a.attr-val-more');
                },
                unmatch: function () {
                    // Move layered nav back to left column
                    adjnavNavigation.clearObservers('#narrow-by-list a.attr-val-more, #narrow-by-list-top a.attr-val-more')
                    var topFilters=$j('.col-left-first .top');
                    topFilters.each(function(index){
                        if(((index+1)%2)==0){
                            var elements=$j([]);
                            elements=elements.add(topFilters[index-1]).add(topFilters[index]);
                            var div=$j("<div class='adjnav-filter'></div>").append(elements);
                            div.appendTo($j('#narrow-by-list-top'));
                        }
                    });
                    $j('.col-left-first').insertBefore($j('.col-main'));
                    $j('#narrow-by-list').removeClass('no-display');
                    $j('#narrow-by-list-top').removeClass('no-display');
                    $j('p.block-subtitle.block-subtitle--filter').removeClass('active');
                    featured_values = new AdjnavAttributeValues('#narrow-by-list a.attr-val-more');
                    featured_values_top = new AdjnavAttributeValues('#narrow-by-list-top a.attr-val-more');
                }
            });
            $j('p.block-subtitle.block-subtitle--filter').removeClass('active');
        }
    },

    clearObservers: function(dl){
        $$(dl).each(function(a){
            a.stopObserving();
        });
    }
});
var adjnavNavigation = new AdjnavNavigation();