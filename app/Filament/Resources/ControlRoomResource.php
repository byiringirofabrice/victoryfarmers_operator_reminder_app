<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ControlRoomResource\Pages;
use App\Models\ControlRoom;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Toggle;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\BooleanColumn;

class ControlRoomResource extends Resource
{
    protected static ?string $model = ControlRoom::class;
    protected static ?string $navigationIcon = 'heroicon-o-cursor-arrow-ripple';
    protected static ?string $navigationGroup = 'Settings';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Select::make('country_id')
                    ->relationship('country', 'name')
                    ->required()
                    ->label('Country'),
                TextInput::make('name')
                    ->required()
                    ->maxLength(255)
                    ->label('Control Room Name'),
                TextInput::make('notification_interval_minutes')
                    ->numeric()
                    ->required()
                    ->minValue(1)
                    ->default(10)
                    ->label('Notification Interval (Minutes)'),
                Toggle::make('is_active')
                    ->label('Active')
                    ->default(true),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')->label('Name')->sortable()->searchable(),
                TextColumn::make('country.name')->label('Country')->sortable()->searchable(),
                TextColumn::make('notification_interval_minutes')->label('Interval (Min)')->sortable(),
                BooleanColumn::make('is_active')->label('Active'),
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
            'index' => Pages\ListControlRooms::route('/'),
            'create' => Pages\CreateControlRoom::route('/create'),
            'edit' => Pages\EditControlRoom::route('/{record}/edit'),
        ];
    }
}