<?php

namespace App\Classes;

use App\Http\Controllers\UsersController;
use Exception;
use Money\Money;
use Money\MoneyFormatter;

class DiscountMethods
{
    private MoneyFormatter $moneyFormatter;
    private Money $subtotal;

    /**
     * @var array{message: string, data: array<string, bool|string>} $userInfo
     */
    private array $userInfo;

    /**
     * @var array<int, array{id: int, categoryId: string, quantity: int, unitPrice: numeric-string}> $products
     */
    private array $products;

    /**
     * @param string                                                                                   $userEmail
     * @param array<int, array{id: int, categoryId: string, quantity: int, unitPrice: numeric-string}> $products
     * @param Money                                                                                    $subtotal
     * @param MoneyFormatter                                                                           $moneyFormatter
     */
    public function __construct(
        string $userEmail,
        array $products,
        Money $subtotal,
        MoneyFormatter $moneyFormatter
    ) {
        try {
            $this->userInfo = (new UsersController())->getExternalInformation($userEmail);
        } catch (Exception) {
            $this->userInfo = [
                'message' => 'Internal server error.',
                'data' => [
                    'email' => $userEmail,
                    'isEmployee' => false,
                ],
            ];
        }
        $this->subtotal = $subtotal;
        $this->products = $products;
        $this->moneyFormatter = $moneyFormatter;
    }

    /**
     * Get the bigger discount from all applicable discounts
     *
     * @return array{applicable: bool, totalDiscount: string, strategy: string}
     */
    public function bigger(): array
    {
        $response = [
            'applicable' => false,
            'totalDiscount' => '0',
            'strategy' => 'none',
        ];

        //Results from all available discount methods
        $allMethods = [
            $this->newUser(),
            $this->employee(),
            $this->aboveThreeThousand(),
            $this->takeThreePayTwo(),
            $this->sameCategory(),
        ];

        //Create a new associative array with only applicable methods
        $applicableMethods = array_map(function ($method) {
            return $method['applicable'] ? $method : [];
        }, $allMethods);

        //Remove empty keys
        $applicableMethods = array_filter($applicableMethods);

        if (count($applicableMethods) > 0) {
            //Order by total discount
            $columns = array_column($applicableMethods, 'totalDiscount');
            array_multisort($columns, SORT_DESC, $applicableMethods);

            //Select the bigger applicable discount
            $biggerDiscount = $applicableMethods[0];
            $response = $biggerDiscount;

            //Removes unnecessary float last digit and makes it fixed in BRL.
            $totalDiscountAmount = $response['totalDiscount'];
            $split = explode('.', $totalDiscountAmount);
            $qntLastDigits = strlen(end($split));
            if ($qntLastDigits > 2) {
                $response['totalDiscount'] = substr($totalDiscountAmount, 0, -1);
            }
        }

        return $response;
    }

    /**
     * Consists in 25.00BRL discount for new users that has minimum 50.00BRL+ on products
     * in cart.
     *
     * @return array{applicable: bool, totalDiscount: string, strategy: string}
     */
    private function newUser(): array
    {
        $userInfoMsg = $this->userInfo['message'];
        $subtotalAmount = $this->moneyFormatter->format($this->subtotal);
        $minSubtotalAmount = '50.00';
        $discountAmount = '25.00';
        $response = [
            'applicable' => false,
            'totalDiscount' => '00.00',
            'strategy' => 'new-user',
        ];

        if ('Not Found.' === $userInfoMsg && $subtotalAmount > $minSubtotalAmount) {
            $response['applicable'] = true;
            $response['totalDiscount'] = $discountAmount;
        }

        return $response;
    }

    /**
     * Consists in a 20% discount for users that are employees.
     *
     * @return array{applicable: bool, totalDiscount: string, strategy: string}
     */
    private function employee(): array
    {
        $userInfoMsg = $this->userInfo['message'];
        $subtotalAmount = $this->moneyFormatter->format($this->subtotal);
        $discountPercentage = 20;
        $response = [
            'applicable' => false,
            'totalDiscount' => '00.00',
            'strategy' => 'employee',
        ];

        //Whether a user is an Employee, apply 20 percent discount in subtotal.
        if ('Success.' === $userInfoMsg) {
            /** @var array<string, bool> $userData */
            $userData = $this->userInfo['data'];
            $isUserEmployee = $userData['isEmployee'];
            if ($isUserEmployee) {
                $totalDiscount = (float) $subtotalAmount * ($discountPercentage / 100);
                $formatted = number_format(
                    floor($totalDiscount * 100) / 100,
                    2,
                    '.',
                    ''
                );
                $response['applicable'] = true;
                $response['totalDiscount'] = $formatted;
            }
        }

        return $response;
    }

    /**
     * Consists in a 15% discount on carts with 3000BRL+
     *
     * @return array{applicable: bool, totalDiscount: string, strategy: string}
     */
    private function aboveThreeThousand(): array
    {
        $subtotalAmount = $this->moneyFormatter->format($this->subtotal);
        $minimumAmount = '3000.00';
        $discountPercentage = 15;
        $response = [
            'applicable' => false,
            'totalDiscount' => '00.00',
            'strategy' => 'above-3000',
        ];

        //Whether cart has 3000BRL+ on items then get discount amount
        // according to the discount percentage.
        if ($subtotalAmount >= $minimumAmount) {
            $discountAmount = (float) $subtotalAmount * ($discountPercentage / 100);
            $formatted = number_format(
                floor($discountAmount * 100) / 100,
                2,
                '.',
                ''
            );
            $response['applicable'] = true;
            $response['totalDiscount'] = $formatted;
        }

        return $response;
    }

    /**
     * Consists in a discount for the same product whether user pick multiples
     * and the product are in promotional list
     * e.g: If the user pick two that are in promotion
     * the third will be free.
     *
     * @return array{applicable: bool, totalDiscount: string, strategy: string}
     */
    private function takeThreePayTwo(): array
    {
        /** @var array<int, string> $promotionalProducts */
        $promotionalProducts = config('api.promotional.products');
        $totalDiscount = '0';
        $response = [
            'applicable' => false,
            'totalDiscount' => '00.00',
            'strategy' => 'take-3-pay-2',
        ];

        foreach ($this->products as $product) {
            $productId = $product['id'];
            $qntProduct = $product['quantity'];
            $unitPrice = $product['unitPrice'];
            $qntFreeProducts = 0;

            //Determine whether this product is promotional and
            // the user pick 3+ units...
            foreach ($promotionalProducts as $promoProductId) {
                if ($productId == $promoProductId && $qntProduct >= 3) {
                    $qntFreeProducts = $qntProduct / 3;

                    break;
                }
            }

            //Multiply qnt free products x the unit price
            $productDiscount = $unitPrice * (int) $qntFreeProducts;
            $totalDiscount += $productDiscount;
        }

        if ($totalDiscount) {
            $formatted = number_format(
                floor($totalDiscount * 100) / 100,
                2,
                '.',
                ''
            );
            $response['applicable'] = true;
            $response['totalDiscount'] = $formatted;
        }

        return $response;
    }

    /**
     * Consists in a 40% discount on a unit of the cheapest product
     * in the cart that has two products of the same category.
     *
     * @return array{applicable: bool, totalDiscount: string, strategy: string}
     */
    private function sameCategory(): array
    {
        /** @var array<string> $promotionalCategories */
        $promotionalCategories = config('api.promotional.categories');
        $totalDiscount = '0';
        $discountPercentage = 40;
        $response = [
            'applicable' => false,
            'totalDiscount' => '00.00',
            'strategy' => 'same-category',
        ];

        /** @var string $promoCategoryId */
        foreach ($promotionalCategories as $promoCategoryId) {
            //Creates a new array only with  products that match with this
            // promotional category id
            $sameCategoryProducts = array_map(function ($product) use ($promoCategoryId) {
                $productCategoryId = $product['categoryId'];

                return $productCategoryId == $promoCategoryId ? $product : [];
            }, $this->products);

            //Removes duplicated products and consider only different products -
            //in same category
            $differentProducts = array_column(
                array_reverse($sameCategoryProducts),
                null,
                'id'
            );
            //Remove empty keys
            $differentProducts = array_filter($differentProducts);

            //Whether exists two different products in same category, then -
            //pick the cheapest product and apply 40 percent of discount.
            if (count($differentProducts) >= 2) {
                $allPrices = array_column($sameCategoryProducts, 'unitPrice');
                $minPrice = min($allPrices);
                $discountAmount = $minPrice * ($discountPercentage / 100);
                $totalDiscount += $discountAmount;
            }
        }


        if ('0' !== $totalDiscount) {
            $formatted = number_format(
                floor($totalDiscount * 100) / 100,
                2,
                '.',
                ''
            );
            $response['applicable'] = true;
            $response['totalDiscount'] = $formatted;
        }

        return $response;
    }
}
