<?php

namespace App\Filament\Resources\PostResource\Pages;

use App\Filament\Resources\PostResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditPost extends EditRecord
{
    protected static string $resource = PostResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
            Actions\Action::make('regenerateCaption')
                ->label('Regenerate Caption')
                ->action(function () {
                    ray($this->record);
                    $this->record->caption = '';
                    $this->record->save();

                    $this->record->generateCaption();
                }),
        ];
    }
}
