<?php

namespace App\Filament\Resources\StudentQuizResource\Pages;

use App\Filament\Resources\StudentQuizResource;
use App\Models\Question;
use App\Models\Option;
use App\Models\Subject;
use App\Models\Submission;
use App\Models\SubmissionAnswer;
use Filament\Actions;
use Filament\Resources\Pages\Page;
use Filament\Notifications\Notification;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\On;
use Livewire\Attributes\Reactive;

class QuizSession extends Page
{
    protected static string $resource = StudentQuizResource::class;

    protected static string $view = "filament.resources.student-quiz-resource.pages.quiz-session";

    public ?Subject $record = null;

    public ?int $currentQuestionId = null;

    public ?Question $currentQuestion = null;

    public Collection $allQuestions;

    public array $selectedAnswers = [];

    public bool $isQuizSubmitted = false;

    public function mount(Subject $record)
    {
        if (auth()->check() && auth()->user()?->role !== "student") {
            abort(403, "Hanya siswa yang dapat mengakses halaman ini");
        }

        $this->record = $record;

        // Get all questions for the subject
        $this->allQuestions = $record->questions()->with("options")->get();

        if ($this->allQuestions->isEmpty()) {
            Notification::make()
                ->title("Belum ada soal untuk mata pelajaran ini")
                ->warning()
                ->send();

            return redirect()->route(
                "filament.teacher.resources.student-quizzes.index"
            );
        }

        // Load previous answers if exist
        $this->loadPreviousAnswers();

        // Set the first question as current if not set
        if (is_null($this->currentQuestionId)) {
            $this->setCurrentQuestion($this->allQuestions->first()->id);
        }

        // Check if quiz has been fully submitted
        $this->checkQuizSubmissionStatus();
    }

    public function getTitle(): string|Htmlable
    {
        return $this->record->name;
    }

    public function setCurrentQuestion(int $questionId): void
    {
        $this->currentQuestionId = $questionId;
        $this->currentQuestion = $this->allQuestions->firstWhere(
            "id",
            $questionId
        );
    }

    public function navigateToNextQuestion(): void
    {
        // Find current index
        $currentIndex = $this->allQuestions->search(function ($q) {
            return $q->id === $this->currentQuestionId;
        });

        // Check if not the last question
        if ($currentIndex < $this->allQuestions->count() - 1) {
            $nextQuestion = $this->allQuestions[$currentIndex + 1];
            $this->setCurrentQuestion($nextQuestion->id);
        }
    }

    public function navigateToPreviousQuestion(): void
    {
        // Find current index
        $currentIndex = $this->allQuestions->search(function ($q) {
            return $q->id === $this->currentQuestionId;
        });

        // Check if not the first question
        if ($currentIndex > 0) {
            $prevQuestion = $this->allQuestions[$currentIndex - 1];
            $this->setCurrentQuestion($prevQuestion->id);
        }
    }

    #[On("autoSaveAnswer")]
    public function saveProgress(): void
    {
        if (empty($this->selectedAnswers)) {
            return;
        }

        // Emit event for saving indicator
        $this->dispatch("answer-saving");

        try {
            DB::beginTransaction();

            foreach ($this->selectedAnswers as $questionId => $optionId) {
                if (empty($optionId)) {
                    continue;
                }

                // Check if a submission already exists for this question
                $submission = Submission::where("user_id", Auth::id())
                    ->where("question_id", $questionId)
                    ->first();

                if (!$submission) {
                    // Create a new submission
                    $submission = Submission::create([
                        "user_id" => Auth::id(),
                        "question_id" => $questionId,
                        "submitted_at" => now(),
                    ]);
                } else {
                    // Delete any existing answers for this submission
                    $submission->answers()->delete();
                }

                // Create the new answer
                SubmissionAnswer::create([
                    "submission_id" => $submission->id,
                    "option_id" => $optionId,
                ]);
            }

            DB::commit();

            // Emit event for saved indicator
            $this->dispatch("answer-saved");

            if (!$this->isQuizSubmitted) {
                Notification::make()
                    ->title("Jawaban berhasil disimpan")
                    ->success()
                    ->send();
            }
        } catch (\Exception $e) {
            DB::rollBack();

            Notification::make()
                ->title("Gagal menyimpan jawaban")
                ->body($e->getMessage())
                ->danger()
                ->send();
        }
    }

    public function submitQuiz(): void
    {
        // First save any pending answers
        $this->saveProgress();

        if ($this->getAnsweredCount() === 0) {
            Notification::make()
                ->title("Anda belum menjawab soal apapun")
                ->warning()
                ->send();
            return;
        }

        $this->isQuizSubmitted = true;

        Notification::make()
            ->title("Quiz berhasil diselesaikan")
            ->body("Terima kasih telah menyelesaikan quiz ini!")
            ->success()
            ->send();
    }

    private function loadPreviousAnswers(): void
    {
        // Get all submissions for this user and questions
        $questionIds = $this->allQuestions->pluck("id");

        $submissions = Submission::where("user_id", Auth::id())
            ->whereIn("question_id", $questionIds)
            ->with("answers.option")
            ->get();

        foreach ($submissions as $submission) {
            // Get first answer for each submission
            $firstAnswer = $submission->answers->first();

            if ($firstAnswer) {
                $this->selectedAnswers[$submission->question_id] =
                    $firstAnswer->option_id;
            }
        }
    }

    private function checkQuizSubmissionStatus(): void
    {
        // A quiz is considered submitted if all questions have been answered
        // This can be modified based on your specific requirements
        $answeredCount = $this->getAnsweredCount();
        if (
            $answeredCount > 0 &&
            $answeredCount === $this->allQuestions->count()
        ) {
            $this->isQuizSubmitted = true;
        }
    }

    public function getAnsweredCount(): int
    {
        return count($this->selectedAnswers);
    }

    public function getTotalQuestions(): int
    {
        return $this->allQuestions->count();
    }

    public function getQuestionStatus(int $questionId): string
    {
        if ($questionId === $this->currentQuestionId) {
            return "current";
        }

        if (isset($this->selectedAnswers[$questionId])) {
            return "answered";
        }

        return "unanswered";
    }

    public function isCorrectAnswer(int $questionId): bool
    {
        if (!isset($this->selectedAnswers[$questionId])) {
            return false;
        }

        $selectedOptionId = $this->selectedAnswers[$questionId];
        $option = Option::find($selectedOptionId);

        return $option && $option->is_correct;
    }

    public function getCorrectOptionForQuestion(int $questionId): ?Option
    {
        $question = $this->allQuestions->firstWhere("id", $questionId);
        if (!$question) {
            return null;
        }

        return $question->options->first(function ($option) {
            return $option->is_correct;
        });
    }
}
