<?php

namespace App\Filament\Resources;

use App\Filament\Resources\TaskResource\Pages;
use App\Models\Task;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\TimePicker;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\BooleanColumn;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\TasksImport;

class TaskResource extends Resource
{
    protected static ?string $model = Task::class;
    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document';
    protected static ?string $navigationGroup = 'Monitoring';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Select::make('control_room_id')
                    ->relationship('controlRoom', 'name')
                    ->required()
                    ->label('Control Room'),
                Select::make('site_id')
                    ->relationship('site', 'name')
                    ->nullable()
                    ->label('Site'),
                Select::make('camera_ids')
                    ->relationship('cameras', 'name', fn ($query) => $query->where('is_active', true))
                    ->multiple()
                    ->nullable()
                    ->label('Cameras'),
                TextInput::make('title')
                    ->required()
                    ->maxLength(255)
                    ->label('Task Title'),
                Textarea::make('message')
                    ->required()
                    ->label('Task Message'),
                TextInput::make('duration_minutes')
                    ->numeric()
                    ->required()
                    ->minValue(1)
                    ->default(10)
                    ->label('Duration (Minutes)'),
                Toggle::make('is_priority')
                    ->label('Priority Task')
                    ->default(false),
                Toggle::make('is_break')
                    ->label('Is Break')
                    ->default(false),
                Select::make('break_type')
                    ->options(['eye_break' => 'Eye Break', 'lunch_break' => 'Lunch Break'])
                    ->nullable()
                    ->label('Break Type'),
                TimePicker::make('scheduled_time')
                    ->nullable()
                    ->label('Scheduled Time'),
                TextInput::make('frequency_hours')
                    ->numeric()
                    ->nullable()
                    ->label('Frequency (Hours)'),
                Toggle::make('is_active')
                    ->label('Active')
                    ->default(true),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('title')->label('Title')->sortable()->searchable(),
                TextColumn::make('controlRoom.name')->label('Control Room')->sortable()->searchable(),
                TextColumn::make('site.name')->label('Site')->sortable()->searchable(),
                BooleanColumn::make('is_priority')->label('Priority'),
                BooleanColumn::make('is_break')->label('Break'),
                TextColumn::make('break_type')->label('Break Type'),
                TextColumn::make('scheduled_time')->label('Scheduled Time'),
                TextColumn::make('frequency_hours')->label('Frequency (Hours)'),
                BooleanColumn::make('is_active')->label('Active'),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
                Tables\Actions\Action::make('import')
                    ->label('Import Tasks')
                    ->action(function ($data) {
                        Excel::import(new TasksImport, $data['file']);
                        return redirect()->back()->with('success', 'Tasks imported successfully.');
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
            'index' => Pages\ListTasks::route('/'),
            'create' => Pages\CreateTask::route('/create'),
            'edit' => Pages\EditTask::route('/{record}/edit'),
        ];
    }
}