<?php

require __DIR__ . '/vendor/autoload.php';

$dotenv = \Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

print_r(ask('我想知道台北的天氣如何？'));

function ask(string $question): array {
    $client = OpenAI::client($_ENV['OPENAI_API_KEY']);
    $response = $client->chat()->create([
        'model' => 'gpt-3.5-turbo-0613',
        'temperature' => 1,
        'messages' => [
            [
                'role' => 'user',
                'content' => $question,
            ],
            [
                'role' => 'assistant',
                'content' => null,
                'function_call' => [
                    'name' => 'get_current_weather',
                    'arguments' => json_encode([
                        'location' => 'Taipei',
                    ]),
                ],
            ],
            [
                'role' => 'function',
                'name' => 'get_current_weather',
                'content' => json_encode([
                    'temperature' => 22,
                    'unit' => 'celsius',
                    'description' => 'Sunny',
                ]),
            ],
        ],
        'functions' => [
            [
                'name' => 'get_current_weather',
                'description' => 'Get the current weather in a given location.',
                'parameters' => [
                    'type' => 'object',
                    'properties' => [
                        'location' => [
                            'type' => 'string',
                            'description' => 'The city and state, e.g. San Francisco, CA',
                        ],
                        'unit' => [
                            'type' => 'string',
                            'enum' => ['celsius', 'fahrenheit'],
                        ],
                    ],
                    'required' => [
                        'location',
                    ],
                ],
            ],
        ],
    ]);

    return $response['choices'][0]['message'];
}
