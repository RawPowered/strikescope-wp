import { useEffect, useMemo, useRef, useState } from 'react';
import { motion, AnimatePresence } from 'motion/react';
import type { LeadFormConfig, SubmitErrorBody } from '../types/lead-form';
import {
  Home,
  Wrench,
  Bath,
  Plus,
  Sun,
  LifeBuoy,
  ArrowRight,
  ArrowLeft,
  CheckCircle2,
  MapPin,
  Phone,
  Mail,
  User,
  Shield,
  Clock,
  Star,
} from 'lucide-react';
import { ImageWithFallback } from './figma/ImageWithFallback';
import { buildFormThemeStyle, DEFAULT_FORM_COLORS } from '../lib/form-theme';

interface FormData {
  serviceType: string[];
  propertyType: string;
  timeline: string;
  budget: string;
  firstName: string;
  lastName: string;
  email: string;
  phone: string;
  address: string;
  city: string;
  state: string;
  zipCode: string;
  additionalDetails: string;
  issueDescription: string;
}

type FormMode = 'quote' | 'help_ticket';

const services = [
  { id: 'roofing', name: 'Roofing', icon: Home, description: 'Roof repair & replacement' },
  { id: 'kitchen', name: 'Kitchen Remodeling', icon: Wrench, description: 'Complete kitchen renovation' },
  { id: 'bathroom', name: 'Bathroom Remodeling', icon: Bath, description: 'Bathroom upgrades & renovation' },
  { id: 'addons', name: 'Home Additions', icon: Plus, description: 'Expand your living space' },
  { id: 'solar', name: 'Commercial Solar', icon: Sun, description: 'Solar panel installation' },
];

const propertyTypes = [
  { id: 'residential', name: 'Residential', desc: 'Single-family or multi-family home' },
  { id: 'commercial', name: 'Commercial', desc: 'Business or commercial property' },
];

const timelines = [
  { id: 'asap', name: 'As soon as possible' },
  { id: '1-3months', name: '1–3 months' },
  { id: '3-6months', name: '3–6 months' },
  { id: '6plus', name: '6+ months' },
  { id: 'just-exploring', name: 'Just exploring options' },
];

const budgets = [
  { id: 'under-10k', name: 'Under $10,000' },
  { id: '10k-25k', name: '$10,000 – $25,000' },
  { id: '25k-50k', name: '$25,000 – $50,000' },
  { id: '50k-100k', name: '$50,000 – $100,000' },
  { id: 'over-100k', name: 'Over $100,000' },
  { id: 'not-sure', name: "Not sure yet" },
];

const DEV_CONFIG: LeadFormConfig = {
  restUrl: '/wp-json/strikescope/v1/lead-form/submit',
  helpTicketRestUrl: '/wp-json/strikescope/v1/help-ticket/submit',
  nonce: 'dev',
  turnstileSiteKey: '',
  phoneDisplay: '',
  formStartedAt: Date.now(),
  footerText: 'Licensed & Insured',
  colors: { ...DEFAULT_FORM_COLORS },
};

const emptyFormData = (): FormData => ({
  serviceType: [],
  propertyType: '',
  timeline: '',
  budget: '',
  firstName: '',
  lastName: '',
  email: '',
  phone: '',
  address: '',
  city: '',
  state: '',
  zipCode: '',
  additionalDetails: '',
  issueDescription: '',
});

function TurnstileField({
  siteKey,
  onToken,
  onExpire,
}: {
  siteKey: string;
  onToken: (token: string) => void;
  onExpire: () => void;
}) {
  const containerRef = useRef<HTMLDivElement>(null);
  const widgetIdRef = useRef<string | null>(null);

  useEffect(() => {
    if (!siteKey || !containerRef.current) {
      return;
    }

    let cancelled = false;

    const renderWidget = () => {
      if (cancelled || !containerRef.current || !window.turnstile) {
        return;
      }
      if (widgetIdRef.current) {
        window.turnstile.reset(widgetIdRef.current);
      }
      widgetIdRef.current = window.turnstile.render(containerRef.current, {
        sitekey: siteKey,
        callback: onToken,
        'expired-callback': onExpire,
        'error-callback': onExpire,
      });
    };

    if (window.turnstile) {
      renderWidget();
      return () => {
        cancelled = true;
      };
    }

    const existing = document.querySelector('script[data-strikescope-turnstile]');
    if (existing) {
      existing.addEventListener('load', renderWidget);
      return () => {
        cancelled = true;
        existing.removeEventListener('load', renderWidget);
      };
    }

    const script = document.createElement('script');
    script.src = 'https://challenges.cloudflare.com/turnstile/v0/api.js?render=explicit';
    script.async = true;
    script.defer = true;
    script.dataset.strikescopeTurnstile = '1';
    script.onload = renderWidget;
    document.head.appendChild(script);

    return () => {
      cancelled = true;
    };
  }, [siteKey, onExpire, onToken]);

  if (!siteKey) {
    return null;
  }

  return <div ref={containerRef} className="flex justify-center pt-2" />;
}

const quoteSteps = [
  { label: 'Service', num: 1 },
  { label: 'Project', num: 2 },
  { label: 'Contact', num: 3 },
  { label: 'Location', num: 4 },
];

const helpSteps = [
  { label: 'Request', num: 1 },
  { label: 'Contact', num: 2 },
  { label: 'Address', num: 3 },
];

function StepIndicator({ current, steps }: { current: number; steps: { label: string; num: number }[] }) {
  return (
    <div className="slf-step-indicator flex items-center justify-center gap-0 mb-5">
      {steps.map((s, i) => (
        <div key={s.num} className="flex items-center">
          <div className="flex flex-col items-center">
            <div
              className={`slf-step-dot w-10 h-10 rounded-full flex items-center justify-center text-sm font-bold transition-all duration-300 ${
                s.num < current
                  ? 'bg-[var(--slf-primary)] text-white shadow-md'
                  : s.num === current
                  ? 'bg-[var(--slf-primary)] text-white shadow-lg ring-4 ring-[var(--slf-primary)]/20'
                  : 'bg-white text-[var(--slf-muted)] border-2 border-[var(--slf-border)]'
              }`}
            >
              {s.num < current ? <CheckCircle2 className="w-4 h-4" /> : s.num}
            </div>
            <span
              className={`slf-step-label mt-1 text-sm font-semibold tracking-wide hidden sm:block ${
                s.num <= current ? 'text-[var(--slf-primary)]' : 'text-[var(--slf-muted)]'
              }`}
            >
              {s.label}
            </span>
          </div>
          {i < steps.length - 1 && (
            <div
              className={`slf-step-line w-12 sm:w-20 h-0.5 mx-1 mb-4 transition-all duration-500 ${
                s.num < current ? 'bg-[var(--slf-primary)]' : 'bg-[var(--slf-border)]'
              }`}
            />
          )}
        </div>
      ))}
    </div>
  );
}

function OptionCard({
  selected,
  onClick,
  children,
}: {
  selected: boolean;
  onClick: () => void;
  children: React.ReactNode;
}) {
  return (
    <motion.button
      whileHover={{ scale: 1.02 }}
      whileTap={{ scale: 0.97 }}
      onClick={onClick}
      className={`slf-option-card w-full p-4 rounded-xl border-2 text-left transition-all duration-200 cursor-pointer ${
        selected
          ? 'border-[var(--slf-primary)] bg-[var(--slf-secondary)] shadow-md'
          : 'border-[var(--slf-border)] bg-white hover:border-[var(--slf-primary)]/50 hover:bg-[var(--slf-secondary)]'
      }`}
    >
      {children}
    </motion.button>
  );
}

function FormInput({
  id,
  label,
  required,
  icon: Icon,
  ...props
}: {
  id: string;
  label: string;
  required?: boolean;
  icon?: React.ComponentType<{ className?: string }>;
} & React.InputHTMLAttributes<HTMLInputElement>) {
  return (
    <div className="slf-field space-y-1.5">
      <label htmlFor={id} className="slf-label block font-semibold text-[var(--slf-text)]" style={{ fontFamily: 'Montserrat, sans-serif' }}>
        {label}
        {required && <span className="text-[var(--slf-primary)] ml-1">*</span>}
      </label>
      <div className="relative">
        {Icon && (
          <Icon className="slf-field-icon absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-[var(--slf-primary)]" />
        )}
        <input
          id={id}
          className={`slf-input w-full rounded-lg border border-[var(--slf-border)] bg-[var(--slf-secondary)] py-3 text-[var(--slf-text)] placeholder:text-[var(--slf-muted)] focus:outline-none focus:border-[var(--slf-primary)] focus:ring-2 focus:ring-[var(--slf-primary)]/20 transition-all ${Icon ? 'pl-10 pr-4' : 'px-4'}`}
          {...props}
        />
      </div>
    </div>
  );
}

export function LeadForm({ config = DEV_CONFIG, embed = false }: { config?: LeadFormConfig; embed?: boolean }) {
  const [formMode, setFormMode] = useState<FormMode>('quote');
  const [step, setStep] = useState(1);
  const [isSubmitted, setIsSubmitted] = useState(false);
  const [isSubmitting, setIsSubmitting] = useState(false);
  const [submitError, setSubmitError] = useState('');
  const [ticketNumber, setTicketNumber] = useState('');
  const [honeypot, setHoneypot] = useState('');
  const [turnstileToken, setTurnstileToken] = useState('');
  const formStartedAtRef = useRef(config.formStartedAt || Date.now());
  const [formData, setFormData] = useState<FormData>(emptyFormData());

  const enabledServiceSet = useMemo(() => {
    const ids = config.enabledServices?.length ? config.enabledServices : services.map((s) => s.id);
    return new Set(ids);
  }, [config.enabledServices]);

  const visibleServices = useMemo(
    () => services.filter((service) => enabledServiceSet.has(service.id)),
    [enabledServiceSet],
  );

  const helpDeskEnabled = config.helpDeskEnabled !== false;

  useEffect(() => {
    if (!helpDeskEnabled && formMode === 'help_ticket') {
      setFormMode('quote');
      setStep(1);
    }
  }, [helpDeskEnabled, formMode]);

  useEffect(() => {
    setFormData((prev) => ({
      ...prev,
      serviceType: prev.serviceType.filter((id) => enabledServiceSet.has(id)),
    }));
  }, [enabledServiceSet]);

  const activeSteps = formMode === 'help_ticket' ? helpSteps : quoteSteps;
  const totalSteps = activeSteps.length;

  const update = (field: keyof FormData, value: string) =>
    setFormData((prev) => ({ ...prev, [field]: value }));

  const selectHelpTicket = () => {
    setFormMode('help_ticket');
    setFormData((prev) => ({ ...prev, serviceType: [] }));
  };

  const toggleService = (id: string) => {
    setFormMode('quote');
    setFormData((prev) => ({
      ...prev,
      serviceType: prev.serviceType.includes(id)
        ? prev.serviceType.filter((s) => s !== id)
        : [...prev.serviceType, id],
    }));
  };

  const nextStep = () => step < totalSteps && setStep(step + 1);
  const prevStep = () => step > 1 && setStep(step - 1);

  const handleSubmit = async () => {
    if (isSubmitting) {
      return;
    }
    if (config.turnstileSiteKey && !turnstileToken) {
      setSubmitError('Please complete the security check before submitting.');
      return;
    }

    setIsSubmitting(true);
    setSubmitError('');

    try {
      const isHelp = formMode === 'help_ticket';
      const submitUrl =
        isHelp && config.helpTicketRestUrl
          ? config.helpTicketRestUrl
          : config.restUrl;

      const payload = isHelp
        ? {
            firstName: formData.firstName,
            lastName: formData.lastName,
            email: formData.email,
            phone: formData.phone,
            address: formData.address,
            city: formData.city,
            state: formData.state,
            zipCode: formData.zipCode,
            issueDescription: formData.issueDescription,
            website: honeypot,
            cfTurnstileResponse: turnstileToken,
            formStartedAt: formStartedAtRef.current,
            _wpnonce: config.nonce,
          }
        : {
            ...formData,
            website: honeypot,
            cfTurnstileResponse: turnstileToken,
            formStartedAt: formStartedAtRef.current,
            _wpnonce: config.nonce,
          };

      const response = await fetch(submitUrl, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'X-WP-Nonce': config.nonce,
        },
        credentials: 'same-origin',
        body: JSON.stringify(payload),
      });

      const body = (await response.json()) as SubmitErrorBody & {
        success?: boolean;
        ticketNumber?: string;
        message?: string;
      };
      if (!response.ok || !body.success) {
        throw new Error(body.message || 'Unable to submit your request. Please try again.');
      }

      if (isHelp && body.ticketNumber) {
        setTicketNumber(body.ticketNumber);
      }
      setIsSubmitted(true);
    } catch (error) {
      setSubmitError(error instanceof Error ? error.message : 'Unable to submit your request.');
    } finally {
      setIsSubmitting(false);
    }
  };

  const isStepValid = () => {
    if (formMode === 'help_ticket') {
      switch (step) {
        case 1:
          return formMode === 'help_ticket' || formData.serviceType.length > 0;
        case 2:
          return (
            formData.firstName !== '' &&
            formData.lastName !== '' &&
            formData.email !== '' &&
            formData.phone !== '' &&
            formData.issueDescription.trim() !== ''
          );
        case 3:
          return formData.address !== '' && formData.city !== '' && formData.state !== '' && formData.zipCode !== '';
        default:
          return false;
      }
    }
    switch (step) {
      case 1: return formData.serviceType.length > 0;
      case 2: return formData.propertyType !== '' && formData.timeline !== '';
      case 3: return formData.firstName !== '' && formData.lastName !== '' && formData.email !== '' && formData.phone !== '';
      case 4: return formData.address !== '' && formData.city !== '' && formData.state !== '' && formData.zipCode !== '';
      default: return false;
    }
  };

  const canSubmit =
    isStepValid() &&
    !isSubmitting &&
    (!config.turnstileSiteKey || Boolean(turnstileToken));

  const resetForm = () => {
    setIsSubmitted(false);
    setFormMode('quote');
    setStep(1);
    setSubmitError('');
    setTicketNumber('');
    setHoneypot('');
    setTurnstileToken('');
    formStartedAtRef.current = Date.now();
    setFormData(emptyFormData());
  };

  return (
    <div
      className={`strikescope-form-branded w-full ${embed ? 'max-w-full px-0 strikescope-form-embed-mode' : 'max-w-2xl mx-auto px-4'}`}
      style={buildFormThemeStyle(config)}
    >

      {!embed && (
        <>
          {/* Logo Header */}
          <div className="flex flex-col items-center mb-8">
            {config.logoUrl ? (
              <ImageWithFallback
                src={config.logoUrl}
                alt={config.companyName || 'Company logo'}
                className="h-12 max-w-[220px] object-contain mb-2"
              />
            ) : config.companyName ? (
              <p className="text-xl font-black text-[var(--slf-text)] mb-2" style={{ fontFamily: 'Montserrat, sans-serif' }}>
                {config.companyName}
              </p>
            ) : null}
            <p className="text-sm font-semibold tracking-widest text-[var(--slf-muted)] uppercase" style={{ fontFamily: 'Montserrat, sans-serif' }}>
              Contact our team
            </p>
          </div>

          {/* Hero Text */}
          <div className="text-center mb-6">
            <h1 className="text-3xl md:text-4xl font-black text-[var(--slf-text)] mb-2 leading-tight" style={{ fontFamily: 'Montserrat, sans-serif' }}>
              Get Your Free Quote
            </h1>
            <p className="text-[var(--slf-muted)] text-base">
              Tell us about your project — we'll respond within 24 hours
            </p>
          </div>
        </>
      )}

      {embed && (
        <div className="slf-embed-header text-center mb-3">
          <h2 className="slf-embed-heading font-black text-[var(--slf-text)] leading-tight" style={{ fontFamily: 'Montserrat, sans-serif' }}>
            Get Your Free Quote
          </h2>
          <p className="slf-embed-subheading text-[var(--slf-muted)]">
            Tell us about your project — we'll respond within 24 hours
          </p>
        </div>
      )}

      {isSubmitted ? (
        <motion.div
          initial={{ opacity: 0, scale: 0.92 }}
          animate={{ opacity: 1, scale: 1 }}
          transition={{ type: 'spring', stiffness: 220, damping: 22 }}
          className="bg-white rounded-2xl shadow-xl border border-[var(--slf-border)] p-10 text-center"
        >
          <motion.div
            initial={{ scale: 0 }}
            animate={{ scale: 1 }}
            transition={{ delay: 0.15, type: 'spring', stiffness: 260 }}
            className="w-20 h-20 rounded-full bg-[var(--slf-secondary)] flex items-center justify-center mx-auto mb-6"
          >
            <CheckCircle2 className="w-10 h-10 text-[var(--slf-primary)]" />
          </motion.div>
          <h2 className="text-3xl font-black text-[var(--slf-text)] mb-3" style={{ fontFamily: 'Montserrat, sans-serif' }}>
            {formMode === 'help_ticket' ? 'Help Ticket Opened!' : 'Request Submitted!'}
          </h2>
          {formMode === 'help_ticket' ? (
            <>
              <p className="text-[var(--slf-muted)] mb-2 text-base">
                Your ticket{' '}
                <span className="font-semibold text-[var(--slf-primary)]">{ticketNumber || 'has been created'}</span>{' '}
                is in our Help Desk queue.
              </p>
              <p className="text-[var(--slf-muted)] text-sm mb-8">
                Check your email at <span className="font-semibold">{formData.email}</span> to continue the conversation. Reply to that message to add updates.
              </p>
            </>
          ) : (
            <>
              <p className="text-[var(--slf-muted)] mb-2 text-base">
                Thank you! Your request for{' '}
                <span className="font-semibold text-[var(--slf-primary)]">
                  {formData.serviceType.map((id) => services.find((s) => s.id === id)?.name).join(', ')}
                </span>{' '}
                is in our hands.
              </p>
              <p className="text-[var(--slf-muted)] text-sm mb-8">
                A team member will contact you within 24 hours at{' '}
                <span className="font-semibold">{formData.phone}</span>.
              </p>
            </>
          )}
          <div className="bg-[var(--slf-secondary)] rounded-xl p-4 mb-8 border border-[var(--slf-border)]">
            <p className="text-xs text-[var(--slf-muted)] font-semibold uppercase tracking-wider mb-1">Questions? Call us directly</p>
            <p className="text-[var(--slf-primary)] font-bold text-lg">{config.phoneDisplay}</p>
            <p className="text-xs text-[var(--slf-muted)]">Mon–Fri 9:00 AM – 5:00 PM</p>
          </div>
          <button
            onClick={resetForm}
            className="px-6 py-2.5 rounded-lg border-2 border-[var(--slf-primary)] text-[var(--slf-primary)] font-bold text-sm hover:bg-[var(--slf-secondary)] transition-colors"
            style={{ fontFamily: 'Montserrat, sans-serif' }}
          >
            Submit Another Request
          </button>
        </motion.div>
      ) : (
        <>
          {/* Step Indicator */}
          <StepIndicator current={step} steps={activeSteps} />

          {/* Card */}
          <AnimatePresence mode="wait">
            <motion.div
              key={step}
              initial={{ opacity: 0, x: 24 }}
              animate={{ opacity: 1, x: 0 }}
              exit={{ opacity: 0, x: -24 }}
              transition={{ duration: 0.25, ease: 'easeInOut' }}
              className="bg-white rounded-2xl shadow-xl border border-[var(--slf-border)] overflow-hidden"
            >
              {/* Card Header */}
              <div className="slf-card-header px-6 py-4 border-b border-[var(--slf-border)] bg-[var(--slf-secondary)]">
                <div className="flex items-center gap-3">
                  <div className="slf-step-badge w-9 h-9 rounded-full bg-[var(--slf-primary)] flex items-center justify-center text-white text-sm font-black shrink-0" style={{ fontFamily: 'Montserrat, sans-serif' }}>
                    {step}
                  </div>
                  <div>
                    <h2 className="slf-step-title text-lg font-black text-[var(--slf-text)] leading-tight" style={{ fontFamily: 'Montserrat, sans-serif' }}>
                      {formMode === 'help_ticket' && step === 1 && 'Existing customer help request'}
                      {formMode === 'help_ticket' && step === 2 && 'Your contact & issue'}
                      {formMode === 'help_ticket' && step === 3 && 'Service address'}
                      {formMode === 'quote' && step === 1 && 'What service do you need?'}
                      {formMode === 'quote' && step === 2 && 'Tell us about your project'}
                      {formMode === 'quote' && step === 3 && 'Your contact information'}
                      {formMode === 'quote' && step === 4 && 'Property location'}
                    </h2>
                    <p className="slf-step-subtitle text-sm text-[var(--slf-muted)] mt-0.5">
                      {formMode === 'help_ticket' && step === 1 && 'Open a help ticket for service at your property'}
                      {formMode === 'help_ticket' && step === 2 && 'How can we reach you and what do you need?'}
                      {formMode === 'help_ticket' && step === 3 && 'Where is the property that needs service?'}
                      {formMode === 'quote' && step === 1 && 'Select the service you are interested in'}
                      {formMode === 'quote' && step === 2 && 'Help us understand your timeline and budget'}
                      {formMode === 'quote' && step === 3 && 'How should we reach you?'}
                      {formMode === 'quote' && step === 4 && 'Where is the project located?'}
                    </p>
                  </div>
                </div>
              </div>

              {/* Card Body */}
              <div className="slf-card-body p-6 space-y-5">
                <div
                  aria-hidden="true"
                  className="absolute left-[-9999px] top-auto h-px w-px overflow-hidden"
                >
                  <label htmlFor="website">Website</label>
                  <input
                    id="website"
                    name="website"
                    tabIndex={-1}
                    autoComplete="off"
                    value={honeypot}
                    onChange={(e) => setHoneypot(e.target.value)}
                  />
                </div>

                {/* Step 1: Service Selection */}
                {step === 1 && (
                  <>
                    <p className="slf-hint text-sm text-[var(--slf-muted)] mb-2">Select all that apply</p>
                    <div className="slf-service-grid">
                      {visibleServices.map((service) => {
                        const Icon = service.icon;
                        const selected = formData.serviceType.includes(service.id);
                        return (
                          <motion.button
                            key={service.id}
                            type="button"
                            whileHover={{ scale: 1.01 }}
                            whileTap={{ scale: 0.98 }}
                            onClick={() => toggleService(service.id)}
                            className={`slf-service-card rounded-xl border-2 text-left transition-all duration-200 cursor-pointer relative ${
                              selected
                                ? 'border-[var(--slf-primary)] bg-[var(--slf-secondary)] shadow-md'
                                : 'border-[var(--slf-border)] bg-white hover:border-[var(--slf-primary)]/50 hover:bg-[var(--slf-secondary)]'
                            }`}
                          >
                            {selected && (
                              <div className="absolute top-2.5 right-2.5 w-5 h-5 rounded-full bg-[var(--slf-primary)] flex items-center justify-center">
                                <CheckCircle2 className="w-3.5 h-3.5 text-white" />
                              </div>
                            )}
                            <div className={`slf-service-icon w-10 h-10 rounded-lg flex items-center justify-center mb-2.5 ${selected ? 'bg-[var(--slf-primary)]' : 'bg-[var(--slf-secondary)]'}`}>
                              <Icon className={`w-5 h-5 ${selected ? 'text-white' : 'text-[var(--slf-primary)]'}`} />
                            </div>
                            <p className="slf-service-title font-bold text-[var(--slf-text)] mb-0.5" style={{ fontFamily: 'Montserrat, sans-serif' }}>
                              {service.name}
                            </p>
                            <p className="slf-service-desc text-[var(--slf-muted)] leading-snug">{service.description}</p>
                          </motion.button>
                        );
                      })}
                    </div>

                    {helpDeskEnabled && (
                      <>
                    <p className="slf-hint text-sm text-[var(--slf-muted)] mt-6 mb-2">Already a customer?</p>
                    <motion.button
                      type="button"
                      whileHover={{ scale: 1.01 }}
                      whileTap={{ scale: 0.98 }}
                      onClick={selectHelpTicket}
                      className={`slf-service-card w-full rounded-xl border-2 text-left transition-all duration-200 cursor-pointer relative p-4 ${
                        formMode === 'help_ticket'
                          ? 'border-[var(--slf-primary)] bg-[var(--slf-secondary)] shadow-md'
                          : 'border-[var(--slf-border)] bg-white hover:border-[var(--slf-primary)]/50 hover:bg-[var(--slf-secondary)]'
                      }`}
                    >
                      {formMode === 'help_ticket' && (
                        <div className="absolute top-2.5 right-2.5 w-5 h-5 rounded-full bg-[var(--slf-primary)] flex items-center justify-center">
                          <CheckCircle2 className="w-3.5 h-3.5 text-white" />
                        </div>
                      )}
                      <div className={`slf-service-icon w-10 h-10 rounded-lg flex items-center justify-center mb-2.5 ${formMode === 'help_ticket' ? 'bg-[var(--slf-primary)]' : 'bg-[var(--slf-secondary)]'}`}>
                        <LifeBuoy className={`w-5 h-5 ${formMode === 'help_ticket' ? 'text-white' : 'text-[var(--slf-primary)]'}`} />
                      </div>
                      <p className="slf-service-title font-bold text-[var(--slf-text)] mb-0.5" style={{ fontFamily: 'Montserrat, sans-serif' }}>
                        Existing Customer
                      </p>
                      <p className="slf-service-desc text-[var(--slf-muted)] leading-snug">Open a Help Ticket</p>
                    </motion.button>
                      </>
                    )}
                  </>
                )}

                {/* Help Step 2: Contact & issue */}
                {step === 2 && formMode === 'help_ticket' && (
                  <div className="space-y-4">
                    <div className="grid grid-cols-2 gap-4">
                      <FormInput
                        id="helpFirstName"
                        label="First Name"
                        required
                        icon={User}
                        placeholder="John"
                        value={formData.firstName}
                        onChange={(e) => update('firstName', e.target.value)}
                      />
                      <FormInput
                        id="helpLastName"
                        label="Last Name"
                        required
                        icon={User}
                        placeholder="Doe"
                        value={formData.lastName}
                        onChange={(e) => update('lastName', e.target.value)}
                      />
                    </div>
                    <FormInput
                      id="helpEmail"
                      label="Email Address"
                      required
                      icon={Mail}
                      type="email"
                      placeholder="john.doe@example.com"
                      value={formData.email}
                      onChange={(e) => update('email', e.target.value)}
                    />
                    <FormInput
                      id="helpPhone"
                      label="Phone Number"
                      required
                      icon={Phone}
                      type="tel"
                      placeholder="(555) 123-4567"
                      value={formData.phone}
                      onChange={(e) => update('phone', e.target.value)}
                    />
                    <div className="slf-field space-y-1.5">
                      <label htmlFor="issueDescription" className="slf-label block font-semibold text-[var(--slf-text)]" style={{ fontFamily: 'Montserrat, sans-serif' }}>
                        Describe your issue <span className="text-[var(--slf-primary)]">*</span>
                      </label>
                      <textarea
                        id="issueDescription"
                        rows={4}
                        placeholder="Tell us what you need help with at your property…"
                        value={formData.issueDescription}
                        onChange={(e) => update('issueDescription', e.target.value)}
                        className="slf-textarea w-full rounded-lg border border-[var(--slf-border)] bg-[var(--slf-secondary)] px-4 py-3 text-[var(--slf-text)] placeholder:text-[var(--slf-muted)] focus:outline-none focus:border-[var(--slf-primary)] focus:ring-2 focus:ring-[var(--slf-primary)]/20 transition-all resize-none"
                      />
                    </div>
                  </div>
                )}

                {/* Quote Step 2: Project Details */}
                {step === 2 && formMode === 'quote' && (
                  <div className="space-y-6">
                    <div className="space-y-2">
                      <p className="slf-section-label font-bold text-[var(--slf-text)] mb-3" style={{ fontFamily: 'Montserrat, sans-serif' }}>
                        Property Type <span className="text-[var(--slf-primary)]">*</span>
                      </p>
                      <div className="grid grid-cols-2 gap-3">
                        {propertyTypes.map((t) => (
                          <OptionCard
                            key={t.id}
                            selected={formData.propertyType === t.id}
                            onClick={() => update('propertyType', t.id)}
                          >
                            <p className="slf-option-title font-bold text-[var(--slf-text)]" style={{ fontFamily: 'Montserrat, sans-serif' }}>{t.name}</p>
                            <p className="slf-option-desc text-[var(--slf-muted)] mt-0.5">{t.desc}</p>
                          </OptionCard>
                        ))}
                      </div>
                    </div>

                    <div className="space-y-2">
                      <p className="slf-section-label font-bold text-[var(--slf-text)] mb-3" style={{ fontFamily: 'Montserrat, sans-serif' }}>
                        Project Timeline <span className="text-[var(--slf-primary)]">*</span>
                      </p>
                      <div className="space-y-2">
                        {timelines.map((t) => (
                          <OptionCard
                            key={t.id}
                            selected={formData.timeline === t.id}
                            onClick={() => update('timeline', t.id)}
                          >
                            <div className="flex items-center gap-3">
                              <div className={`w-4 h-4 rounded-full border-2 flex-shrink-0 transition-all ${formData.timeline === t.id ? 'border-[var(--slf-primary)] bg-[var(--slf-primary)]' : 'border-[var(--slf-border)]'}`}>
                                {formData.timeline === t.id && (
                                  <div className="w-full h-full rounded-full bg-white scale-50" />
                                )}
                              </div>
                              <span className="slf-option-title font-semibold text-[var(--slf-text)]">{t.name}</span>
                            </div>
                          </OptionCard>
                        ))}
                      </div>
                    </div>

                    <div className="space-y-2">
                      <p className="slf-section-label font-bold text-[var(--slf-text)] mb-3" style={{ fontFamily: 'Montserrat, sans-serif' }}>
                        Estimated Budget <span className="slf-muted font-normal text-[var(--slf-muted)]">(Optional)</span>
                      </p>
                      <div className="grid grid-cols-2 gap-2">
                        {budgets.map((b) => (
                          <OptionCard
                            key={b.id}
                            selected={formData.budget === b.id}
                            onClick={() => update('budget', b.id)}
                          >
                            <span className="slf-option-title font-semibold text-[var(--slf-text)]">{b.name}</span>
                          </OptionCard>
                        ))}
                      </div>
                    </div>
                  </div>
                )}

                {/* Quote Step 3: Contact Information */}
                {step === 3 && formMode === 'quote' && (
                  <div className="space-y-4">
                    <div className="grid grid-cols-2 gap-4">
                      <FormInput
                        id="firstName"
                        label="First Name"
                        required
                        icon={User}
                        placeholder="John"
                        value={formData.firstName}
                        onChange={(e) => update('firstName', e.target.value)}
                      />
                      <FormInput
                        id="lastName"
                        label="Last Name"
                        required
                        icon={User}
                        placeholder="Doe"
                        value={formData.lastName}
                        onChange={(e) => update('lastName', e.target.value)}
                      />
                    </div>
                    <FormInput
                      id="email"
                      label="Email Address"
                      required
                      icon={Mail}
                      type="email"
                      placeholder="john.doe@example.com"
                      value={formData.email}
                      onChange={(e) => update('email', e.target.value)}
                    />
                    <FormInput
                      id="phone"
                      label="Phone Number"
                      required
                      icon={Phone}
                      type="tel"
                      placeholder="(555) 123-4567"
                      value={formData.phone}
                      onChange={(e) => update('phone', e.target.value)}
                    />
                    <div className="slf-note bg-[var(--slf-secondary)] rounded-xl p-4 border border-[var(--slf-border)]">
                      <p className="text-[var(--slf-muted)]">
                        <Shield className="w-4 h-4 inline mr-1 text-[var(--slf-primary)]" />
                        Your information is secure and will never be shared with third parties.
                      </p>
                    </div>
                  </div>
                )}

                {/* Help Step 3: Service address */}
                {step === 3 && formMode === 'help_ticket' && (
                  <div className="space-y-4">
                    <FormInput
                      id="helpAddress"
                      label="Street Address"
                      required
                      icon={MapPin}
                      placeholder="123 Main Street"
                      value={formData.address}
                      onChange={(e) => update('address', e.target.value)}
                    />
                    <div className="grid grid-cols-2 gap-4">
                      <div className="col-span-2 sm:col-span-1">
                        <FormInput
                          id="helpCity"
                          label="City"
                          required
                          placeholder="Clearwater"
                          value={formData.city}
                          onChange={(e) => update('city', e.target.value)}
                        />
                      </div>
                      <FormInput
                        id="helpState"
                        label="State"
                        required
                        placeholder="FL"
                        value={formData.state}
                        onChange={(e) => update('state', e.target.value)}
                      />
                    </div>
                    <FormInput
                      id="helpZipCode"
                      label="ZIP Code"
                      required
                      placeholder="33756"
                      value={formData.zipCode}
                      onChange={(e) => update('zipCode', e.target.value)}
                    />
                    <TurnstileField
                      siteKey={config.turnstileSiteKey}
                      onToken={setTurnstileToken}
                      onExpire={() => setTurnstileToken('')}
                    />
                  </div>
                )}

                {/* Quote Step 4: Property Location */}
                {step === 4 && formMode === 'quote' && (
                  <div className="space-y-4">
                    <FormInput
                      id="address"
                      label="Street Address"
                      required
                      icon={MapPin}
                      placeholder="123 Main Street"
                      value={formData.address}
                      onChange={(e) => update('address', e.target.value)}
                    />
                    <div className="grid grid-cols-2 gap-4">
                      <div className="col-span-2 sm:col-span-1">
                        <FormInput
                          id="city"
                          label="City"
                          required
                          placeholder="Your City"
                          value={formData.city}
                          onChange={(e) => update('city', e.target.value)}
                        />
                      </div>
                      <FormInput
                        id="state"
                        label="State"
                        required
                        placeholder="FL"
                        value={formData.state}
                        onChange={(e) => update('state', e.target.value)}
                      />
                    </div>
                    <FormInput
                      id="zipCode"
                      label="ZIP Code"
                      required
                      placeholder="34677"
                      value={formData.zipCode}
                      onChange={(e) => update('zipCode', e.target.value)}
                    />
                    <div className="slf-field space-y-1.5">
                      <label htmlFor="additionalDetails" className="slf-label block font-semibold text-[var(--slf-text)]" style={{ fontFamily: 'Montserrat, sans-serif' }}>
                        Additional Details <span className="slf-muted font-normal text-[var(--slf-muted)]">(Optional)</span>
                      </label>
                      <textarea
                        id="additionalDetails"
                        rows={3}
                        placeholder="Any specific requirements, questions, or details about your project..."
                        value={formData.additionalDetails}
                        onChange={(e) => update('additionalDetails', e.target.value)}
                        className="slf-textarea w-full rounded-lg border border-[var(--slf-border)] bg-[var(--slf-secondary)] px-4 py-3 text-[var(--slf-text)] placeholder:text-[var(--slf-muted)] focus:outline-none focus:border-[var(--slf-primary)] focus:ring-2 focus:ring-[var(--slf-primary)]/20 transition-all resize-none"
                      />
                    </div>
                    <TurnstileField
                      siteKey={config.turnstileSiteKey}
                      onToken={setTurnstileToken}
                      onExpire={() => setTurnstileToken('')}
                    />
                  </div>
                )}

                {submitError && (
                  <div className="slf-error rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-red-700">
                    {submitError}
                  </div>
                )}

                {/* Navigation */}
                <div className="slf-nav flex items-center justify-between pt-4 border-t border-[var(--slf-border)]">
                  <button
                    type="button"
                    onClick={prevStep}
                    disabled={step === 1}
                    className="slf-btn-secondary flex items-center gap-2 px-5 py-3 rounded-lg border-2 border-[var(--slf-border)] text-[var(--slf-muted)] font-bold disabled:opacity-30 hover:border-[var(--slf-primary)] hover:text-[var(--slf-primary)] transition-all"
                    style={{ fontFamily: 'Montserrat, sans-serif' }}
                  >
                    <ArrowLeft className="w-4 h-4" />
                    Back
                  </button>

                  {step < totalSteps ? (
                    <button
                      type="button"
                      onClick={nextStep}
                      disabled={!isStepValid()}
                      className="slf-btn-primary flex items-center gap-2 px-6 py-3 rounded-lg bg-[var(--slf-primary)] text-white font-bold shadow-md hover:bg-[var(--slf-accent)] disabled:opacity-40 disabled:cursor-not-allowed transition-all"
                      style={{ fontFamily: 'Montserrat, sans-serif' }}
                    >
                      Continue
                      <ArrowRight className="w-4 h-4" />
                    </button>
                  ) : (
                    <button
                      type="button"
                      onClick={handleSubmit}
                      disabled={!canSubmit}
                      className="slf-btn-primary flex items-center gap-2 px-6 py-3 rounded-lg bg-[var(--slf-primary)] text-white font-bold shadow-md hover:bg-[var(--slf-accent)] disabled:opacity-40 disabled:cursor-not-allowed transition-all"
                      style={{ fontFamily: 'Montserrat, sans-serif' }}
                    >
                      {isSubmitting
                        ? 'Submitting...'
                        : formMode === 'help_ticket'
                        ? 'Submit Help Ticket'
                        : 'Get My Free Quote'}
                      <CheckCircle2 className="w-4 h-4" />
                    </button>
                  )}
                </div>
              </div>
            </motion.div>
          </AnimatePresence>

          {/* Trust Badges */}
          <div className="slf-trust-grid mt-5 grid grid-cols-3 gap-3 text-center">
            {[
              { icon: Shield, label: '100% Secure', sub: 'SSL Encrypted' },
              { icon: Clock, label: '24hr Response', sub: 'Fast turnaround' },
              { icon: Star, label: 'Licensed & Insured', sub: config.footerText || 'Licensed & Insured' },
            ].map(({ icon: Icon, label, sub }) => (
              <div key={label} className="slf-trust-card bg-white rounded-xl p-3 border border-[var(--slf-border)] shadow-sm">
                <Icon className="w-4 h-4 text-[var(--slf-primary)] mx-auto mb-1" />
                <p className="slf-trust-title font-bold text-[var(--slf-text)]" style={{ fontFamily: 'Montserrat, sans-serif' }}>{label}</p>
                <p className="slf-trust-sub text-[var(--slf-muted)]">{sub}</p>
              </div>
            ))}
          </div>

          {/* Contact Footer */}
          <div className="slf-footer mt-3 text-center">
            <p className="text-[var(--slf-muted)]">
              Prefer to call?{' '}
              <a href={`tel:${config.phoneDisplay.replace(/[^\d+]/g, '')}`} className="text-[var(--slf-primary)] font-bold hover:underline">
                {config.phoneDisplay}
              </a>
              {' · '}Mon–Fri 9:00 AM – 5:00 PM
            </p>
          </div>
        </>
      )}
    </div>
  );
}
