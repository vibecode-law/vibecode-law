<?php

namespace App\Services\Markdown;

use App\Enums\MarkdownProfile;
use Illuminate\Support\Facades\Cache;
use League\CommonMark\Environment\Environment;
use League\CommonMark\Extension\CommonMark\CommonMarkCoreExtension;
use League\CommonMark\Extension\GithubFlavoredMarkdownExtension;
use League\CommonMark\Extension\HeadingPermalink\HeadingPermalinkExtension;
use League\CommonMark\MarkdownConverter;

class MarkdownService
{
    private ?MarkdownConverter $basicConverter = null;

    private ?MarkdownConverter $fullConverter = null;

    public function render(string $markdown, MarkdownProfile $profile = MarkdownProfile::Basic, ?string $cacheKey = null): string
    {
        if (trim($markdown) === '') {
            return '';
        }

        $resolvedCacheKey = $this->getCacheKey(
            profile: $profile,
            markdown: $markdown,
            cacheKey: $cacheKey
        );

        return Cache::rememberForever(
            key: $resolvedCacheKey,
            callback: fn (): string => $this->convertToHtml(markdown: $markdown, profile: $profile)
        );
    }

    public function renderWithoutCache(string $markdown, MarkdownProfile $profile = MarkdownProfile::Basic): string
    {
        if (trim($markdown) === '') {
            return '';
        }

        return $this->convertToHtml(markdown: $markdown, profile: $profile);
    }

    public function getCacheKey(MarkdownProfile $profile = MarkdownProfile::Basic, ?string $markdown = null, ?string $cacheKey = null): string
    {
        $identifier = $cacheKey ?? crc32($markdown);

        return 'markdown:'.$profile->value.':'.$identifier;
    }

    public function clearCache(string $markdown, MarkdownProfile $profile = MarkdownProfile::Basic, ?string $cacheKey = null): bool
    {
        return Cache::forget($this->getCacheKey(
            profile: $profile,
            markdown: $markdown,
            cacheKey: $cacheKey
        ));
    }

    public function clearCacheByKey(string $cacheKey, MarkdownProfile $profile = MarkdownProfile::Basic): bool
    {
        return Cache::forget($this->getCacheKey(
            profile: $profile,
            cacheKey: $cacheKey
        ));
    }

    private function convertToHtml(string $markdown, MarkdownProfile $profile): string
    {
        $converter = $this->getConverter(profile: $profile);

        return $converter->convert($markdown)->getContent();
    }

    private function getConverter(MarkdownProfile $profile): MarkdownConverter
    {
        if ($profile === MarkdownProfile::Full) {
            return $this->fullConverter ??= $this->createFullConverter();
        }

        return $this->basicConverter ??= $this->createBasicConverter();
    }

    private function createBasicConverter(): MarkdownConverter
    {
        $environment = new Environment([
            'html_input' => 'strip',
            'allow_unsafe_links' => false,
        ]);

        $environment->addExtension(new BasicFormattingExtension);

        return new MarkdownConverter($environment);
    }

    private function createFullConverter(): MarkdownConverter
    {
        $environment = new Environment([
            'html_input' => 'allow',
            'allow_unsafe_links' => false,
            'heading_permalink' => [
                'min_heading_level' => 2,
                'max_heading_level' => 6,
                'insert' => 'none',
                'apply_id_to_heading' => true,
                'id_prefix' => '',
            ],
            'disallowed_raw_html' => [
                'disallowed_tags' => ['title', 'textarea', 'style', 'xmp', 'noembed', 'noframes', 'script', 'plaintext'],
            ],
        ]);

        $environment->addExtension(new CommonMarkCoreExtension);
        $environment->addExtension(new GithubFlavoredMarkdownExtension);
        $environment->addExtension(new HeadingPermalinkExtension);

        return new MarkdownConverter($environment);
    }
}
