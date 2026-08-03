<?php
namespace App\Modules\Agent\Contracts;

interface AgentDataServiceInterface
{
    public function getAgentData(): array;
    public function clearAgentDataCache(): void;
}
