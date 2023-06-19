<?php

use Botble\Base\Forms\FormAbstract;
use Botble\Blog\Models\Post;
use Botble\Page\Models\Page;
use Kris\LaravelFormBuilder\FormHelper;
use Theme\Ripple\Fields\ThemeIconField;

register_page_template([
    'no-sidebar' => __('No sidebar'),
]);

register_sidebar([
    'id'          => 'top_sidebar',
    'name'        => __('Top sidebar'),
    'description' => __('Area for widgets on the top sidebar'),
]);

register_sidebar([
    'id'          => 'footer_sidebar',
    'name'        => __('Footer sidebar'),
    'description' => __('Area for footer widgets'),
]);

RvMedia::setUploadPathAndURLToPublic();
RvMedia::addSize('featured', 565, 375)->addSize('medium', 540, 360);

add_filter(BASE_FILTER_BEFORE_RENDER_FORM, function ($form, $data) {
    switch (get_class($data)) {
        case Post::class:
        case Page::class:
            $bannerImage = MetaBox::getMetaData($data, 'banner_image', true);

//            $form
//                ->addAfter('image', 'banner_image', 'mediaImage', [
//                    'label'      => __('Banner image (1920x170px)'),
//                    'label_attr' => ['class' => 'control-label'],
//                    'value'      => $bannerImage,
//                ]);
//            break;
    }

    return $form;
}, 124, 3);

add_action([BASE_ACTION_AFTER_CREATE_CONTENT, BASE_ACTION_AFTER_UPDATE_CONTENT], function ($type, $request, $object) {
    switch (get_class($object)) {
        case Post::class:
        case Page::class:
            if ($request->has('banner_image')) {
                MetaBox::saveMetaBoxData($object, 'banner_image', $request->input('banner_image'));
            }

            break;
    }
}, 175, 3);

Form::component('themeIcon', Theme::getThemeNamespace() . '::partials.icons-field', [
    'name',
    'value'      => null,
    'attributes' => [],
]);

add_filter('form_custom_fields', function (FormAbstract $form, FormHelper $formHelper) {
    if (!$formHelper->hasCustomField('themeIcon')) {
        $form->addCustomField('themeIcon', ThemeIconField::class);
    }

    return $form;
}, 29, 2);
