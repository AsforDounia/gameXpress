<?php // Code within app\Helpers\Helper.php

namespace App\Helpers;

class Helper
{
    public static function calculateTotalHelper($cartItems)
    {
        $totalBeforeTax = 0;
        $totalTax = 0;
        $totalAfterTax = 0;
        $totalDiscount = 0;

        $tvaRate = 0.20;

        foreach ($cartItems as $cartItem) {
            $product = $cartItem->product;
            $productTotal = $product->price * $cartItem->quantity;
            $totalBeforeTax += $productTotal;
            $totalTax += $productTotal * $tvaRate;
            $discount = $product->remise ;
            $totalDiscount += $productTotal * ($discount / 100);
            $totalAfterTax += $productTotal + ($productTotal * $tvaRate) - ($productTotal * ($discount / 100));
        }


        return response()->json([
            'total_before_tax' =>$totalBeforeTax,
            'total_tax' =>$totalTax,
            'total_after_tax' =>$totalAfterTax,
            'total_discount' =>$totalDiscount,
            'total_final' =>$totalAfterTax - $totalDiscount
        ]);
    }
}
