<?php

namespace App\Services;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Serializer\Encoder\CsvEncoder;
use Symfony\Component\Serializer\SerializerInterface;

class CsvExporter
{

    public function __construct(
        private SerializerInterface $serializer,
    )
    {
    }

    /**
     * @param array $data
     * @param string $filename
     * @return Response
     */
    public function export(array $data, string $filename): Response
    {
        $context = [CsvEncoder::DELIMITER_KEY => ';'];
        $csvContent = $this->serializer->encode(mb_convert_encoding($data, 'UTF-8'), CsvEncoder::FORMAT, $context);
        $response = new Response($csvContent);
        $response->headers->set('Content-Type', 'text/csv; charset=UTF-8');
        $response->headers->set('Content-Disposition', "attachment; filename=\"$filename\"");

        return $response;
    }
}