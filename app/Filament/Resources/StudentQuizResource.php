<?php

namespace App\Filament\Resources;

use App\Filament\Resources\StudentQuizResource\Pages;
use App\Models\Subject;
use App\Models\Submission;
use App\Filament\Resources\StudentQuizResource\Pages\QuizSession;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;


class StudentQuizResource extends Resource
{
    protected static ?string $model = Subject::class;

    protected static ?string $navigationIcon = 'heroicon-o-academic-cap';
    
    protected static ?string $navigationLabel = 'Kerjakan Soal';
    
    protected static ?int $navigationSort = 1;
    
    public static function shouldRegisterNavigation(): bool
    {
        return auth()->check() && auth()->user()?->role === 'student';
    }

    public static function canViewAny(): bool
    {
        return auth()->check() && auth()->user()?->role === 'student';
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function canEdit(Model $record): bool
    {
        return false;
    }

    public static function canDelete(Model $record): bool
    {
        return false;
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                //
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Mata Pelajaran')
                    ->searchable(),
                Tables\Columns\TextColumn::make('category')
                    ->label('Kategori')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'Science' => 'success',
                        'English' => 'warning',
                        'History' => 'danger',
                    }),
                Tables\Columns\TextColumn::make('questions_count')
                    ->label('Jumlah Soal')
                    ->counts('questions'),
                Tables\Columns\IconColumn::make('submission_status')
                    ->label('Status Pengerjaan')
                    ->boolean()
                    ->state(function (Subject $record): bool {
                        $user = Auth::user();
                        // Check if the user has submissions for questions in this subject
                        $questionIds = $record->questions()->pluck('id');
                        return Submission::whereIn('question_id', $questionIds)
                            ->where('user_id', $user->id)
                            ->exists();
                    })
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle')
                    ->trueColor('success')
                    ->falseColor('danger'),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('category')
                    ->label('Kategori')
                    ->options([
                        'Science' => 'Science',
                        'English' => 'English',
                        'History' => 'History',
                    ]),
            ])
            ->actions([
                Tables\Actions\Action::make('quiz_action')
                    ->label(function (Subject $record): string {
                        $user = Auth::user();
                        $questionIds = $record->questions()->pluck('id');
                        $hasSubmissions = Submission::whereIn('question_id', $questionIds)
                            ->where('user_id', $user->id)
                            ->exists();

                        return $hasSubmissions ? 'Lihat Hasil' : 'Mulai Mengerjakan';
                    })
                    ->icon(function (Subject $record): string {
                        $user = Auth::user();
                        $questionIds = $record->questions()->pluck('id');
                        $hasSubmissions = Submission::whereIn('question_id', $questionIds)
                            ->where('user_id', $user->id)
                            ->exists();

                        return $hasSubmissions ? 'heroicon-o-document-chart-bar' : 'heroicon-o-play';
                    })
                    ->color(function (Subject $record): string {
                        $user = Auth::user();
                        $questionIds = $record->questions()->pluck('id');
                        $hasSubmissions = Submission::whereIn('question_id', $questionIds)
                            ->where('user_id', $user->id)
                            ->exists();

                        return $hasSubmissions ? 'info' : 'success';
                    })
                    ->url(fn (Subject $record): string => route('filament.teacher.resources.student-quizzes.quiz-session', $record))
                    ->visible(fn (Subject $record): bool => $record->questions()->count() > 0),
            ])
            ->bulkActions([])
            ->emptyStateActions([]);
    }
    
    public static function getRelations(): array
    {
        return [
            //
        ];
    }
    
    public static function getPages(): array
    {
        return [
            'index' => Pages\ListStudentQuizzes::route('/'),
            'questions' => Pages\QuestionList::route('/{record}/questions'),
            'answer' => Pages\AnswerQuestion::route('/{record}/questions/{questionId}/answer'),
            'quiz-session' => QuizSession::route('/{record}/quiz'),
        ];
    }
}
