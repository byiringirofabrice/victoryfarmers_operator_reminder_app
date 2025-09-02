<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CameraResource\Pages;
use App\Models\Camera;
use Filament\Forms;
use Filament\Tables\Actions\ImportAction;
use App\Filament\Imports\CameraImporter;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Toggle;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\BooleanColumn;


class CameraResource extends Resource
{
    protected static ?string $model = Camera::class;
    protected static ?string $navigationIcon = 'heroicon-o-video-camera';
    protected static ?string $navigationGroup = 'Monitoring';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                // Country select
                Select::make('country_id')
                    ->label('Country')
                    ->relationship('site.branch.controlRoom.country', 'name')
                    ->options(fn () => \App\Models\Country::pluck('name', 'id'))
                    ->reactive()
                    ->afterStateUpdated(fn (callable $set) => $set('control_room_id', null)),

                // Control Room based on Country
                Select::make('control_room_id')
                    ->label('Control Room')
                    ->options(fn (callable $get) => 
                        \App\Models\ControlRoom::where('country_id', $get('country_id'))->pluck('name', 'id')
                    )
                    ->reactive()
                    ->afterStateUpdated(fn (callable $set) => $set('branch_id', null))
                    ->required(),

                // Branch based on Control Room
                Select::make('branch_id')
                    ->label('Branch')
                    ->options(fn (callable $get) =>
                        \App\Models\Branch::where('control_room_id', $get('control_room_id'))->pluck('name', 'id')
                    )
                    ->reactive()
                    ->afterStateUpdated(fn (callable $set) => $set('site_id', null))
                    ->required(),

                // Site based on Branch
                Select::make('site_id')
                    ->label('Site')
                    ->options(fn (callable $get) =>
                        \App\Models\Site::where('branch_id', $get('branch_id'))->pluck('name', 'id')
                    )
                    ->required(),

                TextInput::make('name')->required()->label('Camera Name'),

                Select::make('camera_type')
                    ->options(['fixed' => 'Fixed', 'ptz' => 'PTZ'])
                    ->required()
                    ->label('Camera Type'),

                Toggle::make('is_priority')->label('Priority Camera')->default(false),
                TextInput::make('sort_order')->numeric()->default(0)->label('Sort Order')->hidden(),
                Toggle::make('is_active')->label('Active')->default(true),
                Toggle::make('is_online')->label('Online')->default(true),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id')->label('Camera ID')->sortable()->searchable()->toggleable(),
                TextColumn::make('site.branch.controlRoom.country.name')->label('Country')->sortable()->searchable()->toggleable(),
                TextColumn::make('site.branch.controlRoom.name')->label('Control Room')->sortable()->searchable()->toggleable(),
                TextColumn::make('site.branch.name')->label('Branch')->sortable()->searchable()->toggleable(),
                TextColumn::make('site.name')->label('Site')->sortable()->searchable()->toggleable(),
                TextColumn::make('name')->label('Camera Name')->searchable()->sortable()->toggleable(),
                BooleanColumn::make('is_priority')->label('Priority')->sortable()->searchable()->toggleable(),
                BooleanColumn::make('is_active')->label('Active')->sortable()->searchable()->toggleable(),
                BooleanColumn::make('is_online')->label('Online')->sortable()->searchable()->toggleable(),
            ])
            ->filters([
                // Your filters here
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->headerActions([
            Tables\Actions\CreateAction::make(),
                     ImportAction::make()
                       ->importer(CameraImporter::class)
            ->label('Import Cameras')
                ->color('primary'),
])

            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
            ]);
    }



    public static function getPages(): array
    {
        return [
            'index' => Pages\ListCameras::route('/'),
            'create' => Pages\CreateCamera::route('/create'),
            'edit' => Pages\EditCamera::route('/{record}/edit'),
        ];
    }
}