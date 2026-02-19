import { TabNav } from '@/components/navigation/tab-nav';
import { useActiveUrl } from '@/hooks/use-active-url';
import { index as practiceAreasIndex } from '@/routes/staff/metadata/practice-areas';
import { index as tagsIndex } from '@/routes/staff/metadata/tags';

export function MetadataSubNav() {
    const { currentUrl } = useActiveUrl();

    return (
        <div className="mb-6">
            <TabNav
                items={[
                    {
                        title: 'Practice Areas',
                        href: practiceAreasIndex().url,
                        isActive: currentUrl.startsWith(
                            '/staff/metadata/practice-areas',
                        ),
                    },
                    {
                        title: 'Tags',
                        href: tagsIndex().url,
                        isActive: currentUrl.startsWith('/staff/metadata/tags'),
                    },
                ]}
                ariaLabel="Metadata"
                variant="secondary"
            />
        </div>
    );
}
