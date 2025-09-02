<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CountryResource\Pages;
use App\Models\Country;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Forms\Components\TextInput;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Forms\Components\Select;

use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Actions\EditAction;

class CountryResource extends Resource
{
    protected static ?string $model = Country::class;
    protected static ?string $navigationIcon = 'heroicon-o-flag';
    protected static ?string $navigationGroup = 'Settings';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('name')
                    ->required()
                    ->maxLength(255)
                    ->label('Country Name'),
                TextInput::make('code')
                    ->required()
                    ->maxLength(2)
                    ->unique(Country::class, 'code', ignoreRecord: true)
                    ->label('Country Code (ISO 3166-1 alpha-2)'),
               Select::make('timezone')
    ->label('Timezone')
    ->required()
    ->options(
        collect(\DateTimeZone::listIdentifiers(\DateTimeZone::AFRICA))
            ->mapWithKeys(fn ($tz) => [$tz => $tz])
            ->toArray()
    )
    ->searchable()
    ->native(false)
    ->default('Africa/Kigali')
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')->label('Name')->sortable()->searchable(),
                TextColumn::make('code')->label('Code')->sortable()->searchable(),
                TextColumn::make('timezone')->label('Timezone')->sortable(),
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
            'index' => Pages\ListCountries::route('/'),
            'create' => Pages\CreateCountry::route('/create'),
            'edit' => Pages\EditCountry::route('/{record}/edit'),
        ];
    }
}