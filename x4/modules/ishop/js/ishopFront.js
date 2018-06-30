moduleRegister.pushModule('ishop');

var ishopFront = new Class(
    {
        Extends: x4FrontModule,
        preventJquery: false,
        constructor: function () {
            this.parent('ishop');

        },

        setPaySystem: function (paysystem) {
            this.connector.execute({set_paysystem: {paysystem: paysystem}});
        },


        addToCart: function (id, count, isSku, extendedFields, callback) {
        
            
            if (!id) return;
            obj = {id: id, count: count};
            if (extendedFields) {
                obj['extendedData'] = extendedFields;
            }
            if (isSku) {
                obj['isSku'] = isSku;
            }
            this.connector.execute({addProductToCart: obj}, callback);
            if (!callback) return this.connector.result.cart;
        },


        calculateDelivery: function (deliveryId) {

            this.connector.execute({calculateCartWithDelivery: {id: deliveryId}});
            return this.connector.result.delivery;
        },

        setOrderData: function (data) {
            this.connector.execute({setOrderData: {data: data}});
        },

        getCartInfo: function () {
            this.connector.execute({getCartInfo: true});
            return this.connector.result.cartInfo;
        },

        getLastCartItems: function () {
            this.connector.execute({getLastCartItems: true});
            return this.connector.result.cartItems;
        },

        getCurrentCurrency: function () {
            result = this.connector.execute({getCurrentCurrency: true});
            return this.connector.result.currency;

        },

        getCartItems: function () {
            this.connector.execute({getCart: true});
            return this.connector.result.cartItems;
        },

        jqueryRun: function () {
            if (this.preventJquery) return;



            $.fn.ishopToBasket = function (options) {
                var defaults = {
                    count: 1,
                    idAttribute: 'id',
                    onGoodAdded: null,
                    extDataFunc: null,
                    countDataFunc:null,
                    onFinishedFunc:null,
                    isSKUFunc:null,
                    syncAddToBasket: true,
                    basketContainer: '.basket-container',
                    basketCountSelector: '.basket-count',
                    basketAllCountSelector: '.basket-count',
                    basketSumSelector: '.basket-sum',
                    basketElementTemplate: '<li><a>{details.props.Name}</a> <span>{count}</span> <span>{details.props.price}</span> <span>{priceSum}</span></li>'
                };
                console.log(options);
                var options = $.extend(defaults, options);

                console.log(options);

                $(this).click(function (e) {
                    e.preventDefault();
                    ext = {};
                    if (jQuery.isFunction(options.extDataFunc)) ext = options.extDataFunc(this);
                    if (jQuery.isFunction(options.countDataFunc)) options.count = options.countDataFunc(this);
                    if (jQuery.isFunction(options.isSKUFunc)){
                        isSKU = options.isSKUFunc(this);}else{
                        isSKU=false;}


                    cart = ishop.addToCart($(this).attr(options.idAttribute), options.count, isSKU, ext, options.onGoodAdded);


                    if (!options.onGoodAdded) {
                        $(options.basketCountSelector).html(cart.itemsCount);
                        $(options.basketAllCountSelector).html(cart.allCount);
                        $(options.basketSumSelector).html(cart.orderSum);

                        if (options.syncAddToBasket) {
                            items = ishop.getCartItems();

                            if(options.basketContainer)
                            {
                                var container = $(options.basketContainer);
                                container.html('');
                                $.each(items, function (i, item) {
                                    container.append($.nano(options.basketElementTemplate, item))
                                });
                            }
                        }
                        if (jQuery.isFunction(options.onFinishedFunc))options.onFinishedFunc(this,cart);
                    }
                });

            }

        }

    });

