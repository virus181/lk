<?php

use Codeception\Util\Locator;

class OrderCalculateCest
{
    public function _before(AcceptanceTester $I)
    {
    }

    // tests
    public function tryToTest(AcceptanceTester $I)
    {
        $I->wantTo('Проверка просмотра магазина в ЛК');
        $loginPage = new \Page\Login($I);
        $loginPage->login('sleverin@bk.ru', 'srs666tt');
        if (method_exists($I, 'wait')) {
            $I->wait(1); // only for selenium
        }
        $I->see('Приветствуем Вас в системе Fastery');
        $I->click(Locator::href( '/orders' ));
        $I->waitForJs('return document.readyState == "complete"', 5);
        $I->see('Создать заказ');
        $I->click(Locator::href( '/order/create' ));
        if (method_exists($I, 'wait')) {
            $I->wait(1); // only for selenium
        }
        $I->selectOption('Order[shop_id]', 'Тестовый магазин 3');
        $I->fillField('Product[0][barcode]', '11111');
        $I->fillField('Product[0][name]', 'Тест');
        $I->fillField('Product[0][weight]', '1');
        $I->fillField('Product[0][quantity]', '1');
        $I->fillField('Product[0][price]', '1000');
        $I->fillField('Product[0][accessed_price]', '1000');
        $I->fillField('Address[full_address]', 'Москва, Лесная, 7');
        if (method_exists($I, 'wait')) {
            $I->wait(1); // only for selenium
        }
        $I->see('г Москва, ул Лесная, д 7');
        $I->click('div.autocomplete-suggestions  div.autocomplete-suggestion ');
        $I->see('Получение доставки');
        if (method_exists($I, 'wait')) {
            $I->wait(5); // only for selenium
        }
        $I->see('Доступные способы доставки');
        $I->click('.quote .apply button');
        if (method_exists($I, 'wait')) {
            $I->wait(10); // only for selenium
        }
        $I->see('Изменить доставку');
    }
}
