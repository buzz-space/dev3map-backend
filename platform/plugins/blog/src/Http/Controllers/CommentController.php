<?php

namespace Botble\Blog\Http\Controllers;

use Botble\Base\Events\BeforeEditContentEvent;
use Botble\Blog\Http\Requests\CommentRequest;
use Botble\Blog\Repositories\Interfaces\CommentInterface;
use Botble\Base\Http\Controllers\BaseController;
use Illuminate\Http\Request;
use Exception;
use Botble\Blog\Tables\CommentTable;
use Botble\Base\Events\CreatedContentEvent;
use Botble\Base\Events\DeletedContentEvent;
use Botble\Base\Events\UpdatedContentEvent;
use Botble\Base\Http\Responses\BaseHttpResponse;
use Botble\Blog\Forms\CommentForm;
use Botble\Base\Forms\FormBuilder;

class CommentController extends BaseController
{
    /**
     * @var CommentInterface
     */
    protected $commentRepository;

    /**
     * @param CommentInterface $commentRepository
     */
    public function __construct(CommentInterface $commentRepository)
    {
        $this->commentRepository = $commentRepository;
    }

    /**
     * @param CommentTable $table
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function index(CommentTable $table)
    {
        page_title()->setTitle(trans('plugins/blog::comment.name'));

        return $table->renderTable();
    }

    /**
     * @param FormBuilder $formBuilder
     * @return string
     */
    public function create(FormBuilder $formBuilder)
    {
        page_title()->setTitle(trans('plugins/blog::comment.create'));

        return $formBuilder->create(CommentForm::class)->renderForm();
    }

    /**
     * @param CommentRequest $request
     * @param BaseHttpResponse $response
     * @return BaseHttpResponse
     */
    public function store(CommentRequest $request, BaseHttpResponse $response)
    {
        $comment = $this->commentRepository->createOrUpdate($request->input());

        event(new CreatedContentEvent(COMMENT_MODULE_SCREEN_NAME, $request, $comment));

        return $response
            ->setPreviousUrl(route('comments.index'))
            ->setNextUrl(route('comments.edit', $comment->id))
            ->setMessage(trans('core/base::notices.create_success_message'));
    }

    /**
     * @param int $id
     * @param Request $request
     * @param FormBuilder $formBuilder
     * @return string
     */
    public function edit($id, FormBuilder $formBuilder, Request $request)
    {
        $comment = $this->commentRepository->findOrFail($id);

        event(new BeforeEditContentEvent($request, $comment));

        page_title()->setTitle(trans('plugins/blog::comment.edit') . ' "' . $comment->name . '"');

        return $formBuilder->create(CommentForm::class, ['model' => $comment])->renderForm();
    }

    /**
     * @param int $id
     * @param CommentRequest $request
     * @param BaseHttpResponse $response
     * @return BaseHttpResponse
     */
    public function update($id, CommentRequest $request, BaseHttpResponse $response)
    {
        $comment = $this->commentRepository->findOrFail($id);

        $comment->fill($request->input());

        $comment = $this->commentRepository->createOrUpdate($comment);

        event(new UpdatedContentEvent(COMMENT_MODULE_SCREEN_NAME, $request, $comment));

        return $response
            ->setPreviousUrl(route('comments.index'))
            ->setMessage(trans('core/base::notices.update_success_message'));
    }

    /**
     * @param int $id
     * @param Request $request
     * @param BaseHttpResponse $response
     * @return BaseHttpResponse
     */
    public function destroy(Request $request, $id, BaseHttpResponse $response)
    {
        try {
            $comment = $this->commentRepository->findOrFail($id);

            $this->removeChildComment($comment->reply);

            $this->commentRepository->delete($comment);

            event(new DeletedContentEvent(COMMENT_MODULE_SCREEN_NAME, $request, $comment));

            return $response->setMessage(trans('core/base::notices.delete_success_message'));
        } catch (Exception $exception) {
            return $response
                ->setError()
                ->setMessage($exception->getMessage());
        }
    }

    private function removeChildComment($data)
    {
        if (!empty($data)){
            foreach ($data as $item){
                $this->removeChildComment($item->child);
                $item->delete();
            }
        }
    }

    /**
     * @param Request $request
     * @param BaseHttpResponse $response
     * @return BaseHttpResponse
     * @throws Exception
     */
    public function deletes(Request $request, BaseHttpResponse $response)
    {
        $ids = $request->input('ids');
        if (empty($ids)) {
            return $response
                ->setError()
                ->setMessage(trans('core/base::notices.no_select'));
        }

        foreach ($ids as $id) {
            $comment = $this->commentRepository->findOrFail($id);
            $this->commentRepository->delete($comment);
            event(new DeletedContentEvent(COMMENT_MODULE_SCREEN_NAME, $request, $comment));
        }

        return $response->setMessage(trans('core/base::notices.delete_success_message'));
    }
}
