<?php

namespace Tests\Feature;

use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class InfrastructureSetupTest extends TestCase
{
    #[Test]
    public function git_remote_origin_is_configured(): void
    {
        exec('git remote get-url origin 2>&1', $output, $exitCode);
        $this->assertEquals(0, $exitCode, 'No Git remote origin configured.');
        $this->assertNotEmpty($output, 'Git remote origin URL is empty.');
        $this->assertStringContainsString('github.com', $output[0],
            'Remote origin should point to GitHub.'
        );
    }

    #[Test]
    public function ci_cd_workflow_file_exists(): void
    {
        $this->assertDirectoryExists(base_path('.github/workflows'),
            'CI/CD workflow directory does not exist.'
        );

        $workflowFiles = glob(base_path('.github/workflows/*.yml'));
        $this->assertNotEmpty($workflowFiles,
            'No CI/CD workflow YAML files found in .github/workflows/.'
        );
    }

    #[Test]
    public function deployment_script_exists(): void
    {
        $deployScripts = glob(base_path('deploy*'));
        $this->assertNotEmpty($deployScripts,
            'No deployment script found at project root.'
        );
    }
}
