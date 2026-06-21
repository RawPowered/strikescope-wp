import { createRoot } from 'react-dom/client';
import { LeadForm } from './app/components/LeadForm';
import './styles/index.css';

function mountLeadForm() {
  const rootEl = document.getElementById('strikescope-form-root');
  const config = window.strikeScopeForm;
  if (!rootEl || !config) {
    return;
  }

  createRoot(rootEl).render(
    <div className="strikescope-form-embed w-full">
      <LeadForm config={config} embed />
    </div>,
  );
}

if (document.readyState === 'loading') {
  document.addEventListener('DOMContentLoaded', mountLeadForm);
} else {
  mountLeadForm();
}

window.addEventListener('strikescope-form-ready', mountLeadForm);