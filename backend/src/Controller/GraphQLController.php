<?php

declare(strict_types=1);

namespace App\Controller;

use GraphQL\Error\DebugFlag;
use GraphQL\GraphQL;
use GraphQL\Type\Schema;
use JsonException;
use RuntimeException;
use Throwable;

final class GraphQLController
{
    public function __construct(
        private readonly Schema $schema,
        private readonly bool $debug
    ) {
    }

    public function handle(): string
    {
        try {
            $rawInput = file_get_contents('php://input');

            if ($rawInput === false) {
                throw new RuntimeException('The request body could not be read.');
            }

            $input = json_decode($rawInput, true, 512, JSON_THROW_ON_ERROR);
            $query = $input['query'] ?? null;

            if (!is_string($query) || trim($query) === '') {
                throw new RuntimeException('A GraphQL query is required.');
            }

            $variables = $input['variables'] ?? null;
            $operationName = $input['operationName'] ?? null;
            $result = GraphQL::executeQuery(
                $this->schema,
                $query,
                null,
                null,
                is_array($variables) ? $variables : null,
                is_string($operationName) ? $operationName : null
            );
            $debugFlags = $this->debug
                ? DebugFlag::INCLUDE_DEBUG_MESSAGE | DebugFlag::INCLUDE_TRACE
                : DebugFlag::NONE;

            return json_encode($result->toArray($debugFlags), JSON_THROW_ON_ERROR);
        } catch (JsonException $error) {
            http_response_code(400);

            return $this->errorResponse('The request body must contain valid JSON.');
        } catch (Throwable $error) {
            http_response_code(400);

            return $this->errorResponse(
                $this->debug ? $error->getMessage() : 'The GraphQL request is invalid.'
            );
        }
    }

    private function errorResponse(string $message): string
    {
        return json_encode(
            ['errors' => [['message' => $message]]],
            JSON_THROW_ON_ERROR
        );
    }
}
