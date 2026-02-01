import LinkedinAuthRedirectController from '@/actions/App/Http/Controllers/Auth/LinkedinAuthRedirectController';
import AppLogo from '@/components/logo/app-logo';
import {
    Card,
    CardContent,
    CardDescription,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';
import { type PropsWithChildren } from 'react';

interface AuthLayoutProps {
    name?: string;
    title?: string;
    description?: string | React.ReactNode;
    showLinkedinLogin?: boolean;
}

export default function AuthCardLayout({
    children,
    title,
    description,
    showLinkedinLogin = true,
}: PropsWithChildren<AuthLayoutProps>) {
    return (
        <div className="flex min-h-svh flex-col items-center justify-center gap-6 bg-muted p-6 md:p-10">
            <div className="flex w-full max-w-md flex-col gap-6">
                <AppLogo className="mx-auto" />

                <div className="flex flex-col gap-6">
                    <Card className="rounded-xl">
                        <CardHeader className="px-10 pt-8 pb-0 text-center">
                            <CardTitle className="text-xl">{title}</CardTitle>
                            <CardDescription>{description}</CardDescription>
                        </CardHeader>
                        <CardContent className="px-10 pb-8">
                            <div
                                className={showLinkedinLogin ? 'divide-y' : ''}
                            >
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

                                <div
                                    className={showLinkedinLogin ? 'pt-8' : ''}
                                >
                                    {children}
                                </div>
                            </div>
                        </CardContent>
                    </Card>
                </div>
            </div>
        </div>
    );
}
