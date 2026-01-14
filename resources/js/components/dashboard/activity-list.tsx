import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Clock } from 'lucide-react';

interface ActivityItem {
    id: number;
    action: string;
    description: string;
    user: string;
    created_at: string;
}

interface ActivityListProps {
    activities: ActivityItem[];
}

export default function ActivityList({ activities }: ActivityListProps) {
    return (
        <Card className="border-sidebar-border/70 dark:border-sidebar-border">
            <CardHeader>
                <CardTitle className="text-lg font-semibold text-gray-900 dark:text-gray-100">
                    Recent Activity
                </CardTitle>
            </CardHeader>
            <CardContent>
                {activities.length === 0 ? (
                    <div className="py-8 text-center">
                        <Clock className="mx-auto mb-3 size-12 text-gray-400" />
                        <p className="text-sm text-gray-600 dark:text-gray-400">
                            No recent activity
                        </p>
                    </div>
                ) : (
                    <div className="space-y-4">
                        {activities.map((activity) => (
                            <div
                                key={activity.id}
                                className="flex items-start gap-3 border-b border-gray-100 pb-3 last:border-0 last:pb-0 dark:border-gray-800"
                            >
                                <div className="flex size-8 flex-shrink-0 items-center justify-center rounded-full bg-gray-100 dark:bg-gray-800">
                                    <Clock className="size-4 text-gray-600 dark:text-gray-400" />
                                </div>
                                <div className="min-w-0 flex-1">
                                    <p className="text-sm font-medium text-gray-900 dark:text-gray-100">
                                        {activity.description}
                                    </p>
                                    <p className="mt-1 text-xs text-gray-600 dark:text-gray-400">
                                        by {activity.user} ·{' '}
                                        {activity.created_at}
                                    </p>
                                </div>
                            </div>
                        ))}
                    </div>
                )}
            </CardContent>
        </Card>
    );
}
