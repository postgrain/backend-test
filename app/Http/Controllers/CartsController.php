<?php

namespace App\Http\Controllers;

use App\Http\Requests\CartDiscountRequest;
use Illuminate\Http\JsonResponse;
use Money\Currency;
use Money\Money;
use Money\MoneyFormatter;
use Money\MoneyParser;

class CartsController extends Controller
{
    public function calculateDiscount(
        CartDiscountRequest $request,
        MoneyParser $moneyParser,
        MoneyFormatter $moneyFormatter
    ): JsonResponse {
        // Your logic goes here, use the code below just as a guidance.
        // You can do whatever you want with this code, even delete it.
        // Think about responsibilities, testing and code clarity.

        $subtotal = Money::BRL(0);

        /** @var array<int, array<string, numeric-string>> $products */
        $products = $request->get('products');

        foreach ($products as $product) {
            $currency = new Currency('BRL');
            $unitPrice = $moneyParser->parse($product['unitPrice'], $currency);
            $amount = $unitPrice->multiply($product['quantity']);
            $subtotal = $subtotal->add($amount);
        }

        $discount = Money::BRL(0);

        $total = $subtotal->subtract($discount);

        return new JsonResponse(
            [
                'message' => 'Success.',
                'data' => [
                    'subtotal' => $moneyFormatter->format($subtotal),
                    'discount' => $moneyFormatter->format($discount),
                    'total' => $moneyFormatter->format($total),
                    'strategy' => 'none',
                ],
            ]
        );
    }
}
