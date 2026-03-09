import { ButtonHTMLAttributes } from 'react';

export default function SecondaryButton({
    type = 'button',
    className = '',
    disabled,
    children,
    ...props
}: ButtonHTMLAttributes<HTMLButtonElement>) {
    return (
        <button
            {...props}
            type={type}
            className={
                `btn px-4 bg-gray-200 hover:bg-gray-300 transition-colors duration-200` + className
            }
            disabled={disabled}
        >
            {children}
        </button>
    );
}
