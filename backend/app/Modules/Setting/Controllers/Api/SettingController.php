<?php

namespace App\Modules\Setting\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Modules\Setting\Requests\SettingRequest;
use App\Modules\Setting\Services\SettingService;
use App\Modules\Setting\Resources\SettingResource;
use App\Modules\Setting\Requests\EmbassyRequest;
use App\Modules\Setting\Resources\EmbassyResource;
use App\Modules\Setting\Services\SettingDataDbService;
use App\Modules\Parties\Requests\PartyTypeMappingRequest;
use App\Modules\Parties\Services\PartyTypeService;

class SettingController extends Controller
{
    public function __construct(
        private SettingService $service,
        private SettingDataDbService $dataDbService,
        private PartyTypeService $partyTypeService,
    ) {}

     public function show($id)
    {
        \Log::info("Fetching setting with ID: {$id}");
        return new SettingResource($this->service->getById($id));
    }


    public function update(SettingRequest $request, $id)
    {
        $record = $this->service->update(
            $id,
            $request->validated()
        );

        return apiSuccess(
            new SettingResource($record),
            'updated'
        );
    }

    public function landingPageInfo()
    {
       return new SettingResource($this->service->getById(1));
    }

    public function backup()
    {
        try {
            // 1️⃣ Generate a filename with timestamp
            $fileName = 'backup_' . date('Y_m_d_H_i_s') . '.sql';
            $backupDir = storage_path('app/backups');

            // 2️⃣ Make sure the backups folder exists
            if (!file_exists($backupDir)) {
                mkdir($backupDir, 0755, true);
            }

            $filePath = $backupDir . '/' . $fileName;

            // 3️⃣ Database credentials from .env
            $dbHost = env('DB_HOST');
            $dbPort = env('DB_PORT', 3306);
            $dbName = env('DB_DATABASE');
            $dbUser = env('DB_USERNAME');
            $dbPass = env('DB_PASSWORD');

            // 4️⃣ Detect mysqldump path dynamically
            $mysqldumpPath = trim(shell_exec('which mysqldump'));
            if (!$mysqldumpPath) {
                throw new \Exception('mysqldump not found. Please install mysql-client.');
            }

            // 5️⃣ Build backup command
            $passwordPart = !empty($dbPass) ? "-p{$dbPass}" : '';
            $command = "{$mysqldumpPath} -h {$dbHost} -u {$dbUser} {$passwordPart} "
                . "--routines --triggers --events --single-transaction --quick --add-drop-table "
                . "{$dbName} > {$filePath}";

            exec($command . ' 2>&1', $output, $returnVar);

            if ($returnVar !== 0 || !file_exists($filePath)) {
                throw new \Exception('Database backup failed. Command output: ' . implode("\n", $output));
            }

            // 6️⃣ Return file as download and delete after sending
            return response()->download($filePath, $fileName, [
                'Content-Type' => 'application/sql',
            ])->deleteFileAfterSend(true);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Backup failed: ' . $e->getMessage()
            ], 500);
        }
    }

    public function embassyShow($id)
    {
        return new EmbassyResource($this->service->getById($id));
    }

    public function embassyUpdate(EmbassyRequest $request, $id)
    {
        $record = $this->service->embassyUpdate($id, $request->validated());
        $this->dataDbService->clearSettingDataCache();
        return apiSuccess(new EmbassyResource($record), 'Embassy data updated successfully');
    }

    public function getSettingData()
    {
        $data = $this->dataDbService->getSettingData();
        return apiSuccess($data, 'Setting data retrieved successfully');
    }

    public function partyTypeMappingsShow()
    {
        return apiSuccess(
            $this->partyTypeService->getSourceModuleMappings(),
            'Party type mappings retrieved successfully'
        );
    }

    public function partyTypeMappingsUpdate(PartyTypeMappingRequest $request)
    {
        $mappings = $this->partyTypeService->updateSourceModuleMappings(
            $request->validated('mappings')
        );

        return apiSuccess($mappings, 'Party type mappings updated successfully');
    }


}
