<?php

use App\Enums\MarkdownProfile;
use App\Services\Markdown\MarkdownService;
use Illuminate\Support\Facades\Cache;

beforeEach(function () {
    Cache::flush();
});

describe('basic markdown rendering', function () {
    it('renders bold text', function () {
        $service = new MarkdownService;

        $html = $service->render(markdown: '**bold**');

        expect($html)->toBe("<p><strong>bold</strong></p>\n");
    });

    it('renders italic text', function () {
        $service = new MarkdownService;

        $html = $service->render(markdown: '*italic*');

        expect($html)->toBe("<p><em>italic</em></p>\n");
    });

    it('renders unordered lists', function () {
        $service = new MarkdownService;

        $markdown = "- Item 1\n- Item 2\n- Item 3";
        $html = $service->render(markdown: $markdown);

        expect($html)->toContain('<ul>');
        expect($html)->toContain('<li>Item 1</li>');
        expect($html)->toContain('<li>Item 2</li>');
        expect($html)->toContain('<li>Item 3</li>');
    });

    it('renders ordered lists', function () {
        $service = new MarkdownService;

        $markdown = "1. First\n2. Second\n3. Third";
        $html = $service->render(markdown: $markdown);

        expect($html)->toContain('<ol>');
        expect($html)->toContain('<li>First</li>');
        expect($html)->toContain('<li>Second</li>');
        expect($html)->toContain('<li>Third</li>');
    });
});

describe('disallowed features for basic profile', function () {
    it('does not render images', function () {
        $service = new MarkdownService;

        $html = $service->render(markdown: '![alt text](https://example.com/image.png)');

        expect($html)->not->toContain('<img');
    });

    it('does not render tables', function () {
        $service = new MarkdownService;

        $markdown = "| Header 1 | Header 2 |\n| --- | --- |\n| Cell 1 | Cell 2 |";
        $html = $service->render(markdown: $markdown);

        expect($html)->not->toContain('<table>');
        expect($html)->not->toContain('<th>');
        expect($html)->not->toContain('<td>');
    });

    it('renders links with external link attributes', function () {
        $service = new MarkdownService;

        $html = $service->render(markdown: '[click here](https://example.com)');

        expect($html)->toContain('<a');
        expect($html)->toContain('href="https://example.com"');
        expect($html)->toContain('target="_blank"');
        expect($html)->toContain('class="external-link"');
        expect($html)->toContain('rel="nofollow noopener"');
    });

    it('does not render code blocks', function () {
        $service = new MarkdownService;

        $markdown = "```php\n\$foo = 'bar';\n```";
        $html = $service->render(markdown: $markdown);

        expect($html)->not->toContain('<pre>');
        expect($html)->not->toContain('<code>');
    });

    it('does not render inline code', function () {
        $service = new MarkdownService;

        $html = $service->render(markdown: '`inline code`');

        expect($html)->not->toContain('<code>');
    });

    it('does not render blockquotes', function () {
        $service = new MarkdownService;

        $html = $service->render(markdown: '> This is a quote');

        expect($html)->not->toContain('<blockquote>');
    });
});

describe('full markdown profile', function () {
    it('renders headings', function () {
        $service = new MarkdownService;

        $html = $service->render(markdown: '# Heading 1', profile: MarkdownProfile::Full);

        expect($html)->toContain('<h1>Heading 1</h1>');
    });

    it('renders links with external link attributes', function () {
        $service = new MarkdownService;

        $html = $service->render(markdown: '[click here](https://example.com)', profile: MarkdownProfile::Full);

        expect($html)->toContain('href="https://example.com"');
        expect($html)->toContain('target="_blank"');
        expect($html)->toContain('class="external-link"');
        expect($html)->toContain('rel="nofollow noopener"');
        expect($html)->toContain('>click here</a>');
    });

    it('renders code blocks', function () {
        $service = new MarkdownService;

        $markdown = "```php\n\$foo = 'bar';\n```";
        $html = $service->render(markdown: $markdown, profile: MarkdownProfile::Full);

        expect($html)->toContain('<pre>');
        expect($html)->toContain('<code');
    });

    it('renders inline code', function () {
        $service = new MarkdownService;

        $html = $service->render(markdown: '`inline code`', profile: MarkdownProfile::Full);

        expect($html)->toContain('<code>inline code</code>');
    });

    it('renders blockquotes', function () {
        $service = new MarkdownService;

        $html = $service->render(markdown: '> This is a quote', profile: MarkdownProfile::Full);

        expect($html)->toContain('<blockquote>');
        expect($html)->toContain('This is a quote');
    });

    it('renders tables', function () {
        $service = new MarkdownService;

        $markdown = "| Header 1 | Header 2 |\n| --- | --- |\n| Cell 1 | Cell 2 |";
        $html = $service->render(markdown: $markdown, profile: MarkdownProfile::Full);

        expect($html)->toContain('<table>');
        expect($html)->toContain('<th>Header 1</th>');
        expect($html)->toContain('<th>Header 2</th>');
        expect($html)->toContain('<td>Cell 1</td>');
        expect($html)->toContain('<td>Cell 2</td>');
    });

    it('renders strikethrough', function () {
        $service = new MarkdownService;

        $html = $service->render(markdown: '~~strikethrough~~', profile: MarkdownProfile::Full);

        expect($html)->toContain('<del>strikethrough</del>');
    });

    it('renders task lists', function () {
        $service = new MarkdownService;

        $markdown = "- [x] Done\n- [ ] Not done";
        $html = $service->render(markdown: $markdown, profile: MarkdownProfile::Full);

        expect($html)->toContain('type="checkbox"');
        expect($html)->toContain('checked');
    });

    it('renders autolinks with external link attributes', function () {
        $service = new MarkdownService;

        $html = $service->render(markdown: 'Visit https://example.com for more info', profile: MarkdownProfile::Full);

        expect($html)->toContain('href="https://example.com"');
        expect($html)->toContain('target="_blank"');
        expect($html)->toContain('class="external-link"');
        expect($html)->toContain('rel="nofollow noopener"');
    });

    it('works with renderWithoutCache', function () {
        $service = new MarkdownService;

        $html = $service->renderWithoutCache(markdown: '# Heading', profile: MarkdownProfile::Full);

        expect($html)->toContain('<h1>Heading</h1>');
    });
});

describe('empty string handling', function () {
    it('returns empty string for empty input', function () {
        $service = new MarkdownService;

        expect($service->render(markdown: ''))->toBe('');
    });

    it('returns empty string for whitespace-only input', function () {
        $service = new MarkdownService;

        expect($service->render(markdown: '   '))->toBe('');
        expect($service->render(markdown: "\n\n"))->toBe('');
        expect($service->render(markdown: "\t\t"))->toBe('');
    });

    it('returns empty string for renderWithoutCache with empty input', function () {
        $service = new MarkdownService;

        expect($service->renderWithoutCache(markdown: ''))->toBe('');
        expect($service->renderWithoutCache(markdown: '   '))->toBe('');
    });

    it('returns empty string for full profile with empty input', function () {
        $service = new MarkdownService;

        expect($service->render(markdown: '', profile: MarkdownProfile::Full))->toBe('');
        expect($service->renderWithoutCache(markdown: '', profile: MarkdownProfile::Full))->toBe('');
    });
});

describe('cache key format', function () {
    it('generates cache key with correct format for basic profile', function () {
        $service = new MarkdownService;
        $markdown = 'test content';

        $cacheKey = $service->getCacheKey(markdown: $markdown);

        expect($cacheKey)->toBe('markdown:basic:'.crc32($markdown));
    });

    it('generates cache key with correct format for full profile', function () {
        $service = new MarkdownService;
        $markdown = 'test content';

        $cacheKey = $service->getCacheKey(markdown: $markdown, profile: MarkdownProfile::Full);

        expect($cacheKey)->toBe('markdown:full:'.crc32($markdown));
    });

    it('generates unique cache keys for different content', function () {
        $service = new MarkdownService;

        $key1 = $service->getCacheKey(markdown: 'content one');
        $key2 = $service->getCacheKey(markdown: 'content two');

        expect($key1)->not->toBe($key2);
    });

    it('generates same cache key for identical content', function () {
        $service = new MarkdownService;

        $key1 = $service->getCacheKey(markdown: 'same content');
        $key2 = $service->getCacheKey(markdown: 'same content');

        expect($key1)->toBe($key2);
    });

    it('generates different cache keys for same content with different profiles', function () {
        $service = new MarkdownService;
        $markdown = 'same content';

        $basicKey = $service->getCacheKey(markdown: $markdown, profile: MarkdownProfile::Basic);
        $fullKey = $service->getCacheKey(markdown: $markdown, profile: MarkdownProfile::Full);

        expect($basicKey)->not->toBe($fullKey);
    });
});

describe('caching behavior', function () {
    it('stores rendered html in cache', function () {
        $service = new MarkdownService;
        $markdown = '**cached**';

        $service->render(markdown: $markdown);

        $cacheKey = $service->getCacheKey(markdown: $markdown);
        expect(Cache::has(key: $cacheKey))->toBeTrue();
    });

    it('retrieves html from cache on subsequent calls', function () {
        $service = new MarkdownService;
        $markdown = '**cached content**';

        $firstResult = $service->render(markdown: $markdown);
        $secondResult = $service->render(markdown: $markdown);

        expect($firstResult)->toBe($secondResult);
    });

    it('does not cache when using renderWithoutCache', function () {
        $service = new MarkdownService;
        $markdown = '**not cached**';

        $service->renderWithoutCache(markdown: $markdown);

        $cacheKey = $service->getCacheKey(markdown: $markdown);
        expect(Cache::has(key: $cacheKey))->toBeFalse();
    });

    it('clears cache for specific content', function () {
        $service = new MarkdownService;
        $markdown = '**to be cleared**';

        $service->render(markdown: $markdown);
        $cacheKey = $service->getCacheKey(markdown: $markdown);
        expect(Cache::has(key: $cacheKey))->toBeTrue();

        $result = $service->clearCache(markdown: $markdown);

        expect($result)->toBeTrue();
        expect(Cache::has(key: $cacheKey))->toBeFalse();
    });

    it('returns false when clearing cache for non-existent content', function () {
        $service = new MarkdownService;

        $result = $service->clearCache(markdown: 'never rendered');

        expect($result)->toBeFalse();
    });

    it('caches full profile separately from basic profile', function () {
        $service = new MarkdownService;
        $markdown = '**same content**';

        $service->render(markdown: $markdown);
        $service->render(markdown: $markdown, profile: MarkdownProfile::Full);

        $basicKey = $service->getCacheKey(markdown: $markdown, profile: MarkdownProfile::Basic);
        $fullKey = $service->getCacheKey(markdown: $markdown, profile: MarkdownProfile::Full);

        expect(Cache::has(key: $basicKey))->toBeTrue();
        expect(Cache::has(key: $fullKey))->toBeTrue();
    });

    it('clears cache for specific profile only', function () {
        $service = new MarkdownService;
        $markdown = '**test content**';

        $service->render(markdown: $markdown);
        $service->render(markdown: $markdown, profile: MarkdownProfile::Full);

        $service->clearCache(markdown: $markdown, profile: MarkdownProfile::Basic);

        $basicKey = $service->getCacheKey(markdown: $markdown, profile: MarkdownProfile::Basic);
        $fullKey = $service->getCacheKey(markdown: $markdown, profile: MarkdownProfile::Full);

        expect(Cache::has(key: $basicKey))->toBeFalse();
        expect(Cache::has(key: $fullKey))->toBeTrue();
    });

    it('clears cache by key identifier', function () {
        $service = new MarkdownService;
        $cacheKey = 'custom-cache-key';

        $service->render(markdown: '**content**', cacheKey: $cacheKey);

        $fullCacheKey = $service->getCacheKey(cacheKey: $cacheKey);
        expect(Cache::has(key: $fullCacheKey))->toBeTrue();

        $result = $service->clearCacheByKey(cacheKey: $cacheKey);

        expect($result)->toBeTrue();
        expect(Cache::has(key: $fullCacheKey))->toBeFalse();
    });

    it('clears cache by key identifier for specific profile', function () {
        $service = new MarkdownService;
        $cacheKey = 'profile-specific-key';

        $service->render(markdown: '**content**', profile: MarkdownProfile::Full, cacheKey: $cacheKey);

        $fullCacheKey = $service->getCacheKey(profile: MarkdownProfile::Full, cacheKey: $cacheKey);
        expect(Cache::has(key: $fullCacheKey))->toBeTrue();

        $result = $service->clearCacheByKey(cacheKey: $cacheKey, profile: MarkdownProfile::Full);

        expect($result)->toBeTrue();
        expect(Cache::has(key: $fullCacheKey))->toBeFalse();
    });

    it('returns false when clearing cache by key for non-existent key', function () {
        $service = new MarkdownService;

        $result = $service->clearCacheByKey(cacheKey: 'non-existent-key');

        expect($result)->toBeFalse();
    });
});

describe('xss protection', function () {
    it('strips script tags from input', function () {
        $service = new MarkdownService;

        $html = $service->render(markdown: '<script>alert("xss")</script>');

        expect($html)->not->toContain('<script>');
        expect($html)->not->toContain('</script>');
    });

    it('escapes html entities', function () {
        $service = new MarkdownService;

        $html = $service->render(markdown: '<div>test</div>');

        expect($html)->not->toContain('<div>');
        expect($html)->toContain('&lt;div&gt;');
    });

    it('strips script tags in full profile', function () {
        $service = new MarkdownService;

        $html = $service->render(markdown: '<script>alert("xss")</script>', profile: MarkdownProfile::Full);

        expect($html)->not->toContain('<script>');
        expect($html)->not->toContain('</script>');
    });
});
