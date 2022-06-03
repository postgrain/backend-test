<?php

namespace Tests\Unit\App\Services;

use Money\Money;
use Tests\TestCase;
use App\Services\CartService;
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

        $cartService = $this->createCartServiceInstance($products);

        // Action
        $subtotal = $cartService->calculateSubtotal();

        // Assertions
        $this->assertEquals(Money::BRL(12000), $subtotal);
    }

    public function testShouldCalculateCartTotal(): void
    {
        // Set
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
        $cartService = $this->createCartServiceInstance($products);

        // Action
        $total = $cartService->getTotal();

        // Assertions
        $this->assertEquals(Money::BRL(9500), $total);
    }

    /**
     * @param array<int, array<string, numeric-string>> $products
     */
    private function createCartServiceInstance(array $products = []): CartService
    {
        $currencies = new ISOCurrencies();
        $moneyParser = new DecimalMoneyParser($currencies);
        $moneyFormatter = new DecimalMoneyFormatter($currencies);

        return new CartService($products, $moneyParser, $moneyFormatter);
    }
}
