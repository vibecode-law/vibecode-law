interface AvatarFallbackProps {
    name: string;
    imageUrl?: string | null;
    size?: 'sm' | 'md' | 'lg';
    shape?: 'circle' | 'square';
}

const sizeClasses = {
    sm: 'size-12 text-sm',
    md: 'size-16 text-base',
    lg: 'size-20 text-lg',
};

const shapeClasses = {
    circle: 'rounded-full',
    square: 'rounded',
};

// Generate a consistent color based on the name
function getColorFromName(name: string): string {
    const colors = [
        'bg-amber-500',
        'bg-blue-500',
        'bg-cyan-500',
        'bg-emerald-500',
        'bg-fuchsia-500',
        'bg-green-500',
        'bg-indigo-500',
        'bg-lime-500',
        'bg-orange-500',
        'bg-pink-500',
        'bg-purple-500',
        'bg-red-500',
        'bg-rose-500',
        'bg-sky-500',
        'bg-teal-500',
        'bg-violet-500',
        'bg-yellow-500',
    ];

    // Simple hash function to get a consistent color
    let hash = 0;
    for (let i = 0; i < name.length; i++) {
        hash = name.charCodeAt(i) + ((hash << 5) - hash);
    }
    const index = Math.abs(hash) % colors.length;
    return colors[index];
}

// Extract initials from name
function getInitials(name: string): string {
    const words = name.trim().split(/\s+/);
    if (words.length >= 2) {
        // Take first letter of first two words
        return (words[0][0] + words[1][0]).toUpperCase();
    }
    // Take first two letters of single word
    return name.substring(0, 2).toUpperCase();
}

export function AvatarFallback({
    name,
    imageUrl,
    size = 'sm',
    shape = 'circle',
}: AvatarFallbackProps) {
    const sizeClass = sizeClasses[size];
    const shapeClass = shapeClasses[shape];

    if (imageUrl) {
        return (
            <img
                src={imageUrl}
                alt={name}
                className={`${sizeClass} ${shapeClass} object-cover`}
            />
        );
    }

    const initials = getInitials(name);
    const bgColor = getColorFromName(name);

    return (
        <div
            className={`${sizeClass} ${shapeClass} ${bgColor} flex items-center justify-center font-semibold text-white`}
        >
            {initials}
        </div>
    );
}
