import { InertiaLinkProps } from '@inertiajs/react';
import { LucideIcon } from 'lucide-react';

export interface Auth {
    user: App.Http.Resources.User.PrivateUserResource;
    permissions: string[];
}

export interface BreadcrumbItem {
    title: string;
    href: string;
}

export interface NavGroup {
    title: string;
    items: NavItem[];
}

export interface NavItem {
    title: string;
    href: NonNullable<InertiaLinkProps['href']>;
    icon?: LucideIcon | null;
    isActive?: boolean;
}

export interface FlashMessage {
    message: string;
    type: 'success' | 'error' | 'warning' | 'info';
}

export interface FlashData {
    message?: FlashMessage | null;
}

export interface LegalPage {
    title: string;
    route: string;
}

export interface SharedData {
    name: string;
    appUrl: string;
    defaultMetaDescription: string;
    auth: Auth;
    flash: FlashData;
    legalPages: LegalPage[];
    transformImages: boolean;
    [key: string]: unknown;
}

export interface User {
    id: number;
    first_name: string;
    last_name: string;
    organisation?: string | null;
    job_title?: string | null;
    linkedin_url?: string | null;
    bio?: string | null;
    email: string;
    avatar?: string;
    email_verified_at: string | null;
    is_admin: boolean;
    two_factor_enabled?: boolean;
    created_at: string;
    updated_at: string;
    [key: string]: unknown; // This allows for additional properties...
}

export interface FrontendEnum<T = number | string> {
    name: string | null;
    value: T;
    label: string;
}

export interface PaginatedData<T> {
    data: T[];
    links: {
        first: string | null;
        last: string | null;
        prev: string | null;
        next: string | null;
    };
    meta: {
        current_page: number;
        from: number | null;
        last_page: number;
        path: string;
        per_page: number;
        to: number | null;
        total: number;
        links: Array<{
            url: string | null;
            label: string;
            active: boolean;
        }>;
    };
}
