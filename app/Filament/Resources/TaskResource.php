<?php

namespace App\Filament\Resources;

use App\Filament\Resources\TaskResource\Pages;
use App\Models\Task;
use App\Models\ControlRoom;
use App\Models\Site;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class TaskResource extends Resource
{
    protected static ?string $model = Task::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('controlRoom.name')
                    ->label('Control Room')
                    ->sortable()
                    ->searchable(),

                Tables\Columns\TextColumn::make('site.name')
                    ->label('Site')
                    ->sortable()
                    ->searchable(),

                Tables\Columns\TextColumn::make('cameraNames')
                    ->label('Cameras')
                    ->limit(30)
                    ->tooltip(fn ($record) => 
                        implode(', ', is_array($record->cameraNames) ? $record->cameraNames : (array) $record->cameraNames)
                    ),

                Tables\Columns\BadgeColumn::make('type')
                    ->label('Task Type')
                    ->colors([
                        'primary' => 'cross_country',
                        'secondary' => 'kenya_hatchery',
                        'info' => 'lunch_break',
                        'success' => 'country_specific',
                    ])
                    ->sortable(),

                Tables\Columns\BadgeColumn::make('status')
                    ->label('Status')
                    ->colors([
                        'success' => 'sent',
                        'warning' => 'pending',
                        'danger' => 'failed',
                        'primary' => 'processing',
                    ])
                    ->sortable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Generated At')
                    ->dateTime('M d, Y H:i')
                    ->sortable()
                    ->since(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('control_room_id')
                    ->label('Control Room')
                    ->options(ControlRoom::pluck('name', 'id'))
                    ->searchable()
                    ->query(fn (Builder $query, $state) => $state ? $query->where('control_room_id', $state) : $query),

                Tables\Filters\SelectFilter::make('site_id')
                    ->label('Site')
                    ->options(function ($livewire) {
                        $controlRoomId = $livewire->tableFilters['control_room_id']['value'] ?? null;

                        $query = Site::query()->with('branch.country');

                        if ($controlRoomId) {
                            $countryId = ControlRoom::where('id', $controlRoomId)->value('country_id');
                            if ($countryId) {
                                $query->whereHas('branch.country', fn (Builder $q) => $q->where('id', $countryId));
                            }
                        }

                        $sites = $query->get();

                        // Group sites by country name for better organization
                        return $sites->groupBy(fn ($site) => $site->branch->country->name)
                                     ->mapWithKeys(fn ($sites, $countryName) => [
                                         $countryName => $sites->pluck('name', 'id')
                                     ])
                                     ->toArray();
                    })
                    ->searchable()
                    ->query(fn (Builder $query, $state) => $state ? $query->where('site_id', $state) : $query),

                Tables\Filters\SelectFilter::make('type')
                    ->label('Task Type')
                    ->options([
                        'cross_country' => 'Cross Country',
                        'kenya_hatchery' => 'Kenya Hatchery',
                        'lunch_break' => 'Lunch Break',
                        'country_specific' => 'Country Specific',
                    ])
                    ->searchable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->filtersFormColumns(3)  // Organize filters in 3 columns
            ->persistFiltersInSession();  // Persist filters across page reloads
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListTasks::route('/'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->with(['controlRoom', 'site.branch.country']);
    }
}