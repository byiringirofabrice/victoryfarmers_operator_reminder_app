<?php

namespace App\Filament\Resources;

use App\Filament\Resources\UserResource\Pages;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Actions\Action;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;

class UserResource extends Resource
{
    protected static ?string $model = User::class;
    protected static ?string $navigationIcon = 'heroicon-o-users';
    protected static ?string $navigationLabel = 'Manage Users';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('name')
                ->required()
                ->maxLength(255),
            Forms\Components\TextInput::make('email')
                ->required()
                ->email()
                ->maxLength(255),
            Forms\Components\Select::make('role')
                ->options([
                    'foreman' => 'Foreman',
                    'operator' => 'Operator',
                ])
                ->required(),
            Forms\Components\TextInput::make('password')
                ->password()
                ->dehydrateStateUsing(fn ($state) => !empty($state) ? bcrypt($state) : null)
                ->dehydrated(fn ($state) => filled($state))
                ->maxLength(255),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')->searchable(),
                Tables\Columns\TextColumn::make('email')->searchable(),
                Tables\Columns\BadgeColumn::make('role')
                    ->colors([
                        'success' => 'foreman',
                        'info' => 'operator',
                    ])
                    ->label('Role'),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('role')
                    ->options([
                        'foreman' => 'Foreman',
                        'operator' => 'Operator',
                    ])
                    ->label('Filter by Role'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),

                Action::make('Make Foreman')
                    ->icon('heroicon-o-arrow-up-on-square')
                    ->color('success')
                    ->requiresConfirmation()
                    ->visible(fn (User $record): bool =>
                        Auth::user()->role === 'foreman' && $record->role !== 'foreman'
                    )
                    ->action(function (User $record) {
                        $record->syncRoles('foreman'); // assign real role
                        $record->update(['role' => 'foreman']); // keep role column updated if needed
                    }),

                Action::make('Make Operator')
                    ->icon('heroicon-o-arrow-down-on-square')
                    ->color('warning')
                    ->requiresConfirmation()
                    ->visible(fn (User $record): bool =>
                        Auth::user()->role === 'foreman' && $record->role !== 'operator'
                    )
                    ->action(function (User $record) {
                        $record->syncRoles('operator'); // assign real role
                        $record->update(['role' => 'operator']); // keep role column updated if needed
                    }),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListUsers::route('/'),
            'create' => Pages\CreateUser::route('/create'),
            'edit' => Pages\EditUser::route('/{record}/edit'),
        ];
    }
}
