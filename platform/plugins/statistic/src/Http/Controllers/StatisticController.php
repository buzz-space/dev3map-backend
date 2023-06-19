<?php

namespace Botble\Statistic\Http\Controllers;

use Botble\Base\Events\BeforeEditContentEvent;
use Botble\Statistic\Http\Requests\StatisticRequest;
use Botble\Statistic\Repositories\Interfaces\StatisticInterface;
use Botble\Base\Http\Controllers\BaseController;
use Illuminate\Http\Request;
use Exception;
use Botble\Statistic\Tables\StatisticTable;
use Botble\Base\Events\CreatedContentEvent;
use Botble\Base\Events\DeletedContentEvent;
use Botble\Base\Events\UpdatedContentEvent;
use Botble\Base\Http\Responses\BaseHttpResponse;
use Botble\Statistic\Forms\StatisticForm;
use Botble\Base\Forms\FormBuilder;

class StatisticController extends BaseController
{
    /**
     * @var StatisticInterface
     */
    protected $statisticRepository;

    /**
     * @param StatisticInterface $statisticRepository
     */
    public function __construct(StatisticInterface $statisticRepository)
    {
        $this->statisticRepository = $statisticRepository;
    }

    /**
     * @param StatisticTable $table
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function index(StatisticTable $table)
    {
        page_title()->setTitle(trans('plugins/statistic::statistic.name'));

        return $table->renderTable();
    }

    /**
     * @param FormBuilder $formBuilder
     * @return string
     */
    public function create(FormBuilder $formBuilder)
    {
        page_title()->setTitle(trans('plugins/statistic::statistic.create'));

        return $formBuilder->create(StatisticForm::class)->renderForm();
    }

    /**
     * @param StatisticRequest $request
     * @param BaseHttpResponse $response
     * @return BaseHttpResponse
     */
    public function store(StatisticRequest $request, BaseHttpResponse $response)
    {
        $statistic = $this->statisticRepository->createOrUpdate($request->input());

        event(new CreatedContentEvent(STATISTIC_MODULE_SCREEN_NAME, $request, $statistic));

        return $response
            ->setPreviousUrl(route('statistic.index'))
            ->setNextUrl(route('statistic.edit', $statistic->id))
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
        $statistic = $this->statisticRepository->findOrFail($id);

        event(new BeforeEditContentEvent($request, $statistic));

        page_title()->setTitle(trans('plugins/statistic::statistic.edit') . ' "' . $statistic->name . '"');

        return $formBuilder->create(StatisticForm::class, ['model' => $statistic])->renderForm();
    }

    /**
     * @param int $id
     * @param StatisticRequest $request
     * @param BaseHttpResponse $response
     * @return BaseHttpResponse
     */
    public function update($id, StatisticRequest $request, BaseHttpResponse $response)
    {
        $statistic = $this->statisticRepository->findOrFail($id);

        $statistic->fill($request->input());

        $statistic = $this->statisticRepository->createOrUpdate($statistic);

        event(new UpdatedContentEvent(STATISTIC_MODULE_SCREEN_NAME, $request, $statistic));

        return $response
            ->setPreviousUrl(route('statistic.index'))
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
            $statistic = $this->statisticRepository->findOrFail($id);

            $this->statisticRepository->delete($statistic);

            event(new DeletedContentEvent(STATISTIC_MODULE_SCREEN_NAME, $request, $statistic));

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
            $statistic = $this->statisticRepository->findOrFail($id);
            $this->statisticRepository->delete($statistic);
            event(new DeletedContentEvent(STATISTIC_MODULE_SCREEN_NAME, $request, $statistic));
        }

        return $response->setMessage(trans('core/base::notices.delete_success_message'));
    }
}
