<?php

namespace App\Services\Posts;

use App\Events\FileDownloaded;
use App\Http\Resources\ThumbMediaResource;
use App\Models\Posts\Post;
use App\Repositories\PostRepository;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Response;
use ZipArchive;

class PostService
{
    private PostStatisticsService $statService;

    private PostRepository $postRepository;

    public function __construct(PostStatisticsService $statService, PostRepository $postRepository)
    {
        $this->statService = $statService;
        $this->postRepository = $postRepository;
    }

    public function getPosts(array $filters, $userId = null)
    {
        $posts = $this->postRepository->getPosts($filters, $userId);

        return ThumbMediaResource::collection($posts);
    }

    public function getPost(int $id)
    {
        $post = $this->postRepository->getPost($id);

        if (! $post) {
            return ['message' => 'Post not found.'];
        }

        $this->statService->incrementViews($post->id);

        return $post;
    }

    public function createPost(array $data)
    {
        return $this->postRepository->createPost($data);
    }

    public function updatePost(int $id, array $data)
    {
        return $this->postRepository->updatePost($id, $data);
    }

    public function deletePost(int $id): void
    {
        $this->postRepository->deletePost($id);
    }

    public function likePost(int $id)
    {
        $post = $this->postRepository->getPost($id);

        return $this->statService->incrementLikes($post->id);
    }

    public function unlikePost(int $id)
    {
        $post = $this->postRepository->getPost($id);

        return $this->statService->decrementLikes($post->id);
    }

    public function repostPost(int $id)
    {
        $post = $this->postRepository->getPost($id);

        return $this->statService->repostPost($post->id);
    }

    public function download(int $postId, ?array $mediaIds = null)
    {
        $filename = md5(Auth::user().$postId);

        $post = Post::with('media')->find($postId);

        if (! $post) {
            Log::error(sprintf('Пост с ID %s не найден.', $postId));

            return response()->json(['error' => 'Post not found'], 404);
        }

        $mediaFiles = $post->media()->when($mediaIds, function ($query) use ($mediaIds) {
            $query->whereIn('media.id', (array) $mediaIds);
        })
            ->where('media.type', 'original')
            ->get();

        if ($mediaFiles->isEmpty()) {
            Log::warning(sprintf('Медиафайлы для поста с ID %s не найдены.', $postId));

            return response()->json(['error' => 'No media found for the post'], 404);
        }

        if ($mediaFiles->count() === 1) {
            $media = $mediaFiles->first();
            $filePath = storage_path(sprintf('app/public/%s', $media->file_path));

            if (! file_exists($filePath)) {
                Log::error(sprintf('Файл не найден: %s', $filePath));

                return response()->json(['error' => 'File not found'], 404);
            }
            event(new FileDownloaded($media));

            return Response::download($filePath, $media->name);
        }

        $zipFileName = sprintf('%s.zip', $filename);
        $zipPath = storage_path(sprintf('app/public/%s', $zipFileName));

        $zip = new ZipArchive;
        if ($zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) === true) {
            $filesAdded = false;

            $mediaModel = null;

            foreach ($mediaFiles as $media) {
                $filePath = storage_path(sprintf('app/public/%s', $media->file_path));
                if (file_exists($filePath)) {
                    $mediaModel = $media;
                    $zip->addFile($filePath, $media->name);
                    $filesAdded = true;
                } else {
                    Log::error(sprintf('Файл не найден: %s', $filePath));
                }
            }

            event(new FileDownloaded($mediaModel));

            if (! $filesAdded) {
                Log::warning('Не было добавлено ни одного файла в архив.');

                return response()->json(['error' => 'No valid media files to download'], 404);
            }

            $zip->close();
        } else {
            Log::error(sprintf('Не удалось создать ZIP-файл: %s', $zipPath));

            return response()->json(['error' => 'Failed to create ZIP file'], 500);
        }

        return Response::download($zipPath)->deleteFileAfterSend();
    }
}
