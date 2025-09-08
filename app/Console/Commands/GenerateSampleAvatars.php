<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Http;

class GenerateSampleAvatars extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'avatars:generate-samples';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate sample avatars for users';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Generating sample avatars...');

        $users = User::with('employeeInfo')->take(10)->get();

        foreach ($users as $user) {
            $this->generateAvatarForUser($user);
        }

        $this->info('Sample avatars generated successfully!');
    }

    private function generateAvatarForUser(User $user)
    {
        $employeeInfo = $user->employeeInfo;
        $name = '';

        if ($employeeInfo) {
            $name = $employeeInfo->FirstName . ' ' . $employeeInfo->LastName;
        } else {
            $name = $user->username;
        }

        // Generate avatar using DiceBear API (free avatar generation service)
        $avatarUrl = "https://api.dicebear.com/7.x/avataaars/png?seed=" . urlencode($name) . "&size=200";

        try {
            $response = Http::get($avatarUrl);

            if ($response->successful()) {
                $filename = $user->username . '.png';
                Storage::disk('public')->put('avatars/' . $filename, $response->body());
                $this->line("Generated avatar for: {$user->username}");
            }
        } catch (\Exception $e) {
            $this->error("Failed to generate avatar for {$user->username}: " . $e->getMessage());
        }
    }
}
