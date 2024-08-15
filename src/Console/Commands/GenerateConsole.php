<?php

namespace Suzunone\Hibana\Console\Commands;

use Illuminate\Config\Repository;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use RuntimeException;
use Suzunone\Hibana\Contracts\StaticSiteFactory;
use Suzunone\Hibana\Contracts\StaticSiteGenerator;
use Suzunone\Hibana\Simulations\HttpRequestSimulation;
use Symfony\Component\Console\Attribute\AsCommand;

#[AsCommand(name: 'hibana:generator')]
class GenerateConsole extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'hibana:generator';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';


    public function __construct(public Repository $config)
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @throws \Throwable
     */
    public function handle(): void
    {
        $httpRequest = $this->httpRequestSimulation();

        foreach ($this->config->get('hibana.generators') as $generator_name) {
            $generator = new $generator_name;
            throw_unless($generator instanceof StaticSiteGenerator, RuntimeException::class, sprintf('%s does not implement %s', $generator_name, StaticSiteGenerator::class));

            $this->components->info(sprintf('Execute generator [%s].', $generator_name));
            foreach ($generator->execute() as $factory) {
                throw_unless($factory instanceof StaticSiteFactory, RuntimeException::class, sprintf('%s does not implement %s', get_class($factory), StaticSiteFactory::class));
                $body = $httpRequest->getBody($factory->url());
                Storage::disk($this->config->get('hibana.storage_disk', 'app'))
                    ->put($this->config->get('hibana.artifact_path') . $factory->savePath(), $body);

                $path = Storage::disk($this->config->get('hibana.storage_disk', 'app'))
                    ->path($this->config->get('hibana.artifact_path') . $factory->savePath());
                $this->components->info(sprintf('Static contents %s [%s] created successfully.', $path, $factory->url()));
            }
        }

    }

    public function httpRequestSimulation(): HttpRequestSimulation
    {
        return new HttpRequestSimulation;
    }
}
