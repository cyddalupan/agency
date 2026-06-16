<?php

namespace Tests\Unit;

use App\Models\StatusCode;
use App\Services\StatusTransitionService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class StatusTransitionTest extends TestCase
{
    use RefreshDatabase;

    private StatusTransitionService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(\Database\Seeders\StatusCodesSeeder::class);
        $this->seed(\Database\Seeders\StatusTransitionSeeder::class);
        $this->service = app(StatusTransitionService::class);
    }

    #[Test]
    public function can_transition_from_pending_to_for_interview(): void
    {
        $this->assertTrue($this->service->canTransition(0, 1));
    }

    #[Test]
    public function cannot_skip_steps(): void
    {
        $this->assertFalse($this->service->canTransition(0, 6));
    }

    #[Test]
    public function cannot_transition_to_invalid_status(): void
    {
        // Pending should only go to For Interview (1) or terminal states (35-38)
        $this->assertFalse($this->service->canTransition(0, 9));
    }

    #[Test]
    public function deployed_can_transition_to_parallel_tracks(): void
    {
        $this->assertTrue($this->service->canTransition(8, 9));   // For PDOS
        $this->assertTrue($this->service->canTransition(8, 11));  // POEA Processing
        $this->assertTrue($this->service->canTransition(8, 13));  // For OEC
        $this->assertTrue($this->service->canTransition(8, 15));  // For OWWA
        $this->assertTrue($this->service->canTransition(8, 17));  // For Medical
        $this->assertTrue($this->service->canTransition(8, 19));  // For Visa
        $this->assertTrue($this->service->canTransition(8, 21));  // For Ticket
        $this->assertTrue($this->service->canTransition(8, 29));  // For Contract
        $this->assertTrue($this->service->canTransition(8, 39));  // For MOFA
        // Deep pipeline (Visa Stamping, Exit Clearance) requires intermediate steps first
        $this->assertFalse($this->service->canTransition(8, 41)); // No direct to For Visa Stamping
        $this->assertFalse($this->service->canTransition(8, 43)); // No direct to For Exit Clearance
    }

    #[Test]
    public function each_forward_track_completes_sequentially(): void
    {
        $tracks = [
            [0, 1, 2, 3, 4, 5, 6, 7, 8],   // Main pipeline
            [9, 10],                          // PDOS
            [11, 12],                         // POEA
            [13, 14],                         // OEC
            [15, 16],                         // OWWA
            [17, 18],                         // Medical
            [19, 20],                         // Visa
            [21, 22],                         // Ticket
            [23, 24, 25, 26, 27, 28],        // Second pipeline
            [29, 30],                         // Contract
            [31, 32],                         // Contract 2
            [33, 34],                         // Deployment 2
            [39, 40],                         // MOFA
            [41, 42],                         // Visa Stamping
            [43, 44],                         // Exit Clearance
        ];

        foreach ($tracks as $track) {
            for ($i = 0; $i < count($track) - 1; $i++) {
                $this->assertTrue(
                    $this->service->canTransition($track[$i], $track[$i + 1]),
                    "Track [" . implode(',', $track) . "] failed at {$track[$i]} → {$track[$i+1]}"
                );
            }
        }
    }

    #[Test]
    public function terminal_states_are_terminal(): void
    {
        $terminals = [35, 36, 37, 38]; // Repatriated, Blacklisted, Banned, Cancel

        foreach ($terminals as $terminal) {
            $this->assertFalse(
                $this->service->canTransition($terminal, 0),
                "Terminal state {$terminal} should not allow forward transition"
            );
        }
    }

    #[Test]
    public function any_active_status_can_transition_to_terminal(): void
    {
        $activeStatuses = [0, 1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14, 15, 16];
        $terminals = [35, 36, 37, 38];

        foreach ($activeStatuses as $from) {
            foreach ($terminals as $to) {
                $this->assertTrue(
                    $this->service->canTransition($from, $to),
                    "Active status {$from} should be able to transition to terminal {$to}"
                );
            }
        }
    }

    #[Test]
    public function returns_allowed_next_statuses(): void
    {
        $allowed = $this->service->allowedTransitions(0);

        $this->assertContains(1, $allowed); // For Interview
        $this->assertContains(35, $allowed); // Repatriated
        $this->assertContains(36, $allowed); // Blacklisted
        $this->assertContains(37, $allowed); // Banned
        $this->assertContains(38, $allowed); // Cancel
        $this->assertCount(5, $allowed);
    }

    #[Test]
    public function deployed_has_many_allowed_transitions(): void
    {
        $allowed = $this->service->allowedTransitions(8);

        $this->assertContains(9, $allowed);  // PDOS track
        $this->assertContains(11, $allowed); // POEA track
        $this->assertContains(17, $allowed); // Medical
        $this->assertContains(19, $allowed); // Visa
        $this->assertContains(21, $allowed); // Ticket
    }

    #[Test]
    public function returns_valid_transitions_list(): void
    {
        $transitions = $this->service->getAllTransitions();

        $this->assertIsArray($transitions);
        $this->assertArrayHasKey(0, $transitions);
        $this->assertContains(1, $transitions[0]);
    }
}
