<?php

namespace App\Filament\Resources;

use function Illuminate\Events\queueable;
use Illuminate\Support\Collection;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Database\Eloquent\Builder;
use Filament\Tables\Table;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Actions\ImportAction;
use Filament\Tables\Actions\BulkAction;
use Filament\Tables;
use Filament\Resources\Resource;
use Filament\Forms\Form;
use Filament\Forms;
use App\Models\Post;
use App\Filament\Resources\PostResource\RelationManagers;
use App\Filament\Resources\PostResource\Pages;
use App\Filament\Imports\PostImporter;

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
                Forms\Components\Select::make('music_id')
                    ->label('Music')
                    ->relationship('music', 'name'),
                Forms\Components\Toggle::make('unblock_image')->label('erlaube Bildgenerierung')->disabled(),
                Forms\Components\Toggle::make('unblock_video')->label('erlaube Videoerstellung')->disabled(),
                Forms\Components\Toggle::make('unblock_post')->label('erlaube Posting')->disabled(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('brand.name')
                    ->label('Brand')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\ImageColumn::make('image')
                    ->url(fn(Post $record): string => url('/posts/' . $record->id.'/preview'))
                    ->openUrlInNewTab(),

                TextColumn::make('text')
                    ->label('Text')
                    ->limit(50)
                    ->tooltip(function (TextColumn $column): ?string {
                        $state = $column->getState();

                        if (strlen($state) <= $column->getCharacterLimit()) {
                            return null;
                        }

                        // Only render the tooltip if the column content exceeds the length limit.
                        return $state;
                    })
                    ->searchable(),

                ToggleColumn::make('unblock_image')
                    ->afterStateUpdated(function ($record, $state) {
                        if ($state) dispatch(new \App\Jobs\GenerateBackground($record));
                    }),
                ToggleColumn::make('unblock_video')
                    ->afterStateUpdated(function ($record, $state) {
                        if ($state) dispatch(new \App\Jobs\GenerateVideo($record));
                    }),
                ToggleColumn::make('unblock_post')
                    // ->afterStateUpdated(function ($record, $state) {
                    //     if ($state) dispatch(new \App\Jobs\UploadInstagramReel($record));
                    // })
                    ,

                TextColumn::make('author')
                    ->label('Author')
                    ->sortable()
                    ->searchable(),

                TextColumn::make('rendered_at')
                    ->label('Rendered At')
                    ->dateTime()
                    ->sortable(),

                TextColumn::make('rendered_at')
                    ->label('Rendered At')
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('caption')
                    ->limit(50)
                    ->tooltip(function (TextColumn $column): ?string {
                        $state = $column->getState();

                        if (strlen($state) <= $column->getCharacterLimit()) {
                            return null;
                        }

                        // Only render the tooltip if the column content exceeds the length limit.
                        return $state;
                    })
                    ->searchable(),
            ])
            ->filters([
                SelectFilter::make('brand')
                    ->relationship('brand', 'name'),
                Filter::make('is_posted')
                    ->query(fn(Builder $query): Builder => $query->whereNotNull('posted_at')),
                Filter::make('not posted')
                    ->query(fn(Builder $query): Builder => $query->whereNull('posted_at')),
                TernaryFilter::make('unblock_image'),
                TernaryFilter::make('unblock_video'),
                TernaryFilter::make('unblock_post'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    BulkAction::make('Generate Image&Video')
                        ->requiresConfirmation()
                        ->action(function (Collection $records) {
                            foreach ($records as $record) {
                                $record->update([
                                    'unblock_image' => true,
                                    'unblock_video' => true,
                                ]);
                                dispatch(new \App\Jobs\GenerateBackground($record));
                                dispatch(new \App\Jobs\GenerateVideo($record));
                            }
                        }),
                    BulkAction::make('Redo Prompt&Image')
                        ->requiresConfirmation()
                        ->action(function (Collection $records) {
                            foreach ($records as $record) {
                                $record->update([
                                    'unblock_image' => true,
                                    'image_prompt' => '',
                                ]);
                                dispatch(function () use ($record) {
                                    $record->generateCaption();
                                });

                                dispatch(new \App\Jobs\GenerateBackground($record))->delay(now()->addSeconds(30));
                            }
                        }),
                    BulkAction::make('Post')
                        ->requiresConfirmation()
                        ->action(function (Collection $records) {
                            foreach ($records as $record) {
                                if($record->unblock_image != true){
                                    $record->update([
                                        'unblock_image' => true,
                                    ]);
                                    dispatch(new \App\Jobs\GenerateBackground($record));
                                }
                                if($record->unblock_video != true){
                                    $record->update([
                                        'unblock_video' => true,
                                    ]);
                                    dispatch(new \App\Jobs\GenerateVideo($record));
                                }
                                if($record->unblock_post != true){
                                    $record->update([
                                        'unblock_post' => true,
                                    ]);
                                }
                            }
                        })
                ]),
            ])
            ->headerActions([
                ImportAction::make()
                    ->importer(PostImporter::class)
            ])
            ->defaultSort('created_at', 'desc')
            ->poll('15s');
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
