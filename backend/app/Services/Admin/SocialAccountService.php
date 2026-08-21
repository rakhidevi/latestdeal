<?php

namespace App\Services\Admin;

use App\Models\SocialAccount;

class SocialAccountService
{
    public function getAllAccounts()
    {
        return SocialAccount::all();
    }

    public function createAccount(array $data, int $userId)
    {
        $data['is_active'] = true;
        $data['user_id'] = $userId;

        return SocialAccount::create($data);
    }

    public function deleteAccount(SocialAccount $socialAccount)
    {
        return $socialAccount->delete();
    }

    public function toggleAccountStatus(SocialAccount $socialAccount)
    {
        return $socialAccount->update(['is_active' => !$socialAccount->is_active]);
    }
}
