function CardPayment(url, pubkey)
{
    this.url = url;
    this.pubkey = pubkey;
    $("#card_payment_form").attr("action", url);

    var numberInput = $('#card_number'),
        expiryInput = $('#expiry_date'),
        cvcInput = $('#cvc'),
        termsOfServiceInput = $('input[name="conditions_to_approve[terms-and-conditions]"]');
    const TRIGGER_EVENTS = 'input change blur';

	function hashAsync(algo, str)
    {
        return crypto.subtle.digest(algo, new TextEncoder("utf-8").encode(str)).then(buf => {
            return Array.prototype.map.call(new Uint8Array(buf), x => (('00' + x.toString(16)).slice(-2))).join('');
        });
    }

    function SubmitPayment()
    {
        let cardRedirectType = document.querySelector('input[name=redirect_type]');
        if (cardRedirectType.value === 'redirect'){
            $('#card_payment_form').submit();
        }

        var cardNumber = numberInput.val().replace(/\s/g, ''),
            cd = cardNumber + '|' + expiryInput.val()
	            .replace(/\s/g, '') + '|' + cvcInput.val().replace(/\s/g, '') + '|' + document.location.origin,
            encrypt = new JSEncrypt(),
            decoded = Base64.decode(pubkey),
            encrypted;
        $("#payment-confirmation button").fadeOut();
        $(".tpay-card-wrapper .tpay-preload").fadeIn();
        encrypt.setPublicKey(decoded);
        encrypted = encrypt.encrypt(cd);
        $("#carddata").val(encrypted);
        $("#card_vendor").val($.payment.cardType(cardNumber));
        $("#card_short_code").val(cardNumber.substr(-4));

        numberInput.val('');
        expiryInput.val('');
        cvcInput.val('');

        hashAsync("SHA-256", cardNumber).then(hash => hash).then(val => {
            $("#card_hash").val(val);
            $('#card_payment_form').submit();
        });

    }

    function setWrong(elem)
    {
        elem.addClass('wrong').removeClass('valid');
    }

    function setValid(elem)
    {
        elem.addClass('valid').removeClass('wrong');
    }


	function checkValidate(elem, state)
	{
		elem.addClass('valid');
		elem.removeClass('wrong');

		if(!state) {
			elem.addClass('wrong');
			elem.removeClass('valid');
		}
	}


    function validateCcNumber($elem)
    {
        var isValid = false,
            ccNumber = $.payment.formatCardNumber($elem.val()),
            supported = ['mastercard', 'maestro', 'visa'],
            type = $.payment.cardType(ccNumber),
            notValidNote = $('#info_msg_not_valid'),
            cardTypeHolder = $('.tpay-card-icon'),
            notSupportedNote = $('#info_msg_not_supported');
        $elem.val($.payment.formatCardNumber($elem.val()));
        cardTypeHolder.attr('class', 'tpay-card-icon');
        if (supported.indexOf(type) < 0 && type !== null && ccNumber.length > 1) {
            showElem(notSupportedNote);
            hideElem(notValidNote);
            setWrong($elem);
        } else if (supported.indexOf(type) > -1 && $.payment.validateCardNumber(ccNumber)) {
            setValid($elem);
            hideElem(notSupportedNote);
            hideElem(notValidNote);
            isValid = true;
        } else if (ccNumber.length < 4) {
            hideElem(notSupportedNote);
            hideElem(notValidNote);
            setWrong($elem);
        } else {
            setWrong($elem);
            showElem(notValidNote);
            hideElem(notSupportedNote);
        }
        if (type !== '') {
            cardTypeHolder.addClass('tpay-' + type + '-icon');
        }

        return isValid;
    }

    function hideElem($elem)
    {
        $elem.css('display', 'none');
    }

    function showElem($elem)
    {
        $elem.css('display', 'block');
    }

    function validateExpiryDate($elem)
    {
        var isValid = false, expiration;
        $elem.val($.payment.formatExpiry($elem.val()));
        expiration = $elem.payment('cardExpiryVal');
        if (!$.payment.validateCardExpiry(expiration.month, expiration.year)) {
            setWrong($elem);
        } else {
            setValid($elem);
            isValid = true;
        }

        return isValid;
    }

    function validateCvc($elem)
    {
        var isValid = false;
        if (!$.payment.validateCardCVC($elem.val(), $.payment.cardType(numberInput.val().replace(/\s/g, '')))) {
            setWrong($elem);
        } else {
            setValid($elem);
            isValid = true;
        }

        return isValid;
    }


	function validateClause(element) {
		let isValid;

		const notValidCause = $('#info_msg_cause');
		const button = document.querySelector('#payment-confirmation button');

        if (null === element) {
            isValid = true;
        } else {
            isValid = element.is(':checked');
        }

		if(!isValid) {
			showElem(notValidCause);
			setWrong(element);
			checkValidate(element, false);
		} else {
			hideElem(notValidCause);
			setValid(element);
			checkValidate(element, true);
		}

		return isValid;
	}

    function checkForm()
    {
        const savedCards = document.querySelector('input[name=savedId]');
        let cardRedirectType = document.querySelector('input[name=redirect_type]');
        if (cardRedirectType.value === 'redirect'){
            return validateClause(termsOfServiceInput)
        }

        let isValidForm = false;
        if (
	        (
				validateCcNumber(numberInput) &&
				validateExpiryDate(expiryInput) &&
				validateCvc(cvcInput) &&
				validateClause(termsOfServiceInput)
	        ) ||
	        (
		        validateClause(termsOfServiceInput) &&
		        (savedCards && savedCards.checked === true)
	        )
        ) {
            isValidForm = true;
        }

        return isValidForm;
    }

    function isCardContainerChosen(e)
    {
        var cardContainer = $('#card_payment_form').parents('div').eq(1);

        if (!cardContainer.is(':visible')){
            return false;
        }

        e.preventDefault();
        e.stopPropagation();

        return true;
    }

    $('#payment-confirmation button').click(function (e) {
        if(isCardContainerChosen(e) && checkForm()) {
            SubmitPayment();
		}
    });

    numberInput.on(TRIGGER_EVENTS, function () {
        validateCcNumber($(this));
    });

    expiryInput.on(TRIGGER_EVENTS, function () {
        validateExpiryDate($(this));
    });

    cvcInput.on(TRIGGER_EVENTS, function () {
        validateCvc($(this));
    });

    let input = document.querySelector('input[name="conditions_to_approve[terms-and-conditions]"]');
    if (null !== input) {
        input.addEventListener('click', (e) => {
            validateClause(termsOfServiceInput);
        })
    }
}

//// Card init
document.addEventListener("DOMContentLoaded", function (e) {
    const savedCards = document.querySelector('input[name=savedId]');
    const newCardForm = document.querySelector('.tpay-card-new');

    if (savedCards !== null) {
        savedCards.checked = true;

        handleCardForm(newCardForm);
        handleCardFormNewCard(newCardForm);
    }
});

function handleCardForm(newCardForm)
{
    const savedCards = document.querySelectorAll('input[name=savedId]');

    if (savedCards) {
        for (const card of savedCards) {
            card.addEventListener('click', (e) => {
                newCardForm.classList.remove('tpay-fadeIn');
                newCardForm.classList.add('tpay-fadeOut');
            }, false)
        }
    }

}

function handleCardFormNewCard(newCardForm)
{
    const btn = document.querySelector('#newCard');

    if (btn !== null) {
        btn.addEventListener("click", (e) => {
            newCardForm.classList.remove('tpay-fadeOut');
            newCardForm.classList.add('tpay-fadeIn');
        });
    }
}

$('body').on('click', '[data-link-action=delete-credit-card]', function () {
    const action = $(this).attr('href');
    const id = $(this).attr('data-id');
    handleCardFormRemove(action, id);

    return false;
});

function handleCardFormRemove(action, id)
{
    $.ajax({
        type: 'POST',
        cache: false,
        dataType: "json",
        url: action,
        data: {
            'id': id
        },
        success: function (jsonData) {
            if (jsonData['results'] === 'success') {
                $('[data-card-id=' + id + ']').hide();
            } else {
            }
        }
    });
}
