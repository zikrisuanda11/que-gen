<?php

namespace App\Filament\Resources;

use App\Filament\Resources\OptionResource\Pages;
use App\Filament\Resources\OptionResource\RelationManagers;
use App\Models\Option;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\Auth;

class OptionResource extends Resource
{
    protected static ?string $model = Option::class;

    protected static ?string $navigationIcon = "heroicon-o-check-circle";

    protected static ?string $navigationLabel = "Pilihan Jawaban";

    protected static ?int $navigationSort = 3;

    public static function canViewAny(): bool
    {
        $user = Auth::user();

        return in_array($user->role, ["teacher"]);
    }

    public static function canCreate(): bool
    {
        $user = Auth::user();

        return in_array($user->role, ["teacher"]);
    }

    public static function canEdit($record): bool
    {
        $user = Auth::user();

        return in_array($user->role, ["teacher"]);
    }

    public static function canDelete($record): bool
    {
        $user = Auth::user();

        return in_array($user->role, ["teacher"]);
    }

    // Hide from navigation
    public static function shouldRegisterNavigation(): bool
    {
        return false;
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Select::make("question_id")
                ->label("Pertanyaan")
                ->relationship("question", "title")
                ->searchable()
                ->preload()
                ->required(),
            Forms\Components\TextInput::make("option_text")
                ->label("Teks Pilihan")
                ->required()
                ->maxLength(255),
            Forms\Components\Toggle::make("is_correct")
                ->label("Jawaban Benar")
                ->default(false)
                ->required(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make("question.title")
                    ->label("Pertanyaan")
                    ->limit(50)
                    ->sortable(),
                Tables\Columns\TextColumn::make("option_text")
                    ->label("Teks Pilihan")
                    ->searchable(),
                Tables\Columns\IconColumn::make("is_correct")
                    ->label("Jawaban Benar")
                    ->boolean(),
                Tables\Columns\TextColumn::make("created_at")
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make("updated_at")
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make("question_id")
                    ->label("Pertanyaan")
                    ->relationship("question", "title")
                    ->searchable()
                    ->preload(),
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

    public static function getRelations(): array
    {
        return [
                //
            ];
    }

    public static function getPages(): array
    {
        return [
            "index" => Pages\ListOptions::route("/"),
            "create" => Pages\CreateOption::route("/create"),
            "edit" => Pages\EditOption::route("/{record}/edit"),
        ];
    }
}
