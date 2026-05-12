import { type SharedData } from '@/types';
import { usePage } from '@inertiajs/react';

export function AnnouncementBanner() {
    const { announcement } = usePage<SharedData>().props;

    if (announcement === null) {
        return null;
    }

    return (
        <div className="border-b border-black/10 bg-brand text-white shadow-md dark:border-white/10">
            <div className="mx-auto max-w-6xl px-4 py-2.5 text-center font-semibold tracking-tight">
                <div
                    className="prose-sm inline max-w-none [&_a]:underline [&_a]:decoration-dashed [&_a]:decoration-2 [&_a]:underline-offset-3 [&_a:hover]:text-white/80 [&_p]:inline"
                    dangerouslySetInnerHTML={{ __html: announcement }}
                />
            </div>
        </div>
    );
}
