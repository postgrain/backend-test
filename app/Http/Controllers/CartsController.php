<?php

namespace App\Http\Controllers;

use App\Classes\DiscountMethods;
use App\Http\Requests\CartDiscountRequest;
use Illuminate\Http\JsonResponse;
use Money\Currency;
use Money\Money;
use Money\MoneyFormatter;
use Money\MoneyParser;

class CartsController extends Controller
{
    public Currency $currency;

    public function __construct()
    {
        $this->currency = new Currency('BRL');
    }


    public function calculateDiscount(
        CartDiscountRequest $request,
        MoneyParser $moneyParser,
        MoneyFormatter $moneyFormatter
    ): JsonResponse {
        /** @var array<int, array{id: int, categoryId: string, quantity: int, unitPrice: numeric-string}> $products */
        $products = $request->get('products');

        /** @var string $userEmail */
        $userEmail = $request->get('userEmail');

        //Calculates the subtotal
        $subtotal = Money::BRL(0);
        foreach ($products as $product) {
            $unitPrice = $moneyParser->parse($product['unitPrice'], $this->currency);
            $amount = $unitPrice->multiply($product['quantity']);
            $subtotal = $subtotal->add($amount);
        }

        //Calculates the discount
        $totalDiscount = Money::BRL(0);
        $discountMethods = new DiscountMethods($userEmail, $products, $subtotal, $moneyFormatter);
        $biggerDiscount = $discountMethods->bigger();
        $discountAmount = $moneyParser->parse($biggerDiscount['totalDiscount'], $this->currency);
        $totalDiscount = $totalDiscount->add($discountAmount);

        $total = $subtotal->subtract($totalDiscount);
        $strategy = $biggerDiscount['strategy'];

        return new JsonResponse(
            [
                'message' => 'Success.',
                'data' => [
                    'subtotal' => $moneyFormatter->format($subtotal),
                    'discount' => $moneyFormatter->format($totalDiscount),
                    'total' => $moneyFormatter->format($total),
                    'strategy' => $strategy,
                ],
            ]
        );
    }
}
