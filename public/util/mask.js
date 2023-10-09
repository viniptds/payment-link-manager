
function maskCPF(element) {
    let value = element.value;
    let positions = [
        {
            "pos": 3,
            "signal": '.'
        },
        {
            "pos": 7,
            "signal": '.'
        },
        {
            "pos": 11,
            "signal": '-'
        }
    ];

    if (value.length) {
        value = value.split('');
        value.forEach((item, i) => {
            if(!['0','1','2','3','4','5','6','7','8','9'].includes(item)) {
                value.splice(i, 1);
            }
        })
        value = value.join('');
    }

    positions.filter((position, index) => {
        if (value[position.pos] == position.signal) {
            if (value.length == position.pos + 1) {
                value = value.split('');
                value.splice(position.pos, 1);
                value = value.join('');
            }
        } else {
         if (value.length > position.pos && value[position.pos] != position.signal) {
                value = value.split('');
                value.splice(position.pos, 0, position.signal);
                value = value.join('');
            }
        }

        if (value.length > element.maxlength) {
            value = value.split('');
            value.splice(value.length-1, value.length - element.maxlength);
            value = value.join('');
        }
        element.value = value;
    })
}

function maskCard(element) {
    let value = element.value;
    let positions = [
        {
            "pos": 4,
            "signal": ' '
        },
        {
            "pos": 9,
            "signal": ' '
        },
        {
            "pos": 14,
            "signal": ' '
        }
    ];

    if (value.length) {
        value = value.split('');
        value.forEach((item, i) => {
            if(!['0','1','2','3','4','5','6','7','8','9'].includes(item)) {
                value.splice(i, 1);
            }
        })
        value = value.join('');
    }

    positions.filter((position, index) => {
        
        
        if (value[position.pos] == position.signal) {
            if (value.length == position.pos + 1) {
                value = value.split('');
                value.splice(position.pos, 1);
                value = value.join('');
            }
        } else {
         if (value.length > position.pos && value[position.pos] != position.signal) {
                value = value.split('');
                value.splice(position.pos, 0, position.signal);
                value = value.join('');
            }
        }

        if (value.length > element.maxlength) {
            value = value.split('');
            value.splice(value.length-1, value.length - element.maxlength);
            value = value.join('');
        }
        element.value = value;
    })
}


function maskNumber(element, decimal = 0) {
    let value = element.value;
    if (value.length) {
        value = value.split('');

        value.forEach((item, i) => {
            // Passar por todos os itens de value
            if(!['0','1','2','3','4','5','6','7','8','9'].includes(item)) {
                value.splice(i, 1);
            }
        })
        value = value.join('');
        element.value = value;
    }
}

document.addEventListener("DOMContentLoaded", function(e) {
    let cpf = document.querySelectorAll(".cpf-mask");
    cpf.forEach((item) => {
        maskCPF(item);
        item.addEventListener('keyup', function(e) {
            maskCPF(e.target);
        })
    });

    let card = document.querySelectorAll(".card-mask");
    card.forEach((item) => {
        maskCard(item);
        item.addEventListener('keyup', function(e) {
            maskCard(e.target);
        })
    });
    
    let number = document.querySelectorAll(".number-mask");
    number.forEach((item) => {
        maskNumber(item);
        item.addEventListener('keyup', function(e) {
            maskNumber(e.target);
        })
    })

});
