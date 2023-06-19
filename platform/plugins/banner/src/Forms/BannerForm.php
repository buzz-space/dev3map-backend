<?php

namespace Botble\Banner\Forms;

use Botble\Base\Forms\FormAbstract;
use Botble\Base\Enums\BaseStatusEnum;
use Botble\Banner\Http\Requests\BannerRequest;
use Botble\Banner\Models\Banner;

class BannerForm extends FormAbstract
{

    /**
     * {@inheritDoc}
     */
    public function buildForm()
    {
        $this
            ->setupModel(new Banner)
            ->setValidatorClass(BannerRequest::class)
            ->withCustomFields()
            ->add('name', 'text', [
                'label'      => trans('core/base::forms.name'),
                'label_attr' => ['class' => 'control-label required'],
                'attr'       => [
                    'placeholder'  => trans('core/base::forms.name_placeholder'),
                    'data-counter' => 120,
                ],
            ])->add('subtitle', 'text', [
                'label'      => trans('plugins/banner::banner.subtitle'),
                'label_attr' => ['class' => 'control-label'],
                'attr'       => [
                    'placeholder'  => trans('plugins/banner::banner.subtitle'),
                    'data-counter' => 120,
                ],
            ])->add('link', 'text', [
                'label'      => trans('core/base::forms.link'),
                'label_attr' => ['class' => 'control-label'],
                'attr'       => [
                    'placeholder'  => trans('core/base::forms.link'),
                    'data-counter' => 120,
                ],
            ])->add('description', 'textarea', [
                'label'      => trans('core/base::forms.description'),
                'label_attr' => ['class' => 'control-label'],
                'attr'       => [
                    'placeholder'  => trans('core/base::forms.description_placeholder'),
                ],
            ])->add('status', 'customSelect', [
                'label'      => trans('core/base::tables.status'),
                'label_attr' => ['class' => 'control-label required'],
                'attr'       => [
                    'class' => 'form-control select-full',
                ],
                'choices'    => BaseStatusEnum::labels(),
            ])->add('order', 'number', [
                'label'      => trans('core/base::forms.order'),
                'label_attr' => ['class' => 'control-label'],
                'default_value' => 0
            ])->add('position', 'customSelect', [
                'label'      => trans('plugins/banner::banner.position'),
                'label_attr' => ['class' => 'control-label required'],
                'attr'       => [
                    'class' => 'form-control select-full',
                ],
                'choices'    => getBannerPosition(),
            ])->add('background_color', 'color', [
                'label'      => trans('plugins/banner::banner.background_color'),
                'label_attr' => ['class' => 'control-label'],
            ])->add('image', 'mediaImage', [
                'label'      => trans('core/base::forms.image'),
                'label_attr' => ['class' => 'control-label'],
                'help_block' => [
                    'text' => "Recommended size: 868:320 (px) ",
                    'tag' => 'p',
                    'attr' => ['class' => 'help-block']
                ],
            ])->setBreakFieldPoint('status');
    }
}
