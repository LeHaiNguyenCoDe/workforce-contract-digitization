<?php

namespace App\Repositories\Contracts;

use App\Models\Shipment;

interface ShipmentRepositoryInterface
{
    /**
     * Find shipment by order ID
     *
     * @param  int  $orderId
     * @return Shipment|null
     */
    public function findByOrderId(int $orderId): ?Shipment;

    /**
     * Create or update shipment
     *
     * @param  array  $attributes
     * @param  array  $values
     * @return Shipment
     */
    public function updateOrCreate(array $attributes, array $values = []): Shipment;

    /**
     * Update shipment
     *
     * @param  Shipment  $shipment
     * @param  array  $data
     * @return Shipment
     */
    public function update(Shipment $shipment, array $data): Shipment;
}
