'use client';

import { useState, useEffect, useRef } from 'react';
import { useRouter } from 'next/navigation';
import { Bell, HelpCircle, ChevronDown, Check, AlertTriangle, User, Settings, LogOut, X, Book, MessageCircle, Shield } from 'lucide-react';
import { notificationApi, Notification } from '@/services/notificationApi';
import { NOTIFICATION_POLL_INTERVAL } from '@/constants/config';

export default function Header() {
    const router = useRouter();
    const [unreadCount, setUnreadCount] = useState(0);
    const [notifications, setNotifications] = useState<Notification[]>([]);
    const [showNotificationDropdown, setShowNotificationDropdown] = useState(false);
    const [showUserDropdown, setShowUserDropdown] = useState(false);
    const [showHelpModal, setShowHelpModal] = useState(false);
    const [isLoading, setIsLoading] = useState(false);
    const notificationRef = useRef<HTMLDivElement>(null);
    const userRef = useRef<HTMLDivElement>(null);

    // Fetch unread notifications
    const fetchNotifications = async () => {
        try {
            const response = await notificationApi.getUnread();
            if (response.success) {
                setUnreadCount(response.data.count);
                setNotifications(response.data.notifications);
            }
        } catch (error) {
            console.error('Failed to fetch notifications:', error);
        }
    };

    // Polling every 30 seconds
    useEffect(() => {
        fetchNotifications();
        const interval = setInterval(fetchNotifications, NOTIFICATION_POLL_INTERVAL);
        return () => clearInterval(interval);
    }, []);

    // Close dropdowns when clicking outside
    useEffect(() => {
        const handleClickOutside = (event: MouseEvent) => {
            if (notificationRef.current && !notificationRef.current.contains(event.target as Node)) {
                setShowNotificationDropdown(false);
            }
            if (userRef.current && !userRef.current.contains(event.target as Node)) {
                setShowUserDropdown(false);
            }
        };
        document.addEventListener('mousedown', handleClickOutside);
        return () => document.removeEventListener('mousedown', handleClickOutside);
    }, []);

    const handleMarkAsRead = async (id: number) => {
        try {
            await notificationApi.markAsRead(id);
            setNotifications(prev => prev.filter(n => n.id !== id));
            setUnreadCount(prev => Math.max(0, prev - 1));
        } catch (error) {
            console.error('Failed to mark as read:', error);
        }
    };

    const handleMarkAllAsRead = async () => {
        setIsLoading(true);
        try {
            await notificationApi.markAllAsRead();
            setNotifications([]);
            setUnreadCount(0);
        } catch (error) {
            console.error('Failed to mark all as read:', error);
        } finally {
            setIsLoading(false);
        }
    };

    const formatTimeAgo = (dateStr: string) => {
        const date = new Date(dateStr);
        const now = new Date();
        const diffMs = now.getTime() - date.getTime();
        const diffMins = Math.floor(diffMs / 60000);
        const diffHours = Math.floor(diffMins / 60);
        const diffDays = Math.floor(diffHours / 24);

        if (diffMins < 1) return 'Just now';
        if (diffMins < 60) return `${diffMins}m ago`;
        if (diffHours < 24) return `${diffHours}h ago`;
        return `${diffDays}d ago`;
    };

    return (
        <>
            <header className="h-16 bg-slate-950/80 backdrop-blur-xl border-b border-slate-800 sticky top-0 z-40 flex items-center justify-between px-6 md:px-8">
                {/* Mobile Logo */}
                <div className="flex items-center md:hidden">
                    <span className="text-lg font-bold text-white">RiskControl</span>
                </div>

                {/* Right Section */}
                <div className="flex items-center gap-4 ml-auto">
                    {/* Notifications */}
                    <div className="relative" ref={notificationRef}>
                        <button
                            onClick={() => setShowNotificationDropdown(!showNotificationDropdown)}
                            className="relative rounded-lg p-2 text-slate-400 transition-colors hover:bg-slate-800 hover:text-white"
                        >
                            <Bell className="h-5 w-5" />
                            {unreadCount > 0 && (
                                <span className="absolute -right-1 -top-1 flex h-5 w-5 items-center justify-center rounded-full bg-red-500 text-xs font-bold text-white ring-2 ring-slate-950">
                                    {unreadCount > 9 ? '9+' : unreadCount}
                                </span>
                            )}
                        </button>

                        {/* Notification Dropdown */}
                        {showNotificationDropdown && (
                            <div className="absolute right-0 mt-2 w-80 rounded-xl border border-slate-700 bg-slate-900 shadow-2xl shadow-black/50">
                                <div className="flex items-center justify-between border-b border-slate-700 px-4 py-3">
                                    <h3 className="font-semibold text-white">Notifications</h3>
                                    {notifications.length > 0 && (
                                        <button
                                            onClick={handleMarkAllAsRead}
                                            disabled={isLoading}
                                            className="text-xs text-indigo-400 hover:text-indigo-300 disabled:opacity-50"
                                        >
                                            Mark all as read
                                        </button>
                                    )}
                                </div>
                                <div className="max-h-80 overflow-y-auto">
                                    {notifications.length === 0 ? (
                                        <div className="flex flex-col items-center justify-center py-8 text-slate-500">
                                            <Check className="h-8 w-8 mb-2" />
                                            <p>All caught up!</p>
                                        </div>
                                    ) : (
                                        notifications.map((notification) => (
                                            <div
                                                key={notification.id}
                                                className="group flex items-start gap-3 border-b border-slate-800 px-4 py-3 hover:bg-slate-800/50 transition-colors"
                                            >
                                                <div className="flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-red-500/20 text-red-400">
                                                    <AlertTriangle className="h-4 w-4" />
                                                </div>
                                                <div className="flex-1 min-w-0">
                                                    <p className="text-sm font-medium text-white truncate">
                                                        {notification.riskRule?.name || 'Risk Rule Violation'}
                                                    </p>
                                                    <p className="text-xs text-slate-400 truncate">
                                                        Account {notification.account?.login || notification.account_id}
                                                    </p>
                                                    <p className="text-xs text-slate-500 mt-1">
                                                        {formatTimeAgo(notification.triggered_at)}
                                                    </p>
                                                </div>
                                                <button
                                                    onClick={(e) => {
                                                        e.stopPropagation();
                                                        handleMarkAsRead(notification.id);
                                                    }}
                                                    className="opacity-0 group-hover:opacity-100 rounded p-1 text-slate-400 hover:bg-slate-700 hover:text-white transition-all"
                                                    title="Mark as read"
                                                >
                                                    <Check className="h-4 w-4" />
                                                </button>
                                            </div>
                                        ))
                                    )}
                                </div>
                            </div>
                        )}
                    </div>

                    {/* Help */}
                    <button
                        onClick={() => setShowHelpModal(true)}
                        className="rounded-lg p-2 text-slate-400 transition-colors hover:bg-slate-800 hover:text-white"
                    >
                        <HelpCircle className="h-5 w-5" />
                    </button>

                    {/* Divider */}
                    <div className="h-6 w-px bg-slate-800" />

                    {/* User Menu */}
                    <div className="relative" ref={userRef}>
                        <button
                            onClick={() => setShowUserDropdown(!showUserDropdown)}
                            className="flex items-center gap-3 rounded-lg p-1.5 pr-3 transition-colors hover:bg-slate-800"
                        >
                            <div className="flex h-8 w-8 items-center justify-center rounded-full bg-gradient-to-br from-indigo-500 to-purple-600 shadow-lg shadow-indigo-500/25">
                                <span className="text-xs font-semibold text-white">AD</span>
                            </div>
                            <div className="hidden sm:block text-left">
                                <p className="text-sm font-medium text-white">Admin</p>
                                <p className="text-xs text-slate-500">Risk Manager</p>
                            </div>
                            <ChevronDown className={`h-4 w-4 text-slate-500 transition-transform ${showUserDropdown ? 'rotate-180' : ''}`} />
                        </button>

                        {/* User Dropdown */}
                        {showUserDropdown && (
                            <div className="absolute right-0 mt-2 w-56 rounded-xl border border-slate-700 bg-slate-900 shadow-2xl shadow-black/50">
                                {/* User Info */}
                                <div className="border-b border-slate-700 px-4 py-3">
                                    <p className="text-sm font-medium text-white">Admin</p>
                                    <p className="text-xs text-slate-400">admin@riskcontrol.com</p>
                                </div>

                                {/* Menu Items */}
                                <div className="py-1">
                                    <button
                                        onClick={() => {
                                            setShowUserDropdown(false);
                                            router.push('/profile');
                                        }}
                                        className="flex w-full items-center gap-3 px-4 py-2.5 text-sm text-slate-300 hover:bg-slate-800 hover:text-white transition-colors"
                                    >
                                        <User className="h-4 w-4" />
                                        Profile
                                    </button>
                                    <button
                                        onClick={() => {
                                            setShowUserDropdown(false);
                                            router.push('/settings');
                                        }}
                                        className="flex w-full items-center gap-3 px-4 py-2.5 text-sm text-slate-300 hover:bg-slate-800 hover:text-white transition-colors"
                                    >
                                        <Settings className="h-4 w-4" />
                                        Settings
                                    </button>
                                </div>

                                {/* Logout */}
                                <div className="border-t border-slate-700 py-1">
                                    <button
                                        onClick={() => {
                                            localStorage.removeItem('token');
                                            localStorage.removeItem('user');
                                            setShowUserDropdown(false);
                                            // In production, redirect to login page
                                            alert('Signed out successfully. In production, this would redirect to login.');
                                        }}
                                        className="flex w-full items-center gap-3 px-4 py-2.5 text-sm text-red-400 hover:bg-red-500/10 hover:text-red-300 transition-colors"
                                    >
                                        <LogOut className="h-4 w-4" />
                                        Sign out
                                    </button>
                                </div>
                            </div>
                        )}
                    </div>
                </div>
            </header>

            {/* Help Modal */}
            {showHelpModal && (
                <div className="fixed inset-0 z-50 flex items-center justify-center bg-black/60 backdrop-blur-sm">
                    <div className="relative w-full max-w-lg rounded-2xl border border-slate-700 bg-slate-900 p-6 shadow-2xl">
                        {/* Close Button */}
                        <button
                            onClick={() => setShowHelpModal(false)}
                            className="absolute right-4 top-4 rounded-lg p-1 text-slate-400 hover:bg-slate-800 hover:text-white transition-colors"
                        >
                            <X className="h-5 w-5" />
                        </button>

                        {/* Header */}
                        <div className="mb-6">
                            <h2 className="text-xl font-bold text-white">Help & Support</h2>
                            <p className="text-sm text-slate-400 mt-1">Get help with Risk Control System</p>
                        </div>

                        {/* Help Options */}
                        <div className="space-y-3">
                            <a href="#" className="flex items-center gap-4 rounded-xl border border-slate-700 p-4 hover:bg-slate-800/50 transition-colors group">
                                <div className="flex h-10 w-10 items-center justify-center rounded-lg bg-indigo-500/20 text-indigo-400 group-hover:bg-indigo-500/30">
                                    <Book className="h-5 w-5" />
                                </div>
                                <div>
                                    <p className="font-medium text-white">Documentation</p>
                                    <p className="text-xs text-slate-400">Learn how to use the system</p>
                                </div>
                            </a>

                            <a href="#" className="flex items-center gap-4 rounded-xl border border-slate-700 p-4 hover:bg-slate-800/50 transition-colors group">
                                <div className="flex h-10 w-10 items-center justify-center rounded-lg bg-emerald-500/20 text-emerald-400 group-hover:bg-emerald-500/30">
                                    <MessageCircle className="h-5 w-5" />
                                </div>
                                <div>
                                    <p className="font-medium text-white">Contact Support</p>
                                    <p className="text-xs text-slate-400">support@mmtech-solutions.com</p>
                                </div>
                            </a>

                            <a href="#" className="flex items-center gap-4 rounded-xl border border-slate-700 p-4 hover:bg-slate-800/50 transition-colors group">
                                <div className="flex h-10 w-10 items-center justify-center rounded-lg bg-amber-500/20 text-amber-400 group-hover:bg-amber-500/30">
                                    <Shield className="h-5 w-5" />
                                </div>
                                <div>
                                    <p className="font-medium text-white">Risk Rules Guide</p>
                                    <p className="text-xs text-slate-400">Configure and manage rules</p>
                                </div>
                            </a>
                        </div>

                        {/* Version */}
                        <div className="mt-6 text-center">
                            <p className="text-xs text-slate-500">Risk Control System v1.0.0</p>
                        </div>
                    </div>
                </div>
            )}
        </>
    );
}
