export type User = {
    id: number;
    name: string;
    email: string;
    avatar?: string;
    email_verified_at: string | null;
    created_at: string;
    updated_at: string;
    is_onboarded: boolean;
    must_reset_password: boolean;
    [key: string]: unknown;
};

export type Auth = {
    user: User;
    security: {
        isVerified: boolean;
        isOnboarded: boolean;
        mustResetPassword: boolean;
        hasTransactionPin: boolean;
        hasTwoFactorAuthentication: boolean;
    };
};

export type TwoFactorConfigContent = {
    title: string;
    description: string;
    buttonText: string;
};
