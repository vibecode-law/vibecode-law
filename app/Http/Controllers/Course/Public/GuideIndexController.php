<?php

namespace App\Http\Controllers\Course\Public;

use App\Http\Controllers\BaseController;
use App\Services\Content\ContentService;
use Illuminate\Support\Facades\Config;
use Inertia\Inertia;
use Inertia\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class GuideIndexController extends BaseController
{
    public function __construct(
        private ContentService $contentService
    ) {}

    public function __invoke(): Response
    {
        return Inertia::render(component: 'learn/guides/index', props: [
            'title' => $this->getIndexTitle(),
            'content' => $this->getContent(),
            'children' => $this->getChildren(),
        ]);
    }

    private function getIndexTitle(): string
    {
        /** @var array{title?: string, slug?: string|null} $config */
        $config = Config::get(key: 'content.resources.index', default: []);

        return $config['title'] ?? 'Guides';
    }

    private function getContent(): string
    {
        $contentPath = 'resources/index';

        if ($this->contentService->exists(location: $contentPath) === false) {
            throw new NotFoundHttpException;
        }

        return $this->contentService->get(location: $contentPath);
    }

    /**
     * @return array<int, array{name: string, slug: string, summary: string, icon: string, route: string}>
     */
    private function getChildren(): array
    {
        /** @var array<int, array{title: string, slug: string, summary: string, icon: string}> $childrenConfig */
        $childrenConfig = Config::get(key: 'content.resources.children', default: []);

        return collect($childrenConfig)->map(fn (array $child): array => [
            'name' => $child['title'],
            'slug' => $child['slug'],
            'summary' => $child['summary'],
            'icon' => $child['icon'],
            'route' => route(name: 'resources.show', parameters: ['slug' => $child['slug']]),
        ])->all();
    }
}
