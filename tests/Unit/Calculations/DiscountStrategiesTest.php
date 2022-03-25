<?php

namespace Calculations;

use App\Services\UsersServices;
use Exception;
use Tests\TestCase;

class DiscountStrategiesTest extends TestCase
{
    public function testShouldAboveThreeThousandDiscountBeApplicable()
    {
        //set
        $subtotalAmount = '3100.00';
        $minSubtotalAmount = '3000.00';
        $discountPercentage = 15;
        $response = [];

        //act
        if ((float) $subtotalAmount > (float) $minSubtotalAmount) {
            $discountAmount = (float) $subtotalAmount * ($discountPercentage / 100);
            $response = [
                'applicable' => true,
                'totalDiscount' => $discountAmount,
                'strategy' => 'above-3000',
            ];
        }

        //assert
        $expected = [
            'applicable' => true,
            'totalDiscount' => '465.00',
            'strategy' => 'above-3000',
        ];

        $this->assertEquals($expected, $response);
    }

    /**
     * @throws Exception
     */
    public function testShouldNewUserDiscountBeApplicable()
    {
        //Set
        $userEmail = 'bringMeTheHorizon@gmail.com';
        $isNewUser = (new UsersServices())->isNewUser($userEmail);
        $subtotal = '52.22';
        $minSubtotal = '50.00';
        $discount = '25.00';
        $response = [];

        //Act
        if ($isNewUser && (float) $subtotal > (float) $minSubtotal) {
            $response = [
                'applicable' => true,
                'totalDiscount' => '25.00',
                'strategy' => 'new-user',
            ];
        }

        //Assert
        $expected = [
            'applicable' => true,
            'totalDiscount' => '25.00',
            'strategy' => 'new-user',
        ];

        $this->assertEquals($expected, $response);
    }

    public function testShouldTakeThreePayTwoDiscountBeApplicable()
    {
        //Set
        /** @var array<int, string> $promotionalProducts */
        $promotionalProducts = config('api.promotional.products');
        $response = [];
        $totalDiscount = 0;
        $products = [
            [
                'id' => 'b8be61e7-c170-5623-9ff7-00b2d83f91a5',
                'categoryId' => '3bef5406-6a50-5780-9022-ac10bd80cd99',
                'quantity' => 9,
                'unitPrice' => '1200.99',
            ],
            [
                'id' => 'b8be61e7-c170-5623-9ff7-00b2d83f91a2',
                'categoryId' => '3bef5406-6a50-5780-9022-ac10bd80cd99',
                'quantity' => 3,
                'unitPrice' => '1200.99',
            ],
        ];

        //Act
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
            $response = [
                'applicable' => true,
                'totalDiscount' => $totalDiscount,
                'strategy' => 'take-3-pay-2',
            ];
        }


        //Assert
        $expected = [
            'applicable' => true,
            'totalDiscount' => '3602.97',
            'strategy' => 'take-3-pay-2',
        ];

        $this->assertEquals($expected, $response);
    }

    /**
     * @throws Exception
     */
    public function testShouldEmployeeDiscountBeApplicable()
    {
        //Set
        $userEmail = 'boitata@boitata.com';
        $isUserEmployee = (new UsersServices())->isUserEmployee($userEmail);
        $subtotalAmount = '250.00';
        $discountPercentage = 20;
        $response = [];

        //Act
        if ($isUserEmployee) {
            $discountAmount = (float) $subtotalAmount * ($discountPercentage / 100);
            $response = [
                'applicable' => true,
                'totalDiscount' => $discountAmount,
                'strategy' => 'employee',
            ];
        }

        //Assert
        $expected = [
            'applicable' => true,
            'totalDiscount' => '50.00',
            'strategy' => 'employee',
        ];

        $this->assertEquals($expected, $response);
    }

    public function testShouldSameCategoryDiscountBeApplicable()
    {
        //Set
        /** @var array<string> $promotionalCategories */
        $promotionalCategories = config('api.promotional.categories');
        $totalDiscount = 0;
        $discountPercentage = 40;
        $response = [];
        $products = [
            [
                'id' => 'b8be61e7-c170-5623-9ff7-00b2d83f91a5',
                'categoryId' => '563877aa-7121-5de4-9d53-10c0ea68ca37',
                'quantity' => 9,
                'unitPrice' => '1200.99',
            ],
            [
                'id' => 'b8be61e7-c170-5623-9ff7-00b2d83f91a2',
                'categoryId' => '563877aa-7121-5de4-9d53-10c0ea68ca37',
                'quantity' => 3,
                'unitPrice' => '1200.99',
            ],
            [
                'id' => 'b8be61e7-c170-5623-9ff7-00b2d83f91a1',
                'categoryId' => '563877aa-7121-5de4-9d53-10c0ea68ca37',
                'quantity' => 3,
                'unitPrice' => '547.99',
            ],
        ];

        //Act
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
            $response = [
                'applicable' => true,
                'totalDiscount' => '219.196',
                'strategy' => 'same-category',
            ];
        }

        //Assert
        $expected = [
            'applicable' => true,
            'totalDiscount' => '219.196',
            'strategy' => 'same-category',
        ];

        $this->assertEquals($expected, $response);
    }
}
