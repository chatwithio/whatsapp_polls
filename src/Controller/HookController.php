<?php

namespace App\Controller;

use App\Message\WhatsappNotification;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Routing\Annotation\Route;
use App\Service\MessageService;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\Persistence\ManagerRegistry;
use App\Entity\PollMessage;

class HookController extends AbstractController
{

    private $setIdText;

    #[Route('/hook-endpoint', name: 'hook_endpoint1')]
    // POST
    public function whatsappHook(MessageBusInterface $bus,  Request $request): Response
    {

        /*
         * Json:
         *
         * {
                "contacts": [
                    {
                        "profile": {
                            "name": "Ward"
                        },
                        "wa_id": "34697110110"
                    }
                ],
                "messages": [
                    {
                        "from": "34697110110",
                        "id": "ABGGNGIoFGQvAgo-sAr3kcI5DI30",
                        "text": {
                            "body": "1"
                        },
                        "timestamp": "1640174341",
                        "type": "text"
                    }
                ]
            }
         *
         *
         *
         */



        $content = $request->getContent();

        $bus->dispatch(new WhatsappNotification($content));

        return $this->json([
            //Facebook doesnt care about our message only the status code - 200 or 201
            'message' => 'Message ok!',
        ]);
    }

    #[Route('/chatwith-endpoint', name: 'chatwith_endpoint1')]
    // POST
    public function index(
        MessageService $messageService,
        Request $request,
        ManagerRegistry $doctrine,
        LoggerInterface $logger): Response
    {
        $content = $request->getContent();
        $json = json_decode($content); //decode JSON and obtain data
        $status = "KO";
        $message = " ";


        $placeholders = [
            $json -> q1,
            $json-> a1,
            $json-> a2,
            $json -> a3,
            $json -> a4
        ];



        if (!is_numeric($json->number)) {
            $message = 'This is not a number';
        }else{
            $isfirst=true;
            foreach($json->numbers_poll as $user){
            try{
                
                $messageService->sendWhatsApp(
                    $user, //Number
                    $placeholders, //Placeholders
                    'poll', //template
                    'en', //language
                    'f6baa15e_fb52_4d4f_a5a0_cde307dc3a85');

                $status = "OK";
                $this->setIdText=0;
            }
            catch(\Exception $exception){
                dd($exception->getMessage());
            }

            if($status == "OK" ){
                
                try {
                    
                    $entityManager = $doctrine->getManager();
                    $messages = new PollMessage();
                    $messages->setIdText($this->setIdText);
                    $messages->setWaId($user);
                    $messages->setText("Template text"); // "template"
                    $messages->setFirstmessage($isfirst);
                    $messages->setPollid(md5($json -> q1));
                    //$message->setMessagesent();
                    $entityManager->persist($messages);
                    $entityManager->flush();
                }
                catch (\Exception $exception){
                    $logger->error($exception->getMessage());
                }
            }
        
            $isfirst=false;
        }

        }

        return $this->json([
            'status' => $status,
            'message' => $message
        ]);
    }

    
    #[Route('/cron-endpoint', name: 'cron_endpoint1')]
    // POST
    public function cron(ManagerRegistry $doctrine,MessageService $messageService): Response{

        //Recibimos el cron - see crontab -e

        ////1. Buscamos los mensajes que  han cuaducado y q no tienen bandera enviado
        ///
        $expired = $doctrine->getRepository(PollMessage::class)->getExpiredMessages();
        foreach($expired as $item){
    
            
            $countOne = $doctrine->getRepository(PollMessage::class)
            ->count(array('text' => 1, 'wa_id'=> $item->getWaId(), 'pollid'=> $item->getPollid()));
        
            $countTwo = $doctrine->getRepository(PollMessage::class)
            ->count(array('text' => 2, 'wa_id'=> $item->getWaId(), 'pollid'=> $item->getPollid()));

            $countThree = $doctrine->getRepository(PollMessage::class)
            ->count(array('text' => 3, 'wa_id'=> $item->getWaId(), 'pollid'=> $item->getPollid()));
            $countFour = $doctrine->getRepository(PollMessage::class)
            ->count(array('text' => 4, 'wa_id'=> $item->getWaId(), 'pollid'=> $item->getPollid()));

            $resultsPoll = "The results of the poll are the followings
            Answer 1: $countOne votes.
            Answer 2: $countTwo votes.
            Answer 3: $countThree votes.
            Answer 4: $countFour votes.";

            /// 2. enviar un whatsapp a cada uno
             /// 
    

            $messageService->sendWhatsAppText(
                $item->getWaId(),
                $resultsPoll
                
            ); 

            /// 3. cerrarlo - cambiar la bandera
            $item->setMessagesent(true);
            $doctrine->getManager()->persist($item);
            $doctrine->getManager()->flush();

        }
    
        return $this->json([
            'status' => 'OK',
            'message' => ' '
        ]);
    } 

}
