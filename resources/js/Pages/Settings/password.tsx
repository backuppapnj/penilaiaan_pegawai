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
import { CheckCircle2 } from 'lucide-react';
import { type FormEvent, useRef } from 'react';

export default function Password() {
    const passwordInput = useRef<HTMLInputElement>(null);
    const currentPasswordInput = useRef<HTMLInputElement>(null);

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

                        <Input
                            id="current_password"
                            name="current_password"
                            type="password"
                            value={data.current_password}
                            onChange={(e) =>
                                setData('current_password', e.target.value)
                            }
                            ref={currentPasswordInput}
                            autoComplete="current-password"
                        />

                        <InputError message={errors.current_password} />
                    </div>

                    <div className="grid gap-2">
                        <Label htmlFor="password">New Password</Label>

                        <Input
                            id="password"
                            name="password"
                            type="password"
                            value={data.password}
                            onChange={(e) => setData('password', e.target.value)}
                            ref={passwordInput}
                            autoComplete="new-password"
                        />

                        <InputError message={errors.password} />
                    </div>

                    <div className="grid gap-2">
                        <Label htmlFor="password_confirmation">
                            Confirm Password
                        </Label>

                        <Input
                            id="password_confirmation"
                            name="password_confirmation"
                            type="password"
                            value={data.password_confirmation}
                            onChange={(e) =>
                                setData('password_confirmation', e.target.value)
                            }
                            autoComplete="new-password"
                        />

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
