<?php
# START 7f4e2a9c8b1d / Admin User Seeder
# Hash: 7f4e2a9c8b1d
# Purpose: Oppretter admin bruker fra .env (ingen hardkodet passord)

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $email = env('ADMIN_EMAIL');
        $password = env('ADMIN_PASSWORD');
        
        if (empty($email) || empty($password)) {
            $this->command->error('ADMIN_EMAIL og ADMIN_PASSWORD må være satt i .env');
            return;
        }
        
        // Sjekk om bruker allerede finnes
        $existingUser = User::where('email', $email)->first();
        
        if ($existingUser) {
            $this->command->info("Admin bruker '{$email}' finnes allerede.");
            
            // Oppdater passord hvis det har endret seg
            if (!Hash::check($password, $existingUser->password)) {
                $existingUser->password = Hash::make($password);
                $existingUser->save();
                $this->command->info("Passord oppdatert for '{$email}'.");
            }
            
            return;
        }
        
        // Opprett ny admin bruker
        $user = User::create([
            'name' => 'Terje',
            'email' => $email,
            'password' => Hash::make($password),
            'email_verified_at' => now(),
        ]);
        
        $this->command->info("Admin bruker '{$email}' opprettet.");
    }
}
# SLUTT 7f4e2a9c8b1d
