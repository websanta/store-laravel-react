import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { PageProps, PaginationProps, Product } from '@/types';
import { Head, Link } from '@inertiajs/react';
import ProductItem from '@/Components/App/ProductItem';

export default function Home({
  products
}: PageProps<{ products: PaginationProps<Product> }>) {

    return (
        <AuthenticatedLayout>
            <Head title="Home" />
            <div className="hero bg-gray-200 h-[300px]">
              <div className="hero-content text-center">
                <div className="max-w-md">
                  <h1 className="text-5xl font-bold">Welcome!</h1>
                  <p className="py-6">
                    We believe that great technology should be both powerful and beautiful. That's why every
                    product in our store is carefully selected for its performance, design, and value.
                  </p>
                  <button className="btn btn-primary px-4 bg-black text-white hover:bg-gray-700 transition-colors duration-200">Shop Now</button>
                </div>
              </div>
            </div>

            <div className="grid grid-cols-1 gap-8 md:grid-cols-2 lg:grid-cols-3 p-8">
              {products.data.map(product => (
                <ProductItem product={product} key={product.id}/>
              ))}
            </div>
        </AuthenticatedLayout>
    );
}
