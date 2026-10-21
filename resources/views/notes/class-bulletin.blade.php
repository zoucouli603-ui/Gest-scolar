<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <title>Bulletins - {{ $classRoom->name }}</title>
    <style>
        * { box-sizing: border-box; } body { margin: 0; padding: 22px; color: #1e293b; font: 12px Arial, sans-serif; background: #f1f5f9; }
        .toolbar { max-width: 1180px; margin: 0 auto 14px; display: flex; justify-content: space-between; }
        button, a { border: 0; border-radius: 6px; padding: 9px 13px; background: #2563eb; color: white; font-weight: 700; text-decoration: none; cursor: pointer; }
        a { background: #e2e8f0; color: #334155; } .page { max-width: 1180px; margin: auto; padding: 28px; background: white; }
        header { border-bottom: 2px solid #1e3a8a; padding-bottom: 12px; text-align: center; } header h1 { margin: 0 0 5px; color: #1e3a8a; text-transform: uppercase; }
        h2 { text-align: center; margin: 18px 0 5px; text-transform: uppercase; } .meta { text-align: center; color: #475569; }
        .student { break-inside: avoid; margin-top: 24px; } .student h3 { margin: 0; padding: 9px; background: #eff6ff; border: 1px solid #cbd5e1; }
        table { width: 100%; border-collapse: collapse; } th, td { padding: 6px; border: 1px solid #cbd5e1; } th { background: #f8fafc; text-align: left; } .num { text-align: center; }
        .annual-row { font-weight: 700; background: #eff6ff; } .signatures { display: grid; grid-template-columns: repeat(3, 1fr); gap: 20px; margin-top: 30px; text-align: center; } .signature { border-top: 1px solid #94a3b8; padding-top: 8px; min-height: 46px; }
        .summary { display: flex; justify-content: space-between; gap: 12px; padding: 8px; border: 1px solid #93c5fd; background: #eff6ff; font-weight: 700; }
        .second-cycle { font-size: 12px; }
        .high-school .student-header strong { font-size: 20px; }
        .second-cycle .page { max-width: 1120px; padding: 26px; }
        .second-cycle > .page > header, .second-cycle > .page > h2, .second-cycle > .page > .meta { display: none; }
        .second-cycle .student h3 { display: none; }
        .second-cycle .student { margin: 0 0 24px; padding-bottom: 18px; border-bottom: 2px solid #cbd5e1; }
        .second-cycle .student-header { display: flex; align-items: center; justify-content: center; gap: 16px; min-height: 68px; margin-bottom: 8px; text-align: center; }
        .second-cycle .student-header strong { color: #1e3a8a; font-size: 18px; text-transform: uppercase; }
        .second-cycle .identity { display: grid; grid-template-columns: repeat(3, 1fr); gap: 7px 18px; padding: 10px 12px; border: 1px solid #94a3b8; margin-bottom: 10px; font-size: 12px; }
        .second-cycle th, .second-cycle td { padding: 6px 7px; }
        .second-cycle .bulletin-table { table-layout: fixed; }
        .second-cycle .bulletin-table th { text-align: center; vertical-align: middle; }
        .second-cycle .bulletin-table th:first-child, .second-cycle .bulletin-table td:first-child { width: 21%; text-align: left; }
        .second-cycle .bulletin-table th:nth-child(2) { width: 8%; }
        .second-cycle .bulletin-table th:nth-child(3), .second-cycle .bulletin-table th:nth-child(4) { width: 13%; }
        .second-cycle .bulletin-table th:nth-child(5) { width: 14%; }
        .second-cycle .bulletin-table th:nth-child(6) { width: 12%; }
        .second-cycle .bulletin-table th:nth-child(7) { width: 19%; text-align: left; }
        .second-cycle .bulletin-table tbody td { height: 28px; }
        .high-school .bulletin-table th:nth-child(7) { width: 8%; }
        .high-school .bulletin-table th:nth-child(8) { width: 15%; text-align: left; }
        .second-cycle .summary-table { margin-top: 9px; table-layout: fixed; }
        .second-cycle .summary-table th { padding: 7px 5px; text-align: center; vertical-align: middle; font-size: 11px; }
        .second-cycle .summary-table td { height: 34px; text-align: center; font-weight: 700; font-size: 12px; }
        .second-cycle .signature-grid { display: grid; grid-template-columns: repeat(3, 1fr); margin-top: 12px; border: 1px solid #94a3b8; }
        .high-school .signature-grid { grid-template-columns: repeat(2, 1fr); }
        .second-cycle .signature-cell { height: 74px; display: flex; flex-direction: column; justify-content: space-between; border-right: 1px solid #94a3b8; padding: 8px; }
        .second-cycle .signature-cell:last-child { border-right: 0; }
        .second-cycle .signature-label { text-align: center; font-weight: 700; }
        .second-cycle .signature-space { flex: 1; margin: 6px 8px 0; border-bottom: 1px solid #94a3b8; }
        .primary-cycle { font-size: 11px; }
        .primary-cycle > .page > header, .primary-cycle > .page > h2, .primary-cycle > .page > .meta { display: none; }
        .primary-cycle .student h3 { display: none; }
        .primary-cycle .student { margin: 0 0 20px; padding-bottom: 14px; border-bottom: 2px solid #cbd5e1; }
        .primary-cycle .primary-student-header { display: flex; align-items: center; justify-content: center; gap: 14px; margin-bottom: 7px; text-align: center; }
        .primary-cycle .primary-student-header strong { color: #1e3a8a; font-size: 16px; text-transform: uppercase; }
        .primary-cycle .primary-identity { display: grid; grid-template-columns: repeat(3, 1fr); gap: 5px 12px; border: 1px solid #64748b; padding: 7px 9px; margin-bottom: 7px; }
        .primary-cycle .primary-bulletin { width: 100%; table-layout: fixed; font-size: 9px; }
        .primary-cycle .primary-bulletin th, .primary-cycle .primary-bulletin td { border: 1px solid #64748b; padding: 2px; text-align: center; }
        .primary-cycle .primary-bulletin .vertical-heading { height: 100px; padding: 3px 2px; vertical-align: bottom; white-space: nowrap; }
        .primary-cycle .primary-bulletin .vertical-heading span { display: inline-block; writing-mode: vertical-rl; transform: rotate(180deg); }
        .primary-cycle .primary-bulletin .month-heading, .primary-cycle .primary-bulletin .month-cell { width: 32px; }
        .primary-cycle .primary-bulletin .subject-heading, .primary-cycle .primary-bulletin .subject-cell { width: 30px; }
        .primary-cycle .primary-bulletin .signature-column { width: 34px; }
        .primary-cycle .primary-bulletin .signature-mark { height: 24px; }
        .primary-cycle .primary-bulletin .annual-row { background: #eff6ff; font-weight: 700; }
        .primary-cycle .primary-summary { display: flex; justify-content: space-between; gap: 8px; margin-top: 7px; border: 1px solid #93c5fd; background: #eff6ff; padding: 6px 8px; font-weight: 700; }
        @if($isSecondCycle || $isPrimary || $isHighSchool)
            @page { size: A4 landscape; margin: 10mm; }
        @endif
        @media print {
            body { padding: 0; background: white; }
            .second-cycle { font-size: 11pt; }
            .second-cycle .page { max-width: none; padding: 0; }
            .second-cycle .student { break-inside: avoid; break-after: page; margin: 0; padding: 0; border: 0; }
            .second-cycle .student:last-child { break-after: auto; }
            .second-cycle .student-header { min-height: 58px; }
            .second-cycle .identity { font-size: 10pt; }
            .second-cycle th, .second-cycle td { padding: 5px 6px; }
            .second-cycle .bulletin-table tbody td { height: 24px; }
            .second-cycle .signature-cell { height: 64px; }
            .primary-cycle { font-size: 10pt; }
            .primary-cycle .page { max-width: none; padding: 0; }
            .primary-cycle .student { break-inside: avoid; break-after: page; margin: 0; padding: 0; border: 0; }
            .primary-cycle .student:last-child { break-after: auto; }
            .primary-cycle .primary-identity { font-size: 9pt; }
            .primary-cycle .primary-bulletin { font-size: 7.5pt; }
            .primary-cycle .primary-bulletin .vertical-heading { height: 92px; }
            .primary-cycle .primary-bulletin th, .primary-cycle .primary-bulletin td { padding: 2px; }
            .no-print { display: none; }
        }
    </style>
</head>
<body @class(['second-cycle' => $isSecondCycle || $isHighSchool, 'high-school' => $isHighSchool, 'primary-cycle' => $isPrimary])>
    <div class="toolbar no-print"><a href="{{ route('notes.index') }}">Retour</a><button type="button" data-print>Imprimer tous les bulletins</button></div>
    <main class="page">
        <header><x-doc-logo /><h1>{{ $schoolName }}</h1><div>Année scolaire : {{ $currentYear->name }}</div></header>
        <h2>Bulletins de la classe {{ $classRoom->name }}</h2>
        <p class="meta">{{ $classRoom->cycle }} · {{ $isPrimary ? 'Année scolaire '.$currentYear->name : $trimesterLabel }} · Moyenne classe : {{ $classAverage !== null ? number_format($classAverage, 2, ',', ' ').' / '.$gradeScale : '—' }} · Moyenne du 1er : {{ $firstAverage !== null ? number_format($firstAverage, 2, ',', ' ').' / '.$gradeScale : '—' }}</p>
        @foreach($bulletins as $bulletin)
            <section class="student">
                <h3>{{ $bulletin['student']->last_name }} {{ $bulletin['student']->first_name }}</h3>
                @if($isPrimary)
                    @php
                        $primary = $bulletin['primaryReport'];
                    @endphp
                    <div class="primary-student-header">
                        <x-doc-logo style="height:48px;max-width:130px;margin:0" />
                        <div><strong>{{ $schoolName }}</strong><br>Bulletin de notes · {{ $classRoom->name }} · {{ $currentYear->name }}</div>
                    </div>
                    <div class="primary-identity">
                        <div><strong>Élève :</strong> {{ $bulletin['student']->last_name }} {{ $bulletin['student']->first_name }}</div>
                        <div><strong>Matricule :</strong> {{ $bulletin['student']->registration_number ?: '—' }}</div>
                        <div><strong>Classe :</strong> {{ $classRoom->name }}</div>
                        <div><strong>Date de naissance :</strong> {{ $bulletin['student']->birth_date ? \Carbon\Carbon::parse($bulletin['student']->birth_date)->format('d/m/Y') : '—' }} {{ $bulletin['student']->birth_place ? 'à '.$bulletin['student']->birth_place : '' }}</div>
                        <div><strong>Sexe :</strong> {{ $bulletin['student']->genderLabel() }}</div>
                        <div><strong>Année scolaire :</strong> {{ $currentYear->name }}</div>
                    </div>
                    <table class="primary-bulletin">
                        <thead>
                            <tr>
                                <th rowspan="2" class="vertical-heading month-heading"><span>Mois</span></th>
                                <th colspan="{{ $primaryAssignments->count() }}">MATIÈRES</th>
                                <th rowspan="2" class="vertical-heading"><span>Total</span></th>
                                <th rowspan="2" class="vertical-heading"><span>Moyenne</span></th>
                                <th rowspan="2" class="vertical-heading"><span>Rang</span></th>
                                <th rowspan="2" class="vertical-heading"><span>Moyenne du 1er</span></th>
                                <th rowspan="2" class="vertical-heading signature-column"><span>Signature du maître</span></th>
                                <th rowspan="2" class="vertical-heading signature-column"><span>Signature du Directeur</span></th>
                                <th rowspan="2" class="vertical-heading signature-column"><span>Visa des parents</span></th>
                                <th rowspan="2" class="vertical-heading"><span>Appréciation</span></th>
                            </tr>
                            <tr>
                                @foreach($primaryAssignments as $assignment)
                                    <th class="vertical-heading subject-heading"><span>{{ $assignment->subject->name }}</span></th>
                                @endforeach
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($primary['months'] as $monthRow)
                                <tr>
                                    <th class="month-cell">{{ $monthRow['label'] }}</th>
                                    @foreach($primaryAssignments as $assignment)
                                        <td class="subject-cell">{{ $monthRow['scores'][$assignment->subject_id] !== null ? number_format($monthRow['scores'][$assignment->subject_id], 2, ',', ' ') : '—' }}</td>
                                    @endforeach
                                    <td class="num">{{ $monthRow['points'] !== null ? number_format($monthRow['points'], 2, ',', ' ') : '—' }}</td>
                                    <td class="num">{{ $monthRow['average'] !== null ? number_format($monthRow['average'], 2, ',', ' ') : '—' }}</td>
                                    <td class="num">{{ $monthRow['rank'] ? $monthRow['rank'].'e' : '—' }}</td>
                                    <td class="num">{{ $monthRow['first_average'] !== null ? number_format($monthRow['first_average'], 2, ',', ' ') : '—' }}</td>
                                    <td class="signature-mark"></td><td class="signature-mark"></td><td class="signature-mark"></td>
                                    <td>{{ $monthRow['appreciation'] ?? '—' }}</td>
                                </tr>
                            @endforeach
                            <tr class="annual-row">
                                <th class="month-cell">Total</th>
                                @foreach($primaryAssignments as $assignment)
                                    <td class="num">{{ ($primary['annual_subjects'][$assignment->subject_id] ?? null) !== null ? number_format($primary['annual_subjects'][$assignment->subject_id], 2, ',', ' ') : '—' }}</td>
                                @endforeach
                                <td class="num">{{ $primary['annual_points'] !== null ? number_format($primary['annual_points'], 2, ',', ' ') : '—' }}</td>
                                <td class="num">{{ $primary['annual_average'] !== null ? number_format($primary['annual_average'], 2, ',', ' ') : '—' }}</td>
                                <td class="num">{{ $primary['annual_rank'] ? $primary['annual_rank'].'e' : '—' }}</td>
                                <td class="num">{{ $firstAverage !== null ? number_format($firstAverage, 2, ',', ' ') : '—' }}</td>
                                <td></td><td></td><td></td>
                                <td>{{ $primary['annual_appreciation'] ?? '—' }}</td>
                            </tr>
                        </tbody>
                    </table>
                    <div class="primary-summary"><span>Moyenne annuelle : {{ $primary['annual_average'] ?? '—' }} / 10</span><span>Rang : {{ $primary['annual_rank'] ? $primary['annual_rank'].'e' : '—' }}</span><span>Moyenne du premier : {{ $firstAverage !== null ? number_format($firstAverage, 2, ',', ' ') : '—' }} / 10</span><span>Appréciation : {{ $primary['annual_appreciation'] ?? '—' }}</span></div>
                @elseif($isSecondCycle || $isHighSchool)
                    @php
                        $totalPoints = $bulletin['rows']->sum(fn ($row) => $row['average'] !== null ? $row['average'] * $row['coefficient'] : 0);
                        $totalCoefficients = $bulletin['rows']->sum(fn ($row) => $row['average'] !== null ? $row['coefficient'] : 0);
                    @endphp
                    <div class="student-header">
                        <x-doc-logo style="height:54px;max-width:150px;margin:0" />
                        <div><strong>{{ $schoolName }}</strong><br>Bulletin de notes · {{ $classRoom->name }} · {{ $trimesterLabel }} · {{ $currentYear->name }}</div>
                    </div>
                    <div class="identity">
                        <div><strong>Élève :</strong> {{ $bulletin['student']->last_name }} {{ $bulletin['student']->first_name }}</div>
                        <div><strong>Matricule :</strong> {{ $bulletin['student']->registration_number ?: '—' }}</div>
                        <div><strong>Classe :</strong> {{ $classRoom->name }}</div>
                        <div><strong>Date et lieu de naissance :</strong> {{ $bulletin['student']->birth_date ? \Carbon\Carbon::parse($bulletin['student']->birth_date)->format('d/m/Y') : '—' }} {{ $bulletin['student']->birth_place ? 'à '.$bulletin['student']->birth_place : '' }}</div>
                        <div><strong>Sexe :</strong> {{ $bulletin['student']->genderLabel() }}</div>
                        <div><strong>Parent / tuteur :</strong> {{ $bulletin['student']->parent_name ?: '—' }}{{ $bulletin['student']->parent_phone ? ' · '.$bulletin['student']->parent_phone : '' }}</div>
                        <div><strong>Année :</strong> {{ $currentYear->name }}</div>
                        <div><strong>Période :</strong> {{ $trimesterLabel }}</div>
                        <div><strong>Date d'édition :</strong> {{ now()->format('d/m/Y') }}</div>
                        <div><strong>Effectif :</strong> {{ $classStudentCount }}</div>
                    </div>
                    <table class="bulletin-table">
                        <thead><tr><th>Matières</th><th class="num">Coefficient</th><th class="num">{{ $isHighSchool ? 'Note de Classe' : 'Moy. de classe /'.$gradeScale }}</th><th class="num">{{ $isHighSchool ? 'Note de Comp /20' : 'Composition /'.$gradeScale }}</th><th class="num">{{ $isHighSchool ? 'Moy Général' : 'Moyenne /'.$gradeScale }}</th><th class="num">{{ $isHighSchool ? 'Moy. coef.' : 'Points coef.' }}</th>@if($isHighSchool)<th class="num">Rang</th>@endif<th>{{ $isHighSchool ? 'Appréciations' : 'Appréciation' }}</th></tr></thead>
                        <tbody>
                            @forelse($bulletin['rows'] as $row)
                                <tr>
                                    <td>{{ $row['subject']->name }}</td>
                                    <td class="num">{{ number_format($row['coefficient'], 2, ',', ' ') }}</td>
                                    <td class="num">{{ $row['continuous'] !== null ? number_format($row['continuous'], 2, ',', ' ') : '—' }}</td>
                                    <td class="num">{{ $row['exam'] !== null ? number_format($row['exam'], 2, ',', ' ') : '—' }}</td>
                                    <td class="num">{{ $row['average'] !== null ? number_format($row['average'], 2, ',', ' ') : '—' }}</td>
                                    <td class="num">{{ $row['average'] !== null ? number_format($row['average'] * $row['coefficient'], 2, ',', ' ') : '—' }}</td>
                                    @if($isHighSchool)<td class="num">{{ $row['rank'] ? $row['rank'].'e' : '—' }}</td>@endif
                                    <td>{{ $row['appreciation'] ?? '—' }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="{{ $isHighSchool ? 8 : 7 }}" class="num">Aucune matière n’est affectée à cette classe.</td></tr>
                            @endforelse
                            <tr class="annual-row"><th>Total</th><td class="num">{{ number_format($totalCoefficients, 2, ',', ' ') }}</td><td colspan="3"></td><td class="num">{{ number_format($totalPoints, 2, ',', ' ') }}</td>@if($isHighSchool)<td></td>@endif<td></td></tr>
                        </tbody>
                    </table>
                    <table class="summary-table">
                        <thead><tr>
                            <th>{{ $isHighSchool ? 'Moyenne de l’élève' : 'Moyenne générale' }}</th>
                            <th>Rang de l'élève</th>
                            @if($isHighSchool)<th>Effectif de la classe</th>@endif
                            <th>Moyenne du premier</th>
                            <th>Moyenne de classe</th>
                            <th>Total points / coefficients</th>
                            @unless($isHighSchool)<th>Effectif de la classe</th>@endunless
                            <th>Appréciation</th>
                        </tr></thead>
                        <tbody><tr>
                            <td>{{ $bulletin['average'] !== null ? number_format($bulletin['average'], 2, ',', ' ').' / '.$gradeScale : '—' }}</td>
                            <td>{{ $bulletin['rank'] ? $bulletin['rank'].'e' : '—' }}</td>
                            @if($isHighSchool)<td>{{ $classStudentCount }}</td>@endif
                            <td>{{ $bulletin['first_average'] !== null ? number_format($bulletin['first_average'], 2, ',', ' ').' / '.$gradeScale : '—' }}</td>
                            <td>{{ $classAverage !== null ? number_format($classAverage, 2, ',', ' ').' / '.$gradeScale : '—' }}</td>
                            <td>{{ $totalPoints !== 0.0 ? number_format($totalPoints, 2, ',', ' ').' / '.$totalCoefficients : '—' }}</td>
                            @unless($isHighSchool)<td>{{ $classStudentCount }}</td>@endunless
                            <td>{{ $bulletin['appreciation'] ?? '—' }}</td>
                        </tr></tbody>
                    </table>
                    <div class="signature-grid">
                        @unless($isHighSchool)<div class="signature-cell"><span class="signature-label">Signature du maître</span><span class="signature-space"></span></div>@endunless
                        <div class="signature-cell"><span class="signature-label">Signature du Directeur</span><span class="signature-space"></span></div>
                        <div class="signature-cell"><span class="signature-label">Visa des parents</span><span class="signature-space"></span></div>
                    </div>
                @else
                    <table>
                        <thead><tr><th>Matière</th><th class="num">Note de classe</th><th class="num">Note du trimestre</th><th class="num">Moyenne</th><th class="num">Coefficient</th></tr></thead>
                        <tbody>
                            @foreach($bulletin['rows'] as $row)
                                <tr><td>{{ $row['subject']->name }}</td><td class="num">{{ $row['continuous'] ?? '—' }}</td><td class="num">{{ $row['exam'] ?? '—' }}</td><td class="num">{{ $row['average'] ?? '—' }}</td><td class="num">{{ $row['coefficient'] }}</td></tr>
                            @endforeach
                        </tbody>
                    </table>
                    <div class="summary"><span>Moyenne : {{ $bulletin['average'] ?? '—' }} / {{ $gradeScale }}</span><span>Rang : {{ $bulletin['rank'] ? $bulletin['rank'].'e' : '—' }}</span><span>Appréciation : {{ $bulletin['appreciation'] ?? '—' }}</span></div>
                @endif
            </section>
        @endforeach
    </main>
    <script @cspNonce>
        document.querySelectorAll('[data-print]').forEach((button) => {
            button.addEventListener('click', () => window.print());
        });
    </script>
</body>
</html>
