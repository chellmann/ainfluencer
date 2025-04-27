<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PostResource\Pages;
use App\Filament\Resources\PostResource\RelationManagers;
use App\Models\Post;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

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
                Forms\Components\TextInput::make('image')
                    ->label('Image URL'),
                Forms\Components\Textarea::make('text')
                    ->label('Text')
                    ->required(),
                Forms\Components\TextInput::make('author')
                    ->label('Author')
                    ->maxLength(255),
                Forms\Components\ColorPicker::make('font_color')
                    ->label('Font Color')
                    ->default('#000000'),
                Forms\Components\TextInput::make('font_size')
                    ->label('Font Size')
                    ->numeric()
                    ->default(66),
                Forms\Components\TextInput::make('font_style')
                    ->label('Font Style')
                    ->numeric()
                    ->default(1),
                Forms\Components\Textarea::make('caption')
                    ->label('Caption'),
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
                Tables\Columns\TextColumn::make('font-color')
                    ->label('Font Color'),
                Tables\Columns\TextColumn::make('font-size')
                    ->label('Font Size'),
                Tables\Columns\TextColumn::make('font-style')
                    ->label('Font Style'),
                Tables\Columns\TextColumn::make('caption')
                    ->label('Caption')
                    ->limit(50),
                Tables\Columns\TextColumn::make('rendered_at')
                    ->label('Rendered At')
                    ->dateTime()
                    ->sortable(),
                Tables\Columns\TextColumn::make('posted_at')
                    ->label('Posted At')
                    ->dateTime()
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Created At')
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
