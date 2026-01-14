import { Badge } from '@/components/ui/badge';

interface StatusBadgeProps {
    status: 'draft' | 'open' | 'closed' | 'announced';
}

export default function StatusBadge({ status }: StatusBadgeProps) {
    const variants = {
        draft: 'bg-gray-100 text-gray-800 dark:bg-gray-800 dark:text-gray-200',
        open: 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200',
        closed: 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200',
        announced:
            'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200',
    };

    const labels = {
        draft: 'Draft',
        open: 'Open',
        closed: 'Closed',
        announced: 'Announced',
    };

    return (
        <Badge className={variants[status]} variant="secondary">
            {labels[status]}
        </Badge>
    );
}
