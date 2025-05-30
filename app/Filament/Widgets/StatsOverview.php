<?php

namespace App\Filament\Widgets;

use App\Models\Question;
use App\Models\Subject;
use App\Models\Submission;
use App\Models\User;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class StatsOverview extends BaseWidget
{
    protected static ?string $pollingInterval = null;
    
    protected function getStats(): array
    {
        $user = Auth::user();
        
        if ($user->role === 'teacher') {
            return $this->getTeacherStats();
        } else {
            return $this->getStudentStats();
        }
    }
    
    protected function getTeacherStats(): array
    {
        $totalStudents = User::where('role', 'student')->count();
        $totalQuestions = Question::count();
        $totalSubjects = Subject::count();
        $totalSubmissions = Submission::count();
        
        $questionsPerSubject = round($totalQuestions / ($totalSubjects ?: 1), 1);
        
        // Get average score of all submissions
        $correctAnswers = DB::table('submission_answers')
            ->join('options', 'submission_answers.option_id', '=', 'options.id')
            ->where('options.is_correct', true)
            ->count();
        
        $totalAnswers = DB::table('submission_answers')->count();
        $averageScore = $totalAnswers > 0 ? round(($correctAnswers / $totalAnswers) * 100, 1) : 0;
        
        return [
            Stat::make('Total Siswa', $totalStudents)
                ->description('Jumlah siswa terdaftar')
                ->descriptionIcon('heroicon-m-user-group')
                ->color('primary'),
                
            Stat::make('Total Soal', $totalQuestions)
                ->description('Dalam ' . $totalSubjects . ' mata pelajaran')
                ->descriptionIcon('heroicon-m-question-mark-circle')
                ->color('success'),
                
            Stat::make('Rata-rata Nilai', $averageScore . '%')
                ->description('Dari ' . $totalSubmissions . ' pengerjaan')
                ->descriptionIcon('heroicon-m-academic-cap')
                ->color($averageScore >= 60 ? 'success' : 'warning'),
        ];
    }
    
    protected function getStudentStats(): array
    {
        $user = Auth::user();
        
        $totalQuestions = Question::count();
        $answeredQuestions = Submission::where('user_id', $user->id)->count();
        $progressPercentage = $totalQuestions > 0 ? round(($answeredQuestions / $totalQuestions) * 100, 1) : 0;
        
        // Get student's average score
        $correctAnswers = DB::table('submission_answers')
            ->join('options', 'submission_answers.option_id', '=', 'options.id')
            ->join('submissions', 'submission_answers.submission_id', '=', 'submissions.id')
            ->where('submissions.user_id', $user->id)
            ->where('options.is_correct', true)
            ->count();
            
        $totalAnswers = DB::table('submission_answers')
            ->join('submissions', 'submission_answers.submission_id', '=', 'submissions.id')
            ->where('submissions.user_id', $user->id)
            ->count();
        
        $averageScore = $totalAnswers > 0 ? round(($correctAnswers / $totalAnswers) * 100, 1) : 0;
        
        // Get best subject
        $subjectScores = DB::table('submission_answers')
            ->join('options', 'submission_answers.option_id', '=', 'options.id')
            ->join('submissions', 'submission_answers.submission_id', '=', 'submissions.id')
            ->join('questions', 'submissions.question_id', '=', 'questions.id')
            ->join('subjects', 'questions.subject_id', '=', 'subjects.id')
            ->where('submissions.user_id', $user->id)
            ->select('subjects.name', 
                DB::raw('COUNT(CASE WHEN options.is_correct = 1 THEN 1 END) as correct'),
                DB::raw('COUNT(*) as total'),
                DB::raw('(COUNT(CASE WHEN options.is_correct = 1 THEN 1 END) / COUNT(*)) as score_ratio'))
            ->groupBy('subjects.name')
            ->having('total', '>', 0)
            ->orderBy('score_ratio', 'DESC')
            ->first();
        
        $bestSubject = $subjectScores ? $subjectScores->name . ' (' . round(($subjectScores->correct / $subjectScores->total) * 100) . '%)' : 'Belum ada data';
        
        return [
            Stat::make('Progress', $progressPercentage . '%')
                ->description($answeredQuestions . ' dari ' . $totalQuestions . ' soal dikerjakan')
                ->descriptionIcon('heroicon-m-arrow-trending-up')
                ->color('primary'),
                
            Stat::make('Nilai Rata-rata', $averageScore . '%')
                ->description($correctAnswers . ' jawaban benar dari ' . $totalAnswers)
                ->descriptionIcon('heroicon-m-academic-cap')
                ->color($averageScore >= 60 ? 'success' : 'warning'),
                
            Stat::make('Mata Pelajaran Terbaik', $bestSubject)
                ->description('Performamu terbaik di sini')
                ->descriptionIcon('heroicon-m-trophy')
                ->color('success'),
        ];
    }
}