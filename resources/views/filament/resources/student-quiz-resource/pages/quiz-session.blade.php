<x-filament-panels::page :heading="false">
    <div class="mb-6">
        <p class="mt-2 text-sm text-gray-500">
            @if(!$this->isQuizSubmitted)
                <span wire:key="question-form-{{ $this->currentQuestionId }}">
                    <span class="font-medium text-primary-600">Soal {{ $this->allQuestions->search(function($q) { return $q->id === $this->currentQuestionId; }) + 1 }} dari {{ $this->getTotalQuestions() }}</span> •
                    <span>Terjawab: {{ $this->getAnsweredCount() }} dari {{ $this->getTotalQuestions() }}</span>
                    @if($this->getAnsweredCount() == $this->getTotalQuestions())
                        <span class="ml-2 px-2 py-0.5 bg-success-100 text-success-700 text-xs font-medium rounded-full">Semua Terjawab</span>
                    @endif
                    <span id="save-status" class="ml-2 text-xs hidden">
                        <span id="saving" class="text-amber-600 flex items-center">
                            <span class="inline-block h-2 w-2 bg-amber-600 rounded-full animate-pulse mr-1"></span>
                            Menyimpan...
                        </span>
                        <span id="saved" class="text-success-600 hidden flex items-center">
                            <span class="inline-block h-2 w-2 bg-success-600 rounded-full mr-1"></span>
                            Tersimpan
                        </span>
                    </span>
                </span>
            @else
                <span class="font-bold text-2xl text-success-600">Quiz telah selesai</span>
            @endif
        </p>
    </div>

    <div class="flex flex-col md:flex-row gap-6 mb-6 mt-6">
        <!-- Main content - question and answers -->
        <div class="flex-1 space-y-6">
            @if(!$this->isQuizSubmitted)
                <!-- Current question form -->
                <div class="p-6 bg-white rounded-xl shadow border-l-4 border-primary-500">
                    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between mb-4 gap-2">
                        <h2 class="text-xl font-bold">{{ $this->currentQuestion->title }}</h2>
                        <span class="bg-primary-100 text-primary-800 text-sm font-medium px-3 py-1 rounded-full inline-flex items-center justify-center whitespace-nowrap">
                            Soal {{ $this->allQuestions->search(function($q) { return $q->id === $this->currentQuestionId; }) + 1 }}
                        </span>
                    </div>

                    <div class="prose prose-base max-w-none mb-6 text-gray-800 prose-img:rounded-lg prose-img:mx-auto prose-headings:text-primary-600 prose-p:my-2 prose-ul:my-2 prose-ol:my-2 prose-pre:bg-gray-100 prose-pre:p-2 prose-pre:rounded prose-table:border prose-table:border-collapse prose-td:border prose-td:p-2 prose-th:border prose-th:p-2 prose-th:bg-gray-100">
                        {!! $this->currentQuestion->content !!}
                    </div>

                    <div class="space-y-4" wire:key="options-{{ $this->currentQuestionId }}">
                        @foreach($this->currentQuestion->options as $option)
                            <label
                                class="block p-4 border-2 rounded-lg cursor-pointer transition-colors
                                {{ isset($this->selectedAnswers[$this->currentQuestionId]) && $this->selectedAnswers[$this->currentQuestionId] == $option->id ? 'border-primary-500 bg-primary-50 shadow-sm' : 'border-gray-200 hover:border-primary-200 hover:bg-gray-50' }}"
                                wire:key="option-{{ $option->id }}"
                            >
                                <div class="flex items-center  gap-2">
                                    <div class="flex-shrink-0 mt-0.5">
                                        <input
                                            type="radio"
                                            name="option_{{ $this->currentQuestionId }}"
                                            value="{{ $option->id }}"
                                            class="h-5 w-5 text-primary-600 border-gray-300 focus:ring-primary-500 focus:ring-offset-1"
                                            wire:model.live="selectedAnswers.{{ $this->currentQuestionId }}"
                                        >
                                    </div>
                                    <div class="ml-3 prose prose-sm">
                                        <div class="text-gray-900 md:text-base">{!! $option->option_text !!}</div>
                                    </div>
                                </div>
                            </label>
                        @endforeach
                    </div>

                    <!-- Navigation buttons -->
                    <div class="flex flex-col sm:flex-row justify-between mt-6 pt-6 border-t border-gray-200 gap-4">
                        <x-filament::button
                            wire:click="navigateToPreviousQuestion()"
                            type="button"
                            color="gray"
                            icon="heroicon-m-arrow-left"
                            :disabled="$this->allQuestions->search(function($q) { return $q->id === $this->currentQuestionId; }) === 0"
                            class="justify-center sm:justify-start"
                        >
                            Soal Sebelumnya
                        </x-filament::button>

                        <div class="flex gap-3 sm:justify-end justify-between">

                            <x-filament::button
                                wire:click="navigateToNextQuestion()"
                                type="button"
                                color="gray"
                                icon-right="heroicon-m-arrow-right"
                                :disabled="$this->allQuestions->search(function($q) { return $q->id === $this->currentQuestionId; }) === $this->allQuestions->count() - 1"
                                class="flex-grow sm:flex-grow-0"
                            >
                                Soal Berikutnya
                            </x-filament::button>
                        </div>
                    </div>
                </div>

                <!-- Save info and submit -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div class="p-5 bg-white rounded-lg shadow">
                        <div class="p-3 bg-blue-50 rounded-lg mb-2">
                                    <div class="flex items-center text-blue-700 mb-2">
                                        <x-heroicon-s-information-circle class="w-5 h-5 mr-2 flex-shrink-0" />
                                        <span class="text-sm font-medium">Jawaban Anda otomatis tersimpan</span>
                                    </div>
                                    <p class="text-xs text-blue-600">Setiap kali Anda memilih jawaban atau berpindah soal, jawaban akan otomatis disimpan. Anda dapat melanjutkan quiz ini kapan saja.</p>
                                    <div class="flex items-center text-xs mt-2 pt-2 border-t border-blue-200">
                                        <div id="save-indicator" class="flex items-center">
                                            <span id="save-idle" class="text-blue-700">
                                                <span class="inline-block h-2 w-2 bg-blue-500 rounded-full"></span>
                                                Siap menyimpan jawaban Anda
                                            </span>
                                        </div>
                                    </div>
                                </div>
                    </div>
                    <div class="p-5 bg-white rounded-lg shadow">
                        <div class="flex items-center justify-between gap-2 p-4">
                            <div class="text-sm font-medium text-gray-700">
                                Progress quiz Anda:
                            </div>
                            <span class="text-sm font-medium text-gray-700 whitespace-nowrap">
                                {{ $this->getAnsweredCount() }}/{{ $this->getTotalQuestions() }}
                            </span>
                        </div>

                        <x-filament::button
                            wire:click="submitQuiz()"
                            type="button"
                            color="success"
                            icon="heroicon-m-check-circle"
                            class="w-full justify-center py-3.5 text-lg font-bold mt-5"
                            :disabled="$this->getAnsweredCount() !== $this->getTotalQuestions()"
                            wire:loading.attr="disabled"
                        >
                            <span wire:loading.remove>Selesaikan Quiz</span>
                            <span wire:loading>Menyimpan...</span>
                        </x-filament::button>
                    </div>
                </div>
            @else
                <!-- Quiz results -->
                <div class="p-6 bg-white rounded-xl shadow">
                    <h2 class="text-xl font-bold mb-4">Hasil Quiz</h2>
                    <div class="space-y-6">
                        @php $correctCount = 0; @endphp
                        @foreach($this->allQuestions as $index => $question)
                            @php
                                $isCorrect = $this->isCorrectAnswer($question->id);
                                if($isCorrect) { $correctCount++; }
                                $selectedOption = isset($this->selectedAnswers[$question->id]) ? App\Models\Option::find($this->selectedAnswers[$question->id]) : null;
                                $correctOption = $this->getCorrectOptionForQuestion($question->id);
                                $cardClass = $isCorrect ? 'border-green-300 shadow-md shadow-green-100' : 'border-red-300 shadow-md shadow-red-100';
                                $headerClass = $isCorrect ? 'bg-green-100' : 'bg-red-100';
                                $statusBadgeClass = $isCorrect ? 'bg-green-50 text-green-700 border border-green-200' : 'bg-red-50 text-red-700 border border-red-200';
                                $statusIcon = $isCorrect ? 'heroicon-s-check-circle' : 'heroicon-s-x-circle';
                                $statusColor = $isCorrect ? 'text-green-600' : 'text-red-600';
                            @endphp

                            <div class="border-2 rounded-xl overflow-hidden {{ $cardClass }}">
                                <!-- Question header with status -->
                                <div class="p-4 flex justify-between items-center {{ $headerClass }} border-b border-gray-200">
                                    <h3 class="font-bold text-gray-800 flex items-center">
                                        <span class="bg-white text-gray-700 rounded-full h-6 w-6 flex items-center justify-center text-sm font-bold mr-3 border">{{ $index + 1 }}</span>
                                        {{ $question->title }}
                                    </h3>
                                    <span class="px-3 py-1 rounded-full text-xs font-medium flex items-center {{ $isCorrect ? 'bg-green-50 text-green-700 border border-green-200' : 'bg-red-50 text-red-700 border border-red-200' }}">
                                        @if($isCorrect)
                                            <x-heroicon-s-check-circle class="w-4 h-4 mr-1 text-green-600" />
                                            Benar
                                        @else
                                            <x-heroicon-s-x-circle class="w-4 h-4 mr-1 text-red-600" />
                                            Salah
                                        @endif
                                    </span>
                                </div>

                                <!-- Question content -->
                                <div class="p-5">
                                    <div class="prose prose-sm max-w-none mb-6 text-gray-700 mx-10" style="margin-left: 0.7rem;">{!! $question->content !!}</div>

                                    <!-- Answer section -->
                                    <div class="mt-4 space-y-4">
                                        <!-- User's answer -->
                                        @if($selectedOption)
                                            <div class="rounded-lg p-3 {{ $isCorrect ? 'bg-green-50' : 'bg-red-50' }}">
                                                <p class="text-xs font-semibold uppercase tracking-wide text-gray-500 mb-1">Jawaban Anda:</p>
                                                <div class="flex items-start">
                                                    <div class="mt-0.5 mr-2 flex-shrink-0">
                                                        @if($isCorrect)
                                                            <span class="flex h-5 w-5 items-center justify-center rounded-full bg-green-100">
                                                                <x-heroicon-s-check class="h-3 w-3 text-green-600" />
                                                            </span>
                                                        @else
                                                            <span class="flex h-5 w-5 items-center justify-center rounded-full bg-red-100">
                                                                <x-heroicon-s-x-mark class="h-3 w-3 text-red-600" />
                                                            </span>
                                                        @endif
                                                    </div>
                                                    <div class="{{ $isCorrect ? 'text-green-700' : 'text-red-700' }} prose prose-sm max-w-none">
                                                        {!! $selectedOption->option_text !!}
                                                    </div>
                                                </div>
                                            </div>
                                        @else
                                            <div class="rounded-lg p-3 bg-gray-50 border border-gray-200">
                                                <p class="text-xs font-semibold uppercase tracking-wide text-gray-500 mb-1">Jawaban Anda:</p>
                                                <div class="text-gray-500 italic">Tidak dijawab</div>
                                            </div>
                                        @endif

                                        <!-- Correct answer (if user's answer is wrong) -->
                                        @if(!$isCorrect && $correctOption)
                                            <div class="rounded-lg p-3 bg-green-50 border border-green-200">
                                                <p class="text-xs font-semibold uppercase tracking-wide text-gray-500 mb-1">Jawaban yang Benar:</p>
                                                <div class="flex items-start">
                                                    <div class="mt-0.5 mr-2 flex-shrink-0">
                                                        <span class="flex h-5 w-5 items-center justify-center rounded-full bg-green-100">
                                                            <x-heroicon-s-check class="h-3 w-3 text-green-600" />
                                                        </span>
                                                    </div>
                                                    <div class="text-green-700 prose prose-sm max-w-none">
                                                        {!! $correctOption->option_text !!}
                                                    </div>
                                                </div>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        @endforeach

                        <div class="mt-8 p-6 bg-white rounded-xl shadow-md border-t-4 {{ ($correctCount / $this->allQuestions->count()) >= 0.6 ? 'border-green-500' : 'border-amber-500' }}">
                            <h3 class="text-lg font-bold mb-4 text-center">Skor Akhir</h3>
                            <div class="flex flex-col items-center">
                                <div class="relative">
                                    <svg class="w-32 h-32" viewBox="0 0 36 36" xmlns="http://www.w3.org/2000/svg">
                                        <!-- Background circle -->
                                        <circle cx="18" cy="18" r="15.9" fill="none" stroke="#e6e6e6" stroke-width="3.8" />
                                        <!-- Progress circle -->
                                        <circle
                                            cx="18" cy="18" r="15.9" fill="none"
                                            stroke="{{ ($correctCount / $this->allQuestions->count()) >= 0.6 ? '#10b981' : '#f59e0b' }}"
                                            stroke-width="3.8"
                                            stroke-dasharray="100"
                                            stroke-dashoffset="{{ 100 - round(($correctCount / $this->allQuestions->count()) * 100) }}"
                                            transform="rotate(-90 18 18)"
                                            style="transition: all 1s ease-in-out"
                                        />
                                    </svg>
                                    <div class="absolute inset-0 flex items-center justify-center">
                                        <div class="text-center">
                                            <span class="text-3xl font-bold {{ ($correctCount / $this->allQuestions->count()) >= 0.6 ? 'text-green-600' : 'text-amber-600' }}">
                                                {{ round(($correctCount / $this->allQuestions->count()) * 100) }}%
                                            </span>
                                        </div>
                                    </div>
                                </div>

                                <div class="mt-4 flex items-center justify-center gap-1">
                                    <span class="text-xl font-bold text-gray-700">{{ $correctCount }} / {{ $this->allQuestions->count() }}</span>
                                    <span class="text-sm text-gray-500">jawaban benar</span>
                                </div>

                                <div class="mt-4 {{ ($correctCount / $this->allQuestions->count()) >= 0.6 ? 'text-green-600' : 'text-amber-600' }} font-medium">
                                    @if(($correctCount / $this->allQuestions->count()) == 1)
                                        Sempurna! Anda menjawab semua pertanyaan dengan benar.
                                    @elseif(($correctCount / $this->allQuestions->count()) >= 0.8)
                                        Sangat bagus! Anda menguasai materi dengan baik.
                                    @elseif(($correctCount / $this->allQuestions->count()) >= 0.6)
                                        Bagus! Anda memahami sebagian besar materi.
                                    @elseif(($correctCount / $this->allQuestions->count()) >= 0.4)
                                        Cukup. Anda perlu belajar lebih giat lagi.
                                    @else
                                        Perlu perbaikan. Jangan menyerah, tetap semangat belajar!
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @endif
        </div>

        <!-- Sidebar - questions list -->
        <div class="md:w-80 lg:w-96">
            <div class="bg-white p-6 rounded-xl shadow sticky top-24">
                <h3 class="text-lg font-semibold mb-4">Navigasi Soal</h3>

                <div class="flex flex-wrap gap-3 justify-start p-2">
                    @foreach($this->allQuestions as $index => $question)
                        @php
                            $status = $this->getQuestionStatus($question->id);
                            $statusClasses = [
                                'answered' => 'bg-primary-100 border-primary-500 text-primary-700',
                                'current' => 'bg-yellow-100 border-yellow-500 text-yellow-800',
                                'unanswered' => 'bg-gray-100 border-gray-300 text-gray-700',
                            ];
                        @endphp
                        <div
                            wire:click="setCurrentQuestion({{ $question->id }})"
                            wire:key="question-nav-{{ $question->id }}"
                            class="flex items-center justify-center
                                   h-10 w-10 rounded-md border-2 cursor-pointer
                                   {{ $statusClasses[$status] }}
                                   hover:border-primary-700 transition-colors font-medium text-sm
                                   {{ $question->id === $this->currentQuestionId ? 'ring-2 ring-primary-500 ring-offset-2' : '' }}"
                            title="{{ $question->title }}"
                        >
                            {{ $index + 1 }}
                        </div>
                    @endforeach
                </div>

                <div class="mt-6 space-y-3 p-5 bg-gray-50 rounded-lg">
                    <div class="text-sm font-medium mb-2">Keterangan:</div>
                    <div class="flex items-center gap-3">
                        <div class="w-5 h-5 rounded-md bg-primary-100 border-2 border-primary-500"></div>
                        <span class="ml-2 text-sm text-gray-700">Sudah dijawab</span>
                    </div>
                    <div class="flex items-center gap-3">
                        <div class="w-5 h-5 rounded-md bg-gray-100 border-2 border-gray-300"></div>
                        <span class="ml-2 text-sm text-gray-700">Belum dijawab</span>
                    </div>
                    <div class="flex items-center gap-3">
                    <div class="w-5 h-5 rounded-md bg-yellow-100 border-2 border-yellow-500"></div>
                    <span class="ml-2 text-sm text-gray-700">Soal Aktif</span>
                </div>
                <div class="flex items-center mt-3 pt-3 border-t border-gray-200">
                    <div class="flex items-center bg-blue-50 text-blue-700 px-3 py-2.5 rounded-lg w-full">
                        <x-heroicon-s-information-circle class="w-5 h-5 mr-2 flex-shrink-0" />
                        <span class="text-xs font-medium">Jawaban otomatis tersimpan saat Anda memilih opsi atau berpindah soal</span>
                    </div>
                </div>
                </div>

                <div class="mt-6">
                    <div class="text-sm font-medium text-gray-700 flex justify-between">
                        <span>Progres Quiz</span>
                        <span class="text-primary-600 font-semibold">{{ round(($this->getAnsweredCount() / $this->getTotalQuestions()) * 100) }}%</span>
                    </div>
                    <div class="w-full bg-gray-200 rounded-full h-3 mt-2 overflow-hidden">
                        <div class="bg-primary-600 h-3 rounded-full transition-all duration-300" style="width: {{ ($this->getAnsweredCount() / $this->getTotalQuestions()) * 100 }}%"></div>
                    </div>
                    <div class="flex justify-between text-xs text-gray-600 mt-3">
                        <span class="flex items-center gap-1">
                            {{ $this->getAnsweredCount() }} terjawab
                            <span class="inline-block w-3 h-3 bg-primary-600 rounded-full"></span>
                        </span>
                        <span class="flex items-center gap-1">
                            {{ $this->getTotalQuestions() - $this->getAnsweredCount() }} belum dijawab
                            <span class="inline-block w-3 h-3 bg-gray-300 rounded-full"></span>
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const saveStatus = document.getElementById('save-status');
            const saving = document.getElementById('saving');
            const saved = document.getElementById('saved');

            // Listen for the custom event from Livewire
            window.addEventListener('answer-saving', function() {
                saveStatus.classList.remove('hidden');
                saving.classList.remove('hidden');
                saved.classList.add('hidden');
            });

            window.addEventListener('answer-saved', function() {
                saving.classList.add('hidden');
                saved.classList.remove('hidden');

                // Hide the save status after 2 seconds
                setTimeout(function() {
                    saveStatus.classList.add('hidden');
                }, 2000);
            });

            // Auto-save periodically
            setInterval(function() {
                if (@this.selectedAnswers && Object.keys(@this.selectedAnswers).length > 0) {
                    @this.dispatch('autoSaveAnswer');
                }
            }, 10000); // Auto save every 10 seconds
        });

        // Confirm before leaving page if quiz is not submitted
        window.addEventListener('beforeunload', function(e) {
            if (!@this.isQuizSubmitted) {
                e.preventDefault();
                e.returnValue = 'Anda yakin ingin keluar? Jawaban Anda telah tersimpan dan Anda dapat melanjutkan kapan saja.';
            }
        });
    </script>
</x-filament-panels::page>
