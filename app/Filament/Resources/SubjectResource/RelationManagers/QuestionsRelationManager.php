<?php

namespace App\Filament\Resources\SubjectResource\RelationManagers;

use App\Models\Question;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Http;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\DB;

class QuestionsRelationManager extends RelationManager
{
    protected static string $relationship = "questions";

    protected static ?string $recordTitleAttribute = "title";

    public function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\TextInput::make("title")
                ->label("Judul Pertanyaan")
                ->required()
                ->columnSpanFull()
                ->maxLength(255),
            Forms\Components\RichEditor::make("content")
                ->label("Isi Pertanyaan")
                ->columnSpanFull()
                ->required(),
            Forms\Components\Section::make("Pilihan Jawaban")->schema([
                Forms\Components\Repeater::make("options")
                    ->label("Pilihan")
                    ->relationship()
                    ->schema([
                        Forms\Components\TextInput::make("option_text")
                            ->label("Teks Pilihan")
                            ->required(),
                        Forms\Components\Toggle::make("is_correct")
                            ->label("Jawaban Benar")
                            ->default(false)
                            ->required(),
                    ])
                    ->columns(2)
                    ->minItems(2)
                    ->maxItems(5)
                    ->defaultItems(4)
                    ->columnSpanFull(),
            ]),
        ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute("title")
            ->columns([
                Tables\Columns\TextColumn::make("title")
                    ->label("Judul")
                    ->searchable()
                    ->limit(50),
                Tables\Columns\TextColumn::make("options_count")
                    ->label("Jumlah Pilihan")
                    ->counts("options"),
                Tables\Columns\IconColumn::make("options.is_correct")
                    ->label("Ada Jawaban Benar")
                    ->boolean()
                    ->state(function (Question $record): bool {
                        return $record
                            ->options()
                            ->where("is_correct", true)
                            ->exists();
                    }),
                Tables\Columns\TextColumn::make("created_at")
                    ->label("Dibuat")
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()->label("Tambah Soal Baru"),
                Tables\Actions\Action::make('generateQuestion')
                    ->label('Generate Soal dengan AI')
                    ->icon('heroicon-o-sparkles')
                    ->form([
                        Forms\Components\TextInput::make('category')
                            ->label('Kategori')
                            ->required(),
                        Forms\Components\Select::make('answer_option')
                            ->label('Jumlah Pilihan Jawaban')
                            ->options([
                                2 => '2 Pilihan',
                                3 => '3 Pilihan',
                                4 => '4 Pilihan',
                                5 => '5 Pilihan',
                            ])
                            ->default(4)
                            ->required(),
                    ])
                    ->action(function (array $data, RelationManager $livewire): void {
                        $subject = $livewire->getOwnerRecord();
                        
                        try {
                            $response = Http::post('localhost:8000/generate-question', [
                                'subject_name' => $subject->name,
                                'category' => $data['category'],
                                'answer_option' => $data['answer_option']
                            ]);
                            
                            if ($response->successful()) {
                                $questionData = $response->json();
                                
                                // Buat pertanyaan baru
                                $question = new Question();
                                $question->subject_id = $subject->id;
                                $question->title = $questionData['question']['title'];
                                $question->content = $questionData['question']['content'];
                                $question->save();
                                
                                // Tambahkan opsi jawaban
                                foreach ($questionData['options'] as $option) {
                                    $question->options()->create([
                                        'option_text' => $option['option_text'],
                                        'is_correct' => $option['is_correct']
                                    ]);
                                }
                                
                                Notification::make()
                                    ->title('Soal berhasil digenerate')
                                    ->success()
                                    ->send();
                                
                                // Refresh the table
                                $livewire->mountedTableActionRecord = null;
                                $livewire->dispatch('refreshComponent');
                            } else {
                                Notification::make()
                                    ->title('Gagal generate soal')
                                    ->body('Terjadi kesalahan saat menghubungi layanan AI.')
                                    ->danger()
                                    ->send();
                            }
                        } catch (\Exception $e) {
                            Notification::make()
                                ->title('Error')
                                ->body($e->getMessage())
                                ->danger()
                                ->send();
                        }
                    }),
                Tables\Actions\Action::make('generateBulkQuestions')
                    ->label('Generate Bulk Soal')
                    ->icon('heroicon-o-bolt')
                    ->color('success')
                    ->form([
                        Forms\Components\TextInput::make('category')
                            ->label('Kategori')
                            ->required(),
                        Forms\Components\Select::make('answer_option')
                            ->label('Jumlah Pilihan Jawaban')
                            ->options([
                                2 => '2 Pilihan',
                                3 => '3 Pilihan',
                                4 => '4 Pilihan',
                                5 => '5 Pilihan',
                            ])
                            ->default(4)
                            ->required(),
                        Forms\Components\TextInput::make('total_question')
                            ->label('Jumlah Soal')
                            ->required()
                            ->numeric()
                            ->default(5)
                            ->minValue(1)
                            ->maxValue(50),
                    ])
                    ->action(function (array $data, RelationManager $livewire): void {
                        $subject = $livewire->getOwnerRecord();
                    
                        try {
                            Notification::make()
                                ->title('Memproses permintaan...')
                                ->info()
                                ->send();
                            
                            $response = Http::post('localhost:8000/generate/bulk', [
                                'subject_name' => $subject->name,
                                'category' => $data['category'],
                                'answer_option' => (int)$data['answer_option'],
                                'total_question' => (int)$data['total_question']
                            ]);
                        
                            if ($response->successful()) {
                                $bulkData = $response->json();
                                $successCount = 0;
                            
                                DB::beginTransaction();
                            
                                try {
                                    foreach ($bulkData['questions'] as $questionData) {
                                        // Buat pertanyaan baru
                                        $question = new Question();
                                        $question->subject_id = $subject->id;
                                        $question->title = $questionData['question']['title'];
                                        $question->content = $questionData['question']['content'];
                                        $question->save();
                                    
                                        // Tambahkan opsi jawaban
                                        foreach ($questionData['options'] as $option) {
                                            $question->options()->create([
                                                'option_text' => $option['option_text'],
                                                'is_correct' => $option['is_correct']
                                            ]);
                                        }
                                    
                                        $successCount++;
                                    }
                                
                                    DB::commit();
                                
                                    Notification::make()
                                        ->title("Berhasil generate $successCount soal")
                                        ->success()
                                        ->send();
                                    
                                    // Refresh the table
                                    $livewire->mountedTableActionRecord = null;
                                    $livewire->dispatch('refreshComponent');
                                
                                } catch (\Exception $e) {
                                    DB::rollBack();
                                    throw $e;
                                }
                            } else {
                                Notification::make()
                                    ->title("Gagal generate soal")
                                    ->body("Terjadi kesalahan saat menghubungi layanan AI.")
                                    ->danger()
                                    ->send();
                            }
                        } catch (\Exception $e) {
                            Notification::make()
                                ->title("Error")
                                ->body($e->getMessage())
                                ->danger()
                                ->send();
                        }
                    }),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }
}