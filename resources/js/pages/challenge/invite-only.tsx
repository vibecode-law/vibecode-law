import PublicLayout from '@/layouts/public-layout';
import { home } from '@/routes';
import { Head, router } from '@inertiajs/react';
import { Lock, Shield, UserX } from 'lucide-react';

export default function InviteOnlyPage() {
    const handleGoBack = () => {
        if (window.history.length > 1) {
            window.history.back();
        } else {
            router.visit(home());
        }
    };

    return (
        <PublicLayout>
            <Head title="Invite Only Challenge" />

            <section className="grid bg-white lg:min-h-[70vh] lg:place-items-center dark:bg-neutral-950">
                <div className="mx-auto w-full max-w-3xl px-4 py-16 lg:py-8">
                    <div className="overflow-hidden rounded-xl bg-neutral-900">
                        <div className="flex aspect-video w-full flex-col items-center justify-center gap-5 px-6 text-white">
                            <div className="flex items-center gap-3">
                                <div className="rounded-full bg-neutral-800 p-3">
                                    <Shield className="size-5 text-neutral-500" />
                                </div>
                                <div className="rounded-full bg-neutral-800 p-4">
                                    <Lock className="size-8 text-neutral-400" />
                                </div>
                                <div className="rounded-full bg-neutral-800 p-3">
                                    <UserX className="size-5 text-neutral-500" />
                                </div>
                            </div>
                            <div className="text-center">
                                <h1 className="text-2xl font-bold">
                                    Invite Only
                                </h1>
                                <p className="mt-2 max-w-sm text-neutral-400">
                                    This challenge is private. You need an
                                    invitation to access it.
                                </p>
                            </div>
                            <p className="max-w-sm text-center text-sm text-neutral-500">
                                Contact the organiser to request an invite link.
                            </p>
                            <button
                                type="button"
                                onClick={handleGoBack}
                                className="mt-1 inline-flex items-center gap-2 rounded-md border border-neutral-700 px-4 py-2 text-sm font-medium text-neutral-300 transition-colors hover:bg-neutral-800"
                            >
                                Go back
                            </button>
                        </div>
                    </div>
                </div>
            </section>
        </PublicLayout>
    );
}
