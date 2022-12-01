<?php

namespace Botble\Blog\Http\Controllers\API;

use App\Models\User;
use Botble\Base\Enums\BaseStatusEnum;
use Botble\Base\Http\Responses\BaseHttpResponse;
use Botble\Blog\Http\Resources\ListCategoryResource;
use Botble\Blog\Http\Resources\ListCommentResource;
use Botble\Blog\Http\Resources\ListPostCR4Resource;
use Botble\Blog\Http\Resources\ListPostResource;
use Botble\Blog\Http\Resources\PostCR4Resource;
use Botble\Blog\Http\Resources\PostResource;
use Botble\Blog\Models\Category;
use Botble\Blog\Models\Post;
use Botble\Blog\Models\PostComment;
use Botble\Blog\Models\PostCommentLike;
use Botble\Blog\Models\PostLike;
use Botble\Blog\Repositories\Interfaces\PostInterface;
use Botble\Blog\Supports\FilterPost;
use Botble\Media\Models\MediaFile;
use Botble\Slug\Models\Slug;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use SlugHelper;
use TechTailor\BinanceApi\BinanceAPI;
use DOMDocument;
use RvMedia;

class PostController extends Controller
{
    /**
     * @var PostInterface
     */
    protected $postRepository;

    /**
     * AuthenticationController constructor.
     *
     * @param PostInterface $postRepository
     */
    public function __construct(PostInterface $postRepository)
    {
        $this->postRepository = $postRepository;
    }


    public function getPosts(Request $request, BaseHttpResponse $response)
    {
        $query = Post::where("status", BaseStatusEnum::PUBLISHED)->orderBy("created_at", "DESC");;

        if ($keyword = $request->input("keyword", ""))
            $query->where(function ($q) use ($keyword){
                $q->where("name", "like", "%$keyword%")
                    ->orWhere("description", "like", "%keyword%");
            });

        if ($cSlug = $request->input("category", "")) {
            if ($cSlug != "latest") {
                $slugHelper = SlugHelper::getSlug($cSlug, SlugHelper::getPrefix(Category::class), Category::class);
                if ($slugHelper) {
                    $postIDs = DB::table("post_categories")
                        ->where("category_id", $slugHelper->reference_id)
                        ->pluck("post_id")->toArray();

                    $postIDsChild = DB::table("post_categories")
                        ->whereIn("category_id", Category::where("parent_id", $slugHelper->reference_id)->pluck("id"))
                        ->pluck("post_id")->toArray();

                    $query->whereIn("id", array_merge($postIDs, $postIDsChild));
                } else
                    $query->where("id", 0);
            }
        }

        if ($request->has("feature"))
            $query->where("is_featured", true);

        $limit = $request->input("limit", 8);
        if ($request->has("page")){
            $page = $request->input("page", 1);
            selectPage($page);

            $data = $query->paginate($limit);

            return $response->setData([
                "data" => ListPostResource::collection($data->items()),
                "next_page" => $data->currentPage() < $data->lastPage()
            ]);
        }

        $data = $query->take($limit)->get();

        return $response->setData(ListPostResource::collection($data));

    }

    public function findBySlug(string $slug, BaseHttpResponse $response)
    {
        $slugHelper = SlugHelper::getSlug($slug, SlugHelper::getPrefix(Post::class), Post::class);

        if (!$slugHelper)
            return $response->setError()->setMessage("Không tìm thấy bài viết!");

        $post = $this->postRepository->getFirstBy([
            'id' => $slugHelper->reference_id,
            'status' => BaseStatusEnum::PUBLISHED,
        ]);

        $categories = DB::table("post_categories")->where("post_id", $post->id)->pluck("category_id");

        $postIDs = DB::table("posts")->join("post_categories as p", "posts.id", "=", "p.post_id")
            ->whereIn("p.category_id", $categories)
            ->where("posts.id", "!=", $post->id)
            ->selectRaw("posts.*")
            ->take(4)->pluck("id");

        $data = Post::whereIn("id", $postIDs)->get();
        if (sizeof($postIDs) < 4){
            $anotherPost = Post::whereNotIn("id", $postIDs)->take(4 - sizeof($postIDs))->get();
            $data = $data->merge($anotherPost);
        }

        $post->posts = $data;

        return $response->setData(new PostResource($post));
    }

    public function getAuthor($name, BaseHttpResponse $response)
    {
        if (!$user = User::where("username", $name)->selectRaw("id, last_name as name, description, avatar_id")->first())
            return $response->setError()->setMessage("Không tìm thấy tác giả!");

        $user->avatar = $user->avatar_id ? RvMedia::url(MediaFile::find($user->avatar_id)->url) : Rvmedia::getDefaultImage();
        $posts = Post::where("author_id", $user->id)->orderBy("created_at", "DESC")->get();

        $user->posts = ListPostResource::collection($posts);

        return $response->setData($user);
    }

    public function getCategories(Request $request, BaseHttpResponse $response)
    {
        $query = Category::query();

        if ($keyword = $request->input("keyword", ""))
            $query->where(function ($q) use ($keyword){
                $q->where("name", "like", "%$keyword%")
                    ->orWhere("description", "like", "%keyword%");
            });

        if ($request->has("special"))
            $query->where("is_featured", true);

        $data = $query->where("status", BaseStatusEnum::PUBLISHED)->take($request->input("limit", 10))->get();

        return $response->setData(ListCategoryResource::collection($data));
    }
}
