<?php

namespace Botble\Media\Http\Controllers\API;

use Botble\Base\Http\Responses\BaseHttpResponse;
use Botble\Media\Models\MediaFile;
use Botble\Media\Models\MediaFolder;
use Botble\Media\Repositories\Interfaces\MediaFileInterface;
use Botble\Media\Repositories\Interfaces\MediaFolderInterface;
use Botble\Media\Repositories\Interfaces\MediaSettingInterface;
use Botble\Media\Services\UploadsManager;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use RvMedia;

/**
 * @since 19/08/2015 08:05 AM
 */
class MediaController extends Controller
{
    /**
     * @var MediaFileInterface
     */
    protected $fileRepository;

    /**
     * @var MediaFolderInterface
     */
    protected $folderRepository;

    /**
     * @var UploadsManager
     */
    protected $uploadManager;

    /**
     * @var MediaSettingInterface
     */
    protected $mediaSettingRepository;

    /**
     * MediaController constructor.
     * @param MediaFileInterface $fileRepository
     * @param MediaFolderInterface $folderRepository
     * @param MediaSettingInterface $mediaSettingRepository
     * @param UploadsManager $uploadManager
     */
    public function __construct(
        MediaFileInterface $fileRepository,
        MediaFolderInterface $folderRepository,
        MediaSettingInterface $mediaSettingRepository,
        UploadsManager $uploadManager
    )
    {
        $this->fileRepository = $fileRepository;
        $this->folderRepository = $folderRepository;
        $this->uploadManager = $uploadManager;
        $this->mediaSettingRepository = $mediaSettingRepository;
    }

    public function create(Request $request, BaseHttpResponse $response)
    {
        try {
            $base64Img = $request->input("base64_img");
            if ($base64Img) {

                list($type, $item) = explode(';', $base64Img);
                list(, $item) = explode(',', $item);
                $item = base64_decode($item);

                $folder = MediaFolder::whereSlug("capture")->first();
                if (!$folder) {
                    $folder = new MediaFolder();
                    $folder->name = "capture";
                    $folder->slug = "capture";
                    $folder->user_id = 0;
                    $folder->save();
                }
                $file = new MediaFile();
                $file->name = "capture_" . strtotime(now()) . ".png";
                $file->folder_id = $folder->id;
                $file->mime_type = "image/png";
                $file->url = "capture/" . $file->name;
                $file->size = 2000;
                $file->user_id = 0;
                $file->save();

                $this->uploadManager->saveFile($file->url, $item);
                RvMedia::generateThumbnails($file);
                return $response->setData(RvMedia::url($file->url));
            }

            throw new \Exception("Cannot find base64 image string!");

        } catch (\Exception $exception) {
            return $response->setError()->setMessage($exception->getMessage());

        }

    }
}
