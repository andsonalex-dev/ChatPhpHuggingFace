<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

final class ChatController extends AbstractController
{
    private HttpClientInterface $httpClient;

    public function __construct(HttpClientInterface $httpClient)
    {
        $this->httpClient = $httpClient;
    }

    #[Route('/chat', name: 'chat', methods: ['POST'])]
    public function chat(Request $request): JsonResponse
    {
        $apiKey = $this->getParameter('huggingface_api_key');
        $data = json_decode($request->getContent(), true);
        $message = $data['message'] ?? '';

        if (!$message) {
            return new JsonResponse(['error' => 'Mensagem vazia!'], 400);
        }

        $response = $this->httpClient->request('POST', 'https://api-inference.huggingface.co/models/distilgpt2', [
            'headers' => [
                'Authorization' => 'Bearer ' . $apiKey,
                'Content-Type' => 'application/json',
            ],
            'json' => ['inputs' => $message],
            'timeout' => 30,
        ]);

        $result = $response->toArray();
        $reply = $result[0]['generated_text'] ?? 'Erro ao obter resposta.';

        return new JsonResponse(['message' => $reply]);
    }
}
