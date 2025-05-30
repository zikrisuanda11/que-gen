<x-filament-panels::page>
    <x-filament-panels::header :heading="$this->getTitle()">
        <x-slot name="description">
            Soal-soal ini dapat dikerjakan secara berurutan melalui tombol Mulai Kerjakan Quiz
        </x-slot>
        <x-slot name="actions">
            <x-filament::button
                color="success"
                icon="heroicon-m-play"
                :href="route('filament.teacher.resources.student-quizzes.quiz-session', $record)"
            >
                Mulai Kerjakan Quiz
            </x-filament::button>

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
