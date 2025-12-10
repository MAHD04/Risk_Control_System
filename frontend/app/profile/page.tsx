'use client';

import { useState } from 'react';
import { User, Mail, Shield, Camera, Save, Key, AlertTriangle } from 'lucide-react';

// TODO: Connect this page to backend authentication system when implemented
// TODO: Fetch user data from API instead of using hardcoded values
// TODO: Implement actual save functionality with API call

export default function ProfilePage() {
    const [isEditing, setIsEditing] = useState(false);
    const [isSaving, setIsSaving] = useState(false);

    // TODO: Replace hardcoded profile with data from authenticated user
    const [profile, setProfile] = useState({
        name: 'Admin',
        email: 'admin@riskcontrol.com',
        role: 'Risk Manager',
        phone: '+1 (555) 123-4567',
        timezone: 'America/New_York',
    });

    const handleSave = async () => {
        setIsSaving(true);
        // TODO: Replace with actual API call to persist profile changes
        await new Promise(resolve => setTimeout(resolve, 1000));
        setIsSaving(false);
        setIsEditing(false);
    };

    return (
        <div className="space-y-6">
            {/* Mock Implementation Warning */}
            <div className="flex items-center gap-3 p-4 bg-amber-500/10 border border-amber-500/30 rounded-lg text-amber-400">
                <AlertTriangle className="h-5 w-5 flex-shrink-0" />
                <div>
                    <p className="font-medium">Demo Mode</p>
                    <p className="text-sm text-amber-400/80">
                        This page displays mock data. Changes will not be persisted until authentication is implemented.
                    </p>
                </div>
            </div>

            {/* Header */}
            <div>
                <h1 className="text-2xl font-bold text-white">Profile</h1>
                <p className="text-slate-400 mt-1">Manage your account information</p>
            </div>

            {/* Profile Card */}
            <div className="rounded-xl border border-slate-800 bg-slate-900/50 overflow-hidden">
                {/* Cover */}
                <div className="h-32 bg-gradient-to-r from-indigo-600 via-purple-600 to-pink-600" />

                {/* Avatar & Info */}
                <div className="relative px-6 pb-6">
                    <div className="flex flex-col sm:flex-row sm:items-end gap-4 -mt-12">
                        {/* Avatar */}
                        <div className="relative">
                            <div className="h-24 w-24 rounded-full bg-gradient-to-br from-indigo-500 to-purple-600 flex items-center justify-center text-3xl font-bold text-white ring-4 ring-slate-900">
                                AD
                            </div>
                            <button className="absolute bottom-0 right-0 rounded-full bg-slate-800 p-2 text-slate-400 hover:text-white hover:bg-slate-700 transition-colors">
                                <Camera className="h-4 w-4" />
                            </button>
                        </div>

                        {/* Name & Role */}
                        <div className="flex-1 pt-2 sm:pt-0">
                            <h2 className="text-xl font-bold text-white">{profile.name}</h2>
                            <div className="flex items-center gap-2 text-slate-400">
                                <Shield className="h-4 w-4" />
                                <span>{profile.role}</span>
                            </div>
                        </div>

                        {/* Edit Button */}
                        <button
                            onClick={() => isEditing ? handleSave() : setIsEditing(true)}
                            disabled={isSaving}
                            className="flex items-center gap-2 rounded-lg bg-indigo-600 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-500 transition-colors disabled:opacity-50"
                        >
                            {isSaving ? (
                                <>
                                    <div className="h-4 w-4 animate-spin rounded-full border-2 border-white/30 border-t-white" />
                                    Saving...
                                </>
                            ) : isEditing ? (
                                <>
                                    <Save className="h-4 w-4" />
                                    Save Changes
                                </>
                            ) : (
                                'Edit Profile'
                            )}
                        </button>
                    </div>
                </div>
            </div>

            {/* Profile Form */}
            <div className="grid gap-6 md:grid-cols-2">
                {/* Personal Information */}
                <div className="rounded-xl border border-slate-800 bg-slate-900/50 p-6">
                    <h3 className="text-lg font-semibold text-white mb-4 flex items-center gap-2">
                        <User className="h-5 w-5 text-indigo-400" />
                        Personal Information
                    </h3>
                    <div className="space-y-4">
                        <div>
                            <label className="block text-sm font-medium text-slate-400 mb-1">Full Name</label>
                            <input
                                type="text"
                                value={profile.name}
                                onChange={(e) => setProfile({ ...profile, name: e.target.value })}
                                disabled={!isEditing}
                                className="w-full rounded-lg border border-slate-700 bg-slate-800/50 px-4 py-2.5 text-white placeholder-slate-500 focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500 disabled:opacity-60 disabled:cursor-not-allowed"
                            />
                        </div>
                        <div>
                            <label className="block text-sm font-medium text-slate-400 mb-1">Phone</label>
                            <input
                                type="tel"
                                value={profile.phone}
                                onChange={(e) => setProfile({ ...profile, phone: e.target.value })}
                                disabled={!isEditing}
                                className="w-full rounded-lg border border-slate-700 bg-slate-800/50 px-4 py-2.5 text-white placeholder-slate-500 focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500 disabled:opacity-60 disabled:cursor-not-allowed"
                            />
                        </div>
                        <div>
                            <label className="block text-sm font-medium text-slate-400 mb-1">Timezone</label>
                            <select
                                value={profile.timezone}
                                onChange={(e) => setProfile({ ...profile, timezone: e.target.value })}
                                disabled={!isEditing}
                                className="w-full rounded-lg border border-slate-700 bg-slate-800/50 px-4 py-2.5 text-white focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500 disabled:opacity-60 disabled:cursor-not-allowed"
                            >
                                <option value="America/New_York">Eastern Time (ET)</option>
                                <option value="America/Chicago">Central Time (CT)</option>
                                <option value="America/Denver">Mountain Time (MT)</option>
                                <option value="America/Los_Angeles">Pacific Time (PT)</option>
                                <option value="Europe/London">London (GMT)</option>
                                <option value="Europe/Paris">Paris (CET)</option>
                            </select>
                        </div>
                    </div>
                </div>

                {/* Account Information */}
                <div className="rounded-xl border border-slate-800 bg-slate-900/50 p-6">
                    <h3 className="text-lg font-semibold text-white mb-4 flex items-center gap-2">
                        <Mail className="h-5 w-5 text-indigo-400" />
                        Account Information
                    </h3>
                    <div className="space-y-4">
                        <div>
                            <label className="block text-sm font-medium text-slate-400 mb-1">Email Address</label>
                            <input
                                type="email"
                                value={profile.email}
                                onChange={(e) => setProfile({ ...profile, email: e.target.value })}
                                disabled={!isEditing}
                                className="w-full rounded-lg border border-slate-700 bg-slate-800/50 px-4 py-2.5 text-white placeholder-slate-500 focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500 disabled:opacity-60 disabled:cursor-not-allowed"
                            />
                        </div>
                        <div>
                            <label className="block text-sm font-medium text-slate-400 mb-1">Role</label>
                            <input
                                type="text"
                                value={profile.role}
                                disabled
                                className="w-full rounded-lg border border-slate-700 bg-slate-800/50 px-4 py-2.5 text-slate-400 disabled:opacity-60 disabled:cursor-not-allowed"
                            />
                            <p className="text-xs text-slate-500 mt-1">Role can only be changed by an administrator</p>
                        </div>
                    </div>

                    {/* Change Password */}
                    <div className="mt-6 pt-6 border-t border-slate-700">
                        <button
                            onClick={() => alert('Change password functionality will be available when authentication is implemented.')}
                            className="flex items-center gap-2 text-indigo-400 hover:text-indigo-300 transition-colors"
                        >
                            <Key className="h-4 w-4" />
                            Change Password
                        </button>
                    </div>
                </div>
            </div>
        </div>
    );
}
