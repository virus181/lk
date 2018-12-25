<?php
namespace Page;

use AcceptanceTester;

class Login
{
    // include url of current page
    public static $URL = '/';

    /**
     * Declare UI map for this page here. CSS or XPath allowed.
     * public static $usernameField = '#username';
     * public static $formSubmitButton = "#mainForm input[type=submit]";
     */

    /**
     * Basic route example for your current URL
     * You can append any additional parameter to URL
     * and use it in tests like: Page\Edit::route('/123-post');
     */
    public static function route($param)
    {
        return static::$URL.$param;
    }

    /**
     * @var AcceptanceTester
     */
    protected $tester;

    public function __construct(\AcceptanceTester $I)
    {
        $this->tester = $I;
    }

    /**
     * @param string $name
     * @param string $password
     * @return $this
     */
    public function login(string $name, string $password)
    {
        $I = $this->tester;

        $I->amOnPage(self::$URL);
        $I->fillField('LoginForm[email]', $name);
        $I->fillField('LoginForm[password]', $password);
        $I->click('login-button');

        return $this;
    }
}
