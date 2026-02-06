import { Button } from '@/components/ui/button';
import { Checkbox } from '@/components/ui/checkbox';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
    DialogTrigger,
} from '@/components/ui/dialog';
import { FormField } from '@/components/ui/form-field';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { SubmitButton } from '@/components/ui/submit-button';
import { Textarea } from '@/components/ui/textarea';
import { UserSearchSelect } from '@/components/ui/user-search-select';
import { useModalForm } from '@/hooks/use-modal-form';
import { router } from '@inertiajs/react';
import { Plus } from 'lucide-react';
import { useRef, useState } from 'react';

interface CreateTestimonialModalProps {
    storeUrl: string;
}

export function CreateTestimonialModal({
    storeUrl,
}: CreateTestimonialModalProps) {
    const {
        isOpen,
        handleOpenChange: baseHandleOpenChange,
        isSubmitting,
        setIsSubmitting,
        errors,
        setErrors,
    } = useModalForm<{
        name?: string;
        job_title?: string;
        organisation?: string;
        content?: string;
        avatar?: string;
        display_order?: string;
    }>();

    const [selectedUser, setSelectedUser] = useState<{
        id: number;
        name: string;
        email: string;
        job_title?: string | null;
        organisation?: string | null;
    } | null>(null);
    const [name, setName] = useState('');
    const [jobTitle, setJobTitle] = useState('');
    const [organisation, setOrganisation] = useState('');
    const [content, setContent] = useState('');
    const [displayOrder, setDisplayOrder] = useState('0');
    const [isPublished, setIsPublished] = useState(false);
    const [avatarFile, setAvatarFile] = useState<File | null>(null);
    const fileInputRef = useRef<HTMLInputElement>(null);

    const handleAvatarChange = (e: React.ChangeEvent<HTMLInputElement>) => {
        const file = e.target.files?.[0];
        if (file !== undefined) {
            setAvatarFile(file);
        }
    };

    const handleSubmit = (e: React.FormEvent) => {
        e.preventDefault();

        setIsSubmitting(true);
        setErrors({});

        const formData = new FormData();

        if (selectedUser) {
            formData.append('user_id', selectedUser.id.toString());
        } else {
            formData.append('name', name);
            formData.append('job_title', jobTitle);
            formData.append('organisation', organisation);
            if (avatarFile !== null) {
                formData.append('avatar', avatarFile);
            }
        }

        formData.append('content', content);
        formData.append('display_order', displayOrder);
        formData.append('is_published', isPublished ? '1' : '0');

        router.post(storeUrl, formData, {
            onSuccess: () => {
                baseHandleOpenChange(false);
                setSelectedUser(null);
                setName('');
                setJobTitle('');
                setOrganisation('');
                setContent('');
                setDisplayOrder('0');
                setIsPublished(false);
                setAvatarFile(null);
                if (fileInputRef.current !== null) {
                    fileInputRef.current.value = '';
                }
            },
            onError: (newErrors) => {
                setErrors(
                    newErrors as {
                        name?: string;
                        job_title?: string;
                        organisation?: string;
                        content?: string;
                        avatar?: string;
                        display_order?: string;
                    },
                );
            },
            onFinish: () => {
                setIsSubmitting(false);
            },
        });
    };

    const handleOpenChange = (open: boolean) => {
        if (open === false) {
            setSelectedUser(null);
            setName('');
            setJobTitle('');
            setOrganisation('');
            setContent('');
            setDisplayOrder('0');
            setIsPublished(false);
            setAvatarFile(null);
            if (fileInputRef.current !== null) {
                fileInputRef.current.value = '';
            }
        }
        baseHandleOpenChange(open);
    };

    return (
        <Dialog open={isOpen} onOpenChange={handleOpenChange}>
            <DialogTrigger asChild>
                <Button>
                    <Plus className="size-4" />
                    Add Testimonial
                </Button>
            </DialogTrigger>
            <DialogContent className="max-h-[90vh] overflow-y-auto">
                <form onSubmit={handleSubmit}>
                    <DialogHeader>
                        <DialogTitle>Create Testimonial</DialogTitle>
                        <DialogDescription>
                            Add a new testimonial to the Wall of Love.
                        </DialogDescription>
                    </DialogHeader>

                    <div className="mt-4 space-y-4">
                        <UserSearchSelect
                            selectedUser={selectedUser}
                            onSelect={setSelectedUser}
                            disabled={isSubmitting}
                        />

                        {!selectedUser && (
                            <>
                                <FormField
                                    label="Name"
                                    htmlFor="name"
                                    error={errors.name}
                                >
                                    <Input
                                        id="name"
                                        value={name}
                                        onChange={(e) =>
                                            setName(e.target.value)
                                        }
                                        placeholder="e.g. Jane Doe"
                                        disabled={isSubmitting}
                                        aria-invalid={
                                            errors.name !== undefined
                                                ? true
                                                : undefined
                                        }
                                    />
                                </FormField>

                                <FormField
                                    label="Job Title"
                                    htmlFor="job_title"
                                    error={errors.job_title}
                                >
                                    <Input
                                        id="job_title"
                                        value={jobTitle}
                                        onChange={(e) =>
                                            setJobTitle(e.target.value)
                                        }
                                        placeholder="e.g. Senior Associate"
                                        disabled={isSubmitting}
                                        aria-invalid={
                                            errors.job_title !== undefined
                                                ? true
                                                : undefined
                                        }
                                    />
                                </FormField>

                                <FormField
                                    label="Organisation"
                                    htmlFor="organisation"
                                    error={errors.organisation}
                                >
                                    <Input
                                        id="organisation"
                                        value={organisation}
                                        onChange={(e) =>
                                            setOrganisation(e.target.value)
                                        }
                                        placeholder="e.g. ABC Law Firm"
                                        disabled={isSubmitting}
                                        aria-invalid={
                                            errors.organisation !== undefined
                                                ? true
                                                : undefined
                                        }
                                    />
                                </FormField>

                                <FormField
                                    label="Avatar"
                                    htmlFor="avatar"
                                    error={errors.avatar}
                                >
                                    <Input
                                        ref={fileInputRef}
                                        id="avatar"
                                        type="file"
                                        accept="image/png,image/jpg,image/jpeg,image/gif,image/webp"
                                        onChange={handleAvatarChange}
                                        disabled={isSubmitting}
                                        aria-invalid={
                                            errors.avatar !== undefined
                                                ? true
                                                : undefined
                                        }
                                    />
                                    <p className="mt-1 text-xs text-neutral-500 dark:text-neutral-400">
                                        Optional. Max 2MB. Formats: PNG, JPG,
                                        GIF, WEBP
                                    </p>
                                </FormField>
                            </>
                        )}

                        <FormField
                            label="Testimonial Content"
                            htmlFor="content"
                            error={errors.content}
                            required
                        >
                            <Textarea
                                id="content"
                                value={content}
                                onChange={(e) => setContent(e.target.value)}
                                placeholder="Enter the testimonial quote..."
                                disabled={isSubmitting}
                                rows={4}
                                aria-invalid={
                                    errors.content !== undefined
                                        ? true
                                        : undefined
                                }
                            />
                        </FormField>

                        <div className="flex items-center justify-between">
                            <Label htmlFor="is_published">
                                Publish Immediately
                            </Label>
                            <Checkbox
                                id="is_published"
                                checked={isPublished}
                                onCheckedChange={(checked) =>
                                    setIsPublished(checked === true)
                                }
                                disabled={isSubmitting}
                            />
                        </div>
                    </div>

                    <DialogFooter className="mt-6">
                        <Button
                            type="button"
                            variant="outline"
                            onClick={() => handleOpenChange(false)}
                            disabled={isSubmitting}
                        >
                            Cancel
                        </Button>
                        <SubmitButton
                            processing={isSubmitting}
                            processingLabel="Creating..."
                        >
                            Create
                        </SubmitButton>
                    </DialogFooter>
                </form>
            </DialogContent>
        </Dialog>
    );
}
