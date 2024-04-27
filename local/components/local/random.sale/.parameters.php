<?
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();

//use Bitrix\Main\Loader;
//use Bitrix\Main\Localization\Loc;

//if (!Loader::includeModule('iblock'))
//    return;

$arUserFieldsDiapasone = [];
$arUserFieldsValueSale = [];
$arUserFieldsDateTime = [];
$arUserFields = \Bitrix\Main\UserFieldTable::getList(
    [
        'filter' => [
            '=ENTITY_ID' => 'USER'
        ],
        'select' => [
            'ID',
            'FIELD_NAME',
            'USER_TYPE_ID'
        ]
    ]
)->fetchAll();

if (!empty($arUserFields)) {
    foreach ($arUserFields as $arItems) {
        if ($arItems['USER_TYPE_ID'] == 'string') {
            $arUserFieldsDiapasone[$arItems['FIELD_NAME']] = $arItems['FIELD_NAME'];
        }
        if ($arItems['USER_TYPE_ID'] == 'double') {
            $arUserFieldsValueSale[$arItems['FIELD_NAME']] = $arItems['FIELD_NAME'];
        }
        if ($arItems['USER_TYPE_ID'] == 'datetime') {
            $arUserFieldsDateTime[$arItems['FIELD_NAME']] = $arItems['FIELD_NAME'];
        }
    }
}

$arComponentParameters = [
    'PARAMETERS' => [
        'FIELD_RANDOM_CODE_SALE' => array(
            'PARENT' => 'BASE',
            'NAME' => GetMessage('GROUP_PARAM_FIELD_RANDOM_CODE_SALE'),
            'TYPE' => 'LIST',
            'VALUES' => $arUserFieldsDiapasone,
        ),
        'FIELD_RANDOM_COUNT_SALE' => array(
            'PARENT' => 'BASE',
            'NAME' => GetMessage('GROUP_PARAM_FIELD_RANDOM_COUNT_SALE'),
            'TYPE' => 'LIST',
            'VALUES' => $arUserFieldsValueSale,
        ),
        'FIELD_RANDOM_CODE_DATE' => array(
            'PARENT' => 'BASE',
            'NAME' => GetMessage('GROUP_PARAM_FIELD_RANDOM_CODE_DATE'),
            'TYPE' => 'LIST',
            'VALUES' => $arUserFieldsDateTime,
        ),
        'MIN_SALE' => array(
            'PARENT' => 'BASE',
            'NAME' => GetMessage('GROUP_PARAM_OTHER_MIN_SALE'),
            'TYPE' => 'STRING',
            'DEFAULT' => '',
        ),
        'MAX_SALE' => array(
            'PARENT' => 'BASE',
            'NAME' => GetMessage('GROUP_PARAM_OTHER_MAX_SALE'),
            'TYPE' => 'STRING',
            'DEFAULT' => '',
        ),
        'COUNT_CODE' => array(
            'PARENT' => 'BASE',
            'NAME' => GetMessage('GROUP_PARAM_FIELD_COUNT_CODE'),
            'TYPE' => 'STRING',
            'DEFAULT' => '',
        ),
    ]
];