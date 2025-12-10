'use client';

import { forwardRef, InputHTMLAttributes, TextareaHTMLAttributes, SelectHTMLAttributes } from 'react';
import { FieldError } from 'react-hook-form';
import { AlertCircle } from 'lucide-react';

interface FormFieldProps {
    label: string;
    error?: FieldError;
    required?: boolean;
    helpText?: string;
}

interface InputProps extends InputHTMLAttributes<HTMLInputElement>, FormFieldProps { }
interface TextareaProps extends TextareaHTMLAttributes<HTMLTextAreaElement>, FormFieldProps { }
interface SelectProps extends SelectHTMLAttributes<HTMLSelectElement>, FormFieldProps {
    options: { value: string; label: string }[];
}

/**
 * Accessible form input component with error handling.
 */
export const FormInput = forwardRef<HTMLInputElement, InputProps>(
    ({ label, error, required, helpText, className = '', id, ...props }, ref) => {
        const inputId = id || `input-${label.toLowerCase().replace(/\s+/g, '-')}`;
        const errorId = `${inputId}-error`;
        const helpId = `${inputId}-help`;

        return (
            <div className="space-y-1">
                <label
                    htmlFor={inputId}
                    className="block text-sm font-medium text-slate-300"
                >
                    {label}
                    {required && <span className="text-red-400 ml-1" aria-hidden="true">*</span>}
                </label>
                <input
                    ref={ref}
                    id={inputId}
                    aria-invalid={error ? 'true' : 'false'}
                    aria-describedby={error ? errorId : helpText ? helpId : undefined}
                    aria-required={required}
                    className={`input w-full ${error ? 'border-red-500 focus:border-red-500 focus:ring-red-500' : ''} ${className}`}
                    {...props}
                />
                {helpText && !error && (
                    <p id={helpId} className="text-xs text-slate-500">
                        {helpText}
                    </p>
                )}
                {error && (
                    <p id={errorId} className="flex items-center gap-1 text-xs text-red-400" role="alert">
                        <AlertCircle className="w-3 h-3" aria-hidden="true" />
                        {error.message}
                    </p>
                )}
            </div>
        );
    }
);
FormInput.displayName = 'FormInput';

/**
 * Accessible textarea component with error handling.
 */
export const FormTextarea = forwardRef<HTMLTextAreaElement, TextareaProps>(
    ({ label, error, required, helpText, className = '', id, ...props }, ref) => {
        const textareaId = id || `textarea-${label.toLowerCase().replace(/\s+/g, '-')}`;
        const errorId = `${textareaId}-error`;

        return (
            <div className="space-y-1">
                <label
                    htmlFor={textareaId}
                    className="block text-sm font-medium text-slate-300"
                >
                    {label}
                    {required && <span className="text-red-400 ml-1" aria-hidden="true">*</span>}
                </label>
                <textarea
                    ref={ref}
                    id={textareaId}
                    aria-invalid={error ? 'true' : 'false'}
                    aria-describedby={error ? errorId : undefined}
                    aria-required={required}
                    className={`input w-full min-h-[100px] ${error ? 'border-red-500' : ''} ${className}`}
                    {...props}
                />
                {error && (
                    <p id={errorId} className="flex items-center gap-1 text-xs text-red-400" role="alert">
                        <AlertCircle className="w-3 h-3" aria-hidden="true" />
                        {error.message}
                    </p>
                )}
            </div>
        );
    }
);
FormTextarea.displayName = 'FormTextarea';

/**
 * Accessible select component with error handling.
 */
export const FormSelect = forwardRef<HTMLSelectElement, SelectProps>(
    ({ label, error, required, helpText, options, className = '', id, ...props }, ref) => {
        const selectId = id || `select-${label.toLowerCase().replace(/\s+/g, '-')}`;
        const errorId = `${selectId}-error`;

        return (
            <div className="space-y-1">
                <label
                    htmlFor={selectId}
                    className="block text-sm font-medium text-slate-300"
                >
                    {label}
                    {required && <span className="text-red-400 ml-1" aria-hidden="true">*</span>}
                </label>
                <select
                    ref={ref}
                    id={selectId}
                    aria-invalid={error ? 'true' : 'false'}
                    aria-describedby={error ? errorId : undefined}
                    aria-required={required}
                    className={`input w-full ${error ? 'border-red-500' : ''} ${className}`}
                    {...props}
                >
                    <option value="">Select {label.toLowerCase()}...</option>
                    {options.map((option) => (
                        <option key={option.value} value={option.value}>
                            {option.label}
                        </option>
                    ))}
                </select>
                {error && (
                    <p id={errorId} className="flex items-center gap-1 text-xs text-red-400" role="alert">
                        <AlertCircle className="w-3 h-3" aria-hidden="true" />
                        {error.message}
                    </p>
                )}
            </div>
        );
    }
);
FormSelect.displayName = 'FormSelect';

export default FormInput;
