<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <title>Inventaire des biens</title>
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
    <h1>Inventaire du patrimoine de l'État</h1>
    <p class="meta">Généré le {{ $genereLe }} — {{ $biens->count() }} bien(s)</p>
    <table>
        <thead>
            <tr>
                <th>N° inventaire</th>
                <th>Désignation</th>
                <th>Catégorie</th>
                <th>Service</th>
                <th>État</th>
                <th>Localisation</th>
                <th>Marque / Modèle</th>
                <th>N° série</th>
                <th>Acquisition</th>
                <th>Valeur</th>
                <th>Statut</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($biens as $bien)
                <tr>
                    <td>{{ $bien->numero_inventaire }}</td>
                    <td>{{ $bien->designation }}</td>
                    <td>{{ $bien->categorie?->libelle }}</td>
                    <td>{{ $bien->service?->nom_service }}</td>
                    <td>{{ $labels[$bien->etat] ?? $bien->etat }}</td>
                    <td>{{ $bien->localisation }}</td>
                    <td>{{ trim(($bien->marque ?? '').' '.($bien->modele ?? '')) ?: '—' }}</td>
                    <td>{{ $bien->numero_serie }}</td>
                    <td>{{ $bien->date_acquisition?->format('d/m/Y') }}</td>
                    <td>{{ $bien->valeur_acquisition !== null ? number_format((float) $bien->valeur_acquisition, 0, ',', ' ') : '—' }}</td>
                    <td>{{ $bien->is_active ? 'Actif' : 'Archivé' }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
