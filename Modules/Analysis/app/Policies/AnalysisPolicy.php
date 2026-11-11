<?php

namespace Modules\Analysis\Policies;

use Modules\Analysis\Models\Analysis;
use App\Models\User;

class AnalysisPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Analysis $analysis): bool
    {
        return true;
    }

    public function create(User $user): bool
    {
        return true;
    }

    public function update(User $user, Analysis $analysis): bool
    {
        return true;
    }

    public function delete(User $user, Analysis $analysis): bool
    {
        return true;
    }

    public function restore(User $user, Analysis $analysis): bool
    {
        return true;
    }

    public function forceDelete(User $user, Analysis $analysis): bool
    {
        return true;
    }

    public function bulkDelete(User $user): bool
    {
        return true;
    }

    public function bulkUpdateResult(User $user): bool
    {
        return true;
    }

    public function manageSettings(User $user): bool
    {
        return true;
    }
}
