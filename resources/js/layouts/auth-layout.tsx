import AuthLayoutTemplate from '@/layouts/auth/auth-card-layout';

export default function AuthLayout({
    children,
    title,
    description,
    showLinkedinLogin,
}: {
    children: React.ReactNode;
    title: string;
    description?: string | React.ReactNode;
    showLinkedinLogin?: boolean;
}) {
    return (
        <AuthLayoutTemplate
            title={title}
            description={description}
            showLinkedinLogin={showLinkedinLogin}
        >
            {children}
        </AuthLayoutTemplate>
    );
}
