<?php

namespace App\Filament\Resources\QuestionResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class OptionsRelationManager extends RelationManager
{
    protected static string $relationship = 'options';

    protected static ?string $recordTitleAttribute = 'option_text';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('option_text')
                    ->label('Teks Pilihan')
                    ->required()
                    ->maxLength(255),
                Forms\Components\Toggle::make('is_correct')
                    ->label('Jawaban Benar')
                    ->default(false)
                    ->required(),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('option_text')
            ->columns([
                Tables\Columns\TextColumn::make('option_text')
                    ->label('Teks Pilihan')
                    ->searchable(),
                Tables\Columns\IconColumn::make('is_correct')
                    ->label('Jawaban Benar')
                    ->boolean(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Dibuat')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Diperbarui')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->label('Tambah Pilihan Baru'),
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