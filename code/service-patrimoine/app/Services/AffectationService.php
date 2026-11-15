<?php

namespace App\Services;

use App\Models\Affectation;
use App\Models\Bien;
use App\Models\Service;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class AffectationService
{
    /**
     * @param  array{service_destinataire_id: int, localisation_destinataire?: ?string, motif?: ?string, date_effet?: ?string}  $data
     */
    public function proposer(Bien $bien, array $data, User $demandeur): Affectation
    {
        if (! $bien->is_active) {
            throw ValidationException::withMessages([
                'bien' => ['Ce bien est archivé et ne peut plus être affecté.'],
            ]);
        }

        $this->ensureNoPendingRequest($bien);

        $serviceDestinataire = Service::query()->findOrFail($data['service_destinataire_id']);

        if (! $serviceDestinataire->is_active) {
            throw ValidationException::withMessages([
                'service_destinataire_id' => ['Ce service est inactif.'],
            ]);
        }

        $this->ensureMeaningfulDestination($bien, $serviceDestinataire->getKey());

        return Affectation::create([
            'bien_id' => $bien->getKey(),
            'service_origine_id' => $this->serviceActuel($bien)?->getKey(),
            'service_destinataire_id' => $serviceDestinataire->getKey(),
            'localisation_origine' => $bien->localisation,
            'localisation_destinataire' => $data['localisation_destinataire'] ?? null,
            'motif' => $data['motif'] ?? null,
            'date_effet' => $data['date_effet'] ?? null,
            'date_demande' => now(),
            'demandeur_id' => $demandeur->getKey(),
        ]);
    }

    public function valider(Affectation $affectation, User $valideur, ?string $motifDecision = null): Affectation
    {
        $this->ensurePending($affectation);
        $this->ensureNotOwnRequest($affectation, $valideur);

        DB::transaction(function () use ($affectation, $valideur, $motifDecision) {
            $dateEffet = $affectation->date_effet?->toDateString() ?? now()->toDateString();

            $affectation->bien->affectations()
                ->where('statut', 'VALIDEE')
                ->whereNull('date_fin')
                ->whereKeyNot($affectation->getKey())
                ->update(['date_fin' => $dateEffet]);

            $affectation->update([
                'statut' => 'VALIDEE',
                'date_effet' => $dateEffet,
                'date_validation' => now(),
                'valideur_id' => $valideur->getKey(),
                'motif_decision' => $motifDecision,
            ]);

            $affectation->bien->update([
                'service_id' => $affectation->service_destinataire_id,
                'localisation' => $affectation->localisation_destinataire
                    ?? $affectation->bien->localisation,
            ]);
        });

        return $affectation->refresh();
    }

    public function rejeter(Affectation $affectation, User $valideur, string $motifDecision): Affectation
    {
        $this->ensurePending($affectation);
        $this->ensureNotOwnRequest($affectation, $valideur);

        $affectation->update([
            'statut' => 'REJETEE',
            'date_validation' => now(),
            'valideur_id' => $valideur->getKey(),
            'motif_decision' => $motifDecision,
        ]);

        return $affectation->refresh();
    }

    public function annuler(Affectation $affectation, User $user): Affectation
    {
        $this->ensurePending($affectation);

        $isOwner = $affectation->demandeur_id === $user->getKey();
        if (! $isOwner && $user->role !== 'ADMIN') {
            throw ValidationException::withMessages([
                'affectation' => ['Seul le demandeur ou un administrateur peut annuler cette demande.'],
            ]);
        }

        $affectation->update([
            'statut' => 'ANNULEE',
            'motif_decision' => 'Demande annulée.',
        ]);

        return $affectation->refresh();
    }

    private function ensurePending(Affectation $affectation): void
    {
        if (! $affectation->estEnAttente()) {
            throw ValidationException::withMessages([
                'affectation' => ['Cette demande n’est plus en attente de validation.'],
            ]);
        }
    }

    private function ensureNotOwnRequest(Affectation $affectation, User $user): void
    {
        if ($affectation->demandeur_id === $user->getKey()) {
            throw ValidationException::withMessages([
                'affectation' => ['Le demandeur ne peut pas valider sa propre demande.'],
            ]);
        }
    }

    private function ensureNoPendingRequest(Bien $bien): void
    {
        $exists = $bien->affectations()
            ->where('statut', 'EN_ATTENTE')
            ->exists();

        if ($exists) {
            throw ValidationException::withMessages([
                'bien' => ['Une demande d’affectation est déjà en attente pour ce bien.'],
            ]);
        }
    }

    private function ensureMeaningfulDestination(Bien $bien, int $serviceDestinataireId): void
    {
        $actuel = $this->serviceActuel($bien)?->getKey();

        if ($actuel !== null && $actuel === $serviceDestinataireId) {
            throw ValidationException::withMessages([
                'service_destinataire_id' => ['Ce service est déjà le service responsable actuel du bien.'],
            ]);
        }
    }

    private function serviceActuel(Bien $bien): ?Service
    {
        $affectationActive = $bien->affectations()
            ->where('statut', 'VALIDEE')
            ->whereNull('date_fin')
            ->latest('date_effet')
            ->first();

        if ($affectationActive) {
            return $affectationActive->serviceDestinataire;
        }

        return $bien->service_id ? Service::find($bien->service_id) : null;
    }
}
