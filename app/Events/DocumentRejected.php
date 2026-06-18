<?php

namespace App\Events;

use App\Models\Applicant;
use App\Models\User;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class DocumentRejected
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * Create a new event instance.
     */
    public function __construct(
        public Applicant $applicant,
        public string $documentType,
        public string $reason,
        public User $rejectedBy,
    ) {}
}
