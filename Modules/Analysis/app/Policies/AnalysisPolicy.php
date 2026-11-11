<?php

namespace Modules\Analysis\Policies;

use Modules\Analysis\Models\Analysis;
use App\Models\User;

class AnalysisPolicy
{
    public function viewAny(User $user): bool
    {
        return in_array($user->role, User::ROLES, true);
    }

    public function view(User $user, Analysis $analysis): bool
    {
        return $this->viewAny($user);
    }

    public function create(User $user): bool
    {
        return $user->canMutateAnalyses();
    }

    public function update(User $user, Analysis $analysis): bool
    {
        return $user->canMutateAnalyses();
    }

    public function delete(User $user, Analysis $analysis): bool
    {
        return $user->isAdmin();
    }

    public function restore(User $user, Analysis $analysis): bool
    {
        return $user->isAdmin();
    }

    public function forceDelete(User $user, Analysis $analysis): bool
    {
        return $user->isAdmin();
    }

    public function bulkDelete(User $user): bool
    {
        return $user->isAdmin();
    }

    public function bulkUpdateResult(User $user): bool
    {
        return $user->canMutateAnalyses();
    }

    public function manageSettings(User $user): bool
    {
        return $user->canMutateAnalyses();
    }
}
