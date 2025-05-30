<x-filament-panels::page>
    <x-filament-panels::header :heading="$this->getTitle()">
        <x-slot name="actions">
            <x-filament::button
                color="gray"
                icon="heroicon-m-arrow-left"
                :href="route('filament.teacher.resources.student-quizzes.index')"
            >
                Kembali
            </x-filament::button>
        </x-slot>
    </x-filament-panels::header>

    {{ $this->table }}
</x-filament-panels::page>