import { SignInButtons } from '@/components/auth/sign-in-buttons';
import { Lock } from 'lucide-react';

interface LessonGatedPlaceholderProps {
    thumbnailUrl?: string | null;
}

export function LessonGatedPlaceholder({
    thumbnailUrl,
}: LessonGatedPlaceholderProps) {
    return (
        <div className="relative mb-8 overflow-hidden rounded-xl bg-neutral-900">
            {thumbnailUrl && (
                <img
                    src={thumbnailUrl}
                    alt=""
                    className="absolute inset-0 size-full object-cover opacity-20 blur-sm"
                />
            )}
            <div className="relative flex aspect-video w-full flex-col items-center justify-center gap-4 text-white">
                <div className="rounded-full bg-neutral-800 p-4">
                    <Lock className="size-8 text-neutral-400" />
                </div>
                <h3 className="text-xl font-semibold">Log in to watch</h3>
                <p className="max-w-sm text-center text-sm text-neutral-400">
                    Sign in to access this lesson and track your progress.
                </p>
                <SignInButtons
                    description="Enter your email and password to access this lesson."
                    idPrefix="gated"
                />
            </div>
        </div>
    );
}
