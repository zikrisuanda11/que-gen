<?php

namespace App\Filament\Resources;

use App\Filament\Resources\StudentQuizResource\Pages;
use App\Models\Subject;
use App\Filament\Resources\StudentQuizResource\Pages\QuizSession;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;


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
                Tables\Actions\Action::make('start_quiz')
                    ->label('Mulai Mengerjakan')
                    ->icon('heroicon-o-play')
                    ->color('success')
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
