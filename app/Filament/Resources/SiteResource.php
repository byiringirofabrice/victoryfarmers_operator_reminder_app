<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SiteResource\Pages;
use App\Models\Site;
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

class SiteResource extends Resource
{
    protected static ?string $model = Site::class;
    protected static ?string $navigationIcon = 'heroicon-o-globe-europe-africa';
    protected static ?string $navigationGroup = 'Locations';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Select::make('branch_id')
                    ->relationship('branch', 'name')
                    ->required()
                    ->label('Branch'),
                TextInput::make('name')
                    ->required()
                    ->maxLength(255)
                    ->label('Site Name'),
                Toggle::make('is_priority')
                    ->label('Priority Site')
                    ->default(false),
                Toggle::make('is_active')
                    ->label('Active')
                    ->default(true),
                Toggle::make('is_online')
                    ->label('Online')
                    ->default(true),
                TextInput::make('location')
                    ->maxLength(255)
                    ->label('Location (Optional)'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')->label('Name')->sortable()->searchable(),
                TextColumn::make('branch.name')->label('Branch')->sortable()->searchable(),
                BooleanColumn::make('is_priority')->label('Priority'),
                BooleanColumn::make('is_active')->label('Active'),
                BooleanColumn::make('is_online')->label('Online'),
                TextColumn::make('location')->label('Location'),
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
            'index' => Pages\ListSites::route('/'),
            'create' => Pages\CreateSite::route('/create'),
            'edit' => Pages\EditSite::route('/{record}/edit'),
        ];
    }
}