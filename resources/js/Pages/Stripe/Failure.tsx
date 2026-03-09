import React from 'react';
import { Link } from '@inertiajs/react';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';

function Failure () {
  return (
    <AuthenticatedLayout>
        <div className="container mx-auto px-4 py-8">
            <div className="bg-white rounded-lg shadow p-6 text-center">
                <h1 className="text-2xl font-bold text-red-600 mb-4">
                    Payment Failed
                </h1>
                <p className="mb-4">Something went wrong with your payment.</p>
                <Link
                    href={route('cart.index')}
                    className="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600"
                >
                    Return to Cart
                </Link>
            </div>
        </div>
    </AuthenticatedLayout>
  );
}

export default Failure;
