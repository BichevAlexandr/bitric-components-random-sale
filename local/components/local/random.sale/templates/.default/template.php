<?php

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}

?>
<div class="sale-container">
    <? if ($arResult['AUTHORIZATION']) { ?>
        <? if (!empty($arResult['ITEMS'])) { ?>
            <div class="sale-container-item">
                <div class="sale-container-item-code">
                    <span class="error-promo-code <?= $arResult['ITEMS']['ERROR_CLASS'] ?>"><?= $arResult['ITEMS']['ERROR'] ?></span>
                    <div class="sale-container-item-code-count <? if ($arResult['ITEMS']['COUNT_SALE'] > 0) { ?>containerShow<? } ?>">
                        <span id="js-sale-code"><?= $arResult['ITEMS']['COUNT_SALE'] ?>%</span>
                        <span id="js-sale-code-description"><?= $arResult['ITEMS']['DESCRIPTION'] ?></span>
                    </div>
                </div>
                <div class="sale-container-item-form">
                    <form onsubmit="return false">
                        <input class="input-sale-code" id="js-input-sale-code" type="text" name="code"
                               placeholder="Введите код для проверки">
                        <button class="input-sale-btn" id="js-input-sale-btn">Проверить скидку</button>
                    </form>
                </div>
            </div>
        <? } ?>
        <? if ($arResult['ITEMS']['NEW_SALE_CODE']) { ?>
            <button id="js-create-code-sale">Получить скидку</button>
        <? } ?>
    <? } else { ?>
        <div class="sale-container-no-login">
            <p><?= $arResult['ERROR'] ?></p>
            <a href="/login/?login=yes&backurl=%2Frandom-discount%2F">Войти</a>
        </div>
    <? } ?>
</div>

<script>
    const buttonGetSaleToCode = document.querySelector('#js-input-sale-btn')
    const errorContainer = document.querySelector('.error-promo-code')
    const saleContrainer = document.querySelector('.sale-container-item-code-count')
    const saleCode = document.querySelector('#js-sale-code')
    const saleCodeDesctiption = document.querySelector('#js-sale-code-description')

    buttonGetSaleToCode.addEventListener('click', function () {
        const codeValue = document.getElementById("js-input-sale-code").value
        if (codeValue.trim() !== '') {
            BX.ajax.runComponentAction("local:random.sale", "searchSaleToCode", {
                mode: "class",
                data: {
                    "code": codeValue
                }
            }).then(function (response) {
                if (Object.keys(response.data).length !== 0) {
                    if (response.data.ERROR) {
                        console.log(response.data.ERROR)
                        if (!errorContainer.classList.contains('containerShow')) {
                            errorContainer.classList.toggle('containerShow')
                        }
                        if (saleContrainer.classList.contains('containerShow')) {
                            saleContrainer.classList.toggle('containerShow')
                        }
                        errorContainer.innerHTML = response.data.ERROR
                    } else {
                        if (response.data.COUNT_SALE > 0 && response.data.DECRIPTION !== '') {
                            if (errorContainer.classList.contains('containerShow')) {
                                errorContainer.classList.toggle('containerShow')
                            }
                            if (!saleContrainer.classList.contains('containerShow')) {
                                saleContrainer.classList.toggle('containerShow')
                            }
                            saleCode.innerHTML = response.data.COUNT_SALE + '%'
                            saleCodeDesctiption.innerHTML = response.data.DESCRIPTION
                        }
                    }
                }
            });
        }
    });
</script>
