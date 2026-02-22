<?php

namespace App\Concerns;

use App\Enums\MarkdownProfile;
use App\Services\Markdown\MarkdownService;

trait ClearsMarkdownCache
{
    /**
     * @return array<int, string>
     */
    abstract public function getCachedFields(): array;

    protected static function bootClearsMarkdownCache(): void
    {
        static::updated(function (self $model): void {
            $markdownService = app(MarkdownService::class);
            $cached = $model->getCachedFields();
            $prefix = $model->getMarkdownCacheKeyPrefix();

            foreach ($model->getChanges() as $field => $value) {
                if (in_array($field, $cached) === false) {
                    continue;
                }

                foreach (MarkdownProfile::cases() as $profile) {
                    $markdownService->clearCacheByKey(
                        cacheKey: "{$prefix}|{$model->id}|{$field}",
                        profile: $profile
                    );
                }
            }
        });

        static::deleted(function (self $model): void {
            $model->clearMarkdownCache();
        });
    }

    public function clearMarkdownCache(): void
    {
        $markdownService = app(MarkdownService::class);
        $prefix = $this->getMarkdownCacheKeyPrefix();

        foreach ($this->getCachedFields() as $field) {
            foreach (MarkdownProfile::cases() as $profile) {
                $markdownService->clearCacheByKey(
                    cacheKey: "{$prefix}|{$this->id}|{$field}",
                    profile: $profile
                );
            }
        }
    }

    public function getMarkdownCacheKeyPrefix(): string
    {
        return strtolower(class_basename(static::class));
    }
}
