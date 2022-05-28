<?php

namespace App\Services;

use Money\Currency;
use Tests\TestCase;
use Money\Currencies\ISOCurrencies;
use Money\Parser\DecimalMoneyParser;
use Money\Formatter\DecimalMoneyFormatter;

class CartServiceTest extends TestCase
{
    public function testShouldProvideCartServiceInstances(): void
    {
        // Set
        $cartService = $this->createCartServiceInstance();

        // Assertions
        $this->assertTrue($cartService instanceof CartService);
    }

    private function createCartServiceInstance(): CartService
    {
        $products = [];
        $currency = new Currency('BRL');
        $currencies = new ISOCurrencies();
        $moneyParser = new DecimalMoneyParser($currencies);
        $moneyFormatter = new DecimalMoneyFormatter($currencies);

        return new CartService($products, $currency, $moneyParser, $moneyFormatter);
    }
}
