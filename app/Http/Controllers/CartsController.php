<?php

namespace App\Http\Controllers;

use App\Http\Requests\CartDiscountRequest;
use App\Services\DiscountService;
use Exception;
use Illuminate\Http\JsonResponse;
use Money\Currency;
use Money\Money;
use Money\MoneyFormatter;
use Money\MoneyParser;

class CartsController extends Controller
{
    private MoneyParser $moneyParser;
    private MoneyFormatter $moneyFormatter;
    private Currency $currency;
    private DiscountService $discountService;

    public function __construct(
        MoneyParser $moneyParser,
        MoneyFormatter $moneyFormatter,
        DiscountService $discountService
    ) {
        $this->moneyParser = $moneyParser;
        $this->moneyFormatter = $moneyFormatter;
        $this->discountService = $discountService;
        $this->currency = new Currency('BRL');
    }


    /**
     * @throws Exception
     */
    public function calculateDiscount(
        CartDiscountRequest $request,
    ): JsonResponse {
        /** @var array<int, array{id: string, categoryId: string, quantity: int, unitPrice: numeric-string}> $products */
        $products = $request->get('products');

        /** @var string $userEmail */
        $userEmail = $request->get('userEmail');

        $subtotal = $this->calculateSubtotal($products);

        $totalDiscount = Money::BRL(0);
        $availableDiscounts = $this->discountService->available($userEmail, $products, $subtotal);
        $biggerDiscount = $this->discountService->determineBigger($availableDiscounts);
        $formattedDiscount = $this->discountService->formatDiscountValue((float) $biggerDiscount['totalDiscount']);
        $discountAmount = $this->moneyParser->parse((string) $formattedDiscount, $this->currency);
        $totalDiscount = $totalDiscount->add($discountAmount);

        $total = $subtotal->subtract($totalDiscount);
        $strategy = $biggerDiscount['strategy'];

        return new JsonResponse(
            [
                'message' => 'Success.',
                'data' => [
                    'subtotal' => $this->moneyFormatter->format($subtotal),
                    'discount' => $this->moneyFormatter->format($totalDiscount),
                    'total' => $this->moneyFormatter->format($total),
                    'strategy' => $strategy,
                ],
            ]
        );
    }

    /**
     * Calculates the subtotal on the cart.
     *
     * @param array<int, array{id: string, categoryId: string, quantity: int, unitPrice: numeric-string}> $products
     *
     * @return Money
     */
    public function calculateSubtotal(array $products): Money
    {
        $subtotal = Money::BRL(0);
        foreach ($products as $product) {
            $unitPrice = $this->moneyParser->parse($product['unitPrice'], $this->currency);
            $amount = $unitPrice->multiply($product['quantity']);
            $subtotal = $subtotal->add($amount);
        }

        return $subtotal;
    }
}
