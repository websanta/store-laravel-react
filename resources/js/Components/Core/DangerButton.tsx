import { ButtonHTMLAttributes } from 'react';

export default function DangerButton({
    className = '',
    disabled,
    children,
    ...props
}: ButtonHTMLAttributes<HTMLButtonElement>) {
    return (
        <button
            {...props}
            className={
                `btn btn-error px-4 bg-red-600 text-white hover:bg-red-700 transition-colors duration-200 ` + className
            }
            disabled={disabled}
        >
            {children}
        </button>
    );
}
