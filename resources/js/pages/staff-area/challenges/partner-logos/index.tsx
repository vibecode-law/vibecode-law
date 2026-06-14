import ChallengeLiveController from '@/actions/App/Http/Controllers/Challenge/Public/ChallengeLiveController';
import HeadingSmall from '@/components/heading/heading-small';
import {
    AlertDialog,
    AlertDialogAction,
    AlertDialogCancel,
    AlertDialogContent,
    AlertDialogDescription,
    AlertDialogFooter,
    AlertDialogHeader,
    AlertDialogTitle,
    AlertDialogTrigger,
} from '@/components/ui/alert-dialog';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { FormField } from '@/components/ui/form-field';
import { Input } from '@/components/ui/input';
import {
    ListCard,
    ListCardContent,
    ListCardEmpty,
    ListCardHeader,
    ListCardTitle,
} from '@/components/ui/list-card';
import { SortableItem } from '@/components/ui/sortable-item';
import { SortableList } from '@/components/ui/sortable-list';
import { SubmitButton } from '@/components/ui/submit-button';
import { Switch } from '@/components/ui/switch';
import StaffAreaLayout from '@/layouts/staff-area/layout';
import { cn } from '@/lib/utils';
import { edit } from '@/routes/staff/challenges';
import {
    destroy,
    reorder,
    store,
    update,
} from '@/routes/staff/challenges/partner-logos';
import { Form, Head, Link, router } from '@inertiajs/react';
import { ArrowLeft, ExternalLink, Trash2 } from 'lucide-react';
import { useState } from 'react';

type ChallengePartnerLogoResource =
    App.Http.Resources.Challenge.ChallengePartnerLogoResource;

interface PartnerLogosIndexProps {
    challenge: Pick<
        App.Http.Resources.Challenge.ChallengeResource,
        'id' | 'slug' | 'title' | 'live_view_enabled' | 'live_view_access_token'
    >;
    partnerLogos: ChallengePartnerLogoResource[];
}

function LogoRow({
    challengeSlug,
    logo,
}: {
    challengeSlug: string;
    logo: ChallengePartnerLogoResource;
}) {
    const [invertInDark, setInvertInDark] = useState(logo.invert_in_dark);

    const handleDelete = () => {
        router.delete(
            destroy.url({ challenge: challengeSlug, partnerLogo: logo.id }),
            { preserveScroll: true },
        );
    };

    return (
        <div className="flex flex-col gap-4 py-4 sm:flex-row sm:items-center">
            <div className="flex shrink-0 gap-2">
                <div className="flex size-20 items-center justify-center rounded-lg border border-neutral-200 bg-white p-2">
                    <img
                        src={logo.url}
                        alt={logo.filename}
                        className="max-h-full max-w-full object-contain"
                    />
                </div>
                <div className="flex size-20 items-center justify-center rounded-lg border border-neutral-700 bg-neutral-900 p-2">
                    <img
                        src={logo.url}
                        alt={logo.filename}
                        className={cn(
                            'max-h-full max-w-full object-contain',
                            invertInDark === true && 'invert',
                        )}
                    />
                </div>
            </div>

            <Form
                {...update.form({
                    challenge: challengeSlug,
                    partnerLogo: logo.id,
                })}
                options={{ preserveScroll: true }}
                className="flex min-w-0 flex-1 flex-col gap-3"
            >
                {({ processing, errors }) => (
                    <>
                        <FormField
                            label="Link (optional)"
                            htmlFor={`href-${logo.id}`}
                            error={errors.href}
                            optional
                        >
                            <Input
                                id={`href-${logo.id}`}
                                name="href"
                                type="url"
                                defaultValue={logo.href ?? ''}
                                placeholder="https://partner.example"
                                disabled={processing}
                            />
                        </FormField>
                        <div className="flex items-center justify-between gap-4">
                            <label className="flex cursor-pointer items-center gap-2">
                                <Switch
                                    checked={invertInDark}
                                    onCheckedChange={setInvertInDark}
                                    disabled={processing}
                                />
                                <span className="text-sm">
                                    Invert in dark mode
                                </span>
                            </label>
                            <input
                                type="hidden"
                                name="invert_in_dark"
                                value={invertInDark ? '1' : '0'}
                            />
                            <SubmitButton processing={processing} size="sm">
                                Save
                            </SubmitButton>
                        </div>
                    </>
                )}
            </Form>

            <AlertDialog>
                <AlertDialogTrigger asChild>
                    <Button
                        variant="outline"
                        size="sm"
                        className="shrink-0 gap-1.5 text-red-600 hover:text-red-700 dark:text-red-400"
                    >
                        <Trash2 className="size-4" />
                        Delete
                    </Button>
                </AlertDialogTrigger>
                <AlertDialogContent>
                    <AlertDialogHeader>
                        <AlertDialogTitle>Delete logo?</AlertDialogTitle>
                        <AlertDialogDescription>
                            <strong>{logo.filename}</strong> will be removed
                            from the live view. This cannot be undone.
                        </AlertDialogDescription>
                    </AlertDialogHeader>
                    <AlertDialogFooter>
                        <AlertDialogCancel>Cancel</AlertDialogCancel>
                        <AlertDialogAction onClick={handleDelete}>
                            Delete
                        </AlertDialogAction>
                    </AlertDialogFooter>
                </AlertDialogContent>
            </AlertDialog>
        </div>
    );
}

export default function PartnerLogosIndex({
    challenge,
    partnerLogos,
}: PartnerLogosIndexProps) {
    const [localLogos, setLocalLogos] = useState(partnerLogos);
    const [prevLogos, setPrevLogos] = useState(partnerLogos);

    if (partnerLogos !== prevLogos) {
        setPrevLogos(partnerLogos);
        setLocalLogos(partnerLogos);
    }

    const handleReorder = (reorderedItems: ChallengePartnerLogoResource[]) => {
        setLocalLogos(reorderedItems);

        router.post(
            reorder.url({ challenge: challenge.slug }),
            {
                items: reorderedItems.map((item) => ({
                    id: item.id,
                    order: item.order,
                })),
            },
            { preserveScroll: true, preserveState: true, only: [] },
        );
    };

    const liveUrl = ChallengeLiveController.url(
        { challenge: challenge.slug },
        challenge.live_view_access_token !== null &&
            challenge.live_view_access_token !== undefined
            ? { query: { key: challenge.live_view_access_token } }
            : undefined,
    );

    return (
        <StaffAreaLayout fullWidth>
            <Head title={`Partner logos - ${challenge.title}`} />

            <div className="space-y-6">
                <div className="flex items-center gap-4">
                    <Button variant="ghost" size="sm" asChild>
                        <Link href={edit.url({ challenge: challenge.slug })}>
                            <ArrowLeft className="mr-1.5 size-4" />
                            Back to {challenge.title}
                        </Link>
                    </Button>
                </div>

                <div className="flex items-center justify-between gap-4">
                    <HeadingSmall
                        title={`Partner logos for ${challenge.title}`}
                        description="Shown in the footer of the live leaderboard. Drag to reorder."
                    />
                    {challenge.live_view_enabled === true && (
                        <Button variant="outline" asChild>
                            <a
                                href={liveUrl}
                                target="_blank"
                                rel="noopener noreferrer"
                            >
                                <ExternalLink className="mr-1.5 size-4" />
                                Open live view
                            </a>
                        </Button>
                    )}
                </div>

                <div className="rounded-lg border bg-white p-6 dark:border-neutral-800 dark:bg-neutral-900">
                    <h3 className="mb-4 text-sm font-medium">Upload logos</h3>
                    <Form
                        {...store.form({ challenge: challenge.slug })}
                        resetOnSuccess
                        encType="multipart/form-data"
                        options={{ preserveScroll: true }}
                        className="space-y-3"
                    >
                        {({ processing, errors }) => (
                            <>
                                <FormField
                                    label="Logo images"
                                    htmlFor="logos"
                                    error={errors.logos ?? errors['logos.0']}
                                >
                                    <Input
                                        id="logos"
                                        name="logos[]"
                                        type="file"
                                        accept="image/png,image/jpeg,image/gif,image/webp"
                                        multiple
                                        disabled={processing}
                                    />
                                    <p className="mt-1 text-sm text-neutral-500 dark:text-neutral-400">
                                        PNG, JPG, GIF or WebP, up to 2MB each.
                                        Transparent PNGs work best.
                                    </p>
                                </FormField>
                                <SubmitButton processing={processing}>
                                    Upload
                                </SubmitButton>
                            </>
                        )}
                    </Form>
                </div>

                <ListCard>
                    <ListCardHeader>
                        <ListCardTitle>Logos</ListCardTitle>
                        <Badge variant="secondary">
                            {localLogos.length}{' '}
                            {localLogos.length === 1 ? 'logo' : 'logos'}
                        </Badge>
                    </ListCardHeader>

                    {localLogos.length > 0 ? (
                        <ListCardContent>
                            <SortableList
                                items={localLogos}
                                onReorder={handleReorder}
                                orderKey="order"
                            >
                                {(logo) => (
                                    <SortableItem key={logo.id} id={logo.id}>
                                        <LogoRow
                                            challengeSlug={challenge.slug}
                                            logo={logo}
                                        />
                                    </SortableItem>
                                )}
                            </SortableList>
                        </ListCardContent>
                    ) : (
                        <ListCardEmpty>
                            No partner logos have been uploaded yet.
                        </ListCardEmpty>
                    )}
                </ListCard>
            </div>
        </StaffAreaLayout>
    );
}
