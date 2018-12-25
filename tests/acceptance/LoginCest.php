<?php

class LoginCest
{
    public function _before(AcceptanceTester $I)
    {
    }

    // tests
    public function tryToTest(AcceptanceTester $I)
    {
        $I->wantTo('Проверка авторизация в ЛК');

        $loginPage = new \Page\Login($I);
        $loginPage->login('sleverin@bk.ru', 'srs666tt');

        if (method_exists($I, 'wait')) {
            $I->wait(3); // only for selenium
        }
        $I->see('Приветствуем Вас в системе Fastery');
    }
}
