<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use App\Models\TrustedUser;
use App\Models\FinalWord;
use App\Models\LegacyAlbum;
use Carbon\Carbon;
use App\Traits\OneSignalTrait;

class SendDeathUserNotification extends Command
{
    use OneSignalTrait;

    protected $signature = 'send:user-notification';
    protected $description = 'Send notification for recently passed users (last 5 days)';

    public function handle()
    {
        $today = Carbon::today();
        $fiveDaysAgo = Carbon::today()->subDays(5);

        $deadUsers = User::where('is_dead', 1)
            ->whereBetween('passed_date', [$fiveDaysAgo, $today])
            ->whereNull('deleted_at')
            ->get();

        foreach ($deadUsers as $user) {

            /*
            |--------------------------------------------------------------------------
            | FINAL WORD NOTIFICATION
            |--------------------------------------------------------------------------
            */

            if (FinalWord::where('user_id', $user->id)->exists()) {

                $trustedUsers = TrustedUser::where('user_id', $user->id)
                    ->where('is_send_notify', 0)
                    ->where('status', 'accepted')
                    ->get();

                foreach ($trustedUsers as $trusted) {

                    $receiver = User::where('id', $trusted->trusted_user_id)
                        ->where('is_dead', 0)
                        ->whereNull('deleted_at')
                        ->first();

                    if ($receiver) {
                        $name = $user->first_name ?? 'User';
                        $this->notifyMessage(
                            $user,
                            $receiver->id,
                            $user->id,
                            "final_word_released",
                            null,
                            null,
                            "Final words released",
                            "$name has passed away. Final words video released."
                        );

                        // mark sent
                        $trusted->update(['is_send_notify' => 1]);
                    }
                }
            }

            /*
            |--------------------------------------------------------------------------
            | LEGACY ALBUM NOTIFICATION
            |--------------------------------------------------------------------------
            */

            $legacyAlbums = LegacyAlbum::where('user_id', $user->id)
                ->where('is_send_notify', 0)
                ->get();

            foreach ($legacyAlbums as $album) {

                $receiver = User::where('id', $album->shared_with_id)
                    ->where('is_dead', 0)
                    ->whereNull('deleted_at')
                    ->first();

                if ($receiver) {
                    $name = $user->first_name ?? 'User';

                    $this->notifyMessage(
                        $user,
                        $receiver->id,
                        $user->id,
                        "legacy_album_released",
                        null,
                        null,
                        "Legacy Album Released",
                        "$name has passed away. Legacy album released."
                    );

                    // mark sent
                    $album->update(['is_send_notify' => 1]);
                }
            }
        }

        return Command::SUCCESS;
    }
}