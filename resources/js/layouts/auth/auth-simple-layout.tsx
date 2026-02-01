import LinkedinAuthRedirectController from '@/actions/App/Http/Controllers/Auth/LinkedinAuthRedirectController';
import AppLogo from '@/components/logo/app-logo';
import { type PropsWithChildren } from 'react';

interface AuthLayoutProps {
    name?: string;
    title?: string;
    description?: string | React.ReactNode;
    showLinkedinLogin?: boolean;
}

export default function AuthSimpleLayout({
    children,
    title,
    description,
    showLinkedinLogin = true,
}: PropsWithChildren<AuthLayoutProps>) {
    return (
        <div className="flex min-h-svh flex-col items-center justify-center gap-6 bg-background p-6 md:p-10">
            <div className="w-full max-w-sm">
                <div className="flex flex-col gap-7">
                    <div className="flex flex-col items-center gap-4">
                        <AppLogo />

                        <div className="space-y-3 text-center">
                            <h1 className="text-xl font-medium">{title}</h1>
                            {description && (
                                <p className="text-center text-sm text-muted-foreground">
                                    {description}
                                </p>
                            )}
                        </div>
                    </div>
                    <div className={showLinkedinLogin ? 'divide-y' : ''}>
                        {showLinkedinLogin && (
                            <div className="pb-8 text-center">
                                <a
                                    href={LinkedinAuthRedirectController.url()}
                                    className="inline-block cursor-pointer hover:brightness-90"
                                >
                                    <img
                                        src="/static/sign-in-with-linkedin.png"
                                        alt="Login with Linkedin"
                                    />
                                </a>
                            </div>
                        )}

                        <div className={showLinkedinLogin ? 'pt-8' : ''}>
                            {children}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    );
}
