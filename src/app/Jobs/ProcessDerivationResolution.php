<?php

namespace App\Jobs;

use App\Repositories\LexicalEntryDerivationRepository;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

/**
 * Second pass of the Eldamo import: resolves `parent_external_id` on derivation rows to
 * actual lexical entry IDs. Dispatched last on the import queue so all entries exist
 * by the time it runs.
 */
class ProcessDerivationResolution implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(private readonly ?array $lexicalEntryGroupIds = null) {}

    public function handle(LexicalEntryDerivationRepository $lexicalEntryDerivationRepository): void
    {
        $resolved = $lexicalEntryDerivationRepository->resolveParentReferences($this->lexicalEntryGroupIds);
        Log::info(sprintf('ProcessDerivationResolution: resolved %d derivation parent references.', $resolved));
    }
}
