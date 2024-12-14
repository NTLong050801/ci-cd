<?php

namespace App\Jobs;


use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

class KafkaMessageJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct(public $message)
    {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        try {
            User::create([
                'name' => $this->message['name'],
                'email' => $this->message['email'],
                'password' => Hash::make($this->message['password']),
            ]);
        } catch (\Exception $e) {
            Log::error("Error saving Kafka message: " . $e->getMessage());
        }
    }
}
