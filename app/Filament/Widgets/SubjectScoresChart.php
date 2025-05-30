<?php

namespace App\Filament\Widgets;

use App\Models\Subject;
use App\Models\User;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class SubjectScoresChart extends ChartWidget
{
    protected static ?string $heading = 'Nilai per Mata Pelajaran';
    
    protected int|string|array $columnSpan = 'full';
    
    protected static ?string $maxHeight = '300px';
    
    public function getDescription(): ?string
    {
        if (Auth::user()->role === 'teacher') {
            return 'Rata-rata nilai siswa di setiap mata pelajaran';
        }
        
        return 'Nilai kamu di setiap mata pelajaran';
    }
    
    protected function getData(): array
    {
        $user = Auth::user();
        
        if ($user->role === 'teacher') {
            return $this->getTeacherData();
        }
        
        return $this->getStudentData($user);
    }
    
    protected function getTeacherData(): array
    {
        $subjects = Subject::all();
        
        $data = [];
        $labels = [];
        
        foreach ($subjects as $subject) {
            $labels[] = $subject->name;
            
            // Calculate average score for this subject
            $subjectScore = DB::table('submission_answers')
                ->join('options', 'submission_answers.option_id', '=', 'options.id')
                ->join('submissions', 'submission_answers.submission_id', '=', 'submissions.id')
                ->join('questions', 'submissions.question_id', '=', 'questions.id')
                ->where('questions.subject_id', $subject->id)
                ->select(
                    DB::raw('SUM(CASE WHEN options.is_correct = 1 THEN 1 ELSE 0 END) as correct'),
                    DB::raw('COUNT(*) as total')
                )
                ->first();
            
            if ($subjectScore && $subjectScore->total > 0) {
                $data[] = round(($subjectScore->correct / $subjectScore->total) * 100, 1);
            } else {
                $data[] = 0;
            }
        }
        
        return [
            'datasets' => [
                [
                    'label' => 'Rata-rata Nilai (%)',
                    'data' => $data,
                    'backgroundColor' => [
                        'rgba(54, 162, 235, 0.6)',
                        'rgba(75, 192, 192, 0.6)',
                        'rgba(255, 205, 86, 0.6)',
                        'rgba(255, 99, 132, 0.6)',
                        'rgba(153, 102, 255, 0.6)',
                    ],
                    'borderColor' => [
                        'rgb(54, 162, 235)',
                        'rgb(75, 192, 192)',
                        'rgb(255, 205, 86)',
                        'rgb(255, 99, 132)',
                        'rgb(153, 102, 255)',
                    ],
                    'borderWidth' => 1
                ],
            ],
            'labels' => $labels,
        ];
    }
    
    protected function getStudentData(User $user): array
    {
        $subjects = Subject::all();
        
        $data = [];
        $labels = [];
        $colors = [];
        
        foreach ($subjects as $subject) {
            $labels[] = $subject->name;
            
            // Calculate student's score for this subject
            $subjectScore = DB::table('submission_answers')
                ->join('options', 'submission_answers.option_id', '=', 'options.id')
                ->join('submissions', 'submission_answers.submission_id', '=', 'submissions.id')
                ->join('questions', 'submissions.question_id', '=', 'questions.id')
                ->where('questions.subject_id', $subject->id)
                ->where('submissions.user_id', $user->id)
                ->select(
                    DB::raw('SUM(CASE WHEN options.is_correct = 1 THEN 1 ELSE 0 END) as correct'),
                    DB::raw('COUNT(*) as total')
                )
                ->first();
            
            if ($subjectScore && $subjectScore->total > 0) {
                $score = round(($subjectScore->correct / $subjectScore->total) * 100, 1);
                $data[] = $score;
                
                if ($score >= 80) {
                    $colors[] = 'rgba(75, 192, 192, 0.6)'; // Green
                } elseif ($score >= 60) {
                    $colors[] = 'rgba(255, 205, 86, 0.6)'; // Yellow
                } else {
                    $colors[] = 'rgba(255, 99, 132, 0.6)'; // Red
                }
            } else {
                $data[] = 0;
                $colors[] = 'rgba(201, 203, 207, 0.6)'; // Grey
            }
        }
        
        return [
            'datasets' => [
                [
                    'label' => 'Nilai (%)',
                    'data' => $data,
                    'backgroundColor' => $colors,
                    'borderColor' => 'rgba(0, 0, 0, 0.1)',
                    'borderWidth' => 1
                ],
            ],
            'labels' => $labels,
        ];
    }
    
    protected function getType(): string
    {
        return 'bar';
    }
}