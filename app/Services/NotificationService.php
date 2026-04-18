<?php

namespace App\Services;

use App\Models\Demande;
use App\Models\Document;
use App\Models\Notification;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

class NotificationService
{
    /**
     * Envoyer une notification web + email optionnel
     */
    public function envoyer(
        Demande $demande,
        string  $type,
        string  $titre,
        string  $message,
        bool    $sendEmail = true
    ): Notification {
        // 1. Notification in-app
        $notification = Notification::create([
            'user_id'    => $demande->user_id,
            'demande_id' => $demande->id,
            'type'       => $type,
            'titre'      => $titre,
            'message'    => $message,
            'canal'      => 'web',
        ]);

        // 2. Email (si activé)
        if ($sendEmail && config('app.notifications_email', true)) {
            $this->envoyerEmail($demande, $titre, $message);
        }

        Log::info("Notification envoyée", [
            'user_id'    => $demande->user_id,
            'type'       => $type,
            'demande'    => $demande->numero_demande,
        ]);

        return $notification;
    }

    /**
     * Notifications prédéfinies selon changement de statut
     */
    public function notifierChangementStatut(Demande $demande, string $ancienStatut): void
    {
        $messages = [
            'soumise' => [
                'titre'   => '✅ Demande soumise avec succès',
                'message' => "Votre demande {$demande->numero_demande} a été soumise. Vous allez recevoir les instructions de paiement.",
            ],
            'en_attente_paiement' => [
                'titre'   => '💳 Paiement requis',
                'message' => "Votre demande {$demande->numero_demande} est en attente de paiement de {$demande->montant_formate}.",
            ],
            'payee' => [
                'titre'   => '💚 Paiement confirmé',
                'message' => "Votre paiement a été confirmé. Votre demande est maintenant en cours de traitement.",
            ],
            'en_cours_traitement' => [
                'titre'   => '🔄 Demande en cours de traitement',
                'message' => "Votre demande {$demande->numero_demande} est en cours d'examen par nos agents.",
            ],
            'documents_manquants' => [
                'titre'   => '⚠️ Documents manquants',
                'message' => "Des documents supplémentaires sont requis pour votre demande. Commentaire : {$demande->commentaire_admin}",
            ],
            'validee' => [
                'titre'   => '🎉 Demande validée !',
                'message' => "Félicitations ! Votre demande {$demande->numero_demande} a été validée. Votre passeport sera prêt sous 15 jours ouvrés.",
            ],
            'rejetee' => [
                'titre'   => '❌ Demande rejetée',
                'message' => "Votre demande {$demande->numero_demande} a été rejetée. Motif : {$demande->motif_rejet}. Vous pouvez soumettre une nouvelle demande.",
            ],
            'passeport_pret' => [
                'titre'   => '🛂 Passeport prêt à retirer',
                'message' => "Votre passeport est prêt ! Rendez-vous au bureau avec votre reçu de paiement et votre pièce d'identité.",
            ],
        ];

        $messages['delivre'] = [
            'titre'   => '📬 Passeport délivré',
            'message' => "Votre passeport pour la demande {$demande->numero_demande} a été délivré. Merci d'avoir utilisé PasseportSN.",
        ];

        // Message enrichi pour passeport_pret avec date de RDV
        if ($demande->statut === 'passeport_pret' && $demande->date_rdv) {
            $messages['passeport_pret'] = [
                'titre'   => '🛂 Passeport prêt — Rendez-vous confirmé',
                'message' => "Votre passeport est prêt ! Votre rendez-vous de retrait est fixé au {$demande->date_rdv->format('d/m/Y')}. Présentez-vous avec votre CIN et votre reçu de paiement.",
            ];
        }

        $types = [
            'soumise'             => 'demande_soumise',
            'en_attente_paiement' => 'systeme',
            'payee'               => 'paiement_recu',
            'en_cours_traitement' => 'demande_en_cours',
            'documents_manquants' => 'documents_manquants',
            'validee'             => 'demande_validee',
            'rejetee'             => 'demande_rejetee',
            'passeport_pret'      => 'passeport_pret',
            'delivre'             => 'systeme',
        ];

        if (isset($messages[$demande->statut])) {
            $msg  = $messages[$demande->statut];
            $type = $types[$demande->statut] ?? 'systeme';
            $this->envoyer($demande, $type, $msg['titre'], $msg['message']);
        }
    }

    /**
     * Notification lors de la validation d'un document
     */
    public function notifierDocumentValide(Document $document): void
    {
        $demande   = $document->demande;
        $typeLabel = \App\Models\Document::TYPES_LABELS[$document->type_document] ?? $document->type_document;

        $this->envoyer(
            $demande,
            'systeme',
            '✅ Document validé',
            "Votre document « {$typeLabel} » pour la demande {$demande->numero_demande} a été validé par nos agents."
        );
    }

    /**
     * Notification lors du rejet d'un document
     */
    public function notifierDocumentRejete(Document $document): void
    {
        $demande   = $document->demande;
        $typeLabel = \App\Models\Document::TYPES_LABELS[$document->type_document] ?? $document->type_document;
        $motif     = $document->commentaire ? " Motif : {$document->commentaire}" : '';

        $this->envoyer(
            $demande,
            'documents_manquants',
            '❌ Document rejeté — action requise',
            "Votre document « {$typeLabel} » pour la demande {$demande->numero_demande} a été rejeté.{$motif} Veuillez soumettre un nouveau document."
        );
    }

    private function envoyerEmail(Demande $demande, string $titre, string $message): void
    {
        try {
            $user = $demande->user;

            Mail::send('emails.notification', [
                'user'    => $user,
                'titre'   => $titre,
                'message' => $message,
                'demande' => $demande,
            ], function ($mail) use ($user, $titre) {
                $mail->to($user->email, $user->nom_complet)
                     ->subject("[PasseportSN] {$titre}");
            });

        } catch (\Exception $e) {
            Log::error("Erreur envoi email notification", ['error' => $e->getMessage()]);
        }
    }
}
