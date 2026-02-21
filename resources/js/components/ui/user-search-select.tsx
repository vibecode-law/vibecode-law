import UsersController from '@/actions/App/Http/Controllers/Api/UsersController';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Search, X } from 'lucide-react';
import { type ReactNode, useEffect, useRef, useState } from 'react';

interface User {
    id: number;
    name: string;
    email?: string | null;
    job_title?: string | null;
    organisation?: string | null;
}

interface UserSearchSelectProps {
    selectedUser: User | null;
    onSelect: (user: User | null) => void;
    disabled?: boolean;
    label?: ReactNode;
    selectedHelpText?: string;
    searchHelpText?: string;
}

export function UserSearchSelect({
    selectedUser,
    onSelect,
    disabled = false,
    label = 'Link to User (Optional)',
    selectedHelpText = 'Name, job title, organisation, and avatar will be pulled from this user profile',
    searchHelpText = 'Search for a user to link this testimonial to their profile, or leave empty to enter details manually',
}: UserSearchSelectProps) {
    const [search, setSearch] = useState('');
    const [users, setUsers] = useState<User[]>([]);
    const [isOpen, setIsOpen] = useState(false);
    const [isLoading, setIsLoading] = useState(false);
    const searchTimeoutRef = useRef<NodeJS.Timeout | undefined>(undefined);
    const containerRef = useRef<HTMLDivElement>(null);

    useEffect(() => {
        const handleClickOutside = (event: MouseEvent) => {
            if (
                containerRef.current &&
                !containerRef.current.contains(event.target as Node)
            ) {
                setIsOpen(false);
            }
        };

        document.addEventListener('mousedown', handleClickOutside);
        return () =>
            document.removeEventListener('mousedown', handleClickOutside);
    }, []);

    useEffect(() => {
        if (!isOpen || search.length < 2) {
            return;
        }

        if (searchTimeoutRef.current) {
            clearTimeout(searchTimeoutRef.current);
        }

        searchTimeoutRef.current = setTimeout(() => {
            setIsLoading(true);
            fetch(UsersController.url({ query: { search } }))
                .then((res) => res.json())
                .then((data) => {
                    setUsers(data);
                    setIsLoading(false);
                })
                .catch(() => {
                    setUsers([]);
                    setIsLoading(false);
                });
        }, 300);

        return () => {
            if (searchTimeoutRef.current) {
                clearTimeout(searchTimeoutRef.current);
            }
        };
    }, [search, isOpen]);

    return (
        <div ref={containerRef} className="relative">
            <Label>{label}</Label>
            {selectedUser ? (
                <div className="mt-1.5 flex items-center justify-between rounded-md border border-neutral-300 bg-neutral-50 p-3 dark:border-neutral-700 dark:bg-neutral-900">
                    <div>
                        <p className="font-medium text-neutral-900 dark:text-white">
                            {selectedUser.name}
                        </p>
                        {selectedUser.email && (
                            <p className="text-sm text-neutral-600 dark:text-neutral-400">
                                {selectedUser.email}
                            </p>
                        )}
                        {(selectedUser.job_title || selectedUser.organisation) && (
                            <p className="mt-0.5 text-xs text-neutral-500 dark:text-neutral-400">
                                {selectedUser.job_title}
                                {selectedUser.job_title &&
                                    selectedUser.organisation &&
                                    ' at '}
                                {selectedUser.organisation}
                            </p>
                        )}
                    </div>
                    <Button
                        type="button"
                        variant="ghost"
                        size="sm"
                        onClick={() => onSelect(null)}
                        disabled={disabled}
                    >
                        <X className="size-4" />
                    </Button>
                </div>
            ) : (
                <>
                    <div className="relative mt-1.5">
                        <Search className="absolute left-3 top-1/2 size-4 -translate-y-1/2 text-neutral-400" />
                        <Input
                            type="text"
                            placeholder="Search users by name or email..."
                            value={search}
                            onChange={(e) => {
                                const value = e.target.value;
                                setSearch(value);
                                if (value.length < 2) {
                                    setUsers([]);
                                }
                                setIsOpen(true);
                            }}
                            onFocus={() => setIsOpen(true)}
                            disabled={disabled}
                            className="pl-10"
                        />
                    </div>
                    {isOpen && search.length >= 2 && (
                        <div className="absolute z-50 mt-1 w-full rounded-md border border-neutral-200 bg-white shadow-lg dark:border-neutral-800 dark:bg-neutral-950">
                            {isLoading ? (
                                <div className="p-4 text-center text-sm text-neutral-500">
                                    Searching...
                                </div>
                            ) : users.length === 0 ? (
                                <div className="p-4 text-center text-sm text-neutral-500">
                                    No users found
                                </div>
                            ) : (
                                <div className="max-h-60 overflow-y-auto">
                                    {users.map((user) => (
                                        <button
                                            key={user.id}
                                            type="button"
                                            onClick={() => {
                                                onSelect(user);
                                                setIsOpen(false);
                                                setSearch('');
                                            }}
                                            className="w-full border-b border-neutral-100 p-3 text-left transition-colors hover:bg-neutral-50 last:border-b-0 dark:border-neutral-800 dark:hover:bg-neutral-900"
                                        >
                                            <p className="font-medium text-neutral-900 dark:text-white">
                                                {user.name}
                                            </p>
                                            <p className="text-sm text-neutral-600 dark:text-neutral-400">
                                                {user.email}
                                            </p>
                                            {(user.job_title ||
                                                user.organisation) && (
                                                <p className="mt-0.5 text-xs text-neutral-500 dark:text-neutral-400">
                                                    {user.job_title}
                                                    {user.job_title &&
                                                        user.organisation &&
                                                        ' at '}
                                                    {user.organisation}
                                                </p>
                                            )}
                                        </button>
                                    ))}
                                </div>
                            )}
                        </div>
                    )}
                </>
            )}
            <p className="mt-1 text-xs text-neutral-500 dark:text-neutral-400">
                {selectedUser ? selectedHelpText : searchHelpText}
            </p>
        </div>
    );
}
