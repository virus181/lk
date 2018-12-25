<?php

use Codeception\Util\Locator;

class ProductCest
{
    public function _before(AcceptanceTester $I)
    {
    }

    // tests
    public function tryToTest(AcceptanceTester $I)
    {
        $I->wantTo('Проверка просмотра товара в ЛК');
        $loginPage = new \Page\Login($I);
        $loginPage->login('sleverin@bk.ru', 'srs666tt');
        if (method_exists($I, 'wait')) {
            $I->wait(1); // only for selenium
        }
        $I->see('Приветствуем Вас в системе Fastery');
        $I->click(Locator::href( '/products' ));
        $I->waitForJs('return document.readyState == "complete"', 5);
        $I->see('Создать товар');
        $I->click('.table tbody tr:first-child td:nth-child(2)');
        if (method_exists($I, 'wait')) {
            $I->wait(1); // only for selenium
        }
        $I->see('Сохранить');
        $I->see('Товар:');
    }
}
