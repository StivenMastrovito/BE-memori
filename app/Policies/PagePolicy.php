<?php

namespace App\Policies;

use App\Models\Page;
use App\Models\User;

class PagePolicy
{
    /**
     * L'utente può vedere il dettaglio della pagina solo se è sua.
     */
    public function view(User $user, Page $page): bool
    {
        return $user->id === $page->user_id;
    }

    /**
     * Chiunque sia autenticato può creare una pagina.
     */
    public function create(User $user): bool
    {
        return true;
    }

    /**
     * Solo il proprietario può modificarla.
     */
    public function update(User $user, Page $page): bool
    {
        return $user->id === $page->user_id;
    }

    /**
     * Solo il proprietario può eliminarla.
     */
    public function delete(User $user, Page $page): bool
    {
        return $user->id === $page->user_id;
    }
}