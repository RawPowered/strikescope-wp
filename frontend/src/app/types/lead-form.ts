export interface LeadFormConfig {
  restUrl: string;
  helpTicketRestUrl?: string;
  nonce: string;
  turnstileSiteKey: string;
  phoneDisplay: string;
  formStartedAt: number;
  enabledServices?: string[];
  helpDeskEnabled?: boolean;
  logoUrl?: string;
  companyName?: string;
  footerText?: string;
  colors?: {
    primary?: string;
    accent?: string;
    background?: string;
    secondary?: string;
    border?: string;
    muted?: string;
    text?: string;
  };
}

export interface SubmitErrorBody {
  code?: string;
  message?: string;
}

declare global {
  interface Window {
    strikeScopeForm?: LeadFormConfig;
    turnstile?: {
      render: (
        container: HTMLElement,
        options: {
          sitekey: string;
          callback: (token: string) => void;
          'expired-callback'?: () => void;
          'error-callback'?: () => void;
        },
      ) => string;
      reset: (widgetId?: string) => void;
    };
  }
}

export {};
