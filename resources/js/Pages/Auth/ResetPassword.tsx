import InputError from '@/components/input-error';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Spinner } from '@/components/ui/spinner';
import AuthLayout from '@/layouts/auth-layout';
import { update } from '@/routes/password';
import { Form, Head } from '@inertiajs/react';

interface ResetPasswordProps {
    email: string;
    token: string;
}

export default function ResetPassword({ email, token }: ResetPasswordProps) {
    return (
        <AuthLayout
            title="Reset Password"
            description="Masukkan password baru Anda"
        >
            <Head title="Reset Password" />

            <div className="mb-6 text-center">
                <h1 className="text-2xl font-bold text-gray-900 dark:text-gray-100">
                    Reset Password
                </h1>
                <p className="mt-2 text-sm text-gray-600 dark:text-gray-400">
                    Masukkan password baru untuk akun Anda
                </p>
            </div>

            <Form {...update.form()} className="flex flex-col gap-6">
                {({ processing, errors }) => (
                    <>
                        <div className="grid gap-6">
                            <input type="hidden" name="token" value={token} />

                            <div className="grid gap-2">
                                <Label htmlFor="email">NIP</Label>
                                <Input
                                    id="email"
                                    type="text"
                                    name="email"
                                    value={email}
                                    required
                                    autoFocus
                                    tabIndex={1}
                                    autoComplete="username"
                                    readOnly
                                />
                                <InputError message={errors.email} />
                            </div>

                            <div className="grid gap-2">
                                <Label htmlFor="password">Password Baru</Label>
                                <Input
                                    id="password"
                                    type="password"
                                    name="password"
                                    required
                                    tabIndex={2}
                                    autoComplete="new-password"
                                    placeholder="Masukkan password baru"
                                />
                                <InputError message={errors.password} />
                            </div>

                            <div className="grid gap-2">
                                <Label htmlFor="password_confirmation">
                                    Konfirmasi Password
                                </Label>
                                <Input
                                    id="password_confirmation"
                                    type="password"
                                    name="password_confirmation"
                                    required
                                    tabIndex={3}
                                    autoComplete="new-password"
                                    placeholder="Ulangi password baru"
                                />
                                <InputError
                                    message={errors.password_confirmation}
                                />
                            </div>

                            <Button
                                type="submit"
                                className="mt-4 w-full"
                                tabIndex={4}
                                disabled={processing}
                            >
                                {processing && <Spinner className="mr-2" />}
                                Reset Password
                            </Button>
                        </div>
                    </>
                )}
            </Form>
        </AuthLayout>
    );
}
