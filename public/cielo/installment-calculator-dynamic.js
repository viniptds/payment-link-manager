// Requires const variables
// const maxInstallments
// const installmentMinValue
// const paymentMaxInstallments
// const paymentInitialValue
// var availableMaxInstallments

// Call updateInstallments function with 2 required params to apply installments based on input's value
function updateInstallments(value, select, selectedValue = 1) {
    let selectInstallments = document.querySelector('#' + select);
    let installmentList = '';
    // let value = input.value;

    if (!value) {
        value = input.value;
    }

    let brlCurrency = new Intl.NumberFormat('en-US', {
        style: 'currency',
        currency: 'BRL',
    });

    let i = 1;
    for (i = 1, installment = value / i; i <= maxInstallments && installment >= installmentMinValue; i++, installment = value / i) {
        // TODO: fix decimal issue
        let item = `<option value='${i}' ${selectedValue == i ? 'selected' : ''}>${i} x ${brlCurrency.format(installment)}</option>`;
        installmentList += item;
    }

    if (installmentList === '' && value > 0) {
        installmentList = `<option value='1'>1 x ${brlCurrency.format(value)}</option>`;
    }
    selectInstallments.innerHTML = installmentList;
    availableMaxInstallments = i - 1;
}

document.addEventListener("DOMContentLoaded", function (e) {
    // let input = document.querySelector('#valueInput');
    let input = '#valueInput';
    let selectedMaxInstallments = paymentMaxInstallments;
    let initiaValue = paymentInitialValue;
    // let initiaValue = "{{ $payment->value }}"
    // console.log(initiaValue);
    // initiaValue = initiaValue.replace('', ''); // ensure decimal is dot
    // console.log(initiaValue);
    $(input).maskMoney('mask', parseFloat(initiaValue));
    // let initiaValue = "{{ sprintf('%.2f', $payment->value) }}"; 
    // initiaValue = initiaValue.replace(',', '.'); // ensure decimal is dot
    // $(input).maskMoney('mask', parseFloat(initiaValue));

    let value = $(input).maskMoney('unmasked')[0];
    updateInstallments(parseFloat(value).toFixed(2), 'maxInstallmentsSelect', selectedMaxInstallments);

    // if (availableMaxInstallments != maxInstallments) {
        setInstallmentTypeOptions(selectedMaxInstallments);
    // }
    $('.maxInstallmentsValue').each(function () {
        if (selectedMaxInstallments) {
            $(this).text(selectedMaxInstallments);
        }
    })
});

$('#maxInstallmentsSelect').on('change', function (e) {
    // select a specific option from typeMaxInstallmentsSelect
    let selectedMaxInstallments = $(this).val();

    // Add options to typeMaxInstallmentsSelect

    setInstallmentTypeOptions(selectedMaxInstallments);

    if (selectedMaxInstallments < maxInstallments) {
        // updateInstallments(selectedMaxInstallments, 'typeMaxInstallmentsSelect', 1);
        let installmentType = $('#typeMaxInstallmentsSelect').val();

        if (installmentType == '') {
            $('#typeMaxInstallmentsSelect').val('max')
        }
    }
});

function setInstallmentTypeOptions(selectedMaxInstallments = 1) {
    $('#typeMaxInstallmentsSelect').empty();
    if (selectedMaxInstallments < maxInstallments) {
        $('#typeMaxInstallmentsSelect').append(`<option value=''>1x a ${maxInstallments}x</option>`);
    }
    if (selectedMaxInstallments < availableMaxInstallments && availableMaxInstallments == maxInstallments) {
        $('#typeMaxInstallmentsSelect').append(`<option value='max'>1x a ${selectedMaxInstallments}x</option>`);
    }
    if (selectedMaxInstallments < maxInstallments) {
        $('#typeMaxInstallmentsSelect').append(`<option value='min'>${selectedMaxInstallments}x a ${maxInstallments}x</option>`);
    }
    $('#typeMaxInstallmentsSelect').append(`<option value='exact'>Somente em ${selectedMaxInstallments}x</option>`);
}