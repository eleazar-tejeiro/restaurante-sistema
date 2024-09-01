<x-filament-panels::page>
    <x-filament::tabs color="danger" label="Content tabs">
        <x-filament::tabs.item 
            :active="$selectedTab === 'all'"
            icon='heroicon-m-wallet'
            wire:click="selectTab('all')">
            Todos
        </x-filament::tabs.item>

        <x-filament::tabs.item 
            :active="$selectedTab === 'daily'"
            icon="heroicon-m-calendar" 
            wire:click="selectTab('daily')">
            Por día
        </x-filament::tabs.item>

        <x-filament::tabs.item 
            :active="$selectedTab === 'monthly'"
            icon='heroicon-m-calendar-days'
            wire:click="selectTab('monthly')">
            Por mes
        </x-filament::tabs.item>
    </x-filament::tabs>

    @if ($selectedTab === 'all')
        @livewire('reports.sale-all')
    @elseif ($selectedTab === 'daily')
        @livewire('reports.sale-today')
    @elseif ($selectedTab === 'monthly')
        @livewire('reports.sale-monthly')
    @endif
</x-filament-panels::page>