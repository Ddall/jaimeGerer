<?php

namespace App\Command;

use App\Service\Emailing\MailgunService;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class EmailingSendScheduledCommand extends Command
{

    private $em;
    private $mailgunService;
    private $cronLogger;
    
    
    public function __construct(EntityManagerInterface $em, MailgunService $mailgunService, LoggerInterface $cronLogger)
    {
        parent::__construct();
        $this->em = $em;
        $this->mailgunService = $mailgunService;
        $this->cronLogger = $cronLogger;
    }


    protected function configure()
    {
        $this
            ->setName('jg:emailing-send-scheduled')
            ->setDescription('Send scheduled campaigns to MailGun');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {

        $campagneRepo = $this->em->getRepository('App:Emailing\Campagne');
        
        $arr_campagnes = $campagneRepo->findScheduledForToday();

        $this->cronLogger->info(count($arr_campagnes).' campagnes à envoyer à MailGun.');

        foreach($arr_campagnes as $campagne){

            try{
                $this->mailgunService->sendCampagneViaAPI($campagne);

                $campagne->setEtat('DELIVERING');
         
                $this->em->persist($campagne);
                $this->em->flush();

                $this->cronLogger->info('--- '.$campagne->getId().' envoyée à MailGun.');
            } catch(\Exception $e){
                $this->cronLogger->error('--- Erreur lors de l\'envoi de la campagne '.$campagne->getId().'  à MailGun : '.$e->getMessage());
                
                $campagne->setEtat('ERROR');

                $this->em->persist($campagne);
                $this->em->flush();
            }
        }
      
    }

}
