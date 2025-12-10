'use client';

import { useEffect, useState } from 'react';
import { Activity, ShieldAlert, Users, AlertTriangle, TrendingUp, ArrowUpRight, ArrowDownRight, Plus, FileText, Eye } from 'lucide-react';
import Link from 'next/link';
import IncidentsChart from '@/components/dashboard/IncidentsChart';
import { dashboardApi, type RecentIncident, type SystemStatus } from '@/services';
import type { DashboardStats } from '@/types';

export default function Dashboard() {
  const [stats, setStats] = useState<DashboardStats | null>(null);
  const [recentIncidents, setRecentIncidents] = useState<RecentIncident[]>([]);
  const [systemStatus, setSystemStatus] = useState<SystemStatus | null>(null);
  const [isLoading, setIsLoading] = useState(true);
  const [error, setError] = useState<string | null>(null);

  useEffect(() => {
    const fetchDashboardData = async () => {
      try {
        setIsLoading(true);
        setError(null);
        const [statsData, incidentsData, statusData] = await Promise.all([
          dashboardApi.getStats(),
          dashboardApi.getRecentIncidents(),
          dashboardApi.getSystemStatus(),
        ]);
        setStats(statsData);
        setRecentIncidents(incidentsData);
        setSystemStatus(statusData);
      } catch (err) {
        console.error('Error fetching dashboard data:', err);
        setError('Failed to load dashboard data');
      } finally {
        setIsLoading(false);
      }
    };

    fetchDashboardData();
  }, []);

  // Prepare stats array for rendering
  const statsArray = stats ? [
    {
      label: 'Active Rules',
      value: stats.active_rules.toString(),
      change: '', // We can calculate this later if needed
      changeType: 'neutral' as 'positive' | 'negative' | 'neutral',
      icon: ShieldAlert,
      iconColor: 'text-indigo-400',
      iconBg: 'bg-indigo-500/10',
    },
    {
      label: 'Total Incidents',
      value: stats.total_incidents.toString(),
      change: `+${stats.incidents_today}`,
      changeType: (stats.incidents_today > 0 ? 'negative' : 'neutral') as 'positive' | 'negative' | 'neutral',
      icon: AlertTriangle,
      iconColor: 'text-amber-400',
      iconBg: 'bg-amber-500/10',
    },
    {
      label: 'Active Accounts',
      value: stats.active_accounts.toString(),
      change: '',
      changeType: 'neutral' as 'positive' | 'negative' | 'neutral',
      icon: Users,
      iconColor: 'text-emerald-400',
      iconBg: 'bg-emerald-500/10',
    },
    {
      label: 'Open Trades',
      value: stats.open_trades.toString(),
      change: '',
      changeType: 'neutral' as 'positive' | 'negative' | 'neutral',
      icon: TrendingUp,
      iconColor: 'text-blue-400',
      iconBg: 'bg-blue-500/10',
    },
  ] : [];

  if (isLoading) {
    return (
      <div className="animate-fade-in space-y-8">
        <div className="flex items-center justify-center h-64">
          <div className="text-slate-400">Loading dashboard data...</div>
        </div>
      </div>
    );
  }

  if (error) {
    return (
      <div className="animate-fade-in space-y-8">
        <div className="flex items-center justify-center h-64">
          <div className="text-red-400">{error}</div>
        </div>
      </div>
    );
  }

  return (
    <div className="animate-fade-in space-y-8">
      {/* Page Header */}
      <div className="flex items-center justify-between">
        <div>
          <h1 className="text-2xl font-bold text-white">Dashboard</h1>
          <p className="mt-1 text-slate-400">
            Monitor your risk control system in real-time
          </p>
        </div>
        <div className="flex items-center gap-3">
          <span className="badge badge-success">
            <span className="mr-1.5 h-2 w-2 rounded-full bg-emerald-400 animate-pulse-soft" />
            System Operational
          </span>
        </div>
      </div>

      {/* Stats Grid */}
      <div className="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-4">
        {statsArray.map((stat, index) => {
          const Icon = stat.icon;
          return (
            <div
              key={index}
              className="card card-hover"
            >
              <div className="flex items-center justify-between mb-4">
                <div className={`p-2.5 rounded-lg ${stat.iconBg}`}>
                  <Icon className={`w-5 h-5 ${stat.iconColor}`} />
                </div>
                {stat.change && (
                  <span className={`flex items-center gap-1 text-sm font-medium ${stat.changeType === 'positive' ? 'text-emerald-400' :
                    stat.changeType === 'negative' ? 'text-red-400' :
                      'text-slate-400'
                    }`}>
                    {stat.changeType === 'positive' && <ArrowUpRight className="w-4 h-4" />}
                    {stat.changeType === 'negative' && <ArrowDownRight className="w-4 h-4" />}
                    {stat.change}
                  </span>
                )}
              </div>
              <div>
                <p className="text-3xl font-bold text-white">{stat.value}</p>
                <p className="text-sm text-slate-400 mt-1">{stat.label}</p>
              </div>
            </div>
          );
        })}
      </div>

      {/* Incidents Chart Section */}
      <IncidentsChart />

      {/* Main Content Grid */}
      <div className="grid grid-cols-1 lg:grid-cols-3 gap-6">
        {/* Recent Incidents */}
        <div className="lg:col-span-2 card">
          <div className="flex items-center justify-between mb-6">
            <h2 className="text-lg font-semibold text-white">Recent Incidents</h2>
            <Link
              href="/incidents"
              className="flex items-center gap-1 text-sm text-indigo-400 hover:text-indigo-300 transition-colors"
            >
              View all
              <ArrowUpRight className="w-4 h-4" />
            </Link>
          </div>
          <div className="table-container">
            <table className="table">
              <thead>
                <tr>
                  <th>Rule</th>
                  <th>Account</th>
                  <th>Severity</th>
                  <th>Time</th>
                </tr>
              </thead>
              <tbody>
                {recentIncidents.map((incident) => (
                  <tr key={incident.id}>
                    <td className="font-medium">{incident.rule}</td>
                    <td>
                      <span className="text-slate-400">#{incident.account}</span>
                    </td>
                    <td>
                      <span className={`badge ${incident.severity === 'HARD' ? 'badge-danger' : 'badge-warning'
                        }`}>
                        {incident.severity}
                      </span>
                    </td>
                    <td className="text-slate-500">{incident.time}</td>
                  </tr>
                ))}
              </tbody>
            </table>
          </div>
        </div>

        {/* Quick Actions & Status */}
        <div className="space-y-6">
          {/* Quick Actions */}
          <div className="card">
            <h2 className="text-lg font-semibold text-white mb-6">Quick Actions</h2>
            <div className="space-y-3">
              <Link href="/rules/create" className="btn btn-primary w-full">
                <Plus className="w-4 h-4" />
                Create New Rule
              </Link>
              <Link href="/incidents" className="btn btn-secondary w-full">
                <Eye className="w-4 h-4" />
                Review Incidents
              </Link>
              <button
                onClick={() => alert('Report generation will be available in a future update.')}
                className="btn btn-secondary w-full"
              >
                <FileText className="w-4 h-4" />
                Generate Report
              </button>
            </div>
          </div>

          {/* System Status */}
          <div className="card">
            <h2 className="text-lg font-semibold text-white mb-6">System Status</h2>
            <div className="space-y-4">
              <div className="flex items-center justify-between">
                <span className="text-sm text-slate-300">API Connection</span>
                <span className={`flex items-center gap-2 text-sm ${systemStatus?.api_connection === 'Connected' ? 'text-emerald-400' : 'text-red-400'}`}>
                  <span className={`h-2 w-2 rounded-full ${systemStatus?.api_connection === 'Connected' ? 'bg-emerald-400 animate-pulse-soft' : 'bg-red-400'}`} />
                  {systemStatus?.api_connection || 'Unknown'}
                </span>
              </div>
              <div className="flex items-center justify-between">
                <span className="text-sm text-slate-300">Rule Engine</span>
                <span className={`flex items-center gap-2 text-sm ${systemStatus?.rule_engine === 'Active' ? 'text-emerald-400' : 'text-slate-400'}`}>
                  <span className={`h-2 w-2 rounded-full ${systemStatus?.rule_engine === 'Active' ? 'bg-emerald-400 animate-pulse-soft' : 'bg-slate-400'}`} />
                  {systemStatus?.rule_engine || 'Unknown'}
                </span>
              </div>
              <div className="flex items-center justify-between">
                <span className="text-sm text-slate-300">Event Listener</span>
                <span className={`flex items-center gap-2 text-sm ${systemStatus?.event_listener === 'Running' ? 'text-emerald-400' : 'text-red-400'}`}>
                  <span className={`h-2 w-2 rounded-full ${systemStatus?.event_listener === 'Running' ? 'bg-emerald-400 animate-pulse-soft' : 'bg-red-400'}`} />
                  {systemStatus?.event_listener || 'Unknown'}
                </span>
              </div>
              <div className="pt-4 border-t border-slate-800">
                <div className="flex items-center justify-between">
                  <span className="text-sm text-slate-300">Last Sync</span>
                  <span className="text-sm text-slate-500">
                    {systemStatus?.last_sync
                      ? new Date(systemStatus.last_sync).toLocaleTimeString()
                      : 'Never'}
                  </span>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  );
}
