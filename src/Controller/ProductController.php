<?php

namespace App\Controller;

use App\Message\Command\ReceiveProductCommand;
use App\Message\Cqrs\CommandBus;
use App\Message\Cqrs\QueryBus;
use App\Message\Query\GetProductsQuery;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ProductController extends AbstractController
{
    public function __construct(private CommandBus $commandBus, private QueryBus $queryBus){}

    #[Route('/products/{sku}/receive', name: 'receiveProduct', methods: 'PATCH')]
    public function receiveProduct(string $sku, Request $request): Response
    {
        $data = json_decode($request->getContent(), true);
        $receiveProductCommand = new ReceiveProductCommand($sku,$data['quantity']);
        $this->commandBus->dispatch($receiveProductCommand);
        return $this->json([
            'message' => 'success',
        ], Response::HTTP_ACCEPTED);
    }

    #[Route('/products', name: 'products', methods: 'GET')]
    public function getProducts(): Response
    {
        $productStates = $this->queryBus->handle(new GetProductsQuery());
        foreach ($productStates as $sku=>$productState){
            $result[] = [
                'sku'=>$sku,
                'firstAddedAt'=>date_format($productState->firstAddedAt, 'Y-m-d H:i:s'),
                'lastUpdatedAt'=>date_format($productState->lastUpdatedAt, 'Y-m-d H:i:s'),
                'quantity'=>$productState->quantity,
                ];
        }
        $response = $this->json($result);
        $response->headers->set('Content-Type', 'application/json');
        $response->headers->set('Access-Control-Allow-Origin', '*');
        return $response;

    }
}
