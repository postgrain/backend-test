<?php

namespace App\Services;

use Money\Money;
use Money\Currency;
use Money\MoneyParser;
use Money\MoneyFormatter;

class CartService
{
    /**
     * @var array<int, array<string, numeric-string>> $products
     */
    protected $products;

    /**
     * @var Currency $currency
     */
    protected $currency;

    /**
     * @var MoneyParser $moneyParser
     */
    protected $moneyParser;

    /**
     * @var MoneyFormatter $moneyFormatter
     */
    protected $moneyFormatter;

    /** @param array<int, array<string, numeric-string>> $products */
    public function __construct(
        array $products,
        Currency $currency,
        MoneyParser $moneyParser,
        MoneyFormatter $moneyFormatter
    ) {
        $this->products = $products;
        $this->currency = $currency;
        $this->moneyParser = $moneyParser;
        $this->moneyFormatter = $moneyFormatter;
    }

    public function getSubtotal(): Money
    {
        return array_reduce($this->products, function ($acc, array $product) {
            /** @var string $price */
            $price = $product['unitPrice'];

            /** @var int $quantity */
            $quantity = $product['quantity'];

            $unitPrice = $this->moneyParser->parse($price, $this->currency);
            $amount = $unitPrice->multiply($quantity);
            $acc = $acc->add($amount);

            return $acc;
        }, Money::BRL(0));
    }
}
