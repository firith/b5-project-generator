<?php

namespace App\Commands;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use LaravelZero\Framework\Commands\Command;
use Symfony\Component\Console\Exception\InvalidArgumentException;

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
        $this->appName = (string) $this->argument('app-name');
        $this->parentDirectory = $this->argument('app-name') . '-b5';

        if ($this->option('framework') && !in_array($this->option('framework'), $this->frameworks)) {
            throw new InvalidArgumentException(sprintf('Támogatott frameworkök: %s', implode(', ', $this->frameworks)));
        }

        $this->task('Generate b5 files', function () {
            Storage::disk('cwd')->makeDirectory($this->parentDirectory . '/build');
            Storage::disk('cwd')->makeDirectory($this->parentDirectory . '/' . $this->argument('app-name'));

            $this->generateFile('config.yml', 'config.yml');
            $this->generateFile('Taskfile', 'Taskfile');
            $this->generateFile('docker-compose.yaml', 'docker-compose.yaml');
            $this->generateFile('configs/nginx/site.conf', 'configs/nginx/site.conf');
            $this->generateFile('configs/php/Dockerfile', 'configs/php/Dockerfile');
            // Todo gitignore és gitkeep
            //todo git init

            Storage::disk('cwd')->put($this->parentDirectory . '/'. $this->appName .'/index.php', 'hello world');
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
}
