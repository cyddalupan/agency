<?php

namespace App\Events;

use App\Models\Applicant;
use App\Models\User;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ApplicantStatusChanged
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * Create a new event instance.
     */
    public function __construct(
        public Applicant $applicant,
        public int $fromStatus,
        public int $toStatus,
        public User $updatedBy,
    ) {}

    /**
     * Get the data that should be broadcast.
     *
     * @return array<string, mixed>
     */
    public function broadcastWith(): array
    {
        return [];
    }
}
