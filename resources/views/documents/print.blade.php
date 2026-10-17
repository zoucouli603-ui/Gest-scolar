<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $documentTitle }} - {{ $student->first_name }} {{ $student->last_name }}</title>
    <style>
        * { box-sizing: border-box; }
        body { margin: 0; padding: 24px; color: #1e293b; background: #f1f5f9; font: 14px Arial, sans-serif; }
        .page { max-width: 900px; min-height: 1050px; margin: 0 auto; padding: 42px; background: white; border: 1px solid #cbd5e1; }
        .toolbar { max-width: 900px; margin: 0 auto 16px; }
        .toolbar-line { display: flex; justify-content: space-between; align-items: center; gap: 12px; margin-bottom: 12px; }
        .toolbar a, .toolbar button { display: inline-block; border: 0; border-radius: 7px; padding: 10px 14px; color: white; background: #2563eb; font-size: 13px; font-weight: 700; text-decoration: none; cursor: pointer; }
        .toolbar a { color: #334155; background: #e2e8f0; }
        .transfer-form { display: grid; grid-template-columns: 1fr 1fr; gap: 10px; padding: 14px; border: 1px solid #cbd5e1; border-radius: 9px; background: white; }
        .transfer-form label { display: block; margin-bottom: 5px; color: #475569; font-size: 12px; font-weight: 700; }
        .transfer-form input { width: 100%; padding: 9px; border: 1px solid #cbd5e1; border-radius: 6px; font-size: 13px; }
        .transfer-form .wide { grid-column: 1 / -1; }
        .school-header { padding-bottom: 18px; border-bottom: 2px solid #1e3a8a; text-align: center; }
        .school-header h1 { margin: 0 0 6px; color: #1e3a8a; font-size: 22px; text-transform: uppercase; }
        .school-header p { margin: 4px 0; color: #475569; }
        .document-title { margin: 34px 0; text-align: center; font-size: 19px; text-decoration: underline; text-transform: uppercase; }
        .identity { display: grid; grid-template-columns: 1fr 1fr; gap: 12px 24px; margin: 20px 0; padding: 16px; border: 1px solid #cbd5e1; border-radius: 7px; }
        .identity strong, .label { color: #334155; }
        .body-copy { margin: 28px 0; line-height: 1.9; text-align: justify; }
        .field { display: inline-block; min-width: 170px; border-bottom: 1px dotted #64748b; }
        .signatures { display: grid; grid-template-columns: 1fr 1fr; gap: 60px; margin-top: 72px; text-align: center; }
        .signature { min-height: 90px; padding-top: 10px; border-top: 1px solid #94a3b8; }
        table { width: 100%; margin-top: 18px; border-collapse: collapse; font-size: 12px; }
        th, td { padding: 8px; border: 1px solid #cbd5e1; text-align: left; }
        th { background: #eff6ff; }
        .muted { color: #64748b; }
        @media print {
            @page { size: A4; margin: 16mm; }
            body { padding: 0; background: white; }
            .page { max-width: none; min-height: 0; padding: 0; border: 0; }
            .no-print { display: none !important; }
            .identity, table, .school-header { break-inside: avoid; print-color-adjust: exact; -webkit-print-color-adjust: exact; }
        }
        @media (max-width: 640px) {
            body { padding: 10px; }
            .page { min-height: 0; padding: 22px; }
            .identity { grid-template-columns: 1fr; }
            .transfer-form { grid-template-columns: 1fr; }
            .transfer-form .wide { grid-column: auto; }
        }
    </style>
</head>
<body>
    <div class="toolbar no-print">
        <div class="toolbar-line">
            <a href="{{ route('documents.index') }}">Retour aux documents</a>
            @if($documentType !== 'transfert' || !empty($transferDetails['destination']))
                <button type="button" onclick="window.print()">Imprimer le document</button>
            @endif
        </div>
        @if($documentType === 'transfert')
            <form method="GET" action="{{ route('documents.print', [$student, $documentType]) }}" class="transfer-form">
                <div>
                    <label for="destination">Établissement de destination</label>
                    <input id="destination" name="destination" value="{{ $transferDetails['destination'] ?? '' }}" required maxlength="255" placeholder="Nom de l’école d’accueil">
                </div>
                <div>
                    <label for="transfer_date">Date du transfert</label>
                    <input id="transfer_date" type="date" name="transfer_date" value="{{ $transferDetails['transfer_date'] ?? now()->toDateString() }}" required>
                </div>
                <div class="wide">
                    <label for="reason">Motif (facultatif)</label>
                    <input id="reason" name="reason" value="{{ $transferDetails['reason'] ?? '' }}" maxlength="500" placeholder="Motif du transfert">
                </div>
                <div class="wide"><button type="submit">Préparer le certificat</button></div>
            </form>
            @if(empty($transferDetails['destination']))
                <p class="muted">Renseignez la destination puis préparez le certificat avant de l’imprimer.</p>
            @endif
        @endif
    </div>

    @if($documentType !== 'transfert' || !empty($transferDetails['destination']))
        <main class="page">
            <header class="school-header">
                <x-doc-logo />
                <h1>{{ $schoolName }}</h1>
                <p>Établissement scolaire</p>
                @if($schoolYear)
                    <p>Année scolaire : {{ $schoolYear->name }}</p>
                @endif
            </header>

            <h2 class="document-title">{{ $documentTitle }}</h2>

            <section class="identity">
                <div><strong>Nom et prénom :</strong> {{ $student->last_name }} {{ $student->first_name }}</div>
                @unless(in_array($documentType, ['scolarite', 'transfert', 'inscription'], true))
                    <div><strong>Matricule :</strong> {{ $student->registration_number }}</div>
                @endunless
                <div><strong>Classe :</strong> {{ $student->classRoom?->name ?? $student->class_name }}</div>
                <div><strong>Sexe :</strong> {{ $student->genderLabel() }}</div>
                <div><strong>Date de naissance :</strong> {{ $student->birth_date ? \Carbon\Carbon::parse($student->birth_date)->format('d/m/Y') : '—' }}</div>
                <div><strong>Lieu de naissance :</strong> {{ $student->birth_place ?: '—' }}</div>
                <div><strong>Parent / tuteur :</strong> {{ $student->parent_name ?: '—' }}</div>
                <div><strong>Téléphone du parent :</strong> {{ $student->parent_phone ?: '—' }}</div>
            </section>

            @if($documentType === 'inscription')
                <p class="body-copy">
                    La présente fiche confirme l’inscription de <strong>{{ $student->first_name }} {{ $student->last_name }}</strong>
                    dans notre établissement, en classe de <strong>{{ $student->classRoom?->name ?? $student->class_name }}</strong>,
                    au titre de l’année scolaire <strong>{{ $schoolYear?->name ?? '—' }}</strong>.
                </p>
                <section class="identity">
                    <div><strong>Date d’inscription :</strong> {{ $student->inscription_date ? \Carbon\Carbon::parse($student->inscription_date)->format('d/m/Y') : '—' }}</div>
                    <div><strong>Nationalité :</strong> {{ $student->nationality ?: '—' }}</div>
                    <div><strong>École précédente :</strong> {{ $student->school_name ?: '—' }}</div>
                    <div><strong>Statut :</strong> {{ ucfirst($student->status) }}</div>
                </section>
            @elseif($documentType === 'scolarite')
                <p class="body-copy">
                    Je soussigné(e), <strong>{{ $directorName ? $directorName.', Directeur' : 'Directeur' }}</strong> de l’établissement <strong>{{ $schoolName }}</strong>, certifie que
                    <strong>{{ $student->first_name }} {{ $student->last_name }}</strong>{{ $documentType === 'scolaire' ? ', matricule '.$student->registration_number : '' }}, est régulièrement inscrit(e) et suit sa scolarité
                    en classe de <strong>{{ $student->classRoom?->name ?? $student->class_name }}</strong>
                    durant l’année scolaire <strong>{{ $schoolYear?->name ?? '—' }}</strong>.
                </p>
                <p class="body-copy">Le présent certificat est délivré à l’intéressé(e) pour servir et valoir ce que de droit.</p>
            @elseif($documentType === 'transfert')
                <p class="body-copy">
                    Je soussigné(e), <strong>{{ $directorName ? $directorName.', Directeur' : 'Directeur' }}</strong> de l’établissement <strong>{{ $schoolName }}</strong>, certifie que
                    <strong>{{ $student->first_name }} {{ $student->last_name }}</strong>{{ $documentType === 'scolaire' ? ', matricule '.$student->registration_number : '' }}, inscrit(e) en classe de
                    <strong>{{ $student->classRoom?->name ?? $student->class_name }}</strong> au titre de l’année scolaire
                    <strong>{{ $schoolYear?->name ?? '—' }}</strong>, est autorisé(e) à poursuivre sa scolarité à
                    <strong>{{ $transferDetails['destination'] }}</strong>.
                    @if(!empty($transferDetails['reason']))
                        Motif : {{ $transferDetails['reason'] }}.
                    @endif
                    Le transfert prend effet le
                    <strong>{{ !empty($transferDetails['transfer_date']) ? \Carbon\Carbon::parse($transferDetails['transfer_date'])->format('d/m/Y') : now()->format('d/m/Y') }}</strong>.
                </p>
                <p class="body-copy">Le présent certificat est délivré à la demande de l’intéressé(e) pour servir et valoir ce que de droit.</p>
            @elseif($documentType === 'scolaire')
                <p class="body-copy">
                    Historique du parcours de <strong>{{ $student->first_name }} {{ $student->last_name }}</strong> :
                    classes fréquentées, années scolaires et moyennes annuelles.
                </p>
                <table>
                    <thead>
                        <tr><th>Année scolaire</th><th>Classe</th><th>Moyenne annuelle</th><th>Parcours</th></tr>
                    </thead>
                    <tbody>
                        @forelse($schoolHistory as $row)
                            <tr>
                                <td>{{ $row['school_year'] }}</td>
                                <td>{{ $row['class'] }}</td>
                                <td>{{ $row['average'] !== null ? number_format($row['average'], 2, ',', ' ').($row['grade_scale'] ? ' / '.$row['grade_scale'] : '') : '—' }}</td>
                                <td>{{ $row['repeated'] ? 'Redoublée' : 'Passage' }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="4" style="text-align:center">Aucun parcours scolaire enregistré pour cet élève.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            @endif

            <div class="signatures">
                <div class="signature">Le parent / tuteur<br>{{ $student->parent_name ?: '' }}</div>
                <div class="signature">Fait le {{ now()->format('d/m/Y') }}<br>La direction / cachet</div>
            </div>
        </main>
    @endif
</body>
</html>
