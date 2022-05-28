<?php

namespace App\Services;

use Money\Money;
use Money\Currency;
use Money\MoneyParser;
use Money\MoneyFormatter;
use Money\Currencies\ISOCurrencies;
use Money\Parser\DecimalMoneyParser;
use Money\Formatter\DecimalMoneyFormatter;

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

    /**
     * @var Money $subtotal
     */
    protected $subtotal;

    /** @param array<int, array<string, numeric-string>> $products */
    public function __construct(
        array $products = [],
        ?Currency $currency = null,
        ?MoneyParser $moneyParser = null,
        ?MoneyFormatter $moneyFormatter = null
    ) {
        $currencies = new ISOCurrencies();

        $this->products = $products;
        $this->currency = $currency ?? new Currency('BRL');
        $this->moneyParser = $moneyParser ?? new DecimalMoneyParser($currencies);
        $this->moneyFormatter = $moneyFormatter ?? new DecimalMoneyFormatter($currencies);

        $this->subtotal = $this->calculateSubtotal();
    }

    public function getSubtotal(): Money
    {
        return $this->subtotal;
    }

    public function setSubtotal(Money $value): void
    {
        $this->subtotal = $value;
    }

    public function calculateSubtotal(): Money
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
