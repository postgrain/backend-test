<?php

namespace App\Http\Controllers;

use App\Http\Requests\CartDiscountRequest;
use Illuminate\Http\JsonResponse;
use Money\Currency;
use Money\Money;
use Money\MoneyFormatter;
use Money\MoneyParser;
use App\Services\Cart\DiscountCartService;

class CartsController extends Controller
{
    public function calculateDiscount(
        CartDiscountRequest $request,
        MoneyParser $moneyParser,
        MoneyFormatter $moneyFormatter,
        DiscountCartService $discountCartService
    ): JsonResponse {
        $subtotal = Money::BRL(0);

        /** @var array<int, array<string, numeric-string>> $products */
        $products = $request->get('products');

        foreach ($products as $product) {
            $currency = new Currency('BRL');
            $unitPrice = $moneyParser->parse($product['unitPrice'], $currency);
            $amount = $unitPrice->multiply($product['quantity']);
            $subtotal = $subtotal->add($amount);
        }
        /** @var array<array<string, array<string, numeric-string>>> $requestValidated */
        $requestValidated = $request->validated();
        $discount = $discountCartService($requestValidated, $subtotal);
        $discountValue = floor((float) $discount['value']) . substr(
            str_replace((string) (floor((float) $discount['value'])), '', $discount['value']),
            0,
            2 + 1
        );
        $total = ($subtotal->getAmount() - floatval($discountValue) * 100) / 100;

        return new JsonResponse(
            [
                'message' => 'Success.',
                'data' => [
                    'subtotal' => $moneyFormatter->format($subtotal),
                    'discount' => floatval($discountValue),
                    'total' => floatval($total),
                    'strategy' => $discount['name'],
                ],
            ]
        );
    }
}
