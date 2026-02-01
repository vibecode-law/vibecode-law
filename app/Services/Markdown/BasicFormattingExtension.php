<?php

namespace App\Services\Markdown;

use League\CommonMark\Environment\EnvironmentBuilderInterface;
use League\CommonMark\Extension\CommonMark\Delimiter\Processor\EmphasisDelimiterProcessor;
use League\CommonMark\Extension\CommonMark\Node\Block\ListBlock;
use League\CommonMark\Extension\CommonMark\Node\Block\ListItem;
use League\CommonMark\Extension\CommonMark\Node\Inline\Emphasis;
use League\CommonMark\Extension\CommonMark\Node\Inline\Link;
use League\CommonMark\Extension\CommonMark\Node\Inline\Strong;
use League\CommonMark\Extension\CommonMark\Parser\Block\ListBlockStartParser;
use League\CommonMark\Extension\CommonMark\Parser\Inline\CloseBracketParser;
use League\CommonMark\Extension\CommonMark\Parser\Inline\OpenBracketParser;
use League\CommonMark\Extension\CommonMark\Renderer\Block\ListBlockRenderer;
use League\CommonMark\Extension\CommonMark\Renderer\Block\ListItemRenderer;
use League\CommonMark\Extension\CommonMark\Renderer\Inline\EmphasisRenderer;
use League\CommonMark\Extension\CommonMark\Renderer\Inline\LinkRenderer;
use League\CommonMark\Extension\CommonMark\Renderer\Inline\StrongRenderer;
use League\CommonMark\Extension\ConfigurableExtensionInterface;
use League\CommonMark\Node\Block\Document;
use League\CommonMark\Node\Block\Paragraph;
use League\CommonMark\Node\Inline\Newline;
use League\CommonMark\Node\Inline\Text;
use League\CommonMark\Parser\Inline\NewlineParser;
use League\CommonMark\Renderer\Block\DocumentRenderer;
use League\CommonMark\Renderer\Block\ParagraphRenderer;
use League\CommonMark\Renderer\Inline\NewlineRenderer;
use League\CommonMark\Renderer\Inline\TextRenderer;
use League\Config\ConfigurationBuilderInterface;
use Nette\Schema\Expect;

final class BasicFormattingExtension implements ConfigurableExtensionInterface
{
    public function configureSchema(ConfigurationBuilderInterface $builder): void
    {
        $builder->addSchema('commonmark', Expect::structure([
            'use_asterisk' => Expect::bool(true),
            'use_underscore' => Expect::bool(true),
            'enable_strong' => Expect::bool(true),
            'enable_em' => Expect::bool(true),
            'unordered_list_markers' => Expect::listOf('string')->min(1)->default(['*', '+', '-'])->mergeDefaults(false),
        ]));
    }

    public function register(EnvironmentBuilderInterface $environment): void
    {
        // Block parsers - only lists
        $environment->addBlockStartParser(new ListBlockStartParser, 10);

        // Inline parsers - newlines and links (emphasis handled via delimiter processors)
        $environment->addInlineParser(new NewlineParser, 200);
        $environment->addInlineParser(new OpenBracketParser, 150);
        $environment->addInlineParser(new CloseBracketParser, 151);

        // Block renderers
        $environment->addRenderer(nodeClass: Document::class, renderer: new DocumentRenderer, priority: 0);
        $environment->addRenderer(nodeClass: Paragraph::class, renderer: new ParagraphRenderer, priority: 0);
        $environment->addRenderer(nodeClass: ListBlock::class, renderer: new ListBlockRenderer, priority: 0);
        $environment->addRenderer(nodeClass: ListItem::class, renderer: new ListItemRenderer, priority: 0);

        // Inline renderers
        $environment->addRenderer(nodeClass: Text::class, renderer: new TextRenderer, priority: 0);
        $environment->addRenderer(nodeClass: Newline::class, renderer: new NewlineRenderer, priority: 0);
        $environment->addRenderer(nodeClass: Emphasis::class, renderer: new EmphasisRenderer, priority: 0);
        $environment->addRenderer(nodeClass: Strong::class, renderer: new StrongRenderer, priority: 0);
        $environment->addRenderer(nodeClass: Link::class, renderer: new LinkRenderer, priority: 0);

        // Delimiter processors for emphasis (bold/italic)
        if ($environment->getConfiguration()->get('commonmark/use_asterisk') === true) {
            $environment->addDelimiterProcessor(new EmphasisDelimiterProcessor('*'));
        }

        if ($environment->getConfiguration()->get('commonmark/use_underscore') === true) {
            $environment->addDelimiterProcessor(new EmphasisDelimiterProcessor('_'));
        }
    }
}
