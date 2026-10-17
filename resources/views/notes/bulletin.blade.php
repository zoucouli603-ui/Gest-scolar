<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Bulletin - {{ $student->last_name }} {{ $student->first_name }}</title>
    <style>
        * { box-sizing: border-box; }
        body { margin: 0; padding: 24px; color: #1e293b; font-family: Arial, sans-serif; font-size: 13px; background: #f1f5f9; }
        .page { max-width: 1120px; margin: 0 auto; padding: 26px; background: white; }
        .toolbar { display: flex; justify-content: space-between; margin-bottom: 18px; }
        button, .back { border: 0; border-radius: 7px; padding: 10px 14px; color: #fff; background: #2563eb; font-size: 13px; font-weight: 700; text-decoration: none; cursor: pointer; }
        .back { color: #334155; background: #f1f5f9; }
        .school-header { padding: 18px; border: 1px solid #cbd5e1; border-radius: 10px; text-align: center; }
        .school-header h1 { margin: 0 0 6px; color: #1d4ed8; font-size: 22px; text-transform: uppercase; }
        .school-header p { margin: 3px 0; color: #475569; }
        .report-title { margin: 16px 0; text-align: center; font-size: 18px; font-weight: 700; text-transform: uppercase; }
        .identity { display: grid; grid-template-columns: 1fr 1fr; gap: 8px 24px; margin-bottom: 16px; padding: 14px; border: 1px solid #cbd5e1; border-radius: 8px; }
        .identity strong { color: #334155; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #cbd5e1; padding: 9px 8px; }
        th { background: #eff6ff; text-align: left; }
        td.number, th.number { text-align: center; }
        .average-row { margin-top: 14px; padding: 12px; border: 1px solid #93c5fd; border-radius: 8px; background: #eff6ff; text-align: right; font-size: 16px; font-weight: 700; }
        .method { margin-top: 8px; color: #64748b; font-size: 11px; }
        .signatures { display: grid; grid-template-columns: 1fr 1fr; gap: 40px; margin-top: 42px; text-align: center; }
        .signature { min-height: 70px; border-top: 1px solid #94a3b8; padding-top: 8px; }
        .second-cycle { font-size: 12px; }
        .second-cycle .school-header { padding: 10px; border-radius: 0; }
        .second-cycle .school-header h1 { font-size: 20px; }
        .second-cycle .report-title { margin: 10px 0; font-size: 17px; }
        .second-cycle .identity { grid-template-columns: repeat(3, 1fr); gap: 7px 18px; margin-bottom: 10px; padding: 10px 12px; border-radius: 0; }
        .second-cycle th, .second-cycle td { padding: 6px 7px; }
        .second-cycle .second-cycle-table { table-layout: fixed; }
        .second-cycle .second-cycle-table th { text-align: center; }
        .second-cycle .second-cycle-table th:first-child, .second-cycle .second-cycle-table td:first-child { width: 21%; text-align: left; }
        .second-cycle .second-cycle-table th:nth-child(2) { width: 8%; }
        .second-cycle .second-cycle-table th:nth-child(3), .second-cycle .second-cycle-table th:nth-child(4) { width: 13%; }
        .second-cycle .second-cycle-table th:nth-child(5) { width: 14%; }
        .second-cycle .second-cycle-table th:nth-child(6) { width: 12%; }
        .second-cycle .second-cycle-table th:nth-child(7) { width: 19%; text-align: left; }
        .second-cycle .second-cycle-table tbody td { height: 30px; }
        .second-cycle .summary-table { margin-top: 7px; table-layout: fixed; }
        .second-cycle .summary-table th { padding: 7px 5px; text-align: center; vertical-align: middle; font-size: 11px; }
        .second-cycle .summary-table td { height: 34px; text-align: center; font-weight: 700; font-size: 12px; }
        .second-cycle .signature-grid { display: grid; grid-template-columns: repeat(3, 1fr); margin-top: 12px; border: 1px solid #94a3b8; }
        .second-cycle .signature-cell { height: 74px; display: flex; flex-direction: column; justify-content: space-between; border-right: 1px solid #94a3b8; padding: 8px; }
        .second-cycle .signature-cell:last-child { border-right: 0; }
        .second-cycle .signature-label { text-align: center; font-weight: 700; }
        .second-cycle .signature-space { flex: 1; margin: 6px 8px 0; border-bottom: 1px solid #94a3b8; }
        .second-cycle .method { margin: 5px 0; }
        .second-cycle .average-row { display: none; }
        .second-cycle .signatures { display: none; }
        .high-school .report-title { margin: 8px 0; }
        .high-school .second-cycle-table th:nth-child(7) { width: 8%; }
        .high-school .second-cycle-table th:nth-child(8) { width: 15%; text-align: left; }
        .primary-bulletin { table-layout: fixed; font-size: 10px; }
        .primary-bulletin th, .primary-bulletin td { border: 1px solid #64748b; padding: 3px; text-align: center; }
        .primary-bulletin .vertical-heading { width: 38px; height: 112px; padding: 4px 2px; vertical-align: bottom; white-space: nowrap; }
        .primary-bulletin .vertical-heading span { display: inline-block; writing-mode: vertical-rl; transform: rotate(180deg); }
        .primary-bulletin .month-heading, .primary-bulletin .month-cell { width: 34px; }
        .primary-bulletin .subject-heading, .primary-bulletin .subject-cell { width: 34px; }
        .primary-bulletin .signature-cell { width: 40px; }
        .primary-bulletin .annual-row { background: #eff6ff; font-weight: 700; }
        .primary-bulletin .signature-mark { height: 26px; }
        .primary-cycle .report-title { margin: 8px 0; }
        .primary-cycle .identity { margin-bottom: 8px; }
        .primary-cycle .primary-summary { margin-top: 8px; }
        .primary-cycle .primary-summary th { text-align: left; }
        .primary-cycle .primary-summary td { text-align: center; font-weight: 700; }
        .primary-cycle .signatures { display: none; }
        @if($isSecondCycle || $isHighSchool)
            @page { size: A4 landscape; margin: 10mm; }
        @elseif($isPrimary)
            @page { size: A4 landscape; margin: 8mm; }
        @endif
        @media print {
            body { padding: 0; }
            .page { max-width: none; }
            .no-print { display: none !important; }
            .school-header, .identity, .average-row { break-inside: avoid; print-color-adjust: exact; -webkit-print-color-adjust: exact; }
            .second-cycle .page { max-width: none; }
            .second-cycle .identity { font-size: 10pt; }
            .second-cycle th, .second-cycle td { padding: 5px 6px; }
            .primary-cycle { font-size: 10pt; }
            .primary-cycle .page { max-width: none; padding: 0; }
            .primary-bulletin { font-size: 8pt; }
            .primary-bulletin .vertical-heading { height: 104px; }
            .primary-bulletin th, .primary-bulletin td { padding: 2px; }
        }
    </style>
</head>
<body @class(['second-cycle' => $isSecondCycle || $isHighSchool, 'high-school' => $isHighSchool, 'primary-cycle' => $isPrimary])>
    <main class="page">
        <div class="toolbar no-print">
            @php
                $notesQuery = ['class_room_id' => $classRoom->id, 'student_id' => $student->id];
            @endphp
            @if($isPrimary)
                @php
                    $notesQuery['evaluation_month'] = request('evaluation_month');
                @endphp
            @else
                @php
                    $notesQuery['trimester'] = $trimester;
                @endphp
            @endif
            <a class="back" href="{{ route('notes.index', $notesQuery) }}">Retour aux notes</a>
            <button type="button" onclick="window.print()">Imprimer le bulletin</button>
        </div>

        <header class="school-header">
            <x-doc-logo />
            <h1>{{ $schoolName }}</h1>
            <p>Ann&eacute;e scolaire : {{ $currentYear->name }}</p>
            <p>Document officiel de r&eacute;sultats scolaires</p>
        </header>

        <h2 class="report-title">{{ $isPrimary ? 'Bulletin de notes' : 'Bulletin du '.$trimesterLabel }}</h2>

        <section class="identity">
            <div><strong>&Eacute;l&egrave;ve :</strong> {{ $student->last_name }} {{ $student->first_name }}</div>
            <div><strong>Matricule :</strong> {{ $student->registration_number }}</div>
            <div><strong>Classe :</strong> {{ $classRoom->name }}</div>
            <div><strong>Sexe :</strong> {{ $student->genderLabel() }}</div>
            <div><strong>Date et lieu de naissance :</strong> {{ $student->birth_date ? \Carbon\Carbon::parse($student->birth_date)->format('d/m/Y') : '—' }} {{ $student->birth_place ? 'à '.$student->birth_place : '' }}</div>
            <div><strong>Parent / tuteur :</strong> {{ $student->parent_name ?: '—' }}{{ $student->parent_phone ? ' · '.$student->parent_phone : '' }}</div>
            @unless($isPrimary)
                <div><strong>P&eacute;riode :</strong> {{ $trimesterLabel }}</div>
            @endunless
            @if($isSecondCycle || $isHighSchool)
                <div><strong>Effectif de la classe :</strong> {{ $classStudentCount }}</div>
                <div><strong>Date d'édition :</strong> {{ now()->format('d/m/Y') }}</div>
            @endif
        </section>

        @if($isPrimary)
            <table class="primary-bulletin">
                <thead>
                    <tr>
                        <th rowspan="2" class="vertical-heading month-heading"><span>Mois</span></th>
                        <th colspan="{{ $primaryAssignments->count() }}">MATIÈRES</th>
                        <th rowspan="2" class="vertical-heading"><span>Total</span></th>
                        <th rowspan="2" class="vertical-heading"><span>Moyenne</span></th>
                        <th rowspan="2" class="vertical-heading"><span>Rang</span></th>
                        <th rowspan="2" class="vertical-heading"><span>Moyenne du 1er</span></th>
                        <th rowspan="2" class="vertical-heading signature-cell"><span>Signature du maître</span></th>
                        <th rowspan="2" class="vertical-heading signature-cell"><span>Signature du Directeur</span></th>
                        <th rowspan="2" class="vertical-heading signature-cell"><span>Visa des parents</span></th>
                        <th rowspan="2" class="vertical-heading"><span>Appréciation</span></th>
                    </tr>
                    <tr>
                        @foreach($primaryAssignments as $assignment)
                            <th class="vertical-heading subject-heading"><span>{{ $assignment->subject->name }}</span></th>
                        @endforeach
                    </tr>
                </thead>
                <tbody>
                    @foreach($primaryReport['months'] ?? [] as $monthRow)
                        <tr>
                            <th class="month-cell">{{ $monthRow['label'] }}</th>
                            @foreach($primaryAssignments as $assignment)
                                <td class="subject-cell">{{ $monthRow['scores'][$assignment->subject_id] !== null ? number_format($monthRow['scores'][$assignment->subject_id], 2, ',', ' ') : '—' }}</td>
                            @endforeach
                            <td>{{ $monthRow['points'] !== null ? number_format($monthRow['points'], 2, ',', ' ') : '—' }}</td>
                            <td>{{ $monthRow['average'] !== null ? number_format($monthRow['average'], 2, ',', ' ') : '—' }}</td>
                            <td>{{ $monthRow['rank'] ? $monthRow['rank'].'e' : '—' }}</td>
                            <td>{{ $monthRow['first_average'] !== null ? number_format($monthRow['first_average'], 2, ',', ' ') : '—' }}</td>
                            <td class="signature-mark"></td><td class="signature-mark"></td><td class="signature-mark"></td>
                            <td>{{ $monthRow['appreciation'] ?? '—' }}</td>
                        </tr>
                    @endforeach
                    <tr class="annual-row">
                        <th class="month-cell">Total</th>
                        @foreach($primaryAssignments as $assignment)
                            <td class="number">{{ ($primaryReport['annual_subjects'][$assignment->subject_id] ?? null) !== null ? number_format($primaryReport['annual_subjects'][$assignment->subject_id], 2, ',', ' ') : '—' }}</td>
                        @endforeach
                        <td class="number">{{ $primaryReport['annual_points'] !== null ? number_format($primaryReport['annual_points'], 2, ',', ' ') : '—' }}</td>
                        <td class="number">{{ $primaryReport['annual_average'] !== null ? number_format($primaryReport['annual_average'], 2, ',', ' ') : '—' }}</td>
                        <td class="number">{{ $primaryReport['annual_rank'] ? $primaryReport['annual_rank'].'e' : '—' }}</td>
                        <td class="number">{{ $firstClassAverage !== null ? number_format($firstClassAverage, 2, ',', ' ') : '—' }}</td>
                        <td></td><td></td><td></td>
                        <td>{{ $primaryReport['annual_appreciation'] ?? '—' }}</td>
                    </tr>
                </tbody>
            </table>
        @elseif($isSecondCycle || $isHighSchool)
            @php
                $totalPoints = $bulletinRows->sum(fn ($row) => $row['average'] !== null ? $row['average'] * $row['coefficient'] : 0);
                $totalCoefficients = $bulletinRows->sum(fn ($row) => $row['average'] !== null ? $row['coefficient'] : 0);
            @endphp
            <table class="second-cycle-table">
                <thead>
                    <tr>
                        <th>{{ $isHighSchool ? 'Discipline' : 'Matières' }}</th>
                        <th class="number">Coefficient</th>
                        <th class="number">{{ $isHighSchool ? 'Note de Classe' : 'Note de classe /'.$gradeScale }}</th>
                        <th class="number">{{ $isHighSchool ? 'Note de Comp /20' : 'Composition /'.$gradeScale }}</th>
                        <th class="number">{{ $isHighSchool ? 'Moy Général' : 'Moyenne /'.$gradeScale }}</th>
                        <th class="number">{{ $isHighSchool ? 'Moy. coef.' : 'Points coef.' }}</th>
                        @if($isHighSchool)<th class="number">Rang</th>@endif
                        <th>{{ $isHighSchool ? 'Appréciations' : 'Appr&eacute;ciation' }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($bulletinRows as $row)
                        <tr>
                            <td>{{ $row['subject']->name }}</td>
                            <td class="number">{{ number_format($row['coefficient'], 2, ',', ' ') }}</td>
                            <td class="number">{{ $row['continuous'] !== null ? number_format($row['continuous'], 2, ',', ' ') : '—' }}</td>
                            <td class="number">{{ $row['exam'] !== null ? number_format($row['exam'], 2, ',', ' ') : '—' }}</td>
                            <td class="number">{{ $row['average'] !== null ? number_format($row['average'], 2, ',', ' ') : '—' }}</td>
                            <td class="number">{{ $row['average'] !== null ? number_format($row['average'] * $row['coefficient'], 2, ',', ' ') : '—' }}</td>
                            @if($isHighSchool)<td class="number">{{ $row['rank'] ? $row['rank'].'e' : '—' }}</td>@endif
                            <td>{{ $row['appreciation'] ?? '—' }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="{{ $isHighSchool ? 8 : 7 }}" class="number">Aucune matière n’est affectée à cette classe.</td></tr>
                    @endforelse
                    <tr class="annual-row">
                        <th>Total</th>
                        <td class="number">{{ number_format($totalCoefficients, 2, ',', ' ') }}</td>
                        <td colspan="3"></td>
                        <td class="number">{{ number_format($totalPoints, 2, ',', ' ') }}</td>
                        @if($isHighSchool)<td></td>@endif
                        <td></td>
                    </tr>
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
                </tr></thead>
                <tbody><tr>
                    <td>{{ $generalAverage !== null ? number_format($generalAverage, 2, ',', ' ').' / '.$gradeScale : '—' }}</td>
                    <td>{{ $studentRank ? $studentRank.'e' : '—' }}</td>
                    @if($isHighSchool)<td>{{ $classStudentCount }}</td>@endif
                    <td>{{ $firstClassAverage !== null ? number_format($firstClassAverage, 2, ',', ' ').' / '.$gradeScale : '—' }}</td>
                    <td>{{ $classGeneralAverage !== null ? number_format($classGeneralAverage, 2, ',', ' ').' / '.$gradeScale : '—' }}</td>
                    <td>{{ $totalPoints !== 0.0 ? number_format($totalPoints, 2, ',', ' ').' / '.$totalCoefficients : '—' }}</td>
                    @unless($isHighSchool)<td>{{ $classStudentCount }}</td>@endunless
                </tr></tbody>
            </table>
        @endif

        @if($isPrimary || !$isSecondCycle)
        <div class="average-row">
            @if($isPrimary)
                Moyenne annuelle pondérée :
                {{ ($primaryReport['annual_average'] ?? null) !== null ? number_format($primaryReport['annual_average'], 2, ',', ' ').' / 10' : 'Non calculable' }}
            @elseif(!$isSecondCycle)
                Moyenne générale :
                {{ $generalAverage !== null ? number_format($generalAverage, 2, ',', ' ').' / '.$gradeScale : 'Non calculable' }}
            @endif
        </div>
        @endif
        @if(!$isSecondCycle && !$isPrimary && !$isHighSchool)
        <div class="identity">
            <div><strong>Rang :</strong> {{ $isPrimary ? (($primaryReport['annual_rank'] ?? null) ? $primaryReport['annual_rank'].'e' : 'Non classé') : ($studentRank ? $studentRank.'e' : 'Non classé') }}</div>
            <div><strong>Appréciation :</strong> {{ $isPrimary ? ($primaryReport['annual_appreciation'] ?? 'Non disponible') : ($studentAppreciation ?? 'Non disponible') }}</div>
            <div><strong>Moyenne de la classe :</strong> {{ $classGeneralAverage !== null ? number_format($classGeneralAverage, 2, ',', ' ').' / '.$gradeScale : 'Non calculable' }}</div>
            <div><strong>Moyenne du 1er :</strong> {{ $firstClassAverage !== null ? number_format($firstClassAverage, 2, ',', ' ').' / '.$gradeScale : 'Non calculable' }}</div>
        </div>
        @endif
        <p class="method">
            @if($isPrimary)
                Les compositions mensuelles sont notées sur 10. Les moyennes sont pondérées par les coefficients des matières.
            @else
                La moyenne est simple entre tous les devoirs, contrôles et l’examen du trimestre, notés sur 20.
            @endif
            La moyenne générale est pondérée par les coefficients.
        </p>

        @if($isSecondCycle || $isHighSchool)
            <div class="signature-grid">
                @unless($isHighSchool)<div class="signature-cell"><span class="signature-label">Signature du maître</span><span class="signature-space"></span></div>@endunless
                <div class="signature-cell"><span class="signature-label">Signature du Directeur</span><span class="signature-space"></span></div>
                <div class="signature-cell"><span class="signature-label">Visa des parents</span><span class="signature-space"></span></div>
            </div>
        @else
        <div class="signatures">
            <div class="signature">Le titulaire de classe</div>
            <div class="signature">La direction</div>
            @if($isPrimary)<div class="signature">Visa des parents</div>@endif
        </div>
        @endif
    </main>
</body>
</html>