<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Fiche de notes - {{ $classe->name }}</title>
    <style>
        * { box-sizing: border-box; }
        body { margin: 0; padding: 24px; color: #1e293b; background: #f1f5f9; font: 14px Arial, sans-serif; }
        .toolbar { max-width: 900px; margin: 0 auto 16px; display: flex; gap: 10px; }
        .toolbar button, .toolbar a { border: 0; border-radius: 7px; padding: 10px 14px; font-size: 13px; font-weight: 700; text-decoration: none; cursor: pointer; color: white; background: #2563eb; }
        .toolbar a { color: #334155; background: #e2e8f0; }
        .page { max-width: 900px; margin: 0 auto; padding: 32px; background: white; border: 1px solid #cbd5e1; }
        header { padding-bottom: 12px; border-bottom: 2px solid #1e3a8a; text-align: center; }
        header h1 { margin: 0 0 4px; color: #1e3a8a; font-size: 20px; text-transform: uppercase; }
        header p { margin: 3px 0; color: #475569; }
        h2 { margin: 20px 0 10px; text-align: center; font-size: 17px; text-transform: uppercase; }
        .fields { display: flex; justify-content: space-between; gap: 20px; margin-bottom: 14px; font-size: 13px; }
        .fields span { flex: 1; border-bottom: 1px dotted #64748b; padding-bottom: 2px; }
        table { width: 100%; border-collapse: collapse; font-size: 13px; }
        th, td { padding: 7px 8px; border: 1px solid #94a3b8; text-align: left; }
        th { background: #eff6ff; }
        td.num, th.num { width: 40px; text-align: center; }
        td.note, th.note { width: 90px; text-align: center; }
        tr { break-inside: avoid; }
        thead { display: table-header-group; }
        @media print {
            @page { size: A4; margin: 14mm; }
            body { padding: 0; background: white; }
            .page { max-width: none; padding: 0; border: 0; }
            .no-print { display: none !important; }
            th { print-color-adjust: exact; -webkit-print-color-adjust: exact; }
        }
    </style>
</head>
<body>
    <div class="toolbar no-print">
        <button type="button" onclick="window.print()">Imprimer</button>
        <a href="{{ route('classes.show', $classe) }}">Retour</a>
    </div>

    <div class="page">
        <header>
            <x-doc-logo />
            <h1>{{ $schoolName }}</h1>
            @if($schoolYear)
                <p>Année scolaire : {{ $schoolYear->name }}</p>
            @endif
        </header>

        <h2>Fiche de notes – Classe {{ $classe->name }} ({{ $classe->cycle }})</h2>
        <div class="fields">
            <span>Matière : </span>
            <span>Enseignant : </span>
            <span>Période : </span>
        </div>

        <table>
            <thead>
                <tr>
                    <th class="num">N°</th>
                    <th>Nom et prénom</th>
                    <th class="note">Date de Naissance</th>
                    <th class="note">NOTE 1</th>
                    <th class="note">NOTE 2</th>
                    <th class="note">NOTE 3</th>
                    <th class="note">Note Finale</th>
                </tr>
            </thead>
            <tbody>
                @forelse($students as $student)
                    <tr>
                        <td class="num">{{ $loop->iteration }}</td>
                        <td>{{ mb_strtoupper($student->last_name) }} {{ $student->first_name }}</td>
                        <td class="note">{{ $student->birth_date ? \Carbon\Carbon::parse($student->birth_date)->format('d/m/Y') : '' }}</td>
                        <td class="note"></td>
                        <td class="note"></td>
                        <td class="note"></td>
                        <td class="note"></td>
                    </tr>
                @empty
                    <tr><td colspan="7" style="text-align:center">Aucun élève dans cette classe.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</body>
</html>
