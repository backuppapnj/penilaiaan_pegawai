import HeadingSmall from '@/components/heading-small';
import { useAppearance } from '@/hooks/use-appearance';
import SettingsLayout from '@/layouts/settings/layout';
import { Head } from '@inertiajs/react';
import { Monitor, Moon, Sun } from 'lucide-react';

export default function Appearance() {
    const { appearance, updateAppearance } = useAppearance();

    const appearances = [
        { name: 'Light', value: 'light' as const, icon: Sun },
        { name: 'Dark', value: 'dark' as const, icon: Moon },
        { name: 'System', value: 'system' as const, icon: Monitor },
    ];

    return (
        <SettingsLayout>
            <Head title="Appearance Settings" />

            <div className="space-y-6">
                <HeadingSmall
                    title="Appearance"
                    description="Customize how your application looks on your device"
                />

                <div className="grid grid-cols-1 gap-3 sm:grid-cols-3">
                    {appearances.map((item) => {
                        const isActive = appearance === item.value;
                        const Icon = item.icon;

                        return (
                            <button
                                key={item.value}
                                type="button"
                                onClick={() => updateAppearance(item.value)}
                                className={`flex flex-col items-center justify-center gap-2 rounded-lg border-2 p-4 transition-colors hover:bg-muted ${
                                    isActive
                                        ? 'border-primary bg-primary/10'
                                        : 'border-border'
                                }`}
                            >
                                <Icon className="h-6 w-6" />
                                <span className="text-sm font-medium">
                                    {item.name}
                                </span>
                                {isActive && (
                                    <span className="sr-only">(Selected)</span>
                                )}
                            </button>
                        );
                    })}
                </div>

                <div className="rounded-lg border border-blue-100 bg-blue-50 p-4 dark:border-blue-200/10 dark:bg-blue-700/10">
                    <p className="text-sm text-blue-600 dark:text-blue-400">
                        Your appearance preference will be saved and applied
                        across all your devices.
                    </p>
                </div>
            </div>
        </SettingsLayout>
    );
}
