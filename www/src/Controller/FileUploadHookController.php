<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Finder\Finder;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Reader\Xlsx;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Message\FileUploadHook;
use Symfony\Component\Messenger\MessageBusInterface;
use App\Entity\Category;

class FileUploadHookController extends AbstractController
{
    /**
     * @Route("/file-upload-hook", name="file_upload_hook")
     */
    public function index(MessageBusInterface $messageBus, \Swift_Mailer $mailer)
    {
        //Connecting to ftp
        $finder = new Finder();
        $finder->in('ftp://challenge%4090pixel.net:ch%40lleng3@90pixel.net/categories/'); 
        
        //Files extension xlsx getting
        $fileArray = $finder->files()->name('*.xlsx'); 

        //Close date 
        $closeDate = 0;

        //Close date file
        $getFile = "";
        foreach ($fileArray as $file) 
        {
            //Find date from filename
            $getDate = $this->getStringBeetwen($file->getFileName(), "-", "." );

            //When the text or date is missing, there will be no close dates as the date will show 1970 by default 
            $checkDate = date("Y-m-d H:i:s", strtotime($getDate) ); 

            //Recent history crushes distant history
            if($checkDate > $closeDate)
            {
                $closeDate = $checkDate;
                $getFile = $file;
            } 
        }   
        //Take the project path as a parameter
        $projectDir = $this->getParameter('projectDir'); 

        //Temporarily print the file I received from Finder in the excel file on the server
        file_put_contents($projectDir . "/uploads/temp.xlsx", $getFile->getContents() );

        $reader = new Xlsx();
        //Upload the excel file I temporarily printed
        $spreadSheet = $reader->load($projectDir . "/uploads/temp.xlsx");

        //Bring incoming data as an array
        $sheetData = $spreadSheet->getActiveSheet()->toArray(null, true, true, true);
        
        $entityManager = $this->getDoctrine()->getManager(); 
        
        $value = $sheetData[1]; 
        $categoryOne = new Category();
        $categoryOne->setName($value["A"]);
        $categoryOne->setLft(1);
        $categoryOne->setRgt(7);
        $categoryOne->setParent(null);
        $entityManager->persist($categoryOne);
 
        $categoryTwo = new Category();
        $categoryTwo->setName($value["B"]);
        $categoryTwo->setLft(2);
        $categoryTwo->setRgt(6);
        $categoryTwo->setParent($categoryOne);
        $entityManager->persist($categoryTwo);
 
        $categoryThree = new Category();
        $categoryThree->setName($value["C"]);
        $categoryThree->setLft(3);
        $categoryThree->setRgt(5);
        $categoryThree->setParent($categoryTwo);
        $entityManager->persist($categoryThree);
 
        $categoryFour = new Category();
        $categoryFour->setName($value["D"]);
        $categoryFour->setLft(4);
        $categoryFour->setRgt(4);
        $categoryFour->setParent($categoryThree);
        $entityManager->persist($categoryFour);
 
        $categoryFive = new Category();
        $categoryFive->setName($value["E"]);
        $categoryFive->setLft(5);
        $categoryFive->setRgt(3);
        $categoryFive->setParent($categoryFour);
        $entityManager->persist($categoryFive);
 
        $categorySix = new Category();
        $categorySix->setName($value["F"]);
        $categorySix->setLft(6);
        $categorySix->setRgt(2);
        $categorySix->setParent($categoryFive);
        $entityManager->persist($categorySix);

        $entityManager->flush();  

        // $message = new FileUploadHook();
        // $messageBus->dispatch($message);  

        return $this->sendMail($mailer);
    }

    // Two string beetwen
    function getStringBeetwen($string, $start, $end)
    {
        $string = " " . $string;
        $ini = strpos($string, $start);

        if ($ini == 0) return "";
        $ini += strlen($start);

        $len = strpos($string, $end, $ini) - $ini;

        return substr($string, $ini, $len);
    }

    //Send Mail
    function sendMail($mailer)
    {
        $message = (new \Swift_Message("File Upload Success"))
        ->setFrom("") //From who
        ->setTo("") //To whom to go
        ->setBody(
            $this->renderView( 
                "emails/file-upload-hook-email.html.twig",
                ["name" => "Bilal Ceylan"]
            ),
            "text/html"
        );

        //Send Mail
        $mailer->send($message); 

        $response = new JsonResponse([
            "message" => "Mail send success"
        ]);
        return $response;
    }
}
