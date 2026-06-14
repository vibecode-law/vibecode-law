import QRCode from 'qrcode';
import { useEffect, useState } from 'react';

interface LiveQrCodeProps {
    url: string;
    /** Foreground (dark) module colour. */
    dark: string;
    /** Background (light) colour. */
    light: string;
    size?: number;
}

export function LiveQrCode({ url, dark, light, size = 160 }: LiveQrCodeProps) {
    const [dataUrl, setDataUrl] = useState<string | null>(null);

    useEffect(() => {
        let cancelled = false;

        QRCode.toDataURL(url, {
            width: size,
            margin: 1,
            color: { dark, light },
        })
            .then((result) => {
                if (cancelled === false) {
                    setDataUrl(result);
                }
            })
            .catch(() => {
                if (cancelled === false) {
                    setDataUrl(null);
                }
            });

        return () => {
            cancelled = true;
        };
    }, [url, dark, light, size]);

    if (dataUrl === null) {
        return (
            <div
                className="animate-pulse rounded-lg bg-neutral-200 dark:bg-neutral-800"
                style={{ width: size, height: size }}
            />
        );
    }

    return (
        <img
            src={dataUrl}
            alt="Scan to open this challenge and vote"
            width={size}
            height={size}
            className="rounded-lg"
        />
    );
}
