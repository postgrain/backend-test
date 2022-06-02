<?php

namespace App\Http\Controllers;

use Money\MoneyParser;
use Money\MoneyFormatter;
use App\Services\CartService;
use Illuminate\Http\JsonResponse;
use App\Http\Requests\CartDiscountRequest;

class CartsController extends Controller
{
    public function calculateDiscount(
        CartDiscountRequest $request,
        MoneyParser $moneyParser,
        MoneyFormatter $moneyFormatter
    ): JsonResponse {
        /** @var array<int, array<string, numeric-string>> $products */
        $products = $request->get('products');

        /** @var string $email */
        $email = $request->get('userEmail');

        $cartService = new CartService($email, $products, $moneyParser, $moneyFormatter);

        return new JsonResponse(
            [
                'message' => 'Success.',
                'data' => [
                    'subtotal' => $moneyFormatter->format($cartService->getSubtotal()),
                    'discount' => $moneyFormatter->format($cartService->getDiscount()['total']),
                    'total' => $moneyFormatter->format($cartService->getTotal()),
                    'strategy' => $cartService->getDiscount()['strategy'],
                ],
            ]
        );
    }
}
