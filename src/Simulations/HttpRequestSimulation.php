<?php

namespace Suzunone\Hibana\Simulations;

use Illuminate\Contracts\Console\Kernel;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Testing\Concerns\InteractsWithAuthentication;
use Illuminate\Foundation\Testing\Concerns\InteractsWithConsole;
use Illuminate\Foundation\Testing\Concerns\InteractsWithContainer;
use Illuminate\Foundation\Testing\Concerns\InteractsWithDatabase;
use Illuminate\Foundation\Testing\Concerns\InteractsWithDeprecationHandling;
use Illuminate\Foundation\Testing\Concerns\InteractsWithExceptionHandling;
use Illuminate\Foundation\Testing\Concerns\InteractsWithSession;
use Illuminate\Foundation\Testing\Concerns\InteractsWithTestCaseLifecycle;
use Illuminate\Foundation\Testing\Concerns\InteractsWithTime;
use Illuminate\Foundation\Testing\Concerns\InteractsWithViews;
use Illuminate\Foundation\Testing\Concerns\MakesHttpRequests;

class HttpRequestSimulation
{
    use InteractsWithContainer,
        MakesHttpRequests,
        InteractsWithAuthentication,
        InteractsWithConsole,
        InteractsWithDatabase,
        InteractsWithDeprecationHandling,
        InteractsWithExceptionHandling,
        InteractsWithSession,
        InteractsWithTime,
        InteractsWithTestCaseLifecycle,
        InteractsWithViews;


    /**
     * Body of the execution result
     * @param $uri
     * @param array $headers
     * @return string
     */
    public function getBody($uri, array $headers = []): string
    {
        $this->setUp();

        $response = $this->get($uri, $headers);

        return $response->baseResponse->content();
    }

    /**
     * Creates the application.
     *
     * @return \Illuminate\Foundation\Application
     */
    public function createApplication(): Application
    {
        $app = require Application::inferBasePath() . '/bootstrap/app.php';

        $app->make(Kernel::class)->bootstrap();

        return $app;
    }

    /**
     * Setup the test environment.
     *
     * @return void
     * @noinspection PhpInternalEntityUsedInspection
     * @noinspection UnknownInspectionInspection
     */
    public function setUp(): void
    {
        $this->setUpTheTestEnvironment();
    }

    /**
     * Refresh the application instance.
     *
     * @return void
     */
    protected function refreshApplication(): void
    {
        $this->app = $this->createApplication();
    }


    /**
     * Dummy method to be called
     * @return void
     */
    public function addToAssertionCount(): void
    {
    }

}
