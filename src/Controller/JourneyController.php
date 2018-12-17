<?php

namespace App\Controller;

use Symfony\Component\Serializer\Encoder;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;

use App\Entity\Poll;

class JourneyController extends AbstractController
{
    /**
     * @Route("/journey", name="journey")
     */
    public function index()
    {
        $data['polls'] = $this->getDoctrine()
            ->getRepository(Poll::class)
            ->findAll();



        return $this->render('journey/index.html.twig', [
            'controller_name' => 'JourneyController',
            'data' => $data
        ]);
    }

    /**
     * @Route("/vote/{id}", name="vote", methods={"GET"}, options={"expose"=true}, requirements={"id"="\d+"})
     * @param Request $request
     * @param int $id
     */
    public function vote(Request $request, int $id) {
        if (!$request->isXmlHttpRequest())
            return new JsonResponse([
               'type'       => 'error',
               'message'    => 'AJAX Only'
            ]);

        //all is good, we have a valid AJAX request, so move forward
        //get the poll object
        $em = $this->getDoctrine()->getManager();

        $poll = $em->getRepository(Poll::class)->findOneBy([
            'id' => $id,
        ]);


        if ($poll) {

            //update the vote field
            $current_votes = $poll->getVotes();
            //
            $poll->setVotes($current_votes+1);
            //
            $em->persist($poll);
            //
            $em->flush();

            //try sending to Mercure
            //$this->sendToMercure($poll);
            //

            $encoders = [
                new Encoder\JsonEncoder(),
            ];

            $normalizers = [
                new ObjectNormalizer(),
            ];
            $serializer = new Serializer($normalizers,$encoders);

            $data = $serializer->serialize($poll, 'json');

            //dump($data); die;

            return new JsonResponse($data, 200, [], true);
        }
    }

    public function sendToMercure(Poll $poll) {

        $query_url = 'http://localhost:8000/api/polls/'.$poll->getId();
        $publish_url = getenv('MERCURE_PUBLISH_URL');
        $secret = getenv('MERCURE_JWT_SECRET');

        if (is_null($publish_url) || is_null($secret))
            return false;

        $postData = http_build_query([
            'topic' => $query_url,
            'data' => json_encode(['id' => $poll->getId(), 'votes' => $poll->getVotes()]),
        ]);

        //dump($postData); die;

        $tt = file_get_contents($publish_url, false, stream_context_create(['http' => [
            'method'  => 'POST',
            'header'  => "Content-type: application/x-www-form-urlencoded\r\nAuthorization: Bearer ".$secret,
            'content' => $postData,
        ]]));

    }
}
