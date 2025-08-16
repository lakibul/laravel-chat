<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Pusher\Pusher;

class TestPusher extends Command
{
    protected $signature = 'test:pusher';
    protected $description = 'Test Pusher connection';

    public function handle()
    {
        try {
            $this->info('Testing Pusher connection...');

            $pusher = new Pusher(
                config('broadcasting.connections.pusher.key'),
                config('broadcasting.connections.pusher.secret'),
                config('broadcasting.connections.pusher.app_id'),
                config('broadcasting.connections.pusher.options')
            );

            $result = $pusher->trigger('test-channel', 'test-event', [
                'message' => 'Hello World'
            ]);

            $this->info('✓ Pusher connection successful!');
            $this->info('Response: ' . json_encode($result));

        } catch (\Exception $e) {
            $this->error('✗ Pusher connection failed:');
            $this->error($e->getMessage());
        }
    }
}
