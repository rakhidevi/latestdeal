<?php

namespace App\Services\Mail;

class RateLimiterService
{
    /**
     * Calculates the delay in seconds for the $nth email being queued,
     * based on the configured hourly rate limit.
     *
     * @param int $nthEmail The index of the email in the current batch/campaign.
     * @return int The delay in seconds to apply to the queue job.
     */
    public function getDelayForEmail(int $nthEmail): int
    {
        $ratePerHour = config('mail.rate_per_hour', 300);
        
        // If rate is set to 0 or negative, we assume no limits
        if ($ratePerHour <= 0) {
            return 0;
        }

        // e.g., 3600 / 300 = 12 seconds per email
        $secondsPerEmail = 3600 / $ratePerHour;
        
        return (int) round($nthEmail * $secondsPerEmail);
    }
}
