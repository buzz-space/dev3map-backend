<?php

namespace Botble\Gallery;

use Botble\Base\Models\BaseModel;
use Botble\Gallery\Repositories\Interfaces\GalleryMetaInterface;
use Botble\LanguageAdvanced\Supports\LanguageAdvancedManager;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Language;
use Theme;

class GallerySupport
{
    /**
     * @var GalleryMetaInterface
     */
    protected $galleryMetaRepository;

    /**
     * Gallery constructor.
     * @param GalleryMetaInterface $galleryMetaRepository
     */
    public function __construct(GalleryMetaInterface $galleryMetaRepository)
    {
        $this->galleryMetaRepository = $galleryMetaRepository;
    }

    /**
     * @param string | array $model
     * @return GallerySupport
     */
    public function registerModule($model): GallerySupport
    {
        if (!is_array($model)) {
            $model = [$model];
        }

        config([
            'plugins.gallery.general.supported' => array_merge($this->getSupportedModules(), $model),
        ]);

        return $this;
    }

    /**
     * @return array
     */
    public function getSupportedModules(): array
    {
        return config('plugins.gallery.general.supported', []);
    }

    /**
     * @param string | array $model
     * @return GallerySupport
     */
    public function removeModule($model): GallerySupport
    {
        $models = $this->getSupportedModules();

        foreach ($this->getSupportedModules() as $key => $item) {
            if ($item == $model) {
                Arr::forget($models, $key);
                break;
            }
        }

        config(['plugins.gallery.general.supported' => $models]);

        return $this;
    }

    /**
     * @param Request $request
     * @param BaseModel $data
     * @throws Exception
     */
    public function saveGallery($request, $data)
    {
        if ($data && in_array(get_class($data), $this->getSupportedModules()) && $request->has('gallery')) {
            $meta = $this->galleryMetaRepository->getFirstBy([
                'reference_id'   => $data->id,
                'reference_type' => get_class($data),
            ]);

            $currentLanguage = $request->input('ref_lang');

            if (defined('LANGUAGE_MODULE_SCREEN_NAME') && $currentLanguage && $currentLanguage != Language::getDefaultLocaleCode()) {
                $formRequest = new Request();
                $formRequest->replace([
                    'language' => $request->input('language'),
                    'ref_lang' => $currentLanguage,
                    'images'   => $request->input('gallery'),
                ]);

                if (!$meta) {
                    $meta = $this->galleryMetaRepository->getModel();
                    $meta->reference_id = $data->id;
                    $meta->reference_type = get_class($data);
                    $meta->images = json_decode($request->input('gallery'), true);
                    $meta->save();
                }

                LanguageAdvancedManager::save($meta, $formRequest);
            } else {
                if (empty($meta->images)) {
                    $this->deleteGallery($data);
                }

                if (!$meta) {
                    $meta = $this->galleryMetaRepository->getModel();
                    $meta->reference_id = $data->id;
                    $meta->reference_type = get_class($data);
                }

                $meta->images = json_decode($request->input('gallery'), true);

                $this->galleryMetaRepository->createOrUpdate($meta);
            }
        }
    }

    /**
     * @param BaseModel $data
     * @return bool
     * @throws Exception
     */
    public function deleteGallery($data): bool
    {
        if (in_array(get_class($data), $this->getSupportedModules())) {
            $this->galleryMetaRepository->deleteBy([
                'reference_id'   => $data->id,
                'reference_type' => get_class($data),
            ]);
        }

        return true;
    }

    /**
     * @return $this
     */
    public function registerAssets(): GallerySupport
    {
        Theme::asset()
            ->usePath(false)
            ->add('lightgallery-css', asset('vendor/core/plugins/gallery/css/lightgallery.min.css'), [], [], '1.0.0')
            ->add('gallery-css', asset('vendor/core/plugins/gallery/css/gallery.css'), [], [], '1.0.0');

        Theme::asset()
            ->container('footer')
            ->usePath(false)
            ->add(
                'lightgallery-js',
                asset('vendor/core/plugins/gallery/js/lightgallery.min.js'),
                ['jquery'],
                [],
                '1.0.0'
            )
            ->add(
                'imagesloaded',
                asset('vendor/core/plugins/gallery/js/imagesloaded.pkgd.min.js'),
                ['jquery'],
                [],
                '1.0.0'
            )
            ->add('masonry', asset('vendor/core/plugins/gallery/js/masonry.pkgd.min.js'), ['jquery'], [], '1.0.0')
            ->add('gallery-js', asset('vendor/core/plugins/gallery/js/gallery.js'), ['jquery'], [], '1.0.0');

        return $this;
    }
}
