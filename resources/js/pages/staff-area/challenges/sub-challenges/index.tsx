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
import { Textarea } from '@/components/ui/textarea';
import StaffAreaLayout from '@/layouts/staff-area/layout';
import { edit } from '@/routes/staff/challenges';
import {
    destroy,
    reorder,
    store,
    update,
} from '@/routes/staff/challenges/sub-challenges';
import { Form, Head, Link, router } from '@inertiajs/react';
import { ArrowLeft, Pencil, Trash2 } from 'lucide-react';
import { useState } from 'react';

type SubChallengeResource = App.Http.Resources.Challenge.SubChallengeResource;

interface SubChallengesIndexProps {
    challenge: Pick<
        App.Http.Resources.Challenge.ChallengeResource,
        'id' | 'slug' | 'title'
    >;
    subChallenges: SubChallengeResource[];
}

function SubChallengeRow({
    challengeSlug,
    subChallenge,
}: {
    challengeSlug: string;
    subChallenge: SubChallengeResource;
}) {
    const [isEditing, setIsEditing] = useState(false);

    const handleDelete = () => {
        router.delete(
            destroy.url({
                challenge: challengeSlug,
                subChallenge: subChallenge.id,
            }),
            { preserveScroll: true },
        );
    };

    if (isEditing === true) {
        return (
            <div className="py-4">
                <Form
                    {...update.form({
                        challenge: challengeSlug,
                        subChallenge: subChallenge.id,
                    })}
                    onSuccess={() => setIsEditing(false)}
                    options={{ preserveScroll: true }}
                    className="space-y-3"
                >
                    {({ processing, errors }) => (
                        <>
                            <FormField
                                label="Name"
                                htmlFor={`name-${subChallenge.id}`}
                                error={errors.name}
                                required
                            >
                                <Input
                                    id={`name-${subChallenge.id}`}
                                    name="name"
                                    defaultValue={subChallenge.name}
                                    disabled={processing}
                                    aria-invalid={
                                        errors.name !== undefined
                                            ? true
                                            : undefined
                                    }
                                />
                            </FormField>
                            <FormField
                                label="Tagline"
                                htmlFor={`tagline-${subChallenge.id}`}
                                error={errors.tagline}
                                required
                            >
                                <Input
                                    id={`tagline-${subChallenge.id}`}
                                    name="tagline"
                                    defaultValue={subChallenge.tagline}
                                    disabled={processing}
                                    aria-invalid={
                                        errors.tagline !== undefined
                                            ? true
                                            : undefined
                                    }
                                />
                            </FormField>
                            <FormField
                                label="Description"
                                htmlFor={`description-${subChallenge.id}`}
                                error={errors.description}
                                optional
                            >
                                <Textarea
                                    id={`description-${subChallenge.id}`}
                                    name="description"
                                    defaultValue={
                                        subChallenge.description ?? ''
                                    }
                                    rows={3}
                                    disabled={processing}
                                />
                            </FormField>
                            <div className="flex items-center gap-2">
                                <SubmitButton processing={processing}>
                                    Save
                                </SubmitButton>
                                <Button
                                    type="button"
                                    variant="ghost"
                                    onClick={() => setIsEditing(false)}
                                >
                                    Cancel
                                </Button>
                            </div>
                        </>
                    )}
                </Form>
            </div>
        );
    }

    return (
        <div className="flex items-center gap-4 py-4">
            <div className="min-w-0 flex-1">
                <h3 className="font-semibold text-neutral-900 dark:text-white">
                    {subChallenge.name}
                </h3>
                <p className="text-sm text-neutral-500 dark:text-neutral-400">
                    {subChallenge.tagline}
                </p>
            </div>

            <div className="flex shrink-0 items-center gap-2">
                <Button
                    variant="outline"
                    size="sm"
                    className="gap-1.5"
                    onClick={() => setIsEditing(true)}
                >
                    <Pencil className="size-4" />
                    Edit
                </Button>
                <AlertDialog>
                    <AlertDialogTrigger asChild>
                        <Button
                            variant="outline"
                            size="sm"
                            className="gap-1.5 text-red-600 hover:text-red-700 dark:text-red-400"
                        >
                            <Trash2 className="size-4" />
                            Delete
                        </Button>
                    </AlertDialogTrigger>
                    <AlertDialogContent>
                        <AlertDialogHeader>
                            <AlertDialogTitle>
                                Delete sub-challenge?
                            </AlertDialogTitle>
                            <AlertDialogDescription>
                                Entries assigned to{' '}
                                <strong>{subChallenge.name}</strong> will be
                                unassigned, but will remain in the challenge.
                                This cannot be undone.
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
        </div>
    );
}

export default function SubChallengesIndex({
    challenge,
    subChallenges,
}: SubChallengesIndexProps) {
    const [localSubChallenges, setLocalSubChallenges] = useState(subChallenges);
    const [prevSubChallenges, setPrevSubChallenges] = useState(subChallenges);

    if (subChallenges !== prevSubChallenges) {
        setPrevSubChallenges(subChallenges);
        setLocalSubChallenges(subChallenges);
    }

    const handleReorder = (reorderedItems: SubChallengeResource[]) => {
        setLocalSubChallenges(reorderedItems);

        router.post(
            reorder.url({ challenge: challenge.slug }),
            {
                items: reorderedItems.map((item) => ({
                    id: item.id,
                    order: item.order,
                })),
            },
            {
                preserveScroll: true,
                preserveState: true,
                only: [],
            },
        );
    };

    return (
        <StaffAreaLayout fullWidth>
            <Head title={`Sub-challenges - ${challenge.title}`} />

            <div className="space-y-6">
                <div className="flex items-center gap-4">
                    <Button variant="ghost" size="sm" asChild>
                        <Link href={edit.url({ challenge: challenge.slug })}>
                            <ArrowLeft className="mr-1.5 size-4" />
                            Back to {challenge.title}
                        </Link>
                    </Button>
                </div>

                <HeadingSmall
                    title={`Sub-challenges for ${challenge.title}`}
                    description="Drag and drop to reorder. Tabs appear on the public challenge page in this order."
                />

                <div className="rounded-lg border bg-white p-6 dark:border-neutral-800 dark:bg-neutral-900">
                    <h3 className="mb-4 text-sm font-medium">
                        Add Sub-challenge
                    </h3>
                    <Form
                        {...store.form({ challenge: challenge.slug })}
                        resetOnSuccess
                        options={{ preserveScroll: true }}
                        className="space-y-3"
                    >
                        {({ processing, errors }) => (
                            <>
                                <FormField
                                    label="Name"
                                    htmlFor="name"
                                    error={errors.name}
                                    required
                                >
                                    <Input
                                        id="name"
                                        name="name"
                                        placeholder="e.g. Best Solo Project"
                                        disabled={processing}
                                        aria-invalid={
                                            errors.name !== undefined
                                                ? true
                                                : undefined
                                        }
                                    />
                                </FormField>
                                <FormField
                                    label="Tagline"
                                    htmlFor="tagline"
                                    error={errors.tagline}
                                    required
                                >
                                    <Input
                                        id="tagline"
                                        name="tagline"
                                        placeholder="A short description shown under the tab"
                                        disabled={processing}
                                        aria-invalid={
                                            errors.tagline !== undefined
                                                ? true
                                                : undefined
                                        }
                                    />
                                </FormField>
                                <FormField
                                    label="Description"
                                    htmlFor="description"
                                    error={errors.description}
                                    optional
                                >
                                    <Textarea
                                        id="description"
                                        name="description"
                                        placeholder="Optional longer description"
                                        rows={3}
                                        disabled={processing}
                                    />
                                </FormField>
                                <SubmitButton processing={processing}>
                                    Add Sub-challenge
                                </SubmitButton>
                            </>
                        )}
                    </Form>
                </div>

                <ListCard>
                    <ListCardHeader>
                        <ListCardTitle>Sub-challenges</ListCardTitle>
                        <Badge variant="secondary">
                            {localSubChallenges.length}{' '}
                            {localSubChallenges.length === 1
                                ? 'sub-challenge'
                                : 'sub-challenges'}
                        </Badge>
                    </ListCardHeader>

                    {localSubChallenges.length > 0 ? (
                        <ListCardContent>
                            <SortableList
                                items={localSubChallenges}
                                onReorder={handleReorder}
                                orderKey="order"
                            >
                                {(subChallenge) => (
                                    <SortableItem
                                        key={subChallenge.id}
                                        id={subChallenge.id}
                                    >
                                        <SubChallengeRow
                                            challengeSlug={challenge.slug}
                                            subChallenge={subChallenge}
                                        />
                                    </SortableItem>
                                )}
                            </SortableList>
                        </ListCardContent>
                    ) : (
                        <ListCardEmpty>
                            No sub-challenges have been created yet.
                        </ListCardEmpty>
                    )}
                </ListCard>
            </div>
        </StaffAreaLayout>
    );
}
