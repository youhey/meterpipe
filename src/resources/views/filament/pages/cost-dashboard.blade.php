<x-filament-panels::page>
    @unless (app(\App\Services\CostSummaryService::class)->hasCostData())
        <div class="rounded-lg border border-gray-200 bg-white p-4 text-sm text-gray-700 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-200">
            まだコストデータが同期されていません。手動同期を実行するか、定期同期を待ってください。
        </div>
    @endunless
</x-filament-panels::page>
