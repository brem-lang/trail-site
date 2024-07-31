<?php

namespace App\Filament\Resources\SessionResource\Pages;

use App\Filament\Resources\SessionResource;
use Filament\Actions;
use Filament\Actions\Action;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;

class EditSession extends EditRecord
{
    protected static string $resource = SessionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('paid')
                ->icon('heroicon-o-shield-check')
                ->label('Paid')
                ->action(function (array $data) {
                    $this->record->status = 'done';
                    $this->record->notes = $data['notes'];
                    $this->record->discount = $data['discount'];
                    $this->record->save();

                    Notification::make()
                        ->title('Success')
                        ->success()
                        ->send();

                    return redirect()->to('app/sessions');
                })
                ->form([
                    TextInput::make('discount')->numeric(),
                    Textarea::make('notes'),
                ])
                ->visible(auth()->user()->isAdmin())
                ->requiresConfirmation(),
            Actions\DeleteAction::make()
                ->icon('heroicon-o-trash')
                ->visible(auth()->user()->isAdmin()),
        ];
    }
}
