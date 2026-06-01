<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class MakeRepository extends Command {
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'make:repository {name} {--services}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a Repository class, optionally with a paired Service class';

    /**
     * Execute the console command.
     */
    public function handle() {
        $name = $this->argument('name');
        $withService = $this->option('services');

        $repoPath = app_path("Repositories/{$name}Repository.php");
        $servicePath = app_path("Services/Applications/{$name}Service.php");

        File::ensureDirectoryExists(app_path('Repositories'));
        if (!File::exists($repoPath)) {
            File::put($repoPath, $this->repositoryTemplate($name));
            $this->info("Repository created: {$repoPath}");
        } else {
            $this->warn("Repository already exists: {$repoPath}");
        }

        if ($withService) {
            File::ensureDirectoryExists(app_path('Services/Applications'));
            if (!File::exists($servicePath)) {
                File::put($servicePath, $this->serviceTemplate($name));
                $this->info("Service created: {$servicePath}");
            } else {
                $this->warn("Service already exists: {$servicePath}");
            }
        }
    }

    protected function repositoryTemplate($name) {
        return "<?php

namespace App\Repositories;

class {$name}Repository
{
    // TODO: Implement repository methods
}";
    }

    protected function serviceTemplate($name) {
        return "<?php

namespace App\Services\Applications;

use App\Repositories\\{$name}Repository;

class {$name}Service
{
    protected \${$name}Repository;

    public function __construct({$name}Repository \${$name}Repository)
    {
        \$this->{$name}Repository = \${$name}Repository;
    }

    // TODO: Implement service methods
}";
    }
}
