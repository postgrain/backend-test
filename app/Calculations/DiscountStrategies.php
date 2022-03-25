<?php

namespace App\Calculations;

use App\Services\UsersServices;
use Money\Money;
use Money\MoneyFormatter;
use Exception;

class DiscountStrategies
{
    private MoneyFormatter $moneyFormatter;
    private UsersServices $userServices;

    public function __construct(MoneyFormatter $moneyFormatter, UsersServices $usersServices)
    {
        $this->moneyFormatter = $moneyFormatter;
        $this->userServices = $usersServices;
    }

    /**
     * Consists in 25.00BRL discount for new users that has minimum 50.00BRL+ on products
     * in cart.
     *
     * @param string $userEmail
     * @param Money  $subtotal
     *
     * @return array{applicable: bool, totalDiscount: float, strategy: string} | false
     *
     * @throws Exception
     */
    public function newUser(string $userEmail, Money $subtotal): bool|array
    {
        $isNewUser = $this->userServices->isNewUser($userEmail);
        $subtotalAmount = $this->moneyFormatter->format($subtotal);
        $minSubtotalAmount = '50.00';
        $discountAmount = '25.00';

        if ($isNewUser && (float) $subtotalAmount > (float) $minSubtotalAmount) {
            return [
                'applicable' => true,
                'totalDiscount' => (float) $discountAmount,
                'strategy' => 'new-user',
            ];
        }

        return false;
    }

    /**
     * Consists in a 20% discount for users that are employees.
     *
     * @param string $userEmail
     * @param Money  $subtotal
     *
     * @return array{applicable: bool, totalDiscount: float, strategy: string} | false
     *
     * @throws Exception
     */
    public function employee(string $userEmail, Money $subtotal): array|false
    {
        $isUserEmployee = $this->userServices->isUserEmployee($userEmail);
        $subtotalAmount = $this->moneyFormatter->format($subtotal);
        $discountPercentage = 20;

        if ($isUserEmployee) {
            $discountAmount = (float) $subtotalAmount * ($discountPercentage / 100);

            return [
                'applicable' => true,
                'totalDiscount' => (float) $discountAmount,
                'strategy' => 'employee',
            ];
        }

        return false;
    }

    /**
     * Consists in a 15% discount on carts with 3000BRL+
     *
     * @param Money $subtotal
     *
     * @return array{applicable: bool, totalDiscount: float, strategy: string} | false
     */
    public function aboveThreeThousand(Money $subtotal): array|false
    {
        $subtotalAmount = $this->moneyFormatter->format($subtotal);
        $minSubtotalAmount = '3000.00';
        $discountPercentage = 15;

        if ((float) $subtotalAmount >= (float) $minSubtotalAmount) {
            $discountAmount = (float) $subtotalAmount * ($discountPercentage / 100);

            return [
                'applicable' => true,
                'totalDiscount' => (float) $discountAmount,
                'strategy' => 'above-3000',
            ];
        }

        return false;
    }


    /**
     * Consists in a discount for the same product whether user pick multiples
     * and the product are in promotional list
     * e.g: If the user pick two that are in promotion
     * the third will be free.
     *
     * @param array<int, array{id: string, categoryId: string, quantity: int, unitPrice: numeric-string}> $products
     *
     * @return array{applicable: bool, totalDiscount: float, strategy: string} | false
     */
    public function takeThreePayTwo(array $products): array|false
    {
        /** @var array<int, string> $promotionalProducts */
        $promotionalProducts = config('api.promotional.products');
        $totalDiscount = 0;

        foreach ($products as $product) {
            $productId = $product['id'];
            $unitPrice = $product['unitPrice'];
            $qntProducts = $product['quantity'];
            $qntFreeProducts = 0;

            foreach ($promotionalProducts as $promoProductId) {
                if ($productId == $promoProductId && $qntProducts >= 3) {
                    $qntFreeProducts = $qntProducts / 3;

                    break;
                }
            }

            $productDiscount = $unitPrice * (int) $qntFreeProducts;
            $totalDiscount += $productDiscount;
        }

        if ($totalDiscount) {
            return [
                'applicable' => true,
                'totalDiscount' => (float) $totalDiscount,
                'strategy' => 'take-3-pay-2',
            ];
        }

        return false;
    }

    /**
     * Consists in a 40% discount on a unit of the cheapest product
     * in the cart that has two products of the same category.
     *
     * @param array<int, array{id: string, categoryId: string, quantity: int, unitPrice: numeric-string}> $products
     *
     * @return array{applicable: bool, totalDiscount: float, strategy: string} | false
     */
    public function sameCategory(array $products): array | false
    {
        /** @var array<string> $promotionalCategories */
        $promotionalCategories = config('api.promotional.categories');
        $totalDiscount = 0;
        $discountPercentage = 40;

        /** @var string $promoCategoryId */
        foreach ($promotionalCategories as $promoCategoryId) {
            $productsInSameCategoryId = array_map(function ($product) use ($promoCategoryId) {
                $productCategoryId = $product['categoryId'];

                return $productCategoryId == $promoCategoryId ? $product : [];
            }, $products);

            $removeDuplicateProducts = array_column(
                array_reverse($productsInSameCategoryId),
                null,
                'id'
            );
            $removeDuplicateProducts = array_filter($removeDuplicateProducts);

            if (count($removeDuplicateProducts) >= 2) {
                $allPrices = array_column($productsInSameCategoryId, 'unitPrice');
                $minPrice = min($allPrices);
                $discountAmount = $minPrice * ($discountPercentage / 100);
                $totalDiscount += $discountAmount;
            }
        }

        if ($totalDiscount) {
            return [
                'applicable' => true,
                'totalDiscount' => (float) $totalDiscount,
                'strategy' => 'same-category',
            ];
        }

        return false;
    }
}
