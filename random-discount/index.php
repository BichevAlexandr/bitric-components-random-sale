<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
$APPLICATION->SetTitle("Интернет-магазин \"Одежда\"");

$APPLICATION->IncludeComponent(
    "local:random.sale",
    "",
    Array(
        "FIELD_RANDOM_CODE_DATE" => "UF_DATE_RANDOM_SALE",
        "FIELD_RANDOM_CODE_SALE" => "UF_RANDOM_CODE_SALE",
        "FIELD_RANDOM_COUNT_SALE" => "UF_RANDOM_COUNT_SALE",
        "COUNT_CODE" => "6",
        "MAX_SALE" => "2",
        "MIN_SALE" => "1"
    )
);

require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>