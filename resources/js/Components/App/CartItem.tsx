import {React, useState} from 'react';
import {Link, router, useForm} from '@inertiajs/react';
import {CartItem as CartItemType} from '@/types';
import CurrencyFormatter from "@/Components/Core/CurrencyFormatter";
import TextInput from "@/Components/Core/TextInput";
import { productRoute } from '@/helpers';

function CartItem({item}: {item: CartItemType}) {
  const deleteForm = useForm({
    option_ids: item.option_ids
  })

  const [error, setError] = useState('')

  const onDeleteClick = () => {
    deleteForm.delete(route('cart.destroy', item.product_id), {
      preserveScroll: true
    })
  }

  // Handle quantity change and immediately update the form
  const handleQuantityChange = (ev:
    React.ChangeEvent<HTMLInputElement>) => {
      setError('')
      router.put(route('cart.update', item.product_id), {
        quantity: ev.target.value,
        option_ids: item.option_ids
      }, {
        preserveScroll: true,
        onSuccess: () => {
          setError('')
        },
        onError: (errors) => {
          setError(Object.values(errors)[0])
        }
      })
    };

  return (
    <>
      <div key={item.id} className="flex gab-6 p-3">
        <Link href={productRoute(item)} className="w-32 min-w-32 min-h-32 flex justify-center self-start">
          <img src={item.image} alt={item.title} className="max-w-full max-h-full" />
        </Link>
        <div className="flex-1 flex flex-col">
          <div className="flex-1">
            <h3 className="mb-3 text-sm font-semibold">
              <Link href={productRoute(item)}>
                {item.title}
              </Link>
            </h3>
            <div className="text-xs">
              {/*<pre>{JSON.stringify(item, undefined, 2)}</pre>*/}

              {item.options.map(option => (
                <div key={option.id} className="mb-1">
                  <strong className="text-bold">
                    {option.type.name + ': '}
                  </strong>
                  {option.name}
                </div>
              ))}
            </div>
          </div>
          <div className="flex justify-between items-center mt-4">
            <div className="flex gap-2 items-center">
              <div className="text-sm">Quantity:</div>
              <div className={error ? 'tooltip tooltip-open tooltip-error' : ''} data-tip={error}>
                <TextInput type="number"
                defaultValue={item.quantity}
                onBlur={handleQuantityChange}
                className="input-sm w-16"
                min="1"/>
              </div>
              <button onClick={() => onDeleteClick()}
                className="btn btn-sm btn-ghost px-4 bg-red-200 hover:bg-red-400 transition-colors duration-200">
                Delete
              </button>
              <button className="btn btn-sm btn-ghost px-4 bg-gray-200 hover:bg-gray-400 transition-colors duration-200">
                Save for Later
              </button>
            </div>
            <div className="font-bold text-lg">
              <CurrencyFormatter amount={item.price * item.quantity} />
            </div>
          </div>
        </div>
      </div>
      <div className="divider"></div>
    </>
  );
}

export default CartItem;
