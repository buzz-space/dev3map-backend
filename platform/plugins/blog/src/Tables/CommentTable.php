<?php

namespace Botble\Blog\Tables;

use Illuminate\Support\Facades\Auth;
use BaseHelper;
use Botble\Base\Enums\BaseStatusEnum;
use Botble\Blog\Repositories\Interfaces\CommentInterface;
use Botble\Table\Abstracts\TableAbstract;
use Illuminate\Contracts\Routing\UrlGenerator;
use Yajra\DataTables\DataTables;
use Html;

class CommentTable extends TableAbstract
{

    /**
     * @var bool
     */
    protected $hasActions = true;

    /**
     * @var bool
     */
    protected $hasFilter = true;

    /**
     * CommentTable constructor.
     * @param DataTables $table
     * @param UrlGenerator $urlGenerator
     * @param CommentInterface $commentRepository
     */
    public function __construct(DataTables $table, UrlGenerator $urlGenerator, CommentInterface $commentRepository)
    {
        parent::__construct($table, $urlGenerator);

        $this->repository = $commentRepository;

        if (!Auth::user()->hasAnyPermission(['comment.edit', 'comment.destroy'])) {
            $this->hasOperations = false;
            $this->hasActions = false;
        }
    }

    /**
     * {@inheritDoc}
     */
    public function ajax()
    {
        $data = $this->table
            ->eloquent($this->query())
            ->editColumn('name', function ($item) {
                return $item->user ? $item->user->name : "";
            })
            ->editColumn('checkbox', function ($item) {
                return $this->getCheckbox($item->id);
            })
            ->editColumn('comment', function ($item) {
                return $item->comment;
            })
            ->editColumn('likes', function ($item) {
                return $item->likes;
            })
            ->editColumn('replies', function ($item) {
                return $item->reply()->count();
            })
            ->editColumn('reports', function ($item) {
                $class = ($item->reports > 0) ? "danger" : 'info';
                return "<button class='btn btn-".$class."' disabled>".$item->reports."</button>";
            })
            ->editColumn('created_at', function ($item) {
                return BaseHelper::formatDate($item->created_at);
            })
            ->addColumn('operations', function ($item) {
                return $this->getOperations('', 'comments.destroy', $item);
            });

        return $this->toJson($data);
    }

    /**
     * {@inheritDoc}
     */
    public function query()
    {
        $query = $this->repository->getModel()
            ->select([
               'id',
               'post_id',
               'post_cr4_id',
               'comment_id',
               'comment',
               'user_id',
               'created_at',
               'likes',
               'reports',
           ]);

        return $this->applyScopes($query);
    }

    /**
     * {@inheritDoc}
     */
    public function columns()
    {
        return [
            'id' => [
                'title' => trans('core/base::tables.id'),
                'width' => '20px',
            ],
            'name' => [
                'title' => trans('plugins/customers::customers.singular_name'),
                'class' => 'text-start',
                'orderable' => false,
                'searchable' => false,
            ],
            'comment' => [
                'title' => trans('plugins/blog::comment.name'),
                'class' => 'text-start',
            ],
            'replies' => [
                'title' => trans('plugins/blog::comment.replies'),
                'class' => 'text-start',
                'searchable' => false,
                'orderable' => false,
            ],
            'likes' => [
                'title' => trans('plugins/blog::comment.like'),
                'class' => 'text-start',
                'searchable' => false,
            ],
            'reports' => [
                'title' => trans('plugins/blog::comment.report'),
                'class' => 'text-start',
            ],
            'created_at' => [
                'title' => trans('core/base::tables.created_at'),
                'width' => '100px',
            ],
        ];
    }

    /**
     * {@inheritDoc}
     */
    public function buttons()
    {
        return null;
    }

    /**
     * {@inheritDoc}
     */
    public function bulkActions(): array
    {
        return $this->addDeleteAction(route('comments.deletes'), 'comment.destroy', parent::bulkActions());
    }

    /**
     * {@inheritDoc}
     */
    public function getBulkChanges(): array
    {
        return [
            'name' => [
                'title'    => trans('core/base::tables.name'),
                'type'     => 'text',
                'validate' => 'required|max:120',
            ],
            'status' => [
                'title'    => trans('core/base::tables.status'),
                'type'     => 'select',
                'choices'  => BaseStatusEnum::labels(),
                'validate' => 'required|in:' . implode(',', BaseStatusEnum::values()),
            ],
            'created_at' => [
                'title' => trans('core/base::tables.created_at'),
                'type'  => 'date',
            ],
        ];
    }

    /**
     * @return array
     */
    public function getFilters(): array
    {
        return $this->getBulkChanges();
    }

    public function getDefaultButtons() : array
    {
        return [];
    }
}
