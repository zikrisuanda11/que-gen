<?php

namespace App\Filament\Widgets;

use App\Models\Submission;
use Carbon\Carbon;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class PerformanceTrendsChart extends ChartWidget
{
    protected static ?string $heading = 'Tren Performa';
    
    protected static ?int $sort = 2;
    
    protected int|string|array $columnSpan = 'full';
    
    protected static ?string $maxHeight = '300px';
    
    public function getDescription(): ?string
    {
        if (Auth::user()->role === 'teacher') {
            return 'Tren performa nilai rata-rata siswa dalam 7 hari terakhir';
        }
        
        return 'Tren perkembangan nilai kamu dalam 7 hari terakhir';
    }
    
    protected function getData(): array
    {
        $user = Auth::user();
        
        if ($user->role === 'teacher') {
            return $this->getTeacherData();
        }
        
        return $this->getStudentData($user->id);
    }
    
    protected function getTeacherData(): array
    {
        $days = 7;
        $dateLabels = [];
        $performanceData = [];
        $submissionCounts = [];
        
        // Get dates for last 7 days
        for ($i = $days - 1; $i >= 0; $i--) {
            $date = Carbon::now()->subDays($i)->format('Y-m-d');
            $dateLabels[] = Carbon::now()->subDays($i)->format('d M');
            
            // Get performance data for this day
            $dayData = DB::table('submission_answers')
                ->join('options', 'submission_answers.option_id', '=', 'options.id')
                ->join('submissions', 'submission_answers.submission_id', '=', 'submissions.id')
                ->whereDate('submissions.submitted_at', $date)
                ->select(
                    DB::raw('SUM(CASE WHEN options.is_correct = 1 THEN 1 ELSE 0 END) as correct'),
                    DB::raw('COUNT(*) as total')
                )
                ->first();
                
            if ($dayData && $dayData->total > 0) {
                $performanceData[] = round(($dayData->correct / $dayData->total) * 100, 1);
                $submissionCounts[] = $dayData->total;
            } else {
                $performanceData[] = 0;
                $submissionCounts[] = 0;
            }
        }
        
        return [
            'datasets' => [
                [
                    'label' => 'Rata-rata Nilai (%)',
                    'data' => $performanceData,
                    'borderColor' => '#4ade80',
                    'backgroundColor' => 'rgba(74, 222, 128, 0.1)',
                    'fill' => true,
                ],
                [
                    'label' => 'Jumlah Jawaban',
                    'data' => $submissionCounts,
                    'borderColor' => '#94a3b8',
                    'backgroundColor' => 'rgba(148, 163, 184, 0.1)',
                    'fill' => true,
                    'yAxisID' => 'y1',
                ],
            ],
            'labels' => $dateLabels,
        ];
    }
    
    protected function getStudentData(int $userId): array
    {
        $days = 7;
        $dateLabels = [];
        $performanceData = [];
        
        // Get dates for last 7 days
        for ($i = $days - 1; $i >= 0; $i--) {
            $date = Carbon::now()->subDays($i)->format('Y-m-d');
            $dateLabels[] = Carbon::now()->subDays($i)->format('d M');
            
            // Get performance data for this day
            $dayData = DB::table('submission_answers')
                ->join('options', 'submission_answers.option_id', '=', 'options.id')
                ->join('submissions', 'submission_answers.submission_id', '=', 'submissions.id')
                ->where('submissions.user_id', $userId)
                ->whereDate('submissions.submitted_at', $date)
                ->select(
                    DB::raw('SUM(CASE WHEN options.is_correct = 1 THEN 1 ELSE 0 END) as correct'),
                    DB::raw('COUNT(*) as total')
                )
                ->first();
                
            if ($dayData && $dayData->total > 0) {
                $performanceData[] = round(($dayData->correct / $dayData->total) * 100, 1);
            } else {
                $performanceData[] = 0;
            }
        }
        
        // Add moving average trend line
        $movingAvg = $this->calculateMovingAverage($performanceData, 3);
        
        return [
            'datasets' => [
                [
                    'label' => 'Nilai Harian (%)',
                    'data' => $performanceData,
                    'borderColor' => '#60a5fa',
                    'backgroundColor' => 'rgba(96, 165, 250, 0.1)',
                    'fill' => true,
                ],
                [
                    'label' => 'Tren (Rata-rata 3 hari)',
                    'data' => $movingAvg,
                    'borderColor' => '#f43f5e',
                    'borderWidth' => 2,
                    'borderDash' => [5, 5],
                    'fill' => false,
                    'pointRadius' => 0,
                ],
            ],
            'labels' => $dateLabels,
        ];
    }
    
    protected function calculateMovingAverage(array $data, int $window): array
    {
        $result = [];
        $count = count($data);
        
        for ($i = 0; $i < $count; $i++) {
            if ($i < $window - 1) {
                // Not enough data points yet for full window
                $result[] = null;
                continue;
            }
            
            $sum = 0;
            for ($j = 0; $j < $window; $j++) {
                $sum += $data[$i - $j];
            }
            
            $result[] = round($sum / $window, 1);
        }
        
        return $result;
    }
    
    protected function getType(): string
    {
        return 'line';
    }
    
    protected function getOptions(): array
    {
        $options = [
            'scales' => [
                'y' => [
                    'min' => 0,
                    'max' => 100,
                    'title' => [
                        'display' => true,
                        'text' => 'Nilai (%)'
                    ],
                ],
            ],
            'elements' => [
                'line' => [
                    'tension' => 0.3 // Adds curve to the lines
                ]
            ],
        ];
        
        // Add second y-axis for submission counts if teacher view
        if (Auth::user()->role === 'teacher') {
            $options['scales']['y1'] = [
                'position' => 'right',
                'title' => [
                    'display' => true,
                    'text' => 'Jumlah Jawaban'
                ],
                'grid' => [
                    'drawOnChartArea' => false,
                ],
            ];
        }
        
        return $options;
    }
}