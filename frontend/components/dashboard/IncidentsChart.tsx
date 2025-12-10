"use client";

import { useEffect, useState } from 'react';
import { AreaChart, Area, XAxis, YAxis, CartesianGrid, Tooltip, ResponsiveContainer } from 'recharts';
import { ArrowUpRight, ArrowDownRight, Calendar } from 'lucide-react';
import { dashboardApi, type IncidentActivityData } from '@/services';

export default function IncidentsChart() {
    const [chartData, setChartData] = useState<IncidentActivityData[]>([]);
    const [percentageChange, setPercentageChange] = useState<number>(0);
    const [isLoading, setIsLoading] = useState(true);
    const [error, setError] = useState<string | null>(null);

    useEffect(() => {
        const fetchChartData = async () => {
            try {
                setIsLoading(true);
                setError(null);
                const response = await dashboardApi.getIncidentActivity();
                setChartData(response.chart_data);
                setPercentageChange(response.percentage_change);
            } catch (err) {
                console.error('Error fetching incident activity:', err);
                setError('Failed to load chart data');
                // Set empty data on error
                setChartData([]);
            } finally {
                setIsLoading(false);
            }
        };

        fetchChartData();
    }, []);

    const isPositive = percentageChange >= 0;

    return (
        <div className="card">
            <div className="flex items-center justify-between mb-6">
                <div>
                    <h2 className="text-lg font-semibold text-white">Incident Activity</h2>
                    <p className="text-sm text-slate-400">Real-time monitoring of rule violations</p>
                </div>
                <div className="flex items-center gap-2">
                    {!isLoading && !error && (
                        <div className={`flex items-center gap-1 text-sm ${isPositive ? 'text-emerald-400 bg-emerald-500/10' : 'text-red-400 bg-red-500/10'} px-2 py-1 rounded`}>
                            {isPositive ? <ArrowUpRight className="w-4 h-4" /> : <ArrowDownRight className="w-4 h-4" />}
                            <span>{isPositive ? '+' : ''}{percentageChange.toFixed(1)}%</span>
                        </div>
                    )}
                    <button className="p-1.5 text-slate-400 hover:text-white transition-colors rounded-lg hover:bg-slate-800">
                        <Calendar className="w-4 h-4" />
                    </button>
                </div>
            </div>

            <div className="h-[300px] w-full">
                {isLoading ? (
                    <div className="flex items-center justify-center h-full">
                        <div className="text-slate-400">Loading chart data...</div>
                    </div>
                ) : error ? (
                    <div className="flex items-center justify-center h-full">
                        <div className="text-red-400">{error}</div>
                    </div>
                ) : (
                    <ResponsiveContainer width="100%" height="100%">
                        <AreaChart data={chartData} margin={{ top: 10, right: 10, left: 0, bottom: 0 }}>
                        <defs>
                            <linearGradient id="colorIncidents" x1="0" y1="0" x2="0" y2="1">
                                <stop offset="5%" stopColor="#6366f1" stopOpacity={0.3} />
                                <stop offset="95%" stopColor="#6366f1" stopOpacity={0} />
                            </linearGradient>
                        </defs>
                        <CartesianGrid strokeDasharray="3 3" stroke="#1e293b" vertical={false} />
                        <XAxis
                            dataKey="name"
                            stroke="#64748b"
                            fontSize={12}
                            tickLine={false}
                            axisLine={false}
                        />
                        <YAxis
                            stroke="#64748b"
                            fontSize={12}
                            tickLine={false}
                            axisLine={false}
                            tickFormatter={(value) => `${value}`}
                        />
                        <Tooltip
                            contentStyle={{
                                backgroundColor: '#0f172a',
                                borderColor: '#1e293b',
                                borderRadius: '0.5rem',
                                color: '#f8fafc'
                            }}
                            itemStyle={{ color: '#818cf8' }}
                        />
                        <Area
                            type="monotone"
                            dataKey="incidents"
                            stroke="#6366f1"
                            strokeWidth={2}
                            fillOpacity={1}
                            fill="url(#colorIncidents)"
                        />
                    </AreaChart>
                </ResponsiveContainer>
                )}
            </div>
        </div>
    );
}
