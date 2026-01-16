import PasswordController from '@/actions/App/Http/Controllers/Settings/PasswordController';
import AlertError from '@/components/alert-error';
import HeadingSmall from '@/components/heading-small';
import InputError from '@/components/input-error';
import { Alert, AlertDescription, AlertTitle } from '@/components/ui/alert';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import SettingsLayout from '@/layouts/settings/layout';
import { Head, useForm } from '@inertiajs/react';
import { CheckCircle2, Eye, EyeOff } from 'lucide-react';
import { type FormEvent, useRef, useState } from 'react';

export default function Password() {
    const passwordInput = useRef<HTMLInputElement>(null);
    const currentPasswordInput = useRef<HTMLInputElement>(null);
    const [showCurrentPassword, setShowCurrentPassword] = useState(false);
    const [showNewPassword, setShowNewPassword] = useState(false);
    const [showConfirmPassword, setShowConfirmPassword] = useState(false);

    const { data, setData, put, errors, processing, recentlySuccessful, reset } =
        useForm({
            current_password: '',
            password: '',
            password_confirmation: '',
        });

    const errorMessages = Object.values(errors);

    const submit = (e: FormEvent<HTMLFormElement>) => {
        e.preventDefault();

        put(PasswordController.update.url(), {
            preserveScroll: true,
            onSuccess: () => {
                reset();
            },
            onError: (errs) => {
                if (errs.password) {
                    reset('password', 'password_confirmation');
                    passwordInput.current?.focus();
                }

                if (errs.current_password) {
                    reset('current_password');
                    currentPasswordInput.current?.focus();
                }
            },
        });
    };

    return (
        <SettingsLayout>
            <Head title="Password Settings" />

            <div className="space-y-6">
                <HeadingSmall
                    title="Update password"
                    description="Ensure your account is using a long, random password to stay secure"
                />

                <form
                    id="password-form"
                    onSubmit={submit}
                    className="space-y-6"
                >
                    {errorMessages.length > 0 && (
                        <AlertError
                            title="Gagal memperbarui password"
                            errors={errorMessages}
                        />
                    )}

                    {recentlySuccessful && (
                        <Alert className="border-green-200 bg-green-50 text-green-800 dark:border-green-900/50 dark:bg-green-900/30 dark:text-green-100">
                            <CheckCircle2 className="h-4 w-4" />
                            <AlertTitle>Password diperbarui</AlertTitle>
                            <AlertDescription>
                                Password Anda berhasil disimpan.
                            </AlertDescription>
                        </Alert>
                    )}

                    <div className="grid gap-2">
                        <Label htmlFor="current_password">Current Password</Label>
                        <div className="relative">
                            <Input
                                id="current_password"
                                name="current_password"
                                type={showCurrentPassword ? 'text' : 'password'}
                                value={data.current_password}
                                onChange={(e) =>
                                    setData('current_password', e.target.value)
                                }
                                ref={currentPasswordInput}
                                autoComplete="current-password"
                                className="pr-20"
                            />
                            <button
                                type="button"
                                onClick={() =>
                                    setShowCurrentPassword((prev) => !prev)
                                }
                                className="absolute right-2 top-1/2 -translate-y-1/2 text-sm font-medium text-gray-600 hover:text-gray-900 dark:text-gray-300 dark:hover:text-gray-100"
                                aria-label={
                                    showCurrentPassword
                                        ? 'Sembunyikan password'
                                        : 'Lihat password'
                                }
                            >
                                {showCurrentPassword ? (
                                    <span className="inline-flex items-center gap-1">
                                        <EyeOff className="size-4" /> Sembunyikan
                                    </span>
                                ) : (
                                    <span className="inline-flex items-center gap-1">
                                        <Eye className="size-4" /> Lihat
                                    </span>
                                )}
                            </button>
                        </div>

                        <InputError message={errors.current_password} />
                    </div>

                    <div className="grid gap-2">
                        <Label htmlFor="password">New Password</Label>
                        <div className="relative">
                            <Input
                                id="password"
                                name="password"
                                type={showNewPassword ? 'text' : 'password'}
                                value={data.password}
                                onChange={(e) =>
                                    setData('password', e.target.value)
                                }
                                ref={passwordInput}
                                autoComplete="new-password"
                                className="pr-20"
                            />
                            <button
                                type="button"
                                onClick={() =>
                                    setShowNewPassword((prev) => !prev)
                                }
                                className="absolute right-2 top-1/2 -translate-y-1/2 text-sm font-medium text-gray-600 hover:text-gray-900 dark:text-gray-300 dark:hover:text-gray-100"
                                aria-label={
                                    showNewPassword
                                        ? 'Sembunyikan password'
                                        : 'Lihat password'
                                }
                            >
                                {showNewPassword ? (
                                    <span className="inline-flex items-center gap-1">
                                        <EyeOff className="size-4" /> Sembunyikan
                                    </span>
                                ) : (
                                    <span className="inline-flex items-center gap-1">
                                        <Eye className="size-4" /> Lihat
                                    </span>
                                )}
                            </button>
                        </div>

                        <InputError message={errors.password} />
                    </div>

                    <div className="grid gap-2">
                        <Label htmlFor="password_confirmation">
                            Confirm Password
                        </Label>
                        <div className="relative">
                            <Input
                                id="password_confirmation"
                                name="password_confirmation"
                                type={
                                    showConfirmPassword ? 'text' : 'password'
                                }
                                value={data.password_confirmation}
                                onChange={(e) =>
                                    setData(
                                        'password_confirmation',
                                        e.target.value,
                                    )
                                }
                                autoComplete="new-password"
                                className="pr-20"
                            />
                            <button
                                type="button"
                                onClick={() =>
                                    setShowConfirmPassword((prev) => !prev)
                                }
                                className="absolute right-2 top-1/2 -translate-y-1/2 text-sm font-medium text-gray-600 hover:text-gray-900 dark:text-gray-300 dark:hover:text-gray-100"
                                aria-label={
                                    showConfirmPassword
                                        ? 'Sembunyikan password'
                                        : 'Lihat password'
                                }
                            >
                                {showConfirmPassword ? (
                                    <span className="inline-flex items-center gap-1">
                                        <EyeOff className="size-4" /> Sembunyikan
                                    </span>
                                ) : (
                                    <span className="inline-flex items-center gap-1">
                                        <Eye className="size-4" /> Lihat
                                    </span>
                                )}
                            </button>
                        </div>

                        <InputError message={errors.password_confirmation} />
                    </div>

                    <div className="flex items-center gap-4">
                        <Button type="submit" disabled={processing}>
                            Save
                        </Button>

                        {recentlySuccessful && (
                            <p className="text-sm text-gray-600 dark:text-gray-400">
                                Saved.
                            </p>
                        )}
                    </div>
                </form>
            </div>
        </SettingsLayout>
    );
}
