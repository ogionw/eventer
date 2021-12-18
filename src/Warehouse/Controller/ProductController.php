<?php

namespace App\Warehouse\Controller;

use App\Warehouse\Message\Command\ReceiveProductCommand;
use App\Warehouse\Message\Cqrs\CommandBus;
use App\Warehouse\Message\Cqrs\QueryBus;
use App\Warehouse\Message\Query\GetProductsQuery;
use App\Warehouse\Serializer\ProductSerializerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\GetSetMethodNormalizer;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;

class ProductController extends AbstractController
{
    public function __construct(private CommandBus $commandBus, private QueryBus $queryBus, private ProductSerializerInterface $serializer){}

    #[Route('/products/{sku}/receive', name: 'receiveProduct', methods: 'POST')]
    public function receiveProduct(string $sku, Request $request): Response
    {
        $receiveProductCommand = $this->serializer->deserialize($request, $sku);
        $this->commandBus->dispatch($receiveProductCommand);
        return $this->json([
            'message' => 'success',
        ], Response::HTTP_ACCEPTED);
    }

    #[Route('/products', name: 'products', methods: 'GET')]
    public function getProducts(): Response
    {
        $productStates = $this->queryBus->handle(new GetProductsQuery());
        $products = [];
        foreach ($productStates as $sku=>$productState){
            $products[] = [
                'sku'=>$sku,
                'firstAddedAt'=>date_format($productState->firstAddedAt, 'Y-m-d H:i:s'),
                'lastUpdatedAt'=>date_format($productState->lastUpdatedAt, 'Y-m-d H:i:s'),
                'quantity'=>$productState->quantity,
                ];
        }
//        $response = $this->json($result);
//        $response->headers->set('Content-Type', 'application/json');
//        $response->headers->set('Access-Control-Allow-Origin', '*');
//        return $response;
        return $this->render('product/index.html.twig', ['products' => $products]);

    }
}
