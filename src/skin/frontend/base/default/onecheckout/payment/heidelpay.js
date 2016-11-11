checkout.currentStep = 'review';
var Heidelpay = {};

Heidelpay.toggle = Class.create({
    initialize: function () {
        this.once = 0;
    },
    getOnce: function () {
        return this.once;
    },
    setOnce: function () {
        this.once++
    },
    resetOnce: function () {
        this.once = 0;
    },

    hpform: function (actPayment, change) {
        var replace = '';
        $(actPayment + "_hpform").toggle();
    },
    button: function (url) {
        $$('.btn-hcdmpa').each(Element.toggle);
        $$(".btn-checkout").each(Element.toggle);
        $$(".masterpass-please-wait").each(Element.toggle);

        window.location.href = url;
    }
});

Heidelpay.toggle.getInstance = function () {
    if (!this.instance) {
        this.instance = new this();
    }
    return this.instance;
};

Heidelpay.checkIban = Class.create({
    check: function (elem) {
        var value = $(elem).getValue();
        var iban_id = $(elem).identify();
        var prefix = iban_id.split('_', 1);
        var bic_id = prefix[0] + '_bic';

        if ($(bic_id) != undefined) {

            if (value.match(/^[A-Za-z]{2}/)) {
                $(bic_id).up().hide();
                $(bic_id).disable();
            } else {
                $(bic_id).up().show();
                $(bic_id).enable();
            }
        }
    }
});

Heidelpay.checkIban.getInstance = function () {
    if (!this.instance) {
        this.instance = new this();
    }
    return this.instance;
}

var hcdreceive = {};

function receiveMessage(e) {

    var recMsg = JSON.parse(e.data);
    //console.log(e.data);
    if (recMsg["POST.VALIDATION"] == "NOK" || recMsg["PROCESSING.RESULT"] == 'NOK') {
        Heidelpay.toggle.getInstance().resetOnce();
        hcdreceive.complete = false;
        hcdreceive.error = true;
    } else {
        hcdreceive.complete = true;
        hcdreceive.error = false;
    }
}
 
payment.save = payment.save.wrap(
    function (origMethod) {
        doInit = true
        if (!window.review || review.overriddenOnSave || review.overriddenOnComplete) {
            doInit = false;
        }

        if (doInit)
        {
            var actPayment = $$('input:checked[type=radio][name=\'payment[method]\']')[0].id.replace(/p_method_/, "");

            if (actPayment == 'hcdcc' || actPayment == 'hcddc') {
                var newreg = $$('input:checked[type=radio][name=\'' + actPayment + '_use_again\']')[0].value;
                if (Heidelpay.toggle.getInstance().getOnce() == 0) {
                    var newreg = $$('input:checked[type=radio][name=\'' + actPayment + '_use_again\']')[0].value;
                    if (newreg == 1 && checkout.currentStep == 'review') {
                        var url = $(actPayment + '_payment_frame').readAttribute('src');
                        var arr = url.split("/");
                        var targetOrigin = arr[0] + "//" + arr[2];
                        var paymentFrameIframe = $(actPayment + '_payment_frame');
                        var data = {};
                        checkout.setLoadWaiting('review');
                        paymentFrameIframe.contentWindow.postMessage(JSON.stringify(data), targetOrigin);

                        if (window.addEventListener) {  // W3C DOM
                            window.addEventListener('message', receiveMessage);
                        } else if (window.attachEvent) { // IE DOM
                            window.attachEvent('onmessage', receiveMessage);
                        }
                        Heidelpay.toggle.getInstance().setOnce();
                    }

                }
            }
        }
        origMethod();
    }
);

var checkoutIntervall = 0;
checkout.save = checkout.save.wrap(function (origMethod) {
    window.clearInterval(checkoutIntervall);
    var actPayment = $$('input:checked[type=radio][name=\'payment[method]\']')[0].id.replace(/p_method_/, "");
    if (actPayment == 'hcdcc' || actPayment == 'hcddc') {
        var hcdsecurebreak = 0;
        checkoutIntervall = window.setInterval(function() {
            if (hcdreceive.error)
            {
                alert(Translator.translate('Please enter a valid credit card number.'));
                window.clearInterval(checkoutIntervall);
                return;
            }
            if (hcdreceive.complete)
            {
                window.clearInterval(checkoutIntervall);
                origMethod();
                return;
            }
            hcdsecurebreak++;
            if (hcdsecurebreak > 10)
            {
                alert(Translator.translate('Please enter a valid credit card number.'));
                window.clearInterval(checkoutIntervall);
                return;
            }
        }, 300);
    } else {
        origMethod();
    }
});