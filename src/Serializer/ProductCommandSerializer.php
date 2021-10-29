<?php
declare(strict_types=1);

namespace App\Serializer;
use App\Message\Command\ReceiveProductCommand;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;

final class ProductCommandSerializer implements ProductSerializerInterface
{
    private Serializer $serializer;

    public function __construct(ObjectNormalizer $normalizer)
    {
        $this->serializer = new Serializer([$normalizer], [new JsonEncoder()]);
    }

    public function deserialize(Request $request, string $sku){
        $a = json_decode($request->getContent(),true);
        $a["sku"] = $sku;
        return $this->serializer->deserialize(
            json_encode($a),
            ReceiveProductCommand::class,
            'json'
        );
    }
}
