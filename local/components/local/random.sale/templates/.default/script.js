BX.ready(function () {

    const createCodeSale = () => {
        const btnGo = document.querySelector('#js-create-code-sale')
        const errorContainer = document.querySelector('.error-promo-code')
        const saleContrainer = document.querySelector('.sale-container-item-code-count')
        const saleCode = document.querySelector('#js-sale-code')
        const saleCodeDesctiption = document.querySelector('#js-sale-code-description')

        BX.ajax.runComponentAction("local:random.sale", "createCode", {
            mode: "class"
        }).then(function (response) {
            console.log(response)
            if (Object.keys(response.data).length != 0) {
                console.log(response.data.SALE_COUNT)
                console.log(response.data.DESCRIPTION)
                if (errorContainer.classList.contains('containerShow')) {
                    errorContainer.classList.toggle('containerShow')
                    // errorContainer.classList.toggle('containerHide')
                }
                if (!saleContrainer.classList.contains('containerShow')) {
                    saleContrainer.classList.toggle('containerShow')
                    saleCode.innerHTML = response.data.SALE_COUNT + '%'
                    saleCodeDesctiption.innerHTML = response.data.DESCRIPTION
                }
                btnGo.remove()
            }
        });
    };

    const buttonCreateCodeSale = document.querySelector('#js-create-code-sale')
    buttonCreateCodeSale.addEventListener('click', createCodeSale, false)
})