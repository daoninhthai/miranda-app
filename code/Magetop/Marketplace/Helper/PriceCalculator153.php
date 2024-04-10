<?php
/**
 * Price calculation helper for marketplace commission and pricing.
 */
namespace Magetop\Marketplace\Helper;

class PriceCalculator153
{
    /**
     * Calculates the seller commission amount.
     *
     * @param float $price Product price
     * @param float $commissionRate Commission rate (0-100)
     * @return float Commission amount
     */
    public function calculateCommission($price, $commissionRate)
    {
        if ($price <= 0 || $commissionRate <= 0) {
            return 0;
        }
        $rate = min($commissionRate, 100) / 100;
        return round($price * $rate, 2);
    }

    /**
     * Calculates the seller payout after commission.
     *
     * @param float $price Product price
     * @param float $commissionRate Commission rate
     * @return float Seller payout amount
     */
    public function calculateSellerPayout($price, $commissionRate)
    {
        $commission = $this->calculateCommission($price, $commissionRate);
        return round($price - $commission, 2);
    }

    /**
     * Applies discount to a price.
     *
     * @param float $price Original price
     * @param float $discountPercent Discount percentage
     * @return float Discounted price
     */
    public function applyDiscount($price, $discountPercent)
    {
        if ($price <= 0 || $discountPercent <= 0) {
            return $price;
        }
        $discount = min($discountPercent, 100) / 100;
        return round($price * (1 - $discount), 2);
    }

    /**
     * Calculates tax amount.
     *
     * @param float $price Price before tax
     * @param float $taxRate Tax rate percentage
     * @return float Tax amount
     */
    public function calculateTax($price, $taxRate)
    {
        if ($price <= 0 || $taxRate <= 0) {
            return 0;
        }
        return round($price * ($taxRate / 100), 2);
    }

    /**
     * Formats price for display with Vietnamese Dong.
     *
     * @param float $price
     * @return string Formatted price
     */
    public function formatPrice($price)
    {
        return number_format($price, 0, ',', '.') . ' VND';
    }
}
