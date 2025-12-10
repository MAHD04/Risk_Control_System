'use client';

import { useState } from 'react';
import { Settings, Bell, Shield, Moon, Globe, Save, ToggleLeft, ToggleRight, AlertTriangle } from 'lucide-react';

// TODO: Connect this page to backend settings API when implemented
// TODO: Persist settings to backend instead of using local state only
// TODO: Load user preferences from backend on page load

export default function SettingsPage() {
    const [isSaving, setIsSaving] = useState(false);

    // TODO: Replace hardcoded defaults with settings from API
    const [settings, setSettings] = useState({
        emailNotifications: true,
        slackNotifications: false,
        darkMode: true,
        autoRefresh: true,
        refreshInterval: 30,
        language: 'en',
        incidentAlerts: true,
        ruleChangeAlerts: true,
        systemAlerts: false,
    });

    const handleSave = async () => {
        setIsSaving(true);
        // TODO: Replace with actual API call to persist settings
        await new Promise(resolve => setTimeout(resolve, 1000));
        setIsSaving(false);
    };

    const Toggle = ({ enabled, onChange }: { enabled: boolean; onChange: () => void }) => (
        <button onClick={onChange} className="text-indigo-400 hover:text-indigo-300 transition-colors">
            {enabled ? <ToggleRight className="h-8 w-8" /> : <ToggleLeft className="h-8 w-8 text-slate-500" />}
        </button>
    );

    return (
        <div className="space-y-6">
            {/* Mock Implementation Warning */}
            <div className="flex items-center gap-3 p-4 bg-amber-500/10 border border-amber-500/30 rounded-lg text-amber-400">
                <AlertTriangle className="h-5 w-5 flex-shrink-0" />
                <div>
                    <p className="font-medium">Demo Mode</p>
                    <p className="text-sm text-amber-400/80">
                        Settings are stored locally and will not persist across sessions until the backend is implemented.
                    </p>
                </div>
            </div>

            {/* Header */}
            <div className="flex items-center justify-between">
                <div>
                    <h1 className="text-2xl font-bold text-white">Settings</h1>
                    <p className="text-slate-400 mt-1">Manage your application preferences</p>
                </div>
                <button
                    onClick={handleSave}
                    disabled={isSaving}
                    className="flex items-center gap-2 rounded-lg bg-indigo-600 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-500 transition-colors disabled:opacity-50"
                >
                    {isSaving ? (
                        <>
                            <div className="h-4 w-4 animate-spin rounded-full border-2 border-white/30 border-t-white" />
                            Saving...
                        </>
                    ) : (
                        <>
                            <Save className="h-4 w-4" />
                            Save Settings
                        </>
                    )}
                </button>
            </div>

            <div className="grid gap-6 lg:grid-cols-2">
                {/* Notifications */}
                <div className="rounded-xl border border-slate-800 bg-slate-900/50 p-6">
                    <h3 className="text-lg font-semibold text-white mb-4 flex items-center gap-2">
                        <Bell className="h-5 w-5 text-indigo-400" />
                        Notifications
                    </h3>
                    <div className="space-y-4">
                        <div className="flex items-center justify-between py-2">
                            <div>
                                <p className="text-white font-medium">Email Notifications</p>
                                <p className="text-sm text-slate-400">Receive alerts via email</p>
                            </div>
                            <Toggle
                                enabled={settings.emailNotifications}
                                onChange={() => setSettings({ ...settings, emailNotifications: !settings.emailNotifications })}
                            />
                        </div>
                        <div className="flex items-center justify-between py-2 border-t border-slate-700">
                            <div>
                                <p className="text-white font-medium">Slack Notifications</p>
                                <p className="text-sm text-slate-400">Receive alerts via Slack</p>
                            </div>
                            <Toggle
                                enabled={settings.slackNotifications}
                                onChange={() => setSettings({ ...settings, slackNotifications: !settings.slackNotifications })}
                            />
                        </div>
                        <div className="flex items-center justify-between py-2 border-t border-slate-700">
                            <div>
                                <p className="text-white font-medium">Incident Alerts</p>
                                <p className="text-sm text-slate-400">Get notified on new incidents</p>
                            </div>
                            <Toggle
                                enabled={settings.incidentAlerts}
                                onChange={() => setSettings({ ...settings, incidentAlerts: !settings.incidentAlerts })}
                            />
                        </div>
                        <div className="flex items-center justify-between py-2 border-t border-slate-700">
                            <div>
                                <p className="text-white font-medium">Rule Change Alerts</p>
                                <p className="text-sm text-slate-400">Get notified when rules are modified</p>
                            </div>
                            <Toggle
                                enabled={settings.ruleChangeAlerts}
                                onChange={() => setSettings({ ...settings, ruleChangeAlerts: !settings.ruleChangeAlerts })}
                            />
                        </div>
                    </div>
                </div>

                {/* Appearance */}
                <div className="rounded-xl border border-slate-800 bg-slate-900/50 p-6">
                    <h3 className="text-lg font-semibold text-white mb-4 flex items-center gap-2">
                        <Moon className="h-5 w-5 text-indigo-400" />
                        Appearance
                    </h3>
                    <div className="space-y-4">
                        <div className="flex items-center justify-between py-2">
                            <div>
                                <p className="text-white font-medium">Dark Mode</p>
                                <p className="text-sm text-slate-400">Use dark theme</p>
                            </div>
                            <Toggle
                                enabled={settings.darkMode}
                                onChange={() => setSettings({ ...settings, darkMode: !settings.darkMode })}
                            />
                        </div>
                        <div className="py-2 border-t border-slate-700">
                            <label className="block text-white font-medium mb-2">Language</label>
                            <select
                                value={settings.language}
                                onChange={(e) => setSettings({ ...settings, language: e.target.value })}
                                className="w-full rounded-lg border border-slate-700 bg-slate-800/50 px-4 py-2.5 text-white focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500"
                            >
                                <option value="en">English</option>
                                <option value="es">Español</option>
                                <option value="fr">Français</option>
                                <option value="de">Deutsch</option>
                            </select>
                        </div>
                    </div>
                </div>

                {/* Data & Refresh */}
                <div className="rounded-xl border border-slate-800 bg-slate-900/50 p-6">
                    <h3 className="text-lg font-semibold text-white mb-4 flex items-center gap-2">
                        <Globe className="h-5 w-5 text-indigo-400" />
                        Data & Refresh
                    </h3>
                    <div className="space-y-4">
                        <div className="flex items-center justify-between py-2">
                            <div>
                                <p className="text-white font-medium">Auto Refresh</p>
                                <p className="text-sm text-slate-400">Automatically update dashboard data</p>
                            </div>
                            <Toggle
                                enabled={settings.autoRefresh}
                                onChange={() => setSettings({ ...settings, autoRefresh: !settings.autoRefresh })}
                            />
                        </div>
                        <div className="py-2 border-t border-slate-700">
                            <label className="block text-white font-medium mb-2">Refresh Interval (seconds)</label>
                            <input
                                type="number"
                                min="10"
                                max="300"
                                value={settings.refreshInterval}
                                onChange={(e) => setSettings({ ...settings, refreshInterval: parseInt(e.target.value) })}
                                disabled={!settings.autoRefresh}
                                className="w-full rounded-lg border border-slate-700 bg-slate-800/50 px-4 py-2.5 text-white focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500 disabled:opacity-50"
                            />
                        </div>
                    </div>
                </div>

                {/* Security */}
                <div className="rounded-xl border border-slate-800 bg-slate-900/50 p-6">
                    <h3 className="text-lg font-semibold text-white mb-4 flex items-center gap-2">
                        <Shield className="h-5 w-5 text-indigo-400" />
                        Security
                    </h3>
                    <div className="space-y-4">
                        <div className="py-2">
                            <p className="text-white font-medium">Two-Factor Authentication</p>
                            <p className="text-sm text-slate-400 mb-3">Add an extra layer of security</p>
                            <button
                                onClick={() => alert('Two-Factor Authentication will be available when authentication is implemented.')}
                                className="rounded-lg border border-indigo-500 px-4 py-2 text-sm font-medium text-indigo-400 hover:bg-indigo-500/10 transition-colors"
                            >
                                Enable 2FA
                            </button>
                        </div>
                        <div className="py-2 border-t border-slate-700">
                            <p className="text-white font-medium">Active Sessions</p>
                            <p className="text-sm text-slate-400 mb-3">Manage your active sessions</p>
                            <button
                                onClick={() => alert('Session management will be available when authentication is implemented.')}
                                className="rounded-lg border border-slate-600 px-4 py-2 text-sm font-medium text-slate-300 hover:bg-slate-700 transition-colors"
                            >
                                View Sessions
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    );
}
