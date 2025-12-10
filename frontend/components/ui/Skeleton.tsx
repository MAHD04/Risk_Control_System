import { Loader2 } from 'lucide-react';

interface SkeletonProps {
    className?: string;
}

/**
 * Basic skeleton loading placeholder.
 */
export function Skeleton({ className = '' }: SkeletonProps) {
    return (
        <div className={`animate-pulse bg-slate-800 rounded ${className}`} />
    );
}

interface TableSkeletonProps {
    rows?: number;
    columns?: number;
}

/**
 * Skeleton for table loading state.
 */
export function TableSkeleton({ rows = 5, columns = 4 }: TableSkeletonProps) {
    return (
        <div className="table-container">
            <table className="table">
                <thead>
                    <tr>
                        {Array.from({ length: columns }).map((_, i) => (
                            <th key={i}>
                                <Skeleton className="h-4 w-20" />
                            </th>
                        ))}
                    </tr>
                </thead>
                <tbody>
                    {Array.from({ length: rows }).map((_, rowIndex) => (
                        <tr key={rowIndex}>
                            {Array.from({ length: columns }).map((_, colIndex) => (
                                <td key={colIndex}>
                                    <Skeleton className="h-4 w-full" />
                                </td>
                            ))}
                        </tr>
                    ))}
                </tbody>
            </table>
        </div>
    );
}

interface CardSkeletonProps {
    count?: number;
}

/**
 * Skeleton for card grid loading state.
 */
export function CardSkeleton({ count = 4 }: CardSkeletonProps) {
    return (
        <div className="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-4">
            {Array.from({ length: count }).map((_, i) => (
                <div key={i} className="card">
                    <div className="flex items-center justify-between mb-4">
                        <Skeleton className="h-10 w-10 rounded-lg" />
                        <Skeleton className="h-4 w-12" />
                    </div>
                    <Skeleton className="h-8 w-20 mb-2" />
                    <Skeleton className="h-4 w-24" />
                </div>
            ))}
        </div>
    );
}

interface PageLoaderProps {
    message?: string;
}

/**
 * Full page loading indicator.
 */
export function PageLoader({ message = 'Loading...' }: PageLoaderProps) {
    return (
        <div className="flex flex-col items-center justify-center min-h-[400px]">
            <Loader2 className="w-8 h-8 text-indigo-500 animate-spin mb-4" />
            <p className="text-slate-400">{message}</p>
        </div>
    );
}

export default Skeleton;
