<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;

class AuditService
{
    public static function log(string , string  = null, array  = [], string  = \'info\'): void
    {
        DB::table(\'audits\')->insert([
            \'user_id\' => auth()->id() ?? null,
            \'action\' => ,
            \'resource\' => ,
            \'payload\' => json_encode(),
            \'severity\' => ,
            \'created_at\' => now(),
            \'updated_at\' => now(),
        ]);
    }
}
