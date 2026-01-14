import InputError from '@/components/input-error';
import TextLink from '@/components/text-link';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Spinner } from '@/components/ui/spinner';
import AuthLayout from '@/layouts/auth-layout';
import { login } from '@/routes';
import { email } from '@/routes/password';
import { Form, Head } from '@inertiajs/react';

interface ForgotPasswordProps {
    status?: string;
}

export default function ForgotPassword({ status }: ForgotPasswordProps) {
    return (
        <AuthLayout
            title="Lupa Password"
            description="Masukkan NIP Anda untuk menerima link reset password"
        >
            <Head title="Lupa Password" />

            <div className="mb-6 text-center">
                <h1 className="text-2xl font-bold text-gray-900 dark:text-gray-100">
                    Lupa Password?
                </h1>
                <p className="mt-2 text-sm text-gray-600 dark:text-gray-400">
                    Masukkan NIP Anda dan kami akan mengirimkan link untuk reset
                    password
                </p>
            </div>

            <Form {...email.form()} className="flex flex-col gap-6">
                {({ processing, errors, recentlySuccessful }) => (
                    <>
                        <div className="grid gap-6">
                            <div className="grid gap-2">
                                <Label htmlFor="nip">
                                    NIP (Nomor Induk Pegawai)
                                </Label>
                                <Input
                                    id="nip"
                                    type="text"
                                    name="email"
                                    required
                                    autoFocus
                                    tabIndex={1}
                                    autoComplete="username"
                                    placeholder="Masukkan NIP"
                                />
                                <InputError message={errors.email} />
                            </div>

                            <Button
                                type="submit"
                                className="mt-4 w-full"
                                tabIndex={2}
                                disabled={processing}
                            >
                                {processing && <Spinner className="mr-2" />}
                                Kirim Link Reset Password
                            </Button>
                        </div>

                        {(status || recentlySuccessful) && (
                            <div className="mb-4 text-center text-sm font-medium text-green-600">
                                {status ||
                                    'Link reset password telah dikirim ke email Anda'}
                            </div>
                        )}
                    </>
                )}
            </Form>

            <div className="mt-6 text-center">
                <TextLink href={login.url()} className="text-sm">
                    Kembali ke Halaman Login
                </TextLink>
            </div>
        </AuthLayout>
    );
}
