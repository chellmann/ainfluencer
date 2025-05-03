<?php

namespace App\Filament\Imports;

use Filament\Forms\Components\Checkbox;
use Filament\Actions\Imports\Models\Import;
use Filament\Actions\Imports\Importer;
use Filament\Actions\Imports\ImportColumn;
use App\Models\Post;
use Filament\Forms\Components\Select;

class PostImporter extends Importer
{
    protected static ?string $model = Post::class;

    public static function getColumns(): array
    {
        return [
            ImportColumn::make('text')
                ->requiredMapping()
                ->rules(['required']),
            ImportColumn::make('author')
                ->rules(['max:100']),
            ImportColumn::make('font_color')
                ->rules([ 'max:100']),

        ];
    }

    public static function getOptionsFormComponents(): array
    {
        return [
            Select::make('brand')
                ->relationship('brand', 'name')
                ->required()
                ->label('Brand'),
        ];
    }



    public function resolveRecord(): ?Post
    {
        $Post = new Post();
        $Post->brand_id = $this->options['brand'];

        return $Post;
    }

    public static function getCompletedNotificationBody(Import $import): string
    {
        $body = 'Your post import has completed and ' . number_format($import->successful_rows) . ' ' . str('row')->plural($import->successful_rows) . ' imported.';

        if ($failedRowsCount = $import->getFailedRowsCount()) {
            $body .= ' ' . number_format($failedRowsCount) . ' ' . str('row')->plural($failedRowsCount) . ' failed to import.';
        }

        return $body;
    }
}
