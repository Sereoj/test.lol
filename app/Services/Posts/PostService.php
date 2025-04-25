<?php

namespace App\Services\Posts;

use App\Events\FileDownloaded;
use App\Http\Resources\Media\ThumbMediaResource;
use App\Models\Posts\Post;
use App\Models\Users\User;
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
        return $this->postRepository->getPosts($filters, $userId);
    }

    public function getPost($id)
    {
        $post = $this->postRepository->getPost($id);

        if(Auth::guard('api')->check()) {
            $this->statService->incrementViews($post->id);

            return [
                'post' => $post,
                'isUserLiked' => (bool)$this->statService->isUserLiked(Auth::guard('api')->id(), $post->id),
                'isFavorited' => false
            ];
        }
        return [
            'post' => $post,
            'isUserLiked' => false,
            'isFavorited' => false
        ];
    }

    public function getPostsByUser(User $user, $filters)
    {
        $perPage = $filters['per_page'] ?? 40;
        $pageOffset = $filters['page_offset'] ?? 0;
        return $user->posts()->with(['media', 'user', 'statistics'])->published()->paginate($perPage, ['*'], 'page', $pageOffset + 1);
    }

    public function createPost(array $data)
    {
        return $this->postRepository->createPost($data);
    }

    public function updatePost(int $id, array $data)
    {
        $post = $this->postRepository->updatePost($id, $data);
        return [
            'post' => $post,
            'isUserLiked' => false,
            'isFavorited' => false
        ];
    }

    public function deletePost(int $id): void
    {
        $this->postRepository->deletePost($id);
    }

    public function likePost($userId,$postId)
    {
        $post = $this->postRepository->getPost($postId);
        return $this->statService->incrementLikes($userId, $post->id);
    }

    public function unlikePost($userId,$postId)
    {
        $post = $this->postRepository->getPost($postId);
        return $this->statService->decrementLikes($userId, $post->id);
    }

    public function isUserLiked($userId, $postId)
    {
        return (bool)$this->statService->isUserLiked($userId, $postId);
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

        $zip = new ZipArchive();
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
