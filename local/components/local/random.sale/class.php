<?
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();

use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Error;
use Bitrix\Main\Errorable;
use Bitrix\Main\ErrorCollection;
use Bitrix\Main\Engine\ActionFilter;
use Bitrix\Main\Engine\Contract\Controllerable;
use CBitrixComponent;

class RandomSale extends CBitrixComponent implements Controllerable, Errorable
{
    private $str = '23456789ABCDEFGHKLMNPQRSTUVWXYZ';
    private $countCode = 6;
    private $minSale = 1;
    private $maxSale = 50;
    private $classElementShow = 'containerShow';
    private $classElementHide = 'containerHide';

    protected ErrorCollection $errorCollection;

    public function onPrepareComponentParams($arParams)
    {
        $arParams['USER_ID'] = \Bitrix\Main\Engine\CurrentUser::get()->getId();
        $this->errorCollection = new ErrorCollection();

        if (!empty($arParams['FIELD_RANDOM_CODE_DATE'])) {
            $arParams['FILEDS'][] = $arParams['FIELD_RANDOM_CODE_DATE'];
        } else {
            ShowError(Loc::getMessage('ERROR_CMP_NO_FIELD_RANDOM_CODE_DATE'));
            return false;
        }

        if (!empty($arParams['FIELD_RANDOM_CODE_SALE'])) {
            $arParams['FILEDS'][] = $arParams['FIELD_RANDOM_CODE_SALE'];
        } else {
            ShowError(Loc::getMessage('ERROR_CMP_ERROR_COMP_NO_FIELD_RANDOM_CODE_SALE'));
            return false;
        }

        if (!empty($arParams['FIELD_RANDOM_COUNT_SALE'])) {
            $arParams['FILEDS'][] = $arParams['FIELD_RANDOM_COUNT_SALE'];
        } else {
            ShowError(Loc::getMessage('ERROR_CMP_ERROR_COMP_NO_FIELD_RANDOM_COUNT_SALE'));
            return false;
        }

        if (empty($arParams['MAX_SALE'])) $arParams['MAX_SALE'] = $this->maxSale;
        if (empty($arParams['MIN_SALE'])) $arParams['MIN_SALE'] = $this->minSale;
        if (empty($arParams['COUNT_CODE'])) $arParams['MIN_SALE'] = $this->countCode;

        return $arParams;
    }

    public function getErrors(): array
    {
        return $this->errorCollection->toArray();
    }

    public function getErrorByCode($code): Error
    {
        return $this->errorCollection->getErrorByCode($code);
    }

    public function configureActions()
    {
    }


    /**
     * Генерируем скидку
     * @return array
     */
    public function createCodeAction()
    {
        return $this->codeGeneration();
    }

    /**
     * Поиск скидкип о коду
     * @return array
     */
    public function searchSaleToCodeAction()
    {
        $arParams = $this->request();
        if (!empty($arParams['code'])) {
            $arSale = $this->searchSaleCode($arParams['code']);
            if (!empty($arSale) && $arSale['ID'] == $this->arParams['USER_ID']) {
                $arDateDiff = self::dateDiff($arSale['UF_DATE_RANDOM_SALE']);
                return $this->accessSaleToTime($arDateDiff, $arSale);
            } else {
                if (!$arSale) {
                    return [
                        'ERROR' => Loc::getMessage('PROMOCODE_NO_SEARCH_CODE'),
                        'ERROR_CLASS' => $this->classElementShow
                    ];
                }
                return [
                    'ERROR' => Loc::getMessage('PROMOCODE_NO_CODE'),
                    'ERROR_CLASS' => $this->classElementShow,
                ];
            }
        }
    }


    /**
     * Получение данных по пользователю
     * @return array|false
     * @throws \Bitrix\Main\ArgumentException
     * @throws \Bitrix\Main\ObjectPropertyException
     * @throws \Bitrix\Main\SystemException
     */
    public function getUserSale()
    {
        if ($this->arParams['USER_ID'] > 0) {

            $arResult = [];

            $arRes = \Bitrix\Main\UserTable::getList(
                [
                    'filter' => [
                        '=ID' => $this->arParams['USER_ID'],
                        '!=UF_RANDOM_CODE_SALE' => false,
                        '!=UF_RANDOM_COUNT_SALE' => false,
                        '!=UF_DATE_RANDOM_SALE' => false
                    ],
                    'select' => [
                        'UF_RANDOM_CODE_SALE',
                        'UF_RANDOM_COUNT_SALE',
                        'UF_DATE_RANDOM_SALE'
                    ]
                ]
            )->fetch();

            if (!empty($arRes)) {
                //  Если скидка была сгенерирована ранее
                $arDateDiff = self::dateDiff($arRes['UF_DATE_RANDOM_SALE']);
                if (!empty($arDateDiff)) {
                    return $this->accessSaleToTime($arDateDiff, $arRes);
                }
            } else {
                //  Если первый раз
                $arResult = [
                    'ERROR' => Loc::getMessage('PROMOCODE_NO_SEARCH_USER_CODE_SALE'),
                    'ERROR_CLASS' => $this->classElementShow,
                    'COUNT_SALE' => 0,
                    'BTN_CREATE_CODE_SALE' => Loc::getMessage('PROMOCODE_NO_SEARCH_USER_CODE_SALE'),
                    'NEW_SALE_CODE' => true
                ];
                return $arResult;
            }
        }
        return false;
    }

    private function accessSaleToTime($arTime, $arSale)
    {
        $arResult = [];

        if ($arTime['HOURS'] >= 3) {
            $arResult = [
                'ERROR' => Loc::getMessage('PROMOCODE_NO_CODE'),
                'ERROR_CLASS' => $this->classElementShow,
                'FORM_CLASS' => $this->classElementHide,
                'NEW_SALE_CODE' => false
            ];
            return $arResult;
        } else if ($arTime['HOURS'] < 3) {
            $arResult = [
                'COUNT_SALE' => $arSale['UF_RANDOM_COUNT_SALE'],
                'ERROR_CLASS' => $this->classElementHide,
                'FORM_CLASS' => $this->classElementShow,
                'DESCRIPTION' => Loc::getMessage('PROMOCODE_SALE_CODE_DESCRIPTION')
            ];
            return $arResult;
        }
    }

    /**
     * Поиск скидки по коду
     * @param $code
     * @return array|false
     * @throws \Bitrix\Main\ArgumentException
     * @throws \Bitrix\Main\ObjectPropertyException
     * @throws \Bitrix\Main\SystemException
     */
    private function searchSaleCode($code)
    {
        if (!empty($code)) {
            $arUser = \Bitrix\Main\UserTable::getList(
                [
                    'filter' => [
                        '=UF_RANDOM_CODE_SALE' => $code
                    ],
                    'select' => [
                        'ID',
                        'UF_RANDOM_COUNT_SALE',
                        'UF_RANDOM_CODE_SALE',
                        'UF_DATE_RANDOM_SALE'
                    ]
                ]
            )->fetch();
            if (!empty($arUser)) {
                return $arUser;
            }
            return false;
        }
    }

    /**
     * Получение разницы между текущим временем и временем создания промокода
     * @param $date
     */
    private static function dateDiff($date)
    {
        if (!empty($date)) {
            $date = DateTime::createFromFormat('d.m.Y H:i:s', $date);
            $now = new DateTime();
            $arResult['HOURS'] = $date->diff($now)->format('%h');
            $arResult['MINUTES'] = $date->diff($now)->format('%i');
            return $arResult;
        }
        return false;
    }

    private function codeGeneration()
    {
        $arResult = [];

        $arRes = \Bitrix\Main\UserTable::getList(
            [
                'filter' => [
                    '!=UF_RANDOM_CODE_SALE' => false
                ],
                'select' => [
                    'UF_RANDOM_CODE_SALE'
                ]
            ]
        )->fetchAll();

        if (!empty($arRes)) {
            foreach ($arRes as $arCode) {
                $arResult['CODE_COLLECTION'][] = $arCode['UF_RANDOM_CODE_SALE'];
            }
        }

        $arResult['SALE_CODE'] = substr(str_shuffle($this->str), 0, $this->countCode);
        if (is_array($arResult['CODE_COLLECTION']) && in_array($arResult['SALE_CODE'], $arResult['CODE_COLLECTION'])) {
            $arResult['SALE_CODE'] = substr(str_shuffle($this->str), 0, $this->countCode);
        }
        $arResult['SALE_COUNT'] = rand($this->minSale, $this->maxSale);
        $arResult['DESCRIPTION'] = Loc::getMessage('PROMOCODE_SALE_CODE_DESCRIPTION');

        $save = $this->saveSale($arResult['SALE_CODE'], $arResult['SALE_COUNT']);
        if ($save) {
            return $arResult;
        }
    }

    private function saveSale($code, $count)
    {
        if (!empty($code) && !empty($count)) {
            $user = new CUser;
            $fields = [
                'UF_RANDOM_CODE_SALE' => $code,
                'UF_RANDOM_COUNT_SALE' => $count,
                'UF_DATE_RANDOM_SALE' => date('d.m.Y H:i:s')
            ];
            $user->Update($this->arParams['USER_ID'], $fields);
            return true;
        }
        return false;
    }

    private function getUserDetail($userID)
    {
        if ($userID > 0) {

        }
        return false;
    }

    private function request()
    {
        $request = Bitrix\Main\HttpApplication::getInstance()->getContext()->getRequest();
        return $request->getPostList()->toArray();
    }

    public function executeComponent()
    {


        $this->arResult['USER']['ID'] = \Bitrix\Main\Engine\CurrentUser::get()->getId();

        if ($this->arResult['USER']['ID'] <= 0) {
            $this->arResult['ERROR'] = Loc::GetMessage("ERROR_NO_USER_ID");
            $this->arResult['AUTHORIZATION'] = false;
        } else {
            $this->arResult['AUTHORIZATION'] = true;
        }

        $this->arResult['ITEMS'] = self::getUserSale();
        $this->includeComponentTemplate();
        return $this->arResult;
    }

}