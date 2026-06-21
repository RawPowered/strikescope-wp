import type { CSSProperties } from 'react';
import type { LeadFormConfig } from '../types/lead-form';

export const DEFAULT_FORM_COLORS = {
  primary: '#2563eb',
  accent: '#1d4ed8',
  background: '#ffffff',
  secondary: '#eff6ff',
  border: '#dbeafe',
  muted: '#64748b',
  text: '#1c1c1c',
} as const;

export function resolveFormColors(config: LeadFormConfig) {
  return {
    ...DEFAULT_FORM_COLORS,
    ...config.colors,
  };
}

/** CSS custom properties scoped on the form root — consumed by Tailwind arbitrary values. */
export function buildFormThemeStyle(config: LeadFormConfig): CSSProperties {
  const colors = resolveFormColors(config);
  return {
    '--slf-primary': colors.primary,
    '--slf-accent': colors.accent,
    '--slf-bg': colors.background,
    '--slf-secondary': colors.secondary,
    '--slf-border': colors.border,
    '--slf-muted': colors.muted,
    '--slf-text': colors.text,
  } as CSSProperties;
}
