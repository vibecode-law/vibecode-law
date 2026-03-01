import { SignInButtons } from '@/components/auth/sign-in-buttons';
import PublicLayout from '@/layouts/public-layout';
import { Head } from '@inertiajs/react';
import { Lock, Mail, Trophy } from 'lucide-react';

export default function InvitePage() {
    return (
        <PublicLayout>
            <Head title="Challenge Invite" />

            <section className="grid bg-white lg:min-h-[70vh] lg:place-items-center dark:bg-neutral-950">
                <div className="mx-auto w-full max-w-3xl px-4 py-16 lg:py-8">
                    <div className="overflow-hidden rounded-xl bg-neutral-900">
                        <div className="flex aspect-video w-full flex-col items-center justify-center gap-5 px-6 text-white">
                            <div className="flex items-center gap-3">
                                <div className="rounded-full bg-neutral-800 p-3">
                                    <Trophy className="size-5 text-neutral-500" />
                                </div>
                                <div className="rounded-full bg-neutral-800 p-4">
                                    <Mail className="size-8 text-neutral-400" />
                                </div>
                                <div className="rounded-full bg-neutral-800 p-3">
                                    <Lock className="size-5 text-neutral-500" />
                                </div>
                            </div>
                            <div className="text-center">
                                <h1 className="text-2xl font-bold">
                                    You&apos;ve Been Invited
                                </h1>
                                <p className="mt-2 max-w-sm text-neutral-400">
                                    Sign in to accept this invite and access the
                                    challenge.
                                </p>
                            </div>
                            <SignInButtons
                                description="Sign in to accept your invitation."
                                idPrefix="invite"
                            />
                        </div>
                    </div>
                </div>
            </section>
        </PublicLayout>
    );
}
