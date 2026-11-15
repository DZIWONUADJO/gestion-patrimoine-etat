<?php

namespace App\Services;

use App\Models\Bien;
use App\Models\EtatHistorique;
use App\Models\Intervention;
use App\Models\Inventaire;
use App\Models\InventaireLigne;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class SuiviService
{
    public function changerEtat(Bien $bien, string $etat, ?string $commentaire, ?User $auteur): EtatHistorique
    {
        if (! in_array($etat, Bien::ETATS, true)) {
            throw ValidationException::withMessages([
                'etat' => ['État inconnu.'],
            ]);
        }

        if (! $bien->is_active) {
            throw ValidationException::withMessages([
                'bien' => ['Ce bien est archivé et ne peut plus changer d’état.'],
            ]);
        }

        if ($bien->etat === $etat) {
            throw ValidationException::withMessages([
                'etat' => ['Le bien est déjà dans cet état.'],
            ]);
        }

        return DB::transaction(function () use ($bien, $etat, $commentaire, $auteur) {
            $historique = $bien->etatHistoriques()->create([
                'de_etat' => $bien->etat,
                'vers_etat' => $etat,
                'commentaire' => $commentaire,
                'auteur_id' => $auteur?->getKey(),
            ]);

            $bien->update(['etat' => $etat]);

            return $historique;
        });
    }

    /**
     * @param  array{description: string, type?: string, prestataire?: ?string, cout?: ?float, date_debut: string, commentaire?: ?string}  $data
     */
    public function ouvrirIntervention(Bien $bien, array $data, User $auteur): Intervention
    {
        if (! $bien->is_active) {
            throw ValidationException::withMessages([
                'bien' => ['Ce bien est archivé et ne peut plus faire l’objet d’une intervention.'],
            ]);
        }

        $enCours = $bien->interventions()->where('statut', 'EN_COURS')->exists();
        if ($enCours) {
            throw ValidationException::withMessages([
                'intervention' => ['Une intervention est déjà en cours pour ce bien.'],
            ]);
        }

        return DB::transaction(function () use ($bien, $data, $auteur) {
            $intervention = $bien->interventions()->create([
                'type' => $data['type'] ?? 'REPARATION',
                'description' => $data['description'],
                'prestataire' => $data['prestataire'] ?? null,
                'cout' => $data['cout'] ?? null,
                'etat_precedent' => $bien->etat,
                'date_debut' => $data['date_debut'],
                'commentaire' => $data['commentaire'] ?? null,
                'auteur_id' => $auteur->getKey(),
            ]);

            $this->changerEtat(
                $bien,
                'EN_REPARATION',
                'Intervention ouverte : '.$data['description'],
                $auteur,
            );

            return $intervention;
        });
    }

    /**
     * @param  array{etat?: string, date_fin?: ?string, commentaire?: ?string}  $data
     */
    public function cloturerIntervention(Intervention $intervention, User $user, array $data = []): Intervention
    {
        $this->ensureInterventionEnCours($intervention);

        $etatCible = ! empty($data['etat']) ? $data['etat'] : $intervention->etat_precedent;

        return DB::transaction(function () use ($intervention, $user, $data, $etatCible) {
            $intervention->update([
                'statut' => 'TERMINEE',
                'date_fin' => $data['date_fin'] ?? now()->toDateString(),
                'commentaire' => $data['commentaire'] ?? $intervention->commentaire,
            ]);

            $this->changerEtat(
                $intervention->bien,
                $etatCible,
                'Intervention terminée : '.$intervention->description,
                $user,
            );

            return $intervention->refresh();
        });
    }

    public function annulerIntervention(Intervention $intervention, User $user): Intervention
    {
        $this->ensureInterventionEnCours($intervention);

        return DB::transaction(function () use ($intervention, $user) {
            $intervention->update(['statut' => 'ANNULEE']);

            if ($intervention->bien->etat === 'EN_REPARATION') {
                $this->changerEtat(
                    $intervention->bien,
                    $intervention->etat_precedent,
                    'Intervention annulée : '.$intervention->description,
                    $user,
                );
            }

            return $intervention->refresh();
        });
    }

    /**
     * @param  array{statut: string, commentaire?: ?string}  $data
     */
    public function releverLigne(Inventaire $inventaire, Bien $bien, array $data, User $releveur): InventaireLigne
    {
        $this->ensureInventaireEnCours($inventaire);

        return DB::transaction(function () use ($inventaire, $bien, $data, $releveur) {
            $ligne = InventaireLigne::updateOrCreate(
                [
                    'inventaire_id' => $inventaire->getKey(),
                    'bien_id' => $bien->getKey(),
                ],
                [
                    'statut' => $data['statut'],
                    'commentaire' => $data['commentaire'] ?? null,
                    'releve_par' => $releveur->getKey(),
                ],
            );

            if ($data['statut'] === 'INTROUVABLE' && $bien->is_active) {
                $this->changerEtat(
                    $bien,
                    'INTROUVABLE',
                    'Déclaré introuvable lors de l’inventaire « '.$inventaire->libelle.' »'
                        .($data['commentaire'] ? ' : '.$data['commentaire'] : '.'),
                    $releveur,
                );
            }

            return $ligne;
        });
    }

    public function cloturerInventaire(Inventaire $inventaire): Inventaire
    {
        $this->ensureInventaireEnCours($inventaire);

        $inventaire->update(['statut' => 'CLOTUREE']);

        return $inventaire->refresh();
    }

    private function ensureInterventionEnCours(Intervention $intervention): void
    {
        if (! $intervention->estEnCours()) {
            throw ValidationException::withMessages([
                'intervention' => ['Cette intervention n’est plus en cours.'],
            ]);
        }
    }

    private function ensureInventaireEnCours(Inventaire $inventaire): void
    {
        if (! $inventaire->estEnCours()) {
            throw ValidationException::withMessages([
                'inventaire' => ['Cet inventaire est clôturé.'],
            ]);
        }
    }
}
