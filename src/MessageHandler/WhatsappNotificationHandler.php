<?php

// src/MessageHandler/SmsNotificationHandler.php
namespace App\MessageHandler;

use App\Entity\PollMessage;
use App\Message\WhatsappNotification;
use App\Service\MessageService;
use DateTimeInterface;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use App\Repository\PollMessageRepository;
use App\Service\BotService;
use App\Service\WhatsappService;
use Doctrine\ORM\EntityManagerInterface;
use GuzzleHttp\Psr7\Message;


#[AsMessageHandler]
class WhatsappNotificationHandler
{
    private $logger;
    private $em;
    private $messageService;
    private $doctrine;
    private $textToBeSent;
    private $textType;


    public function __construct(LoggerInterface $logger, MessageService $messageService, EntityManagerInterface $em, EntityManagerInterface $doctrine)
    {
        $this->logger = $logger;
        $this->messageService = $messageService;
        $this->em = $em;
        $this->doctrine = $doctrine;
    }

    public function __invoke(WhatsappNotification $message)
    {

        $content = $message->getContent();
        $jsonDecodedMessage = json_decode($content);


        // $this->logger->info("Message sent!");
        if (!isset($jsonDecodedMessage->messages)) {
            return;
        }

        $mensaje = $jsonDecodedMessage->{'contacts'};


        foreach ($jsonDecodedMessage->{'contacts'} as $item){
        $previouspoll = $this->getPreviousMessage($item->wa_id);
          if($this->doctrine->getRepository(PollMessage::class)->checkSentMessage($item->wa_id, $previouspoll->getPollid())){

            $this->messageService->sendWhatsAppText(
               $item->wa_id,
               "You have already answered in the poll."
              
            );
            return;
          
        }
    }


        //foreach
         foreach ($jsonDecodedMessage->{'contacts'} as $item){
             
        //process each one
            
            $previousMessage = $this->getPreviousMessage($item->wa_id);

                $sentThanks = false;

                if ($previousMessage){

                    $this->textToBeSent = " ";
                
                    $this->textToBeSent = 'This is not a valid answer, sorry';
        
                    //decide what/if we send a message
        
                    $texto = $this->gettextToBeSent($previousMessage, $jsonDecodedMessage);
        
                   if($sentThanks==false){
                     $m = $this->sendMessage($jsonDecodedMessage);
                        $sentThanks=true;
                        $this->saveData($previousMessage);
                  //die;
                     }

                    //save the data to the database
                     

             }
        }


    
    }
    private function getPreviousMessage($waId){
        return $this->em->getRepository(PollMessage::class)->findOneBy([
            'wa_id' => $waId
        ],
            [
                'id' => 'DESC'
            ]);
    }

    private function gettextToBeSent($previousMessage, $message)
    {
        $prevText = $previousMessage->getText();

        $msg_messages = $message->{'messages'};
        $textMessage = $msg_messages[0]->text->body;

        $textType = -1;
        $textToBeSent = " ";

        if ($prevText == "Template text") {
            if ($textMessage == '1') {  //get text of the next message depending on the previous message
                $this->textToBeSent = "Thanks for answering the poll!";
                $this->textType = 1;
            } else if ($textMessage == '2') {
                $this->textToBeSent = "Thanks for answering the poll!";
                $this->textType = 2;
            } else if ($textMessage == '3') {
                $this->textToBeSent = "Thanks for answering the poll!";
                $this->textType = 3;
            } else if ($textMessage == '4') {
                $this->textToBeSent = "Thanks for answering the poll!";
                $this->textType = 4;
            }
        } else {

            return;
        }

        return $textToBeSent;
    }

    private function sendMessage($message)
    {  //send message with text depending on previous message
        $msg = $message->{'contacts'};

        $this->messageService->sendWhatsAppText(
            $msg[0]->wa_id,
            $this->textToBeSent
        );
        $sentThanks = true;
        return $message;
    }

    private function saveData($message)
    { //save data of the sent message

        $message->setText($this->textType); 

        $this->em->persist($message);
        $this->em->flush();
    }


}

