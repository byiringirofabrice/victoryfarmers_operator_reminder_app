<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CameraResource\Pages;
use App\Models\Camera;
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
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\CamerasImport;

class CameraResource extends Resource
{
    protected static ?string $model = Camera::class;
    protected static ?string $navigationIcon = 'heroicon-o-video-camera';
    protected static ?string $navigationGroup = 'Monitoring';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Select::make('site_id')
                    ->relationship('site', 'name')
                    ->required()
                    ->label('Site'),
                Select::make('control_room_id')
                    ->relationship('controlRoom', 'name')
                    ->required()
                    ->label('Control Room'),
                TextInput::make('name')
                    ->required()
                    ->maxLength(255)
                    ->label('Camera Name'),
                Select::make('camera_type')
                    ->options(['fixed' => 'Fixed', 'ptz' => 'PTZ'])
                    ->required()
                    ->label('Camera Type'),
                Toggle::make('is_priority')
                    ->label('Priority Camera')
                    ->default(false),
                TextInput::make('sort_order')
                    ->numeric()
                    ->default(0)
                    ->label('Sort Order'),
                Toggle::make('is_active')
                    ->label('Active')
                    ->default(true),
                Toggle::make('is_online')
                    ->label('Online')
                    ->default(true),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')->label('Name')->sortable()->searchable(),
                TextColumn::make('site.name')->label('Site')->sortable()->searchable(),
                TextColumn::make('controlRoom.name')->label('Control Room')->sortable()->searchable(),
                TextColumn::make('camera_type')->label('Type')->sortable(),
                BooleanColumn::make('is_priority')->label('Priority'),
                TextColumn::make('sort_order')->label('Sort Order'),
                BooleanColumn::make('is_active')->label('Active'),
                BooleanColumn::make('is_online')->label('Online'),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
                Tables\Actions\Action::make('import')
                    ->label('Import Cameras')
                    ->action(function ($data) {
                        Excel::import(new CamerasImport, $data['file']);
                        return redirect()->back()->with('success', 'Cameras imported successfully.');
                    })
                    ->form([
                        Forms\Components\FileUpload::make('file')
                            ->label('Excel File')
                            ->acceptedFileTypes(['application/vnd.ms-excel', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'])
                            ->required(),
                    ]),
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