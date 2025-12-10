'use client';

import { useState, useEffect } from 'react';
import { useParams } from 'next/navigation';
import { Loader2, AlertCircle } from 'lucide-react';
import { RuleForm } from '@/components/rules';
import { riskRulesApi } from '@/services';
import type { RiskRule } from '@/types';

export default function EditRulePage() {
    const params = useParams();
    const ruleId = Number(params.id);

    const [rule, setRule] = useState<RiskRule | null>(null);
    const [loading, setLoading] = useState(true);
    const [error, setError] = useState<string | null>(null);

    useEffect(() => {
        const loadRule = async () => {
            try {
                setLoading(true);
                const data = await riskRulesApi.getById(ruleId);
                setRule(data);
            } catch (err) {
                console.error('Error loading rule:', err);
                setError('Failed to load rule. It may not exist.');
            } finally {
                setLoading(false);
            }
        };

        if (ruleId) {
            loadRule();
        }
    }, [ruleId]);

    if (loading) {
        return (
            <div className="flex items-center justify-center h-96">
                <Loader2 className="w-8 h-8 text-indigo-500 animate-spin" />
            </div>
        );
    }

    if (error || !rule) {
        return (
            <div className="flex flex-col items-center justify-center h-96">
                <AlertCircle className="w-12 h-12 text-red-400 mb-4" />
                <h2 className="text-lg font-medium text-white mb-2">Rule Not Found</h2>
                <p className="text-slate-400">{error || 'The requested rule does not exist.'}</p>
            </div>
        );
    }

    return <RuleForm rule={rule} isEditing />;
}
