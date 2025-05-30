<x-filament-panels::page>
    <x-filament-panels::header :heading="$this->getTitle()">
        <x-slot name="description">
            Jawablah soal berikut dengan benar
        </x-slot>
    </x-filament-panels::header>

    <div class="space-y-6">
        <div class="p-6 bg-white rounded-xl shadow">
            {{ $this->form }}
        </div>

        @if(!$submission)
            <div>
                <x-filament::button
                    wire:click="submit"
                    type="button"
                    color="primary"
                    icon="heroicon-m-check"
                    class="w-full justify-center py-3 text-lg"
                    wire:loading.attr="disabled"
                >
                    <span wire:loading.remove>Kirim Jawaban</span>
                    <span wire:loading>Menyimpan...</span>
                </x-filament::button>
            </div>
        @else
            <div class="p-6 rounded-lg {{ $this->isCorrectAnswer() ? 'bg-green-100 border border-green-200' : 'bg-red-100 border border-red-200' }}">
                <h3 class="text-lg font-bold {{ $this->isCorrectAnswer() ? 'text-green-700' : 'text-red-700' }}">
                    @if($this->isCorrectAnswer())
                        <x-heroicon-o-check-circle class="inline-block w-6 h-6 mr-1" />
                        Jawaban Benar!
                    @else
                        <x-heroicon-o-x-circle class="inline-block w-6 h-6 mr-1" />
                        Jawaban Salah!
                    @endif
                </h3>
                
                @if(!$this->isCorrectAnswer())
                    <div class="mt-2 text-red-700">
                        <p class="font-medium">Jawaban yang benar adalah:</p>
                        <ul class="list-disc list-inside mt-1">
                            @foreach($this->getCorrectOptions() as $option)
                                <li>{{ $option->option_text }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif
            </div>
        @endif
    </div>
</x-filament-panels::page>