<?php

namespace App\Http\Controllers;

use App\Http\Requests\CartDiscountRequest;
use App\Services\CartService;
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

        /** @var array<int, array<string, numeric-string>> $products */
        $products = $request->get('products');

        $currency = new Currency('BRL');

        $cartService = new CartService($products, $currency, $moneyParser, $moneyFormatter);

        return new JsonResponse(
            [
                'message' => 'Success.',
                'data' => [
                    'subtotal' => $moneyFormatter->format($cartService->getSubtotal()),
                    'discount' => $moneyFormatter->format($cartService->getDiscount()),
                    'total' => $moneyFormatter->format($cartService->getTotal()),
                    'strategy' => 'none',
                ],
            ]
        );
    }
}
