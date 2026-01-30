declare namespace App.Enums {
    export type MarkdownProfile = 'basic' | 'full';
    export type ShowcaseDraftStatus = 1 | 2 | 3;
    export type ShowcaseStatus = 1 | 2 | 3 | 4;
    export type SourceStatus = 1 | 2 | 3;
    export type TeamType = 1 | 2;
}
declare namespace App.Http.Resources {
    export type PracticeAreaResource = {
        id: number;
        name: string;
        slug: string;
        showcases_count?: number;
    };
}
declare namespace App.Http.Resources.Showcase {
    export type ShowcaseDraftImageResource = {
        id: number;
        original_image_id: number | null;
        action: string;
        filename: string | null;
        order: number;
        alt_text: string | null;
        url: string | null;
    };
    export type ShowcaseDraftResource = {
        id: number;
        showcase_id: number;
        showcase_slug: string;
        showcase_title: string;
        title: string;
        tagline: string;
        description?: string;
        key_features?: string | null;
        help_needed?: string | null;
        url: string | null;
        video_url: string | null;
        source_status: App.ValueObjects.FrontendEnum;
        source_url: string | null;
        status: App.ValueObjects.FrontendEnum;
        created_at: string;
        updated_at: string;
        practiceAreas?: App.Http.Resources.PracticeAreaResource;
        thumbnail_url: string | null;
        thumbnail_rect_string: string | null;
        thumbnail_crop?: App.ValueObjects.ImageCrop | null;
        images?: App.Http.Resources.Showcase.ShowcaseDraftImageResource;
        submitted_at: string | null;
        rejection_reason?: string | null;
        user?: App.Http.Resources.User.UserResource;
    };
    export type ShowcaseImageResource = {
        id: number;
        filename: string;
        order: number;
        alt_text: string | null;
        url: string;
    };
    export type ShowcaseResource = {
        id: number;
        slug: string;
        title: string;
        tagline: string;
        description?: string;
        description_html?: string;
        key_features?: string | null;
        key_features_html?: string | null;
        help_needed?: string | null;
        help_needed_html?: string | null;
        url: string | null;
        video_url: string | null;
        source_status: App.ValueObjects.FrontendEnum;
        source_url: string | null;
        status: App.ValueObjects.FrontendEnum;
        view_count?: number | null;
        created_at: string;
        updated_at: string;
        user?: App.Http.Resources.User.UserResource | null;
        practiceAreas?: App.Http.Resources.PracticeAreaResource;
        thumbnail_url: string | null;
        thumbnail_rect_string: string | null;
        thumbnail_crop?: App.ValueObjects.ImageCrop | null;
        images?: App.Http.Resources.Showcase.ShowcaseImageResource;
        images_count?: number;
        upvotes_count?: number;
        has_upvoted?: boolean;
        submitted_date: string | null;
        rejection_reason?: string | null;
        approved_at?: string | null;
        is_featured?: boolean;
        approvedBy?: App.Http.Resources.User.UserResource | null;
        show_approval_celebration?: boolean;
        linkedin_share_url?: string | null;
        has_draft?: boolean;
        draft_id?: number | null;
        draft_status?: App.ValueObjects.FrontendEnum | null;
        youtube_id?: string | null;
    };
}
declare namespace App.Http.Resources.User {
    export type AdminUserResource = {
        id: number;
        first_name: string;
        last_name: string;
        handle: string;
        organisation: string | null;
        job_title: string | null;
        avatar: string | null;
        linkedin_url: string | null;
        bio: string | null;
        email: string;
        is_admin: boolean;
        blocked_from_submissions_at: string | null;
        created_at: string;
        team_type: App.Enums.TeamType | null;
        team_role: string | null;
        roles: Array<string>;
        showcases_count?: number;
    };
    export type PrivateUserResource = {
        id: number;
        first_name: string;
        last_name: string;
        handle: string;
        organisation: string | null;
        job_title: string | null;
        avatar: string | null;
        linkedin_url: string | null;
        bio: string | null;
        email: string;
        email_verified_at: string | null;
    };
    export type UserResource = {
        id?: number;
        first_name: string;
        last_name: string;
        handle: string;
        organisation: string | null;
        job_title: string | null;
        avatar: string | null;
        linkedin_url: string | null;
        team_role: string | null;
        bio?: string | null;
        bio_html?: string | null;
    };
}
declare namespace App.ValueObjects {
    export type FrontendEnum = {
        value: string;
        label: string;
        name: string | null;
    };
    export type ImageCrop = {
        x: number;
        y: number;
        width: number;
        height: number;
    };
}
