<?php

namespace Modules\Core\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class HashPlaintextPasswords extends Command
{
    protected $signature = 'auth:hash-plaintext-passwords';

    protected $description = 'Hash all plaintext passwords in the users table';

    public function handle(): int
    {
        $users = DB::table('users')->whereNotNull('password')->where('password', '!=', '')->get();

        $hashed = 0;
        $skipped = 0;

        foreach ($users as $user) {
            if (Hash::needsRehash($user->password)) {
                DB::table('users')
                    ->where('id', $user->id)
                    ->update([
                        'hash_password' => Hash::make($user->password),
                    ]);
                $hashed++;
            } else {
                DB::table('users')
                    ->where('id', $user->id)
                    ->update([
                        'hash_password' => $user->password,
                    ]);
                $skipped++;
            }
        }

        $this->info("Done. Hash copied to hash_password: {$hashed}, Already hashed: {$skipped}");

        return Command::SUCCESS;
    }
}
