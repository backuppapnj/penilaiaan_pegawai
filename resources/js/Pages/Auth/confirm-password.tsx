import InputError from '@/components/input-error';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Spinner } from '@/components/ui/spinner';
import AuthLayout from '@/layouts/auth-layout';
import { confirm } from '@/routes/password';
import { Form, Head } from '@inertiajs/react';
import { useRef } from 'react';

interface ConfirmPasswordProps {
    mustVerifyEmail?: boolean;
    status?: string;
}

export default function ConfirmPassword({ status }: ConfirmPasswordProps) {
    const passwordInput = useRef<HTMLInputElement>(null);

    return (
        <AuthLayout
            title="Confirm Password"
            description="This is a secure area of the application. Please confirm your password before continuing."
        >
            <Head title="Confirm Password" />

            <div className="mb-6 text-center">
                <h1 className="text-3xl font-bold text-gray-900 dark:text-gray-100">
                    Confirm Password
                </h1>
                <p className="mt-2 text-sm text-gray-600 dark:text-gray-400">
                    For your security, please confirm your password to continue
                </p>
            </div>

            <Form
                {...confirm.form()}
                id="confirm-password-form"
                resetOnSuccess
                className="flex flex-col gap-6"
            >
                {({ processing, errors }) => (
                    <>
                        <div className="grid gap-6">
                            <div className="grid gap-2">
                                <Label htmlFor="password">Password</Label>
                                <Input
                                    id="password"
                                    name="password"
                                    type="password"
                                    required
                                    autoFocus
                                    tabIndex={1}
                                    autoComplete="current-password"
                                    placeholder="Enter your password"
                                    ref={passwordInput}
                                />
                                <InputError message={errors.password} />
                            </div>

                            <Button
                                type="submit"
                                className="mt-4 w-full"
                                tabIndex={2}
                                disabled={processing}
                                form="confirm-password-form"
                            >
                                {processing && <Spinner className="mr-2" />}
                                Confirm Password
                            </Button>
                        </div>
                    </>
                )}
            </Form>

            {status && (
                <div className="mb-4 text-center text-sm font-medium text-green-600">
                    {status}
                </div>
            )}
        </AuthLayout>
    );
}
