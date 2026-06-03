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
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-gray-200 text-left text-xs font-medium uppercase tracking-wide text-gray-500 dark:border-gray-700 dark:text-gray-400">
                        <th class="whitespace-nowrap px-3 py-2">provider</th>
                        <th class="whitespace-nowrap px-3 py-2">enabled</th>
                        <th class="whitespace-nowrap px-3 py-2">status</th>
                        <th class="whitespace-nowrap px-3 py-2">freshness</th>
                        <th class="whitespace-nowrap px-3 py-2">last synced</th>
                        <th class="whitespace-nowrap px-3 py-2 text-right">fetched</th>
                        <th class="whitespace-nowrap px-3 py-2 text-right">saved</th>
                        <th class="px-3 py-2">error</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($this->getRows() as $row)
                        <tr class="border-b border-gray-100 last:border-0 dark:border-gray-800">
                            <td class="whitespace-nowrap px-3 py-2">
                                <x-filament::badge>{{ $row['provider'] }}</x-filament::badge>
                            </td>
                            <td class="whitespace-nowrap px-3 py-2">
                                <x-filament::badge color="{{ $row['enabled'] ? 'success' : 'gray' }}">
                                    {{ $row['enabled'] ? 'enabled' : 'disabled' }}
                                </x-filament::badge>
                            </td>
                            <td class="whitespace-nowrap px-3 py-2">
                                <x-filament::badge>{{ $row['status'] }}</x-filament::badge>
                            </td>
                            <td class="whitespace-nowrap px-3 py-2">
                                <x-filament::badge color="{{ $row['freshness_color'] }}">
                                    {{ $row['freshness'] }}
                                </x-filament::badge>
                            </td>
                            <td class="whitespace-nowrap px-3 py-2">
                                {{ $row['last_synced_at'] ? \Carbon\CarbonImmutable::parse($row['last_synced_at'])->toDateTimeString() : '-' }}
                            </td>
                            <td class="whitespace-nowrap px-3 py-2 text-right">{{ number_format($row['records_fetched']) }}</td>
                            <td class="whitespace-nowrap px-3 py-2 text-right">{{ number_format($row['records_saved']) }}</td>
                            <td class="max-w-xs truncate px-3 py-2">{{ $row['error_message'] ?? '-' }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </x-filament::section>
</x-filament-widgets::widget>
