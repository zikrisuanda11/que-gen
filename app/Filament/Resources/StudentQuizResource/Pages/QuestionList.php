<?php

namespace App\Filament\Resources\StudentQuizResource\Pages;

use App\Filament\Resources\StudentQuizResource;
use App\Models\Question;
use App\Models\Subject;
use App\Models\Submission;
use Filament\Actions;
use Filament\Resources\Pages\Page;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Support\Facades\Auth;

class QuestionList extends Page implements Tables\Contracts\HasTable
{
    use Tables\Concerns\InteractsWithTable;
    
    protected static string $resource = StudentQuizResource::class;

    protected static string $view = 'filament.resources.student-quiz-resource.pages.question-list';
    
    public ?Subject $record = null;

    public function getTitle(): string|Htmlable
    {
        return 'Soal ' . $this->record->name;
    }
    
    public function mount(Subject $record): void
    {
        if (auth()->user()->role !== 'student') {
            abort(403, 'Hanya siswa yang dapat mengakses halaman ini');
        }
        
        $this->record = $record;
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Question::query()->where('subject_id', $this->record->id)
            )
            ->columns([
                Tables\Columns\TextColumn::make('title')
                    ->label('Judul Soal')
                    ->searchable()
                    ->limit(50),
                Tables\Columns\TextColumn::make('options_count')
                    ->label('Jumlah Pilihan')
                    ->counts('options'),
                Tables\Columns\IconColumn::make('submission_status')
                    ->label('Status')
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle')
                    ->state(function (Question $record): bool {
                        return Submission::where('user_id', Auth::id())
                            ->where('question_id', $record->id)
                            ->exists();
                    }),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\Action::make('answer')
                    ->label('Jawab Soal')
                    ->icon('heroicon-o-pencil')
                    ->url(fn (Question $record): string => route('filament.teacher.resources.student-quizzes.answer', ['record' => $this->record, 'questionId' => $record->id]))
                    ->hidden(function (Question $record): bool {
                        return Submission::where('user_id', Auth::id())
                            ->where('question_id', $record->id)
                            ->exists();
                    }),
                Tables\Actions\Action::make('view')
                    ->label('Lihat Jawaban')
                    ->icon('heroicon-o-eye')
                    ->url(fn (Question $record): string => route('filament.teacher.resources.student-quizzes.answer', ['record' => $this->record, 'questionId' => $record->id]))
                    ->visible(function (Question $record): bool {
                        return Submission::where('user_id', Auth::id())
                            ->where('question_id', $record->id)
                            ->exists();
                    }),
            ])
            ->bulkActions([])
            ->heading('Daftar Soal');
    }
}