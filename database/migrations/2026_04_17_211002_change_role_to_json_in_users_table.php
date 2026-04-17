<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $users = DB::table('users')->get();

        foreach ($users as $user) {
            if ($user->role) {
                DB::table('users')
                    ->where('id', $user->id)
                    ->update([
                        'role' => json_encode([$user->role]) 
                    ]);
            }
        }
    }

    public function down(): void
    {
       
    }
};