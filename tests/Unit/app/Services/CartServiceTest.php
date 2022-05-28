<?php

namespace App\Services;

use Money\Money;
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

    public function testShouldCalculateCartSubtotal(): void
    {
        // Set
        /** @var array<int, array<string, numeric-string>> $products */
        $products = [
            [
                'unitPrice' => '30.0',
                'quantity' => 2,
            ],
            [
                'unitPrice' => '20.0',
                'quantity' => 3,
            ],
        ];

        $cartService = new CartService($products);

        // Action
        $subtotal = $cartService->calculateSubtotal();

        // Assertions
        $this->assertEquals(Money::BRL(12000), $subtotal);
    }

    public function testShouldCalculateCartTotal(): void
    {
        // Set
        $cartService = new CartService();
        $cartService->setSubtotal(Money::BRL(30000));
        $cartService->setDiscount(Money::BRL(10000));

        // Action
        $total = $cartService->getTotal();

        // Assertions
        $this->assertEquals(Money::BRL(20000), $total);
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
