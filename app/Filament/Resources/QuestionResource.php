<?php

namespace App\Filament\Resources;

use App\Filament\Resources\QuestionResource\Pages;
use App\Filament\Resources\QuestionResource\RelationManagers;
use App\Models\Question;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\Auth;

class QuestionResource extends Resource
{
    protected static ?string $model = Question::class;

    protected static ?string $navigationIcon = "heroicon-o-question-mark-circle";

    protected static ?string $navigationLabel = "Bank Soal";

    protected static ?int $navigationSort = 2;

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
            Forms\Components\Section::make("Informasi Soal")->schema([
                Forms\Components\Select::make("subject_id")
                    ->label("Mata Pelajaran")
                    ->relationship("subject", "name")
                    ->searchable()
                    ->preload()
                    ->createOptionForm([
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
                    ])
                    ->required(),
                Forms\Components\TextInput::make("title")
                    ->label("Judul Soal")
                    ->required()
                    ->maxLength(255),
                Forms\Components\RichEditor::make("content")
                    ->label("Isi Soal")
                    ->required()
                    ->columnSpanFull(),
            ]),

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

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make("subject.name")
                    ->label("Mata Pelajaran")
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make("title")
                    ->label("Judul Soal")
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
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make("updated_at")
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make("subject_id")
                    ->label("Mata Pelajaran")
                    ->relationship("subject", "name")
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
        return [RelationManagers\OptionsRelationManager::class];
    }

    public static function getPages(): array
    {
        return [
            "index" => Pages\ListQuestions::route("/"),
            "create" => Pages\CreateQuestion::route("/create"),
            "edit" => Pages\EditQuestion::route("/{record}/edit"),
        ];
    }
}
