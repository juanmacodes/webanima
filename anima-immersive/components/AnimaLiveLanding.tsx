'use client';

import { FormEvent, useState } from 'react';
import { motion } from 'framer-motion';
import { submitWaitlist } from '../lib/wp';

const initialState = {
  name: '',
  email: '',
  network: '',
  country: '',
  consent: false
};

export function AnimaLiveLanding() {
  const [form, setForm] = useState(initialState);
  const [status, setStatus] = useState<'idle' | 'loading' | 'success' | 'error'>('idle');
  const [error, setError] = useState<string | null>(null);

  const handleSubmit = async (event: FormEvent<HTMLFormElement>) => {
    event.preventDefault();
    setError(null);

    if (!form.name || !form.email || !form.consent) {
      setError('Completa nombre, email y acepta privacidad.');
      return;
    }

    setStatus('loading');
    try {
      const response = await submitWaitlist({ ...form });
      if (!response.ok) {
        throw new Error('No se pudo registrar tu solicitud');
      }
      setStatus('success');
      setForm(initialState);
      if (typeof window !== 'undefined' && 'gtag' in window) {
        // @ts-expect-error gtag global
        window.gtag('event', 'waitlist_submit', { form: 'anima_live' });
      }
    } catch (err) {
      setStatus('error');
      setError(err instanceof Error ? err.message : 'Error desconocido');
    }
  };

  return (
    <div className="flex flex-col gap-16">
      <section className="mx-auto max-w-4xl text-center">
        <motion.h1
          className="text-5xl font-semibold md:text-7xl"
          initial={{ opacity: 0, y: 20 }}
          animate={{ opacity: 1, y: 0 }}
          transition={{ duration: 0.8, ease: 'easeOut' }}
        >
          Anima Live
        </motion.h1>
        <p className="mt-6 text-lg text-foreground/70">
          Plataforma para producir, distribuir y monetizar experiencias volumétricas con integraciones de IA, CRM y data en vivo.
        </p>
      </section>
      <section className="mx-auto grid w-full max-w-6xl gap-10 px-6 md:grid-cols-2">
        <div className="space-y-8">
          <div className="card">
            <h2 className="text-xl font-semibold">Cómo funciona</h2>
            <ul className="mt-4 space-y-2 text-sm text-foreground/70">
              <li>Panel unificado para streaming volumétrico, hologramas y VR.</li>
              <li>Gestión de assets desde CDN y sincronización con WordPress.</li>
              <li>Automatizaciones de IA para moderación, captions y traducción.</li>
            </ul>
          </div>
          <div className="card">
            <h2 className="text-xl font-semibold">Integraciones</h2>
            <ul className="mt-4 space-y-2 text-sm text-foreground/70">
              <li>CRM (HubSpot, Salesforce) para leads en tiempo real.</li>
              <li>Herramientas de marketing automation.</li>
              <li>SDKs para Unreal, Unity y motores WebXR.</li>
            </ul>
          </div>
        </div>
        <form onSubmit={handleSubmit} className="card space-y-4 bg-background/80" noValidate>
          <h2 className="text-xl font-semibold">Únete a la lista de espera</h2>
          <div className="grid gap-4">
            <label className="flex flex-col text-left text-sm">
              Nombre
              <input
                className="mt-1 rounded-xl border border-white/10 bg-background px-3 py-2 text-sm"
                value={form.name}
                onChange={(event) => setForm((prev) => ({ ...prev, name: event.target.value }))}
                required
              />
            </label>
            <label className="flex flex-col text-left text-sm">
              Email
              <input
                type="email"
                className="mt-1 rounded-xl border border-white/10 bg-background px-3 py-2 text-sm"
                value={form.email}
                onChange={(event) => setForm((prev) => ({ ...prev, email: event.target.value }))}
                required
              />
            </label>
            <label className="flex flex-col text-left text-sm">
              Red / Plataforma
              <input
                className="mt-1 rounded-xl border border-white/10 bg-background px-3 py-2 text-sm"
                value={form.network}
                onChange={(event) => setForm((prev) => ({ ...prev, network: event.target.value }))}
                placeholder="YouTube, Twitch, TikTok..."
              />
            </label>
            <label className="flex flex-col text-left text-sm">
              País
              <input
                className="mt-1 rounded-xl border border-white/10 bg-background px-3 py-2 text-sm"
                value={form.country}
                onChange={(event) => setForm((prev) => ({ ...prev, country: event.target.value }))}
                placeholder="México, España, Argentina..."
              />
            </label>
          </div>
          <label className="flex items-start gap-2 text-xs text-foreground/60">
            <input
              type="checkbox"
              checked={form.consent}
              onChange={(event) => setForm((prev) => ({ ...prev, consent: event.target.checked }))}
              className="mt-1"
              required
            />
            Acepto la política de privacidad y el contacto por email.
          </label>
          {error ? <p className="text-sm text-red-400">{error}</p> : null}
          <button type="submit" className="button-primary" disabled={status === 'loading'}>
            {status === 'loading' ? 'Enviando...' : status === 'success' ? '¡Estás dentro!' : 'Unirme a la waitlist'}
          </button>
          {status === 'success' ? (
            <p className="text-sm text-accent">Gracias por sumarte. Te contactaremos con acceso anticipado.</p>
          ) : null}
          {status === 'error' ? (
            <p className="text-sm text-red-400">No pudimos procesar tu solicitud. Intenta nuevamente.</p>
          ) : null}
        </form>
      </section>
      <section className="mx-auto w-full max-w-6xl px-6">
        <div className="grid gap-6 md:grid-cols-3">
          {[
            { title: 'Planes Creator', description: 'Monetiza eventos y activa tipping desde plataformas sociales.' },
            { title: 'Planes Enterprise', description: 'Múltiples escenarios, integraciones avanzadas y soporte 24/7.' },
            { title: 'SDK Partners', description: 'APIs para integrarte con motores 3D, IA y hardware volumétrico.' }
          ].map((plan) => (
            <div key={plan.title} className="card bg-background/60">
              <h3 className="text-lg font-semibold">{plan.title}</h3>
              <p className="mt-2 text-sm text-foreground/70">{plan.description}</p>
            </div>
          ))}
        </div>
      </section>
    </div>
  );
}
