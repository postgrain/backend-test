<?php

namespace Tests\Unit\app\Classes;

use App\Http\Controllers\UsersController;
use Exception;
use Tests\TestCase;

class DiscountMethodsTest extends TestCase
{
    /**
     * Take three pay two
     *
     * @return void
     */
    public function testShouldTakeThreePayTwoDiscountBeApplicable(): void
    {
        //Set
        /** @var array<int, string> $promotionalProducts */
        $promotionalProducts = config('api.promotional.products');
        $totalDiscount = '0';
        $products = [
            [
                'id' => 'c4a760a8-dbcf-5254-a0d9-6a4474bd1b68',
                'categoryId' => 'c4a760a8-dbcf-5254-a0d9-6a4474bd1b62',
                'quantity' => 2,
                'unitPrice' => '1021.01',
            ],
            [
                'id' => 'b8be61e7-c170-5623-9ff7-00b2d83f91a5',
                'categoryId' => 'c4a760a8-dbcf-5254-a0d9-6a4474bd1b62',
                'quantity' => 2,
                'unitPrice' => '777.01',
            ],
            [
                'id' => 'b8be61e7-c170-5623-9ff7-00b2d83f91a5',
                'categoryId' => 'c4a760a8-dbcf-5254-a0d9-6a4474bd1b62',
                'quantity' => 9,
                'unitPrice' => '1999.99',
            ],
        ];
        $response = [
            'applicable' => false,
            'totalDiscount' => '00.00',
            'strategy' => 'take-3-pay-2',
        ];

        //Act
        foreach ($products as $product) {
            $productId = $product['id'];
            $qntProduct = $product['quantity'];
            $unitPrice = $product['unitPrice'];
            $qntFreeProducts = 0;

            foreach ($promotionalProducts as $promoProductId) {
                if ($productId === $promoProductId && $qntProduct >= 3) {
                    $qntFreeProducts = $qntProduct / 3;

                    break;
                }
            }

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

        //Assert
        $expected = [
            'applicable' => true,
            'totalDiscount' => '5999.97',
            'strategy' => 'take-3-pay-2',
        ];
        $this->assertIsString($response['totalDiscount']);
        $this->assertIsBool($response['applicable']);
        $this->assertIsString($response['strategy']);
        $this->assertEquals($expected, $response);
    }

    /**
     * Same category
     *
     * @return void
     */
    public function testShouldSameCategoryDiscountBeApplicable(): void
    {
        //Set
        /** @var array<int, string> $promotionalCategories */
        $promotionalCategories = config('api.promotional.categories');
        $totalDiscount = '0';
        $discountPercentage = 40;
        $response = [
            'applicable' => false,
            'totalDiscount' => '00.00',
            'strategy' => 'same-category',
        ];
        $products = [
            [
                'id' => 'b8be61e7-c170-5623-9ff7-00b2d83f91a51',
                'categoryId' => '563877aa-7121-5de4-9d53-10c0ea68ca37',
                'quantity' => 2,
                'unitPrice' => '458.21',
            ],
            [
                'id' => 'b8be61e7-c170-5623-9ff7-00b2d83f91a53',
                'categoryId' => '563877aa-7121-5de4-9d53-10c0ea68ca37',
                'quantity' => 2,
                'unitPrice' => '199.99',
            ],
            [
                'id' => 'b8be61e7-c170-5623-9ff7-00b2d83f91a52',
                'categoryId' => '563877aa-7121-5de4-9d53-10c0ea68ca37',
                'quantity' => 3,
                'unitPrice' => '1407.00',
            ],
        ];

        //Act
        foreach ($promotionalCategories as $promoCategoryId) {
            //Creates a new array only with  products that match with this
            // promotional category id
            $sameCategoryProducts = array_map(function ($product) use ($promoCategoryId) {
                $productCategoryId = $product['categoryId'];

                return $productCategoryId == $promoCategoryId ? $product : [];
            }, $products);

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
            $formatted = number_format(floor($totalDiscount * 100) / 100, 2);
            $response['applicable'] = true;
            $response['totalDiscount'] = $formatted;
        }

        //Assertions
        $expected = [
            'applicable' => true,
            'totalDiscount' => '79.99',
            'strategy' => 'same-category',
        ];
        $this->assertIsString($response['totalDiscount']);
        $this->assertIsBool($response['applicable']);
        $this->assertIsString($response['strategy']);
        $this->assertEquals($expected, $response);
    }


    /**
     * Above three thousand
     *
     * @return void
     */
    public function testShouldAboveThreeThousandDiscountBeApplicable(): void
    {
        //Set
        $subtotalAmount = '3001.98';
        $minimumAmount = '3000.00';
        $discountPercentage = 15;
        $response = [
            'applicable' => false,
            'totalDiscount' => '00.00',
            'strategy' => 'above-3000',
        ];

        //Act
        $discountAmount = (float) $subtotalAmount * ($discountPercentage / 100);
        $formatted = number_format(floor($discountAmount * 100) / 100, 2);
        $response['applicable'] = true;
        $response['totalDiscount'] = $formatted;

        //Assertions
        $expected = [
            'applicable' => true,
            'totalDiscount' => '450.29',
            'strategy' => 'above-3000',
        ];
        $this->assertIsString($response['totalDiscount']);
        $this->assertIsBool($response['applicable']);
        $this->assertIsString($response['strategy']);
        $this->assertEquals($expected, $response);
    }

    /**
     * Employee User
     *
     * @throws Exception
     */
    public function testShouldEmployeeDiscountBeApplicable(): void
    {
        //Set
        $userEmail = 'boitata@boitata.com';
        $userInfo = (new UsersController())->getExternalInformation($userEmail);
        $userInfoMsg = $userInfo['message'];
        $subtotalAmount = '1999.99';
        $discountPercentage = 20;
        $response = [
            'applicable' => false,
            'totalDiscount' => '0',
            'strategy' => 'employee',
        ];

        //Act
        if ('Success.' === $userInfoMsg) {
            /** @var array<string, bool> $userData */
            $userData = $userInfo['data'];
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

        //Assertions
        $expected = [
            'applicable' => true,
            'totalDiscount' => '399.99',
            'strategy' => 'employee',
        ];
        $this->assertIsString($response['totalDiscount']);
        $this->assertIsBool($response['applicable']);
        $this->assertIsString($response['strategy']);
        $this->assertEquals($expected, $response);
    }

    /**
     * New user
     *
     * @throws Exception
     */
    public function testShouldNewUserDiscountBeApplicable(): void
    {
        //Set
        $userEmail = 'ivson@ciclistasrecife.com.br';
        $userInfo = (new UsersController())->getExternalInformation($userEmail);
        $userInfoMsg = $userInfo['message'];
        $discountAmount = '25.00';
        $response = [
            'applicable' => false,
            'totalDiscount' => '00.00',
            'strategy' => 'new-user',
        ];

        //Act
        if ('Not Found.' === $userInfoMsg) {
            $response['applicable'] = true;
            $response['totalDiscount'] = $discountAmount;
        }

        //Assertions
        $this->assertIsString($response['totalDiscount']);
        $this->assertIsBool($response['applicable']);
        $this->assertIsString($response['strategy']);
        $expected = [
            'applicable' => true,
            'totalDiscount' => '25.00',
            'strategy' => 'new-user',
        ];
        $this->assertEquals($expected, $response);
    }

    /**
     * Bigger discount
     *
     * @return void
     */
    public function testShouldReturnTheBiggerDiscount(): void
    {
        //Set
        $response = [
            'applicable' => false,
            'totalDiscount' => '0',
            'strategy' => 'none',
        ];
        $allMethods = [
            [
                'applicable' => false,
                'totalDiscount' => '122.85',
                'strategy' => 'new-user',
            ],
            [
                'applicable' => true,
                'totalDiscount' => '1122.86',
                'strategy' => 'employee',
            ],
            [
                'applicable' => true,
                'totalDiscount' => '842.14',
                'strategy' => 'above-3000',
            ],
            [
                'applicable' => true,
                'totalDiscount' => '4451.01',
                'strategy' => 'take-3-pay-2',
            ],
            [
                'applicable' => true,
                'totalDiscount' => '1182.86',
                'strategy' => 'same-category',
            ],
        ];


        //Act
        $applicableMethods = array_map(function ($method) {
            return $method['applicable'] ? $method : [];
        }, $allMethods);

        $applicableMethods = array_filter($applicableMethods);

        if (count($applicableMethods) > 0) {
            $columns = array_column($applicableMethods, 'totalDiscount');
            array_multisort($columns, SORT_DESC, $applicableMethods);

            $biggerDiscount = $applicableMethods[0];
            $response = $biggerDiscount;

            //Removes unnecessary float last digit and makes it fixed in BRL.
            $totalDiscountAmount = $response['totalDiscount'];
            $splitDiscount = explode('.', $totalDiscountAmount);
            $qntLastDigits = strlen(end($splitDiscount));
            if ($qntLastDigits > 2) {
                $response['totalDiscount'] = substr($totalDiscountAmount, 0, -1);
            }
        }


        //Assertions
        $this->assertIsBool($response['applicable']);
        $this->assertIsString($response['totalDiscount']);
        $this->assertIsString($response['strategy']);
        $expected = [
            'applicable' => true,
            'totalDiscount' => '4451.01',
            'strategy' => 'take-3-pay-2',
        ];
        $this->assertEquals($expected, $response);
    }
}
