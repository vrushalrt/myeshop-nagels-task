<?php

namespace App\Services;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Serializer\Encoder\CsvEncoder;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;

class CsvExporter
{
    /**
     * @param $data
     * @param $filename
     * @return Response
     */
    public function export($data, $filename): Response
    {
        $serializer = new Serializer([new ObjectNormalizer()], [new CsvEncoder()]);
        $context = [CsvEncoder::DELIMITER_KEY => ';'];
        $response = new Response($serializer->encode(mb_convert_encoding($data,'UTF-8'), CsvEncoder::FORMAT,$context));
        $response->headers->set('Content-Type', 'text/csv; charset=UTF-8');
        $response->headers->set('Content-Disposition', "attachment; filename=\"$filename\"");

        return $response;
    }

}