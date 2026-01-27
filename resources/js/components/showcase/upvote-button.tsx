import ShowcaseUpvoteController from '@/actions/App/Http/Controllers/Showcase/ShowcaseUpvoteController';
import { Button } from '@/components/ui/button';
import { cn } from '@/lib/utils';
import { type SharedData } from '@/types';
import { router, usePage } from '@inertiajs/react';
import { ArrowUp } from 'lucide-react';
import { useState } from 'react';
import { AuthPromptModal } from './upvote-prompt-modal';

interface UpvoteButtonProps {
    showcaseSlug: string;
    upvotesCount: number;
    hasUpvoted: boolean;
    variant?: 'compact' | 'full';
}

export function UpvoteButton({
    showcaseSlug,
    upvotesCount,
    hasUpvoted,
    variant = 'compact',
}: UpvoteButtonProps) {
    const page = usePage<SharedData>();
    const { auth } = page.props;
    const isAuthenticated = auth?.user !== undefined && auth?.user !== null;
    const [showAuthModal, setShowAuthModal] = useState(false);

    const handleUpvote = () => {
        if (isAuthenticated === false) {
            setShowAuthModal(true);
            return;
        }

        router.post(
            ShowcaseUpvoteController.url({ showcase: showcaseSlug }),
            {},
            { preserveScroll: true },
        );
    };

    if (variant === 'full') {
        return (
            <>
                <Button
                    variant={hasUpvoted === true ? 'default' : 'outline'}
                    className="w-full"
                    onClick={handleUpvote}
                >
                    <ArrowUp className="size-4" />
                    Upvote
                    {upvotesCount > 0 && (
                        <span className="ml-1">&bull; {upvotesCount}</span>
                    )}
                </Button>
                <AuthPromptModal
                    isOpen={showAuthModal}
                    onClose={() => setShowAuthModal(false)}
                />
            </>
        );
    }

    return (
        <>
            <Button
                variant={hasUpvoted ? 'default' : 'outline'}
                size="sm"
                onClick={handleUpvote}
                className="flex h-auto flex-col gap-1 px-3 py-2"
            >
                <ArrowUp
                    className={cn(
                        'size-5',
                        hasUpvoted === true && 'text-primary-foreground',
                    )}
                />
                <span className="text-sm font-semibold">{upvotesCount}</span>
            </Button>
            <AuthPromptModal
                isOpen={showAuthModal}
                onClose={() => setShowAuthModal(false)}
            />
        </>
    );
}
