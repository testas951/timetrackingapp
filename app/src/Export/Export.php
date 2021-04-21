<?php


namespace App\Export;


use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\HeaderUtils;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

class Export extends AbstractController
{
    public function exportToCSV($persistentObject, $range, $userId): Response
    {
        $query = $this->getDoctrine()->getRepository($persistentObject)->createQueryBuilder("q");

        $query
            ->andWhere('q.date BETWEEN :from AND :to')
            ->andWhere('q.userId = :userId')
            ->setParameter('from', $range['date_from'] )
            ->setParameter('to', $range['date_to'])
            ->setParameter('userId', $userId)
            ->orderBy('q.date');
        $data = $query->getQuery()->getArrayResult();

        $filename = 'Times_' . $range['date_from']->format('Y-m-d') . '-' . $range['date_to']->format('Y-m-d') . '.csv';

        $response = new StreamedResponse(function() use ($data) {
            $outputBuffer = fopen("php://output", 'w');

            $titles = array(
              'Title',
              'Comment',
              'Date',
              'Time',
            );
            fputcsv($outputBuffer, $titles, ';');
            $countSum = 0;
            foreach($data as $v) {
                unset($v['id']);
                unset($v['userId']);
                $v['date'] = $v['date']->format('Y-m-d');
                $countSum += $v['time'];
                fputcsv($outputBuffer, $v, ';');
            }
            $lastLine = array(
                '',
                '',
                'Total time:',
                $countSum,
            );
            fputcsv($outputBuffer, $lastLine, ';');
            fclose($outputBuffer);
        });

        $response->headers->set('Content-Type', 'text/csv; charset=utf-8');
        $disposition = HeaderUtils::makeDisposition(
            HeaderUtils::DISPOSITION_ATTACHMENT,
            $filename
        );

        $response->headers->set('Content-Disposition', $disposition);

        return $response;
    }
}