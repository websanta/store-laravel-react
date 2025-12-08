import ApplicationLogo from '@/Components/App/ApplicationLogo';
import { Link } from '@inertiajs/react';
import { PropsWithChildren } from 'react';
import { Navbar } from '@/Components/App/Navbar';

export default function Guest({ children }: PropsWithChildren) {
    return (
      <div className="min-h-screen bg-gray-100 flex flex-col">
        <Navbar />

          <div className="flex-1 flex flex-col items-center bg-gray-100 pt-6 sm:justify-center sm:pt-0">
              <div>
                  <Link href="/">
                      <ApplicationLogo className="h-20 w-20 fill-current text-gray-500" />
                  </Link>
              </div>

              <div className="mt-6 w-full overflow-hidden bg-white px-6 py-4 shadow-md sm:max-w-md sm:rounded-lg">
                  {children}
              </div>
          </div>
        </div>
    );
}
