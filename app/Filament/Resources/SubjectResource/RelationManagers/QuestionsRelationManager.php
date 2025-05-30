<?php

namespace App\Filament\Resources\SubjectResource\RelationManagers;

use App\Models\Question;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

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
