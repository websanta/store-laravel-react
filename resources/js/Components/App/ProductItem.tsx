import React from 'react';
import {Product} from '@/types';
import {Link, useForm} from '@inertiajs/react';
import CurrencyFormatter from '@/Components/Core/CurrencyFormatter';

export default function ProductItem({product}: {product: Product}) {

  const form = useForm<{
    option_ids: Record<string, number>;
    quantity: number;
  }>({
    option_ids: {},
    quantity: 1,
  });

  const addToCart = () => {
    form.post(route('cart.store', product.id), {
      preserveScroll: true,
      preserveState: true,
      onError: (err) => {
        console.log(err);
      }
    })
  }

  return(
    <div className="card bg-base-100 shadow-xl">
      <Link href={route('product.show', product.slug)}>
        <figure>
          <img
            src={product.image}
            alt={product.title}
            className="aspect-square object-cover" />
        </figure>
      </Link>
      <div className="card-body">
        <h2 className="card-title">{product.title}</h2>
        <p>
          by <Link href={route('vendor.profile', product.user.store_name)} className="hover:underline">
            {product.user.name}
          </Link>&nbsp;
          in <Link href={route('product.byDepartment', product.department.slug)} className="hover:underline">{product.department.name}</Link>&nbsp;
        </p>
        <div className="card-actions items-center justify-between mt-3">
          <button onClick={addToCart} className="btn btn-primary px-4 bg-black text-white hover:bg-gray-700 transition-colors duration-200">Add to Cart</button>
          <span className="text-2xl">
            <CurrencyFormatter amount={product.price} />
          </span>
        </div>
      </div>
    </div>
  )
}
