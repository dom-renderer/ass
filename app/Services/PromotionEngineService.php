<?php

namespace App\Services;

use App\Models\Promotion;
use App\Models\PromotionRedemption;
use Illuminate\Support\Collection;

class PromotionEngineService
{
    public function eligiblePromotions(array $context): Collection
    {
        $storeId = $context['store_id'] ?? null;
        $now = now();

        return Promotion::query()
            ->where('is_active', 1)
            ->where(function ($q) use ($now) {
                $q->whereNull('starts_at')->orWhere('starts_at', '<=', $now);
            })
            ->where(function ($q) use ($now) {
                $q->whereNull('ends_at')->orWhere('ends_at', '>=', $now);
            })
            ->where(function ($q) use ($storeId) {
                $q->where('is_global', 1);
                if ($storeId) {
                    $q->orWhereHas('stores', function ($sq) use ($storeId) {
                        $sq->where('stores.id', $storeId);
                    });
                }
            })
            ->orderByDesc('priority')
            ->get();
    }

    public function preview(array $context): array
    {
        $cartTotal = (float) ($context['cart_total'] ?? 0);
        $items = collect($context['items'] ?? []);
        $userId = $context['user_id'] ?? null;

        $best = ['promotion' => null, 'discount' => 0.0, 'final_total' => $cartTotal];
        foreach ($this->eligiblePromotions($context) as $promotion) {
            if (! $this->passesLimits($promotion, $userId)) {
                continue;
            }
            $discount = $this->computeDiscount($promotion, $cartTotal, $items, $context);
            if ($discount > $best['discount']) {
                $best = [
                    'promotion' => $promotion,
                    'discount' => round($discount, 2),
                    'final_total' => round(max(0, $cartTotal - $discount), 2),
                ];
            }
        }

        return $best;
    }

    protected function passesLimits(Promotion $promotion, $userId): bool
    {
        if ($promotion->total_usage_limit && $promotion->redemptions()->count() >= $promotion->total_usage_limit) {
            return false;
        }

        if ($promotion->per_user_limit && $userId) {
            $count = PromotionRedemption::where('promotion_id', $promotion->id)->where('user_id', $userId)->count();
            if ($count >= $promotion->per_user_limit) {
                return false;
            }
        }

        return true;
    }

    protected function computeDiscount(Promotion $promotion, float $cartTotal, Collection $items, array $context = []): float
    {
        if ($promotion->min_cart_amount && $cartTotal < (float) $promotion->min_cart_amount) {
            return 0;
        }

        switch ($promotion->type) {
            case 'cart_flat':
                return min($cartTotal, (float) ($promotion->discount_value ?? 0));
            case 'cart_percent':
                $d = $cartTotal * ((float) ($promotion->discount_value ?? 0) / 100);
                return $promotion->max_discount_amount ? min($d, (float) $promotion->max_discount_amount) : $d;
            case 'category_flat':
            case 'category_percent':
                $catIds = collect($promotion->applicable_category_ids ?? [])->map(fn($v) => (int) $v);
                $sub = $items->whereIn('category_id', $catIds)->sum(fn($i) => ((float)$i['price']) * ((int)$i['qty']));
                if ($sub <= 0) return 0;
                if ($promotion->type === 'category_flat') return min($sub, (float) ($promotion->discount_value ?? 0));
                $dc = $sub * ((float) ($promotion->discount_value ?? 0) / 100);
                return $promotion->max_discount_amount ? min($dc, (float) $promotion->max_discount_amount) : $dc;
            case 'product_flat':
            case 'product_percent':
                $productIds = collect($promotion->applicable_product_ids ?? [])->map(fn($v) => (int) $v);
                $sub = $items->whereIn('product_id', $productIds)->sum(fn($i) => ((float)$i['price']) * ((int)$i['qty']));
                if ($sub <= 0) return 0;
                if ($promotion->type === 'product_flat') return min($sub, (float) ($promotion->discount_value ?? 0));
                $dp = $sub * ((float) ($promotion->discount_value ?? 0) / 100);
                return $promotion->max_discount_amount ? min($dp, (float) $promotion->max_discount_amount) : $dp;
            case 'bxgy':
                $buyPid = (int) $promotion->buy_product_id;
                $getPid = (int) $promotion->get_product_id;
                $buyQty = max(1, (int) $promotion->buy_quantity);
                $getQty = max(1, (int) $promotion->get_quantity);
                $buyLine = $items->firstWhere('product_id', $buyPid);
                $getLine = $items->firstWhere('product_id', $getPid);
                if (! $buyLine || ! $getLine) return 0;
                $sets = intdiv((int) $buyLine['qty'], $buyQty);
                if ($sets < 1) return 0;
                $eligibleGetQty = min($sets * $getQty, (int) $getLine['qty']);
                $getPrice = (float) $getLine['price'];
                $pct = (float) ($promotion->get_discount_percent ?? 100);
                return $eligibleGetQty * $getPrice * ($pct / 100);
            case 'free_delivery':
                return (float) ($context['delivery_fee'] ?? 0);
            case 'first_order':
                return min($cartTotal, (float) ($promotion->discount_value ?? 0));
            default:
                return 0;
        }
    }
}
