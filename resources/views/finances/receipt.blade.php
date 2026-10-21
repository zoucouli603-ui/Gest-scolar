<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Reçu {{ $payment->receipt_number ?? '' }}</title>
    <style>
        body { margin: 0; padding: 32px; background: #f1f5f9; color: #0f172a; font: 15px Arial, sans-serif; }
        .receipt { box-sizing: border-box; max-width: 720px; margin: 0 auto; padding: 36px; background: white; border: 1px solid #cbd5e1; border-radius: 12px; }
        .header { padding-bottom: 20px; text-align: center; border-bottom: 2px dashed #cbd5e1; }
        .header h1 { margin: 0 0 8px; font-size: 24px; }
        .header p { margin: 0; color: #64748b; }
        .title { margin: 24px 0; text-align: center; font-size: 18px; font-weight: bold; text-transform: uppercase; }
        .line { display: flex; justify-content: space-between; gap: 20px; padding: 12px 0; border-bottom: 1px solid #e2e8f0; }
        .label { color: #64748b; }
        .amount { margin: 24px 0; padding: 18px; text-align: center; background: #eff6ff; border-radius: 8px; font-size: 24px; font-weight: bold; color: #1d4ed8; }
        .footer { display: flex; justify-content: space-between; gap: 24px; margin-top: 48px; color: #475569; }
        .signature { width: 42%; padding-top: 12px; border-top: 1px solid #94a3b8; text-align: center; }
        .actions { display: flex; justify-content: center; gap: 12px; max-width: 720px; margin: 20px auto; }
        .actions a, .actions button { padding: 10px 16px; border: 0; border-radius: 8px; text-decoration: none; font-size: 14px; cursor: pointer; }
        .print { background: #2563eb; color: white; }
        .back { background: white; color: #334155; border: 1px solid #cbd5e1 !important; }
        @media print {
            body { padding: 0; background: white; }
            .receipt { max-width: none; border: 0; border-radius: 0; }
            .actions { display: none; }
        }
    </style>
</head>
<body>
    @php($student = $payment->student)
    <main class="receipt">
        <header class="header">
            <x-doc-logo />
            <h1>{{ $student->school_name ?: ($currentSchool?->name ?? Auth::user()?->school?->name ?? config('app.name', 'YELENSCOL')) }}</h1>
            <p>Reçu officiel de paiement</p>
        </header>

        <p class="title">Reçu de paiement</p>
        <div class="line"><span class="label">N° du reçu</span><strong>{{ $payment->receipt_number ?: 'REC-' . str_pad((string) $payment->id, 6, '0', STR_PAD_LEFT) }}</strong></div>
        <div class="line"><span class="label">Date</span><strong>{{ $payment->payment_date->format('d/m/Y') }}</strong></div>
        <div class="line"><span class="label">Élève</span><strong>{{ $student->first_name }} {{ $student->last_name }}</strong></div>
        <div class="line"><span class="label">Matricule / référence élève</span><strong>{{ $student->registration_number }}</strong></div>
        <div class="line"><span class="label">Classe</span><strong>{{ $payment->classRoom->name ?? $student->classRoom->name ?? $student->class_name }}</strong></div>
        <div class="line"><span class="label">Année scolaire</span><strong>{{ $payment->schoolYear->name }}</strong></div>
        <div class="line"><span class="label">Type de frais</span><strong>{{ $payment->fee_type === 'inscription' ? 'Frais d’inscription' : 'Frais de scolarité' }}</strong></div>
        @if($payment->fee_month)
            <div class="line"><span class="label">Mois de scolarité réglé</span><strong>{{ $payment->fee_month->format('m/Y') }}</strong></div>
        @endif
        <div class="line"><span class="label">Mode de paiement</span><strong>{{ str_replace('_', ' ', ucfirst($payment->payment_method)) }}</strong></div>
        @if($payment->reference)
            <div class="line"><span class="label">Référence de transaction</span><strong>{{ $payment->reference }}</strong></div>
        @endif
        <div class="amount">{{ number_format((float) $payment->amount, 0, ',', ' ') }} {{ $currency ?? 'FCFA' }}</div>
        @if($payment->description)
            <div class="line"><span class="label">Note</span><strong>{{ $payment->description }}</strong></div>
        @endif
        <footer class="footer">
            <div>Merci pour votre paiement.</div>
            <div class="signature">Signature / cachet</div>
        </footer>
    </main>
    <div class="actions">
        <button class="print" type="button" data-print>Imprimer le reçu</button>
        <a class="back" href="{{ route('finances.index') }}">Retour aux finances</a>
    </div>
    <script @cspNonce>
        document.querySelectorAll('[data-print]').forEach((button) => {
            button.addEventListener('click', () => window.print());
        });
    </script>
</body>
</html>
