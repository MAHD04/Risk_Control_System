'use client';

import Link from 'next/link';
import { usePathname } from 'next/navigation';
import { ShieldAlert, Activity, LayoutDashboard, Users, TrendingUp } from 'lucide-react';
import { clsx } from 'clsx';

const navItems = [
    { name: 'Dashboard', href: '/', icon: LayoutDashboard },
    { name: 'Risk Rules', href: '/rules', icon: ShieldAlert },
    { name: 'Incidents', href: '/incidents', icon: Activity },
    { name: 'Trades', href: '/trades', icon: TrendingUp },
    { name: 'Accounts', href: '/accounts', icon: Users },
];

export default function Sidebar() {
    const pathname = usePathname();

    return (
        <aside
            className="fixed inset-y-0 left-0 z-50 w-64 bg-slate-950 border-r border-slate-800 hidden md:flex flex-col"
            role="navigation"
            aria-label="Main navigation"
        >
            {/* Logo Section */}
            <div className="h-16 flex items-center px-6 border-b border-slate-800">
                <div className="flex items-center gap-3">
                    <div
                        className="flex h-9 w-9 items-center justify-center rounded-lg bg-gradient-to-br from-indigo-500 to-purple-600 shadow-lg shadow-indigo-500/25"
                        aria-hidden="true"
                    >
                        <ShieldAlert className="w-5 h-5 text-white" />
                    </div>
                    <span className="text-lg font-semibold text-white">RiskControl</span>
                </div>
            </div>

            {/* Navigation */}
            <nav className="flex-1 px-4 py-6 overflow-y-auto" aria-label="Primary">
                <div className="mb-4">
                    <span
                        className="px-3 text-xs font-semibold uppercase tracking-wider text-slate-500"
                        id="nav-heading"
                    >
                        Navigation
                    </span>
                </div>
                <ul className="space-y-1" role="list" aria-labelledby="nav-heading">
                    {navItems.map((item) => {
                        const isActive = pathname === item.href ||
                            (item.href !== '/' && pathname.startsWith(item.href));
                        return (
                            <li key={item.href} role="listitem">
                                <Link
                                    href={item.href}
                                    aria-current={isActive ? 'page' : undefined}
                                    className={clsx(
                                        'group flex items-center gap-3 rounded-lg px-3 py-2.5 text-sm font-medium transition-all duration-200',
                                        isActive
                                            ? 'bg-gradient-to-r from-indigo-500/20 to-purple-500/10 text-white shadow-sm'
                                            : 'text-slate-400 hover:bg-slate-800/50 hover:text-white'
                                    )}
                                >
                                    <span
                                        className={clsx(
                                            'transition-colors',
                                            isActive ? 'text-indigo-400' : 'text-slate-500 group-hover:text-indigo-400'
                                        )}
                                        aria-hidden="true"
                                    >
                                        <item.icon className="w-5 h-5" />
                                    </span>
                                    {item.name}
                                    {isActive && (
                                        <span
                                            className="ml-auto h-1.5 w-1.5 rounded-full bg-indigo-500"
                                            aria-hidden="true"
                                        />
                                    )}
                                </Link>
                            </li>
                        );
                    })}
                </ul>
            </nav>
        </aside>
    );
}

