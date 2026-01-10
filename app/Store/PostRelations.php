<?php

namespace App\Store;

class PostRelations
{
    public static function getPostRelations(): array
    {
        return [
            'user',
            'category',
            'media',
            'tags',
            'apps',
            'statistics',
            'interactions',
            'collaborators',
        ];
    }

    public static function getPostWithCollaborators(): array
    {
        return [
            'user',
            'category',
            'media',
            'tags',
            'apps',
            'statistics',
            'collaborators',
        ];
    }
}
