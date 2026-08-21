<?php

namespace App\Services\Admin;

use App\Models\Merchant;

class MerchantService
{
    public function getAllMerchants()
    {
        return Merchant::orderBy('name')->get();
    }

    public function createMerchant(array $data)
    {
        return Merchant::create($data);
    }

    public function updateMerchant(Merchant $merchant, array $data)
    {
        return $merchant->update($data);
    }
}
