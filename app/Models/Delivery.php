<?php
class DeliveryService {
    public static function calculateCost(float $subtotal): int {
        if ($subtotal >= 3000) return 0;
        if ($subtotal >= 2000) return ceil($subtotal * 0.10);
        if ($subtotal >= 1000) return ceil($subtotal * 0.25);
        return ceil($subtotal * 0.35);
    }
}
?>