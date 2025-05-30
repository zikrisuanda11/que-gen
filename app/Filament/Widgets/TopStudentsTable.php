<?php

namespace App\Filament\Widgets;

use App\Models\User;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class TopStudentsTable extends BaseWidget
{
    protected static ?int $sort = 3;
    protected int|string|array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->query(function () {
                return User::where('role', 'student')
                    ->leftJoin('submissions', 'users.id', '=', 'submissions.user_id')
                    ->leftJoin('submission_answers', 'submissions.id', '=', 'submission_answers.submission_id')
                    ->leftJoin('options', 'submission_answers.option_id', '=', 'options.id')
                    ->select(
                        'users.id',
                        'users.name',
                        DB::raw('COUNT(DISTINCT submissions.id) as total_submissions'),
                        DB::raw('COUNT(DISTINCT submissions.question_id) as total_questions'),
                        DB::raw('SUM(CASE WHEN options.is_correct = 1 THEN 1 ELSE 0 END) as correct_answers'),
                        DB::raw('COUNT(submission_answers.id) as total_answers'),
                        DB::raw('CASE WHEN COUNT(submission_answers.id) > 0 THEN ROUND((SUM(CASE WHEN options.is_correct = 1 THEN 1 ELSE 0 END) / COUNT(submission_answers.id)) * 100, 1) ELSE 0 END as score')
                    )
                    ->groupBy('users.id', 'users.name')
                    ->having('total_submissions', '>', 0)
                    ->orderBy('score', 'desc')
                    ->orderBy('total_questions', 'desc')
                    ->take(10);
            })
            ->heading('Siswa dengan Nilai Tertinggi')
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Nama Siswa')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('total_questions')
                    ->label('Soal Dikerjakan')
                    ->sortable()
                    ->alignCenter(),
                Tables\Columns\TextColumn::make('correct_answers')
                    ->label('Jawaban Benar')
                    ->sortable()
                    ->alignCenter(),
                Tables\Columns\TextColumn::make('score')
                    ->label('Skor')
                    ->formatStateUsing(fn ($state): string => $state . '%')
                    ->sortable()
                    ->color(fn ($state): string => 
                        $state >= 80 ? 'success' : 
                        ($state >= 60 ? 'warning' : 'danger')
                    )
                    ->alignCenter()
                    ->weight('bold'),
            ])
            ->filters([
                // Add any filters if needed
            ])
            ->emptyStateHeading('Belum ada data')
            ->emptyStateDescription('Siswa belum mengerjakan soal apapun');
    }

    public static function canView(): bool
    {
        return Auth::user()->role === 'teacher';
    }
}