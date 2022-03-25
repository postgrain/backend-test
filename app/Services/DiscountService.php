<?php

namespace App\Services;

use App\Calculations\DiscountStrategies;
use Exception;
use Money\Money;

class DiscountService
{
    public const DEFAULT_BIGGER_DISCOUNT = [
        'applicable' => false,
        'totalDiscount' => 0,
        'strategy' => 'none',
    ];

    private DiscountStrategies $strategies;

    public function __construct(DiscountStrategies $discountStrategies)
    {
        $this->strategies = $discountStrategies;
    }

    /**
     * Get all available discounts for a given cart.
     *
     * @param string                                                                                   $userEmail
     * @param Money                                                                                    $subtotal
     * @param array<int, array{id: int, categoryId: string, quantity: int, unitPrice: numeric-string}> $products
     *
     * @return array<int, array<string, bool>>
     *
     * @throws Exception
     */
    public function available(string $userEmail, array $products, Money $subtotal): array
    {
        $strategiesResults = [
            $this->strategies->sameCategory($products),
            $this->strategies->takeThreePayTwo($products),
            $this->strategies->aboveThreeThousand($subtotal),
            $this->strategies->newUser($userEmail, $subtotal),
            $this->strategies->employee($userEmail, $subtotal),
        ];

        return array_filter($strategiesResults);
    }

    /**
     * Determines the biggest discount on a given discount list.
     *
     * @param array $availableDiscounts
     *
     * @return array{applicable: bool, totalDiscount: float, strategy: string}
     */
    public function determineBigger(array $availableDiscounts): array
    {
        if (!empty($availableDiscounts)) {
            $biggerDiscount = [];
            foreach ($availableDiscounts as $discount) {
                if (!$biggerDiscount) {
                    $biggerDiscount = $discount;
                }

                if ($discount['totalDiscount'] > $biggerDiscount['totalDiscount']) {
                    $biggerDiscount = $discount;
                }
            }

            return $biggerDiscount;
        }

        return self::DEFAULT_BIGGER_DISCOUNT;
    }

    /**
     * Format a string discount value to float, acceptable in Money Package.
     *
     * @param string $value
     *
     * @return float
     */
    public function formatDiscountValue(string $value): float
    {
        $decimals = 2;
        $decimalSeparator = '.';
        $thousandsSeparator = '';

        return number_format(
            floor($value * 100) / 100,
            $decimals,
            $decimalSeparator,
            $thousandsSeparator
        );
    }
}
