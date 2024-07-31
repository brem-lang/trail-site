<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SessionResource\Pages;
use App\Models\Session;
use App\Models\User;
use Filament\Forms\Components\Group;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Support\Enums\FontWeight;
use Filament\Tables;
use Filament\Tables\Columns\Layout\Split;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class SessionResource extends Resource
{
    protected static ?string $model = Session::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Group::make()
                    ->schema([
                        Section::make('')
                            ->schema([
                                TextInput::make('title')
                                    ->required()
                                    ->maxLength(255),
                                Select::make('user_id')
                                    ->label('Mechanic')
                                    ->options(function () {
                                        $user = User::whereNot('id', auth()->user()->id)->get();

                                        return $user->mapWithKeys(function ($user) {
                                            return [$user->id => $user->name];
                                        });
                                    })
                                    ->required(),
                            ]),
                    ])
                    ->visible(function ($record) {
                        return $record === null ? true : $record->status === 'done';
                    })
                    ->columnSpan(2),

                Group::make()
                    ->schema([
                        Section::make('')
                            ->schema([
                                Select::make('type')
                                    ->options([
                                        'cash' => 'Cash',
                                        'utang' => 'Utang',
                                    ])
                                    ->default('cash')
                                    ->in(['cash', 'utang'])
                                    ->required(),
                                Select::make('status')
                                    ->options([
                                        'inprogress' => 'In Progress',
                                        'done' => 'Done',
                                    ])
                                    ->default('inprogress')
                                    ->in(['inprogress', 'done'])
                                    ->required(),
                            ]),
                    ])
                    ->visible(function ($record) {
                        return $record === null ? true : $record->status === 'done';
                    }),

                Group::make()
                    ->schema([
                        Section::make('')
                            ->schema([
                                Repeater::make('items')
                                    ->schema([
                                        TextInput::make('quantity')
                                            ->disabled(! auth()->user()->isAdmin())
                                            ->numeric()
                                            ->required(),
                                        TextInput::make('name')
                                            ->disabled(! auth()->user()->isAdmin())
                                            ->distinct()
                                            ->required(),
                                        TextInput::make('price')
                                            ->disabled(! auth()->user()->isAdmin())
                                            ->numeric()
                                            ->required(),
                                    ])
                                    ->reorderable(false)
                                    ->columns(3)
                                    ->collapsible()
                                    ->defaultItems(1),
                            ]),
                    ])
                    ->columnSpanFull(),
            ])->columns(3);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Split::make([
                    TextColumn::make('title')
                        ->weight(FontWeight::Bold)
                        ->searchable(),
                    TextColumn::make('created_by.name')
                        ->searchable(),
                    TextColumn::make('status')
                        ->formatStateUsing(function ($state) {
                            return $state === 'inprogress' ? 'In Progress' : 'Done';
                        })
                        ->badge()
                        ->colors([
                            'success' => static fn ($state): bool => $state == 'done',
                            'danger' => static fn ($state): bool => $state == 'inprogress',
                        ]),
                    TextColumn::make('created_at')->dateTime('F d Y h:i A'),
                    TextColumn::make('total_price')
                        ->getStateUsing(function ($record) {

                            $totalPrice = 0;

                            // Iterate over each item to calculate the total price
                            foreach ($record->items as $item) {
                                $totalPrice += $item['quantity'] * $item['price'];
                            }

                            return $record->discount ? $totalPrice - $record->discount : $totalPrice;
                        }),
                ]),
            ])
            ->filters([
                //
            ])
            ->actions([
                // Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                // Tables\Actions\BulkActionGroup::make([
                //     Tables\Actions\DeleteBulkAction::make(),
                // ]),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListSessions::route('/'),
            'create' => Pages\CreateSession::route('/create'),
            'edit' => Pages\EditSession::route('/{record}/edit'),
        ];
    }
}
