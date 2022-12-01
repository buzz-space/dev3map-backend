<?php

namespace Botble\Banner\Http\Controllers;

use Botble\Base\Events\BeforeEditContentEvent;
use Botble\Banner\Http\Requests\BannerRequest;
use Botble\Banner\Repositories\Interfaces\BannerInterface;
use Botble\Base\Http\Controllers\BaseController;
use Illuminate\Http\Request;
use Exception;
use Botble\Banner\Tables\BannerTable;
use Botble\Base\Events\CreatedContentEvent;
use Botble\Base\Events\DeletedContentEvent;
use Botble\Base\Events\UpdatedContentEvent;
use Botble\Base\Http\Responses\BaseHttpResponse;
use Botble\Banner\Forms\BannerForm;
use Botble\Base\Forms\FormBuilder;

class BannerController extends BaseController
{
    /**
     * @var BannerInterface
     */
    protected $bannerRepository;

    /**
     * @param BannerInterface $bannerRepository
     */
    public function __construct(BannerInterface $bannerRepository)
    {
        $this->bannerRepository = $bannerRepository;
    }

    /**
     * @param BannerTable $table
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     * @throws \Throwable
     */
    public function index(BannerTable $table)
    {
        page_title()->setTitle(trans('plugins/banner::banner.name'));

        return $table->renderTable();
    }

    /**
     * @param FormBuilder $formBuilder
     * @return string
     */
    public function create(FormBuilder $formBuilder)
    {
        page_title()->setTitle(trans('plugins/banner::banner.create'));

        return $formBuilder->create(BannerForm::class)->renderForm();
    }

    /**
     * @param BannerRequest $request
     * @param BaseHttpResponse $response
     * @return BaseHttpResponse
     */
    public function store(BannerRequest $request, BaseHttpResponse $response)
    {
        $banner = $this->bannerRepository->createOrUpdate($request->input());

        event(new CreatedContentEvent(BANNER_MODULE_SCREEN_NAME, $request, $banner));

        return $response
            ->setPreviousUrl(route('banner.index'))
            ->setNextUrl(route('banner.edit', $banner->id))
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
        $banner = $this->bannerRepository->findOrFail($id);

        event(new BeforeEditContentEvent($request, $banner));

        page_title()->setTitle(trans('plugins/banner::banner.edit') . ' "' . $banner->name . '"');

        return $formBuilder->create(BannerForm::class, ['model' => $banner])->renderForm();
    }

    /**
     * @param int $id
     * @param BannerRequest $request
     * @param BaseHttpResponse $response
     * @return BaseHttpResponse
     */
    public function update($id, BannerRequest $request, BaseHttpResponse $response)
    {
        $banner = $this->bannerRepository->findOrFail($id);

        $banner->fill($request->input());

        $banner = $this->bannerRepository->createOrUpdate($banner);

        event(new UpdatedContentEvent(BANNER_MODULE_SCREEN_NAME, $request, $banner));

        return $response
            ->setPreviousUrl(route('banner.index'))
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
            $banner = $this->bannerRepository->findOrFail($id);

            $this->bannerRepository->delete($banner);

            event(new DeletedContentEvent(BANNER_MODULE_SCREEN_NAME, $request, $banner));

            return $response->setMessage(trans('core/base::notices.delete_success_message'));
        } catch (Exception $exception) {
            return $response
                ->setError()
                ->setMessage($exception->getMessage());
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
            $banner = $this->bannerRepository->findOrFail($id);
            $this->bannerRepository->delete($banner);
            event(new DeletedContentEvent(BANNER_MODULE_SCREEN_NAME, $request, $banner));
        }

        return $response->setMessage(trans('core/base::notices.delete_success_message'));
    }
}
