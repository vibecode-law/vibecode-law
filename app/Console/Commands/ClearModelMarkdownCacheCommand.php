<?php

namespace App\Console\Commands;

use App\Concerns\ClearsMarkdownCache;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\File;
use ReflectionClass;

class ClearModelMarkdownCacheCommand extends Command
{
    /**
     * @var string
     */
    protected $signature = 'app:model-markdown:clear {--model= : Clear cache for a specific model only (e.g. Showcase, Course)}';

    /**
     * @var string
     */
    protected $description = 'Clear markdown caches for models using the ClearsMarkdownCache trait';

    public function handle(): int
    {
        $models = $this->discoverModels();

        $modelFilter = $this->option(key: 'model');

        if ($modelFilter !== null) {
            $models = $models->filter(
                fn (string $class): bool => strcasecmp(class_basename($class), $modelFilter) === 0
            );

            if ($models->isEmpty()) {
                $this->components->error(string: "Model '{$modelFilter}' not found or does not use ClearsMarkdownCache.");

                return Command::FAILURE;
            }
        }

        $totalCleared = 0;

        foreach ($models as $modelClass) {
            $cleared = $this->clearCacheForModel(modelClass: $modelClass);

            $totalCleared += $cleared;
        }

        $this->components->info(string: "Cleared {$totalCleared} markdown cache(s).");

        return Command::SUCCESS;
    }

    /**
     * @param  class-string<Model>  $modelClass
     */
    private function clearCacheForModel(string $modelClass): int
    {
        $cleared = 0;

        $modelClass::query()->select((new $modelClass)->getKeyName())->chunkById(
            count: 500,
            callback: function ($records) use (&$cleared): void {
                foreach ($records as $record) {
                    $record->clearMarkdownCache(); // @phpstan-ignore method.notFound
                    $cleared++;
                }
            }
        );

        $this->components->twoColumnDetail(class_basename($modelClass), "{$cleared} record(s) cleared");

        return $cleared;
    }

    /**
     * @return \Illuminate\Support\Collection<int, class-string<Model>>
     */
    private function discoverModels(): \Illuminate\Support\Collection
    {
        $modelsPath = app_path('Models');

        /** @var \Illuminate\Support\Collection<int, class-string<Model>> */
        return collect(File::allFiles($modelsPath))
            ->map(function (\SplFileInfo $file): string {
                $relativePath = str_replace(
                    ['/', '.php'],
                    ['\\', ''],
                    $file->getRelativePathname()
                );

                return 'App\\Models\\'.$relativePath;
            })
            ->filter(function (string $class): bool {
                if (class_exists($class) === false) {
                    return false;
                }

                $reflection = new ReflectionClass($class);

                return $reflection->isAbstract() === false
                    && $reflection->isSubclassOf(Model::class)
                    && in_array(ClearsMarkdownCache::class, class_uses_recursive($class));
            })
            ->sort()
            ->values();
    }
}
