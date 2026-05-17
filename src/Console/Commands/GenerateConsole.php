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
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Concurrency;

#[AsCommand(name: 'hibana:generator')]
class GenerateConsole extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'hibana:generator {keys?* : 処理対象のID（複数指定可）省略した場合は全実行}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'hibana generates static pages.';


    public function __construct(public Repository $config)
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @throws \Throwable
     * @noinspection PhpUnreachableStatementInspection
     */
    public function handle(): void
    {
        $tasks = [];
        $keys = $this->argument('keys');
        if (count($keys) === 0) {
            $keys = array_keys(config()->get('hibana.generators'));
        }

        foreach ($keys as $gkey) {
            $tasks[] = function () use ($gkey) {
                $httpRequest = new HttpRequestSimulation;

                $iterate = 0;
                $std = [];

                app()->make('view.engine.resolver')->register('blade', function () {
                    return new \Illuminate\View\Engines\CompilerEngine(app()->get('blade.compiler'));
                });
                $generators = config()->get('hibana.generators');

                $generator_name = $generators[$gkey];
                $generator = new $generator_name;
                throw_unless($generator instanceof StaticSiteGenerator, RuntimeException::class, sprintf('%s does not implement %s', $generator_name, StaticSiteGenerator::class));

                // $this->components->info(sprintf('Execute generator [%s].', $generator_name));
                foreach ($generator->execute() as $factory) {
                    throw_unless($factory instanceof StaticSiteFactory, RuntimeException::class, sprintf('%s does not implement %s', get_class($factory), StaticSiteFactory::class));
                    $body = $httpRequest->getBody($factory->url());

                    Storage::disk(config()->get('hibana.storage_disk', 'app'))
                        ->put(config()->get('hibana.artifact_path') . $factory->savePath(), $body);

                    if ($httpRequest->getLastStatus() !== 200) {
                        $std[] = [sprintf('Executed %s status is %s.', $factory->url(), $httpRequest->getLastStatus())];
                        return $std;
                    }

                    $path = Storage::disk(config()->get('hibana.storage_disk', 'app'))->path(config()->get('hibana.artifact_path') . $factory->savePath());

                    $std[] = sprintf('Static contents %s [%s] created successfully.', $path, $factory->url()) . "\n";


                    // メモリ解放
                    if ($iterate++ >= 100) {
                        View::flushState();

                        if (app()->bound('view.finder')) {
                            app('view.finder')->flush();
                        }

                        app()->forgetInstance('view');
                        app()->make('view');
                        app()->forgetInstance('blade.compiler');
                        app()->forgetInstance('view.engine.resolver');

                        app()->make('view.engine.resolver')->register('blade', function () {
                            return new \Illuminate\View\Engines\CompilerEngine(app()->get('blade.compiler'));
                        });

                        gc_collect_cycles();
                        $iterate = 0;
                    }
                }


                $std[] = "Key [{$gkey}] の処理が成功しました。";
                return $std;
            };
        }


        foreach (Concurrency::driver(config()->get('hibana.concurrency_driver'))->run($tasks) as $messages) {
            foreach ($messages as $message) {
                $this->components->info($message);
            }
        }
    }

}
