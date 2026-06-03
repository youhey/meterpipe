@php
    $pollingInterval = $this->getPollingInterval();
@endphp

<x-filament-widgets::widget
    :attributes="
        (new \Illuminate\View\ComponentAttributeBag)
            ->merge([
                'wire:poll.' . $pollingInterval => $pollingInterval ? true : null,
            ], escape: false)
    "
>
    <x-filament::section heading="Sync Status">
        <div class="-mx-6 overflow-x-auto">
            <table class="min-w-full table-fixed divide-y divide-gray-200 text-sm dark:divide-white/10">
                <colgroup>
                    <col class="w-40">
                    <col class="w-28">
                    <col class="w-32">
                    <col class="w-28">
                    <col class="w-44">
                    <col class="w-24">
                    <col class="w-24">
                    <col>
                </colgroup>
                <thead class="bg-gray-50 dark:bg-white/5">
                    <tr class="text-left text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">
                        <th class="whitespace-nowrap px-6 py-3">Provider</th>
                        <th class="whitespace-nowrap px-4 py-3">Enabled</th>
                        <th class="whitespace-nowrap px-4 py-3">Status</th>
                        <th class="whitespace-nowrap px-4 py-3">Freshness</th>
                        <th class="whitespace-nowrap px-4 py-3">Last synced</th>
                        <th class="whitespace-nowrap px-4 py-3 text-right">Fetched</th>
                        <th class="whitespace-nowrap px-4 py-3 text-right">Saved</th>
                        <th class="px-4 py-3">Error</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 bg-white dark:divide-white/10 dark:bg-transparent">
                    @foreach ($this->getRows() as $row)
                        <tr class="transition hover:bg-gray-50 dark:hover:bg-white/5">
                            <td class="whitespace-nowrap px-6 py-3 font-medium text-gray-950 dark:text-white">
                                <x-filament::badge>{{ $row['provider'] }}</x-filament::badge>
                            </td>
                            <td class="whitespace-nowrap px-4 py-3">
                                <x-filament::badge color="{{ $row['enabled'] ? 'success' : 'gray' }}">
                                    {{ $row['enabled'] ? 'enabled' : 'disabled' }}
                                </x-filament::badge>
                            </td>
                            <td class="whitespace-nowrap px-4 py-3">
                                <x-filament::badge>{{ $row['status'] }}</x-filament::badge>
                            </td>
                            <td class="whitespace-nowrap px-4 py-3">
                                <x-filament::badge color="{{ $row['freshness_color'] }}">
                                    {{ $row['freshness'] }}
                                </x-filament::badge>
                            </td>
                            <td class="whitespace-nowrap px-4 py-3 font-mono text-xs text-gray-700 dark:text-gray-300">
                                {{ $row['last_synced_at'] ? \Carbon\CarbonImmutable::parse($row['last_synced_at'])->toDateTimeString() : '-' }}
                            </td>
                            <td class="whitespace-nowrap px-4 py-3 text-right font-mono text-xs tabular-nums text-gray-700 dark:text-gray-300">{{ number_format($row['records_fetched']) }}</td>
                            <td class="whitespace-nowrap px-4 py-3 text-right font-mono text-xs tabular-nums text-gray-700 dark:text-gray-300">{{ number_format($row['records_saved']) }}</td>
                            <td class="truncate px-4 py-3 text-gray-600 dark:text-gray-400" title="{{ $row['error_message'] ?? '-' }}">{{ $row['error_message'] ?? '-' }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </x-filament::section>
</x-filament-widgets::widget>
