<?php

namespace App\Controller;

use App\Message\WhatsappNotification;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Routing\Annotation\Route;
use App\Service\MessageService;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\Persistence\ManagerRegistry;
use App\Entity\Messages;

class HookController extends AbstractController
{
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
                        "wa_id": "34622814642"
                    }
                ],
                "messages": [
                    {
                        "from": "34622814642",
                        "id": "ABGGNGIoFGQvAgo-sAr3kcI5DI30",
                        "text": {
                            "body": "Test from ward"
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

        //$messageService->sendWhatsAppText("34622814642","Hi there");

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
        $setIdText = "";

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
            try{
                $messageService->sendWhatsApp(
                    $json->number, //Number
                    $placeholders, //Placeholders
                    'poll', //template
                    'en', //language
                    'f6baa15e_fb52_4d4f_a5a0_cde307dc3a85');

                $status = "OK";
                $setIdText = "0";
            }
            catch(Exception $exception){
                dd($exception->getMessage());
            }

            if($status == "OK"){
                try {
                    $entityManager = $doctrine->getManager();
                    $date = date("Y-m-d H:i:s");
                    $messages = new Messages();
                    $messages->setIdText($setIdText);
                    $messages->setWaId($json->number);
                    $messages->setText("Template text"); // "template"
                    $messages->setCreated($date);
                    $entityManager->persist($messages);
                    $entityManager->flush();
                }
                catch (Exception $exception){
                    $logger->error($exception->getMessage());
                }
            }

        }

        return $this->json([
            'status' => $status,
            'message' => $message
        ]);
    }
}
