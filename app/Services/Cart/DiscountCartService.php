<?php

namespace App\Services\Cart;

use Money\Money;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

class DiscountCartService
{
    public const DICOUNTS = [
        'fullAmountCart' => [
            'minCartValue' => 3000, //integer
            'discount' => 15, //percentage
        ],
        'multipleItems' => 3, //integer
        'sameCategory' => 40, //percentage
        'employees' => 20, //percentage
        'newUsers' => [
            'minCartValue' => 50, //integer
            'discount' => 25, //integer
        ],
    ];

    /**
     * return discount (const) if cart has value upper to defined (const);
     *
     * @param Money $subtotal
     *
     * @return string
     */
    private function discountFullCartValue(Money $subtotal): string
    {
        $discount = $subtotal->greaterThanOrEqual(Money::BRL(self::DICOUNTS['fullAmountCart']['minCartValue'] * 100)) ?
        $subtotal->multiply(self::DICOUNTS['fullAmountCart']['discount'])->getAmount() / 10000 : '0';

        return (string) $discount;
    }

    /**
     * return discount for multiples equals items (ex: pay 3 get 1), if items are in promotion list (config).
     *
     * @param array<array<string, array<string, numeric-string>>> $cart
     *
     * @return string
     */
    private function discountMultipleItems($cart): string
    {
        $discount = 0;
        foreach ($cart['products'] as $product) {
            if ($this->checkIfItemIsPromotional($product['id']) && $product['quantity'] >= 3) {
                $freeItems = intdiv((int) $product['quantity'], 3);
                $discount += $product['unitPrice'] * $freeItems;
            }
        }

        return (string) $discount;
    }

    /**
     * return discount for different items there are on same category (config), min number products defined by const.
     *
     * @param array<array<string, array<string, numeric-string>>> $cart
     *
     * @return string
     */
    private function discountMultipleItemsSameCategory($cart): string
    {
        $discount = 0;
        $itemsByCategory = [];
        foreach ($cart['products'] as $product) {
            if ($this->checkItemCategory($product['categoryId'])) {
                $itemsByCategory[$product['categoryId']][] = $product['unitPrice'];
            }
        }
        foreach ($itemsByCategory as $item) {
            if (count($item) > 1) {
                $discount += min($item) * self::DICOUNTS['sameCategory'] / 100;
            }
        }

        return (string) $discount;
    }

    /**
     * return discount for employees (config)
     *
     * @param array<array<string, array<string, numeric-string>>> $cart
     * @param Money                                               $subtotal
     *
     * @return Money
     */
    private function discountEmployee($cart, Money $subtotal): Money
    {
        /** @var array<string> $cart['userEmail'] */
        return $this->checkIfUserEmployee($cart['userEmail']) ?
        $subtotal->multiply(self::DICOUNTS['employees'])->divide(10000) : Money::BRL(0);
    }

    /**
     * return discount for new users (interal API)
     *
     * @param array<array<string, array<string, numeric-string>>> $cart
     * @param Money                                               $subtotal
     *
     * @return Money
     */
    private function discountNewUser($cart, Money $subtotal): Money
    {
        /** @var array<string> $cart */
        $request = Request::create('/api/v1/user/' . $cart['userEmail'], 'GET');
        $response = Route::dispatch($request);

        return 404 === $response->getStatusCode() && $subtotal->greaterThan(
            Money::BRL(self::DICOUNTS['newUsers']['minCartValue'] * 100)
        )
         ? Money::BRL(self::DICOUNTS['newUsers']['discount']) : Money::BRL(0);
    }

    /**
     * @param string $productId
     *
     * @return bool
     */
    private function checkIfItemIsPromotional($productId): bool
    {
        /** @var array<array<string>> $promotional */
        $promotional = config('api.promotional');

        return in_array($productId, $promotional['products']);
    }

    /**
     * @param string $productCategory
     *
     * @return bool
     */
    private function checkItemCategory($productCategory): bool
    {
        /** @var array<array<string>> $promotional */
        $promotional = config('api.promotional');

        return in_array($productCategory, $promotional['categories']);
    }

    /**
     * @param string $userEmail
     *
     * @return bool
     */
    private function checkIfUserEmployee($userEmail): bool
    {
        /** @var array<array<string>> $employees */
        $employees = config('api.employees');
        if (!isset($employees[$userEmail])) {
            return false;
        }

        return (bool) $employees[$userEmail];
    }

    /**
     * Calculate and return biggest discount by rules
     *
     * @param array<array<string, array<string, numeric-string>>> $cart
     *
     * @param Money                                               $subtotal
     *
     * @return array{'name': string, "value": string}
     */
    public function __invoke($cart, Money $subtotal): array
    {
        $discounts = [
            'above-3000' => $this->discountFullCartValue($subtotal),
            'new-user' => $this->discountNewUser($cart, $subtotal)->getAmount(),
            'take-3-pay-2' => $this->discountMultipleItems($cart),
            'same-category' => $this->discountMultipleItemsSameCategory($cart),
            'employee' => $this->discountEmployee($cart, $subtotal)->getAmount(),
        ];
        $maxDiscount = array_keys($discounts, max($discounts))[0];

        return [
            'name' => '0' === $discounts[$maxDiscount] ? 'none' : $maxDiscount,
            'value' => $discounts[$maxDiscount],
        ];
    }
}
