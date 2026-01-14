import { Card, CardContent } from '@/components/ui/card';
import { LucideIcon } from 'lucide-react';

interface StatCardProps {
    title: string;
    value: string | number;
    description: string;
    icon?: LucideIcon;
    gradient: string;
    trend?: {
        value: number;
        isPositive: boolean;
    };
}

export default function StatCard({
    title,
    value,
    description,
    icon: Icon,
    gradient,
    trend,
}: StatCardProps) {
    return (
        <Card className="overflow-hidden border-sidebar-border/70 dark:border-sidebar-border">
            <CardContent
                className={`bg-gradient-to-br p-6 ${gradient} text-white`}
            >
                <div className="flex items-start justify-between">
                    <div className="space-y-2">
                        <h3 className="text-sm font-medium opacity-90">
                            {title}
                        </h3>
                        <p className="text-3xl font-bold">{value}</p>
                        <p className="text-sm opacity-80">{description}</p>
                    </div>
                    {Icon && <Icon className="size-8 opacity-80" />}
                </div>
                {trend && (
                    <div className="mt-4 flex items-center gap-2 text-sm">
                        <span
                            className={`font-medium ${trend.isPositive ? 'text-green-100' : 'text-red-100'}`}
                        >
                            {trend.isPositive ? '+' : '-'}
                            {Math.abs(trend.value)}%
                        </span>
                        <span className="opacity-75">vs last period</span>
                    </div>
                )}
            </CardContent>
        </Card>
    );
}
