<?php

namespace App\Repositories;

use App\Models\Shipment;
use App\Repositories\Contracts\ShipmentRepositoryInterface;

class ShipmentRepository implements ShipmentRepositoryInterface
{
    /**
     * Find shipment by order ID
     *
     * @param  int  $orderId
     * @return Shipment|null
     */
    public function findByOrderId(int $orderId): ?Shipment
    {
        return Shipment::where('order_id', $orderId)->first();
    }

    /**
     * Create or update shipment
     *
     * @param  array  $attributes
     * @param  array  $values
     * @return Shipment
     */
    public function updateOrCreate(array $attributes, array $values = []): Shipment
    {
        return Shipment::updateOrCreate($attributes, $values);
    }

    /**
     * Update shipment
     *
     * @param  Shipment  $shipment
     * @param  array  $data
     * @return Shipment
     */
    public function update(Shipment $shipment, array $data): Shipment
    {
        $shipment->update($data);
        return $shipment->fresh();
    }
}
