<?php

namespace App\Modules\__MODEL__\Services;

use App\Modules\__MODEL__\Models\__MODEL__;
use App\Services\BaseCachedService;
use App\Modules\Shared\Helpers\FileHelper;

class __MODEL__Service extends BaseCachedService
{
    public function __construct()
    {
        parent::__construct(new __MODEL__());
    }

    /* ==========================================================
     | Read Operations (Cached)
     |========================================================== */

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

        $this->flushCache();

        return $record->refresh();
    }

    /* ==========================================================
     | Write Operations (Invalidate Cache)
     |========================================================== */

    protected function updateAttributes($record, array $data): void
    {
        $record->update(
            collect($data)->except('company_logo_file')->toArray()
        );
    }

    protected function handleCompanyLogo($record, array $data): void
    {
        if (!isset($data['company_logo_file'])) {
            return;
        }

        $this->deleteOldCompanyLogo($record);
        $this->storeNewCompanyLogo($record, $data['company_logo_file']);
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
}
