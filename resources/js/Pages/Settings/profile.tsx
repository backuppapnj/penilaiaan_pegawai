import ProfileController from '@/actions/App/Http/Controllers/Settings/ProfileController';
import DeleteUser from '@/components/delete-user';
import HeadingSmall from '@/components/heading-small';
import InputError from '@/components/input-error';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import SettingsLayout from '@/layouts/settings/layout';
import { Head, useForm, usePage } from '@inertiajs/react';
import { type SharedData } from '@/types';
import { type FormEvent, useRef } from 'react';

interface ProfileProps {
    mustVerifyEmail: boolean;
    status?: string;
}

export default function Profile({ mustVerifyEmail, status }: ProfileProps) {
    const { auth } = usePage<SharedData>().props;
    const user = auth.user;

    const nameInput = useRef<HTMLInputElement>(null);
    const emailInput = useRef<HTMLInputElement>(null);

    const { data, setData, patch, errors, processing, recentlySuccessful } =
        useForm({
            name: user.name,
            email: user.email,
        });

    const submit = (e: FormEvent<HTMLFormElement>) => {
        e.preventDefault();

        patch(ProfileController.update.url(), {
            preserveScroll: true,
            onError: (errs) => {
                if (errs.name) {
                    nameInput.current?.focus();
                }

                if (errs.email) {
                    emailInput.current?.focus();
                }
            },
        });
    };

    return (
        <SettingsLayout>
            <Head title="Profile Settings" />

            <div className="space-y-6">
                <HeadingSmall
                    title="Profile information"
                    description="Update your account's profile information and email address"
                />

                <form id="profile-form" onSubmit={submit} className="space-y-6">
                    <div className="grid gap-2">
                        <Label htmlFor="name">Name</Label>

                        <Input
                            id="name"
                            name="name"
                            value={data.name}
                            onChange={(e) => setData('name', e.target.value)}
                            ref={nameInput}
                            autoComplete="name"
                        />

                        <InputError message={errors.name} />
                    </div>

                    <div className="grid gap-2">
                        <Label htmlFor="email">Email</Label>

                        <Input
                            id="email"
                            name="email"
                            type="email"
                            value={data.email}
                            onChange={(e) => setData('email', e.target.value)}
                            ref={emailInput}
                            autoComplete="username"
                        />

                        <InputError message={errors.email} />
                    </div>

                    {mustVerifyEmail && user.email_verified_at === null && (
                        <div>
                            <p className="mt-2 text-sm text-gray-800 dark:text-gray-200">
                                Your email address is unverified.
                                <a
                                    href="#"
                                    className="ms-1 rounded-md text-sm text-gray-600 underline hover:text-gray-900 focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 focus:outline-none dark:text-gray-400 dark:hover:text-gray-100"
                                >
                                    Click here to re-send the verification
                                    email.
                                </a>
                            </p>

                            {status === 'verification-link-sent' && (
                                <div className="mt-2 text-sm font-medium text-green-600 dark:text-green-400">
                                    A new verification link has been sent to
                                    your email address.
                                </div>
                            )}
                        </div>
                    )}

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

                <DeleteUser />
            </div>
        </SettingsLayout>
    );
}
