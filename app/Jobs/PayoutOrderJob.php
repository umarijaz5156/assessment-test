<?php

namespace App\Jobs;

use App\Models\Order;
use App\Services\ApiService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;

class PayoutOrderJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(
        public Order $order
    ) {}

    /**
     * Use the API service to send a payout of the correct amount.
     * Note: The order status must be paid if the payout is successful, or remain unpaid in the event of an exception.
     *
     * @return void
     */
    public function handle(ApiService $apiService)
    {
        DB::beginTransaction();
 
        try {
            if ($this->order->status === 'paid') {
                DB::rollBack();
                return;
            }

            $affiliate = $this->order->affiliate;
            if (!$affiliate) {
                DB::rollBack();
                return;
            }

            $payoutAmount = $this->order->subtotal_price * $affiliate->commission_rate;

            $apiService->sendPayout($affiliate, $payoutAmount);

            $this->order->update(['status' => 'paid']);

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack(); // Rollback on failure
            \Log::error("Payout failed for order {$this->order->id}: " . $e->getMessage());
        }
    }

}
