<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SubjectResource\Pages;
use App\Filament\Resources\SubjectResource\RelationManagers;
use App\Models\Subject;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\Auth;

class SubjectResource extends Resource
{
    protected static ?string $model = Subject::class;

    protected static ?string $navigationIcon = "heroicon-o-academic-cap";

    protected static ?string $navigationLabel = "Mata Pelajaran";

    protected static ?int $navigationSort = 1;

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

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Card::make()->schema([
                Forms\Components\TextInput::make("name")
                    ->label("Nama Mata Pelajaran")
                    ->required()
                    ->maxLength(255),
                Forms\Components\Select::make("category")
                    ->label("Kategori")
                    ->required()
                    ->options([
                        "Science" => "Science",
                        "English" => "English",
                        "History" => "History",
                    ]),
            ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make("name")
                    ->label("Nama Mata Pelajaran")
                    ->searchable(),
                Tables\Columns\TextColumn::make("category")
                    ->label("Kategori")
                    ->badge()
                    ->color(
                        fn(string $state): string => match ($state) {
                            "Science" => "success",
                            "English" => "warning",
                            "History" => "danger",
                        }
                    ),
                Tables\Columns\TextColumn::make("questions_count")
                    ->label("Jumlah Soal")
                    ->counts("questions"),
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
                //
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
        return [RelationManagers\QuestionsRelationManager::class];
    }

    public static function getPages(): array
    {
        return [
            "index" => Pages\ListSubjects::route("/"),
            "create" => Pages\CreateSubject::route("/create"),
            "edit" => Pages\EditSubject::route("/{record}/edit"),
        ];
    }
}
