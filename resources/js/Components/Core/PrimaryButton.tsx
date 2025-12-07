import { ButtonHTMLAttributes } from 'react';

export default function PrimaryButton({
    className = '',
    disabled,
    children,
    ...props
}: ButtonHTMLAttributes<HTMLButtonElement>) {
    return (
        <button
            {...props}
            className={
                `btn btn-primary px-4 bg-black text-white hover:bg-gray-700 transition-colors duration-200 ` + className
            }
            disabled={disabled}
        >
            {children}
        </button>
    );
}
