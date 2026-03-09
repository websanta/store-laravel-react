import ApplicationLogo from '@/Components/App/ApplicationLogo';
import Dropdown from '@/Components/Core/Dropdown';
import NavLink from '@/Components/Core/NavLink';
import ResponsiveNavLink from '@/Components/Core/ResponsiveNavLink';
import { Link, usePage } from '@inertiajs/react';
import { PropsWithChildren, ReactNode, useState, useEffect, useRef } from 'react';
import { Navbar } from '@/Components/App/Navbar';

export default function AuthenticatedLayout({
    header,
    children,
}: PropsWithChildren<{ header?: ReactNode }>) {
    const props = usePage().props;
    const user = usePage().props.auth.user;

    const [successMessages, setSuccessMessages] = useState<any[]>([]);
    const timeoutRefs = useRef<{ [key: number]: ReturnType<typeof setTimeout>}>([]);

    const [showingNavigationDropdown, setShowingNavigationDropdown] =
        useState(false);

    useEffect(() => {
      if(props.success.message) {
        const newMessage = {
          ...props.success,
          id: props.success.time, // Use the time as unique ID
        };

        // Add the new message to the list
        setSuccessMessages((prevMessages) => [newMessage, ...prevMessages]);

        // Set atimeout for this specific message
        const timeoutId = setTimeout(() => {
          // Use a functional update to ensure that the latest state is used
          setSuccessMessages((prevMessages) =>
            prevMessages.filter((msg) => msg.id !== newMessage.id)
          );

          // Clear the timeout from reference after execution
          delete timeoutRefs.current[newMessage.id];
        }, 5000);

        // Store the timeout ID in the reference
        timeoutRefs.current[newMessage.id] = timeoutId;
      }
    }, [props.success]);

    return (
        <div className="min-h-screen bg-gray-100">
          <Navbar />

          {props.success && props.from_checkout &&
            <div className="container mx-auto px-8 mt-8">
              <div className="alert alert-success">
                {props.success}
              </div>
            </div>
          }

          {props.error &&
            <div className="container mx-auto px-8 mt-8">
              <div className="alert alert-error">
                {props.error}
              </div>
            </div>
          }

          {successMessages.length > 0 &&
            <div className="toast toast-top toast-end z-[1000] mt-16">
              {successMessages.map((msg) => (
                <div key={msg.id} className="alert alert-success">
                  <span>{msg.message}</span>
                </div>
              ))}
            </div>
          }

            <main>{children}</main>
        </div>
    );
}
