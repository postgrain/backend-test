<?php

namespace App\Http\Controllers;

use Money\MoneyParser;
use Money\MoneyFormatter;
use App\Services\CartService;
use App\Services\UserService;
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

        $user = (new UserService())->getUser($email);

        $cartService = new CartService($products, $moneyParser, $moneyFormatter, $user);

        $subtotal = $cartService->getSubtotal();

        $discount = $cartService->getDiscount();

        $total = $cartService->getTotal();

        return new JsonResponse(
            [
                'message' => 'Success.',
                'data' => [
                    'subtotal' => $moneyFormatter->format($subtotal),
                    'discount' => $moneyFormatter->format($discount['total']),
                    'total' => $moneyFormatter->format($total),
                    'strategy' => $discount['strategy'],
                ],
            ]
        );
    }
}
