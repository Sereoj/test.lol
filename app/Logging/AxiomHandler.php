<?php

namespace App\Logging;

use Monolog\Handler\AbstractProcessingHandler;
use Monolog\LogRecord;
use Monolog\Level;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;

class AxiomHandler extends AbstractProcessingHandler
{
    protected string $apiToken;
    protected string $dataset;
    protected Client $client;
    protected array $buffer = [];
    protected int $bufferLimit = 10;

    public function __construct(
        string $apiToken,
        string $dataset,
        int|string|Level $level = Level::Debug,
        bool $bubble = true
    ) {
        parent::__construct($level, $bubble);

        $this->apiToken = $apiToken;
        $this->dataset = $dataset;
        $this->client = new Client([
            'base_uri' => 'https://api.axiom.co',
            'timeout' => 5.0,
            'headers' => [
                'Authorization' => 'Bearer ' . $this->apiToken,
                'Content-Type' => 'application/json',
            ],
        ]);
    }

    protected function write(LogRecord $record): void
    {
        $this->buffer[] = [
            '_time' => $record->datetime->format('c'),
            'level' => $record->level->getName(),
            'message' => $record->message,
            'context' => $record->context,
            'extra' => $record->extra,
            'channel' => $record->channel,
        ];

        if (count($this->buffer) >= $this->bufferLimit) {
            $this->flush();
        }
    }

    public function flush(): void
    {
        if (empty($this->buffer)) {
            return;
        }

        try {
            $this->client->post("/v1/datasets/{$this->dataset}/ingest", [
                'json' => $this->buffer,
            ]);

            $this->buffer = [];
        } catch (GuzzleException $e) {
            // Silent fail - we don't want logging to break the application
            // You could log this error to a fallback file if needed
        }
    }

    public function __destruct()
    {
        $this->flush();
    }
}
