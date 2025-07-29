// Requires const variables
// const maxInstallments
// const installmentMinValue

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
    
    for (let i = 1, installment = value / i; i <= maxInstallments && installment >= installmentMinValue; i++, installment = value / i) {
        let item = `<option value='${i}' ${selectedValue == i ? 'selected' : ''}>${i} x ${brlCurrency.format(installment)}</option>`;
        installmentList += item;
    }
    
    if (installmentList === '' && value > 0) {
        installmentList = `<option value='1'>1 x ${brlCurrency.format(value)}</option>`;
    }
    selectInstallments.innerHTML = installmentList;
}

