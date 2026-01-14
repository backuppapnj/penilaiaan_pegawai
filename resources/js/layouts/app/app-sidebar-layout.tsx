import { AppContent } from '@/components/app-content';
import { AppShell } from '@/components/app-shell';
import { AppSidebar } from '@/components/app-sidebar';
import { AppSidebarHeader } from '@/components/app-sidebar-header';
import { Alert, AlertDescription, AlertTitle } from '@/components/ui/alert';
import { type BreadcrumbItem, type SharedData } from '@/types';
import { usePage } from '@inertiajs/react';
import { AlertCircle, CheckCircle2 } from 'lucide-react';
import { type PropsWithChildren } from 'react';

// Extend SharedData to include flash messages
interface PageProps extends SharedData {
    flash: {
        success: string | null;
        error: string | null;
        [key: string]: string | null;
    };
}

export default function AppSidebarLayout({
    children,
    breadcrumbs = [],
}: PropsWithChildren<{ breadcrumbs?: BreadcrumbItem[] }>) {
    const { flash } = usePage<PageProps>().props;

    return (
        <AppShell variant="sidebar">
            <AppSidebar />
            <AppContent variant="sidebar" className="overflow-x-hidden">
                <AppSidebarHeader breadcrumbs={breadcrumbs} />
                
                {flash?.success && (
                    <div className="px-6 pt-6">
                        <Alert className="border-green-200 bg-green-50 text-green-800">
                            <CheckCircle2 className="h-4 w-4 text-green-600" />
                            <AlertTitle className="text-green-800">Sukses</AlertTitle>
                            <AlertDescription className="text-green-700">
                                {flash.success}
                            </AlertDescription>
                        </Alert>
                    </div>
                )}

                {flash?.error && (
                    <div className="px-6 pt-6">
                        <Alert variant="destructive">
                            <AlertCircle className="h-4 w-4" />
                            <AlertTitle>Error</AlertTitle>
                            <AlertDescription>{flash.error}</AlertDescription>
                        </Alert>
                    </div>
                )}

                {children}
            </AppContent>
        </AppShell>
    );
}
