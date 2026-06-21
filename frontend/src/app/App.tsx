import { LeadForm } from './components/LeadForm';

export default function App() {
  return (
    <div className="min-h-screen py-8 md:py-12" style={{
      background: 'linear-gradient(160deg, #fff8ee 0%, #fffcf7 40%, #fff5e6 100%)'
    }}>
      <LeadForm />
    </div>
  );
}
