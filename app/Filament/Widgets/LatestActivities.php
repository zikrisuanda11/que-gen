<?php

namespace App\Filament\Widgets;

use App\Models\Submission;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Support\Facades\Auth;

class LatestActivities extends BaseWidget
{
    protected static ?int $sort = 4;
    protected int|string|array $columnSpan = 'full';
    protected static ?string $heading = 'Aktivitas Terbaru';

    public function table(Table $table): Table
    {
        $user = Auth::user();
        $query = Submission::query()
            ->with(['user', 'question', 'question.subject'])
            ->latest('submitted_at');
            
        if ($user->role === 'student') {
            $query->where('user_id', $user->id);
        }
        
        return $table
            ->query($query)
            ->columns([
                Tables\Columns\TextColumn::make('user.name')
                    ->label('Nama Siswa')
                    ->searchable()
                    ->visible(fn () => Auth::user()->role === 'teacher'),
                Tables\Columns\TextColumn::make('question.subject.name')
                    ->label('Mata Pelajaran')
                    ->searchable(),
                Tables\Columns\TextColumn::make('question.title')
                    ->label('Soal')
                    ->limit(50)
                    ->searchable(),
                Tables\Columns\IconColumn::make('is_correct')
                    ->label('Hasil')
                    ->boolean()
                    ->state(function (Submission $record): bool {
                        $correctOptionIds = $record->question->options()
                            ->where('is_correct', true)
                            ->pluck('id');
                            
                        $userAnswerOptionIds = $record->answers()
                            ->pluck('option_id');
                            
                        return $correctOptionIds->intersect($userAnswerOptionIds)->isNotEmpty();
                    })
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle')
                    ->trueColor('success')
                    ->falseColor('danger'),
                Tables\Columns\TextColumn::make('submitted_at')
                    ->label('Waktu Pengerjaan')
                    ->dateTime('d M Y, H:i')
                    ->sortable(),
            ])
            ->filters([
                // Add any filters if needed
            ])
            ->emptyStateHeading('Belum ada aktivitas')
            ->emptyStateDescription(Auth::user()->role === 'teacher' ? 
                'Belum ada siswa yang mengerjakan soal' : 
                'Kamu belum mengerjakan soal apapun'
            )
            ->paginated([10, 25, 50]);
    }
}