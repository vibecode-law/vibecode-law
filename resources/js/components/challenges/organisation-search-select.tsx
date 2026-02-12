import OrganisationsController from '@/actions/App/Http/Controllers/Api/OrganisationsController';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Search, X } from 'lucide-react';
import { useEffect, useRef, useState } from 'react';

interface Organisation {
    id: number;
    name: string;
    tagline?: string | null;
    about?: string | null;
}

interface OrganisationSearchSelectProps {
    name: string;
    defaultValue?: Organisation | null;
    disabled?: boolean;
    error?: string;
    onChange?: (organisation: Organisation | null) => void;
}

export function OrganisationSearchSelect({
    name,
    defaultValue = null,
    disabled = false,
    error,
    onChange,
}: OrganisationSearchSelectProps) {
    const [selected, setSelected] = useState<Organisation | null>(defaultValue);
    const [search, setSearch] = useState('');
    const [results, setResults] = useState<Organisation[]>([]);
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
            fetch(OrganisationsController.url({ query: { search } }))
                .then((res) => res.json())
                .then((data) => {
                    setResults(data);
                    setIsLoading(false);
                })
                .catch(() => {
                    setResults([]);
                    setIsLoading(false);
                });
        }, 300);

        return () => {
            if (searchTimeoutRef.current) {
                clearTimeout(searchTimeoutRef.current);
            }
        };
    }, [search, isOpen]);

    const handleSelect = (organisation: Organisation) => {
        setSelected(organisation);
        setIsOpen(false);
        setSearch('');
        onChange?.(organisation);
    };

    const handleClear = () => {
        setSelected(null);
        onChange?.(null);
    };

    return (
        <div ref={containerRef} className="relative">
            <input type="hidden" name={name} value={selected?.id ?? ''} />

            {selected ? (
                <div className="flex items-center justify-between rounded-md border border-neutral-300 bg-neutral-50 px-3 py-2 dark:border-neutral-700 dark:bg-neutral-900">
                    <span className="text-sm font-medium text-neutral-900 dark:text-white">
                        {selected.name}
                    </span>
                    <Button
                        type="button"
                        variant="ghost"
                        size="sm"
                        onClick={handleClear}
                        disabled={disabled}
                        className="h-auto p-1"
                    >
                        <X className="size-4" />
                    </Button>
                </div>
            ) : (
                <>
                    <div className="relative">
                        <Search className="absolute top-1/2 left-3 size-4 -translate-y-1/2 text-neutral-400" />
                        <Input
                            type="text"
                            placeholder="Search organisations..."
                            value={search}
                            onChange={(e) => {
                                const value = e.target.value;
                                setSearch(value);
                                if (value.length < 2) {
                                    setResults([]);
                                }
                                setIsOpen(true);
                            }}
                            onFocus={() => setIsOpen(true)}
                            disabled={disabled}
                            className="pl-10"
                            aria-invalid={
                                error !== undefined ? true : undefined
                            }
                        />
                    </div>
                    {isOpen && search.length >= 2 && (
                        <div className="absolute z-50 mt-1 w-full rounded-md border border-neutral-200 bg-white shadow-lg dark:border-neutral-800 dark:bg-neutral-950">
                            {isLoading ? (
                                <div className="p-4 text-center text-sm text-neutral-500">
                                    Searching...
                                </div>
                            ) : results.length === 0 ? (
                                <div className="p-4 text-center text-sm text-neutral-500">
                                    No organisations found
                                </div>
                            ) : (
                                <div className="max-h-60 overflow-y-auto">
                                    {results.map((org) => (
                                        <button
                                            key={org.id}
                                            type="button"
                                            onClick={() => handleSelect(org)}
                                            className="w-full border-b border-neutral-100 px-3 py-2 text-left text-sm transition-colors last:border-b-0 hover:bg-neutral-50 dark:border-neutral-800 dark:hover:bg-neutral-900"
                                        >
                                            {org.name}
                                        </button>
                                    ))}
                                </div>
                            )}
                        </div>
                    )}
                </>
            )}
        </div>
    );
}
