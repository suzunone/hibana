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

        $iterate = 0;

        app()->make('view.engine.resolver')->register('blade', function () {
            return new \Illuminate\View\Engines\CompilerEngine(app()->get('blade.compiler'));
        });
        foreach ($this->config->get('hibana.generators') as $generator_name) {
            $generator = new $generator_name;
            throw_unless($generator instanceof StaticSiteGenerator, RuntimeException::class, sprintf('%s does not implement %s', $generator_name, StaticSiteGenerator::class));

            $this->components->info(sprintf('Execute generator [%s].', $generator_name));
            foreach ($generator->execute() as $factory) {
                throw_unless($factory instanceof StaticSiteFactory, RuntimeException::class, sprintf('%s does not implement %s', get_class($factory), StaticSiteFactory::class));
                $body = $httpRequest->getBody($factory->url());

                if ($httpRequest->getLastStatus() !== 200) {
                    $this->components->warn(sprintf('Executed %s status is %s.', $factory->url(), $httpRequest->getLastStatus() ));
                }

                Storage::disk($this->config->get('hibana.storage_disk', 'app'))
                    ->put($this->config->get('hibana.artifact_path') . $factory->savePath(), $body);

                $path = Storage::disk($this->config->get('hibana.storage_disk', 'app'))
                    ->path($this->config->get('hibana.artifact_path') . $factory->savePath());

                $this->components->info(sprintf('Static contents %s [%s] created successfully.', $path, $factory->url()));

                if ($iterate++ >= 100) {
                    // 1. ビューの状態をクリア
                    View::flushState();

                    // ② ViewFinderが内部にキャッシュしている「解決済みファイルパス」のマップをクリア
                    // ※これをしないと、ファイルパスの文字列がループの数だけメモリに蓄積されます
                    if (app()->bound('view.finder')) {
                        app('view.finder')->flush();
                    }

                    // 3. コンテナにキャッシュされているViewファクトリ自体を再生成（※特に有効）
                    app()->forgetInstance('view');
                    app()->make('view');
                    app()->forgetInstance('blade.compiler');
                    app()->forgetInstance('view.engine.resolver');

                    app()->make('view.engine.resolver')->register('blade', function () {
                        return new \Illuminate\View\Engines\CompilerEngine(app()->get('blade.compiler'));
                    });

                    // 4. PHPにメモリ解放を促す
                    gc_collect_cycles();

                    $this->components->info('flushState!!');
                    $iterate = 0;
                }
            }

        }

    }

    public function httpRequestSimulation(): HttpRequestSimulation
    {
        return new HttpRequestSimulation;
    }
}
