<?php

namespace App\Http\Controllers;

use App\Models\Merchant;
use App\Services\MerchantService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class MerchantController extends Controller
{
    public function __construct(
        MerchantService $merchantService
    ) {}
 
    /**
     * Useful order statistics for the merchant API.
     *
     * @param Request $request Will include a from and to date
     * @return JsonResponse Should be in the form {count: total number of orders in range, commission_owed: amount of unpaid commissions for orders with an affiliate, revenue: sum order subtotals}
     */
    public function orderStats(Request $request): JsonResponse
    {
        $from = Carbon::parse($request->input('from', now()->subMonth()));
        $to = Carbon::parse($request->input('to', now()));

        $merchant = auth()->user()->merchant; 

        $orders = $merchant->orders()
            ->whereBetween('created_at', [$from, $to]);

        $orderCount = $orders->count();
        $revenue = $orders->sum('subtotal_price');
        $commissionOwed = $orders->whereNotNull('affiliate_id')
            ->where('commission_paid', false)
            ->sum('commission_owed');

        return response()->json([
            'count' => $orderCount,
            'commission_owed' => $commissionOwed,
            'revenue' => $revenue,
        ]);
    }

}
