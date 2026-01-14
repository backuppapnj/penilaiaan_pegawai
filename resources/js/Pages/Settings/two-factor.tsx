import HeadingSmall from '@/components/heading-small';
import TwoFactorRecoveryCodes from '@/components/two-factor-recovery-codes';
import TwoFactorSetupModal from '@/components/two-factor-setup-modal';
import { Button } from '@/components/ui/button';
import { useTwoFactorAuth } from '@/hooks/use-two-factor-auth';
import SettingsLayout from '@/layouts/settings/layout';
import { disable } from '@/routes/two-factor';
import { Form, Head } from '@inertiajs/react';
import { Shield, ShieldAlert, ShieldCheck } from 'lucide-react';
import { useCallback, useState } from 'react';

interface TwoFactorProps {
    twoFactorEnabled: boolean;
    requiresConfirmation: boolean;
}

export default function TwoFactor({
    twoFactorEnabled,
    requiresConfirmation,
}: TwoFactorProps) {
    const [showSetupModal, setShowSetupModal] = useState(false);
    const [showConfirmDisable, setShowConfirmDisable] = useState(false);

    const {
        qrCodeSvg,
        manualSetupKey,
        recoveryCodesList,
        errors,
        clearSetupData,
        fetchSetupData,
        fetchRecoveryCodes,
    } = useTwoFactorAuth();

    const handleEnableClick = useCallback(() => {
        setShowSetupModal(true);
    }, []);

    const handleDisableClick = useCallback(() => {
        setShowConfirmDisable(true);
    }, []);

    const handleConfirmDisable = useCallback(() => {
        setShowConfirmDisable(false);
    }, []);

    const handleCloseSetupModal = useCallback(() => {
        setShowSetupModal(false);
        clearSetupData();
    }, [clearSetupData]);

    const handleCloseConfirmDisable = useCallback(() => {
        setShowConfirmDisable(false);
    }, []);

    return (
        <SettingsLayout>
            <Head title="Two-Factor Authentication Settings" />

            <div className="space-y-6">
                <HeadingSmall
                    title="Two-Factor Authentication"
                    description="Add an extra layer of security to your account"
                />

                <div className="space-y-4">
                    {twoFactorEnabled ? (
                        <div className="rounded-lg border border-green-100 bg-green-50 p-4 dark:border-green-200/10 dark:bg-green-700/10">
                            <div className="flex items-start gap-3">
                                <ShieldCheck className="mt-0.5 h-5 w-5 text-green-600 dark:text-green-400" />
                                <div className="flex-1">
                                    <h3 className="font-medium text-green-900 dark:text-green-100">
                                        Two-factor authentication is enabled
                                    </h3>
                                    <p className="mt-1 text-sm text-green-700 dark:text-green-300">
                                        Your account is protected with
                                        two-factor authentication.
                                    </p>
                                </div>
                            </div>
                        </div>
                    ) : (
                        <div className="rounded-lg border border-yellow-100 bg-yellow-50 p-4 dark:border-yellow-200/10 dark:bg-yellow-700/10">
                            <div className="flex items-start gap-3">
                                <ShieldAlert className="mt-0.5 h-5 w-5 text-yellow-600 dark:text-yellow-400" />
                                <div className="flex-1">
                                    <h3 className="font-medium text-yellow-900 dark:text-yellow-100">
                                        Two-factor authentication is not enabled
                                    </h3>
                                    <p className="mt-1 text-sm text-yellow-700 dark:text-yellow-300">
                                        Enable two-factor authentication to add
                                        an extra layer of security to your
                                        account.
                                    </p>
                                </div>
                            </div>
                        </div>
                    )}

                    <div className="flex gap-3">
                        {!twoFactorEnabled ? (
                            <Button
                                type="button"
                                onClick={handleEnableClick}
                                className="gap-2"
                            >
                                <Shield className="h-4 w-4" />
                                Enable Two-Factor Authentication
                            </Button>
                        ) : (
                            <Button
                                type="button"
                                variant="destructive"
                                onClick={handleDisableClick}
                                className="gap-2"
                            >
                                <ShieldAlert className="h-4 w-4" />
                                Disable Two-Factor Authentication
                            </Button>
                        )}
                    </div>

                    {twoFactorEnabled && (
                        <TwoFactorRecoveryCodes
                            recoveryCodesList={recoveryCodesList}
                            fetchRecoveryCodes={fetchRecoveryCodes}
                            errors={errors}
                        />
                    )}
                </div>

                <TwoFactorSetupModal
                    isOpen={showSetupModal}
                    onClose={handleCloseSetupModal}
                    requiresConfirmation={requiresConfirmation}
                    twoFactorEnabled={twoFactorEnabled}
                    qrCodeSvg={qrCodeSvg}
                    manualSetupKey={manualSetupKey}
                    clearSetupData={clearSetupData}
                    fetchSetupData={fetchSetupData}
                    errors={errors}
                />

                {showConfirmDisable && (
                    <Form
                        {...disable.form()}
                        onSuccess={handleConfirmDisable}
                        options={{ preserveScroll: true }}
                    >
                        {({ processing }) => (
                            <div className="rounded-lg border border-red-100 bg-red-50 p-4 dark:border-red-200/10 dark:bg-red-700/10">
                                <h3 className="mb-2 font-medium text-red-900 dark:text-red-100">
                                    Are you sure you want to disable two-factor
                                    authentication?
                                </h3>
                                <p className="mb-4 text-sm text-red-700 dark:text-red-300">
                                    This will make your account less secure. You
                                    can enable it again at any time.
                                </p>
                                <div className="flex gap-3">
                                    <Button
                                        type="button"
                                        variant="secondary"
                                        onClick={handleCloseConfirmDisable}
                                        disabled={processing}
                                    >
                                        Cancel
                                    </Button>
                                    <Button
                                        type="submit"
                                        variant="destructive"
                                        disabled={processing}
                                    >
                                        {processing
                                            ? 'Disabling...'
                                            : 'Disable Two-Factor Authentication'}
                                    </Button>
                                </div>
                            </div>
                        )}
                    </Form>
                )}
            </div>
        </SettingsLayout>
    );
}
