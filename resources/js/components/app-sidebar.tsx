import { NavFooter } from '@/components/nav-footer';
import { NavMain } from '@/components/nav-main';
import { NavUser } from '@/components/nav-user';
import {
    Sidebar,
    SidebarContent,
    SidebarFooter,
    SidebarHeader,
    SidebarMenu,
    SidebarMenuButton,
    SidebarMenuItem,
} from '@/components/ui/sidebar';
import { dashboard } from '@/routes';
import { type NavItem, type SharedData } from '@/types';
import { Link, usePage } from '@inertiajs/react';
import {
    Award,
    BookOpen,
    ClipboardCheck,
    FileSpreadsheet,
    Folder,
    History,
    LayoutGrid,
    Settings2,
    UserCog,
    Users,
} from 'lucide-react';
import AppLogo from './app-logo';

export function AppSidebar() {
    const { auth } = usePage<SharedData>().props;
    const userRole = auth.user?.role;

    const getMainNavItems = (): NavItem[] => {
        const items: NavItem[] = [
            {
                title: 'Dashboard',
                href: dashboard(),
                icon: LayoutGrid,
            },
        ];

        // Menu untuk Peserta
        if (
            userRole === 'Peserta' ||
            userRole === 'Penilai' ||
            userRole === 'Admin' ||
            userRole === 'SuperAdmin'
        ) {
            items.push({
                title: 'Sertifikat Saya',
                href: '/peserta/sertifikat',
                icon: Award,
            });
        }

        // Menu untuk Penilai
        if (
            userRole === 'Penilai' ||
            userRole === 'Peserta' || // Peserta biasanya juga bisa menilai teman sejawat
            userRole === 'Admin' ||
            userRole === 'SuperAdmin'
        ) {
            items.push({
                title: 'Voting Penilaian',
                href: '/penilai/voting',
                icon: ClipboardCheck,
            });

            items.push({
                title: 'Riwayat Voting',
                href: '/penilai/voting/history',
                icon: History,
            });
        }

        // Menu untuk Admin & SuperAdmin
        if (userRole === 'Admin' || userRole === 'SuperAdmin') {
            items.push({
                title: 'Manajemen Periode',
                href: '/admin/periods',
                icon: Users, // Using Users icon as generic management icon, or Calendar if available
            });

            items.push({
                title: 'Manajemen Kriteria',
                href: '/admin/criteria',
                icon: Settings2,
            });

            items.push({
                title: 'Manajemen Pegawai',
                href: '/admin/employees',
                icon: UserCog,
            });

            items.push({
                title: 'Import Nilai SIKEP',
                href: '/admin/sikep',
                icon: FileSpreadsheet,
            });
        }

        return items;
    };

    const mainNavItems = getMainNavItems();

    const footerNavItems: NavItem[] = [
        {
            title: 'Repository',
            href: 'https://github.com/laravel/react-starter-kit',
            icon: Folder,
        },
        {
            title: 'Documentation',
            href: 'https://laravel.com/docs/starter-kits#react',
            icon: BookOpen,
        },
    ];

    return (
        <Sidebar collapsible="icon" variant="inset">
            <SidebarHeader>
                <SidebarMenu>
                    <SidebarMenuItem>
                        <SidebarMenuButton size="lg" asChild>
                            <Link href={dashboard()} prefetch>
                                <AppLogo />
                            </Link>
                        </SidebarMenuButton>
                    </SidebarMenuItem>
                </SidebarMenu>
            </SidebarHeader>

            <SidebarContent>
                <NavMain items={mainNavItems} />
            </SidebarContent>

            <SidebarFooter>
                <NavFooter items={footerNavItems} className="mt-auto" />
                <NavUser />
            </SidebarFooter>
        </Sidebar>
    );
}
