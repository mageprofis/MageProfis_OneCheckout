// OneCheckout
var OneCheckout = Class.create();
OneCheckout.prototype = {
    initialize: function () {
        this.saveUrl = '/onecheckout/ajax/update';
        this.reviewUrl = '/onecheckout/ajax/review';
        this.failureUrl = '/checkout/cart';
        this.completeUrl = '';
        this.submitted = false;
        this.loadWaiting = false;
        this.agreements = null;
        this.steps = null;
        this.preloginUrl = '';
        this.imsg = '';
        this.ctime = 0;
        this.triggerShipping = new Array();
        this.triggerPayment = new Array();
        this.triggerReview = new Array();
        this.updateAreas = '';
        this.redirectBeforeSend = false;
    },
    getSteps: function () {
        this.steps = new Array(billing, shipping, shippingMethod, payment, review);
    },
    ajaxFailure: function(){
        location.href = this.failureUrl;
    },
    save: function () {
        if (this.loadWaiting) {
            return;
        }

        doSave = true;
        this.getSteps();
        for (i = 0; i < this.steps.length; i++) {
            if (this.steps[i].beforeSave()) {
            } else {
                doSave = false;
                break;
            }
        }

        if (doSave) {
            that = this;
            this.loadWaiting = true;
            $("review-please-wait").show();
            new Ajax.Request(this.completeUrl, {
                method: 'post',
                parameters: this.collectParameters(),
                onSuccess: function (transport) {
                    that.evaluateResponse(transport);
                    that.loadWaiting = false;
                    $("review-please-wait").hide();
                }
            });
        }
    },
    triggered: function (trigger) {
        toUpdate = new Array();
        for (i = 0; i < this.triggerShipping.length; i++) {
            if (trigger == this.triggerShipping[i]) {
                toUpdate.push("shipping-method");
                break;
            }
        }
        for (i = 0; i < this.triggerPayment.length; i++) {
            if (trigger == this.triggerPayment[i]) {
                toUpdate.push("payment-method");
                break;
            }
        }
        for (i = 0; i < this.triggerReview.length; i++) {
            if (trigger == this.triggerReview[i]) {
                toUpdate.push("review");
                break;
            }
        }
        return toUpdate;
    },
    update: function (trigger) {
        if (this.loadWaiting) {
            return;
        }
        cax = $$("#shippingmethod input.radio");
        cbx = $$("#shippingmethod input.radio");
        if (cax && cax.length > 1 && this.imsg.length + 10 < this.ctime) {
            if (cbx && cbx.length > 1) {
                alert(this.imsg);
                return;
            }
        }
        this.updateAreas = this.triggered(trigger);
        if (this.updateAreas.length == 0) {
            return;
        }


        that = this;
        paras = this.collectParameters();
        this.setLoading(true);
        new Ajax.Request(this.saveUrl, {
            method: 'post',
            parameters: paras,
            onSuccess: function (transport) {
                that.evaluateResponse(transport);
                new Ajax.Request(that.reviewUrl, {
                    method: 'post',
                    parameters: paras,
                    onSuccess: function (transport) {
                        that.setLoading(false);
                        that.evaluateResponse(transport);
                    }
                });
            }
        });
    },
    setLoadWaiting: function (flag) {
        //this.loadWaiting = flag;
    },
    setLoading: function (flag) {
        //areas = new Array("checkout-review-load", "checkout-shipping-method-load", "checkout-payment-method-load");
        areas = this.updateAreas;

        for (i = 0; i < areas.length; i++) {
            area = "checkout-" + areas[i] + "-load";

            if (flag) {
                this.loadWaiting = true;
                $(area).update("");
                $(area).addClassName("opc-ajax-loader");
            } else {
                that.loadWaiting = false;
                $(area).removeClassName("opc-ajax-loader");
            }
        }
    },
    collectParameters: function () {
        paras = {};
        if ($("login:guest") && $("login:guest").checked) {
            paras = {checkout_method: 'register'};
        }
        this.getSteps();
        for (i = 0; i < this.steps.length; i++) {
            if ($(this.steps[i].form)) {
                Object.extend(paras, $(this.steps[i].form).serialize(true));
            }
        }
        return paras;
    },
    init: function () {
        this.agreements = $('checkout-agreements');
        this.setTriggers("default");
        that = this;

        // show/hide shipping address
        $("billing:use_for_shipping_no").observe("click", function () {
            if (this.checked) {
                $("shippingaddress").show();
            }
        });
        $("billing:use_for_shipping_yes").observe("click", function () {
            if (this.checked) {
                $("shippingaddress").hide();
            }
        });

        // register checkbox
        if ($("login:guest") && $("register-customer-password")) {
            $("login:guest").observe("click", function () {
                if (this.checked) {
                    $("register-customer-password").show();
                } else {
                    $("register-customer-password").hide();
                }
            });
            $("register-customer-password").hide();
        }

        // login form
        if ($("onecheckout-login")) {
            $("onecheckout-login").observe("click", function () {
                $(this).hide();
                new Effect.SlideDown("onecheckout-login-form", {duration: 0.5});

                new Ajax.Request(that.preloginUrl, {
                    method: 'post',
                });
            });

        }
    },
    setTriggers: function (source) {
        that = this;

        if (source == "default" || source == "checkout-shipping-method-load") {
            $$("#shippingmethod input.radio").each(function (elem) {
                elem.observe("click", function () {
                    that.update("shipping_method");
                });
            });
        }
        if (source == "default" || source == "checkout-payment-method-load") {
            $$("#paymentmethod input.radio").each(function (elem) {
                elem.observe("click", function () {
                     that.update("payment_method");
                });
            });
        }

        if (source == "default" && $("billing:country_id")) {
            $("billing:country_id").observe("change", function () {
                that.update("country");
            });
            $("shipping:country_id").observe("change", function () {
                that.update("country");
            });
        }

        if (source == "default" && $("billing:region_id")) {
            $("billing:region_id").observe("change", function () {
                that.update("region");
            });
            $("shipping:region_id").observe("change", function () {
                that.update("region");
            });
        }

        if (source == "default" && $("billing:postcode")) {
            $("billing:postcode").observe("blur", function () {
                that.update("postcode");
            });
            $("shipping:postcode").observe("blur", function () {
                that.update("postcode");
            });
        }

        if (source == "default" && $("billing:use_for_shipping_yes")) {
            $("billing:use_for_shipping_yes").observe("click", function () {
                // todo: there might be a better update cause
                that.update("country");
            });
        }
    },
    evaluateResponse: function (transport) {
        if (transport && transport.responseText) {
            try {
                response = eval('(' + transport.responseText + ')');
            } catch (e) {
                response = {};
            }
            if (response.redirect) {
                this.isSuccess = true;
                location.href = response.redirect;
                return;
            }
            if (!response.updates) {
                if (response.redirect_before_send) {
                    this.redirectBeforeSend = response.redirect_before_send;
                }else{
                    this.redirectBeforeSend = false;
                }
            }   
            if (response.success) {
                this.isSuccess = true;
                window.location = this.successUrl;
            } else {
                var msg = response.error_messages;
                if (typeof (msg) == 'object') {
                    msg = msg.join("\n");
                }
                if (msg) {
                    alert(msg);
                }
            }

            if (response.error) {
                if (response.fields) {
                    var fields = response.fields.split(',');
                    for (var i = 0; i < fields.length; i++) {
                        var field = null;
                        if (field = $(fields[i])) {
                            Validation.ajaxError(field, response.error);
                        }
                    }
                    return;
                }
                if (typeof (response.error) != "boolean") {
                    alert(response.error);
                }
            }

            if (response.updates) {
                for (param in response.updates) {
                    $(param).update(response.updates[param]);
                    this.setTriggers(param);
                }
            }

        }
    },
};

// billing
var Billing = Class.create();
Billing.prototype = {
    initialize: function (form, addressUrl, saveUrl) {
        this.form = form;
        if ($(this.form)) {
            $(this.form).observe('submit', function (event) {
                this.beforeSave();
                Event.stop(event);
            }.bind(this));
        }
        this.addressUrl = addressUrl;
        this.saveUrl = saveUrl;
        this.onAddressLoad = this.fillForm.bindAsEventListener(this);
        this.onComplete = this.resetLoadWaiting.bindAsEventListener(this);
    },
    setAddress: function (addressId) {
        if (addressId) {
            request = new Ajax.Request(
                    this.addressUrl + addressId,
                    {method: 'get', onSuccess: this.onAddressLoad, onFailure: checkout.ajaxFailure.bind(checkout)}
            );
        }
        else {
            this.fillForm(false);
        }
    },
    newAddress: function (isNew) {
        if (isNew) {
            this.resetSelectedAddress();
            Element.show('billing-new-address-form');
        } else {
            Element.hide('billing-new-address-form');
        }
    },
    resetSelectedAddress: function () {
        var selectElement = $('billing-address-select')
        if (selectElement) {
            selectElement.value = '';
        }
    },
    fillForm: function (transport) {
        var elementValues = {};
        if (transport && transport.responseText) {
            try {
                elementValues = eval('(' + transport.responseText + ')');
            }
            catch (e) {
                elementValues = {};
            }
        }
        else {
            this.resetSelectedAddress();
        }
        arrElements = Form.getElements(this.form);
        for (var elemIndex in arrElements) {
            if (arrElements[elemIndex].id) {
                var fieldName = arrElements[elemIndex].id.replace(/^billing:/, '');
                arrElements[elemIndex].value = elementValues[fieldName] ? elementValues[fieldName] : '';
                if (fieldName == 'country_id' && billingForm) {
                    billingForm.elementChildLoad(arrElements[elemIndex]);
                }
            }
        }
    },
    setUseForShipping: function (flag) {
        $('shipping:same_as_billing').checked = flag;
    },
    beforeSave: function () {
        if (checkout.loadWaiting != false)
            return;

        var validator = new Validation(this.form);
        if (validator.validate()) {
            return true;
        }
    },
    resetLoadWaiting: function (transport) {
        checkout.setLoadWaiting(false);
    },
}

// shipping
var Shipping = Class.create();
Shipping.prototype = {
    initialize: function (form, addressUrl, saveUrl, methodsUrl) {
        this.form = form;
        if ($(this.form)) {
            $(this.form).observe('submit', function (event) {
                this.beforeSave();
                Event.stop(event);
            }.bind(this));
        }
        this.addressUrl = addressUrl;
        this.saveUrl = saveUrl;
        this.methodsUrl = methodsUrl;
        this.onAddressLoad = this.fillForm.bindAsEventListener(this);
        this.onComplete = this.resetLoadWaiting.bindAsEventListener(this);
    },
    setAddress: function (addressId) {
        if (addressId) {
            request = new Ajax.Request(
                    this.addressUrl + addressId,
                    {method: 'get', onSuccess: this.onAddressLoad, onFailure: checkout.ajaxFailure.bind(checkout)}
            );
        }
        else {
            this.fillForm(false);
        }
    },
    newAddress: function (isNew) {
        if (isNew) {
            this.resetSelectedAddress();
            Element.show('shipping-new-address-form');
        } else {
            Element.hide('shipping-new-address-form');
        }
        shipping.setSameAsBilling(false);
    },
    resetSelectedAddress: function () {
        var selectElement = $('shipping-address-select')
        if (selectElement) {
            selectElement.value = '';
        }
    },
    fillForm: function (transport) {
        var elementValues = {};
        if (transport && transport.responseText) {
            try {
                elementValues = eval('(' + transport.responseText + ')');
            }
            catch (e) {
                elementValues = {};
            }
        }
        else {
            this.resetSelectedAddress();
        }
        arrElements = Form.getElements(this.form);
        for (var elemIndex in arrElements) {
            if (arrElements[elemIndex].id) {
                var fieldName = arrElements[elemIndex].id.replace(/^shipping:/, '');
                arrElements[elemIndex].value = elementValues[fieldName] ? elementValues[fieldName] : '';
                if (fieldName == 'country_id' && shippingForm) {
                    shippingForm.elementChildLoad(arrElements[elemIndex]);
                }
            }
        }
    },
    setSameAsBilling: function (flag) {
        $('shipping:same_as_billing').checked = flag;
// #5599. Also it hangs up, if the flag is not false
//        $('billing:use_for_shipping_yes').checked = flag;
        if (flag) {
            this.syncWithBilling();
        }
    },
    syncWithBilling: function () {
        $('billing-address-select') && this.newAddress(!$('billing-address-select').value);
        $('shipping:same_as_billing').checked = true;
        if (!$('billing-address-select') || !$('billing-address-select').value) {
            arrElements = Form.getElements(this.form);
            for (var elemIndex in arrElements) {
                if (arrElements[elemIndex].id) {
                    var sourceField = $(arrElements[elemIndex].id.replace(/^shipping:/, 'billing:'));
                    if (sourceField) {
                        arrElements[elemIndex].value = sourceField.value;
                    }
                }
            }
            //$('shipping:country_id').value = $('billing:country_id').value;
            shippingRegionUpdater.update();
            $('shipping:region_id').value = $('billing:region_id').value;
            $('shipping:region').value = $('billing:region').value;
            //shippingForm.elementChildLoad($('shipping:country_id'), this.setRegionValue.bind(this));
        } else {
            $('shipping-address-select').value = $('billing-address-select').value;
        }
    },
    setRegionValue: function () {
        $('shipping:region').value = $('billing:region').value;
    },
    beforeSave: function () {
        if (checkout.loadWaiting != false)
            return;
        var validator = new Validation(this.form);
        if (validator.validate()) {
            return true;
        }
        return false;
    },
    resetLoadWaiting: function (transport) {
        checkout.setLoadWaiting(false);
    },
}

// shipping method
var ShippingMethod = Class.create();
ShippingMethod.prototype = {
    initialize: function (form, saveUrl) {
        this.form = form;
        if ($(this.form)) {
            $(this.form).observe('submit', function (event) {
                this.beforeSave();
                Event.stop(event);
            }.bind(this));
        }
        this.saveUrl = saveUrl;
        this.validator = new Validation(this.form);
        this.onComplete = this.resetLoadWaiting.bindAsEventListener(this);
    },
    validate: function () {
        var methods = document.getElementsByName('shipping_method');
        if (methods.length == 0) {
            alert(Translator.translate('Your order cannot be completed at this time as there is no shipping methods available for it. Please make necessary changes in your shipping address.'));
            return false;
        }

        if (!this.validator.validate()) {
            return false;
        }

        for (var i = 0; i < methods.length; i++) {
            if (methods[i].checked) {
                return true;
            }
        }
        alert(Translator.translate('Please specify shipping method.'));
        return false;
    },
    beforeSave: function () {

        if (checkout.loadWaiting != false)
            return;
        if (this.validate()) {
            return true;
        }
        return false;
    },
    resetLoadWaiting: function (transport) {
        checkout.setLoadWaiting(false);
    },
}

// payment
var Payment = Class.create();
Payment.prototype = {
    beforeInitFunc: $H({}),
    afterInitFunc: $H({}),
    beforeValidateFunc: $H({}),
    afterValidateFunc: $H({}),
    initialize: function (form, saveUrl) {
        this.form = form;
        if ($(this.form)) {
            $(this.form).observe('submit', function (event) {
                this.beforeSave();
                Event.stop(event);
            }.bind(this));
        }
        this.saveUrl = saveUrl;
        this.onComplete = this.resetLoadWaiting.bindAsEventListener(this);
    },
    addBeforeInitFunction: function (code, func) {
        this.beforeInitFunc.set(code, func);
    },
    beforeInit: function () {
        (this.beforeInitFunc).each(function (init) {
            (init.value)();
            ;
        });
    },
    init: function () {
        this.beforeInit();
        var elements = Form.getElements(this.form);
        var method = null;
        for (var i = 0; i < elements.length; i++) {
            if (elements[i].name == 'payment[method]') {
                if (elements[i].checked) {
                    method = elements[i].value;
                }
            } else {
                elements[i].disabled = true;
            }
            elements[i].setAttribute('autocomplete', 'off');
        }
        if (method)
            this.switchMethod(method);
        this.afterInit();
    },
    addAfterInitFunction: function (code, func) {
        this.afterInitFunc.set(code, func);
    },
    afterInit: function () {
        (this.afterInitFunc).each(function (init) {
            (init.value)();
        });
    },
    switchMethod: function (method) {
        if (this.currentMethod && $('payment_form_' + this.currentMethod)) {
            this.changeVisible(this.currentMethod, true);
            $('payment_form_' + this.currentMethod).fire('payment-method:switched-off', {method_code: this.currentMethod});
        }
        if ($('payment_form_' + method)) {
            this.changeVisible(method, false);
            $('payment_form_' + method).fire('payment-method:switched', {method_code: method});
        } else {
            //Event fix for payment methods without form like "Check / Money order"
            document.body.fire('payment-method:switched', {method_code: method});
        }
        if (method) {
            this.lastUsedMethod = method;
        }
        this.currentMethod = method;
    },
    changeVisible: function (method, mode) {
        var block = 'payment_form_' + method;
        [block + '_before', block, block + '_after'].each(function (el) {
            element = $(el);
            if (element) {
                element.style.display = (mode) ? 'none' : '';
                element.select('input', 'select', 'textarea', 'button').each(function (field) {
                    field.disabled = mode;
                });
            }
        });
    },
    addBeforeValidateFunction: function (code, func) {
        this.beforeValidateFunc.set(code, func);
    },
    beforeValidate: function () {
        var validateResult = true;
        var hasValidation = false;
        (this.beforeValidateFunc).each(function (validate) {
            hasValidation = true;
            if ((validate.value)() == false) {
                validateResult = false;
            }
        }.bind(this));
        if (!hasValidation) {
            validateResult = false;
        }
        return validateResult;
    },
    validate: function () {
        //return false;
        var result = this.beforeValidate();
        if (result) {
            return true;
        }
        var methods = document.getElementsByName('payment[method]');
        if (methods.length == 0) {
            alert(Translator.translate('Your order cannot be completed at this time as there is no payment methods available for it.'));
            return false;
        }
        for (var i = 0; i < methods.length; i++) {
            if (methods[i].checked) {
                return true;
            }
        }
        result = this.afterValidate();
        if (result) {
            return true;
        }
        alert(Translator.translate('Please specify payment method.'));
        return false;
    },
    addAfterValidateFunction: function (code, func) {
        this.afterValidateFunc.set(code, func);
    },
    afterValidate: function () {
        var validateResult = true;
        var hasValidation = false;
        (this.afterValidateFunc).each(function (validate) {
            hasValidation = true;
            if ((validate.value)() == false) {
                validateResult = false;
            }
        }.bind(this));
        if (!hasValidation) {
            validateResult = false;
        }
        return validateResult;
    },
    beforeSave: function () {
        if (checkout.loadWaiting != false)
            return;
        var validator = new Validation(this.form);
        if (this.validate() && validator.validate()) {
            return true;
        }
        return false;
    },
    resetLoadWaiting: function () {
        checkout.setLoadWaiting(false);
    },
    save: function () {
        // does not needed
    },
    initWhatIsCvvListeners: function () {
        $$('.cvv-what-is-this').each(function (element) {
            Event.observe(element, 'click', toggleToolTip);
        });
    }
}

var Review = Class.create();
Review.prototype = {
    initialize: function (agreementsForm) {
        this.form = agreementsForm;
        if ($(this.form)) {
            $(this.form).observe('submit', function (event) {
                this.beforeSave();
                Event.stop(event);
            }.bind(this));
        }
    },
    beforeSave: function () {
        if (checkout.loadWaiting != false)
            return;
        var validator = new Validation(this.form);
        if (validator.validate()) {
            if(checkout.redirectBeforeSend!=false){
                location.href = checkout.redirectBeforeSend;
                return false;
            }
            return true;
        }
        return false;
    },
    save: function () {
        payment.save(); // ogone compatiblity
        checkout.save();
    },
    resetLoadWaiting: function (transport) {
        checkout.setLoadWaiting(false, this.isSuccess);
    },
}

// only for compatibility to ogone
accordion = new Object();
accordion.openSection = function () {
}
