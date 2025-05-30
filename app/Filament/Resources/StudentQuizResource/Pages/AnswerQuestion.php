<?php

namespace App\Filament\Resources\StudentQuizResource\Pages;

use App\Filament\Resources\StudentQuizResource;
use App\Models\Option;
use App\Models\Question;
use App\Models\Subject;
use App\Models\Submission;
use App\Models\SubmissionAnswer;
use Filament\Actions;
use Filament\Forms;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Form;
use Filament\Resources\Pages\Page;
use Filament\Notifications\Notification;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class AnswerQuestion extends Page
{
    use InteractsWithForms;

    protected static string $resource = StudentQuizResource::class;

    protected static string $view = "filament.resources.student-quiz-resource.pages.answer-question";

    public ?Subject $record = null;

    public ?int $questionId = null;

    public ?Question $question = null;

    public ?Submission $submission = null;

    public ?array $selectedOptions = [];

    public $selectedOption = null;

    public function mount(Subject $record, int $questionId)
    {
        if (auth()->user()->role !== "student") {
            abort(403, "Hanya siswa yang dapat mengakses halaman ini");
        }

        $this->record = $record;
        $this->questionId = $questionId;
        $this->question = Question::findOrFail($questionId);

        // Check if user already answered this question
        $this->submission = Submission::where("user_id", Auth::id())
            ->where("question_id", $this->questionId)
            ->first();

        if ($this->submission) {
            // Get previous selected options
            $submissionAnswers = $this->submission
                ->answers()
                ->with("option")
                ->get();
            $this->selectedOptions = $submissionAnswers
                ->pluck("option_id")
                ->toArray();

            // Set selected option
            $this->selectedOption = $this->selectedOptions[0] ?? null;
        }
    }

    public function getTitle(): string|Htmlable
    {
        return $this->question ? $this->question->title : "Jawab Soal";
    }

    public function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make("Soal")->schema([
                Forms\Components\Placeholder::make("content")
                    ->label("Isi Soal")
                    ->content(
                        fn() => $this->question ? $this->question->content : ""
                    ),
            ]),
            Forms\Components\Section::make("Pilihan Jawaban")->schema([
                Forms\Components\Radio::make("selectedOption")
                    ->label("Pilih Jawaban")
                    ->options(function () {
                        if (!$this->question) {
                            return [];
                        }

                        return $this->question->options
                            ->pluck("option_text", "id")
                            ->toArray();
                    })
                    ->default($this->selectedOption)
                    ->required()
                    ->disabled(!!$this->submission),
            ]),
        ]);
    }

    public function submit()
    {
        $data = $this->form->getState();
        $this->selectedOption = $data["selectedOption"] ?? null;

        if (empty($this->selectedOption)) {
            Notification::make()
                ->title("Pilih jawaban terlebih dahulu")
                ->danger()
                ->send();
            return;
        }

        try {
            DB::beginTransaction();

            // Check if submission already exists
            $existingSubmission = Submission::where("user_id", Auth::id())
                ->where("question_id", $this->questionId)
                ->first();

            if ($existingSubmission) {
                Notification::make()
                    ->title("Anda sudah menjawab soal ini")
                    ->warning()
                    ->send();
                return;
            }

            // Create submission with current timestamp
            $submission = Submission::create([
                "user_id" => Auth::id(),
                "question_id" => $this->questionId,
                "submitted_at" => now(),
            ]);

            // Create submission answer
            SubmissionAnswer::create([
                "submission_id" => $submission->id,
                "option_id" => $this->selectedOption,
            ]);

            DB::commit();

            Notification::make()
                ->title("Jawaban berhasil disimpan")
                ->success()
                ->send();

            // Reload the page to show results
            return redirect()->to(
                route("filament.teacher.resources.student-quizzes.answer", [
                    "record" => $this->record,
                    "questionId" => $this->questionId,
                ])
            );
        } catch (\Exception $e) {
            DB::rollBack();

            Notification::make()
                ->title("Gagal menyimpan jawaban")
                ->body($e->getMessage())
                ->danger()
                ->send();
        }
    }

    public function isCorrectAnswer(): bool
    {
        if (!$this->submission) {
            return false;
        }

        // Get the submitted answers for this submission
        $submissionAnswers = $this->submission
            ->answers()
            ->with("option")
            ->get();
        if ($submissionAnswers->isEmpty()) {
            return false;
        }

        // Check if any of the selected options is correct
        foreach ($submissionAnswers as $answer) {
            if ($answer->option && $answer->option->is_correct) {
                return true;
            }
        }

        return false;
    }

    public function getCorrectOptions(): Collection
    {
        if (!$this->question) {
            return collect();
        }

        return $this->question->options()->where("is_correct", true)->get();
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make("back")
                ->label("Kembali")
                ->url(
                    route(
                        "filament.teacher.resources.student-quizzes.questions",
                        $this->record
                    )
                )
                ->color("gray")
                ->icon("heroicon-m-arrow-left"),
        ];
    }
}
