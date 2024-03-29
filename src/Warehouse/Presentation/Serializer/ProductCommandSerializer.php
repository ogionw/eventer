<?php
declare(strict_types=1);

namespace App\Warehouse\Presentation\Serializer;
use App\Warehouse\Presentation\Message\Command\ProductCommand;
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

    public function deserialize(Request $request, string $sku, string $type) : ProductCommand
    {
        $raw = $request->getContent();
        $post = json_decode($raw, true);
        if(! $post){
            parse_str($raw, $post);
            $post['quantity'] = (int)$post['quantity'];
        }
        if(! isset($post['sku'])) {
            $post['sku'] = $sku;
        }
        return $this->serializer->deserialize(
            json_encode($post),
            $type,
            'json'
        );
    }
}
