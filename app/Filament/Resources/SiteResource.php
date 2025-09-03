<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SiteResource\Pages;
use App\Models\Branch;
use App\Models\Country;
use App\Models\Site;
use App\Models\Camera;
use Filament\Forms;
use Filament\Forms\Set;
use Filament\Forms\Get;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\BooleanColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

class SiteResource extends Resource
{
    protected static ?string $model = Site::class;
    protected static ?string $navigationIcon = 'heroicon-o-globe-europe-africa';
    protected static ?string $navigationGroup = 'Locations';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Select::make('country_id')
                    ->label('Country')
                    ->options(Country::all()->pluck('name', 'id'))
                    ->reactive()
                    ->afterStateUpdated(fn (Set $set) => $set('branch_id', null))
                    ->required(),

                Select::make('branch_id')
                    ->label('Branch')
                    ->options(function (Get $get) {
                        $countryId = $get('country_id');
                        if (!$countryId) return [];
                        return Branch::where('country_id', $countryId)->pluck('name', 'id');
                    })
                    ->searchable()
                    ->required()
                    ->reactive(),
                
                TextInput::make('name')
                    ->required()
                    ->maxLength(255)
                    ->label('Site Name'),
                
                Toggle::make('is_priority')
                    ->label('Priority Site')
                    ->default(false),
                
                Toggle::make('is_active')
                    ->label('Active')
                    ->default(true)
                    ->afterStateUpdated(function ($state, Set $set, $record) {
                        // If site is being deactivated, also mark it as offline
                        if (!$state) {
                            $set('is_online', false);
                        }
                    }),
                
                Toggle::make('is_online')
                    ->label('Online')
                    ->default(true)
                    ->afterStateUpdated(function ($state, $record) {
                        // Update all cameras when site online status changes
                        if ($record) {
                            static::updateCamerasStatus($record, $state);
                        }
                    }),
                
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
                TextColumn::make('branch.country.name')->label('Country')->sortable()->searchable(),
                TextColumn::make('branch.name')->label('Branch')->sortable()->searchable(),
                BooleanColumn::make('is_priority')->label('Priority'),
                BooleanColumn::make('is_active')->label('Active'),
                BooleanColumn::make('is_online')->label('Online'),
                TextColumn::make('location')->label('Location'),
                TextColumn::make('cameras_count')
                    ->label('Cameras')
                    ->counts('cameras')
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\Filter::make('is_online')
                    ->label('Online Status')
                    ->toggle()
                    ->query(fn ($query) => $query->where('is_online', true)),
                
                Tables\Filters\Filter::make('is_active')
                    ->label('Active Status')
                    ->toggle()
                    ->query(fn ($query) => $query->where('is_active', true)),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                
                // Quick online/offline toggle action
                Tables\Actions\Action::make('toggleOnline')
                    ->label(fn (Site $record) => $record->is_online ? 'Take Offline' : 'Bring Online')
                    ->icon(fn (Site $record) => $record->is_online ? 'heroicon-o-x-circle' : 'heroicon-o-check-circle')
                    ->color(fn (Site $record) => $record->is_online ? 'danger' : 'success')
                    ->action(function (Site $record) {
                        $newStatus = !$record->is_online;
                        $record->update(['is_online' => $newStatus]);
                        static::updateCamerasStatus($record, $newStatus);
                    }),
                
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkAction::make('markOnline')
                    ->label('Mark Selected Online')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->action(function ($records) {
                        foreach ($records as $record) {
                            $record->update(['is_online' => true]);
                            static::updateCamerasStatus($record, true);
                        }
                    }),
                
                Tables\Actions\BulkAction::make('markOffline')
                    ->label('Mark Selected Offline')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->action(function ($records) {
                        foreach ($records as $record) { // FIXED: Changed $records to $record
                            $record->update(['is_online' => false]);
                            static::updateCamerasStatus($record, false);
                        }
                    }),
                
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

    /**
     * Update all cameras status when site online status changes
     */
    protected static function updateCamerasStatus(Site $site, bool $isOnline): void
    {
        Camera::where('site_id', $site->id)
            ->update(['is_online' => $isOnline]);
        
        // Log the action
        \Illuminate\Support\Facades\Log::info("Site {$site->name} marked " . ($isOnline ? 'online' : 'offline') . 
            ". Updated " . Camera::where('site_id', $site->id)->count() . " cameras.");
    }

    /**
     * Handle site status changes after save
     */
    public static function afterSave(Model $record, array $data): void
    {
        // If is_online was changed in the form, update cameras
        if (array_key_exists('is_online', $data)) {
            static::updateCamerasStatus($record, $data['is_online']);
        }
        
        // If site is marked inactive, also mark it offline
        if (!$record->is_active && $record->is_online) {
            $record->update(['is_online' => false]);
            static::updateCamerasStatus($record, false);
        }
    }

    /**
     * Handle site status changes after update
     */
    public static function afterUpdate(Model $record, array $data): void
    {
        // If is_online was changed, update cameras
        if (array_key_exists('is_online', $data)) {
            static::updateCamerasStatus($record, $data['is_online']);
        }
        
        // If site is marked inactive, also mark it offline
        if (!$record->is_active && $record->is_online) {
            $record->update(['is_online' => false]);
            static::updateCamerasStatus($record, false);
        }
    }
}