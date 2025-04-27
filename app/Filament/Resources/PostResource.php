<?php

namespace App\Filament\Resources;

use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Database\Eloquent\Builder;
use Filament\Tables\Table;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables;
use Filament\Resources\Resource;
use Filament\Forms\Form;
use Filament\Forms;
use App\Models\Post;
use App\Filament\Resources\PostResource\RelationManagers;
use App\Filament\Resources\PostResource\Pages;
use function Illuminate\Events\queueable;

class PostResource extends Resource
{
    protected static ?string $model = Post::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('brand_id')
                    ->label('Brand')
                    ->relationship('brand', 'name')
                    ->required(),
                // Forms\Components\TextInput::make('image')->label('Image URL'),
                Forms\Components\Textarea::make('text')->required(),
                Forms\Components\TextInput::make('author')->maxLength(255),
                Forms\Components\ColorPicker::make('font_color')->default('#000000'),
                // Forms\Components\TextInput::make('font_size')
                //     ->label('Font Size')
                //     ->numeric()
                //     ->default(66),
                // Forms\Components\TextInput::make('font_style')
                //     ->label('Font Style')
                //     ->numeric()
                //     ->default(1),
                Forms\Components\Textarea::make('caption')->label('Caption'),
                Forms\Components\Textarea::make('image_prompt')->label('Image Prompt'),
                Forms\Components\Toggle::make('unblock_image')->label('erlaube Bildgenerierung'),
                Forms\Components\Toggle::make('unblock_video')->label('erlaube Videoerstellung'),
                Forms\Components\Toggle::make('unblock_post')->label('erlaube Posting'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('brand.name')
                    ->label('Brand')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\ImageColumn::make('image')
                    ->label('Image'),
                Tables\Columns\TextColumn::make('text')
                    ->label('Text')
                    ->limit(50)
                    ->searchable(),
                Tables\Columns\TextColumn::make('author')
                    ->label('Author')
                    ->sortable()
                    ->searchable(),

                ToggleColumn::make('unblock_image')
                    ->afterStateUpdated(function ($record, $state) {
                        if($state) dispatch(new \App\Jobs\GenerateBackground($record));
                    }),
                ToggleColumn::make('unblock_video')
                    ->afterStateUpdated(function ($record, $state) {
                        if($state) dispatch(new \App\Jobs\GenerateVideo($record));
                    }),
                ToggleColumn::make('unblock_post')
                    ->afterStateUpdated(function ($record, $state) {
                        if($state) dispatch(new \App\Jobs\UploadInstagramReel($record));
                    }),

                Tables\Columns\TextColumn::make('rendered_at')
                    ->label('Rendered At')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\Filter::make('posted')
                    ->query(fn(Builder $query) => $query->whereNotNull('posted_at'))
                    ->label('Posted'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
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
            'index' => Pages\ListPosts::route('/'),
            'create' => Pages\CreatePost::route('/create'),
            'edit' => Pages\EditPost::route('/{record}/edit'),
        ];
    }
}
