<?php

namespace App\Commands;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use LaravelZero\Framework\Commands\Command;
use Symfony\Component\Console\Exception\InvalidArgumentException;
use Symfony\Component\Process\Process;

class InitCommand extends Command
{
    /**
     * The signature of the command.
     *
     * @var string
     */
    protected $signature = 'init {app-name} {--framework=} {--install}';

    /**
     * The description of the command.
     *
     * @var string
     */
    protected $description = 'Generate new project';

    /**
     * Supported frameworks
     *
     * @var array|string[]
     */
    private array $frameworks = ['symfony', 'laravel'];

    private string $appName;

    private string $parentDirectory;

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $this->appName = (string)$this->argument('app-name');
        $this->parentDirectory = $this->argument('app-name') . '-b5';

        if ($this->option('framework') && !in_array($this->option('framework'), $this->frameworks)) {
            throw new InvalidArgumentException(sprintf('Támogatott frameworkök: %s', implode(', ', $this->frameworks)));
        }

        $this->task('Generate b5 files', fn() => $this->generateB5Files());

        if ($this->option('framework') === 'laravel') {
            $this->task('Generate laravel specific b5 files', function () {
                $this->generateFile('config-laravel.yml', 'config.yml');
                $this->generateFile('Taskfile-laravel', 'Taskfile');
                $this->generateFile('docker-compose-laravel.yaml', 'docker-compose.yaml');
                $this->generateFile('configs/nginx/site-laravel.conf', 'configs/nginx/site.conf');
                $this->generateFile('configs/php/Dockerfile-laravel', 'configs/php/Dockerfile');
            });
        }

        if ($this->option('framework') === 'symfony') {
            $this->task('Generate laravel specific b5 files', function () {
                $this->generateFile('config-symfony.yml', 'config.yml');
                $this->generateFile('Taskfile-symfony', 'Taskfile');
                $this->generateFile('docker-compose-symfony.yaml', 'docker-compose.yaml');
                $this->generateFile('configs/nginx/site-symfony.conf', 'configs/nginx/site.conf');
                $this->generateFile('configs/php/Dockerfile-symfony', 'configs/php/Dockerfile');
            });
        }

        if ($this->option('install') && $this->option('framework') === 'laravel') {
            $this->task('Installing laravel framework', function () {
                Storage::disk('cwd')->deleteDirectory($this->parentDirectory . '/' . $this->argument('app-name'));
                $process = new Process(
                    ['composer', 'create-project', '--prefer-dist', 'laravel/laravel', $this->appName],
                    Storage::disk('cwd')->path($this->parentDirectory));
                $process->run();
                $this->output->writeln($process->getOutput());
            });
        }

        if ($this->option('install') && $this->option('framework') === 'symfony') {
            $this->task('Installing symfony framework', function () {
                Storage::disk('cwd')->deleteDirectory($this->parentDirectory . '/' . $this->argument('app-name'));
                $process = new Process(
                    ['composer', 'create-project', 'symfony/website-skeleton', $this->appName],
                    Storage::disk('cwd')->path($this->parentDirectory));
                $process->run();
                $this->output->writeln($process->getOutput());
            });
        }

        $this->task('Initialize git', function () {
            // Todo gitignore és gitkeep
            //todo git init

        });

        $this->task('Docker build', function () {

        });

        return 0;
    }

    public function generateFile(string $stub, string $path): void
    {
        $fileContent = File::get(resource_path('stubs/' . $stub));
        $fileContent = Str::of($fileContent)
            ->replace('{{ appName }}', $this->appName);
        Storage::disk('cwd')->put($this->parentDirectory . '/build/' . $path, $fileContent);
    }

    private function generateB5Files(): void
    {
        Storage::disk('cwd')->makeDirectory($this->parentDirectory . '/build');
        Storage::disk('cwd')->makeDirectory($this->parentDirectory . '/' . $this->argument('app-name'));

        $this->generateFile('config.yml', 'config.yml');
        $this->generateFile('Taskfile', 'Taskfile');
        $this->generateFile('docker-compose.yaml', 'docker-compose.yaml');
        $this->generateFile('configs/nginx/site.conf', 'configs/nginx/site.conf');
        $this->generateFile('configs/php/Dockerfile', 'configs/php/Dockerfile');

        if (!$this->option('framework')) {
            Storage::disk('cwd')->put($this->parentDirectory . '/' . $this->appName . '/index.php', 'hello world');
        }
    }
}
