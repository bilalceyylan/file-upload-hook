<?php

namespace App\MessageHandler;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;
use App\Message\FileUploadHook;

class FileUploadHookHandler implements MessageHandlerInterface
{
    public function __invoke(FileUploadHook $fileUploadHook)
    {
        dump($fileUploadHook);
    }
}