<?php

namespace App\Modules\Setting\Services;

use App\Modules\Setting\Models\Setting;
use App\Modules\Shared\Helpers\FileHelper;
use App\Services\BaseCachedService;

class SettingService extends BaseCachedService
{
    public function __construct()
    {
        parent::__construct(new Setting());
    }

    public function getById(int $id)
    {

        return $this->remember(
            $this->byIdCacheKey($id),
            fn() => $this->model->findOrFail($id)
        );
    }


    public function update(int $id, array $data)
    {
        $record = $this->model->findOrFail($id);

        $this->updateAttributes($record, $data);
        $this->handleCompanyLogo($record, $data);
        $this->handleFavIcon($record, $data);
        $this->handleLoginBackgroundImage($record, $data);

        $this->flushCache();

        return $record->refresh();
    }



    protected function updateAttributes($record, array $data): void
    {
        $record->update(
            collect($data)->except('company_logo_file', 'login_background_image_file', 'fav_icon_file')->toArray()
        );
    }


      /* ---------------- Company Logo ---------------- */
    protected function handleCompanyLogo($record, array $data): void
    {
        if (!isset($data['company_logo_file'])) {
            return;
        }

        $this->deleteOldCompanyLogo($record);
        $this->storeNewCompanyLogo($record, $data['company_logo_file']);
    }

    protected function handleFavIcon($record, array $data): void
    {
        if (!isset($data['fav_icon_file'])) {
            return;
        }

        $this->deleteOldFavIcon($record);
        $this->storeNewFavIcon($record, $data['fav_icon_file']);
    }

    protected function deleteOldFavIcon($record): void
    {
        if ($record->fav_icon_path) {
            FileHelper::delete($record->fav_icon_path);
        }
    }

    public function storeNewFavIcon($record, $file): void
    {
        $record->update([
            'fav_icon_path' => FileHelper::store($file, '/settings'),
        ]);
    }

    protected function deleteOldCompanyLogo($record): void
    {
        if ($record->company_logo_path) {
            FileHelper::delete($record->company_logo_path);
        }
    }
    protected function storeNewCompanyLogo($record, $file): void
    {
        $record->update([
            'company_logo_path' => FileHelper::store($file, '/settings'),
        ]);
    }




       /* ---------------- Login Background Image ---------------- */

        protected function handleLoginBackgroundImage($record, array $data): void
    {
        if (!isset($data['login_background_image_file'])) {
            return;
        }

        $this->deleteOldLoginBackgroundImage($record);
        $this->storeNewLoginBackgroundImage($record, $data['login_background_image_file']);
    }

    protected function deleteOldLoginBackgroundImage($record): void
    {
        if ($record->login_background_image_path) {
            FileHelper::delete($record->login_background_image_path);
        }
    }

    protected function storeNewLoginBackgroundImage($record, $file): void
    {
        $record->update([
            'login_background_image_path' => FileHelper::store($file, '/settings'),
        ]);
    }

    public function embassyUpdate(int $id, array $data)
    {
            $record =  $this->mutate(fn() => tap($this->model->findOrFail($id))->update($data));
            return $record->refresh();
    }


}
