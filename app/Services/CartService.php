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

    /**
     * @var array{total: Money, strategy: string} $discount
     */
    protected $discount;

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
        $this->discount = $this->calculateDiscount();
    }

    public function getSubtotal(): Money
    {
        return $this->subtotal;
    }

    public function setSubtotal(Money $value): void
    {
        $this->subtotal = $value;
    }

    /** @return array{total: Money, strategy: string} */
    public function getDiscount(): array
    {
        return $this->discount;
    }

    public function setDiscount(Money $value, string $strategy): void
    {
        $this->discount = [
            'total' => $value,
            'strategy' => $strategy,
        ];
    }

    public function getTotal(): Money
    {
        return $this->subtotal->subtract($this->discount['total']);
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

    /** @return array{total: Money, strategy: string} */
    public function calculateDiscount(): array
    {
        $elegibleDiscounts = $this->getAllElegibleDiscounts();

        if (!count($elegibleDiscounts)) {
            return [
                'strategy' => 'none',
                'total' => Money::BRL(0),
            ];
        }

        return $this->getHighestDiscount($elegibleDiscounts);
    }

    /** @return array{}|array{array{total: Money, strategy: string}} */
    private function getAllElegibleDiscounts(): array
    {
        $calculatedDiscounts = [];

        $activeDiscounts = [
            $this->calculateDiscountAbove3000(),
            $this->calculateDiscountTake3Pay2(),
            $this->calculateDiscountSameCategory(),
        ];

        foreach ($activeDiscounts as $activeDiscount) {
            array_push($calculatedDiscounts, $activeDiscount);
        }

        return array_filter($calculatedDiscounts, function ($item) {
            return $item['elegible'];
        });
    }

    /**
     * @param array{array{total: Money, strategy: string}} $discounts
     *
     * @return array{total: Money, strategy: string}
     */
    private function getHighestDiscount(array $discounts): array
    {
        return array_reduce($discounts, function ($carry, $item) {
            return @$carry['total'] > $item['total'] ? $carry : $item;
        });
    }

    /** @return array{total: Money, strategy: string, elegible: bool} */
    private function calculateDiscountAbove3000(): array
    {
        $discount = [
            'strategy' => 'above-3000',
            'total' => Money::BRL(0),
            'elegible' => false,
        ];

        if ($this->subtotal->greaterThanOrEqual(Money::BRL(300000))) {
            $discount['elegible'] = true;
            $discount['total'] = $this->subtotal->multiply(15)->divide(100);
        }

        return $discount;
    }

    /** @return array{total: Money, strategy: string, elegible: bool} */
    private function calculateDiscountTake3Pay2(): array
    {
        $discount = [
            'strategy' => 'take-3-pay-2',
            'total' => Money::BRL(0),
            'elegible' => false,
        ];

        /** @var array<string> $eligibleProducts */
        $eligibleProducts = config('api.promotional.products');

        $filteredProducts = array_filter($this->products, function ($item) use ($eligibleProducts) {
            return in_array(@$item['id'], $eligibleProducts)
                && $item['quantity'] >= 3;
        });

        if (!count($filteredProducts)) {
            return $discount;
        }

        $total = Money::BRL(0);
        $currency = new Currency('BRL');

        foreach ($filteredProducts as $product) {
            /** @var int $quantity */
            $quantity = $product['quantity'];

            /** @var string $price */
            $price = $product['unitPrice'];

            $freeProducts = intdiv($quantity, 3);
            $unitPrice = $this->moneyParser->parse($price, $currency);
            $total = $total->add($unitPrice->multiply($freeProducts));
        }

        $discount['total'] = $discount['total']->add($total);
        $discount['elegible'] = true;

        return $discount;
    }

    /** @return array{total: Money, strategy: string, elegible: bool} */
    private function calculateDiscountSameCategory(): array
    {
        $discount = [
            'strategy' => 'same-category',
            'total' => Money::BRL(0),
            'elegible' => false,
        ];

        $total = Money::BRL(0);
        $currency = new Currency('BRL');

        /** @var array<string> $eligibleCategories */
        $eligibleCategories = config('api.promotional.categories');

        foreach ($eligibleCategories as $eligibleCategory) {
            $products = [];

            $products = array_filter($this->products, function ($item) use ($eligibleCategory) {
                return @$item['categoryId'] === $eligibleCategory;
            });

            if (count($products) >= 2) {
                /** @var string $lowestPrice */
                $lowestPrice = min(array_column($products, 'unitPrice'));
                $lowestPrice = $this->moneyParser->parse($lowestPrice, $currency);
                $lowestPriceDiscount = $lowestPrice->multiply(40)->divide(100);
                $discount['total'] = $total->add($lowestPriceDiscount);
                $discount['elegible'] = true;
            }
        }

        return $discount;
    }
}
