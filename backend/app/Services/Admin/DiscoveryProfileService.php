<?php

namespace App\Services\Admin;

use App\Models\DiscoveryProfile;

class DiscoveryProfileService
{
    public function getAllProfiles()
    {
        return DiscoveryProfile::with(['brand', 'category', 'merchant'])
            ->orderBy('created_at', 'desc')
            ->paginate(20);
    }

    public function createProfile(array $data)
    {
        $data['next_run_at'] = now(); // Run immediately after creation
        return DiscoveryProfile::create($data);
    }

    public function updateProfile(DiscoveryProfile $profile, array $data)
    {
        return $profile->update($data);
    }

    public function deleteProfile(DiscoveryProfile $profile)
    {
        return $profile->delete();
    }

    public function toggleStatus(DiscoveryProfile $profile)
    {
        $newStatus = $profile->status === 'active' ? 'paused' : 'active';
        return $profile->update(['status' => $newStatus]);
    }
}
