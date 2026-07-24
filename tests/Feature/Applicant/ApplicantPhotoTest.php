<?php

namespace Tests\Feature\Applicant;

use App\Models\Agency;
use App\Models\Applicant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class ApplicantPhotoTest extends TestCase
{
    use RefreshDatabase;

    private Agency $agency;
    private User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->agency = Agency::factory()->create();
        $this->user = User::factory()->create([
            'agency_id' => $this->agency->id,
            'user_type' => 'admin',
        ]);
    }

    #[Test]
    public function create_form_has_photo_upload_field(): void
    {
        $response = $this->actingAs($this->user)
            ->get(route('applicants.create'));

        $response->assertStatus(200);
        $response->assertSee('photo');
        $response->assertSee('2x2');
    }

    #[Test]
    public function edit_form_has_photo_upload_field(): void
    {
        $applicant = Applicant::factory()->create([
            'agency_id' => $this->agency->id,
        ]);

        $response = $this->actingAs($this->user)
            ->get(route('applicants.edit', $applicant));

        $response->assertStatus(200);
        $response->assertSee('photo');
        $response->assertSee('2x2');
    }

    #[Test]
    public function photo_can_be_uploaded_during_creation(): void
    {
        $file = UploadedFile::fake()->image('photo.jpg', 200, 200);

        $response = $this->actingAs($this->user)
            ->post(route('applicants.store'), [
                'first_name' => 'Photo',
                'last_name'  => 'Test',
                'photo'      => $file,
            ]);

        $response->assertSessionHasNoErrors();
        $this->assertDatabaseHas('applicants', [
            'first_name' => 'Photo',
            'last_name'  => 'Test',
        ]);

        $applicant = Applicant::where('first_name', 'Photo')->first();
        $this->assertNotNull($applicant->photo);
        $this->assertFileExists(storage_path('app/public/' . $applicant->photo));
    }

    #[Test]
    public function photo_can_be_uploaded_during_update(): void
    {
        $applicant = Applicant::factory()->create([
            'agency_id' => $this->agency->id,
        ]);

        $file = UploadedFile::fake()->image('new-photo.jpg', 200, 200);

        $response = $this->actingAs($this->user)
            ->put(route('applicants.update', $applicant), [
                'first_name'  => $applicant->first_name,
                'last_name'   => $applicant->last_name,
                'photo'       => $file,
            ]);

        $response->assertSessionHasNoErrors();
        $applicant->refresh();
        $this->assertNotNull($applicant->photo);
        $this->assertFileExists(storage_path('app/public/' . $applicant->photo));
    }

    #[Test]
    public function photo_is_required_to_be_an_image(): void
    {
        $file = UploadedFile::fake()->create('document.pdf', 100);

        $response = $this->actingAs($this->user)
            ->post(route('applicants.store'), [
                'first_name' => 'Bad',
                'last_name'  => 'Photo',
                'photo'      => $file,
            ]);

        $response->assertSessionHasErrors('photo');
    }

    #[Test]
    public function uppercase_jpg_extension_is_accepted(): void
    {
        $file = UploadedFile::fake()->image('photo.JPG', 200, 200);

        $response = $this->actingAs($this->user)
            ->post(route('applicants.store'), [
                'first_name' => 'Uppercase',
                'last_name'  => 'Photo',
                'photo'      => $file,
            ]);

        $response->assertSessionHasNoErrors();
    }

    #[Test]
    public function photo_is_resized_to_max_dimensions(): void
    {
        $file = UploadedFile::fake()->image('large.jpg', 4000, 4000);

        $response = $this->actingAs($this->user)
            ->post(route('applicants.store'), [
                'first_name'  => 'Resized',
                'last_name'   => 'Photo',
                'photo'       => $file,
            ]);

        $response->assertSessionHasNoErrors();
        $applicant = Applicant::where('first_name', 'Resized')->first();
        $this->assertNotNull($applicant->photo);

        // Verify the saved file is resized
        $savedPath = storage_path('app/public/' . $applicant->photo);
        [$width, $height] = getimagesize($savedPath);
        $this->assertLessThanOrEqual(600, $width);
        $this->assertLessThanOrEqual(600, $height);
    }

    #[Test]
    public function photo_is_shown_on_show_page(): void
    {
        // Create a dummy file path
        $path = 'applicant-photos/test-photo.jpg';
        $fullPath = storage_path('app/public/' . $path);
        @mkdir(dirname($fullPath), 0755, true);
        file_put_contents($fullPath, 'fake-image-content');

        $applicant = Applicant::factory()->create([
            'agency_id' => $this->agency->id,
            'photo'     => $path,
        ]);

        $response = $this->actingAs($this->user)
            ->get(route('applicants.show', $applicant));

        $response->assertStatus(200);
        $response->assertSee('storage');
    }

    #[Test]
    public function old_photo_is_deleted_when_replaced(): void
    {
        $oldPath = 'applicant-photos/old-photo.jpg';
        $fullOldPath = storage_path('app/public/' . $oldPath);
        @mkdir(dirname($fullOldPath), 0755, true);
        file_put_contents($fullOldPath, 'old-image-content');

        $applicant = Applicant::factory()->create([
            'agency_id' => $this->agency->id,
            'photo'     => $oldPath,
        ]);

        $newFile = UploadedFile::fake()->image('new-photo.jpg', 200, 200);

        $response = $this->actingAs($this->user)
            ->put(route('applicants.update', $applicant), [
                'first_name'  => $applicant->first_name,
                'last_name'   => $applicant->last_name,
                'photo'       => $newFile,
            ]);

        $response->assertSessionHasNoErrors();
        $this->assertFileDoesNotExist($fullOldPath);
    }
}
