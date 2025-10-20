<?php

declare(strict_types=1);

namespace Ameax\FieldkitCore\Jobs;

use Ameax\FieldkitCore\Contracts\FieldKitMappingHandlerInterface;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Collection;

class ProcessFieldKitMapping implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries;
    public int $backoff;

    public function __construct(
        protected string $handlerClass,
        protected Model $model,
        protected Collection $mappings,
        protected array $formData
    ) {
        $this->tries = config('fieldkit.handlers.retry_attempts', 3);
        $this->backoff = config('fieldkit.handlers.retry_delay', 60);
        
        // Use configured queue connection and name
        $this->onConnection(config('fieldkit.handlers.queue_connection', 'default'));
        $this->onQueue(config('fieldkit.handlers.queue_name', 'default'));
    }

    public function handle(): void
    {
        if (!class_exists($this->handlerClass)) {
            throw new \Exception("Handler class not found: {$this->handlerClass}");
        }

        $handler = app($this->handlerClass);

        if (!$handler instanceof FieldKitMappingHandlerInterface) {
            throw new \Exception("Handler must implement FieldKitMappingHandlerInterface: {$this->handlerClass}");
        }

        $handler->handle($this->model, $this->mappings, $this->formData);
    }

    public function failed(\Throwable $exception): void
    {
        // Log the failure
        logger()->error('FieldKit mapping processing failed', [
            'handler' => $this->handlerClass,
            'model' => get_class($this->model),
            'model_id' => $this->model->getKey(),
            'mappings_count' => $this->mappings->count(),
            'error' => $exception->getMessage(),
            'trace' => $exception->getTraceAsString(),
        ]);
    }
}