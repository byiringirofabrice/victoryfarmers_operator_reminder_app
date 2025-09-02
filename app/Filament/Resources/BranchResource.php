<?php

namespace App\Filament\Resources;

use App\Filament\Resources\BranchResource\Pages;
use App\Models\Branch;
use App\Models\ControlRoom;
use Filament\Forms;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\BooleanColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class BranchResource extends Resource
{
    protected static ?string $model = Branch::class;
    protected static ?string $navigationIcon = 'heroicon-o-map-pin';
    protected static ?string $navigationGroup = 'Locations';

   public static function form(Form $form): Form
{
    return $form
        ->schema([
            Select::make('country_id')
                ->relationship('country', 'name')
                ->label('Country')
                ->required()
                ->reactive(),

            Select::make('control_room_id')
                ->label('Control Room')
                ->required()
                ->options(function (callable $get) {
                    $countryId = $get('country_id');
                    if (!$countryId) return [];

                    return ControlRoom::where('country_id', $countryId)
                        ->pluck('name', 'id')
                        ->toArray();
                })
                ->reactive()
                ->disabled(fn (callable $get) => !$get('country_id')),

            TextInput::make('name')
                ->required()
                ->maxLength(255)
                ->label('Branch Name'),

            Toggle::make('is_active')->label('Active')->default(true),
            Toggle::make('is_online')->label('Online')->default(true),
        ]);
}

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')->label('Name')->sortable()->searchable(),
                TextColumn::make('country.name')->label('Country')->sortable()->searchable(),
                TextColumn::make('controlRoom.name')->label('Control Room')->sortable()->searchable(),
                BooleanColumn::make('is_active')->label('Active'),
                BooleanColumn::make('is_online')->label('Online'),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListBranches::route('/'),
            'create' => Pages\CreateBranch::route('/create'),
            'edit' => Pages\EditBranch::route('/{record}/edit'),
        ];
    }
}