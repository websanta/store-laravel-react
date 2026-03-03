import React from 'react';
import {usePage, Link} from '@inertiajs/react';
import CurrencyFormatter from '../Core/CurrencyFormatter';
import { productRoute } from '@/helpers';

function MiniCartDropdown() {
  const {totalQuantity, totalPrice, miniCartItems} = usePage().props;

  return (
    <div
      tabIndex={0}
      className="card card-compact dropdown-content bg-base-100 z-1 mt-3 w-[480px] shadow">
      <div className="card-body">
        <span className="text-lg font-bold">{totalQuantity} Items</span>

        <div className={'my-4 max-h-[300px] overflow-auto'}>
          {miniCartItems.length === 0 && (
            <div className={'py-2 text-gray-500 text-center'}>
              You don't have any items in your cart
            </div>
          )}
          {miniCartItems.map(item => (
            <div key={item.id} className={'flex gap-4 p-3'}>
              <Link href={productRoute(item)}
              className={'w-16 h-16 flex justify-center items-center'}>
                <img src={item.image}
                alt={item.title}
                className={'max-w-full max-h-full'} />
              </Link>
              <div className={'flex-1'}>
                <h3 className={'mb-3 font-semibold'}>
                  <Link href={productRoute(item)}>
                    {item.title}
                  </Link>
                </h3>
                <div className={'flex justify-between text-sm'}>
                  <div>
                    Quantity: {item.quantity}
                  </div>
                  <div>
                    <CurrencyFormatter amount={item.quantity * item.price} />
                  </div>
                </div>
              </div>
            </div>
          ))}
        </div>

        <span className="text-lg">
          Subtotal: <CurrencyFormatter amount={totalPrice} />
        </span>
        <div className="card-actions">
          <Link
          href={route('cart.index')}
          className="btn btn-primary px-4 bg-black text-white hover:bg-gray-700 transition-colors duration-200 w-full">
            View cart
          </Link>
        </div>
      </div>
    </div>
  )
}

export default MiniCartDropdown;
