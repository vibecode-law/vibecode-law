import { router } from '@inertiajs/react';
import { useEffect } from 'react';

import { toast } from '@/hooks/use-toast';
import { type FlashData } from '@/types';

export function useFlashToasts() {
    useEffect(() => {
        return router.on('success', (event) => {
            const flash = event.detail.page.props.flash as
                FlashData | undefined;

            if (flash?.message === undefined || flash.message === null) {
                return;
            }

            toast({
                variant: flash.message.type,
                description: flash.message.message,
            });
        });
    }, []);
}
