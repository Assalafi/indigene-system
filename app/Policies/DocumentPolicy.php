<?php

namespace App\Policies;

use App\Models\ApplicationDocument;
use App\Models\User;

class DocumentPolicy
{
    use ChecksLgaScope;

    public function view(User $user, ApplicationDocument $document): bool
    {
        return $user->isActive()
            && $user->can('document.view')
            && ($user->isSystemAdmin() || $this->sameLga($user, $document->application->lga_id));
    }

    public function download(User $user, ApplicationDocument $document): bool
    {
        return $this->view($user, $document);
    }
}
