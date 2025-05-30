<?php

namespace App\Filament\Resources\StudentQuizResource\Pages;

use App\Filament\Resources\StudentQuizResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListStudentQuizzes extends ListRecords
{
    protected static string $resource = StudentQuizResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
