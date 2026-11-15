<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <title>Affectations et transferts</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 9px; color: #1e293b; }
        h1 { font-size: 15px; color: #103d32; margin-bottom: 2px; }
        .meta { color: #64748b; font-size: 8px; margin-bottom: 10px; }
        table { width: 100%; border-collapse: collapse; }
        th { background: #103d32; color: #fff; text-align: left; padding: 5px 4px; font-size: 8px; text-transform: uppercase; }
        td { padding: 4px; border-bottom: 1px solid #e2e8f0; vertical-align: top; }
        tr:nth-child(even) td { background: #f8fafc; }
    </style>
</head>
<body>
    <h1>Affectations et transferts</h1>
    <p class="meta">Généré le {{ $genereLe }} — {{ $affectations->count() }} mouvement(s)</p>
    <table>
        <thead>
            <tr>
                <th>Bien</th>
                <th>Origine</th>
                <th>Destinataire</th>
                <th>Motif</th>
                <th>Statut</th>
                <th>Demandé par</th>
                <th>Demandé le</th>
                <th>Décidé le</th>
                <th>Traité par</th>
                <th>Motif décision</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($affectations as $affectation)
                <tr>
                    <td>{{ $affectation->bien?->numero_inventaire }}<br>{{ $affectation->bien?->designation }}</td>
                    <td>{{ $affectation->serviceOrigine?->nom_service }}</td>
                    <td>{{ $affectation->serviceDestinataire?->nom_service }}</td>
                    <td>{{ $affectation->motif }}</td>
                    <td>{{ $labels[$affectation->statut] ?? $affectation->statut }}</td>
                    <td>{{ $affectation->demandeur?->name }}</td>
                    <td>{{ $affectation->date_demande?->format('d/m/Y') }}</td>
                    <td>{{ $affectation->date_validation?->format('d/m/Y') }}</td>
                    <td>{{ $affectation->valideur?->name }}</td>
                    <td>{{ $affectation->motif_decision }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
