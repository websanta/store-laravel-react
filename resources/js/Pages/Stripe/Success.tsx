import { Head, Link } from '@inertiajs/react';
import CurrencyFormatter from '@/Components/Core/CurrencyFormatter';
import {CheckCircleIcon} from '@heroicons/react/24/outline';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import {PageProps, Order} from '@/types';

function Success ({orders}: PageProps<{orders: Order[]}>) {
  return (
    <AuthenticatedLayout>
      <Head title="Payment was completed" />
        {/* <pre>{JSON.stringify(order, undefined, 2)}</pre> */}
        <div className="w-[480px] mx-auto px-4 py-8">
            <div className="flex flex-col gap-2 items-center">
              <div className="text-6xl text-green-600">
                <CheckCircleIcon className={"size-24"} />
              </div>
              <div className="text-3xl">
                Payment was completed
              </div>
            </div>
            <div className="my-6 text-lg">
              Thanks for your purchase! Your payment was successfuly completed.
            </div>
            {orders.map(order => (
              <div key={order.id} className="bg-white rounded-lg p-6 mb-4">
                <h3 className="text-3xl mb-3">Order Summary</h3>
                <div className="flex justify-between mb-2 font-bold">
                  <div className="text-gray-400">
                    Seller
                  </div>
                  <div>
                    <Link href="#" className="hover:underline">
                      {order.vendorUser.store_name}
                    </Link>
                  </div>
                </div>
                <div className="flex justify-between mb-2">
                  <div className="text-gray-400">
                    Order Number
                  </div>
                  <div>
                    <Link href="#" className="hover:underline">#{order.id}</Link>
                  </div>
                </div>
                <div className="flex justify-between mb-3">
                  <div className="text-gray-400">
                    Items
                  </div>
                  <div>
                    {order.orderItems.length}
                  </div>
                </div>
                <div className="flex justify-between mb-3">
                  <div className="text-gray-400">
                    Total
                  </div>
                  <div>
                    <CurrencyFormatter amount={order.total_price} />
                  </div>
                </div>
                <div className="flex justify-between mt-4">
                  <Link href="#" className="btn btn-primary px-4 bg-black text-white hover:bg-gray-700 transition-colors duration-200">
                    View Order Details
                  </Link>
                  <Link href={route('dashboard')} className="btn">
                    Back to Homepage
                  </Link>
                </div>
              </div>
            ))}
        </div>
    </AuthenticatedLayout>
  );
}

export default Success;
