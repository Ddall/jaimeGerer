<?php
namespace App\Service\CRM;

use App\Entity\Compta\CompteComptable;
use App\Entity\CRM\Compte;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

/**
 * @copyright  Copyright (c) 2018
 * @author blancsebastien
 * Created on 29 oct. 2018, 10:06:17
 */
class CompteService
{

    private $em;
    private $tokenStorage;
    private $logger;
    // Sames than into CompteFusionnerType
    private $fieldsToCheck = ['nom', 'telephone', 'adresse', 'ville', 'codePostal', 'region', 'pays', 'url', 'fax', 'codeEvoliz', 'priveOrPublic', 'compteComptableClient', 'compteComptableFournisseur'];

    public function __construct(EntityManagerInterface $em, TokenStorageInterface $tokenStorage, LoggerInterface $logger)
    {
        $this->em = $em;
        $this->tokenStorage = $tokenStorage;
        $this->logger = $logger;
    }

    /**
     * Fusionner 2 comptes ensemble.
     * 
     * @param Compte $compteA compte à garder
     * @param Compte $compteB compte à supprimer
     * @param CompteComptable $compteComptableClientToKeep
     * @param CompteComptable $compteComptableFournisseurToKeep
     * 
     * @return bool
     */
    public function mergeComptes(Compte $compteA, Compte $compteB, CompteComptable $compteComptableClientToKeep = null, CompteComptable $compteComptableFournisseurToKeep = null)
    {
        // Is the user allowed to merge A & B ?
        /* @var $user User */
        $user = $this->tokenStorage->getToken() ? $this->tokenStorage->getToken()->getUser() : null;
        if (!$user || $user->getCompany() !== $compteA->getCompany() || $user->getCompany() !== $compteB->getCompany()) {
            // L'utilisateur n'a pa les droits de merger ces 2 comptes
            return false;
        }
        // Check params validity
        if (!$this->checkMergeParams($compteA, $compteB)) {
            // Il faut sélectionner les champs à garder
            return false;
        }
        // Description
        $userName = $user ? $user->__toString() : 'Inconnu';
        $origCompteAData = $this->em->getUnitOfWork()->getOriginalEntityData($compteA);
        $origCompteBData = $this->em->getUnitOfWork()->getOriginalEntityData($compteB);
        $compteA->setDescription($origCompteAData['description'] . ' -- ' . $origCompteAData['nom'] . ' fusionné avec ' . $origCompteBData['nom'] . ' le ' . (new \DateTime())->format('d/m/Y') . ' par ' . $userName . ' -- ' . $origCompteBData['description']);
        // Set data if missing
        foreach ($this->fieldsToCheck as $field) {
            if (!self::needToChooseField($compteA, $compteB, $field)) {
                $getVal = 'get' . ucfirst($field);
                $setVal = 'set' . ucfirst($field);
                if ($compteB->$getVal()) {
                    $compteA->$setVal($compteB->$getVal());
                }
            }
        }
        // Modifié le / par
        if ($user) {
            $compteA->setUserEdition($user);
        }
        $compteA->setDateEdition(new \DateTime());
        // Compte comptable client : Journal de ventes, achats, banque, operations diverses
        if ($compteA->getCompteComptableClient() && $compteB->getCompteComptableClient() && $compteA->getCompteComptableClient() !== $compteB->getCompteComptableClient()) {
            if (!$compteComptableClientToKeep) {
                // Il faut selectionner un compte comptable client à garder
                return false;
            }
            $compteToCopyFrom = $compteComptableClientToKeep === $compteA->getCompteComptableClient() ? $compteB->getCompteComptableClient() : $compteA->getCompteComptableClient();
            // Journal de ventes
            foreach ($compteToCopyFrom->getJournalVentes() as $journalVente) {
                $journalVente->setCompteComptable($compteComptableClientToKeep);
            }
            // Journal d'achats
            foreach ($compteToCopyFrom->getJournalAchats() as $journalAchat) {
                $journalAchat->setCompteComptable($compteComptableClientToKeep);
            }
            // Journal de banques
            foreach ($compteToCopyFrom->getJournalBanque() as $journalBanque) {
                $journalBanque->setCompteComptable($compteComptableClientToKeep);
            }
            // Opérations diverses
            foreach ($compteToCopyFrom->getOperationsDiverses() as $operationDiverse) {
                $operationDiverse->setCompteComptable($compteComptableClientToKeep);
            }
            $compteA->setCompteComptableClient($compteComptableClientToKeep);
        }
        // Compte comptable fournisseur : Journal de ventes, achats, banque, operations diverses
        if ($compteA->getCompteComptableFournisseur() && $compteB->getCompteComptableFournisseur() && $compteA->getCompteComptableFournisseur() !== $compteB->getCompteComptableFournisseur()) {
            if (!$compteComptableFournisseurToKeep) {
                // Il faut selectionner un compte comptable fournisseur à garder
                return false;
            }
            $compteToCopyFrom = $compteComptableFournisseurToKeep === $compteA->getCompteComptableFournisseur() ? $compteB->getCompteComptableFournisseur() : $compteA->getCompteComptableFournisseur();
            // Journal de ventes
            foreach ($compteToCopyFrom->getJournalVentes() as $journalVente) {
                $journalVente->setCompteComptable($compteComptableFournisseurToKeep);
            }
            // Journal d'achats
            foreach ($compteToCopyFrom->getJournalAchats() as $journalAchat) {
                $journalAchat->setCompteComptable($compteComptableFournisseurToKeep);
            }
            // Journal de banques
            foreach ($compteToCopyFrom->getJournalBanque() as $journalBanque) {
                $journalBanque->setCompteComptable($compteComptableFournisseurToKeep);
            }
            // Opérations diverses
            foreach ($compteToCopyFrom->getOperationsDiverses() as $operationDiverse) {
                $operationDiverse->setCompteComptable($compteComptableFournisseurToKeep);
            }
            $compteA->setCompteComptableFournisseur($compteComptableFournisseurToKeep);
        }
        // Factures & Devis
        foreach ($compteB->getDocumentPrixs() as $documentPrix) {
            $documentPrix->setCompte($compteA);
        }
        // Actions Commerciales
        foreach ($compteB->getOpportunites() as $opportunite) {
            $opportunite->setCompte($compteA);
        }
        // Contacts
        foreach ($compteB->getContacts() as $contact) {
            $contact->setCompte($compteA);
        }
        // Dépenses
        foreach ($compteB->getDepenses() as $depense) {
            $depense->setCompte($compteA);
        }
        // Si $compteB est parent d'autres comptes, changer ces relations
        foreach ($compteB->getCompteEnfants() as $compteEnfant) {
            $compteEnfant->setCompteParent($compteA);
        }

        try {
            $this->em->beginTransaction();
            $this->em->flush();
            $this->em->refresh($compteB);
            $this->em->remove($compteB);
            $this->em->flush();
            $this->em->commit();

            return true;
        } catch (\Exception $e) {
            
            $this->em->rollback();
            $this->logger->critical('Error while merging Comptes ' . $compteA->getId() . ' and ' . $compteB->getId() . ' : ' . $e->getMessage());

            return false;
        }
    }

    /**
     * Return true if data between $compteA and $compteB is OK to be merged
     * 
     * @param Compte $compteA
     * @param Compte $compteB
     * 
     * @return boolean
     */
    private function checkMergeParams(Compte $compteA, Compte $compteB)
    {
        foreach ($this->fieldsToCheck as $field) {
            if (self::needToChooseField($compteA, $compteB, $field)) {
                $method = 'get' . ucfirst($field);
                if (!$compteA->$method()) {

                    return false;
                }
            }
        }

        return true;
    }

    /**
     * Return true if a given field must be chosen between compteA and compteB
     * 
     * @param Compte $compteA
     * @param Compte $compteB
     * @param string $field
     * 
     * @return boolean
     */
    public static function needToChooseField(Compte $compteA, Compte $compteB, $field)
    {
        $method = 'get' . ucfirst($field);
        if (method_exists(Compte::class, $method) && $compteA->$method() && $compteB->$method() && $compteA->$method() !== $compteB->$method()) {

            return true;
        }

        return false;
    }
}
