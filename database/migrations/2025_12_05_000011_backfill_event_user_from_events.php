<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('event_user')) {
            return;
        }

        $now = Carbon::now();

        $events = DB::table('events')->select('id', 'user_id')->whereNotNull('user_id')->get();

        foreach ($events as $event) {
            // insert only if not exists
            $exists = DB::table('event_user')
                ->where('event_id', $event->id)
                ->where('user_id', $event->user_id)
                ->exists();

            if (!$exists) {
                DB::table('event_user')->insert([
                    'event_id' => $event->id,
                    'user_id' => $event->user_id,
                    'role' => 'owner',
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // no-op: keep pivot entries (or implement removal if desired)
    }
};

