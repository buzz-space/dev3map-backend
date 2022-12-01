<?php

namespace Botble\Banner\Tables;

use Illuminate\Support\Facades\Auth;
use BaseHelper;
use Botble\Base\Enums\BaseStatusEnum;
use Botble\Banner\Repositories\Interfaces\BannerInterface;
use Botble\Table\Abstracts\TableAbstract;
use Illuminate\Contracts\Routing\UrlGenerator;
use Yajra\DataTables\DataTables;
use Html;

class BannerTable extends TableAbstract
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
     * BannerTable constructor.
     * @param DataTables $table
     * @param UrlGenerator $urlGenerator
     * @param BannerInterface $bannerRepository
     */
    public function __construct(DataTables $table, UrlGenerator $urlGenerator, BannerInterface $bannerRepository)
    {
        parent::__construct($table, $urlGenerator);

        $this->repository = $bannerRepository;

        if (!Auth::user()->hasAnyPermission(['banner.edit', 'banner.destroy'])) {
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
                if (!Auth::user()->hasPermission('banner.edit')) {
                    return $item->name;
                }
                return Html::link(route('banner.edit', $item->id), $item->name);
            })
            ->editColumn('checkbox', function ($item) {
                return $this->getCheckbox($item->id);
            })
            ->editColumn('image', function ($item) {
                return $this->displayThumbnail($item->image);
            })
            ->editColumn('order', function ($item) {
                return $item->order;
            })
            ->editColumn('created_at', function ($item) {
                return BaseHelper::formatDate($item->created_at);
            })
            ->editColumn('status', function ($item) {
                return $item->status->toHtml();
            })
            ->addColumn('operations', function ($item) {
                return $this->getOperations('banner.edit', 'banner.destroy', $item);
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
               'name',
               'created_at',
               'status',
               'image',
               'order',
               'position',
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
            'image' => [
                'title' => trans('core/base::tables.image'),
                'width' => '70px',
            ],
            'name' => [
                'title' => trans('core/base::tables.name'),
                'class' => 'text-left',
            ],
            'order' => [
                'title' => trans('core/base::tables.order'),
                'width' => '20px',
            ],
            'position' => [
                'title' => trans('plugins/banner::banner.position'),
                'width' => '100px',
            ],
            'created_at' => [
                'title' => trans('core/base::tables.created_at'),
//                'width' => '100px',
            ],
            'status' => [
                'title' => trans('core/base::tables.status'),
                'width' => '100px',
            ],
        ];
    }

    /**
     * {@inheritDoc}
     */
    public function buttons()
    {
        return $this->addCreateButton(route('banner.create'), 'banner.create');
    }

    /**
     * {@inheritDoc}
     */
    public function bulkActions(): array
    {
        return $this->addDeleteAction(route('banner.deletes'), 'banner.destroy', parent::bulkActions());
    }

    /**
     * {@inheritDoc}
     */
    public function getBulkChanges(): array
    {
        return [
            'order' => [
                'title'    => trans('core/base::tables.order'),
                'type'     => 'number',
                'validate' => 'required',
            ],
            'position' => [
                'title'    => trans('plugins/banner::banner.position'),
                'type'     => 'select',
                'choices'  => getBannerPosition(),
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
}
